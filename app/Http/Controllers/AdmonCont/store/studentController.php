<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Users\User;
use App\Models\Users\AcademicProfile;
use App\Models\Users\Role;
use App\Models\Users\Enrollment;
use App\Models\Users\Period;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\HorarioClaseOculta;
use App\Support\HorarioResumenParser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class studentController extends Controller
{
    public function acceptAspirante(Request $request, Lead $lead)
    {
        $normalize = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
        $curp = $normalize($lead->alumno_curp);

        if ($curp === '') {
            return response()->json([
                'success' => false,
                'message' => 'El aspirante no tiene CURP, no se puede convertir automáticamente.',
            ], 422);
        }

        $institutionId = (int) session('active_institution_id', 4);
        $studentRole = Role::query()->where('name', 'estudiante')->first();
        if (! $studentRole) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el rol estudiante.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::query()->where('curp', $curp)->first();

            if (! $user) {
                $baseRfc = $curp;
                $rfc = $baseRfc;
                $i = 1;
                while (User::query()->where('RFC', $rfc)->exists()) {
                    $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                    $rfc = substr($baseRfc, 0, 11) . $suffix;
                    $i++;
                }

                $email = null;
                $emailCandidate = strtolower($curp) . '@tmp.umi.local';
                if (! User::query()->where('email', $emailCandidate)->exists()) {
                    $email = $emailCandidate;
                }

                $user = User::create([
                    'nombre' => trim((string) ($lead->alumno_nombre ?? '')),
                    'apellido_paterno' => trim((string) ($lead->alumno_paterno ?? '')),
                    'apellido_materno' => trim((string) ($lead->alumno_materno ?? '-')),
                    'email' => $email,
                    'password' => Hash::make('TMP_' . uniqid()),
                    'RFC' => $rfc,
                    'curp' => $curp,
                    'telefono' => $lead->telefono1,
                    'institution_id' => $institutionId,
                    'role_id' => $studentRole->id,
                    'is_active' => 1,
                ]);
            }

            $user->institutions()->syncWithoutDetaching([$institutionId]);
            $user->roles()->syncWithoutDetaching([
                $studentRole->id => ['institution_id' => $institutionId, 'is_active' => 1],
            ]);

            AcademicProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'career_id' => $lead->carrera_id,
                    'semestre' => (int) ($lead->semestre ?? 1),
                    'status' => 'Alumno',
                    'is_anfitrion' => false,
                    'doc_acta_nacimiento' => $lead->doc_acta_nacimiento,
                    'doc_certificado_prepa' => $lead->doc_certificado_prepa,
                    'doc_curp' => $lead->doc_curp,
                    'doc_ine' => $lead->doc_ine,
                    'doc_ficha_pago' => $lead->doc_ficha_pago,
                    'doc_factura_xml' => $lead->doc_factura_xml,
                    'doc_acta_rechazado' => (bool) $lead->doc_acta_rechazado,
                    'doc_certificado_rechazado' => (bool) $lead->doc_certificado_rechazado,
                    'doc_curp_rechazado' => (bool) $lead->doc_curp_rechazado,
                    'doc_ine_rechazado' => (bool) $lead->doc_ine_rechazado,
                    'doc_ficha_pago_rechazado' => (bool) $lead->doc_ficha_pago_rechazado,
                    'doc_factura_xml_rechazado' => (bool) $lead->doc_factura_xml_rechazado,
                ]
            );

            // Actualizar la(s) inscripción(es) en espera para que el alumno ya pueda editar.
            $periodoActivo = Period::where('is_active', 1)->first();
            $enrollUpdates = [
                'career_id' => $lead->carrera_id,
                'semestre' => (int) ($lead->semestre ?? 1),
                'periodo' => $periodoActivo?->id,
                'status' => 'Inscrito',
                'doc_acta_nacimiento' => $lead->doc_acta_nacimiento,
                'doc_certificado_prepa' => $lead->doc_certificado_prepa,
                'doc_curp' => $lead->doc_curp,
                'doc_ine' => $lead->doc_ine,
            ];

            Enrollment::where('user_id', $user->id)
                ->where('status', 'Pendiente')
                ->update($enrollUpdates);

            // Si no existía enrollment pendiente, lo creamos para mantener coherencia.
            if (!Enrollment::where('user_id', $user->id)->where('status', 'Pendiente')->exists()) {
                Enrollment::create(array_merge($enrollUpdates, [
                    'user_id' => $user->id,
                ]));
            }

            $lead->seguimientos()->create([
                'estado' => 'Alumno',
                'fecha' => now()->toDateString(),
                'hora' => now()->toTimeString(),
                'comentario' => 'Aspirante aceptado desde Control Escolar.',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Aspirante aceptado como alumno.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No se pudo aceptar el aspirante. Intenta nuevamente.',
            ], 500);
        }
    }

    /**
     * Guarda el correo del estudiante (users.email) desde el modal de expediente.
     * No modifica el lead en el CRM.
     */
    public function syncAlumnoEmailFromModal(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'alumno_email' => 'nullable|email|max:255',
            'alumno_curp' => 'nullable|string|max:18',
        ]);

        $email = trim((string) ($data['alumno_email'] ?? ''));
        if ($email === '') {
            return response()->json(['success' => true]);
        }

        $normalize = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
        $curpN = $normalize($data['alumno_curp'] ?? $lead->alumno_curp ?? '');
        if ($curpN === '') {
            return response()->json([
                'success' => false,
                'message' => 'Se necesita la CURP del alumno para guardar su correo.',
            ], 422);
        }

        $user = User::query()
            ->whereRaw('UPPER(REPLACE(TRIM(IFNULL(curp, \'\')), \' \', \'\')) = ?', [$curpN])
            ->whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
            ->first();

        if (! $user) {
            return response()->json(['success' => true, 'skipped' => true]);
        }

        Validator::make(
            ['alumno_email' => $email],
            ['alumno_email' => 'required|email|max:255|unique:users,email,' . $user->id]
        )->validate();

        $user->update(['email' => $email]);

        return response()->json(['success' => true]);
    }

    /**
     * Guarda el expediente del aspirante (lead CRM) desde Control Escolar / Académico.
     * La ruta del CRM (/crm/leads) solo permitía master/coordinador; aquí quien gestiona la lista sí puede guardar.
     * Si existe usuario estudiante con la misma CURP, sincroniza nombre, apellidos, teléfono y CURP.
     */
    public function updateLeadExpediente(Request $request, Lead $lead)
    {
        $normalize = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
        $prevCurpN = $normalize($lead->alumno_curp ?? '');

        $nullableMerge = [];
        if ($request->input('carrera_id') === '' || $request->input('carrera_id') === null) {
            $nullableMerge['carrera_id'] = null;
        }
        if ($request->input('semestre') === '' || $request->input('semestre') === null) {
            $nullableMerge['semestre'] = null;
        }
        // Siempre normalizar CURP (has() es false cuando viene cadena vacía y no se aplicaba el merge).
        $c = trim((string) $request->input('alumno_curp', ''));
        $nullableMerge['alumno_curp'] = $c === '' ? null : strtoupper(preg_replace('/\s+/', '', $c));

        $request->merge($nullableMerge);

        $request->validate([
            'alumno_nombre' => 'required|string|max:255',
            'alumno_paterno' => 'required|string|max:255',
            'alumno_materno' => 'required|string|max:255',
            'alumno_curp' => [
                'nullable',
                'string',
                'max:18',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (strlen((string) $value) !== 18) {
                        $fail('La CURP debe tener exactamente 18 caracteres.');
                    }
                },
            ],
            'telefono1' => 'required|string|max:20',
            'carrera_id' => 'nullable|exists:careers,id',
            'semestre' => 'nullable|integer|min:1|max:12',
            'doc_acta_nacimiento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_certificado_prepa' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_curp' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_ine' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_ficha_pago' => 'nullable|file|mimes:pdf|max:5120',
            'doc_factura_xml' => 'nullable|file|mimes:pdf,xml,txt,text/plain|max:5120',
        ]);

        $data = $request->only(['alumno_nombre', 'alumno_paterno', 'alumno_materno', 'alumno_curp', 'telefono1', 'carrera_id', 'semestre']);

        $docRechazoPorCampo = [
            'doc_acta_nacimiento' => 'doc_acta_rechazado',
            'doc_certificado_prepa' => 'doc_certificado_rechazado',
            'doc_curp' => 'doc_curp_rechazado',
            'doc_ine' => 'doc_ine_rechazado',
            'doc_ficha_pago' => 'doc_ficha_pago_rechazado',
            'doc_factura_xml' => 'doc_factura_xml_rechazado',
        ];

        // Solo tocar archivos / rechazos si el usuario abrió o usó la pestaña Documentación (evita perder marcas al guardar solo datos personales).
        if ($request->boolean('lead_edit_docs_interacted')) {
            foreach ($docRechazoPorCampo as $campo => $flagCol) {
                if ($request->hasFile($campo)) {
                    if ($lead->$campo) {
                        Storage::disk('public')->delete($lead->$campo);
                    }
                    $pathBase = in_array($campo, ['doc_ficha_pago', 'doc_factura_xml'], true)
                        ? "documentos/leads/{$lead->id}/facturacion"
                        : "documentos/leads/{$lead->id}";
                    $data[$campo] = $request->file($campo)->store($pathBase, 'public');
                    $data[$flagCol] = false;
                } elseif ($request->boolean('rechazar_' . $campo)) {
                    if ($lead->$campo) {
                        Storage::disk('public')->delete($lead->$campo);
                    }
                    $data[$campo] = null;
                    $data[$flagCol] = true;
                } else {
                    // Conservar ruta existente al marcar solo "no rechazado" (evita pérdidas al guardar la pestaña de documentos).
                    $data[$campo] = $lead->$campo;
                    $data[$flagCol] = false;
                }
            }
        }

        $docsInteracted = $request->boolean('lead_edit_docs_interacted');

        DB::transaction(function () use ($lead, $data, $prevCurpN, $docsInteracted): void {
            $lead->update($data);
            $lead->refresh();
            $this->syncEstudianteUserFromLeadAfterExpedienteSave($lead, $prevCurpN, $docsInteracted);
        });

        return response()->json(['success' => true, 'message' => 'Expediente actualizado correctamente.']);
    }

    private function syncEstudianteUserFromLeadAfterExpedienteSave(Lead $lead, string $prevCurpN, bool $syncDocumentos = false): void
    {
        $normalize = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
        $newCurpN = $normalize($lead->alumno_curp ?? '');
        $curpsToMatch = array_values(array_unique(array_filter([$prevCurpN, $newCurpN])));
        if ($curpsToMatch === []) {
            return;
        }

        $user = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
            ->where(function ($q) use ($curpsToMatch) {
                foreach ($curpsToMatch as $i => $c) {
                    if ($i === 0) {
                        $q->whereRaw('UPPER(REPLACE(TRIM(IFNULL(curp, \'\')), \' \', \'\')) = ?', [$c]);
                    } else {
                        $q->orWhereRaw('UPPER(REPLACE(TRIM(IFNULL(curp, \'\')), \' \', \'\')) = ?', [$c]);
                    }
                }
            })
            ->first();

        if (! $user) {
            return;
        }

        $payload = [
            'nombre' => trim((string) ($lead->alumno_nombre ?? '')),
            'apellido_paterno' => trim((string) ($lead->alumno_paterno ?? '')),
            'apellido_materno' => trim((string) ($lead->alumno_materno ?? '')),
            'telefono' => $lead->telefono1,
        ];

        if ($lead->alumno_curp !== null && trim((string) $lead->alumno_curp) !== '') {
            $curpVal = strtoupper(trim(preg_replace('/\s+/', '', (string) $lead->alumno_curp)));
            Validator::make(
                ['curp' => $curpVal],
                ['curp' => 'required|string|size:18|unique:users,curp,' . $user->id]
            )->validate();
            $payload['curp'] = $curpVal;
        } else {
            $payload['curp'] = null;
        }

        $user->update($payload);

        if ($user->academicProfile) {
            $payloadAcademic = [];
            if ($lead->carrera_id) {
                $payloadAcademic['career_id'] = $lead->carrera_id;
            }
            if ($lead->semestre !== null) {
                $payloadAcademic['semestre'] = (int) $lead->semestre;
            }

            if ($syncDocumentos) {
                $docCampos = ['doc_acta_nacimiento', 'doc_certificado_prepa', 'doc_curp', 'doc_ine', 'doc_ficha_pago', 'doc_factura_xml'];
                $flagCampos = ['doc_acta_rechazado', 'doc_certificado_rechazado', 'doc_curp_rechazado', 'doc_ine_rechazado', 'doc_ficha_pago_rechazado', 'doc_factura_xml_rechazado'];
                $profile = $user->academicProfile;

                foreach ($docCampos as $campo) {
                    $viejo = $profile->$campo;
                    $nuevo = $lead->$campo;
                    if ($viejo && $nuevo && $viejo !== $nuevo) {
                        Storage::disk('public')->delete($viejo);
                    } elseif ($viejo && ($nuevo === null || $nuevo === '')) {
                        Storage::disk('public')->delete($viejo);
                    }
                }

                foreach ($docCampos as $campo) {
                    $payloadAcademic[$campo] = $lead->$campo;
                }
                foreach ($flagCampos as $fc) {
                    $payloadAcademic[$fc] = (bool) ($lead->$fc ?? false);
                }
            }

            if ($payloadAcademic !== []) {
                $user->academicProfile->update($payloadAcademic);
            }
        }
    }

    // 1. MOSTRAR LISTA GENERAL
    public function index(Request $request)
    {
        $perPage = 10;
        $filter = $request->input('filter_status');

        // Solo leads CRM en estado Aspirante (último seguimiento)
        if ($filter === 'aspirantes') {
            $dataList = $this->buildAspiranteLeadsPaginator($request, $perPage);
        } elseif ($filter === 'activos' || $filter === 'inactivos') {
            $query = $this->studentsBaseQuery($request);
            if ($filter === 'activos') {
                // "Alumno" en UI: aceptados quedan en 'Alumno'; al asignar contraseña pasan a 'Alumno Activo'
                $query->whereHas('academicProfile', function ($q) {
                    $q->whereIn('status', ['Alumno Activo', 'Alumno']);
                });
            } else {
                $query->whereHas('academicProfile', function ($q) {
                    $q->where('status', 'Alumno Inactivo');
                });
            }
            $dataList = $query->orderBy('created_at', 'desc')->paginate($perPage)
                ->through(fn (User $user) => (object) [
                    'kind' => 'user',
                    'user' => $user,
                    'lead' => null,
                ]);
        } else {
            // Sin filtro (o valor vacío): alumnos inscritos + leads CRM en Aspirante
            $dataList = $this->buildMergedStudentsAndAspiranteLeadsPaginator($request, $perPage);
        }

        // Fallback visual: si el usuario no tiene career_id aún, intentar mostrar carrera desde su lead CRM por CURP.
        $this->applyCareerFallbackFromLead($dataList);
        // Correo del alumno (inscripción → users.email) para filas lead CRM con misma CURP.
        $this->attachLeadStudentEmailFromInscripcion($dataList);

        // Petición AJAX: devolver solo el cuerpo de la tabla (sin recargar toda la página)
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('layouts.ControlAdmin.Listas.students.partials.table_body', compact('dataList'));
        }

        return view('layouts.ControlAdmin.Listas.students.index', compact('dataList'));
    }

    /**
     * Usuarios con rol estudiante + filtros de búsqueda (sin filtro activo/inactivo).
     */
    private function studentsBaseQuery(Request $request)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'estudiante');
        })->with(['academicProfile.career']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                    ->orWhere('curp', 'like', "%{$search}%")
                    ->orWhereHas('academicProfile', function ($subQ) use ($search) {
                        $subQ->whereHas('career', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                    });
            });
        }

        return $query;
    }

    /**
     * Leads con último seguimiento = Aspirante (misma regla que el CRM).
     */
    private function aspiranteLeadsQuery(Request $request)
    {
        $q = Lead::queryBaseAspirantesControlEscolar()->with(['carrera']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $q->where(function ($w) use ($search) {
                $w->where('alumno_nombre', 'like', "%{$search}%")
                    ->orWhere('alumno_paterno', 'like', "%{$search}%")
                    ->orWhere('alumno_materno', 'like', "%{$search}%")
                    ->orWhere('alumno_curp', 'like', "%{$search}%")
                    ->orWhereHas('carrera', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $q;
    }

    private function buildAspiranteLeadsPaginator(Request $request, int $perPage): LengthAwarePaginator
    {
        $leads = $this->aspiranteLeadsQuery($request)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Lead $lead) => (object) [
                'kind' => 'lead',
                'user' => null,
                'lead' => $lead,
                'sort_at' => $lead->updated_at ?? $lead->created_at,
            ]);

        $aspiranteUsers = $this->studentsBaseQuery($request)
            ->whereHas('academicProfile', fn ($q) => $q->where('status', 'Aspirante'))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user) => (object) [
                'kind' => 'user',
                'user' => $user,
                'lead' => null,
                'sort_at' => $user->created_at,
            ]);

        $merged = $leads->concat($aspiranteUsers)->sortByDesc('sort_at')->values();

        $currentPage = Paginator::resolveCurrentPage();
        $total = $merged->count();
        $items = $merged->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function buildMergedStudentsAndAspiranteLeadsPaginator(Request $request, int $perPage): LengthAwarePaginator
    {
        $students = $this->studentsBaseQuery($request)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user) => (object) [
                'kind' => 'user',
                'user' => $user,
                'lead' => null,
                'sort_at' => $user->created_at,
            ]);

        $leads = $this->aspiranteLeadsQuery($request)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Lead $lead) => (object) [
                'kind' => 'lead',
                'user' => null,
                'lead' => $lead,
                'sort_at' => $lead->updated_at ?? $lead->created_at,
            ]);

        $merged = $students->concat($leads)->sortByDesc('sort_at')->values();

        $currentPage = Paginator::resolveCurrentPage();
        $total = $merged->count();
        $items = $merged->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * Para filas de usuarios con carrera vacía, intenta tomar el nombre de carrera desde el lead CRM (matching por CURP).
     * Solo afecta visualización en la tabla, no persiste cambios en BD.
     */
    private function applyCareerFallbackFromLead(LengthAwarePaginator $dataList): void
    {
        $items = collect($dataList->items());

        $users = $items
            ->map(fn ($item) => $item->user ?? null)
            ->filter(fn ($user) => $user instanceof User);

        $usersWithCurp = $users->filter(fn (User $user) => !empty($user->curp));
        if ($usersWithCurp->isEmpty()) {
            return;
        }

        $normalize = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));

        $curps = $usersWithCurp
            ->map(fn (User $user) => $normalize($user->curp))
            ->filter()
            ->unique()
            ->values();

        if ($curps->isEmpty()) {
            return;
        }

        $leads = Lead::with('carrera')
            ->whereIn(
                DB::raw("UPPER(REPLACE(TRIM(IFNULL(alumno_curp, '')), ' ', ''))"),
                $curps->all()
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get([
                'id',
                'alumno_curp',
                'carrera_id',
                'semestre',
                'doc_acta_nacimiento',
                'doc_certificado_prepa',
                'doc_curp',
                'doc_ine',
                'doc_ficha_pago',
                'doc_factura_xml',
                'doc_acta_rechazado',
                'doc_certificado_rechazado',
                'doc_curp_rechazado',
                'doc_ine_rechazado',
                'doc_ficha_pago_rechazado',
                'doc_factura_xml_rechazado',
                'telefono1',
                'tutor_email',
                'updated_at',
            ]);

        $leadByCurp = [];
        foreach ($leads as $lead) {
            $curp = $normalize($lead->alumno_curp);
            if ($curp === '' || isset($leadByCurp[$curp])) {
                continue;
            }
            $leadByCurp[$curp] = $lead;
        }

        foreach ($usersWithCurp as $user) {
            $curp = $normalize($user->curp);
            $matchedLead = $leadByCurp[$curp] ?? null;
            $user->fallback_lead_id = $matchedLead?->id;
            $user->fallback_lead_carrera_id = $matchedLead?->carrera_id;
            $user->fallback_lead_semestre = $matchedLead?->semestre;
            $user->fallback_lead_doc_acta = $matchedLead?->doc_acta_nacimiento;
            $user->fallback_lead_doc_cert = $matchedLead?->doc_certificado_prepa;
            $user->fallback_lead_doc_curp = $matchedLead?->doc_curp;
            $user->fallback_lead_doc_ine = $matchedLead?->doc_ine;
            $user->fallback_lead_doc_ficha = $matchedLead?->doc_ficha_pago;
            $user->fallback_lead_doc_xml = $matchedLead?->doc_factura_xml;
            $user->fallback_lead_doc_rech_acta = (bool) ($matchedLead?->doc_acta_rechazado ?? false);
            $user->fallback_lead_doc_rech_cert = (bool) ($matchedLead?->doc_certificado_rechazado ?? false);
            $user->fallback_lead_doc_rech_curp = (bool) ($matchedLead?->doc_curp_rechazado ?? false);
            $user->fallback_lead_doc_rech_ine = (bool) ($matchedLead?->doc_ine_rechazado ?? false);
            $user->fallback_lead_doc_rech_ficha = (bool) ($matchedLead?->doc_ficha_pago_rechazado ?? false);
            $user->fallback_lead_doc_rech_xml = (bool) ($matchedLead?->doc_factura_xml_rechazado ?? false);

            $careerId = $user->academicProfile->career_id ?? null;
            if (empty($careerId)) {
                $user->fallback_career_name = $matchedLead?->carrera?->name;
            }
        }
    }

    /**
     * En filas tipo lead, expone el correo que guardó el alumno al enviar inscripción (users.email),
     * cruzando por CURP. No modifica el CRM.
     */
    private function attachLeadStudentEmailFromInscripcion(LengthAwarePaginator $dataList): void
    {
        $items = collect($dataList->items());
        $leadItems = $items->filter(fn ($i) => ($i->kind ?? null) === 'lead' && isset($i->lead));
        if ($leadItems->isEmpty()) {
            return;
        }

        $normalize = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));

        $curps = $leadItems
            ->map(fn ($i) => $normalize($i->lead->alumno_curp ?? ''))
            ->filter()
            ->unique()
            ->values();

        if ($curps->isEmpty()) {
            return;
        }

        $users = User::query()
            ->whereIn(DB::raw("UPPER(REPLACE(TRIM(IFNULL(curp, '')), ' ', ''))"), $curps->all())
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderByDesc('updated_at')
            ->get(['curp', 'email']);

        $emailByCurp = [];
        foreach ($users as $u) {
            $c = $normalize($u->curp);
            if ($c !== '' && ! isset($emailByCurp[$c])) {
                $emailByCurp[$c] = $u->email;
            }
        }

        foreach ($leadItems as $item) {
            $c = $normalize($item->lead->alumno_curp ?? '');
            $item->lead_student_email = $emailByCurp[$c] ?? '';
        }
    }

    // 2. MOSTRAR FORMULARIO DE EDICIÓN
    public function edit($id)
    {
        $user = User::with(['academicProfile', 'address'])->findOrFail($id);
        
        $departamentos = \App\Models\Users\Department::all();
        $puestos = \App\Models\Users\Workstation::all();
        $carreras = \App\Models\Users\Career::all(); 

        return view('layouts.ControlAdmin.Listas.students.edit', compact('user', 'departamentos', 'puestos', 'carreras'));
    }

    // 3. GUARDAR CAMBIOS (DATOS, ARCHIVOS Y CONTRASEÑA)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {
            // A. Actualizar Datos Personales
            $user->update([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'RFC' => $request->RFC,
                'curp' => $request->curp,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $request->edad,
            ]);

            // Actualizar Dirección
            if ($user->address) {
                $user->address->update([
                    'calle' => $request->calle,
                    'colonia' => $request->colonia,
                    'ciudad' => $request->ciudad,
                    'estado' => $request->estado,
                    'codigo_postal' => $request->codigo_postal,
                ]);
            }

            // B. Actualizar Datos Laborales (Si es anfitrión)
            $esAnfitrion = $request->has('is_anfitrion');
            if ($esAnfitrion) {
                $user->department_id = $request->department_id;
                $user->workstation_id = $request->workstation_id;
            } else {
                $user->department_id = null;
                $user->workstation_id = null;
            }
            $user->save();

            // C. LÓGICA DE ARCHIVOS Y PERFIL ACADÉMICO (Aquí estaba el faltante)
            // 1. Subir documentos (Retorna array solo con los nuevos)
            $nuevosDocs = $this->subirDocumentos($request, $user->id);

            // 2. Preparar datos del perfil
            // Nota: En tu vista el select se llama 'carrera', mapeamos a 'career_id'
            $datosPerfil = [
                'is_anfitrion' => $esAnfitrion,
                'career_id' => $request->carrera ?? $user->academicProfile->career_id,
                'semestre' => $request->semestre ?? $user->academicProfile->semestre,
            ];

            // 3. Fusionar datos + documentos nuevos (array_filter evita nulos)
            // Si el perfil no existe, lo crea. Si existe, lo actualiza.
            $user->academicProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge($datosPerfil, array_filter($nuevosDocs))
            );

            // D. Asignar Contraseña y Activar
            if ($request->filled('password')) {
                if (empty($user->academicProfile->matricula)) {
                    return back()->with('error', '⛔ No puedes asignar contraseña porque el alumno no tiene matrícula (falta pago).');
                }

                $user->password = Hash::make($request->password);
                $user->save();

                if ($user->academicProfile->status !== 'Alumno Activo') {
                    $user->academicProfile->status = 'Alumno Activo';
                    $user->academicProfile->save();
                }
            }

            DB::commit();
            return redirect()->route('escolar.students.index')
                ->with('success', 'Alumno actualizado correctamente (Datos y Documentos).');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$user->roles()->where('name', 'estudiante')->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Acceso no autorizado.'], 403);
            }
            abort(403, 'Acceso no autorizado.');
        }

        try {
            $user->delete();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'No se pudo eliminar el alumno.'], 500);
            }
            throw $e;
        }

        $message = 'Alumno eliminado correctamente.';
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Exportar lista de alumnos a CSV/Excel. Incluye alumnos inscritos y aspirantes CRM según filtro.
     */
    public function export(Request $request): StreamedResponse
    {
        $filter = $request->input('filter_status');

        if ($filter === 'aspirantes') {
            return $this->exportAspiranteLeadsCsv($request);
        }

        // Sin filtro: exportar alumnos + aspirantes CRM (mismo criterio que la lista)
        if (empty($filter)) {
            return $this->exportMergedStudentsAndLeadsCsv($request);
        }

        // activos / inactivos: solo alumnos
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'estudiante');
        })->with(['academicProfile.career', 'address']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                    ->orWhere('curp', 'like', "%{$search}%")
                    ->orWhereHas('academicProfile', function ($subQ) use ($search) {
                        $subQ->whereHas('career', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                    });
            });
        }

        if ($filter === 'activos') {
            $query->whereHas('academicProfile', fn ($q) => $q->whereIn('status', ['Alumno Activo', 'Alumno']));
        } elseif ($filter === 'inactivos') {
            $query->whereHas('academicProfile', fn ($q) => $q->where('status', 'Alumno Inactivo'));
        }

        $dataList = $query->orderBy('created_at', 'desc')->get();

        $filename = 'alumnos_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $headersCsv = [
            'Tipo', 'CURP', 'Nombre', 'Apellido Paterno', 'Apellido Materno',
            'Email', 'Teléfono', 'RFC', 'Fecha Nacimiento', 'Edad',
            'Calle', 'Colonia', 'Ciudad', 'Estado', 'Código Postal',
            'Carrera', 'Semestre', 'Estatus', 'Matrícula',
        ];

        return response()->streamDownload(function () use ($dataList, $headersCsv) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $headersCsv, ',');
            foreach ($dataList as $user) {
                $status = $user->academicProfile->status ?? null;
                fputcsv($out, [
                    'Alumno', $user->curp ?? '', $user->nombre ?? '', $user->apellido_paterno ?? '', $user->apellido_materno ?? '',
                    $user->email ?? '', $user->telefono ?? '', $user->RFC ?? '', $user->fecha_nacimiento ?? '', $user->edad ?? '',
                    $user->address?->calle ?? '', $user->address?->colonia ?? '', $user->address?->ciudad ?? '', $user->address?->estado ?? '', $user->address?->codigo_postal ?? '',
                    $user->academicProfile?->career?->name ?? '', $user->academicProfile?->semestre ?? '', $status ?? '', $user->academicProfile?->matricula ?? '',
                ], ',');
            }
            fclose($out);
        }, $filename, $headers);
    }

    /**
     * Exportar alumnos inscritos + aspirantes CRM en un solo CSV (sin filtro de estatus).
     */
    private function exportMergedStudentsAndLeadsCsv(Request $request): StreamedResponse
    {
        $students = $this->studentsBaseQuery($request)->orderByDesc('created_at')->get();
        $leads = $this->aspiranteLeadsQuery($request)->with('carrera')->orderByDesc('updated_at')->orderByDesc('created_at')->get();

        $filename = 'alumnos_y_aspirantes_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $headersCsv = [
            'Tipo', 'CURP', 'Nombre', 'Apellido Paterno', 'Apellido Materno',
            'Email', 'Teléfono', 'RFC', 'Fecha Nacimiento', 'Edad',
            'Calle', 'Colonia', 'Ciudad', 'Estado', 'Código Postal',
            'Carrera', 'Semestre', 'Estatus', 'Matrícula',
        ];

        return response()->streamDownload(function () use ($students, $leads, $headersCsv) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $headersCsv, ',');

            foreach ($students as $user) {
                $status = $user->academicProfile->status ?? '';
                if ($status === 'Aspirante') {
                    $status = 'Alumno Inactivo';
                }
                fputcsv($out, [
                    'Alumno', $user->curp ?? '', $user->nombre ?? '', $user->apellido_paterno ?? '', $user->apellido_materno ?? '',
                    $user->email ?? '', $user->telefono ?? '', $user->RFC ?? '', $user->fecha_nacimiento ?? '', $user->edad ?? '',
                    $user->address?->calle ?? '', $user->address?->colonia ?? '', $user->address?->ciudad ?? '', $user->address?->estado ?? '', $user->address?->codigo_postal ?? '',
                    $user->academicProfile?->career?->name ?? '', $user->academicProfile?->semestre ?? '', $status ?? '', $user->academicProfile?->matricula ?? '',
                ], ',');
            }

            foreach ($leads as $lead) {
                fputcsv($out, [
                    'Aspirante CRM', $lead->alumno_curp ?? '', $lead->alumno_nombre ?? '', $lead->alumno_paterno ?? '', $lead->alumno_materno ?? '',
                    '', $lead->telefono1 ?? '', '', '', '',
                    '', '', '', '', '',
                    $lead->carrera?->name ?? '', $lead->semestre ?? '1', 'Aspirante', '',
                ], ',');
            }

            fclose($out);
        }, $filename, $headers);
    }

    /**
     * CSV de leads CRM cuyo último seguimiento es Aspirante.
     */
    private function exportAspiranteLeadsCsv(Request $request): StreamedResponse
    {
        $leads = $this->aspiranteLeadsQuery($request)
            ->with('carrera')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get();

        $aspiranteUsers = $this->studentsBaseQuery($request)
            ->whereHas('academicProfile', fn ($q) => $q->where('status', 'Aspirante'))
            ->with(['academicProfile.career'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'aspirantes_crm_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $headersCsv = [
            'CURP Alumno',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Carrera (CRM)',
            'Semestre',
            'Estatus CRM',
            'Email',
            'Teléfono 1',
            'Teléfono 2',
        ];

        return response()->streamDownload(function () use ($leads, $aspiranteUsers, $headersCsv) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $headersCsv, ',');
            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->alumno_curp ?? '',
                    $lead->alumno_nombre ?? '',
                    $lead->alumno_paterno ?? '',
                    $lead->alumno_materno ?? '',
                    $lead->carrera?->name ?? '',
                    $lead->semestre ?? '1',
                    'Aspirante',
                    '',
                    $lead->telefono1 ?? '',
                    $lead->telefono2 ?? '',
                ], ',');
            }
            foreach ($aspiranteUsers as $user) {
                fputcsv($out, [
                    $user->curp ?? '',
                    $user->nombre ?? '',
                    $user->apellido_paterno ?? '',
                    $user->apellido_materno ?? '',
                    $user->academicProfile?->career?->name ?? '',
                    $user->academicProfile?->semestre ?? '',
                    'Aspirante',
                    $user->email ?? '',
                    $user->telefono ?? '',
                    '',
                ], ',');
            }
            fclose($out);
        }, $filename, $headers);
    }

    // --- HELPER PARA SUBIDA DE ARCHIVOS ---
    private function subirDocumentos($request, $userId) {
        $rutas = [];
        $campos = ['doc_acta_nacimiento', 'doc_certificado_prepa', 'doc_curp', 'doc_ine'];
        
        foreach ($campos as $campo) {
            if ($request->hasFile($campo)) {
                // Guarda en storage/app/public/documentos/{id}/expediente
                $rutas[$campo] = $request->file($campo)->store("documentos/{$userId}/expediente", 'public');
            }
        }
        return $rutas;
    }

    /**
     * Horario del alumno (clases en las que está inscrito). Misma estructura que horario de docente.
     * Respuesta AJAX: solo la grilla semanal para el modal.
     */
    public function horarios(Request $request, string $id): View
    {
        $user = User::with(['academicProfile.career'])->findOrFail($id);

        if (!$user->roles()->where('name', 'estudiante')->exists()) {
            abort(403, 'Acceso no autorizado.');
        }

        $q = trim((string) $request->input('q', ''));
        $dia = $request->input('dia');
        $diaInt = ($dia !== null && $dia !== '') ? (int) $dia : null;

        // Registros guardados en Control → Clases (cajita): mismo alumno
        $cajitaRows = HorarioClaseOculta::query()
            ->where('alumno_id', (int) $user->id)
            ->whereNotNull('horario_clase_id')
            ->orderByDesc('id')
            ->get()
            ->unique('horario_clase_id');

        $materiaLabels = [];
        $horarioResumenPorClase = [];
        foreach ($cajitaRows as $row) {
            $hid = (int) $row->horario_clase_id;
            if ($hid > 0 && $row->materia_nombre !== null && $row->materia_nombre !== '') {
                $materiaLabels[$hid] = $row->materia_nombre;
            }
            if ($hid > 0 && $row->horario_resumen !== null && trim((string) $row->horario_resumen) !== '') {
                $horarioResumenPorClase[$hid] = $row->horario_resumen;
            }
        }

        // Inscripciones (pivot). Sin filtrar por día en SQL: JSON/franjas pueden fallar y borrar la cajita del alumno.
        $horarios = $user->horarioClases()
            ->with(['carrera', 'materia', 'aula', 'franjas'])
            ->get();

        $idsExtra = $cajitaRows->pluck('horario_clase_id')
            ->map(fn ($x) => (int) $x)
            ->filter()
            ->unique()
            ->diff($horarios->pluck('id'))
            ->values()
            ->all();

        if ($idsExtra !== []) {
            $extras = HorarioClase::query()
                ->with(['carrera', 'materia', 'aula', 'franjas'])
                ->whereIn('id', $idsExtra)
                ->get();
            $horarios = $horarios->merge($extras)->unique('id')->values();
        }

        if ($diaInt !== null && $diaInt >= 1 && $diaInt <= 7) {
            $horarios = $horarios->filter(function ($hc) use ($diaInt, $horarioResumenPorClase) {
                return $this->horarioCubreDia($hc, $diaInt, $horarioResumenPorClase[$hc->id] ?? null);
            })->values();
        }

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $horarios = $horarios->filter(function ($hc) use ($needle, $materiaLabels) {
                $nombreMat = mb_strtolower((string) ($hc->materia->nombre ?? ''));
                $label = mb_strtolower((string) ($materiaLabels[$hc->id] ?? ''));
                $carrera = mb_strtolower((string) ($hc->carrera->name ?? ''));
                $aulaBlob = \App\Support\AulaHorarioPresenter::searchBlob($hc->aula);

                return str_contains($nombreMat, $needle)
                    || str_contains($label, $needle)
                    || str_contains($carrera, $needle)
                    || str_contains($aulaBlob, $needle);
            })->values();
        }

        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Listas.members.partials.horarios_grilla_semanal', [
                'user' => $user,
                'horarios' => $horarios,
                'q' => $q,
                'dia' => $dia,
                'esAlumno' => true,
                'materiaLabels' => $materiaLabels,
                'horarioResumenPorClase' => $horarioResumenPorClase,
            ]);
        }

        return view('layouts.ControlAdmin.Listas.members.horarios', [
            'user' => $user,
            'horarios' => $horarios,
            'q' => $q,
            'dia' => $dia,
            'tituloHorario' => 'Horario de Alumno',
            'materiaLabels' => $materiaLabels,
            'horarioResumenPorClase' => $horarioResumenPorClase,
        ]);
    }

    /**
     * ¿La clase imparte al día $diaInt (1–7)? Usa horario_resumen de cajita si existe; si no, franjas en BD.
     */
    private function horarioCubreDia(HorarioClase $hc, int $diaInt, ?string $resumen): bool
    {
        if ($resumen !== null && trim((string) $resumen) !== '') {
            foreach (HorarioResumenParser::intervalsFromResumen($resumen) as $iv) {
                if ((int) ($iv['dia'] ?? 0) === $diaInt) {
                    return true;
                }
            }
        }
        foreach ($hc->franjas ?? [] as $f) {
            $diasRaw = $f->dias_semana;
            if (is_array($diasRaw)) {
                $diasList = $diasRaw;
            } elseif (is_string($diasRaw)) {
                $dec = json_decode($diasRaw, true);
                $diasList = is_array($dec) ? $dec : ($diasRaw !== '' ? preg_split('/\s*,\s*/', $diasRaw) : []);
            } elseif ($diasRaw !== null && $diasRaw !== '') {
                $diasList = [(int) $diasRaw];
            } else {
                $diasList = [];
            }
            foreach ($diasList as $d) {
                if ((int) $d === $diaInt) {
                    return true;
                }
            }
        }

        return false;
    }
}