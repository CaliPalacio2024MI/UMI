<?php

namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use App\Models\Cursos\Course;
use App\Models\Cursos\CoursePeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\ExternalApiService;

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


public function users($courseId, $periodId)
{
    try {
        $baseUrl = env('EXTERNAL_API_BASE_URL');
        $endpoint = "/api/external/propiedades/1/anfitriones";

        $service = new ExternalApiService(
            env('EXTERNAL_API_ACCESS_KEY'),
            env('EXTERNAL_API_SECRET_KEY')
        );

        $response = $service->execute($baseUrl . $endpoint);

        if (!isset($response['success']) || !$response['success']) {
            throw new \Exception($response['message'] ?? 'Error en API');
        }

        $users = collect($response['data'])->map(function ($u) {
            return [
                'id' => $u['no_anfitrion'],
                'nombre' => trim(
                    ($u['nombre'] ?? '') . ' ' .
                    ($u['primer_apellido'] ?? '') . ' ' .
                    ($u['segundo_apellido'] ?? '')
                ),
                'assigned' => false
            ];
        });

        return response()->json([
            'success' => true,
            'users' => $users->values()
        ]);

    } catch (\Exception $e) {
        \Log::error('Error cargando usuarios:', [
            'course_id' => $courseId,
            'period_id' => $periodId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

public function toggleUser(Request $request, $courseId, $periodId)
{
    return response()->json([
        'success' => true
    ]);
}

/**
 * Asistencia del período específico - CON API EXTERNA
 */
public function attendance(Course $course, CoursePeriod $period)
{
    $this->authorize('update', $course);
    
    // ✅ Obtener anfitriones asignados a este período
    $assignedAnfitriones = DB::table('period_user')
        ->where('period_user.period_id', $period->id)
        ->where('period_user.course_id', $course->id)
        ->pluck('no_anfitrion')
        ->toArray();
    
    if (empty($assignedAnfitriones)) {
        $attendances = collect([]);
    } else {
        // ✅ Consumir API para obtener datos de anfitriones
        $propiedadId = 1;
        $apiUrl = url("/external-data?endpoint=/api/external/propiedades/{$propiedadId}/anfitriones");
        $response = Http::timeout(30)->get($apiUrl);
        
        if ($response->successful()) {
            $apiData = $response->json();
            $allAnfitriones = collect($apiData['data'] ?? []);
            
            // ✅ Filtrar solo los asignados a este período
            $filteredAnfitriones = $allAnfitriones->whereIn('no_anfitrion', $assignedAnfitriones);
            
            $attendances = $filteredAnfitriones->map(function($anfitrion) use ($course, $period) {
                // Aquí puedes buscar progreso si lo guardas en alguna tabla
                return [
                    'id' => $anfitrion['no_anfitrion'],
                    'nombre' => trim(($anfitrion['nombre'] ?? '') . ' ' . ($anfitrion['primer_apellido'] ?? '') . ' ' . ($anfitrion['segundo_apellido'] ?? '')),
                    'email' => $anfitrion['rfc'] ?? 'N/A',
                    'started_at' => null, // Por definir
                    'completed_at' => null, // Por definir
                    'progress' => 0, // Por definir
                    'final_score' => null, // Por definir
                    'status' => 'En progreso',
                    'period_id' => $period->id,
                    'period_name' => $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y')
                ];
            });
        } else {
            $attendances = collect([]);
        }
    }
    
    $periods = collect([$period]);
    
    return view('layouts.Cursos.attendance', compact('course', 'attendances', 'periods', 'period'));
}
}