<?php

namespace App\Http\Controllers\AdmonCont\store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//Modelos
use App\Models\Users\Career;

class careerController extends Controller
{
    public function index()
    {
        $institutionId = session('active_institution_id');
        $institutionId = $institutionId ? (int) $institutionId : null;

        $careers = $institutionId
            ? Career::where('institution_id', $institutionId)->orderBy('created_at', 'desc')->get()
            : Career::orderBy('created_at', 'desc')->get();

        return view('layouts.ControlAdmin.Carreras.index', compact('careers'));
    }
    public function create()
    {
        return view('layouts.ControlAdmin.Carreras.create');
    }

    public function reticula(Career $carrera)
    {
        return view('layouts.ControlAdmin.Carreras.reticula', compact('carrera'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'official_id'  => 'required|string|max:255',
            'description1' => 'required|string|max:500',
            'description2' => 'required|string|max:500',
            'description3' => 'required|string|max:500',
            'type'         => 'required|in:Presencial,En linea',
            'semesters'    => 'required|integer|min:1|max:8',
        ], [
            'name.required'         => 'El nombre de la carrera es obligatorio.',
            'official_id.required'  => 'El RVOE es obligatorio.',
            'description1.required' => 'Profesionalización y empleabilidad es obligatorio.',
            'description2.required'  => 'Objetivo General es obligatorio.',
            'description3.required' => 'Elige Ser es obligatorio.',
            'type.required'         => 'Debe seleccionar la modalidad.',
            'semesters.required'    => 'Debe seleccionar el número de semestres.',
        ]);

        $institutionId = session('active_institution_id');
        if (!$institutionId) {
            return redirect()->back()->with('error', 'No hay institución activa. Seleccione una institución.');
        }
        $institutionId = (int) $institutionId;

        Career::create([
            'name'           => $request->name,
            'official_id'    => $request->official_id,
            'description1'  => $request->description1,
            'description2'  => $request->description2,
            'description3'  => $request->description3,
            'type'           => $request->type,
            'semesters'      => $request->semesters,
            'institution_id' => $institutionId,
        ]);

        return redirect()->route('control.careers.index', [], 303)
            ->with('success', 'Carrera creada exitosamente.');
    }

    public function update(Request $request, Career $carrera)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'         => 'required|string|max:255|unique:careers,name,' . $carrera->id,
            'official_id'  => 'required|string|max:255',
            'type'         => 'required|in:Presencial,En linea',
            'semesters'    => 'required|integer|min:1|max:8',
            'description1' => 'nullable|string|max:500',
            'description2' => 'nullable|string|max:500',
            'description3' => 'nullable|string|max:500',
        ], [
            'name.required'   => 'El nombre de la carrera es obligatorio.',
            'name.unique'    => 'Ya existe una carrera con ese nombre.',
            'official_id.required' => 'El RVOE es obligatorio.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('edit_career_id', $carrera->id);
        }

        $carrera->update($request->only([
            'name', 'official_id', 'description1', 'description2', 'description3',
            'type', 'semesters',
        ]));

        return redirect()->route('control.careers.index')->with('success', '¡Carrera actualizada exitosamente!');
    }
    //Eliminar
    //1. Modulo a usar
    //2. Clase(debe ser igual al nombre de la tabla pero en singular)
    //                      1.     2.
    public function destroy(Career $carrera)
    {
        $carrera->delete();
        return redirect()->route('control.careers.index')->with('success', 'Carrera eliminada exitosamente.');
    }
}
