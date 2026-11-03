<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Users\Department;
use Illuminate\Http\Request;
use App\Models\Cursos\CourseSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class GroupsController extends Controller
{
    public function index()
{
    $groups = Group::all();
    $departments = Department::with('workstations')->get();

    return view('groups.index', compact('groups', 'departments'));
}
    public function store(Request $request)
    {
        $session = CourseSession::findOrFail($request->session_id);

        $group = $session->groups()->first();

        if (!$group) {
            $group = Group::create([
                'type' => $request->type,
                'min_participants' => $request->min_participants,
                'max_participants' => $request->max_participants,
                'institution_id' => session('active_institution_id'),
            ]);

            $group->sessions()->attach($session->id);
        }

        $group->departments()->sync($request->departments);
        $group->workstations()->sync($request->workstations);
    }

    public function destroy(Group $group)
    {
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

    $group->update($request->only([
        'name',
        'type',
        'min_participants',
        'max_participants'
    ]));

    return redirect()->route('groups.index')
        ->with('success', 'Grupo actualizado correctamente');
    }

    public function addParticipants(Request $request)
    {
    //  Obtener sesión
    $session = CourseSession::findOrFail($request->session_id);

    //  Obtener grupo ligado a ese horario
    $group = $session->groups()->first();

    if (!$group) {
        return back()->withErrors(['error' => 'Primero configura el grupo']);
    }

    // Guardar participantes
    $group->participants()->syncWithoutDetaching($request->input('users', []));
        return back()->with('success', 'Participantes agregados');
    }

    /**
     * Descarga en PDF la lista de participantes de un horario, con su asistencia.
     * (Se había perdido en la migración del módulo de Cursos.)
     */
    public function exportPdf($sessionId)
    {
        $session = CourseSession::with([
            'course',
            'groups.hosts.department',
            'groups.hosts.workstation',
        ])->findOrFail($sessionId);

        $group = $session->groups->first();
        $hosts = $group ? $group->hosts : collect();

        $attendanceMap = DB::table('course_session_attendances')
            ->where('course_session_id', $session->id)
            ->get()
            ->keyBy(fn ($item) => strtoupper(trim($item->rfc)));

        // Universidad: en lugar de "Puesto" se muestra la carrera del alumno
        $institution = \App\Models\Users\Institution::find(
            ($group->institution_id ?? null) ?: ($session->course->institution_id ?? null) ?: session('active_institution_id')
        );
        $esUniversidad = app(\App\Http\Controllers\Cursos\CourseSessionController::class)->isUniversity($institution);
        $carreraDe = $esUniversidad
            ? DB::table('academic_profiles')
                ->join('careers', 'careers.id', '=', 'academic_profiles.career_id')
                ->whereIn('academic_profiles.user_id', $hosts->pluck('id'))
                ->pluck('careers.name', 'academic_profiles.user_id')
            : collect();

        return Pdf::loadView('pdf.group-list', compact('session', 'hosts', 'attendanceMap', 'esUniversidad', 'carreraDe'))
            ->download('lista-participantes.pdf');
    }
}
