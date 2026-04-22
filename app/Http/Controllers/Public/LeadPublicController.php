<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use App\Models\Users\Career;
use App\Models\Users\CareerClassification;

class LeadPublicController extends Controller
{
    // Muestra el formulario público
    public function create()
    {
        $carreras = Career::orderBy('name')->get();
        $clasificaciones = CareerClassification::orderBy('name')->get();

        return view('public.inscripcion', compact('carreras', 'clasificaciones'));
    }

    // Guarda el lead que viene del formulario
public function store(Request $request)
{
    $request->validate([
        'tutor_curp' => 'required|string|max:18',
        'tutor_nombre' => 'required|string',
        'tutor_paterno' => 'required|string',
        'tutor_materno' => 'required|string',
        'telefono1' => 'required|numeric',
        'telefono2' => 'nullable|numeric',
        'tutor_email' => 'nullable|email',

        'alumno_curp' => 'required|string|max:18',
        'alumno_nombre' => 'required|string',
        'alumno_paterno' => 'required|string',
        'alumno_materno' => 'required|string',
        'carrera_id' => 'required|exists:careers,id',
    ]);

    $normalizeCurp = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
    $alumnoCurpN = $normalizeCurp($request->alumno_curp);

    $payload = [
        'tutor_curp' => strtoupper($request->tutor_curp),
        'tutor_nombre' => $request->tutor_nombre,
        'tutor_paterno' => $request->tutor_paterno,
        'tutor_materno' => $request->tutor_materno,
        'telefono1' => $request->telefono1,
        'telefono2' => $request->telefono2,
        'tutor_email' => $request->tutor_email,
        'alumno_curp' => $alumnoCurpN,
        'alumno_nombre' => $request->alumno_nombre,
        'alumno_paterno' => $request->alumno_paterno,
        'alumno_materno' => $request->alumno_materno,
        'carrera_id' => $request->carrera_id,
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
        'estado' => 'Prospecto',
        'fecha' => now()->toDateString(),
        'hora' => now()->toTimeString(),
    ]);

    return redirect()->back()->with('success', 'Registro enviado correctamente');
}

    // Muestra la página de inicio (Landing Page)
    public function landing()
    {
        return view('public.landing');
    }

    public function campus()
    {
        return view('public.campus');
    }
}

