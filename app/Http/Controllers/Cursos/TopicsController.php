<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use App\Models\Cursos\Course;
use App\Models\Cursos\Topics;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class TopicsController extends Controller
{
    public function create(Course $course): View
{
    // ✅ Cargar topics ORDENADOS por 'order'
    $course->load(['topics' => function($query) {
        $query->orderBy('order');
    }, 'topics.activities', 'topics.subtopics']);
    
    $formActions = route('topics.store');
    return view('layouts.Cursos.topic.create', [
        'course' => $course, 
        'formActions' => $formActions
    ]);
}

    /**
     * Guardar un nuevo tema
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx,ppt,pptx,mp4,mov,avi,wmv|max:163840',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('topic_files', 'public');
            $validatedData['file_path'] = $path;
        }

        unset($validatedData['file']);

        // ✅ NUEVO: Guardar show_title
        $validatedData['show_title'] = $request->has('show_title');
        
        // ✅ NUEVO: Guardar show_turtle y turtle_voice
        $validatedData['show_turtle'] = $request->has('show_turtle');
        $validatedData['turtle_voice'] = $request->input('turtle_voice', null);

        // ✅ NUEVO: Asignar orden automáticamente
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
            'show_title' => $topic->show_title, // ✅
            'show_turtle' => $topic->show_turtle, // ✅ NUEVO
            'turtle_voice' => $topic->turtle_voice // ✅ NUEVO
        ]);
    }

    /**
     * Actualiza el tema en la base de datos.
     */
    public function update(Request $request, Topics $topic)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,mov,avi,wmv|max:163840',
        ]);

        // Actualizar campos básicos
        $topic->title = $request->title;
        $topic->description = $request->description;
        $topic->show_title = $request->has('show_title'); // ✅
        $topic->show_turtle = $request->has('show_turtle'); // ✅ NUEVO
        $topic->turtle_voice = $request->input('turtle_voice', null); // ✅ NUEVO

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