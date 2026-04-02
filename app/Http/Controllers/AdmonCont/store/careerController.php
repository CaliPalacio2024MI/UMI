<?php

namespace App\Http\Controllers\AdmonCont\store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
//Modelos
use App\Models\Users\Career;
use App\Models\Users\CareerClassification;

class careerController extends Controller
{
    public function index()
    {
        $institutionId = session('active_institution_id');
        $institutionId = $institutionId ? (int) $institutionId : null;

        $careers = $institutionId
            ? Career::where('institution_id', $institutionId)->with('classification')->orderBy('created_at', 'desc')->get()
            : Career::with('classification')->orderBy('created_at', 'desc')->get();

        $careerClassifications = $institutionId
            ? CareerClassification::where('institution_id', $institutionId)->orderBy('name')->get()
            : CareerClassification::orderBy('name')->get();

        return view('layouts.ControlAdmin.Carreras.index', compact('careers', 'careerClassifications'));
    }
    public function create()
    {
        $institutionId = session('active_institution_id');
        $institutionId = $institutionId ? (int) $institutionId : null;
        $careerClassifications = $institutionId
            ? CareerClassification::where('institution_id', $institutionId)->orderBy('name')->get()
            : CareerClassification::orderBy('name')->get();

        return view('layouts.ControlAdmin.Carreras.create', compact('careerClassifications'));
    }

    public function reticula(Career $carrera)
    {
        return view('layouts.ControlAdmin.Carreras.reticula', compact('carrera'));
    }
    public function store(Request $request)
    {
        $institutionId = session('active_institution_id');
        if (!$institutionId) {
            return redirect()->back()->with('error', 'No hay institución activa. Seleccione una institución.');
        }
        $institutionId = (int) $institutionId;

        $request->validate([
            'name'         => 'required|string|max:255',
            'official_id'  => 'required|string|max:255',
            'description1' => 'required|string|max:500',
            'description2' => 'required|string|max:500',
            'description3' => 'required|string|max:500',
            'type'         => 'required|in:Presencial,En linea',
            'semesters'    => 'required|integer|min:1|max:8',
            'career_classification_id' => [
                'nullable',
                'integer',
                Rule::exists('career_classifications', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
        ], [
            'name.required'         => 'El nombre de la carrera es obligatorio.',
            'official_id.required'  => 'El RVOE es obligatorio.',
            'description1.required' => 'Profesionalización y empleabilidad es obligatorio.',
            'description2.required'  => 'Objetivo General es obligatorio.',
            'description3.required' => 'Elige Ser es obligatorio.',
            'type.required'         => 'Debe seleccionar la modalidad.',
            'semesters.required'    => 'Debe seleccionar el número de semestres.',
        ]);

        Career::create([
            'name'           => $request->name,
            'official_id'    => $request->official_id,
            'description1'  => $request->description1,
            'description2'  => $request->description2,
            'description3'  => $request->description3,
            'type'           => $request->type,
            'semesters'      => $request->semesters,
            'institution_id' => $institutionId,
            'career_classification_id' => $request->filled('career_classification_id')
                ? (int) $request->career_classification_id
                : null,
        ]);

        return redirect()
            ->route('control.careers.index', ['modal' => 'success'], 303)
            ->with('success', 'Carrera creada exitosamente.');
    }

    public function storeClassification(Request $request)
    {
        if (!$request->user()->hasAnyRole(['master'])) {
            abort(403);
        }

        $institutionId = session('active_institution_id');
        if (!$institutionId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No hay institución activa. Seleccione una institución.',
                ], 422);
            }

            return redirect()->back()->with('error', 'No hay institución activa. Seleccione una institución.');
        }
        $institutionId = (int) $institutionId;

        $request->validate([
            'classification_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('career_classifications', 'name')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
        ], [
            'classification_name.required' => 'El nombre de la clasificación es obligatorio.',
            'classification_name.unique' => 'Ya existe una clasificación con ese nombre.',
        ]);

        $classification = CareerClassification::create([
            'name' => $request->classification_name,
            'institution_id' => $institutionId,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'classification' => [
                    'id' => $classification->id,
                    'name' => $classification->name,
                    'destroy_url' => route('control.careers.classifications.destroy', $classification),
                ],
            ]);
        }

        return redirect()
            ->route('control.careers.index', ['modal' => 'success'])
            ->with('success', 'Clasificación agregada correctamente.');
    }

    public function destroyClassification(Request $request, CareerClassification $careerClassification)
    {
        if (!$request->user()->hasAnyRole(['master'])) {
            abort(403);
        }

        $institutionId = session('active_institution_id');
        $institutionId = $institutionId ? (int) $institutionId : null;
        if (!$institutionId || $careerClassification->institution_id !== $institutionId) {
            abort(403);
        }

        $careerClassification->delete();

        return redirect()
            ->route('control.careers.index', ['modal' => 'success'])
            ->with('success', 'Clasificación eliminada correctamente.');
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
            'career_classification_id' => [
                'nullable',
                'integer',
                Rule::exists('career_classifications', 'id')->where(fn ($q) => $q->where('institution_id', $carrera->institution_id)),
            ],
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

        $data = $request->only([
            'name', 'official_id', 'description1', 'description2', 'description3',
            'type', 'semesters',
        ]);
        $data['career_classification_id'] = $request->filled('career_classification_id')
            ? (int) $request->career_classification_id
            : null;
        $carrera->update($data);

        return redirect()
            ->route('control.careers.index', ['modal' => 'success'])
            ->with('success', 'Departamento actualizado exitosamente.');
    }
    //Eliminar
    //1. Modulo a usar
    //2. Clase(debe ser igual al nombre de la tabla pero en singular)
    //                      1.     2.
    public function destroy(Career $carrera)
    {
        // Eliminar en cascada todo lo que depende de la carrera (en BD: academic_profiles → set null; materias y horario_clases → cascade)
        $carrera->enrollments()->delete();
        \App\Models\AdmonCont\HorarioClase::where('career_id', $carrera->id)->delete();

        $carrera->delete();
        return redirect()
            ->route('control.careers.index', ['modal' => 'success'])
            ->with('success', 'Carrera eliminada exitosamente.');
    }
}
