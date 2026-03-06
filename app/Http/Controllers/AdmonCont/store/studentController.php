<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use App\Models\Users\AcademicProfile;
use App\Models\Users\Address;
use App\Models\Users\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class studentController extends Controller
{
    // 1. MOSTRAR LISTA GENERAL
    public function index(Request $request)
    {
        // Consulta base: Solo usuarios con rol 'estudiante'
        $query = User::whereHas('roles', function($q) {
            $q->where('name', 'estudiante');
        })->with(['academicProfile.career']);

        // Buscador
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('curp', 'like', "%{$search}%")
                  ->orWhereHas('academicProfile', function($subQ) use ($search) {
                      $subQ->where('matricula', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por estatus: activos / inactivos
        if ($request->filled('filter_status')) {
            $filter = $request->input('filter_status');
            if ($filter === 'activos') {
                $query->whereHas('academicProfile', function($q) {
                    $q->where('status', 'Alumno Activo');
                });
            } elseif ($filter === 'inactivos') {
                $query->whereHas('academicProfile', function($q) {
                    $q->where('status', 'Alumno Inactivo');
                });
            }
        }

        $dataList = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('layouts.ControlAdmin.Listas.students.index', compact('dataList'));
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

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Exportar lista de alumnos a CSV/Excel con todos los campos del registro de aspirante.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'estudiante');
        })->with(['academicProfile.career', 'address']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('curp', 'like', "%{$search}%")
                    ->orWhereHas('academicProfile', function ($subQ) use ($search) {
                        $subQ->where('matricula', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('filter_status')) {
            $filter = $request->input('filter_status');
            if ($filter === 'activos') {
                $query->whereHas('academicProfile', fn ($q) => $q->where('status', 'Alumno Activo'));
            } elseif ($filter === 'inactivos') {
                $query->whereHas('academicProfile', fn ($q) => $q->where('status', 'Alumno Inactivo'));
            }
        }

        $dataList = $query->orderBy('created_at', 'desc')->get();

        $filename = 'alumnos_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $headersCsv = [
            'CURP',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Email',
            'Teléfono',
            'RFC',
            'Fecha Nacimiento',
            'Edad',
            'Calle',
            'Colonia',
            'Ciudad',
            'Estado',
            'Código Postal',
            'Carrera',
            'Semestre',
            'Estatus',
            'Matrícula',
        ];

        return response()->streamDownload(function () use ($dataList, $headersCsv) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 para Excel
            fputcsv($out, $headersCsv, ',');
            foreach ($dataList as $user) {
                $status = $user->academicProfile->status ?? null;
                if ($status === 'Aspirante') {
                    $status = 'Alumno Inactivo';
                }
                fputcsv($out, [
                    $user->curp ?? '',
                    $user->nombre ?? '',
                    $user->apellido_paterno ?? '',
                    $user->apellido_materno ?? '',
                    $user->email ?? '',
                    $user->telefono ?? '',
                    $user->RFC ?? '',
                    $user->fecha_nacimiento ?? '',
                    $user->edad ?? '',
                    $user->address?->calle ?? '',
                    $user->address?->colonia ?? '',
                    $user->address?->ciudad ?? '',
                    $user->address?->estado ?? '',
                    $user->address?->codigo_postal ?? '',
                    $user->academicProfile?->career?->name ?? '',
                    $user->academicProfile?->semestre ?? '',
                    $status ?? '',
                    $user->academicProfile?->matricula ?? '',
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

        $horariosQuery = $user->horarioClases()
            ->with(['carrera', 'materia', 'aula', 'franjas']);

        if ($q !== '') {
            $horariosQuery->where(function ($w) use ($q) {
                $w->whereHas('materia', fn($m) => $m->where('nombre', 'LIKE', '%' . $q . '%'))
                    ->orWhereHas('carrera', fn($c) => $c->where('name', 'LIKE', '%' . $q . '%'))
                    ->orWhereHas('aula', fn($a) => $a->where('numero_aula', 'LIKE', '%' . $q . '%'));
            });
        }

        if ($dia !== null && $dia !== '') {
            $diaInt = (int) $dia;
            $horariosQuery->whereHas('franjas', function ($fr) use ($diaInt) {
                $fr->whereRaw('JSON_CONTAINS(dias_semana, ?)', [json_encode($diaInt)]);
            });
        }

        $horarios = $horariosQuery->get();

        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Listas.members.partials.horarios_grilla_semanal', [
                'user' => $user, 'horarios' => $horarios, 'q' => $q, 'dia' => $dia, 'esAlumno' => true,
            ]);
        }

        return view('layouts.ControlAdmin.Listas.members.horarios', [
            'user' => $user,
            'horarios' => $horarios,
            'q' => $q,
            'dia' => $dia,
            'tituloHorario' => 'Horario de Alumno',
        ]);
    }
}