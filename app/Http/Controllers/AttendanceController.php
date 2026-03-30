<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Course;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function scanQr(Request $request)
    {
        $qr = $request->qr_code;

        $course = Course::find($qr);

        if(!$course){
            return response()->json([
                'error' => 'Curso no válido'
            ]);
        }

        $now = Carbon::now();

        $session = $course->sessions()
            ->where('date', $now->toDateString())
            ->where('start_time', '<=', $now->toTimeString())
            ->where('end_time', '>=', $now->toTimeString())
            ->where('attendance_enabled', true)
            ->first();

        if(!$session){
            return response()->json([
                'error' => 'No hay clase activa en este momento'
            ]);
        }

        Attendance::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'session_id' => $session->id
        ]);

        return response()->json([
            'success' => 'Asistencia registrada correctamente'
        ]);
    }
}
