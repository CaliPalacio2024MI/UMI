<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Users\Career;
use App\Models\Users\CareerClassification;

Route::get('/carreras', function () {
    $query = Career::where('visible_landing', true)
        ->whereHas('classification', function ($q) {
            $q->where('visible_landing', true);
        });

    if (request('tipo')) {
        $query->whereHas('classification', function ($q) {
            $q->where('name', request('tipo'));
        });
    }

    return $query->get();
});

Route::get('/carreras/{id}/materias', function ($id) {
    $career = Career::findOrFail($id);

    $materias = \App\Models\AdmonCont\Materia::where('career_id', $id)
        ->select('id', 'nombre', 'semestre', 'creditos', 'type', 'descripcion', 'objetivo', 'temario', 'infografia')
        ->orderBy('semestre')
        ->orderBy('nombre')
        ->get()
        ->groupBy('semestre');

    return response()->json([
        'carrera' => $career->name ?? $career->nombre,
        'materias_por_semestre' => $materias,
        'total_semestres' => $materias->keys()->max() ?? 0,
    ]);
});
