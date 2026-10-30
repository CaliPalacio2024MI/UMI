<?php

namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cursos\Course;
use App\Models\Cursos\CourseSession;
use App\Models\Users\Department;
use App\Models\Users\Institution;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Users\User;
use App\Services\ExternalApiService;
use Illuminate\Support\Facades\DB;

class CourseSessionController extends Controller
{
    public function index(Course $course)
    {
        if (!in_array($course->modality, ['presencial', 'hibrida'])) {
            abort(403, 'Este curso no permite gestión de horarios');
        }

        $sessions = $course->sessions()->orderBy('date')->get();
        $departments = Department::where('institution_id', session('active_institution_id'))->get();
        $groups = $course->groups;
        $users = User::where('institution_id', session('active_institution_id'))->get();

        return view('layouts.Cursos.sessions.index', compact(
            'course',
            'sessions',
            'departments',
            'groups',
            'users'
        ));
    }

    public function assignGroup(Request $request)
    {
        $session = CourseSession::findOrFail($request->session_id);
        $session->groups()->syncWithoutDetaching([$request->group_id]);

        return back()->with('success', 'Grupo asignado');
    }

    public function destroy($courseId, $sessionId)
    {
        $session = CourseSession::where('course_id', $courseId)
            ->where('id', $sessionId)
            ->firstOrFail();

        $session->delete();

        return back()->with('success', 'Horario eliminado correctamente');
    }

    public function toggle(Course $course, CourseSession $session)
    {
        $session->attendance_enabled = !$session->attendance_enabled;
        $session->save();

        // (Se quitó la actualización de courses.active: esa columna no existe en la tabla de cursos)

        return back()->with('success', $session->attendance_enabled
            ? 'Asistencia activada: ya se puede escanear el QR en este horario.'
            : 'Asistencia desactivada para este horario.');
    }

