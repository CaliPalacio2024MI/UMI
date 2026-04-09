<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class FacilityController extends Controller
{
    //Clase Index
    public function index()
    {
        $data = Facility::orderedForHorarios()->get();

        return view('layouts.ControlAdmin.Infraestrucuta.index', compact('data'));
    }
    public function createForm(){
        return view('layouts.ControlAdmin.Infraestrucuta.components.create');
    }
    public function store(Request $request){
        $validated = $request->validate([
            'numero_aula' => 'required|string|max:10',
            'seccion' => 'nullable|string|max:255',
            'capacidad' => 'nullable|integer|min:0',
            'tipo' => 'required|string|in:Aula,Laboratorio,Otro',
        ]);

        $seccion = isset($validated['seccion']) ? trim((string) $validated['seccion']) : '';
        if ($seccion === '') {
            $seccion = 'Sin sección';
        }

        $facility = Facility::create([
            'numero_aula' => $validated['numero_aula'],
            'seccion' => $seccion,
            'capacidad' => $validated['capacidad'] ?? null,
            'tipo' => $validated['tipo'],
        ]);

        // 3. Devolver una respuesta JSON de éxito (Axios lo espera)
        return response()->json([
            'message' => 'Aula creada exitosamente.',
            'data' => $facility, // Opcional: devolver los datos creados
        ], 201); 
    }

    public function show(Request $request, Facility $facility): View
    {
        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Infraestrucuta.components.show_detail', compact('facility'));
        }

        return view('layouts.ControlAdmin.Infraestrucuta.show', compact('facility'));
    }

    public function edit(Request $request, Facility $facility): View
    {
        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Infraestrucuta.components.edit_form', compact('facility'));
        }

        return view('layouts.ControlAdmin.Infraestrucuta.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'numero_aula' => 'required|string|max:10',
            'seccion' => 'nullable|string|max:255',
            'capacidad' => 'nullable|integer|min:0',
            'tipo' => 'required|string|in:Aula,Laboratorio,Otro',
        ]);

        $seccion = isset($validated['seccion']) ? trim((string) $validated['seccion']) : '';
        if ($seccion === '') {
            $seccion = 'Sin sección';
        }

        $facility->update([
            'numero_aula' => $validated['numero_aula'],
            'seccion' => $seccion,
            'capacidad' => $validated['capacidad'] ?? null,
            'tipo' => $validated['tipo'],
        ]);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Aula actualizada correctamente.',
            ], 200);
        }

        return redirect()
            ->route('control.facilities.index', ['modal' => 'success'])
            ->with('success', 'Aula actualizada correctamente.');
    }

    public function destroy(Request $request, Facility $facility)
    {
        $facility->delete();

        $message = 'Aula eliminada correctamente.';
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return Redirect::route('control.facilities.index')->with('success', $message);
    }
}
