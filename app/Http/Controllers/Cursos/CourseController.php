<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCourseRequest; 
use App\Models\Cursos\Course;
use App\Models\Users\Institution;
use App\Models\Schedule;
use App\Models\Users\Department;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse; // <-- Importante
use App\Models\Cursos\Activities;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\TopicTemplate;
use App\Models\SubtopicTemplate;
use App\Models\Cursos\Topics;
use App\Models\Cursos\Subtopic;
use App\Models\Cursos\CoursePeriod;

class CourseController extends Controller
{
    use AuthorizesRequests;
    /**
     * Mostrar lista de cursos
     */
    public function index(): View
    {
        $activeInstitutionId = session('active_institution_id');
        $activeRoleName = session('active_role_name');

        // CORREGIDO: Usar la variable correcta y filtrada
        $course = Course::with('instructor', 'institution')
            ->where('institution_id', $activeInstitutionId)
            ->latest()
            ->paginate(12);

        // Para roles específicos, mostrar información adicional
        $canManageCourses = in_array($activeRoleName, ['master', 'docente']);

        return view('layouts.Cursos.index', compact('course', 'canManageCourses'));
    }

    /**
     * Mostrar formulario de creación de curso
     */
    public function create(): View
    {
         // Obtenemos el ID de la institución de la sesión actual del usuario
        $institutionId = session('active_institution_id');

        // Cargamos la institución actual con sus relaciones (carreras, departamentos, etc.)
        $currentInstitution = Institution::with(['careers', 'departments.workstations'])->find($institutionId);

        $departmentWorkstationsMap = [];
        if ($currentInstitution->departments) {
            $departmentWorkstationsMap = $currentInstitution->departments->mapWithKeys(function ($department) {
                return [$department->id => $department->workstations->toArray()];
            });
        }

        //Para traer los temas de la biblioteca
        $templates = TopicTemplate::orderBy('title')->get();
        // Pasamos solo la institución actual a la vista.
        return view('layouts.Cursos.create', compact('currentInstitution', 'departmentWorkstationsMap', 'templates'));

         // Traer todos los horarios registrados
        $schedules = Schedule::all();

        // Enviar también $schedules a la vista
        return view('layouts.Cursos.create', compact(
        'currentInstitution',
        'departmentWorkstationsMap',
        'templates',
        'schedules'
     ));
    }