    public function update(Request $request, Course $course, CourseSession $session)
    {
        if ($session->course_id !== $course->id) {
            abort(404);
        }

        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'instructor_name' => 'required|string|max:255',
        ]);

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        if ($end <= $start) {
            return back()->withErrors(['error' => 'La hora fin debe ser mayor a la hora inicio']);
        }

        $expectedEnd = $start->copy()->addHours($course->hours);

        if (!$end->equalTo($expectedEnd)) {
            return back()->withErrors(['error' => 'La hora fin debe ser exactamente ' . $expectedEnd->format('H:i')]);
        }

        $session->update([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'instructor_name' => $request->instructor_name,
        ]);

        return back()->with('success', 'Horario actualizado correctamente');
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'instructor_name' => 'required|string|max:255',
        ]);

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        if ($end <= $start) {
            return back()->withErrors(['error' => 'La hora fin debe ser mayor a la hora inicio']);
        }

        $expectedEnd = $start->copy()->addHours($course->hours);

        if (!$end->equalTo($expectedEnd)) {
            return back()->withErrors(['error' => 'La hora fin debe ser exactamente ' . $expectedEnd->format('H:i')]);
        }

        CourseSession::create([
            'course_id' => $course->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'instructor_name' => $request->instructor_name,
            'attendance_enabled' => false,

        ]);

        return back()->with('success', 'Horario creado correctamente');
    }

    private const UNIVERSITY_NAME = 'Universidad Mundo Imperial';

    /** ¿La institución es una universidad? */
    public function isUniversity(?Institution $institution): bool
    {
        return $institution && ((bool) ($institution->is_universidad ?? false) || $institution->name === self::UNIVERSITY_NAME);
    }

    /**
     * Guarda el grupo de un horario presencial: unidad, filtros elegidos,
     * lista de participantes, tipo y mínimo/máximo. La lista se reemplaza
     * con lo que viene seleccionado.
     */
    public function storeGroup(Request $request)
    {
        $data = $request->validate([
            'session_id'       => 'required|exists:course_sessions,id',
            'institution_id'   => 'nullable|integer|exists:institutions,id',
            'selections'       => 'nullable|array',
            'selections.*'     => 'array',
            'selections.*.*'   => 'integer',
            'departments'      => 'nullable|array',
            'departments.*'    => 'integer|exists:departments,id',
            'workstations'     => 'nullable|array',
            'workstations.*'   => 'integer|exists:workstations,id',
            'users'            => 'nullable|array',
            'users.*'          => 'integer|exists:users,id',
            'type'             => 'nullable|in:abierto,cerrado',
            'min_participants' => 'nullable|integer|min:1',
            'max_participants' => 'nullable|integer|min:1|gte:min_participants',
        ], [
            'max_participants.gte' => 'El máximo de participantes debe ser mayor o igual al mínimo.',
        ]);

        $session = CourseSession::findOrFail($data['session_id']);
        $institutionId = (int) ($data['institution_id'] ?? session('active_institution_id'));

        $selections = collect($data['selections'] ?? [])
            ->only(['departments', 'workstations', 'classifications', 'careers', 'materias'])
            ->map(fn ($ids) => array_values(array_unique(array_map('intval', $ids))))
            ->all();

        // Compatibilidad: departamentos/puestos también pueden venir sueltos
        $departments = $data['departments'] ?? ($selections['departments'] ?? []);
        $workstations = $data['workstations'] ?? ($selections['workstations'] ?? []);

        $group = DB::transaction(function () use ($session, $data, $institutionId, $selections, $departments, $workstations) {
            $group = $session->groups()->first();

            $attributes = [
                'type'             => $data['type'] ?? 'abierto',
                'min_participants' => $data['min_participants'] ?? null,
                'max_participants' => $data['max_participants'] ?? null,
                'institution_id'   => $institutionId,
                'filters'          => $selections ?: null,
            ];

            if ($group) {
                $group->update($attributes);
            } else {
                $group = Group::create($attributes);
                $group->sessions()->attach($session->id);
            }

            $group->departments()->sync(Department::whereIn('id', $departments)->pluck('id'));
            $group->workstations()->sync(\App\Models\Users\Workstation::whereIn('id', $workstations)->pluck('id'));
            $group->participants()->sync($data['users'] ?? []);

            return $group;
        });

        $message = 'Grupo guardado: ' . $group->participants()->count() . ' participante(s).';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Catálogo para la ventana "Lista de participantes" de un horario.
     *  - Propiedad:   Departamentos → Puestos → Anfitriones
     *  - Universidad: Clasificaciones → Carreras → Materias → Alumnos
     * Solo aparece lo registrado en la unidad elegida. También regresa lo
     * que ya tiene guardado el grupo del horario.
     */
    public function participantsCatalog(Request $request, CourseSession $session)
    {
        $group = $session->groups()->with(['departments:id', 'workstations:id', 'participants:id'])->first();

        $catalog = $this->buildCatalog(
            (int) $request->query('institution_id') ?: null,
            $group->institution_id ?? null
        );

        $savedSelections = $group ? ($group->filters ?: [
            'departments'  => $group->departments->pluck('id')->all(),
            'workstations' => $group->workstations->pluck('id')->all(),
        ]) : [];

        $catalog['group'] = $group ? [
            'institution_id'   => $group->institution_id,
            'type'             => $group->type,
            'min_participants' => $group->min_participants,
            'max_participants' => $group->max_participants,
            'selections'       => $savedSelections,
            'participants'     => $group->participants->pluck('id'),
        ] : null;

        return response()->json($catalog);
    }

    /**
     * Arma el catálogo de la ventana "Lista de participantes". Lo usan Horarios
     * (presencial/híbrido) y Periodos (virtual), para que todo funcione igual.
     */
    public function buildCatalog(?int $requestedInstitutionId = null, ?int $savedInstitutionId = null): array
    {
        $user = auth()->user();
        $activeId = (int) session('active_institution_id');
        $active = Institution::find($activeId);
        $universityMode = $this->isUniversity($active);

        // Unidades del mismo tipo (propiedades o universidades) a las que tiene acceso
        $accessIds = ($user ? $user->institutions()->pluck('institutions.id') : collect())->push($activeId)->unique();
        $units = Institution::whereIn('id', $accessIds)->orderBy('name')->get()
            ->filter(fn ($i) => $this->isUniversity($i) === $universityMode)
            ->values();

        $institutionId = (int) ($requestedInstitutionId ?: $savedInstitutionId ?: $activeId);
        if (! $units->contains('id', $institutionId) && $units->isNotEmpty()) {
            $institutionId = $units->first()->id;
        }

        [$levels, $people, $peopleTitle] = $universityMode
            ? $this->universityCatalog($institutionId)
            : $this->propertyCatalog($institutionId);

        return [
            'mode'           => $universityMode ? 'university' : 'property',
            'unit_label'     => $universityMode ? 'Universidad' : 'Propiedad',
            'people_title'   => $peopleTitle,
            'properties'     => $units->map(fn ($i) => ['id' => $i->id, 'name' => $i->name])->values(),
            'institution_id' => $institutionId,
            'levels'         => $levels,
            'people'         => $people,
        ];
    }

    /** Propiedad: departamentos creados en ella → sus puestos → anfitriones con esos puestos (sin Master). */
    private function propertyCatalog(int $institutionId): array
    {
        $departments = Department::query()
            ->where(function ($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)
                  ->orWhereHas('institutions', fn ($i) => $i->where('institutions.id', $institutionId));
            })
            ->with(['workstations' => fn ($q) => $q->orderBy('name')->select('id', 'name', 'department_id')])
            ->orderBy('name')
            ->get(['id', 'name']);

        $wsName = [];
        $workstations = [];
        foreach ($departments as $d) {
            foreach ($d->workstations as $w) {
                $wsName[$w->id] = $w->name;
                $workstations[] = ['id' => $w->id, 'name' => $w->name, 'parent' => $d->id];
            }
        }

        $hosts = User::query()
            ->where(function ($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)
                  ->orWhereHas('institutions', fn ($i) => $i->where('institutions.id', $institutionId));
            })
            ->whereIn('department_id', $departments->pluck('id'))
            ->whereNotNull('workstation_id')
            ->whereDoesntHave('roles', fn ($q) => $q->whereRaw('LOWER(roles.name) = ?', ['master']))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'RFC', 'department_id', 'workstation_id'])
            ->map(fn ($h) => [
                'id'    => $h->id,
                'name'  => trim("{$h->nombre} {$h->apellido_paterno} {$h->apellido_materno}"),
                'sub'   => trim(($wsName[$h->workstation_id] ?? '') . ' · ' . ($h->RFC ?: 'Sin RFC'), ' ·'),
                'links' => ['departments' => [$h->department_id], 'workstations' => [$h->workstation_id]],
            ])
            ->values();

        $levels = [
            ['key' => 'departments', 'title' => 'Departamentos', 'empty' => 'Esta propiedad no tiene departamentos registrados (Ajustes → Departamentos).',
             'items' => $departments->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'parent' => null])->values()],
            ['key' => 'workstations', 'title' => 'Puestos', 'empty' => 'Los departamentos elegidos no tienen puestos registrados.',
             'items' => $workstations],
        ];

        return [$levels, $hosts, 'Anfitriones'];
    }

    /** Universidad: clasificaciones → carreras → materias → alumnos de esas carreras/materias. */
    private function universityCatalog(int $institutionId): array
    {
        $classifications = \App\Models\Users\CareerClassification::where('institution_id', $institutionId)
            ->orderBy('name')->get(['id', 'name']);

        $careers = \App\Models\Users\Career::where('institution_id', $institutionId)
            ->orderBy('name')->get(['id', 'name', 'career_classification_id']);

        // Carreras sin clasificación: se agrupan en "Sin clasificación" (id 0)
        $classificationItems = $classifications->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'parent' => null]);
        if ($careers->contains(fn ($c) => empty($c->career_classification_id))) {
            $classificationItems->push(['id' => 0, 'name' => 'Sin clasificación', 'parent' => null]);
        }
        $classOf = $careers->mapWithKeys(fn ($c) => [$c->id => (int) ($c->career_classification_id ?: 0)]);

        $materias = \App\Models\AdmonCont\Materia::whereIn('career_id', $careers->pluck('id'))
            ->orderBy('nombre')->get(['id', 'nombre', 'career_id']);

        // Alumnos (rol estudiante) cuya carrera está en la universidad
        $students = User::query()
            ->whereHas('roles', fn ($q) => $q->where('roles.name', 'estudiante'))
            ->join('academic_profiles', 'academic_profiles.user_id', '=', 'users.id')
            ->whereIn('academic_profiles.career_id', $careers->pluck('id'))
            ->orderBy('users.nombre')
            ->get(['users.id', 'users.nombre', 'users.apellido_paterno', 'users.apellido_materno', 'users.RFC', 'academic_profiles.career_id']);

        // Materias en las que cada alumno está inscrito (clases → alumnos)
        $materiasByStudent = DB::table('horario_clase_user')
            ->join('horario_clases', 'horario_clases.id', '=', 'horario_clase_user.horario_clase_id')
            ->whereIn('horario_clase_user.user_id', $students->pluck('id'))
            ->whereIn('horario_clases.materia_id', $materias->pluck('id'))
            ->get(['horario_clase_user.user_id', 'horario_clases.materia_id'])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('materia_id')->unique()->values()->all());

        $careerName = $careers->pluck('name', 'id');

        $people = $students->map(fn ($u) => [
            'id'    => $u->id,
            'name'  => trim("{$u->nombre} {$u->apellido_paterno} {$u->apellido_materno}"),
            'sub'   => trim(($careerName[$u->career_id] ?? '') . ' · ' . ($u->RFC ?: 'Sin matrícula/RFC'), ' ·'),
            'links' => [
                'classifications' => [$classOf[$u->career_id] ?? 0],
                'careers'         => [(int) $u->career_id],
                // Si el alumno aún no está inscrito en clases, se filtra solo por carrera
                'materias'        => $materiasByStudent[$u->id] ?? [],
            ],
        ])->values();

        $levels = [
            ['key' => 'classifications', 'title' => 'Clasificaciones', 'empty' => 'La universidad no tiene clasificaciones registradas.',
             'items' => $classificationItems->values()],
            ['key' => 'careers', 'title' => 'Carreras', 'empty' => 'Las clasificaciones elegidas no tienen carreras.',
             'items' => $careers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'parent' => (int) ($c->career_classification_id ?: 0)])->values()],
            ['key' => 'materias', 'title' => 'Materias', 'empty' => 'Las carreras elegidas no tienen materias.',
             'items' => $materias->map(fn ($m) => ['id' => $m->id, 'name' => $m->nombre, 'parent' => $m->career_id])->values()],
        ];

        return [$levels, $people, 'Alumnos'];
    }

    public function getGroupData($id)
    {
        $session = CourseSession::with(['groups.departments', 'groups.workstations'])->find($id);

        if (!$session || $session->groups->isEmpty()) {
            return response()->json([]);
        }

        $group = $session->groups->first();

        return response()->json([
            'departments' => $group->departments,
            'workstations' => $group->workstations,
        ]);
    }

