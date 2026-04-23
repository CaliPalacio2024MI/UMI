<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use App\Models\Cursos\Subtopic;
use App\Models\Cursos\Topics;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;
use App\Models\SubtopicTemplate;

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
    public function store(Request $request)
    {
        // Guardar subtemas desde biblioteca
        if ($request->has('subtopic_templates') && count($request->subtopic_templates) >0) {
            foreach ($request->subtopic_templates as $templateId) {
                $template = SubtopicTemplate::find($templateId);

                //Calcular orden
                $maxOrder = Subtopic::where('topic_id', $request->topic_id)->max('order');
                $order = $maxOrder !==null ? $maxOrder + 1 : 0;

                Subtopic::create([
                    'topic_id' => $request->topic_id, //Clave
                    'title' => $template->title,
                    'description' => $template->description,
                    'file_path' => $template->file_path,
                    'order' => $order,
                    'show_title' => true,
                    'show_turtle' => false,
                    'turtle_voice' => null,
                ]);
            }

            return back()->with('success', 'Subtemas agregados desde la biblioteca.');
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,pptx,mp4,mov,avi,wmv|max:51200',
            'order' => 'nullable|integer',
            'video_segments' => 'nullable|array',
            'video_segments.*.start' => 'required_with:video_segments|string',
            'video_segments.*.end' => 'required_with:video_segments|string',
            'video_segments.*.turtle' => 'required_with:video_segments|integer|in:0,1',
        ]);

        if ($request->hasFile('file')){
            $path = $request->file('file')->store('subtopic', 'public');
            $validatedData['file_path'] = $path;
            unset($validatedData['file']);
        }

        // NUEVO: Guardar show_title
        $validatedData['show_title'] = $request->has('show_title');
        
        // NUEVO: Guardar show_turtle y turtle_voice
        $validatedData['show_turtle'] = $request->has('show_turtle');
        $validatedData['turtle_voice'] = $request->input('turtle_voice', null);

        // AGREGAR después de guardar show_turtle y turtle_voice
        if ($request->has('video_segments')) {
        $validatedData['video_segments'] = $request->video_segments;
    }

        // NUEVO: Asignar orden automáticamente si no viene
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

    public function update(Request $request, Subtopic $subtopic)
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
    $topic->title = $request->title;
    $topic->description = $request->description;
    $topic->show_title = $request->has('show_title');
    $topic->show_turtle = $request->has('show_turtle');
    $topic->turtle_voice = $request->input('turtle_voice', null);
    
    // GUARDAR SEGMENTOS DE VIDEO
    if ($request->has('video_segments')) {
        $topic->video_segments = $request->video_segments;
    } else {
        $topic->video_segments = null; // Limpiar si no hay segmentos
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