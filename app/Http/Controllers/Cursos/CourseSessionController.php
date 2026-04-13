<?php

namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cursos\Course;
use App\Models\Cursos\CourseSession;
use App\Models\Users\Department;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Users\User;



class CourseSessionController extends Controller
{
    public function index(Course $course)
    {
        if (!in_array($course->modality, ['presencial', 'hibrida'])) {
            abort(403, 'Este curso no permite gestión de horarios');
        }

        // sesiones
        $sessions = $course->sessions()->orderBy('date')->get();
        // departamentos
        $departments = Department::where('institution_id', session('active_institution_id'))->get();
        // GRUPOS DEL CURSO
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

        return back()->with(
        'success',
        'Grupo asignado'
        );
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

        $activeSessions = $course->sessions()
            ->where('attendance_enabled', true)
            ->count();

        $course->active = $activeSessions > 0;
        $course->save();

        return back()->with('success', 'Estado del horario actualizado');
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
        ]);

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        if ($end <= $start) {
            return back()->withErrors([
                'error' => 'La hora fin debe ser mayor a la hora inicio'
            ]);
        }


        // VALIDACIÓN EXACTA
        $expectedEnd = $start->copy()->addHours($course->hours);

        if (!$end->equalTo($expectedEnd)) {
            return back()->withErrors([
                'error' => 'La hora fin debe ser exactamente ' . $expectedEnd->format('H:i')
            ]);
        }


        $session->update([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return back()->with('success', 'Horario actualizado correctamente');
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        if ($end <= $start) {
            return back()->withErrors([
                'error' => 'La hora fin debe ser mayor a la hora inicio'
            ]);
        }


        // VALIDACIÓN EXACTA
        $expectedEnd = $start->copy()->addHours($course->hours);

        if (!$end->equalTo($expectedEnd)) {
            return back()->withErrors([
                'error' => 'La hora fin debe ser exactamente ' . $expectedEnd->format('H:i')
            ]);
        }

        CourseSession::create([
            'course_id' => $course->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'attendance_enabled' => false,
        ]);

        return back()->with('success', 'Horario creado correctamente');
    }

    public function storeGroup(Request $request)
    {
        $session = CourseSession::find($request->session_id);

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

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

        return back()->with('success', 'Grupo asignado correctamente al horario');
    }
    public function getGroupData($id)
    {
        $session = CourseSession::with([
            'groups.departments',
            'groups.workstations'
        ])->find($id);

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

    $departments = Department::with('workstations')
        ->where('institution_id', session('active_institution_id'))
        ->get();

    $users = User::where('institution_id', session('active_institution_id'))
        ->get();

    $group = $session->groups()->first();

    return view('groups.index', [
        'session' => $session,
        'departments' => $departments,
        'users' => $users,
        'selectedDepartments' => $group ? $group->departments->pluck('id')->toArray() : [],
        'selectedWorkstations' => $group ? $group->workstations->pluck('id')->toArray() : [],
        'selectedUsers' => $group ? $group->users->pluck('id')->toArray() : [],
    ]);
}

}
