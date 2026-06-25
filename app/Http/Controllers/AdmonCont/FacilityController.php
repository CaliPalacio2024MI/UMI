<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Facility;
use App\Models\AdmonCont\Materia;
use App\Models\Users\Career;
use App\Models\Users\CareerClassification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FacilityController extends Controller
{
    private function clasificacionesParaFormulario()
    {
        $institutionId = session('active_institution_id');

        return CareerClassification::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', (int) $institutionId))
            ->orderBy('name')
            ->get();
    }

    private function carrerasParaFormulario()
    {
        $institutionId = session('active_institution_id');

        return Career::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', (int) $institutionId))
            ->orderBy('name')
            ->get();
    }

    /**
     * Materias de varias carreras (AJAX - checkboxes).
     */
    public function materiasPorCarrera(Request $request): JsonResponse
    {
        $request->validate([
            'career_ids'   => 'required|array|min:1',
            'career_ids.*' => 'integer|exists:careers,id',
        ]);

        $institutionId = session('active_institution_id');

        if ($institutionId) {
            $valid = Career::whereIn('id', $request->career_ids)
                ->where('institution_id', (int) $institutionId)
                ->pluck('id');

            if ($valid->count() !== count($request->career_ids)) {
                abort(403);
            }
        }

        $materias = Materia::query()
            ->whereIn('career_id', $request->career_ids)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'clave', 'career_id']);

        return response()->json(['materias' => $materias]);
    }

    public function index()
    {
        $data = Facility::orderedForHorarios()->with(['classification', 'careers', 'materias'])->get();
        $carreras = $this->carrerasParaFormulario();
        $clasificaciones = $this->clasificacionesParaFormulario();

        return view('layouts.ControlAdmin.Infraestrucuta.index', compact('data', 'carreras', 'clasificaciones'));
    }

    public function createForm()
    {
        return view('layouts.ControlAdmin.Infraestrucuta.components.create', [
            'carreras'        => $this->carrerasParaFormulario(),
            'clasificaciones' => $this->clasificacionesParaFormulario(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_aula'              => 'required|string|max:255',
            'career_classification_id' => 'nullable|integer|exists:career_classifications,id',
            'career_ids'               => 'nullable|array',
            'career_ids.*'             => 'integer|exists:careers,id',
            'materia_ids'              => 'nullable|array',
            'materia_ids.*'            => 'integer|exists:materias,id',
        ]);

        $facility = Facility::create([
            'nombre_aula'              => trim($validated['nombre_aula']),
            'career_classification_id' => $validated['career_classification_id'] ?? null,
        ]);

        if (!empty($validated['career_ids'])) {
            $facility->careers()->sync($validated['career_ids']);
        }

        if (!empty($validated['materia_ids'])) {
            $facility->materias()->sync($validated['materia_ids']);
        }

        return response()->json([
            'message' => 'Aula creada exitosamente.',
            'data'    => $facility->load(['careers', 'materias']),
        ], 201);
    }

    public function show(Request $request, Facility $facility): View
    {
        $facility->load(['classification', 'careers', 'materias']);
        $carreras = $this->carrerasParaFormulario();
        $clasificaciones = $this->clasificacionesParaFormulario();

        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Infraestrucuta.components.show_detail', compact('facility', 'carreras', 'clasificaciones'));
        }

        return view('layouts.ControlAdmin.Infraestrucuta.show', compact('facility', 'carreras', 'clasificaciones'));
    }

    public function edit(Request $request, Facility $facility): View
    {
        $facility->load(['classification', 'careers', 'materias']);
        $carreras = $this->carrerasParaFormulario();
        $clasificaciones = $this->clasificacionesParaFormulario();

        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Infraestrucuta.components.edit_form', compact('facility', 'carreras', 'clasificaciones'));
        }

        return view('layouts.ControlAdmin.Infraestrucuta.edit', compact('facility', 'carreras', 'clasificaciones'));
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'nombre_aula'              => 'required|string|max:255',
            'career_classification_id' => 'nullable|integer|exists:career_classifications,id',
            'career_ids'               => 'nullable|array',
            'career_ids.*'             => 'integer|exists:careers,id',
            'materia_ids'              => 'nullable|array',
            'materia_ids.*'            => 'integer|exists:materias,id',
        ]);

        $facility->update([
            'nombre_aula'              => trim($validated['nombre_aula']),
            'career_classification_id' => $validated['career_classification_id'] ?? null,
        ]);

        $facility->careers()->sync($validated['career_ids'] ?? []);
        $facility->materias()->sync($validated['materia_ids'] ?? []);

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