    /**
     * Guardar un nuevo curso
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $activeInstitutionId = session('active_institution_id');
        $activeRoleName = session('active_role_name');

        // VALIDACIÓN DE SEGURIDAD: Verificar que la institución proporcionada coincida con la activa
        $validatedData = $request->validated();

        // SEGURIDAD: Forzar que el curso se cree en la institución activa
        if ($validatedData['institution_id'] != $activeInstitutionId) {
            Log::warning('Intento de crear curso en institución no autorizada', [
                'user_id' => Auth::id(),
                'active_institution_id' => $activeInstitutionId,
                'attempted_institution_id' => $validatedData['institution_id'],
                'ip' => $request->ip()
            ]);

            return redirect()->back()->withErrors([
                'institution_id' => 'No tienes autorización para crear cursos en esa institución.'
            ])->withInput();
        }

        $courseData = $validatedData;
        $courseData['instructor_id'] = Auth::id();

        // Manejo de imagen
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('courses', 'public');
            $courseData['image'] = $path;
        }
        // Material de apoyo
        if ($request->hasFile('guide_material')) {
            $path = $request->file('guide_material')->store('courses/guides', 'public');
            $courseData['guide_material_path'] = $path;
        }
        // 2. Manejo de subida de archivos
        if ($request->hasFile('cert_bg_image')) {
            // Guardar en storage/app/public/certificates/backgrounds
            $path = $request->file('cert_bg_image')->store('certificates/backgrounds', 'public');
            $courseData['cert_bg_path'] = $path;
        }

        if ($request->hasFile('cert_sig_1_image')) {
            $path = $request->file('cert_sig_1_image')->store('certificates/signatures', 'public');
            $courseData['cert_sig_1_path'] = $path;
        }

        if ($request->hasFile('cert_sig_2_image')) {
            $path = $request->file('cert_sig_2_image')->store('certificates/signatures', 'public');
            $courseData['cert_sig_2_path'] = $path;
        }

        $course = Course::create($courseData);

        //Copiar plantillas de tema
        if ($request->has('template_topics')) {

            $selectedTemplates = TopicTemplate::whereIn('id', $request->template_topics)->get();

            foreach ($selectedTemplates as $template){
                Topics::create(['course_id' => $course->id, 'title' => $template->title, 'description' => $template->description,]);
            }
        }

        //Copiar plantillas de subtema
        if ($request->has('template_subtopics')) {

            $selectedTemplates = TopicTemplate::whereIn('id', $request->template_topics)->get();

            foreach ($selectedTemplates as $template){
                Subtopic::create(['course_id' => $course->id, 'title' => $template->title, 'description' => $template->description,]);
            }
        }

        if ($request->filled('career_id')) {
            // sync() adjunta el ID y quita cualquier otro que no esté en el array
            $course->careers()->sync([$request->career_id]);
        } 
         elseif ($request->filled('department_ids')) {

           // Guardar múltiples departamentos
           $course->departments()->sync($request->department_ids);

          // Guardar múltiples puestos (opcional)
          if ($request->filled('workstation_ids')) {

             $cleanIds = array_filter($request->workstation_ids);

            if (!empty($cleanIds)) {
             $course->workstations()->sync($cleanIds);
        }
    }

}

        Log::info('Curso creado exitosamente', [
            'course_id' => $course->id,
            'instructor_id' => Auth::id(),
            'institution_id' => $activeInstitutionId,
            'role' => $activeRoleName
        ]);

        if ($course->modality === 'presencial') {
            return redirect()->route('Cursos.index')
                ->with('success', 'Curso creado exitosamente.');
        }

        if ($course->modality === 'hibrida') {
            return redirect()->route('Cursos.index')
                ->with('success', 'Curso creado exitosamente.');
        }

        return redirect()->route('course.topic.create', ['course' => $course->id])
            ->with('success', 'Curso creado exitosamente.');
    }

    /**
     * Mostrar detalles de un curso
     */
public function show(Course $course)
{
    $course->load('topics.subtopics.activities', 'topics.activities', 'finalExam');

    $user = Auth::user();
    $departments = Department::with('workstations')->get();

    $progress = 0;
    $isEnrolled = false;
    $finalExamData = null;
    $finalExamActivity = null;
    $userCompletions = collect();
    $totalItems = 0;

    if ($user) {

        $isEnrolled = $user->courses()
    ->where('course_id', $course->id)
    ->exists();

        if (!$isEnrolled) {
    // ✅ Buscar período activo HOY
    $currentPeriod = $course->periods()
        ->whereDate('start_date', '<=', now())
        ->whereDate('end_date', '>=', now())
        ->first();
    
    \Log::info('🔍 Período encontrado para inscripción:', [
        'course_id' => $course->id,
        'period_id' => $currentPeriod?->id,
        'period_name' => $currentPeriod?->name,
        'today' => now()->format('Y-m-d')
    ]);
    
    // ✅ Primera vez - inscribir con período actual
    $user->courses()->attach($course->id, [
        'progress' => 0,
        'started_at' => now(),
        'period_id' => $currentPeriod ? $currentPeriod->id : null
    ]);
    
    $progress = 0;
    
} else {
    // ✅ Ya inscrito - SOLO leer el progreso guardado
    $pivotRow = $user->courses()->where('course_id', $course->id)->first();
    if ($pivotRow && $pivotRow->pivot) {
        $progress = $pivotRow->pivot->progress;
        
        // ✅ Si no tiene started_at, agregarlo UNA VEZ
        if (!$pivotRow->pivot->started_at) {
            $user->courses()->updateExistingPivot($course->id, [
                'started_at' => now()
            ]);
        }
        
        // ✅ Si no tiene period_id pero existe un período activo, asignarlo
        if (!$pivotRow->pivot->period_id) {
            $currentPeriod = $course->periods()
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();
            
            if ($currentPeriod) {
                $user->courses()->updateExistingPivot($course->id, [
                    'period_id' => $currentPeriod->id
                ]);
                
                \Log::info('✅ Período asignado a usuario existente:', [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'period_id' => $currentPeriod->id
                ]);
            }
        }
    }
}
        // completions del usuario
        $userCompletions = $user->completions->map(function ($item) {
            return [
                'type' => class_basename($item->completable_type),
                'id'   => $item->completable_id
            ];
        });

        // examen final
        $finalExamActivity = $course->finalExam;

        if ($finalExamActivity) {
            $finalExamData = $user->completions()
                ->where('completable_type', Activities::class)
                ->where('completable_id', $finalExamActivity->id)
                ->first();
                
                
        }
    }

    $topics = $course->topics;

    return view('layouts.Cursos.show', compact(
        'departments',
        'course',
        'topics',
        'progress',
        'totalItems',
        'isEnrolled',
        'finalExamActivity',
        'finalExamData',
        'userCompletions'
    ));
}


