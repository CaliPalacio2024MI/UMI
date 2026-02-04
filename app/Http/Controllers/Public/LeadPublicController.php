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
            'nombre' => 'required',
            'apellido_paterno' => 'required',
            'telefono_1' => 'required'
        ]);

        Lead::create([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'telefono_1' => $request->telefono_1,
            'telefono_2' => $request->telefono_2,
            'correo' => $request->correo,
            'origen' => 'formulario_publico',
            'clasificacion' => 'nuevo'
        ]);

        return redirect()->back()->with('success', 'Registro enviado correctamente');
    }
}
