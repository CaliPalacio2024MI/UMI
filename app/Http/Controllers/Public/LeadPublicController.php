<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use App\Models\Users\Career;
use App\Models\Users\CareerClassification;
use App\Models\FormularioCampo;
use App\Models\PublicFormConfig;
use Illuminate\Support\Facades\Storage;

class LeadPublicController extends Controller
{
    // Muestra el formulario público
    public function create()
    {
        $carreras = Career::orderBy('name')->get();
        $clasificaciones = CareerClassification::orderBy('name')->get();

        $camposAdicionales = FormularioCampo::where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $formConfig = PublicFormConfig::orderBy('orden')->get()->keyBy('campo');

        return view('public.inscripcion', compact('carreras', 'clasificaciones', 'camposAdicionales', 'formConfig'));
    }

    // Devuelve un hash de la configuración actual (para polling)
    public function config()
    {
        $hash = md5(
            PublicFormConfig::orderBy('orden')->get()->toJson() .
            FormularioCampo::where('activo', true)->orderBy('orden')->orderBy('id')->get()->toJson()
        );
        return response()->json(['hash' => $hash]);
    }

    // Devuelve solo el HTML de los campos del formulario (fragmento)
    public function fragment()
    {
        $carreras         = Career::orderBy('name')->get();
        $clasificaciones  = CareerClassification::orderBy('name')->get();
        $camposAdicionales = FormularioCampo::where('activo', true)->orderBy('orden')->orderBy('id')->get();
        $formConfig       = PublicFormConfig::orderBy('orden')->get()->keyBy('campo');
        return view('public._form_fields', compact('carreras', 'clasificaciones', 'camposAdicionales', 'formConfig'));
    }

    // Guarda el lead que viene del formulario
public function store(Request $request)
{
    $formConfig = PublicFormConfig::orderBy('orden')->get()->keyBy('campo');

    $baseRules = [
        'tutor_curp'    => ['string', 'max:18'],
        'tutor_nombre'  => ['string'],
        'tutor_paterno' => ['string'],
        'tutor_materno' => ['string'],
        'telefono1'     => ['numeric'],
        'telefono2'     => ['nullable', 'numeric'],
        'tutor_email'   => ['nullable', 'email'],
        'alumno_curp'   => ['string', 'max:18'],
        'alumno_nombre' => ['string'],
        'alumno_paterno'=> ['string'],
        'alumno_materno'=> ['string'],
        'carrera_id'    => ['exists:careers,id'],
    ];

    $rules = [];
    foreach ($baseRules as $campo => $fieldRules) {
        $cfg = $formConfig->get($campo);
        if ($cfg && !$cfg->activo && !$cfg->siempre_activo) continue;
        $required = $cfg ? ($cfg->obligatorio || $cfg->siempre_activo) : true;
        $rules[$campo] = array_merge([$required ? 'required' : 'nullable'], $fieldRules);
    }

    $request->validate($rules);

    $normalizeCurp = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
    $alumnoCurpN = $normalizeCurp($request->alumno_curp);

    // Recoger valores de campos dinámicos
    $camposActivos = FormularioCampo::where('activo', true)->get();
    $datosAdicionales = [];
    foreach ($camposActivos as $campo) {
        $nombre = $campo->nombre_campo;
        $key = "campo_{$nombre}";
        if ($campo->tipo === 'file' && $request->hasFile($key)) {
            $ruta = $request->file($key)->store("formulario_archivos/{$alumnoCurpN}", 'public');
            $datosAdicionales[$nombre] = $ruta;
        } elseif ($campo->tipo !== 'file' && $request->has($key)) {
            $datosAdicionales[$nombre] = $request->input($key);
        }
    }

    $payload = [
        'tutor_curp'    => strtoupper($request->tutor_curp ?? ''),
        'tutor_nombre'  => $request->tutor_nombre  ?? '',
        'tutor_paterno' => $request->tutor_paterno ?? '',
        'tutor_materno' => $request->tutor_materno ?? '',
        'telefono1'     => $request->telefono1     ?? '',
        'telefono2'     => $request->telefono2,
        'tutor_email'   => $request->tutor_email,
        'alumno_curp'   => $alumnoCurpN,
        'alumno_nombre' => $request->alumno_nombre  ?? '',
        'alumno_paterno'=> $request->alumno_paterno ?? '',
        'alumno_materno'=> $request->alumno_materno ?? '',
        'carrera_id'    => $request->carrera_id,
        'datos_adicionales' => !empty($datosAdicionales) ? $datosAdicionales : null,
    ];

    // Mismo aspirante (misma CURP): actualizar el lead existente, no duplicar fila.
    $existente = Lead::query()
        ->whereRaw('UPPER(REPLACE(TRIM(IFNULL(alumno_curp, \'\')), \' \', \'\')) = ?', [$alumnoCurpN])
        ->orderByDesc('id')
        ->first();

    if ($existente) {
        $existente->update($payload);

        return redirect()->back()->with('success', 'Registro actualizado correctamente.');
    }

    $lead = Lead::create(array_merge($payload, [
        'origen' => 'formulario_publico',
        'clasificacion' => 'Prospecto',
    ]));

    $lead->seguimientos()->create([
        'estado' => 'Prospecto frío',
        'fecha' => now()->toDateString(),
        'hora' => now()->toTimeString(),
    ]);

    return redirect()->back()->with('success', 'Registro enviado correctamente');
}

}

