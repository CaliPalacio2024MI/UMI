<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use App\Models\Cursos\Course;
use App\Models\Cursos\Topics;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Models\SubtopicTemplate;
use Illuminate\Support\Facades\Storage;
use App\Models\TopicTemplate;

class TopicsController extends Controller
{
    public function create(Course $course): View
    {
        //Cargar temas de biblioteca
        $topicTemplates = TopicTemplate::orderBy('title')->get();
        //Para que los temas cargados en cursos se muestren en subtemas
        $courseTopics = Topics::where('course_id', $course->id) ->orderBy('title') ->get();
        //Para los subtemas
        $subtopicTemplates = SubtopicTemplate::orderBy('title')->get();
        // ✅ Cargar topics ORDENADOS por 'order'
        $course->load(['topics' => function($query) {
            $query->orderBy('order');
        }, 'topics.activities', 'topics.subtopics']);
        
        $formActions = route('topics.store');
        return view('layouts.Cursos.topic.create', [
            'course' => $course, 
            'formActions' => $formActions,
            'topicTemplates' => $topicTemplates,
            'courseTopics' => $courseTopics,
            'subtopicTemplates' => $subtopicTemplates
        ]);
    }

    /**
     * Guardar un nuevo tema
     */
    public function store(Request $request): RedirectResponse
    {
        //Guardar temas desde biblioteca
        if ($request->filled('topic_templates')) {

            foreach ($request->topic_templates as $templateId) {
                $template = TopicTemplate::find($templateId);

                $maxOrder = Topics::where('course_id', $request->course_id)->max('order');
                $order = $maxOrder !==null ? $maxOrder + 1 : 0;

                Topics::create([
                    'course_id' => $request->course_id,
                    'title' => $template->title,
                    'description' => $template->description,
                    'file_path' => $template->file_path,
                    'order' => $order,
                ]);
            }

            return back()->with('success', 'Temas agregados desde la biblioteca');
        }
        
        $validatedData = $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx,ppt,pptx,mp4,mov,avi,wmv|max:163840',
            'video_segments' => 'nullable|array',
            'video_segments.*.start' => 'required_with:video_segments|string',
            'video_segments.*.end' => 'required_with:video_segments|string',
            'video_segments.*.turtle' => 'required_with:video_segments|integer|in:0,1',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('topic_files', 'public');
            $validatedData['file_path'] = $path;
        }

        unset($validatedData['file']);

        // Guardar show_title
        $validatedData['show_title'] = $request->has('show_title');
        
        // Guardar show_turtle y turtle_voice
        $validatedData['show_turtle'] = $request->has('show_turtle');
        $validatedData['turtle_voice'] = $request->input('turtle_voice', null);

        // Guardar video_segments como array puro (no objeto con índices)
        if ($request->has('video_segments') && !empty($request->video_segments)) {
            // Convertir a array con índices consecutivos desde 0
            $validatedData['video_segments'] = array_values($request->video_segments);
        } else {
            $validatedData['video_segments'] = null;
        }

        // Asignar orden automáticamente
        $maxOrder = Topics::where('course_id', $validatedData['course_id'])->max('order');
        $validatedData['order'] = $maxOrder !== null ? $maxOrder + 1 : 0;

        Topics::create($validatedData);

        return back()->with('success', 'Tema creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un tema existente.
     */
    public function edit(Topics $topic)
    {
        return response()->json([
            'id' => $topic->id,
            'title' => $topic->title,
            'description' => $topic->description,
            'file_path' => $topic->file_path,
            'course_id' => $topic->course_id,
            'show_title' => $topic->show_title,
            'show_turtle' => $topic->show_turtle,
            'turtle_voice' => $topic->turtle_voice,
            'video_segments' => $topic->video_segments,
        ]);
    }

    public function update(Request $request, Topics $topic)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,mov,avi,wmv|max:163840',
            'video_segments' => 'nullable|array',
            'video_segments.*.start' => 'required_with:video_segments|string',
            'video_segments.*.end' => 'required_with:video_segments|string',
            'video_segments.*.turtle' => 'required_with:video_segments|integer|in:0,1',
        ]);

        // Actualizar campos básicos
        $topic->title = $request->input('title');
        $topic->description = $request->input('description');
        $topic->show_title = $request->has('show_title');
        $topic->show_turtle = $request->has('show_turtle');
        $topic->turtle_voice = $request->input('turtle_voice', null);
        
        // CRÍTICO: Guardar video_segments como array puro
        if ($request->has('video_segments') && !empty($request->video_segments)) {
            // Convertir a array con índices consecutivos desde 0
            $topic->video_segments = array_values($request->video_segments);
        } else {
            $topic->video_segments = null;
        }

        // Manejar archivo si se subió uno nuevo
        if ($request->hasFile('file_path')) {
            // Eliminar archivo anterior si existe
            if ($topic->file_path) {
                Storage::disk('public')->delete($topic->file_path);
            }
            $topic->file_path = $request->file('file_path')->store('topic_files', 'public');
        }

        $topic->save();

        return redirect()->back()->with('success', 'Tema actualizado correctamente.');
    }

    public function updateOrder(Request $request)
    {
        $topics = $request->topics;
        
        foreach ($topics as $topic) {
            Topics::where('id', $topic['id'])->update(['order' => $topic['order']]);
        }
        
        return response()->json(['success' => true]);
    }

    public function destroy(Topics $topic)
    {
        // Gracias al Route Model Binding, Laravel nos encuentra el tema
        $topic->delete();

        // Redirigimos a la página anterior
        return back()->with('success', '¡Tema eliminado exitosamente!');
    }
}