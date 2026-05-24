<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Users\Department;
use App\Models\Users\User;
use Illuminate\Http\Request;
use App\Models\Cursos\CourseSession;
use App\Services\ExternalApiService;
use App\Models\Users\Institution;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class GroupsController extends Controller
{
    public function index()
    {
        // Redirigir porque no usamos esta vista
        return redirect()->route('courses.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:course_sessions,id',
            'departments' => 'nullable|array',
            'workstations' => 'nullable|array',
            'hosts' => 'nullable|array'
        ]);

        $session = CourseSession::findOrFail($request->session_id);

        $group = $session->groups()->first();

        if (!$group) {
            $group = Group::create([
                'type' => $request->type ?? 'abierto',
                'min_participants' => $request->min_participants ?? 1,
                'max_participants' => $request->max_participants ?? 100,
                'institution_id' => session('active_institution_id'),
            ]);

            $group->sessions()->attach($session->id);
        }

        if ($request->has('departments')) {
            $group->departments()->sync($request->departments);
        } else {
            $group->departments()->sync([]);
        }

        if ($request->has('workstations')) {
            $group->workstations()->sync($request->workstations);
        } else {
            $group->workstations()->sync([]);
        }

        if ($request->has('hosts')) {

            $this->syncHosts($group, $request->hosts);
        } else {
            $this->syncHosts($group, []);
        }

        return redirect()->route('courses.sessions.index', $session->course_id)
            ->with('success', 'Configuración del grupo guardada correctamente');
    }

public function getParticipantsByFilters(Request $request)
{
    try {

        $departmentIds = $request->departments ?? [];
        $workstationIds = $request->workstations ?? [];

        if (empty($departmentIds) || empty($workstationIds)) {
            return response()->json([
                'anfitriones' => []
            ]);
        }

        $users = User::query()

            ->whereIn('department_id', $departmentIds)

            ->whereIn('workstation_id', $workstationIds)

            // EXCLUIR SOLO MASTERS
            ->whereDoesntHave('roles', function ($query) {
                $query->whereRaw('LOWER(roles.name) = ?', ['master']);
            })

            ->with([
                'department:id,name',
                'workstation:id,name',
                'roles'
            ])

            ->orderBy('nombre')

            ->get();

        $formattedUsers = $users->map(function ($user) {

            return [
                'id' => $user->id,

                'name' => trim(
                    ($user->nombre ?? '') . ' ' .
                    ($user->apellido_paterno ?? '') . ' ' .
                    ($user->apellido_materno ?? '')
                ),

                'RFC' => $user->RFC ?? null,

                'department_name' =>
                    optional($user->department)->name
                    ?? 'Sin departamento',

                'workstation_name' =>
                    optional($user->workstation)->name
                    ?? 'Sin puesto',
            ];

        })->values();

        return response()->json([
            'anfitriones' => $formattedUsers
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ], 500);

    }
}
    public function destroy(Group $group)
    {
        $group->departments()->detach();
        $group->workstations()->detach();
        $group->participants()->detach();
        $group->sessions()->detach();

        if (method_exists($group, 'hosts')) {
            $group->hosts()->detach();
        }

        $group->delete();

        return back()->with('success', 'Grupo eliminado correctamente');
    }

    public function edit(Group $group)
    {
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:abierto,cerrado',
            'min_participants' => 'required|integer',
            'max_participants' => 'required|integer',
        ]);

        $group->update($request->only(['name', 'type', 'min_participants', 'max_participants']));

        return redirect()->route('groups.index')->with('success', 'Grupo actualizado correctamente');
    }

    public function addParticipants(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:course_sessions,id',
            'users' => 'required|array',
        ]);

        $session = CourseSession::findOrFail($request->session_id);
        $group = $session->groups()->first();

        if (!$group) {
            return response()->json(['error' => 'Primero configura el grupo'], 400);
        }

        $group->participants()->syncWithoutDetaching($request->users);

        return response()->json(['success' => 'Participantes agregados correctamente']);
    }

    public function removeParticipants(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:course_sessions,id',
            'users' => 'required|array',
        ]);

        $session = CourseSession::findOrFail($request->session_id);
        $group = $session->groups()->first();

        if (!$group) {
            return response()->json(['error' => 'Grupo no encontrado'], 400);
        }

        $group->participants()->detach($request->users);

        return response()->json(['success' => 'Participantes removidos correctamente']);
    }

    public function show($groupId, $sessionId = null)
    {
        $group = Group::with(['departments', 'workstations', 'participants', 'sessions'])->findOrFail($groupId);

        if (!$sessionId && $group->sessions->isNotEmpty()) {
            $sessionId = $group->sessions->first()->id;
        }

        $session = CourseSession::findOrFail($sessionId);

        $hosts = collect([]);
        if (method_exists($group, 'hosts')) {
            $hosts = $group->hosts;
        } elseif ($group->hosts_json) {
            $hostIds = json_decode($group->hosts_json, true);
            $hosts = User::whereIn('id', $hostIds)->get();
        }

        return view('groups.show', compact('group', 'session', 'hosts'));
    }

    private function syncHosts($group, array $hostIds)
    {
        if (method_exists($group, 'hosts')) {
            $group->hosts()->sync($hostIds);
        } else {
            $group->hosts_json = json_encode($hostIds);
            $group->save();
        }
    }
    public function exportPdf($sessionId)
{
    $session = CourseSession::with([
        'course',
        'groups.hosts.department',
        'groups.hosts.workstation'
    ])->findOrFail($sessionId);

    $group = $session->groups->first();

    $hosts = collect([]);

    if ($group) {

        if (method_exists($group, 'hosts')) {

            $hosts = $group->hosts;

        } elseif ($group->hosts_json) {

            $hostIds = json_decode($group->hosts_json, true) ?? [];

            $hosts = \App\Models\Users\User::whereIn('id', $hostIds)
                ->with(['department', 'workstation'])
                ->get();
        }
    }
    $attendanceMap = DB::table('course_session_attendances')
        ->where('course_session_id', $session->id)
        ->get()
        ->keyBy(function ($item) {
           return strtoupper(trim($item->rfc));
        });

    $pdf = Pdf::loadView(
        'pdf.group-list',
        compact(
            'session',
            'hosts',
            'attendanceMap'
        )
    );

    return $pdf->download(
        'lista-participantes.pdf'
    );
}
}
