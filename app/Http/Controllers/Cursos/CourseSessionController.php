<?php

namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cursos\Course;
use App\Models\Cursos\CourseSession;

class CourseSessionController extends Controller
{
    public function index(Course $course)
    {
        if (!in_array($course->modality, ['presencial', 'hibrida'])) {
            abort(403, 'Este curso no permite gestión de horarios');
        }

        $sessions = $course->sessions()->orderBy('date')->get();

        return view('layouts.Cursos.sessions.index', compact('course', 'sessions'));
    }
   public function destroy($courseId, $sessionId)
    {
    $session = CourseSession::where('course_id', $courseId)
        ->where('id', $sessionId)
        ->firstOrFail();

    $session->delete();

    return redirect()->back()->with('success', 'Horario eliminado correctamente');
    }

public function toggle(Course $course, CourseSession $session)
{
    // Cambiar estado del horario
    $session->attendance_enabled = !$session->attendance_enabled;
    $session->save();

    // Revisar si hay horarios activos
    $activeSessions = $course->sessions()
        ->where('attendance_enabled', true)
        ->count();

    if ($activeSessions == 0) {
        $course->active = false;
    } else {
        $course->active = true;
    }

    $course->save();

    return redirect()->back()->with('success', 'Estado del horario actualizado');
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

        CourseSession::create([
            'course_id' => $course->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'attendance_enabled' => false,
            'qr_token' => null,
        ]);

        return redirect()
            ->route('courses.sessions.index', $course->id)
            ->with('success', 'Horario creado correctamente');
    }

}
