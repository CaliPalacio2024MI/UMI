<?php

namespace App\Http\Controllers\Cursos;

use App\Models\Cursos\Activities;
use App\Models\Cursos\Subtopic;
use App\Models\Cursos\Topics;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse; 
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Cursos\Completion;
use Illuminate\Validation\Rule;

class ActivitiesController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'course_id' => 'required|exists:courses,id', 
            'is_final_exam' => 'nullable|boolean', 
            
            'topic_id' => 'nullable|exists:topics,id|required_without_all:subtopic_id,is_final_exam',
            'subtopic_id' => 'nullable|exists:subtopics,id|required_without_all:topic_id,is_final_exam',

            'title' => 'required|string|max:255',
            'type' => 'required|string', 
            'content' => 'nullable|array', // ✅ Cambié a nullable porque Video no necesita content
            
            // ✅ NUEVO: Campos para videos con tortuguita
            'video_file' => 'nullable|file|mimes:mp4,webm,ogg,avi,mov|max:163840',
            'show_turtle' => 'nullable|boolean',
            'turtle_voice' => 'nullable|integer|in:0,1',
        ]);

        // Validaciones específicas por tipo
        if ($validatedData['type'] === 'Cuestionario'){
            $request->validate([
                'content.question' => 'required|string',
                'content.options' => 'required|array|min:4', 
                'content.options.*' => 'required|string',
                'content.correct_answer' => 'required', 
            ]);
        }

        elseif ($validatedData['type'] === 'Examen') {
            $request->validate([
                'content.questions' => 'required|array|min:1',
                'content.questions.*.question' => 'required|string',
                'content.questions.*.options' => 'required|array|min:2',
                'content.questions.*.options.*' => 'required|string',
                'content.questions.*.correct_answer' => 'required|string',
            ]);
        }
        
        elseif ($validatedData['type'] === 'SopaDeLetras') {
            $request->validate([
                'content.words' => 'required|array|min:1',
                'content.words.*' => 'required|string|distinct',
                'content.grid_size' => 'required|integer|min:5|max:20',
            ]);
        }

        elseif ($validatedData['type'] === 'Ahorcado') {
            $request->validate([
                'content.word' => 'required|string',
                'content.hint' => 'nullable|string',
                'content.max_attempts' => 'required|integer|min:3|max:10',
            ]);
        }

        elseif ($validatedData['type'] === 'Crucigrama') {
            $request->validate([
                'content.grid_size' => 'required|integer|min:5|max:15',
                'content.words' => 'required|array|min:1',
                'content.words.*.word' => 'required|string',
                'content.words.*.clue' => 'required|string',
                'content.words.*.direction' => 'required|string|in:horizontal,vertical',
            ]);
        }

        // ✅ NUEVO: Manejar videos con tortuguita
        elseif ($validatedData['type'] === 'Video') {
            if ($request->hasFile('video_file')) {
                $validatedData['file_path'] = $request->file('video_file')->store('videos', 'public');
            }
            
            $validatedData['show_turtle'] = $request->has('show_turtle');
            $validatedData['turtle_voice'] = $request->turtle_voice ?? null;
            
            // Video no necesita content
            $validatedData['content'] = [];
        }

        // ✅ NUEVO: Guardar show_title
        $validatedData['show_title'] = $request->has('show_title');
        
        $courseId = $validatedData['course_id'];
        $validatedData['is_final_exam'] = $request->has('is_final_exam');

        // LÓGICA DE LIMPIEZA DE ID (Asegurar que solo uno se guarde)
        if ($validatedData['is_final_exam']) {
            // Es un examen final, pertenece al CURSO. Anular temas.
            $validatedData['topic_id'] = null;
            $validatedData['subtopic_id'] = null;

        } elseif ($request->filled('subtopic_id')) {
            // Caso 1: Actividad pertenece a un Subtema
            $validatedData['topic_id'] = null; // Forzar a NULL
            
            // Obtener el Course ID para la redirección
            $subtopic = Subtopic::with('topic')->find($request->subtopic_id);
            if (!$subtopic) {
                return redirect()->back()->withErrors(['subtopic' => 'Subtema no encontrado.']);
            }
            $courseId = $subtopic->topic->course_id;

        } elseif ($request->filled('topic_id')) {
            // Caso 2: Actividad pertenece a un Tema
            $validatedData['subtopic_id'] = null; // Forzar a NULL
            
            // Obtener el Course ID para la redirección
            $topic = Topics::find($request->topic_id);
            if (!$topic) {
                return redirect()->back()->withErrors(['topic' => 'Tema no encontrado.']);
            }
            $courseId = $topic->course_id;
        } else {
            // Fallo de seguridad: No se seleccionó nada
            return redirect()->back()->withErrors(['parent' => 'Debe seleccionar un Tema o un Subtema para la actividad.']);
        }

        // ✅ NUEVO: Asignar orden automáticamente
        if ($validatedData['subtopic_id']) {
            $maxOrder = Activities::where('subtopic_id', $validatedData['subtopic_id'])->max('order');
        } elseif ($validatedData['topic_id']) {
            $maxOrder = Activities::where('topic_id', $validatedData['topic_id'])->max('order');
        } else {
            $maxOrder = Activities::where('course_id', $validatedData['course_id'])
                                  ->where('is_final_exam', true)
                                  ->max('order');
        }
        $validatedData['order'] = $maxOrder !== null ? $maxOrder + 1 : 0;

        Activities::create($validatedData);
        return back()->with('success', 'Actividad creada exitosamente.');
    }

    public function destroy(Activities $activity)
    {
        // 1. Elimina la actividad específica
        $activity->delete();

        // 2. Redirige al usuario a la página anterior con un mensaje de éxito
        return back()->with('success', '¡Actividad eliminada exitosamente!');
    }

    /**
     * Procesa el envío de una actividad interactiva (Cuestionario, Sopa, etc.)
     */
    public function submit(Request $request, Activities $activity): JsonResponse
    {
        $user = Auth::user();
        $score = 0; 
        $message = '¡Actividad completada!';

        // 1. Validar "Cuestionario" (1 pregunta)
        if ($activity->type === 'Cuestionario') {
            $validated = $request->validate(['answer' => 'required']);
            $userAnswer = $validated['answer'];
            $correctAnswer = $activity->content['correct_answer'] ?? null;

            if (strval($userAnswer) !== strval($correctAnswer)) {
                return response()->json(['success' => false, 'message' => 'Respuesta incorrecta.'], 422);
            }
            $score = 100.00; // Si es correcta, 100
        }

        // 2. Validar "Examen" (múltiples preguntas)
        elseif ($activity->type === 'Examen') {
            $userAnswersData = $request->validate(['answers' => 'required|array']);
            $userAnswers = $userAnswersData['answers'];
            
            $questions = $activity->content['questions'] ?? [];
            $totalQuestions = count($questions);
            $correctCount = 0;

            if ($totalQuestions > 0) {
                foreach ($userAnswers as $index => $answerData) {
                    $questionIndex = $answerData['q'];
                    $userAnswerIndex = $answerData['a'];

                    if (isset($questions[$questionIndex])) {
                        $correctAnswerIndex = $questions[$questionIndex]['correct_answer'] ?? null;
                        if (strval($userAnswerIndex) === strval($correctAnswerIndex)) $correctCount++;
                    }
                }
                $score = round(($correctCount / $totalQuestions) * 100, 2);
                $message = "¡Examen completado! Tu calificación: $correctCount / $totalQuestions ($score%)";
            } else {
                return response()->json(['success' => false, 'message' => 'Este examen no tiene preguntas.'], 422);
            }
        }

        // 3. Validar "Sopa de Letras"
        elseif ($activity->type === 'SopaDeLetras') {
            $validated = $request->validate(['completed' => 'required|boolean']);
            
            if ($validated['completed']) {
                $score = 100.00;
                $message = '¡Sopa de letras completada!';
            } else {
                return response()->json(['success' => false, 'message' => 'No has completado la sopa de letras.'], 422);
            }
        }

        // 4. Marcar como completado usando el sistema polimórfico
        $completion = $user->completions()->updateOrCreate(
            [
                'completable_type' => Activities::class, 
                'completable_id'   => $activity->id
            ],

            [
                'score' => $score
            ]
        );

        if ($completion->wasRecentlyCreated || $completion->wasChanged()) {
            // La actividad tiene la relación 'course' directa en el modelo
            $course = $activity->course; 
            
            // Si por alguna razón la relación es null (ej: actividad huérfana), buscamos manual
            if (!$course) {
                if ($activity->topic) $course = $activity->topic->course;
                elseif ($activity->subtopic) $course = $activity->subtopic->topic->course;
            }

            if ($course) {
                $course->calculateUserProgress($user->id);
            }
        }

        return response()->json([
            'success' => true,
            'created' => $completion->wasRecentlyCreated, // Para que el JS sepa si debe actualizar la barra
            'score'   => $score,
            'message' => $message
        ]);
    }

}