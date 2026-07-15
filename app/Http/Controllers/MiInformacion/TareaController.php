<?php

namespace App\Http\Controllers\MiInformacion;

class TareaController extends AsignacionController
{
    protected string $tipo = 'tarea';
    protected string $routePrefix = 'MiInformacion.tareas';
    protected string $routePrefixClase = 'MiInformacion.clases.tareas';
    protected string $view = 'layouts.MiInformacion.tareas';
    protected string $viewDocente = 'layouts.MiInformacion.tareas_docente';
    protected string $viewEntregas = 'layouts.MiInformacion.tareas_entregas';
    protected string $tituloPagina = 'TAREAS';
}
