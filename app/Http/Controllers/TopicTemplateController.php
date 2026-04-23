<?php

namespace App\Http\Controllers;

use App\Models\TopicTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Importante para manejar archivos

class TopicTemplateController extends Controller
{
    //Mostrar todos los temas (Ahora incluye el formulario de creación)
    public function index()
    {
        $templates = TopicTemplate::orderBy('title')->get();
        return view('templates.index', compact('templates'));
    }

    // Guardar tema plantilla
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,mp4,doc,docx|max:20480' // Agregué validación de archivo
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('topic_files', 'public');
            $data['file_path'] = $path; // Guardamos la ruta del archivo
        }

        TopicTemplate::create($data);

        return redirect()->route('templates.index')->with('success', 'Tema añadido a la biblioteca correctamente.');
    }

    //Formulario para editar un tema existente
    public function edit($id)
    {
// El tema que se va a editar
    $template = TopicTemplate::findOrFail($id); 
    
    // Volvemos a traer todos los temas para que la lista de la derecha siga apareciendo
    $templates = TopicTemplate::orderBy('title')->get(); 
    
    return view('templates.edit', compact('template', 'templates'));
    }

    //Procesar la actualización del tema
    public function update(Request $request, $id)
    {
        $template = TopicTemplate::findOrFail($id);

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

        return redirect()->route('templates.index')->with('success', 'Tema actualizado correctamente.');
    }

    //Eliminar un tema de la biblioteca
    public function destroy($id)
    {
        $template = TopicTemplate::findOrFail($id);

        // Borrar el archivo físico si existe
        if ($template->file_path) {
            Storage::disk('public')->delete($template->file_path);
        }

        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Tema eliminado de la biblioteca.');
    }

    public function create()
{
    return view('templates.create');
}
}