    /**
     * Mostrar formulario de edición
     */
    public function edit(Course $course): View
    {
        $activeInstitutionId = session('active_institution_id');

        // VALIDACIÓN DE SEGURIDAD: Verificar institución
        if ($course->institution_id != $activeInstitutionId) {
            Log::warning('Intento de editar curso de otra institución', [
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'course_institution_id' => $course->institution_id,
                'user_active_institution_id' => $activeInstitutionId
            ]);

            abort(403, 'No puedes editar cursos de otra institución.');
        }

        // Autorización adicional: Verificar que el usuario es el instructor o master
        $this->authorize('update', $course);
        $course->load('institution');

        // 1. Cargar la institución actual y sus relaciones
        $currentInstitution = Institution::with(['careers', 'departments.workstations'])
                                ->find($activeInstitutionId);

        // 2. Crear el mapa para el JS de departamentos/puestos
        $departmentWorkstationsMap = [];
        if ($currentInstitution->departments) {
            $departmentWorkstationsMap = $currentInstitution->departments->mapWithKeys(function ($department) {
                return [$department->id => $department->workstations->toArray()];
            });
        }

        // 3. Cargar los filtros que el curso YA tiene seleccionados
        //    Usamos pluck('id') para obtener un array simple de IDs [1, 3]
        $course->load('careers', 'departments', 'workstations');
        
        $selectedFilters = [
            'career_id' => $course->careers->pluck('id')->first(), // Asumimos que solo es una carrera
            'department_id' => $course->departments->pluck('id')->first(), // Asumimos que solo es un depto
            'workstation_id' => $course->workstations->pluck('id')->first(), // Asumimos que solo es un puesto
        ];

        return view('layouts.Cursos.edit', compact(
            'course', 
            'currentInstitution', // Necesario para los filtros
            'departmentWorkstationsMap', // Necesario para el JS
            'selectedFilters' // Necesario para pre-seleccionar
        ));
    }

