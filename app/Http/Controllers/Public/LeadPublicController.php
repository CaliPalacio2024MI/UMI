<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadPublicController extends Controller
{
    // Muestra el formulario público
    public function create()
    {
        return view('public.inscripcion');
    }

    // Guarda el lead que viene del formulario
    public function store(Request $request)
    {
        $request->validate([
            'tutor_nombre' => 'required|string',
            'tutor_paterno' => 'required|string',
            'tutor_materno' => 'required|string',
            'telefono1' => 'required|numeric',
            'alumno_nombre' => 'required|string',
            'alumno_paterno' => 'required|string'
        ]);

        Lead::create([
            'tutor_nombre' => $request->tutor_nombre,
            'tutor_paterno' => $request->tutor_paterno,
            'tutor_materno' => $request->tutor_materno,
            'telefono1' => $request->telefono1,
            'telefono2' => $request->telefono2,
            'alumno_nombre' => $request->alumno_nombre,
            'alumno_paterno' => $request->alumno_paterno,
            'alumno_materno' => $request->alumno_materno,
            'rfc' => strtoupper($request->rfc),
            'curp' => strtoupper($request->curp),
            'origen' => 'formulario_publico',
            'clasificacion' => 'nuevo'
        ]);

        return redirect()->back()->with('success', 'Registro enviado correctamente');
    }
}
