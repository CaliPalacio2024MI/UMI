<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Users\User;
use App\Models\Users\AcademicProfile;

class MatriculaController extends Controller
{
    /**
     * Muestra la lista de alumnos para gestión de matrículas.
     */
    public function index(Request $request)
    {
        $query = User::with(['academicProfile.career.classification', 'billings.payments'])
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', 7);
            });

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $needle = mb_strtolower($search);
            $isStatusOrMatriculaKeyword = in_array($needle, [
                'pagado', 'pagados',
                'pendiente', 'pendientes', 'no pagado', 'no pagados',
                'con matricula', 'con matrícula',
                'sin matricula', 'sin matrícula',
            ], true);

            // Si el término es keyword de estado, evitamos filtrar por texto en SQL
            // para no dejar la colección vacía antes del filtro en memoria.
            if (! $isStatusOrMatriculaKeyword) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('academicProfile', function ($ap) use ($search) {
                        $ap->where('matricula', 'like', "%{$search}%")
                            ->orWhereHas('career', function ($c) use ($search) {
                                $c->where('name', 'like', "%{$search}%")
                                    ->orWhereHas('classification', function ($cl) use ($search) {
                                        $cl->where('name', 'like', "%{$search}%");
                                    });
                            });
                    });
            });
            }
        }

        $students = $query->paginate(15);

        $students->getCollection()->transform(function ($student) {
            $facturaInscripcion = $student->billings->filter(function ($b) {
                return (str_starts_with($b->factura_uid, 'INS-') || str_starts_with($b->factura_uid, 'RE-'))
                    && $b->status !== 'Cancelada';
            })->last();

            $statusPago = 'N/A';

            if ($facturaInscripcion) {
                $pagado = $facturaInscripcion->status === 'Pagada'
                    || ($facturaInscripcion->payments->sum('monto') >= $facturaInscripcion->monto);
                $statusPago = $pagado ? 'Pagado' : 'Pendiente';
            } else {
                $statusPago = 'Pagado';
            }

            $student->billing_status = $statusPago;
            $student->doc_certificado = $student->academicProfile?->doc_certificado_prepa ? true : false;
            $student->doc_acta = $student->academicProfile?->doc_acta_nacimiento ? true : false;
            $student->doc_curp = $student->academicProfile?->doc_curp ? true : false;
            $student->docs_ready_for_matricula = $this->hasRequiredEnrollmentDocs($student->academicProfile);
            $student->matricula_can_be_assigned = $statusPago === 'Pagado' && $student->docs_ready_for_matricula;

            return $student;
        });

        // Filtros por búsqueda "inteligente" (cuando el texto representa estados)
        if ($request->filled('search')) {
            $needle = mb_strtolower(trim((string) $request->search));

            // Status pago
            if (in_array($needle, ['pagado', 'pagados'], true)) {
                $students->setCollection($students->getCollection()->filter(fn ($s) => ($s->billing_status ?? '') === 'Pagado'));
            } elseif (in_array($needle, ['pendiente', 'pendientes', 'no pagado', 'no pagados'], true)) {
                $students->setCollection($students->getCollection()->filter(fn ($s) => ($s->billing_status ?? '') === 'Pendiente'));
            }

            // Matrícula
            if (in_array($needle, ['con matricula', 'con matrícula'], true)) {
                $students->setCollection($students->getCollection()->filter(fn ($s) => ! empty($s->academicProfile?->matricula)));
            } elseif (in_array($needle, ['sin matricula', 'sin matrícula'], true)) {
                $students->setCollection($students->getCollection()->filter(fn ($s) => empty($s->academicProfile?->matricula)));
            }

        }

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('layouts.ControlEsc.Matriculas.partials.table_rows', [
                    'dataList' => $students,
                ])->render(),
                'pagination' => (string) $students->appends($request->query())->links(),
            ]);
        }

        return view('layouts.ControlEsc.Matriculas.index', [
            'dataList' => $students
        ]);
    }

    /**
     * Da de alta una matrícula (endpoint store del módulo).
     */
    public function store(Request $request, int $id)
    {
        return $this->saveMatricula($request, $id);
    }

    /**
     * Edita matrícula.
     */
    public function update(Request $request, int $id)
    {
        return $this->saveMatricula($request, $id);
    }

    /**
     * Visualiza matrícula/documentos del alumno para consumo de backend (JSON).
     */
    public function show(int $id)
    {
        $student = User::with(['academicProfile.career', 'billings.payments'])->findOrFail($id);
        $profile = $student->academicProfile;

        return response()->json([
            'user_id' => $student->id,
            'nombre' => trim(($student->nombre ?? '') . ' ' . ($student->apellido_paterno ?? '') . ' ' . ($student->apellido_materno ?? '')),
            'email' => $student->email,
            'career' => $profile?->career?->name,
            'matricula' => $profile?->matricula,
            'documento_sep_path' => $profile?->documentoSEP_path,
            'documentos' => [
                'doc_acta_nacimiento' => $profile?->doc_acta_nacimiento,
                'doc_certificado_prepa' => $profile?->doc_certificado_prepa,
                'doc_curp' => $profile?->doc_curp,
                'doc_ine' => $profile?->doc_ine,
            ],
            'docs_ready_for_matricula' => $this->hasRequiredEnrollmentDocs($profile),
            'billing_status' => $this->resolveBillingStatus($student),
        ]);
    }

    /**
     * Elimina matrícula (la deja en null).
     */
    public function destroy(int $id)
    {
        $student = User::with('academicProfile')->findOrFail($id);
        if (! $student->academicProfile) {
            return redirect()->back()->with('error', 'El alumno no tiene perfil académico para eliminar matrícula.');
        }

        $student->academicProfile->update(['matricula' => null]);

        return redirect()->back()->with('success', 'Matrícula eliminada correctamente.');
    }

    public function uploadDocumento(Request $request, int $id)
    {
        $request->validate([
            'documento_pdf' => 'required|mimes:pdf|max:5120',
        ]);

        $student = User::with('academicProfile')->findOrFail($id);

        if ($request->hasFile('documento_pdf')) {
            $filename = 'doc_' . $id . '_' . time() . '.pdf';
            $path = $request->file('documento_pdf')->storeAs('documentacion_sep', $filename, 'public');

            if ($student->academicProfile?->documentoSEP_path && $student->academicProfile->documentoSEP_path !== $path) {
                Storage::disk('public')->delete($student->academicProfile->documentoSEP_path);
            }

            if (! $student->academicProfile) {
                return back()->with('error', 'No se puede adjuntar documento SEP: el alumno no tiene perfil académico.');
            }

            $student->academicProfile->update([
                'documentoSEP_path' => $path,
            ]);

            return back()->with('success', 'Documento SEP subido y vinculado correctamente.');
        }

        return back()->with('error', 'No se pudo subir el archivo.');
    }

    private function saveMatricula(Request $request, int $id)
    {
        $request->validate([
            'matricula' => [
                'required',
                'string',
                'max:20',
                Rule::unique('academic_profiles', 'matricula')->ignore($id, 'user_id'),
            ],
        ]);

        $user = User::with(['billings.payments', 'academicProfile'])->findOrFail($id);
        $profile = $user->academicProfile;
        if (! $profile) {
            return redirect()->back()->with('error', 'El alumno no tiene perfil académico creado.');
        }

        $billingStatus = $this->resolveBillingStatus($user);
        if ($billingStatus !== 'Pagado') {
            return redirect()->back()->with('error', 'ACCIÓN BLOQUEADA: El alumno tiene pagos pendientes de inscripción/reinscripción.');
        }

        if (! $this->hasRequiredEnrollmentDocs($profile)) {
            return redirect()->back()->with('error', 'ACCIÓN BLOQUEADA: faltan documentos de expediente (Acta, Certificado y CURP) para asignar matrícula.');
        }

        if (empty($profile->documentoSEP_path)) {
            return redirect()->back()->with('error', 'ACCIÓN BLOQUEADA: primero sube el documento SEP para completar el trámite de matrícula.');
        }

        $profile->update([
            'matricula' => trim((string) $request->matricula),
        ]);

        return redirect()->back()->with('success', 'Matrícula guardada correctamente: ' . $request->matricula);
    }

    private function resolveBillingStatus(User $user): string
    {
        $factura = $user->billings()
            ->where(function ($q) {
                $q->where('factura_uid', 'like', 'INS-%')
                    ->orWhere('factura_uid', 'like', 'RE-%');
            })
            ->where('status', '!=', 'Cancelada')
            ->latest()
            ->first();

        if (! $factura) {
            return 'Pagado';
        }

        $factura->loadMissing('payments');
        $totalPagado = $factura->payments->sum('monto');
        $estaPagada = $factura->status === 'Pagada' || $totalPagado >= $factura->monto;

        return $estaPagada ? 'Pagado' : 'Pendiente';
    }

    private function hasRequiredEnrollmentDocs(?AcademicProfile $profile): bool
    {
        if (! $profile) {
            return false;
        }

        return ! empty($profile->doc_acta_nacimiento)
            && ! empty($profile->doc_certificado_prepa)
            && ! empty($profile->doc_curp);
    }
}