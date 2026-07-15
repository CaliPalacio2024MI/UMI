<?php

namespace App\Http\Controllers\MiInformacion;

class EvaluacionController extends AsignacionController
{
    protected string $tipo = 'evaluacion';
    protected string $routePrefix = 'MiInformacion.evaluaciones';
    protected string $routePrefixClase = 'MiInformacion.clases.evaluaciones';
    protected string $view = 'layouts.MiInformacion.tareas';
    protected string $viewDocente = 'layouts.MiInformacion.tareas_docente';
    protected string $viewEntregas = 'layouts.MiInformacion.tareas_entregas';
    protected string $tituloPagina = 'EVALUACIONES';
}