public function groups($session)
{
    $session = CourseSession::findOrFail($session);

    $institutionId = session('active_institution_id');

    // =========================
    // DEPARTAMENTOS SOLO
    // DE LA UNIDAD ACTIVA
    // =========================
    $departments = Department::with('workstations')

        ->where('institution_id', $institutionId)

        ->get();

    // =========================
    // ANFITRIONES
    // SOLO DE ESA UNIDAD
    // =========================
    $hosts = User::where('institution_id', $institutionId)

        ->whereHas('roles', function($q) {

            $q->where('name', 'anfitrion');
        })

        ->get();

    $group = $session->groups()->first();

    return view('groups.index', [

        'session' => $session,

        'departments' => $departments,

        'hosts' => $hosts,

        'selectedDepartments' => $group
            ? $group->departments->pluck('id')->toArray()
            : [],

        'selectedWorkstations' => $group
            ? $group->workstations->pluck('id')->toArray()
            : [],

        'selectedHosts' => $group && method_exists($group, 'hosts')
            ? $group->hosts->pluck('id')->toArray()
            : [],
    ]);
}
public function storeQrAttendance(Request $request, CourseSession $session)
{
    $request->validate([
        'rfc' => 'required|string',
    ]);

    $rfc = strtoupper(trim($request->rfc));
    $now = now();

    DB::table('course_session_attendances')->updateOrInsert(
        [
            'course_session_id' => $session->id,
            'rfc' => $rfc,
        ],
        [
            'attended_at' => $now,
            'updated_at' => $now,
            'created_at' => $now,
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Asistencia registrada correctamente',
        'attended_at' => $now->format('H:i:s'),
        'attended_date' => $now->format('d/m/Y'),
        'attended_full' => $now->format('d/m/Y H:i'),
    ]);
}

