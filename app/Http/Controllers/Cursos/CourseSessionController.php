<?php

namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cursos\Course;
use App\Models\Cursos\CourseSession;
use App\Models\Users\Department;
use Carbon\Carbon;
use App\Models\Group;

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

        return view('layouts.Cursos.sessions.index', compact(
            'course',
            'sessions',
            'departments',
            'groups'
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
}