    /**
     * Actualizar curso
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $activeInstitutionId = session('active_institution_id');

        // VALIDACIÓN DE SEGURIDAD: Verificar institución
        if ($course->institution_id != $activeInstitutionId) {
            Log::warning('Intento de actualizar curso de otra institución', [
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'course_institution_id' => $course->institution_id,
                'user_active_institution_id' => $activeInstitutionId
            ]);

            abort(403, 'No puedes actualizar cursos de otra institución.');
        }

        // Autorización: Policy
        $this->authorize('update', $course);

        $institution = Institution::find($course->institution_id);
        $creditsRule = 'nullable|integer|min:0';
        if ($institution && $institution->name === 'Universidad Mundo Imperial') {
            $creditsRule = 'required|integer|min:0';
        }

        // Validación
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credits' => $creditsRule,
            'modality' => 'required|in:presencial,virtual,hibrida',
            'hours' => 'required|integer|min:0|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'guide_material' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:40960', 
            'institution_id' => 'required|exists:institutions,id', // Lo usamos pero no lo actualizamos
            'career_id' => 'nullable|exists:careers,id',
            'department_id' => 'nullable|exists:departments,id',
            'workstation_id' => 'nullable|exists:workstations,id',
        ]);

        // Manejo de imagen
        if ($request->hasFile('image')) {
            if ($course->image) {
                Storage::disk('public')->delete($course->image);
            }
            $validatedData['image'] = $request->file('image')->store('courses', 'public');
        }

        // Manejo de material de guía
        if ($request->hasFile('guide_material')) {
            // Eliminar archivo anterior si existe
            if ($course->guide_material_path) {
                Storage::disk('public')->delete($course->guide_material_path);
            }
            // Guardar el nuevo archivo
            $validatedData['guide_material_path'] = $request->file('guide_material')->store('courses/guides', 'public');
        }

        $course->update($validatedData);

        if ($request->filled('career_id')) {
            $course->careers()->sync([$request->career_id]);
            $course->departments()->sync([]); // Limpiar el otro filtro
            $course->workstations()->sync([]);
        } 
        elseif ($request->filled('department_id')) {
            $course->departments()->sync([$request->department_id]);
            $course->careers()->sync([]); // Limpiar el otro filtro
            
            // Si se especificó un puesto, guardarlo. Si no, limpiarlo.
            if ($request->filled('workstation_id')) {
                $course->workstations()->sync([$request->workstation_id]);
            } else {
                $course->workstations()->sync([]);
            }
        }

        Log::info('Curso actualizado', ['course_id' => $course->id, 'user_id' => Auth::id()]);

        // Redirigir según la acción solicitada
        if ($request->input('action') == 'save_and_continue') {
            return redirect()->route('course.topic.create', ['course' => $course->id])
                ->with('success', 'Curso actualizado. Ahora puedes editar sus temas.');
        }

        return redirect()->route('Cursos.index')
            ->with('success', 'Curso actualizado exitosamente.');
    }

    public function updateWelcome(Request $request, Course $course)
{
    $activeInstitutionId = session('active_institution_id');

    if ($course->institution_id != $activeInstitutionId) {
        abort(403, 'No autorizado.');
    }

    $course->show_welcome = $request->input('show_welcome') == '1';
    $course->save();

    return response()->json([
        'success' => true,
        'show_welcome' => $course->show_welcome
    ]);
}

    /**
     * Eliminar curso
     */
    public function destroy(Course $course): RedirectResponse
    {
        $activeInstitutionId = session('active_institution_id');

        // VALIDACIÓN DE SEGURIDAD: Verificar institución
        if ($course->institution_id != $activeInstitutionId) {
            Log::warning('Intento de eliminar curso de otra institución', [
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'course_institution_id' => $course->institution_id,
                'user_active_institution_id' => $activeInstitutionId
            ]);

            abort(403, 'No puedes eliminar cursos de otra institución.');
        }

        // Autorización: Policy
        $this->authorize('delete', $course);

        // Eliminar imagen asociada
        if ($course->image) {
            Storage::disk('public')->delete($course->image);
        }

        if ($course->guide_material_path) {
            Storage::disk('public')->delete($course->guide_material_path);
        }

        $courseTitle = $course->title;
        $course->delete();

        Log::info('Curso eliminado', [
            'course_id' => $course->id,
            'course_title' => $courseTitle,
            'user_id' => Auth::id(),
            'institution_id' => $activeInstitutionId
        ]);

        return redirect()->route('Cursos.index')
            ->with('success', 'Curso "' . $courseTitle . '" eliminado exitosamente.');
    }


