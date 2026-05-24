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

    DB::table('course_session_attendances')->updateOrInsert(
        [
            'course_session_id' => $session->id,
            'rfc' => $rfc,
        ],
        [
            'attended_at' => now(),
            'updated_at' => now(),
            'created_at' => now(),
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Asistencia registrada correctamente',
        'attended_at' => now()->format('H:i:s'),
    ]);
}
}
