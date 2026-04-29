<?php

namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use App\Models\Cursos\Course;
use App\Models\Cursos\CoursePeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoursePeriodsController extends Controller
{
    /**
     * Mostrar períodos del curso
     */
    public function index(Course $course)
    {
        $this->authorize('update', $course);
        
        $periods = $course->periods()->orderBy('start_date', 'desc')->get();
        
        return view('layouts.Cursos.periods.index', compact('course', 'periods'));
    }

    /**
     * Crear nuevo período
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ]);
        
        $validated['is_active'] = true;
        
        $course->periods()->create($validated);
        
        return redirect()->back()->with('success', 'Período creado exitosamente');
    }

    /**
     * Eliminar período
     */
    public function destroy(Course $course, CoursePeriod $period)
    {
        $this->authorize('update', $course);
        
        $period->delete();
        
        return redirect()->back()->with('success', 'Período eliminado exitosamente');
    }

   /**
 * Mostrar usuarios del período (para asignar)
 */
public function users(Course $course, CoursePeriod $period)
{
    try {
        // ✅ Test ultra simple
        return response()->json([
            'success' => true,
            'users' => [
                [
                    'id' => 1,
                    'nombre' => 'Usuario de Prueba',
                    'email' => 'test@test.com',
                    'assigned' => false
                ]
            ],
            'debug' => [
                'course_id' => $course->id,
                'period_id' => $period->id
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}

    /**
     * Asignar/desasignar usuario al período
     */
    public function toggleUser(Request $request, Course $course, CoursePeriod $period)
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'assigned' => 'required|boolean'
        ]);
        
        if ($validated['assigned']) {
            // Asignar usuario
            DB::table('period_user')->insertOrIgnore([
                'period_id' => $period->id,
                'user_id' => $validated['user_id'],
                'course_id' => $course->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            // Desasignar usuario
            DB::table('period_user')
                ->where('period_id', $period->id)
                ->where('user_id', $validated['user_id'])
                ->where('course_id', $course->id)
                ->delete();
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Asistencia del período específico
     */
    /**
 * Asistencia del período específico
 */
public function attendance(Course $course, CoursePeriod $period)
{
    $this->authorize('update', $course);
    
    // Obtener solo usuarios de este período
    $attendances = DB::table('period_user')
        ->where('period_user.period_id', $period->id) // ✅ Especificar tabla
        ->where('period_user.course_id', $course->id) // ✅ Especificar tabla
        ->join('users', 'period_user.user_id', '=', 'users.id')
        ->leftJoin('course_user', function($join) use ($course) {
            $join->on('users.id', '=', 'course_user.user_id')
                 ->where('course_user.course_id', '=', $course->id);
        })
        ->select(
            'users.id',
            'users.nombre',
            'users.apellido_paterno',
            'users.apellido_materno',
            'users.email',
            'course_user.started_at',
            'course_user.completed_at',
            'course_user.progress'
        )
        ->get()
        ->map(function($user) use ($course, $period) {
            $finalScore = null;
            $completedAt = null;
            
            $finalExam = $course->finalExam;
            if ($finalExam) {
                $completion = DB::table('completions')
                    ->where('user_id', $user->id)
                    ->where('completable_type', 'App\\Models\\Cursos\\Activities')
                    ->where('completable_id', $finalExam->id)
                    ->first();
                
                if ($completion) {
                    $finalScore = $completion->score;
                    $completedAt = $completion->created_at;
                }
            }
            
            $progress = $finalScore !== null ? 100 : ($user->progress ?? 0);
            $status = $finalScore !== null ? 'Completado' : 'En progreso';
            
            return [
                'id' => $user->id,
                'nombre' => trim(($user->nombre ?? '') . ' ' . ($user->apellido_paterno ?? '') . ' ' . ($user->apellido_materno ?? '')),
                'email' => $user->email,
                'started_at' => $user->started_at,
                'completed_at' => $completedAt,
                'progress' => $progress,
                'final_score' => $finalScore,
                'status' => $status,
                'period_id' => $period->id,
                'period_name' => $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y')
            ];
        });
    
    $periods = collect([$period]); // Solo este período
    
    return view('layouts.Cursos.attendance', compact('course', 'attendances', 'periods', 'period'));
}
}