    // --- MÉTODOS DE INSCRIPCIÓN (NUEVOS) ---

    /**
     * Inscribe al usuario autenticado en un curso.
     * Responde a una solicitud AJAX.
     */
    public function enroll(Request $request, Course $course): JsonResponse
    {
        $user = Auth::user();

        // 1. Autorización: ¿Puede el usuario ver este curso?
        //    Usamos la policy 'view' que ya definimos.
        try {
            $this->authorize('view', $course);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes permiso para inscribirte en este curso.'
            ], 403);
        }

        // 2. Lógica de inscripción
        // syncWithoutDetaching previene duplicados si ya está inscrito
        $user->courses()->syncWithoutDetaching($course->id);

        Log::info('Usuario inscrito en curso', [
            'user_id' => $user->id, 
            'course_id' => $course->id
        ]);

        return response()->json([
            'success' => true, 
            'message' => '¡Inscripción exitosa!'
        ]);
    }

    /**
     * Da de baja al usuario autenticado de un curso.
     * Responde a una solicitud AJAX.
     */
    public function unenroll(Request $request, Course $course): JsonResponse
    {
        $user = Auth::user();

        // 1. Lógica de desinscripción
        // detach() simplemente quita la relación.
        $user->courses()->detach($course->id);

        Log::info('Usuario dado de baja de curso', [
            'user_id' => $user->id, 
            'course_id' => $course->id
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Has sido dado de baja del curso.'
        ]);
    }

    public function showCertificate(Course $course)
    {
        $user = Auth::user();
        
        // 1. Buscar el examen final del curso
        $finalExam = $course->finalExam;
        
        if (!$finalExam) {
            return redirect()->route('course.show', $course)
                ->with('error', 'Este curso no tiene certificado disponible.');
        }

        // 2. Verificar si el usuario completó ese examen específico
        $completion = $user->completions()
            ->where('completable_type', Activities::class)
            ->where('completable_id', $finalExam->id)
            ->first();

        // 3. Validar (puedes añadir validación de puntaje mínimo aquí si quieres, ej: score >= 60)
        if (!$completion) {
            return redirect()->route('course.show', $course)
                ->with('error', 'Debes completar el examen final para ver el certificado.');
        }

        // ---------------------------------------------------------
        // 2. PREPARACIÓN DE DATOS
        // ---------------------------------------------------------
        
        // Formatear fecha elegante: "24 de Noviembre de 2025"
        // Si tu Laravel no está en español, esto ayuda a forzarlo.
        $completionDate = $completion->created_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $data = [
            'course'           => $course,
            'user'             => $user,
            'score'            => $completion->score,
            'date'             => $completionDate,
            // Datos de la institución (logotipo y nombre) obtenidos de la sesión actual
            'institution_logo' => session('active_institution_logo'),
            'institution_name' => session('active_institution_name'),
        ];

        // ---------------------------------------------------------
        // 3. GENERACIÓN DEL PDF
        // ---------------------------------------------------------
        
        // Cargamos la vista de diseño que creamos (Cursos.template_v1)
        $pdf = Pdf::loadView('layouts.Cursos.template_v1', $data);

        // Configuramos el papel A4 Horizontal
        $pdf->setPaper('a4', 'landscape');

        // ---------------------------------------------------------
        // 4. DESCARGA
        // ---------------------------------------------------------

        // Creamos un nombre de archivo limpio para evitar caracteres raros
        $filename = 'Certificado_' . Str::slug($course->title) . '_' . Str::slug($user->nombre . ' ' . $user->apellido_paterno) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Muestra la lista de certificados obtenidos por el usuario.
     */
    public function myCertificates()
    {
        $user = Auth::user();
        $activeInstitutionId = session('active_institution_id'); // <--- 1. Obtenemos el contexto actual

        $certificates = $user->completions()
            ->where('completable_type', Activities::class)
            ->with('completable.course') // Cargar curso para ver su institución
            ->get()
            ->filter(function ($completion) use ($activeInstitutionId) {
                
                // Verificaciones de seguridad (que exista la actividad y el curso)
                if (!$completion->completable || !$completion->completable->course) {
                    return false;
                }

                // A. Que sea examen final
                $isFinalExam = $completion->completable->is_final_exam;

                // B. Que pertenezca a la institución activa actualmente
                $isSameInstitution = $completion->completable->course->institution_id == $activeInstitutionId;

                return $isFinalExam && $isSameInstitution;
            });

        return view('layouts.Cursos.certificates_list', compact('certificates'));
    }

    /**
 * Mostrar lista de asistencia del curso
 */
/**
 * Mostrar lista de asistencia
 */
public function attendance(Course $course)
{
    $activeInstitutionId = session('active_institution_id');

    if ($course->institution_id != $activeInstitutionId) {
        abort(403, 'No puedes ver la asistencia de cursos de otra institución.');
    }

    $this->authorize('update', $course);

    // ✅ Obtener todos los períodos del curso
    $periods = $course->periods()->orderBy('start_date', 'desc')->get();

    // Obtener asistencias con información del período
    $attendances = $course->users()
        ->withPivot(['started_at', 'completed_at', 'progress', 'period_id'])
        ->orderBy('course_user.started_at', 'desc')
        ->get()
        ->map(function ($user) use ($course) {
            $finalExam = $course->finalExam;
            $finalScore = null;
            $completedAt = null;
            
            if ($finalExam) {
                $completion = $user->completions()
                    ->where('completable_type', Activities::class)
                    ->where('completable_id', $finalExam->id)
                    ->first();
                
                if ($completion) {
                    $finalScore = $completion->score;
                    $completedAt = $completion->created_at;
                }
            }
            
            // ✅ Si completó el examen final, progreso = 100%
            $progress = $finalScore !== null ? 100 : $user->pivot->progress;
            $status = $finalScore !== null ? 'Completado' : 'En progreso';
            
            // ✅ Obtener nombre del período
            $periodName = null;
            if ($user->pivot->period_id) {
                $period = CoursePeriod::find($user->pivot->period_id);
                $periodName = $period ? $period->name : null;
            }
            
            return [
                'id' => $user->id,
                'nombre' => $user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno,
                'email' => $user->email,
                'started_at' => $user->pivot->started_at,
                'completed_at' => $completedAt,
                'progress' => $progress,
                'final_score' => $finalScore,
                'status' => $status,
                'period_id' => $user->pivot->period_id,
                'period_name' => $periodName
            ];
        });

    return view('layouts.Cursos.attendance', compact('course', 'attendances', 'periods'));
}
/**
 * Guardar progreso del usuario en el curso
 */
public function saveProgress(Request $request, Course $course)
{
    $user = Auth::user();
    
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
    }
    
    $validated = $request->validate([
        'progress' => 'required|numeric|min:0|max:100'
    ]);
    
    // Obtener progreso actual
    $pivotRow = $user->courses()->where('course_id', $course->id)->first();
    $currentProgress = $pivotRow ? $pivotRow->pivot->progress : 0;
    
    if ($validated['progress'] > $currentProgress) {
    $user->courses()->updateExistingPivot($course->id, [
        'progress' => $validated['progress'] // ✅ SOLO progreso, nada más
    ]);
        
        return response()->json([
            'success' => true,
            'progress' => $validated['progress']
        ]);
    }
    
    return response()->json([
        'success' => true,
        'progress' => $currentProgress,
        'message' => 'Progreso no actualizado (ya estaba más avanzado)'
    ]);
}

