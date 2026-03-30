<?php

namespace App\Http\Controllers;

use App\Models\SubtopicTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Importante para manejar archivos

class SubtopicTemplateController extends Controller
{
    //Mostrar todos los subtemas (Ahora incluye el formulario de creación)
    public function index()
    {
        $templates = SubtopicTemplate::orderBy('title')->get();
        return view('subtopics_template.index', compact('templates'));
    }

    // Guardar subtema plantilla
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,mp4,doc,docx|max:20480' // Agregué validación de archivo
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('subtopic_files', 'public');
            $data['file_path'] = $path; // Guardamos la ruta del archivo
        }

        SubtopicTemplate::create($data);

        return redirect()->route('subtopics_template.index')->with('success', 'Subtema añadido a la biblioteca correctamente.');
    }

    //Formulario para editar un subtema existente
    public function edit($id)
    {
// El subtema que se va a editar
    $template = SubtopicTemplate::findOrFail($id); 
    
    // Volvemos a traer todos los subtemas para que la lista de la derecha siga apareciendo
    $templates = SubtopicTemplate::orderBy('title')->get(); 
    
    return view('subtopics_template.edit', compact('template', 'templates'));
    }

    //Procesar la actualización del tema
    public function update(Request $request, $id)
    {
        $template = SubtopicTemplate::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,mp4,doc,docx|max:20480'
        ]);

        if ($request->hasFile('file')) {
            // Opcional: Borrar el archivo anterior si existe para no llenar el servidor
            if ($template->file_path) {
                Storage::disk('public')->delete($template->file_path);
            }
            $data['file_path'] = $request->file('file')->store('topic_files', 'public');
        }

        $template->update($data);

        return redirect()->route('subtopics_template.index')->with('success', 'Subtema actualizado correctamente.');
    }

    //Eliminar un subtema de la biblioteca
    public function destroy($id)
    {
        $template = SubtopicTemplate::findOrFail($id);

        // Borrar el archivo físico si existe
        if ($template->file_path) {
            Storage::disk('public')->delete($template->file_path);
        }

        $template->delete();

        return redirect()->route('subtopics_template.index')->with('success', 'Subtema eliminado de la biblioteca.');
    }
}
