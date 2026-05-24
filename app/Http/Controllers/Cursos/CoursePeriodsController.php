<?php

namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use App\Models\Cursos\Course;
use App\Models\Cursos\CoursePeriod;
use App\Models\Users\User;
use App\Models\Users\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\ExternalApiService;
use Barryvdh\DomPDF\Facade\Pdf;


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
        $validated['attendance_enabled'] = true;

        $course->periods()->create($validated);

        return redirect()->back()->with('success', 'Período creado exitosamente');
    }

    public function update(Request $request, Course $course, CoursePeriod $period)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $period->update($validated);

        return redirect()->back()->with('success', 'Vigencia actualizada correctamente');
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

    // Todos los periodos del curso
    $periods = $course->periods()
        ->orderBy('start_date', 'desc')
        ->get();

    // Usuarios asignados a TODOS los periodos del curso
    $rows = DB::table('period_user')
        ->join('course_periods', 'course_periods.id', '=', 'period_user.period_id')
        ->join('users', 'users.id', '=', 'period_user.user_id')
        ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
        ->leftJoin('workstations', 'workstations.id', '=', 'users.workstation_id')
        ->where('period_user.course_id', $course->id)
        ->select(
            'period_user.period_id',
            'course_periods.start_date',
            'course_periods.end_date',
            'course_periods.attendance_enabled',
            'users.id as user_id',
            'users.nombre',
            'users.apellido_paterno',
            'users.apellido_materno',
            'users.RFC',
            'departments.name as department_name',
            'workstations.name as workstation_name'
        )
        ->orderBy('course_periods.start_date', 'desc')
        ->orderBy('users.nombre')
        ->get();

    $attendances = $rows->map(function ($row) {
        return [
            'id' => $row->user_id,

            'nombre' => trim(
                ($row->nombre ?? '') . ' ' .
                ($row->apellido_paterno ?? '') . ' ' .
                ($row->apellido_materno ?? '')
            ),

            'rfc' => $row->RFC ?? 'N/A',
            'department' => $row->department_name ?? 'Sin departamento',
            'puesto' => $row->workstation_name ?? 'Sin puesto',

            'period_id' => $row->period_id,
            'period_name' =>
                \Carbon\Carbon::parse($row->start_date)->format('d/m/Y') .
                ' - ' .
                \Carbon\Carbon::parse($row->end_date)->format('d/m/Y'),

            'started_at' => $row->start_date,
            'completed_at' => $row->end_date,

            'progress' => 0,
            'final_score' => null,
            'attendance_enabled' => $row->attendance_enabled,
        ];
    });

    return view('layouts.Cursos.attendance', compact(
        'course',
        'attendances',
        'periods',
        'period'
    ));
}

public function attendancePdf(Request $request, Course $course)
{
    $periodId = $request->query('period');

    if (!$periodId) {
        abort(404, 'No se seleccionó un período.');
    }

    $period = CoursePeriod::where('course_id', $course->id)
        ->where('id', $periodId)
        ->firstOrFail();

    $rows = DB::table('period_user')
        ->join('users', 'users.id', '=', 'period_user.user_id')
        ->leftJoin('workstations', 'workstations.id', '=', 'users.workstation_id')
        ->leftJoin('completions', function ($join) use ($course) {
            $join->on('completions.user_id', '=', 'users.id')
                ->where('completions.completable_type', '=', \App\Models\Cursos\Course::class)
                ->where('completions.completable_id', '=', $course->id);
        })
        ->where('period_user.course_id', $course->id)
        ->where('period_user.period_id', $period->id)
        ->select(
            'users.id',
            'users.nombre',
            'users.apellido_paterno',
            'users.apellido_materno',
            'users.RFC',
            'workstations.name as puesto',
            'completions.score as final_score'
        )
        ->orderBy('users.nombre')
        ->get();

    $attendances = $rows->map(function ($user) use ($period) {
        return [
            'nombre' => trim(
                ($user->nombre ?? '') . ' ' .
                ($user->apellido_paterno ?? '') . ' ' .
                ($user->apellido_materno ?? '')
            ),
            'puesto' => $user->puesto ?? 'Sin puesto',
            'rfc' => $user->RFC ?? 'N/A',
            'started_at' => $period->start_date,
            'completed_at' => $period->end_date,
            'final_score' => $user->final_score,
        ];
    });

    $instructor = optional($course->instructor)->nombre ?? 'N/A';
    $fecha_inicio = $period->start_date->format('d/m/Y');
    $fecha_fin = $period->end_date->format('d/m/Y');
    $institution_name = session('active_institution_name') ?? 'Mundo Imperial';

    $pdf = Pdf::loadView('layouts.Cursos.attendance_pdf', compact(
        'course',
        'period',
        'attendances',
        'instructor',
        'fecha_inicio',
        'fecha_fin',
        'institution_name'
    ));

    return $pdf->download(
        'asistencias-virtual-' . $course->id . '-periodo-' . $period->id . '.pdf'
    );
}

public function toggle($courseId, $periodId)
{
    $period = \App\Models\Cursos\CoursePeriod::findOrFail($periodId);

    $period->attendance_enabled = !$period->attendance_enabled;

    $period->save();

    return back()->with('success', 'Estado actualizado correctamente');
}
    public function usersIndex(Course $course, CoursePeriod $period)
{
    $departments = Department::where('institution_id', session('active_institution_id'))
        ->with('workstations')
        ->get();

    $selectedDepartments = [];
    $selectedWorkstations = [];

    $selectedHosts = DB::table('period_user')
        ->where('period_id', $period->id)
        ->where('course_id', $course->id)
        ->pluck('user_id')
        ->toArray();

    return view('layouts.Cursos.periods.users', compact(
        'course',
        'period',
        'departments',
        'selectedDepartments',
        'selectedWorkstations',
        'selectedHosts'
    ));
}
public function usersStore(Request $request)
{
    $request->validate([
        'course_id' => 'required|exists:courses,id',
        'period_id' => 'required|exists:course_periods,id',
        'hosts' => 'nullable|array',
    ]);

    DB::table('period_user')
        ->where('course_id', $request->course_id)
        ->where('period_id', $request->period_id)
        ->delete();

    foreach ($request->hosts ?? [] as $host) {
        DB::table('period_user')->insert([
            'course_id' => $request->course_id,
            'period_id' => $request->period_id,
            'user_id' => $host,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return redirect()
        ->route('courses.periods.users.index', [$request->course_id, $request->period_id])
        ->with('success', 'Usuarios asignados correctamente.');
}

}