/**
 * Exportar lista de asistencia a PDF
 */
/**
 * Exportar lista de asistencia a PDF
 */
/**
 * Exportar lista de asistencia a PDF con filtros
 */
public function exportAttendancePDF(Request $request, Course $course)
{
    $activeInstitutionId = session('active_institution_id');

    if ($course->institution_id != $activeInstitutionId) {
        abort(403, 'No puedes ver la asistencia de cursos de otra institución.');
    }

    $this->authorize('update', $course);

    // ✅ Obtener filtros de la URL
    $searchName = $request->input('search');
    $filterDate = $request->input('date');
    $filterPeriod = $request->input('period');

    // Obtener asistencias
    $attendances = $course->users()
        ->withPivot(['started_at', 'completed_at', 'progress', 'period_id'])
        ->orderBy('course_user.started_at', 'desc')
        ->get()
        ->map(function ($user) use ($course) {
            $finalExam = $course->finalExam;
            $finalScore = null;
            $fechaFin = null;
            
            if ($finalExam) {
                $completion = $user->completions()
                    ->where('completable_type', Activities::class)
                    ->where('completable_id', $finalExam->id)
                    ->first();
                
                if ($completion) {
                    $finalScore = $completion->score;
                    $fechaFin = $completion->created_at->format('d/m/Y H:i');
                }
            }
            
            $progress = $finalScore !== null ? 100 : $user->pivot->progress;
            $status = $finalScore !== null ? 'Completado' : 'En progreso';
            
            return [
                'nombre' => $user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno,
                'puesto' => $user->workstation?->name ?? 'N/A',
                'rfc' => $user->RFC ?? 'N/A',
                'inicio' => $user->pivot->started_at ? \Carbon\Carbon::parse($user->pivot->started_at)->format('d/m/Y H:i') : null,
                'fin' => $fechaFin,
                'started_at' => $user->pivot->started_at,
                'progress' => $progress,
                'final_score' => $finalScore,
                'status' => $status,
                'period_id' => $user->pivot->period_id
            ];
        });

    // ✅ APLICAR FILTROS
    if ($searchName) {
        $attendances = $attendances->filter(function ($attendance) use ($searchName) {
            return stripos($attendance['nombre'], $searchName) !== false;
        });
    }

    if ($filterDate) {
        $attendances = $attendances->filter(function ($attendance) use ($filterDate) {
            if (!$attendance['started_at']) return false;
            $startDate = \Carbon\Carbon::parse($attendance['started_at'])->format('Y-m-d');
            return $startDate === $filterDate;
        });
    }

    if ($filterPeriod) {
        $attendances = $attendances->filter(function ($attendance) use ($filterPeriod) {
            return $attendance['period_id'] == $filterPeriod;
        });
    }

    // ✅ Reindexar después de filtrar
    $attendances = $attendances->values();

    $data = [
        'course' => $course,
        'attendances' => $attendances,
        'instructor' => $course->instructor->nombre . ' ' . $course->instructor->apellido_paterno,
        'institution_logo' => session('active_institution_logo'),
        'institution_name' => session('active_institution_name'),
        'fecha_inicio' => $attendances->whereNotNull('started_at')->min('started_at') ? \Carbon\Carbon::parse($attendances->whereNotNull('started_at')->min('started_at'))->format('d/m/Y') : 'N/A',
        'hora_inicio' => $attendances->whereNotNull('started_at')->min('started_at') ? \Carbon\Carbon::parse($attendances->whereNotNull('started_at')->min('started_at'))->format('H:i') : 'N/A',
        'fecha_fin' => now()->format('d/m/Y'),
        'hora_fin' => now()->format('H:i')
    ];

    $pdf = Pdf::loadView('layouts.Cursos.attendance_pdf', $data);
    $pdf->setPaper('a4', 'portrait');

    $filename = 'Lista_Asistencia_' . str_replace(' ', '_', $course->title) . '.pdf';
    return $pdf->download($filename);
}

}