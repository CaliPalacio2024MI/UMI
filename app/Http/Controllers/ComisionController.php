<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comision;

class ComisionController extends Controller
{
    public function store(Request $request)
    {
        $comision = Comision::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $comision
        ]);
    }

     public function index()
    {
        return response()->json(App\Models\Comision::all());
    }

    public function detalle($ctp)
    {
        $detalle = Comision::where('ctp_id', $ctp)
            ->with('lead')
            ->get()
            ->map(function($c) {
                return [
                    'clasificacion' => $c->clasificacion,
                    'alumno'        => $c->lead->nombre ?? 'Sin nombre',
                    'total'         => $c->total ?? 0,
                ];
            });

        return response()->json(['detalle' => $detalle]);
    }
}