/**
 * Panel lateral "Asignar Usuarios" (botón verde de Horarios, igual que en virtual).
 * Lista los anfitriones/alumnos de la unidad con la marca de si ya están asignados al horario.
 */
public function sessionUsers(CourseSession $session)
{
    $group = $session->groups()->with('participants:id')->first();
    $catalog = $this->buildCatalog(null, $group->institution_id ?? null);
    $assigned = $group ? $group->participants->pluck('id')->map(fn ($id) => (int) $id)->all() : [];

    return response()->json([
        'success'            => true,
        'attendance_enabled' => (bool) $session->attendance_enabled,
        'users'              => $this->usersForPanel($catalog['people'], $assigned),
    ]);
}

/** Lista para el panel "Asignar Usuarios": asignados primero. */
public function usersForPanel($people, array $assigned)
{
    return collect($people)->map(fn ($p) => [
        'id'       => $p['id'],
        'nombre'   => $p['name'],
        'detalle'  => $p['sub'],
        'assigned' => in_array((int) $p['id'], $assigned, true),
    ])->sortByDesc('assigned')->values();
}

/** Asigna o quita un usuario del horario (lista de participantes del grupo del horario). */
public function toggleSessionUser(Request $request, CourseSession $session)
{
    $data = $request->validate([
        'user_id'  => 'required|integer|exists:users,id',
        'assigned' => 'required|boolean',
    ]);

    $group = $session->groups()->first();
    if (! $group) {
        $group = Group::create([
            'type'           => 'cerrado',
            'institution_id' => (int) session('active_institution_id'),
        ]);
        $group->sessions()->attach($session->id);
    }

    if ($data['assigned']) {
        $group->participants()->syncWithoutDetaching([$data['user_id']]);
    } else {
        $group->participants()->detach($data['user_id']);
    }

    return response()->json([
        'success' => true,
        'total'   => $group->participants()->count(),
    ]);
}
}
