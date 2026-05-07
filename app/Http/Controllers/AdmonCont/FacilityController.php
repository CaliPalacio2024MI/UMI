<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Facility;
use App\Models\AdmonCont\Materia;
use App\Models\Users\Career;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FacilityController extends Controller
{
    private function carrerasParaFormulario()
    {
        $institutionId = session('active_institution_id');

        return Career::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', (int) $institutionId))
            ->orderBy('name')
            ->get();
    }

    /**
     * Materias de la carrera (para selects en formularios de aula).
     */
    public function materiasPorCarrera(Request $request): JsonResponse
    {
        $request->validate([
            'career_id' => 'required|integer|exists:careers,id',
        ]);

        $institutionId = session('active_institution_id');
        $career = Career::query()->findOrFail((int) $request->career_id);

        if ($institutionId && (int) $career->institution_id !== (int) $institutionId) {
            abort(403);
        }

        $materias = Materia::query()
            ->where('career_id', $career->id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'clave']);

        return response()->json(['materias' => $materias]);
    }

    private function assertMateriaPerteneceACarrera(?int $careerId, ?string $nombreMateria): void
    {
        $nombreMateria = $nombreMateria !== null ? trim($nombreMateria) : '';
        if ($nombreMateria === '') {
            return;
        }
        if (! $careerId) {
            throw ValidationException::withMessages([
                'tipo_materia' => ['Seleccione una carrera para poder elegir la materia.'],
            ]);
        }
        $ok = Materia::query()
            ->where('career_id', $careerId)
            ->where('nombre', $nombreMateria)
            ->exists();
        if (! $ok) {
            throw ValidationException::withMessages([
                'tipo_materia' => ['La materia no corresponde a la carrera seleccionada.'],
            ]);
        }
    }

    //Clase Index
    public function index()
    {
        $data = Facility::orderedForHorarios()->with('career')->get();
        $carreras = $this->carrerasParaFormulario();

        return view('layouts.ControlAdmin.Infraestrucuta.index', compact('data', 'carreras'));
    }
    public function createForm(){
        return view('layouts.ControlAdmin.Infraestrucuta.components.create', [
            'carreras' => $this->carrerasParaFormulario(),
        ]);
    }
    public function store(Request $request){
        $request->merge([
            'career_id' => $request->filled('career_id') ? $request->career_id : null,
            'tipo_materia' => $request->filled('tipo_materia') ? trim((string) $request->tipo_materia) : null,
        ]);

        $institutionId = session('active_institution_id');
        $careerRule = ['nullable', 'integer'];
        if ($institutionId) {
            $careerRule[] = Rule::exists('careers', 'id')->where('institution_id', (int) $institutionId);
        } else {
            $careerRule[] = 'exists:careers,id';
        }

        $validated = $request->validate([
            'nombre_aula' => 'required|string|max:255',
            'career_id' => $careerRule,
            'tipo_materia' => 'nullable|string|max:100',
        ]);

        $this->assertMateriaPerteneceACarrera(
            isset($validated['career_id']) ? (int) $validated['career_id'] : null,
            $validated['tipo_materia'] ?? null
        );

        $nombre = trim($validated['nombre_aula']);

        $facility = Facility::create([
            'nombre_aula' => $nombre,
            'career_id' => $validated['career_id'] ?? null,
            'tipo_materia' => isset($validated['tipo_materia']) ? trim((string) $validated['tipo_materia']) : null,
        ]);

        // 3. Devolver una respuesta JSON de éxito (Axios lo espera)
        return response()->json([
            'message' => 'Aula creada exitosamente.',
            'data' => $facility, // Opcional: devolver los datos creados
        ], 201); 
    }

    public function show(Request $request, Facility $facility): View
    {
        $facility->load('career');
        $carreras = $this->carrerasParaFormulario();

        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Infraestrucuta.components.show_detail', compact('facility', 'carreras'));
        }

        return view('layouts.ControlAdmin.Infraestrucuta.show', compact('facility', 'carreras'));
    }

    public function edit(Request $request, Facility $facility): View
    {
        $facility->load('career');
        $carreras = $this->carrerasParaFormulario();

        if ($request->ajax()) {
            return view('layouts.ControlAdmin.Infraestrucuta.components.edit_form', compact('facility', 'carreras'));
        }

        return view('layouts.ControlAdmin.Infraestrucuta.edit', compact('facility', 'carreras'));
    }

    public function update(Request $request, Facility $facility)
    {
        $request->merge([
            'career_id' => $request->filled('career_id') ? $request->career_id : null,
            'tipo_materia' => $request->filled('tipo_materia') ? trim((string) $request->tipo_materia) : null,
        ]);

        $institutionId = session('active_institution_id');
        $careerRule = ['nullable', 'integer'];
        if ($institutionId) {
            $careerRule[] = Rule::exists('careers', 'id')->where('institution_id', (int) $institutionId);
        } else {
            $careerRule[] = 'exists:careers,id';
        }

        $validated = $request->validate([
            'nombre_aula' => 'required|string|max:255',
            'career_id' => $careerRule,
            'tipo_materia' => 'nullable|string|max:100',
        ]);

        $this->assertMateriaPerteneceACarrera(
            isset($validated['career_id']) ? (int) $validated['career_id'] : null,
            $validated['tipo_materia'] ?? null
        );

        $nombre = trim($validated['nombre_aula']);

        $facility->update([
            'nombre_aula' => $nombre,
            'career_id' => $validated['career_id'] ?? null,
            'tipo_materia' => isset($validated['tipo_materia']) ? trim((string) $validated['tipo_materia']) : null,
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
