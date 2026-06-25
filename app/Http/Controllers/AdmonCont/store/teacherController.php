<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\Users\Career;
use App\Models\Users\AcademicProfile;
use App\Models\Users\Address;
use App\Models\Users\Role;
use App\Models\Users\User;
use App\Models\AdmonCont\HorarioClase;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class teacherController extends Controller
{
    //
    public function index(Request $request): View
    {
        $listType = 'members'; // Definimos el tipo de lista fijo
        $roleName = 'docente'; // Definimos el rol fijo

        // --- Definición de Columnas ---
        
        // 1. Columnas a seleccionar de la tabla 'users'
        $userColumns = [
            'id',
            'RFC',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'created_at',
            'is_active',
        ];
        
        // 2. Columnas a seleccionar de la tabla 'datos_academicos' (¡incluye user_id!)
        $academicColumns = [
            'user_id',
            'status',
            'career_id',
        ];

        $careerColumns=[
            'official_id',
            'name',
            'id',
            'classification_id',
        ];
        // En belongsToMany la pivote también tiene `id`; el SELECT debe calificar columnas de `careers`.
        $teachingCareerColumns = array_map(
            static fn (string $col): string => 'careers.' . $col,
            $careerColumns
        );

        // --- Ejecución de la Consulta ---
        
        $dataList = User::query()
            // Filtra usuarios que tienen el rol 'estudiante'
            ->whereHas('roles', function (Builder $query) use ($roleName) {
                $query->where('name', $roleName); 
            })
            // Selecciona las columnas necesarias de la tabla 'users'
            ->select($userColumns)
            // Carga la relación 'academicProfile' con columnas específicas
            ->with(['academicProfile' => function (Relation $query) use ($academicColumns) {
                $query->select($academicColumns);
            }])
            ->with(['academicProfile.career' => function (Relation $query) use ($careerColumns) {
                $query->select($careerColumns);
            }])
            ->with(['academicProfile.career.classification' => function (Relation $query) {
                $query->select(['id', 'name']);
            }])
            ->with(['teachingCareers' => function (Relation $query) use ($teachingCareerColumns) {
                $query->select($teachingCareerColumns);
            }])
            ->with(['teachingCareers.classification' => function (Relation $query) {
                $query->select(['id', 'name']);
            }])
            ->get();

        // --- Devolución de la Vista ---
        $carreras = Career::all(['id', 'name']);
        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';

        return view($viewPath, [
            'dataList' => $dataList,
            'carreras' => $carreras,
        ]);
    }

    /**
     * Exportar lista de docentes a CSV con todos los datos del formulario.
     */
    public function export(Request $request): StreamedResponse
    {
        $roleName = 'docente';
        $search = trim((string) $request->query('search_query', ''));
        $userColumns = ['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'email', 'telefono', 'RFC', 'fecha_nacimiento', 'edad', 'address_id'];
        $academicColumns = ['user_id', 'status', 'career_id', 'departamento'];
        $careerColumns = ['id', 'name'];
        $teachingCareerColumnsExport = array_map(
            static fn (string $col): string => 'careers.' . $col,
            $careerColumns
        );

        $dataList = User::query()
            ->whereHas('roles', function (Builder $query) use ($roleName) {
                $query->where('name', $roleName);
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $like = '%' . $search . '%';
                    $q->where('RFC', 'like', $like)
                        ->orWhere('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhereHas('teachingCareers', function (Builder $cq) use ($like) {
                            $cq->where('name', 'like', $like);
                        })
                        ->orWhereHas('academicProfile.career', function (Builder $cq) use ($like) {
                            $cq->where('name', 'like', $like);
                        });
                });
            })
            ->select($userColumns)
            ->with(['address'])
            ->with(['academicProfile' => function (Relation $query) use ($academicColumns) {
                $query->select($academicColumns);
            }])
            ->with(['academicProfile.career' => function (Relation $query) use ($careerColumns) {
                $query->select($careerColumns);
            }])
            ->with(['teachingCareers' => function (Relation $query) use ($teachingCareerColumnsExport) {
                $query->select($teachingCareerColumnsExport);
            }])
            ->get();

        $filename = 'docentes_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $headersCsv = [
            'Carrera',
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
            'Aula',
            'Estado',
        ];

        return response()->streamDownload(function () use ($dataList, $headersCsv) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 para Excel
            fputcsv($out, $headersCsv, ',');
            foreach ($dataList as $user) {
                $edadCsv = $user->edad;
                if (($edadCsv === null || $edadCsv === '') && $user->fecha_nacimiento) {
                    try {
                        $edadCsv = Carbon::parse($user->fecha_nacimiento)->age;
                    } catch (\Throwable $e) {
                        $edadCsv = '';
                    }
                }
                fputcsv($out, [
                    ($user->teachingCareers->isNotEmpty()
                        ? $user->teachingCareers->pluck('name')->implode('; ')
                        : ($user->academicProfile?->career?->name ?? '')),
                    $user->nombre ?? '',
                    $user->apellido_paterno ?? '',
                    $user->apellido_materno ?? '',
                    $user->email ?? '',
                    $user->telefono ?? '',
                    $user->RFC ?? '',
                    $user->fecha_nacimiento ?? '',
                    $edadCsv !== null && $edadCsv !== '' ? (string) $edadCsv : '',
                    $user->address?->calle ?? '',
                    $user->address?->colonia ?? '',
                    $user->address?->ciudad ?? '',
                    $user->address?->estado ?? '',
                    $user->address?->codigo_postal ?? '',
                    '-',
                    (isset($user->is_active) && $user->is_active ? 'Activo' : 'Inactivo'),
                ], ',');
            }
            fclose($out);
        }, $filename, $headers);
    }

    public function form(Request $request){
        // 1. Cargar las Carreras
        // Asume que el modelo se llama 'Carrera' y tiene las columnas 'id' y 'nombre'.
       $carreras = Career::all();
       $clasificaciones = \App\Models\Users\CareerClassification::orderBy('name')->get();

        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Listas.members.partials.form_create', compact('carreras', 'clasificaciones'));
        }

        return view('layouts.ControlAdmin.Listas.members.create', compact('carreras', 'clasificaciones' /*, 'campuses' */));
    }

    /**
     * Lista de docentes en JSON para el menú desplegable del registro.
     * Hoy usa docentes de este sistema; luego puedes sustituir por una API externa
     * devolviendo el mismo formato: [{ id, nombre, apellido_paterno, apellido_materno, email, telefono, RFC, fecha_nacimiento, edad, calle, colonia, ciudad, estado, codigo_postal }, ...]
     */
    public function listForRegister(Request $request)
    {
        $list = User::query()
            ->whereHas('roles', fn (Builder $q) => $q->where('name', 'docente'))
            ->with(['address', 'academicProfile'])
            ->get()
            ->map(function (User $u) {
                return [
                    'id' => $u->id,
                    'nombre' => $u->nombre ?? '',
                    'apellido_paterno' => $u->apellido_paterno ?? '',
                    'apellido_materno' => $u->apellido_materno ?? '',
                    'email' => $u->email ?? '',
                    'telefono' => $u->telefono ?? '',
                    'RFC' => $u->RFC ?? '',
                    'fecha_nacimiento' => $u->fecha_nacimiento ? \Carbon\Carbon::parse($u->fecha_nacimiento)->format('Y-m-d') : '',
                    'edad' => $u->edad ?? '',
                    'calle' => $u->address?->calle ?? '',
                    'colonia' => $u->address?->colonia ?? '',
                    'ciudad' => $u->address?->ciudad ?? '',
                    'estado' => $u->address?->estado ?? '',
                    'codigo_postal' => $u->address?->codigo_postal ?? '',
                ];
            });

        return response()->json($list);
    }

    public function store(Request $request){
        // --- 1. VERIFICACIÓN / VALIDACIÓN DE DATOS ---
        $mensajes = [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser un correo electrónico válido.',
            'unique' => 'El correo electrónico ya está registrado.',
            'min.string' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max.string' => 'El campo :attribute no debe tener más de :max caracteres.',
            'RFC.required' => 'Falta rellenar el RFC.',
            'RFC.max' => 'El campo RFC no debe tener más de 13 caracteres.',
            'digits' => 'El campo :attribute debe tener :digits dígitos.',
            'date' => 'El campo :attribute no es una fecha válida.',
            'exists' => 'El valor seleccionado en :attribute no es válido.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'integer.min' => 'El campo :attribute debe ser al menos :min.',
            'integer.max' => 'El campo :attribute no debe ser mayor que :max.',
            'carreras.required' => 'Seleccione al menos una carrera.',
            'carreras.min' => 'Seleccione al menos una carrera.',
        ];
        $atributos = [
            'nombre' => 'nombre(s)',
            'apellido_paterno' => 'apellido paterno',
            'apellido_materno' => 'apellido materno',
            'email' => 'correo electrónico',
            'telefono' => 'teléfono',
            'RFC' => 'RFC',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'calle' => 'calle',
            'colonia' => 'colonia',
            'ciudad' => 'ciudad',
            'estado' => 'estado',
            'codigo_postal' => 'código postal',
            'carreras' => 'carreras',
            'departamento' => 'departamento',
        ];
        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:20'],
            'RFC' => ['required', 'string', 'max:13'],
            'fecha_nacimiento' => ['required', 'date'],
            'calle' => ['required', 'string', 'max:255'],
            'colonia' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'digits:5'],
            'carreras' => ['required', 'array', 'min:1'],
            'carreras.*' => ['integer', Rule::exists('careers', 'id')],
            'departamento' => ['nullable', 'string', 'max:255'],
        ], $mensajes, $atributos);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $validator->errors()->first() ?? 'Revisa los datos del formulario.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->route('control.teachers.index')
                ->withInput()
                ->withErrors($validator);
        }

        $institutionId = session('active_institution_id', 4);
        $carreraIds = collect($request->input('carreras', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        DB::beginTransaction();

        try {
            $edadCalculada = null;
            try {
                $edadCalculada = Carbon::parse($request->fecha_nacimiento)->age;
            } catch (\Throwable) {
                $edadCalculada = null;
            }

            // --- 1. GUARDAR LA DIRECCIÓN (MODELO ADDRESS) 📍 ---
            $address = Address::create([
                'calle' => $request->calle,
                'colonia' => $request->colonia,
                'ciudad' => $request->ciudad,
                'estado' => $request->estado,
                'codigo_postal' => $request->codigo_postal,
            ]);

            // --- 2. GUARDAR EL USUARIO (MODELO USER) 👤 ---
            // Texto plano: el cast `password => hashed` del modelo aplica el hash una sola vez.
            $user = User::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'password' => Str::password(24),
                'RFC' => $request->RFC ?: null,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $edadCalculada,
                'address_id' => $address->id,
                'institution_id' => $institutionId,
                'is_active' => true,
            ]);

            $user->institutions()->syncWithoutDetaching([$institutionId]);

            // --- 3. ASIGNAR EL ROL 'DOCENTE' (TABLA PIVOTE user_roles_institution) 🔑 ---
            $docenteRole = Role::where('name', 'docente')->firstOrFail();

            $user->roles()->attach($docenteRole->id, [
                'institution_id' => $institutionId,
                'is_active' => true,
            ]);
            // --- 4. GUARDAR EL PERFIL ACADÉMICO (MODELO ACADEMICPROFILE) 🎓 ---

            AcademicProfile::create([
                'user_id' => $user->id,
                'career_id' => $carreraIds[0] ?? null,
                'departamento' => $request->departamento ?? null,
                'status' => 'Activo',
            ]);
            $user->teachingCareers()->sync($carreraIds);
            // --- 5. FINALIZACIÓN Y REDIRECCIÓN 🎉 ---

            DB::commit();
            $message = 'Docente registrado exitosamente.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'message' => $message]);
            }
            return redirect()
                ->route('control.teachers.index', ['modal' => 'success'], 303)
                ->with('success', $message);
        } catch (QueryException $e) {
            DB::rollBack();
            report($e);
            $code = (string) $e->errorInfo[1] ?? '';
            $errMsg = ($code === '1062' || str_contains(strtolower($e->getMessage()), 'duplicate'))
                ? 'Ya existe un usuario con ese correo o RFC. Use otros datos o revise la lista.'
                : 'No se pudo guardar el docente. Intente de nuevo.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $errMsg], 500);
            }
            return redirect()->route('control.teachers.index')->withInput()->with('error', $errMsg);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            $errMsg = config('app.debug')
                ? $e->getMessage()
                : 'No se pudo guardar el docente. Intente de nuevo.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $errMsg], 500);
            }
            return redirect()->route('control.teachers.index')->withInput()->with('error', $errMsg);
        }

        

    }
    public function edit(string $id)
    {
        // 1. Buscar al usuario y cargar las relaciones necesarias
        // Usamos with(['address', 'academicProfile']) para cargar la información de dirección
        // y la información académica en una sola consulta, evitando problemas N+1.
        // findOrFail($id) asegura un error 404 si el ID no existe.
        $user = User::with(['address', 'academicProfile', 'teachingCareers'])->findOrFail($id);

        // Opcional: Si quieres asegurar que solo se editen usuarios con el rol 'Alumno' (ID 7)
        // Descomenta la siguiente línea si es necesario
        
        if (!$user->roles()->where('name', 'docente')->exists()) {
            abort(403, 'Acceso no autorizado. Este usuario no es docente.');
        }

        $carreras = Career::all(['id', 'name']);
        return view('layouts.ControlAdmin.Listas.members.edit', compact('user', 'carreras'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::with(['address', 'academicProfile', 'teachingCareers'])->findOrFail($id);

        if (!$user->roles()->where('name', 'docente')->exists()) {
            abort(403, 'Acceso no autorizado.');
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:20'],
            'RFC' => ['required', 'string', 'max:13'],
            'fecha_nacimiento' => ['required', 'date'],
            'calle' => ['required', 'string', 'max:255'],
            'colonia' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'digits:5'],
            'carreras' => ['required', 'array', 'min:1'],
            'carreras.*' => ['integer', Rule::exists('careers', 'id')],
            'departamento' => ['nullable', 'string', 'max:255'],
        ], [
            'RFC.required' => 'Falta rellenar el RFC.',
        ]);

        $carreraIds = collect($request->input('carreras', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        DB::beginTransaction();
        try {
            $user->update([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'RFC' => $request->RFC,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => \Carbon\Carbon::parse($request->fecha_nacimiento)->age,
            ]);

            if ($user->address) {
                $user->address->update([
                    'calle' => $request->calle,
                    'colonia' => $request->colonia,
                    'ciudad' => $request->ciudad,
                    'estado' => $request->estado,
                    'codigo_postal' => $request->codigo_postal,
                ]);
            }

            if ($user->academicProfile) {
                $user->academicProfile->update([
                    'career_id' => $carreraIds[0] ?? $user->academicProfile->career_id,
                    'departamento' => $request->departamento,
                ]);
            }
            $user->teachingCareers()->sync($carreraIds);

            DB::commit();
            $message = 'Docente actualizado correctamente.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'message' => $message]);
            }
            return redirect()->route('control.teachers.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            $errMsg = 'Error al actualizar el docente. Intente de nuevo.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $errMsg], 500);
            }
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if (!$user->roles()->where('name', 'docente')->exists()) {
            abort(403, 'Acceso no autorizado.');
        }

        $user->delete();
        $message = 'Docente eliminado correctamente.';
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }
        return redirect()->route('control.teachers.index')->with('success', $message);
    }

    public function show(string $id): View
    {
        $user = User::with(['address', 'academicProfile.career', 'teachingCareers'])->findOrFail($id);

        if (!$user->roles()->where('name', 'docente')->exists()) {
            abort(403, 'Acceso no autorizado.');
        }

        return view('layouts.ControlAdmin.Listas.members.show', compact('user'));
    }

    public function horarios(Request $request, string $id): View
    {
        $user = User::with(['academicProfile.career', 'teachingCareers'])->findOrFail($id);

        if (!$user->roles()->where('name', 'docente')->exists()) {
            abort(403, 'Acceso no autorizado.');
        }

        $horarios = HorarioClase::query()
            ->with(['carrera', 'materia', 'aula', 'franjas'])
            ->where('user_id', $user->id)
            ->get();

        // Cuando se abre desde el modal (AJAX), devolvemos solo la grilla semanal.
        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Listas.members.partials.horarios_grilla_semanal', compact('user', 'horarios'));
        }

        return view('layouts.ControlAdmin.Listas.members.horarios', compact('user', 'horarios'));
    }
}
