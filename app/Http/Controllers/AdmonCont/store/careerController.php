<?php

namespace App\Http\Controllers\AdmonCont\store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
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
            ? CareerClassification::where('institution_id', $institutionId)->withCount('careers')->with(['careers' => function($q) { $q->select('id','name','career_classification_id','semesters')->withCount('materias'); }])->orderBy('name')->get()
            : CareerClassification::withCount('careers')->with(['careers' => function($q) { $q->select('id','name','career_classification_id','semesters')->withCount('materias'); }])->orderBy('name')->get();
        // Evita caché del listado (p. ej. GET repetido ?modal=success tras varios DELETE);
        // si el HTML sale de caché, el script del modal de éxito puede no ejecutarse y no verías el aviso.
        return response()
            ->view('layouts.ControlAdmin.Carreras.index', compact('careers', 'careerClassifications'))
            ->header('Cache-Control', 'private, no-store, must-revalidate')
            ->header('Pragma', 'no-cache');
    }
    public function careersByClassification(CareerClassification $careerClassification)
{
    $institutionId = session('active_institution_id');
    $institutionId = $institutionId ? (int) $institutionId : null;

    // Carreras de ESTA clasificación (respetando institución)
    $careers = Career::where('career_classification_id', $careerClassification->id)
        ->when($institutionId, fn($q) => $q->where('institution_id', $institutionId))
        ->with('classification')
        ->withCount('materias')
        ->orderBy('created_at', 'desc')
        ->get();

    // Clasificaciones para los modales de crear/editar carrera
   $careerClassifications = $institutionId
            ? CareerClassification::where('institution_id', $institutionId)->withCount('careers')->with('careers:id,name,career_classification_id')->orderBy('name')->get()
            : CareerClassification::withCount('careers')->with('careers:id,name,career_classification_id')->orderBy('name')->get();
    return response()
        ->view('layouts.ControlAdmin.Carreras.byClassification', compact('careers', 'careerClassifications', 'careerClassification'))
        ->header('Cache-Control', 'private, no-store, must-revalidate')
        ->header('Pragma', 'no-cache');
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
        $institutionId = session('active_institution_id');
        $institutionId = $institutionId ? (int) $institutionId : null;

        $carreras = $institutionId
            ? Career::where('institution_id', $institutionId)->orderBy('name')->get()
            : Career::orderBy('name')->get();

        if (!$carreras->firstWhere('id', $carrera->id)) {
            $carreras = $carreras->push($carrera)->sortBy('name')->values();
        }

        $materias = $carrera->materias()
            ->orderBy('semestre')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $totalSemesters = max(1, (int) ($carrera->semesters ?? 1));

        $grouped = $materias->groupBy(function ($m) use ($totalSemesters) {
            $s = (int) ($m->semestre ?? 1);

            return max(1, min($totalSemesters, $s));
        });

        $porSemestre = [];
        for ($s = 1; $s <= $totalSemesters; $s++) {
            $porSemestre[$s] = $grouped->get($s, collect())->values();
        }

        $totalMaterias = $materias->count();
        $totalCreditos = (int) $materias->sum(fn ($m) => (int) ($m->creditos ?? 0));

        $semestreLabels = [
            1 => '1er Semestre',
            2 => '2do Semestre',
            3 => '3er Semestre',
            4 => '4to Semestre',
            5 => '5to Semestre',
            6 => '6to Semestre',
            7 => '7mo Semestre',
        ];

        $clasificaciones = $institutionId
            ? \App\Models\Users\CareerClassification::where('institution_id', $institutionId)->orderBy('name')->get(['id', 'name'])
            : \App\Models\Users\CareerClassification::orderBy('name')->get(['id', 'name']);

        return view('layouts.ControlAdmin.Carreras.reticula', compact(
            'carrera',
            'carreras',
            'clasificaciones',
            'porSemestre',
            'totalSemesters',
            'totalMaterias',
            'totalCreditos',
            'semestreLabels'
        ));
    }

    public function store(Request $request)
    {
        $institutionId = session('active_institution_id');
        if (!$institutionId) {
            return redirect()->back()->with('error', 'No hay institución activa. Seleccione una institución.');
        }
        $institutionId = (int) $institutionId;

        $request->merge([
            'official_id' => trim((string) $request->input('official_id', '')),
            'name' => trim((string) $request->input('name', '')),
            'description' => trim((string) $request->input('description', '')),
        ]);

        $request->validate([
            'name'         => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'official_id'  => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\/\-\s]+$/',
                Rule::unique('careers', 'official_id'),
            ],
            'description' => 'nullable|string|max:2000',
            'type'         => 'required|in:Presencial,En linea',
            'semesters'    => 'required|integer|min:1',
            'career_classification_id' => [
                'required',
                'integer',
                Rule::exists('career_classifications', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'pricing_mode' => 'required|in:uniform,per_month',
            'monto_mensualidad' => 'required_if:pricing_mode,uniform|nullable|numeric|min:0|max:99999999.99',
            'monthly_prices' => 'nullable|array',
            'monthly_prices.*' => 'nullable|numeric|min:0|max:99999999.99',
            'porcentaje_cargo_moratorio' => 'required|numeric|min:0|max:100',
        ], [
            'name.required'         => 'El nombre de la carrera es obligatorio.',
            'name.regex'            => 'El nombre solo puede contener letras y espacios.',
            'official_id.required'  => 'El RVOE es obligatorio.',
            'official_id.regex'     => 'El RVOE solo puede contener letras, números, espacios, diagonal y guion.',
            'official_id.unique'    => 'Ya existe una carrera con ese número de acuerdo RVOE. Use otro valor.',
            'description.required' => 'La descripción es obligatoria.',
            'type.required'         => 'Debe seleccionar la modalidad.',
            'semesters.required'    => 'Debe seleccionar el número de semestres.',
            'career_classification_id.required' => 'Debe seleccionar primero una clasificación.',
            'pricing_mode.required' => 'Debe seleccionar cómo se definirá la mensualidad.',
            'monto_mensualidad.required_if' => 'El monto de mensualidad es obligatorio cuando se usa el mismo precio para todos.',
            'porcentaje_cargo_moratorio.required' => 'El porcentaje de cargo moratorio es obligatorio.',
        ]);

        $sem = (int) $request->semesters;
        $pricingMode = $request->input('pricing_mode', 'uniform');
        $monthlyPrices = null;
        $monto = null;
        $porcentajeCargoMoratorio = round((float) $request->input('porcentaje_cargo_moratorio', 0), 2);

        if ($pricingMode === 'per_month') {
            $monthlyPrices = [];
            for ($month = 1; $month <= $sem; $month++) {
                $raw = $request->input("monthly_prices.$month");
                if ($raw === null || $raw === '' || !is_numeric($raw) || (float) $raw < 0) {
                    return redirect()->back()
                        ->withErrors(["monthly_prices.$month" => "Ingrese el precio del mes $month."])
                        ->withInput();
                }
                $monthlyPrices[(string) $month] = round((float) $raw, 2);
            }
            $precioTotal = round(array_sum($monthlyPrices), 2);
        } else {
            $monto = round((float) $request->monto_mensualidad, 2);
            $precioTotal = round($monto * $sem, 2);
        }
        $cargoMonetario = round($precioTotal * ($porcentajeCargoMoratorio / 100), 2);
        // Fecha de vencimiento moratorio: asignada por el sistema (31 de diciembre del año siguiente al alta).
        $fechaVencimientoMoratorio = Carbon::now()->addYear()->endOfYear()->toDateString();

        Career::create([
            'name'           => $request->name,
            'official_id'    => $request->official_id,
            'description1'  => $request->description,
            'description2'  => null,
            'description3'  => null,
            'type'           => $request->type,
            'semesters'      => $sem,
            'pricing_mode'   => $pricingMode,
            'monthly_prices' => $monthlyPrices,
            'porcentaje_cargo_moratorio' => $porcentajeCargoMoratorio,
            'institution_id' => $institutionId,
            'career_classification_id' => $request->filled('career_classification_id')
                ? (int) $request->career_classification_id
                : null,
            'monto_mensualidad' => $monto,
            'cargo_monetario' => $cargoMonetario,
            'fecha_vencimiento_moratorio' => $fechaVencimientoMoratorio,
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
        $request->merge([
            'official_id' => trim((string) $request->input('official_id', '')),
            'name' => trim((string) $request->input('name', '')),
            'description' => trim((string) $request->input('description', '')),
        ]);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\s]+$/u',
                Rule::unique('careers', 'name')
                    ->ignore($carrera->id)
                    ->where('institution_id', $carrera->institution_id),
            ],
            'official_id'  => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\/\-\s]+$/',
                Rule::unique('careers', 'official_id')->ignore($carrera->id),
            ],
            'type'         => 'required|in:Presencial,En linea',
            'semesters'    => 'required|integer|min:1',
            'description' => 'nullable|string|max:2000',
            'career_classification_id' => [
                'required',
                'integer',
                Rule::exists('career_classifications', 'id')->where(fn ($q) => $q->where('institution_id', $carrera->institution_id)),
            ],
            'pricing_mode' => 'required|in:uniform,per_month',
            'monto_mensualidad' => 'required_if:pricing_mode,uniform|nullable|numeric|min:0|max:99999999.99',
            'monthly_prices' => 'nullable|array',
            'monthly_prices.*' => 'nullable|numeric|min:0|max:99999999.99',
            'porcentaje_cargo_moratorio' => 'required|numeric|min:0|max:100',
        ], [
            'name.required'   => 'El nombre de la carrera es obligatorio.',
            'name.regex'      => 'El nombre solo puede contener letras y espacios.',
            'name.unique'    => 'Ya existe una carrera con ese nombre.',
            'official_id.required' => 'El RVOE es obligatorio.',
            'official_id.regex' => 'El RVOE solo puede contener letras, números, espacios, diagonal y guion.',
            'official_id.unique'   => 'Ya existe otra carrera con ese número de acuerdo RVOE. Use otro valor.',
            'career_classification_id.required' => 'Debe seleccionar primero una clasificación.',
            'pricing_mode.required' => 'Debe seleccionar cómo se definirá la mensualidad.',
            'monto_mensualidad.required_if' => 'El monto de mensualidad es obligatorio cuando se usa el mismo precio para todos.',
            'porcentaje_cargo_moratorio.required' => 'El porcentaje de cargo moratorio es obligatorio.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('edit_career_id', $carrera->id);
        }

        $data = $request->only([
            'name', 'official_id',
            'type', 'semesters',
        ]);
        $data['career_classification_id'] = $request->filled('career_classification_id')
            ? (int) $request->career_classification_id
            : null;
        $data['description1'] = $request->filled('description') ? $request->description : null;
        $data['description2'] = null;
        $data['description3'] = null;
        $sem = (int) $request->semesters;
        $pricingMode = $request->input('pricing_mode', 'uniform');
        $porcentajeCargoMoratorio = round((float) $request->input('porcentaje_cargo_moratorio', 0), 2);
        $data['pricing_mode'] = $pricingMode;
        $data['porcentaje_cargo_moratorio'] = $porcentajeCargoMoratorio;

        if ($pricingMode === 'per_month') {
            $monthlyPrices = [];
            for ($month = 1; $month <= $sem; $month++) {
                $raw = $request->input("monthly_prices.$month");
                if ($raw === null || $raw === '' || !is_numeric($raw) || (float) $raw < 0) {
                    return redirect()->back()
                        ->withErrors(["monthly_prices.$month" => "Ingrese el precio del mes $month."])
                        ->withInput()
                        ->with('edit_career_id', $carrera->id);
                }
                $monthlyPrices[(string) $month] = round((float) $raw, 2);
            }
            $precioTotal = round(array_sum($monthlyPrices), 2);
            $data['monthly_prices'] = $monthlyPrices;
            $data['monto_mensualidad'] = null;
            $data['cargo_monetario'] = round($precioTotal * ($porcentajeCargoMoratorio / 100), 2);
        } else {
            $monto = $request->filled('monto_mensualidad') ? round((float) $request->monto_mensualidad, 2) : null;
            $precioTotal = $monto !== null ? round($monto * $sem, 2) : 0;
            $data['monthly_prices'] = null;
            $data['monto_mensualidad'] = $monto;
            $data['cargo_monetario'] = $monto !== null ? round($precioTotal * ($porcentajeCargoMoratorio / 100), 2) : null;
        }
        if ($carrera->fecha_vencimiento_moratorio === null) {
            $data['fecha_vencimiento_moratorio'] = Carbon::now()->addYear()->endOfYear()->toDateString();
        }
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
  public function toggleLanding(CareerClassification $careerClassification)
{
    $careerClassification->visible_landing = !$careerClassification->visible_landing;
    $careerClassification->save();

    return redirect()
        ->route('control.careers.index', ['modal' => 'success'])
        ->with('success', 'Visibilidad de la clasificación en Landing actualizada.');
}
    public function toggleCareerLanding(Career $carrera)
{
    $carrera->visible_landing = !$carrera->visible_landing;
    $carrera->save();

    return redirect()
        ->route('control.careers.index', ['modal' => 'success'])
        ->with('success', 'Visibilidad de la carrera en Landing actualizada.');
}

    public function toggleCareerActive(Career $carrera)
    {
        if (!request()->user()->hasAnyRole(['master'])) {
            abort(403);
        }
        $carrera->is_active = !$carrera->is_active;
        $carrera->save();

        $msg = $carrera->is_active ? 'Carrera habilitada.' : 'Carrera deshabilitada.';
        return redirect()->back()->with('success', $msg);
    }

    public function toggleClassificationActive(CareerClassification $careerClassification)
    {
        if (!request()->user()->hasAnyRole(['master'])) {
            abort(403);
        }
        $careerClassification->is_active = !$careerClassification->is_active;
        $careerClassification->save();

        $msg = $careerClassification->is_active ? 'Clasificación habilitada.' : 'Clasificación deshabilitada.';
        return redirect()->back()->with('success', $msg);
    }
}

