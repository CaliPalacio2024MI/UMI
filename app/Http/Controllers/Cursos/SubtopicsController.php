<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use App\Models\Cursos\Subtopic;
use App\Models\Cursos\Topics;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;

class SubtopicsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Topics $topic)
    {
        $course = $topic->course;
        return view('course.topic.create', [
            'topic' => $topic,
            'course' => $course,
            'type' => 'subtopic',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Topics $topic)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,pptx,mp4,mov,avi,wmv|max:51200',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('file')){
            $path = $request->file('file')->store('subtopic', 'public');
            $validatedData['file_path'] = $path;
            unset($validatedData['file']);
        }

        // ✅ NUEVO: Guardar show_title
        $validatedData['show_title'] = $request->has('show_title');
        
        // ✅ NUEVO: Guardar show_turtle y turtle_voice
        $validatedData['show_turtle'] = $request->has('show_turtle');
        $validatedData['turtle_voice'] = $request->input('turtle_voice', null);

        // ✅ NUEVO: Asignar orden automáticamente si no viene
        if (!isset($validatedData['order'])) {
            $maxOrder = Subtopic::where('topic_id', $topic->id)->max('order');
            $validatedData['order'] = $maxOrder !== null ? $maxOrder + 1 : 0;
        }

        $subtopic = $topic->subtopics()->create($validatedData);

        return back()->with('success', 'Subtema creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subtopic $subtopic)
    {
        if ($subtopic->file_path) {
            Storage::disk('public')->delete($subtopic->file_path);
        }

        $subtopic->delete();

        return back()->with('success', '¡Subtema eliminado exitosamente!');
    }
}