@extends('layouts.app')

@section('title', 'Clases - Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/css/control_admin/base.css', 'resources/js/app.js'])

@push('styles')
<style>
#carrera_id,
#semestre_id,
#materia_id,
#dia_semana,
#clase_id {
    -webkit-appearance: none;
    appearance: none;
    padding-right: 36px;
    text-align: center;
    text-align-last: center;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 14 14'%3E%3Cpath fill='%23666' d='M7 10L2 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;
}
#form-filtros #carrera_id {
    text-align: center !important;
    text-align-last: center !important;
}
#form-filtros #semestre_id {
    text-align: center !important;
    text-align-last: center !important;
}
#form-filtros select#semestre_id.semestre-placeholder,
#form-filtros select#semestre_id.placeholder {
    color: #ACACAC !important;
}
#form-filtros select#semestre_id:not(.semestre-placeholder):not(.placeholder) {
    color: #212529;
}
/* Materia: texto centrado */
#form-filtros #materia_id {
    text-align: center !important;
    text-align-last: center !important;
}
/* Horarios (día de la semana): label color #b8860b */
#form-filtros label[for="dia_semana"],
#form-filtros label[for="clase_id"] {
    color: #b8860b !important;
}
#form-filtros #dia_semana,
#form-filtros #clase_id {
    text-align: center !important;
    text-align-last: center !important;
}
#form-filtros select.select-horarios {
    max-width: 100%;
    min-width: 0;
}
.clases-layout { display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap; }
.schedule-table { flex: 1; min-width: 0; }
.panel-derecho { flex: 1; min-width: 280px; border: 1px solid #ddd; border-radius: 12px; padding: 1rem; background: #fafafa; min-height: 200px; }
.panel-derecho h6 { margin: 0 0 0.75rem; color: #b8860b; font-weight: 600; }
.tabla-base { border-collapse: collapse; width: 100%; min-width: 320px; }
.tabla-base th, .tabla-base td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
.encabezado-tabla { background: #f5f5f5; color: #333; font-weight: 600; }
.cuerpo-tabla tr:hover { background: #f9f9f9; }
.btn-agregar-clase { border-radius: 25px; padding: 8px 20px; display: inline-flex; align-items: center; gap: 8px; }
.checkbox-cell { width: 44px; text-align: center; }
.tabla-clases-wrapper .col-materia { white-space: normal; word-wrap: break-word; min-height: 2.8em; line-height: 1.5; width: 55px; max-width: 55px; vertical-align: top; padding: 0.4em 0.5em; color: #333; text-align: center !important; }
.tabla-clases-wrapper th.col-materia { width: 55px; max-width: 55px; text-align: center !important; vertical-align: middle !important; }
.tabla-clases-wrapper .col-acciones { width: 100px; max-width: 100px; padding: 0.4em 0.5em; }
.tabla-clases-wrapper th.col-acciones { width: 100px; max-width: 100px; }
.tabla-clases-wrapper .col-matricula { width: 110px; max-width: 110px; }
.tabla-clases-wrapper th.col-matricula { width: 110px; max-width: 110px; }
.tabla-clases-wrapper .col-carrera { width: 100px; max-width: 100px; }
.tabla-clases-wrapper th.col-carrera { width: 100px; max-width: 100px; }
/* Columna Carrera en tabla de filtros: contenido en 2 líneas (excepto encabezado) */
#form-filtros td.col-carrera {
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.4;
    min-height: 2.8em;
    vertical-align: top;
    padding: 0.4em 0.5em;
}
.col-nombre-dos-lineas { line-height: 1.5; min-height: 2.6em; vertical-align: top; color: #333; }
.btn-accion-circular { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; padding: 0; border: none; border-radius: 50%; background-color: #fff; color: #001f3f; box-shadow: 0 1px 3px rgba(0,0,0,0.2); text-decoration: none; transition: box-shadow 0.2s, background-color 0.2s; }
.btn-accion-circular:hover { background-color: #f0f0f0; color: #001f3f; box-shadow: 0 2px 5px rgba(0,0,0,0.25); }
.btn-accion-circular svg,
.btn-accion-circular .btn-accion-icon { width: 18px; height: 18px; display: block; object-fit: contain; }
.col-acciones { text-align: center; vertical-align: middle !important; }
#form-filtros .add-time-slot-btn--selected { background-color: #198754 !important; box-shadow: 0 2px 6px rgba(25,135,84,0.45); }
/* Botón seleccionar clase: gris claro, sin azul marino (solo en form-filtros Clases) */
#form-filtros .add-time-slot-btn {
    background-color: #c0c0c0 !important;
    color: #333 !important;
}
#form-filtros .add-time-slot-btn:hover {
    background-color: #a8a8a8 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
#form-filtros .add-time-slot-btn .add-time-slot-btn__icon,
#form-filtros .add-time-slot-btn svg { stroke: #fff; }
.btn-seleccionar-todo-clases { color: #ACACAC !important; }
.btn-seleccionar-todo-clases:hover { color: #888 !important; }
.col-acciones-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    min-height: 0;
    margin: 0 auto;
}
.col-acciones .col-acciones-wrap { padding: 0; }
.caja-gris-filtros .tabla-base td.col-acciones { text-align: center; }
.caja-gris-filtros .tabla-base td.col-acciones .col-acciones-wrap { align-items: center; }
#horarios-materia-box .btn-horario-seleccionar:hover { background: #9a7210 !important; color: #fff; box-shadow: 0 3px 8px rgba(184,134,11,0.45); }
#horarios-materia-box .horario-item--selected { background: #e8eef3; border: 2px solid #001f3f; padding: 0.6rem !important; }
#horarios-materia-box .btn-horario-seleccionar--selected:hover { background: #001a33 !important; color: #fff; box-shadow: 0 3px 8px rgba(0,31,63,0.45); }
/* Contenedores con scroll horizontal en tablas */
.filtros-tabla-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.clases-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
/* Columna izquierda: más ancho para el select; un poco más a la izquierda */
#form-filtros .filtros-col-izq { max-width: 560px !important; }
#form-filtros .filtros-col-izq .caja-gris-filtros { max-width: 560px; }
#form-filtros .filtros-col-izq .filtros-tabla-scroll { max-width: 560px; }
/* Cajas grises de filtros */
.caja-gris-filtros { background-color: #e8e8e8; border-radius: 8px; padding: 0 0.5rem 0.5rem 0.5rem; min-height: 60px; margin-top: 0.75rem; width: 100%; max-width: 480px; box-sizing: border-box; }
@media (max-width: 991px) {
    .col-filtro-materia { margin-left: 0 !important; margin-top: 1.5rem !important; }
    .caja-gris-filtros { max-width: 100%; }
}
@media (max-width: 768px) {
    .col-filtro-materia { margin-top: 1rem !important; }
    .clases-layout { flex-direction: column; gap: 1.5rem; }
    .schedule-table { min-width: 0; width: 100%; }
    .panel-derecho { min-width: 0; width: 100%; }
    .tabla-base { min-width: 280px; font-size: 0.9rem; }
    .tabla-base th, .tabla-base td { padding: 8px 6px; }
}
@media (max-width: 576px) {
    #form-filtros .filtros-columnas { flex-direction: column !important; }
    #form-filtros .filtros-col-izq,
    #form-filtros .col-filtro-materia { max-width: 100% !important; }
    .tabla-base th, .tabla-base td { padding: 6px 4px; font-size: 0.85rem; }
}
</style>
@endpush

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="content-header" style="margin-top: -1.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div class="content-title">
            <h3>CLASES</h3>
        </div>
    </div>

    <form action="{{ route('control.classes.index') }}" method="GET" class="mb-4" id="form-filtros">
        <div class="filtros-fila" style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="filtros-columnas" style="display: flex; gap: 1.5rem; align-items: flex-start; flex-wrap: wrap;">
                <div class="filtros-col-izq" style="display: flex; flex-direction: column; gap: 1rem; flex: 1 1 280px; max-width: 560px;">
                    <div>
                        <label for="carrera_id" style="font-weight: 600; color: #b8860b; display: block; margin-bottom: 0.5rem;">Carrera:</label>
                        <select name="carrera_id" id="carrera_id" class="form-control {{ !$carreraId ? 'carrera-placeholder' : '' }}" style="width: 100%; border-radius: 20px; border: 1px solid #ddd; padding: 8px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); text-align: center; text-align-last: center;">
                            <option value="">Seleccione el nombre de la carrera</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera->id }}" {{ $carreraId == $carrera->id ? 'selected' : '' }}>{{ $carrera->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="semestre_id" style="font-weight: 600; color: #b8860b; display: block; margin-bottom: 0.5rem;">Semestre:</label>
                        <select name="semestre" id="semestre_id" class="form-control {{ !$semestre ? 'semestre-placeholder' : '' }}" style="width: 100%; border-radius: 20px; border: 1px solid #ddd; padding: 8px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); text-align: center; text-align-last: center;">
                            <option value="">Seleccione el número del semestre</option>
                            @foreach($semestresCarrera ?? [1,2,3,4,5,6,7,8] as $s)
                                <option value="{{ $s }}" {{ (string)$semestre === (string)$s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="caja-gris-filtros" id="clases-tabla-filtros">
                        <div class="filtros-tabla-scroll" style="width: 559px; height: 391px; overflow: auto;">
                        <table class="tabla-base tabla-rayas tabla-bordes" style="width: 100%; max-width: 559px;">
                            <thead class="encabezado-tabla">
                                <tr>
                                    <th class="col-carrera" style="width: 100px; max-width: 100px;">Carrera</th>
                                    <th class="col-materia" style="text-align: center; vertical-align: middle; width: 55px; max-width: 55px;">Alumno</th>
                                    <th class="col-matricula" style="width: 110px; max-width: 110px;">Matrícula</th>
                                    <th class="col-acciones" style="width: 100px; max-width: 100px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $diasLargoTabla = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                    $clasesPorCarrera = ($clasesParaTabla ?? $clases)->groupBy('career_id');
                                @endphp
                                @forelse($clasesPorCarrera as $careerId => $clasesDeCarrera)
                                    @php
                                        $c = $clasesDeCarrera->first();
                                        $al = ($alumnoPorCarrera ?? collect())->get((int) $careerId);
                                        $nomCarrera = $al?->academicProfile?->career?->name ?? ($c->carrera->name ?? '—');
                                        $nomAlumno1 = $al ? trim(($al->nombre ?? '') . ' ' . ($al->apellido_paterno ?? '')) : '';
                                        $nomAlumno2 = $al ? trim(($al->apellido_materno ?? '')) : '';
                                        $matricula = $al?->academicProfile?->matricula ?? null;
                                        $textoHorarioClase = '';
                                        if ($c->franjas && $c->franjas->isNotEmpty()) {
                                            $partesH = [];
                                            foreach ($c->franjas as $f) {
                                                $dias = $f->dias_semana;
                                                if (is_string($dias)) { $dias = json_decode($dias, true); }
                                                if (!is_array($dias)) { $dias = $dias !== null && $dias !== '' ? [(int)$dias] : []; }
                                                $inicio = \Carbon\Carbon::parse($f->hora_inicio)->format('H:i');
                                                $fin = \Carbon\Carbon::parse($f->hora_fin)->format('H:i');
                                                foreach ($dias as $d) {
                                                    $num = (int) $d;
                                                    if ($num >= 1 && $num <= 7) {
                                                        $partesH[] = ($diasLargoTabla[$num] ?? '') . ' ' . $inicio . ' – ' . $fin;
                                                    }
                                                }
                                            }
                                            $textoHorarioClase = implode(', ', array_unique($partesH));
                                        }
                                    @endphp
                                    <tr>
                                        <td class="col-carrera">@php
                                            $palabrasC = preg_split('/\s+/', trim($nomCarrera), -1, PREG_SPLIT_NO_EMPTY);
                                            if (count($palabrasC) >= 2) {
                                                $mitad = (int) ceil(count($palabrasC) / 2);
                                                $linea1 = implode(' ', array_slice($palabrasC, 0, $mitad));
                                                $linea2 = implode(' ', array_slice($palabrasC, $mitad));
                                                echo e($linea1) . '<br>' . e($linea2);
                                            } else {
                                                echo e($nomCarrera);
                                            }
                                        @endphp</td>
                                        <td class="col-materia">
                                            @if(($nomAlumno1 ?? '') !== '')
                                                {{ $nomAlumno1 }}<br>{{ $nomAlumno2 }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="col-matricula">{{ !empty($matricula) ? $matricula : 'Pendiente' }}</td>
                                        <td class="col-acciones">
                                            <div class="col-acciones-wrap">
                                                @if($loop->first)
                                                <button type="button" class="btn-seleccionar-todo-clases" id="btn-seleccionar-todo-clases" style="color: #ACACAC; background: none; border: none; cursor: pointer; font-size: 0.6rem; padding: 0; line-height: 1.15; display: block; text-align: center; margin: -10px auto 0 auto;">Seleccionar<br>todo</button>
                                                @endif
                                                <button type="button" class="add-time-slot-btn {{ in_array($c->id, $claseIds ?? []) ? 'add-time-slot-btn--selected' : '' }}" aria-label="Seleccionar clase" title="Seleccionar clase" aria-pressed="{{ in_array($c->id, $claseIds ?? []) ? 'true' : 'false' }}" data-clase-id="{{ $c->id }}" data-carrera="{{ e($nomCarrera) }}" data-nombre="{{ e(trim(($nomAlumno1 ?? '') . ' ' . ($nomAlumno2 ?? ''))) }}" data-matricula="{{ e(!empty($matricula) ? $matricula : 'Pendiente') }}" data-href="{{ route('control.classes.index', array_filter(['carrera_id' => $carreraId, 'semestre' => $semestre, 'materia_id' => $materiaId, 'clase_id' => $c->id])) }}">
                                                    <svg class="add-time-slot-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted" style="font-style: normal;">No hay datos relevantes con esta Carrera o semestre.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                    <div style="margin-top: 0.75rem; text-align: center;">
                        <button type="button" class="submit-button" id="btn-guardar-carrera">+ Agregar clase</button>
                    </div>
                </div>
                <div class="col-filtro-materia" style="display: flex; flex-direction: column; gap: 0.75rem; flex: 1 1 280px; max-width: 559px; margin-left: 32px;">
                    <div>
                        <label for="materia_id" style="font-weight: 600; color: #b8860b; display: block; margin-bottom: 0.5rem;">Materia:</label>
                    <select name="materia_id" id="materia_id" class="form-control {{ !$materiaId ? 'materia-placeholder' : '' }}" style="width: 100%; border-radius: 20px; border: 1px solid #ddd; padding: 8px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); text-align: center; text-align-last: center;" {{ !$carreraId || $semestre === null || $semestre === '' ? 'disabled' : '' }}>
                        <option value="">Seleccione el nombre de la materia</option>
                        @foreach($materias as $materia)
                            <option value="{{ $materia->id }}" {{ $materiaId == $materia->id ? 'selected' : '' }}>{{ $materia->nombre }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div style="margin-top: 0.35rem;">
                        <label for="clase_id" style="font-weight: 600; color: #b8860b; display: block; margin-bottom: 0.5rem;">Horarios:</label>
                        <select name="clase_id" id="clase_id" class="form-control select-horarios {{ empty($claseIds) ? 'clase-placeholder' : '' }}" style="width: 100%; max-width: 100%; border-radius: 20px; border: 1px solid #ddd; padding: 8px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); text-align: center; text-align-last: center;" {{ !$materiaId ? 'disabled' : '' }}>
                            <option value="">Seleccione el horario</option>
                            @if($materiaId && ($clasesParaSelect ?? collect())->isNotEmpty())
                                @php
                                    $diasLargo = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                    $opcionesPorDia = [];
                                    foreach ($clasesParaSelect as $hc) {
                                        if ($hc->franjas->isEmpty()) continue;
                                        foreach ($hc->franjas as $f) {
                                            $dias = $f->dias_semana;
                                            if (is_string($dias)) { $dias = json_decode($dias, true); }
                                            if (!is_array($dias)) { $dias = $dias !== null && $dias !== '' ? [(int)$dias] : []; }
                                            $inicio = \Carbon\Carbon::parse($f->hora_inicio)->format('H:i');
                                            $fin = \Carbon\Carbon::parse($f->hora_fin)->format('H:i');
                                            foreach ($dias as $d) {
                                                $num = (int) $d;
                                                if ($num >= 1 && $num <= 7) {
                                                    $texto = ($diasLargo[$num] ?? '') . ' ' . $inicio . ' – ' . $fin;
                                                    $opcionesPorDia[] = ['dia' => $num, 'texto' => $texto, 'clase_id' => $hc->id];
                                                }
                                            }
                                        }
                                    }
                                    usort($opcionesPorDia, fn($a, $b) => $a['dia'] <=> $b['dia']);
                                @endphp
                                @foreach($opcionesPorDia as $op)
                                    <option value="{{ $op['clase_id'] }}" data-texto="{{ e($op['texto']) }}" {{ in_array($op['clase_id'], $claseIds ?? []) ? 'selected' : '' }}>{{ $op['texto'] }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div id="clase_id_resumen" class="mt-1 small" style="display: none; white-space: pre-line; color: #555; min-height: 1.5em;" aria-live="polite"></div>
                    </div>
                    <div class="caja-gris-filtros caja-materia-abajo" style="margin-top: 0.5rem; width: 559px; height: 391px; background-color: #ECF0F1; border-radius: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 0.75rem; border: none;">
                    <div id="horarios-materia-box" style="width: 100%; height: 100%; max-width: 535px; max-height: 367px; background-color: #fff; border-radius: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08); overflow: auto; padding: 1rem;"></div>
                </div>
                    <div style="margin-top: 0.75rem; text-align: center;">
                        <button type="button" class="submit-button" id="btn-guardar">Guardar</button>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </form>
    <form id="form-guardar-cajita" action="{{ route('control.classes.guardar-cajita') }}" method="POST" style="display:none;">
        @csrf
    </form>
    <script>
    function refrescarSoloTablaClases() {
        var form = document.getElementById('form-filtros');
        if (!form) return;
        var carrera = form.querySelector('#carrera_id');
        var semestre = form.querySelector('#semestre_id');
        if (carrera) carrera.classList.toggle('carrera-placeholder', carrera.value === '');
        if (semestre) semestre.classList.toggle('semestre-placeholder', semestre.value === '');
        var url = new URL(form.action || window.location.href, window.location.origin);
        var params = new URLSearchParams(new FormData(form));
        url.search = params.toString();
        history.pushState({}, '', url.toString());
        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newTabla = doc.getElementById('clases-tabla-filtros');
                var currentTabla = document.getElementById('clases-tabla-filtros');
                if (newTabla && currentTabla) {
                    currentTabla.innerHTML = newTabla.innerHTML;
                }
                var form = document.getElementById('form-filtros');
                var docMateria = doc.getElementById('materia_id');
                var currentMateria = document.getElementById('materia_id');
                if (docMateria && currentMateria) {
                    currentMateria.innerHTML = docMateria.innerHTML;
                    currentMateria.className = docMateria.className;
                    currentMateria.classList.toggle('placeholder', currentMateria.value === '');
                    currentMateria.disabled = !form || !form.querySelector('#carrera_id').value || !form.querySelector('#semestre_id').value;
                }
                var docClase = doc.getElementById('clase_id');
                var currentClase = document.getElementById('clase_id');
                if (docClase && currentClase) {
                    currentClase.innerHTML = docClase.innerHTML;
                    currentClase.className = docClase.className;
                    currentClase.classList.toggle('placeholder', currentClase.value === '');
                    currentClase.disabled = !currentMateria || !currentMateria.value;
                    if (typeof window.actualizarResumenHorario === 'function') window.actualizarResumenHorario();
                }
                var newContent = doc.getElementById('clases-dynamic-content');
                var currentContent = document.getElementById('clases-dynamic-content');
                if (newContent && currentContent) {
                    currentContent.innerHTML = newContent.innerHTML;
                    var selectAll = document.getElementById('select-all-alumnos');
                    var checkboxes = document.querySelectorAll('.cb-alumno');
                    if (selectAll && checkboxes.length) {
                        selectAll.onchange = function() { checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; }); };
                    }
                }
            })
            .catch(function() { window.location.href = url.toString(); });
    }
    document.getElementById('carrera_id')?.addEventListener('change', function(e){
        e.preventDefault();
        refrescarSoloTablaClases();
    });
    document.getElementById('semestre_id')?.addEventListener('change', function(e){
        e.preventDefault();
        refrescarSoloTablaClases();
    });
    document.getElementById('materia_id')?.addEventListener('change', function(e){
        e.preventDefault();
        refrescarSoloTablaClases();
    });
    function actualizarResumenHorario() {
        var sel = document.getElementById('clase_id');
        var resumen = document.getElementById('clase_id_resumen');
        if (!sel || !resumen) return;
        var opt = sel.options[sel.selectedIndex];
        var texto = opt && opt.dataset.texto ? opt.dataset.texto.replace(/\|\|/g, '\n') : '';
        resumen.textContent = texto;
    }
    document.getElementById('clase_id')?.addEventListener('change', function(e){
        e.preventDefault();
        actualizarResumenHorario();
        // No refrescar la tabla de filtros para que no se marquen los botones de acción.
        // Solo actualizar el panel derecho (clases-dynamic-content) con la clase elegida.
        var form = document.getElementById('form-filtros');
        if (!form) return;
        var url = new URL(form.action || window.location.href, window.location.origin);
        var params = new URLSearchParams(new FormData(form));
        url.search = params.toString();
        history.pushState({}, '', url.toString());
        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newContent = doc.getElementById('clases-dynamic-content');
                var currentContent = document.getElementById('clases-dynamic-content');
                if (newContent && currentContent) {
                    currentContent.innerHTML = newContent.innerHTML;
                    var selectAll = document.getElementById('select-all-alumnos');
                    var checkboxes = document.querySelectorAll('.cb-alumno');
                    if (selectAll && checkboxes.length) {
                        selectAll.onchange = function() { checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; }); };
                    }
                }
            })
            .catch(function() { window.location.href = url.toString(); });
    });
    actualizarResumenHorario();
    document.getElementById('form-filtros')?.addEventListener('click', function(e) {
        var btnTodo = e.target.closest('#btn-seleccionar-todo-clases');
        if (btnTodo) {
            e.preventDefault();
            var table = btnTodo.closest('.caja-gris-filtros').querySelector('table');
            var btns = table ? table.querySelectorAll('tbody button.add-time-slot-btn[data-href]') : [];
            var allSelected = btns.length > 0 && Array.from(btns).every(function(b) { return b.classList.contains('add-time-slot-btn--selected'); });
            btns.forEach(function(b) {
                if (allSelected) {
                    b.classList.remove('add-time-slot-btn--selected');
                    b.setAttribute('aria-pressed', 'false');
                } else {
                    b.classList.add('add-time-slot-btn--selected');
                    b.setAttribute('aria-pressed', 'true');
                }
            });
            btnTodo.innerHTML = allSelected ? 'Seleccionar<br>todo' : 'Deseleccionar<br>todo';
            return;
        }
        var btn = e.target.closest('button.add-time-slot-btn[data-href]');
        if (!btn) return;
        e.preventDefault();
        btn.classList.toggle('add-time-slot-btn--selected');
        btn.setAttribute('aria-pressed', btn.classList.contains('add-time-slot-btn--selected') ? 'true' : 'false');
        var baseUrl = new URL(btn.dataset.href, window.location.origin);
        baseUrl.searchParams.delete('clase_id');
        document.querySelectorAll('#form-filtros button.add-time-slot-btn.add-time-slot-btn--selected[data-href]').forEach(function(b) {
            baseUrl.searchParams.append('clase_id[]', b.getAttribute('data-clase-id'));
        });
        var href = baseUrl.toString();
        history.pushState({}, '', href);
        fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newContent = doc.getElementById('clases-dynamic-content');
                var current = document.getElementById('clases-dynamic-content');
                if (newContent && current) {
                    current.innerHTML = newContent.innerHTML;
                    var selectAll = document.getElementById('select-all-alumnos');
                    var checkboxes = document.querySelectorAll('.cb-alumno');
                    if (selectAll && checkboxes.length) {
                        selectAll.onchange = function() { checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; }); };
                    }
                }
            })
            .catch(function() { window.location.href = href; });
    });
    var clasesAgregadas = [];
    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }
    function refrescarPanelDerechoDesdeAgregadas() {
        var formFiltros = document.getElementById('form-filtros');
        if (!formFiltros) return;
        var baseUrl = new URL(formFiltros.action || window.location.href, window.location.origin);
        var carrera = formFiltros.querySelector('#carrera_id');
        var semestre = formFiltros.querySelector('#semestre_id');
        var materia = formFiltros.querySelector('#materia_id');
        if (carrera && carrera.value) baseUrl.searchParams.set('carrera_id', carrera.value);
        if (semestre && semestre.value) baseUrl.searchParams.set('semestre', semestre.value);
        if (materia && materia.value) baseUrl.searchParams.set('materia_id', materia.value);
        baseUrl.searchParams.delete('clase_id');
        clasesAgregadas.forEach(function(x) {
            baseUrl.searchParams.append('clase_id[]', String(x.id));
        });
        var href = baseUrl.toString();
        history.pushState({}, '', href);
        fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newContent = doc.getElementById('clases-dynamic-content');
                var current = document.getElementById('clases-dynamic-content');
                if (newContent && current) {
                    current.innerHTML = newContent.innerHTML;
                    var selectAll = document.getElementById('select-all-alumnos');
                    var checkboxes = document.querySelectorAll('.cb-alumno');
                    if (selectAll && checkboxes.length) {
                        selectAll.onchange = function() { checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; }); };
                    }
                }
            })
            .catch(function() { window.location.href = href; });
    }
    function actualizarHorariosMateriaBox() {
        var box = document.getElementById('horarios-materia-box');
        if (!box) return;
        if (!clasesAgregadas.length) {
            box.innerHTML = '';
        } else {
            box.innerHTML =
                '<ul class="mb-0" style="padding-left: 1.25rem; list-style-type: disc;">' +
                clasesAgregadas.map(function(f) {
                    return '<li class="mb-2" data-clase-id="' + String(f.id) + '" style="cursor: pointer;">' +
                        escapeHtml(f.carrera) + ' — ' + escapeHtml(f.nombre) + ' — ' + escapeHtml(f.matricula) +
                        '</li>';
                }).join('') +
                '</ul>';
        }
    }
    function enviarFormAgregarClase() {
        var form = document.getElementById('form-agregar-clase');
        if (form) form.requestSubmit();
    }
    document.getElementById('btn-guardar')?.addEventListener('click', function(e) {
        e.preventDefault();
        var form = document.getElementById('form-guardar-cajita');
        if (!form) return;
        // limpiar inputs previos
        Array.from(form.querySelectorAll('input[name="clase_ids[]"]')).forEach(function(i) { i.remove(); });
        Array.from(form.querySelectorAll('input[name="carrera_id"], input[name="semestre"], input[name="materia_id"]')).forEach(function(i) { i.remove(); });
        // enviar ids de la cajita (aunque esté vacía para limpiar sesión)
        clasesAgregadas.forEach(function(x) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'clase_ids[]';
            inp.value = String(x.id);
            form.appendChild(inp);
        });
        // enviar filtros para volver al mismo estado (sin mostrar clase seleccionada)
        var formFiltros = document.getElementById('form-filtros');
        if (formFiltros) {
            var carrera = formFiltros.querySelector('#carrera_id');
            var semestre = formFiltros.querySelector('#semestre_id');
            var materia = formFiltros.querySelector('#materia_id');
            if (carrera && carrera.value) {
                var i1 = document.createElement('input'); i1.type = 'hidden'; i1.name = 'carrera_id'; i1.value = carrera.value; form.appendChild(i1);
            }
            if (semestre && semestre.value) {
                var i2 = document.createElement('input'); i2.type = 'hidden'; i2.name = 'semestre'; i2.value = semestre.value; form.appendChild(i2);
            }
            if (materia && materia.value) {
                var i3 = document.createElement('input'); i3.type = 'hidden'; i3.name = 'materia_id'; i3.value = materia.value; form.appendChild(i3);
            }
        }
        form.submit();
    });

    document.getElementById('btn-guardar-carrera')?.addEventListener('click', function(e) {
        e.preventDefault();
        var formFiltros = document.getElementById('form-filtros');
        if (!formFiltros) return;
        var selectedBtns = document.querySelectorAll('#form-filtros button.add-time-slot-btn.add-time-slot-btn--selected[data-href]');
        if (selectedBtns.length === 0) return;

        // 1) Agregar a la caja y ocultar de la tabla
        selectedBtns.forEach(function(b) {
            var id = parseInt(b.getAttribute('data-clase-id') || '0', 10);
            if (!id) return;
            var yaExiste = clasesAgregadas.some(function(x) { return x.id === id; });
            if (!yaExiste) {
                clasesAgregadas.push({
                    id: id,
                    carrera: (b.getAttribute('data-carrera') || '').trim(),
                    nombre: (b.getAttribute('data-nombre') || '—').trim() || '—',
                    matricula: (b.getAttribute('data-matricula') || 'Pendiente').trim() || 'Pendiente'
                });
            }
            var tr = b.closest('tr');
            if (tr) tr.style.display = 'none';
        });
        actualizarHorariosMateriaBox();

        // 2) Cargar panel derecho con la primera clase agregada
        refrescarPanelDerechoDesdeAgregadas();
    });

    document.getElementById('horarios-materia-box')?.addEventListener('click', function(e) {
        var li = e.target.closest('li[data-clase-id]');
        if (!li) return;
        var id = parseInt(li.getAttribute('data-clase-id') || '0', 10);
        if (!id) return;

        // Quitar de la caja
        clasesAgregadas = clasesAgregadas.filter(function(x) { return x.id !== id; });

        // Re-mostrar en tabla y desmarcar
        var btn = document.querySelector('#form-filtros button.add-time-slot-btn[data-clase-id=\"' + String(id) + '\"]');
        if (btn) {
            btn.classList.remove('add-time-slot-btn--selected');
            btn.setAttribute('aria-pressed', 'false');
            var tr = btn.closest('tr');
            if (tr) tr.style.display = '';
        }

        actualizarHorariosMateriaBox();
        refrescarPanelDerechoDesdeAgregadas();
    });
    // Restaurar cajita desde sesión después de refrescar la página
    (function() {
        var idsServidor = @json(session('clases_agregadas_cajita', []));
        if (!Array.isArray(idsServidor) || !idsServidor.length) return;
        idsServidor.forEach(function(idRaw) {
            var id = parseInt(idRaw, 10);
            if (!id) return;
            var btn = document.querySelector('#form-filtros button.add-time-slot-btn[data-clase-id="' + String(id) + '"]');
            if (!btn) return;
            var yaExiste = clasesAgregadas.some(function(x) { return x.id === id; });
            if (!yaExiste) {
                clasesAgregadas.push({
                    id: id,
                    carrera: (btn.getAttribute('data-carrera') || '').trim(),
                    nombre: (btn.getAttribute('data-nombre') || '—').trim() || '—',
                    matricula: (btn.getAttribute('data-matricula') || 'Pendiente').trim() || 'Pendiente'
                });
            }
            var tr = btn.closest('tr');
            if (tr) tr.style.display = 'none';
        });
    })();
    actualizarHorariosMateriaBox();
    </script>

    <div id="clases-dynamic-content">
    <div class="clases-layout">
            @if($clase && $alumnosDisponibles->isNotEmpty())
                <form action="{{ route('control.classes.store') }}" method="POST" id="form-agregar-clase">
                    @csrf
                    <input type="hidden" name="horario_clase_id" value="{{ $clase->id }}">
                    <input type="hidden" name="carrera_id" value="{{ $carreraId }}">
                    <input type="hidden" name="semestre" value="{{ $semestre ?? $clase->materia->semestre ?? '' }}">
                    <input type="hidden" name="materia_id" value="{{ $materiaId }}">
                    <div class="Table-view clases-table-scroll">
                        <table class="tabla-base tabla-rayas tabla-bordes" style="width: 100%;">
                            <thead class="encabezado-tabla">
                                <tr>
                                    <th>Carrera</th>
                                    <th>Nombre</th>
                                    <th>Matrícula</th>
                                    <th class="checkbox-cell">
                                        <label class="mb-0 small">Seleccionar todo</label>
                                        <input type="checkbox" id="select-all-alumnos" class="d-block mx-auto" title="Seleccionar todo">
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="cuerpo-tabla" id="clases-tbody">
                                @foreach($alumnosDisponibles as $alumno)
                                    <tr>
                                        <td>{{ $alumno->academicProfile->career->name ?? $clase->carrera->name ?? '—' }}</td>
                                        <td class="col-nombre-dos-lineas">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }}<br>{{ $alumno->apellido_materno }}</td>
                                        <td>{{ $alumno->academicProfile->matricula ?? '—' }}</td>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="alumnos[]" value="{{ $alumno->id }}" class="cb-alumno" {{ in_array($alumno->id, $alumnosInscritos) ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-agregar-clase">
                            <span aria-hidden="true">+</span> Agregar a clase
                        </button>
                    </div>
                </form>
            @endif

    <div class="panel-derecho">
            @if($clase)
                @if($clase->alumnos->isEmpty())

                @else
                    <ul class="list-unstyled mb-0" style="max-height: 280px; overflow-y: auto;">
                        @foreach($clase->alumnos as $a)
                            <li class="py-1">{{ $a->nombre }} {{ $a->apellido_paterno }} — {{ $a->academicProfile->matricula ?? '—' }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-3">
                        <a href="{{ route('control.classes.show', $clase->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 25px;">Ver clase</a>
                        <a href="{{ route('control.classes.edit', $clase->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 25px;">Editar alumnos</a>
                    </div>
                @endif
            @endif
    </div>
    </div>
</div>

@if($clase && $alumnosDisponibles->isNotEmpty())
<script>
(function() {
    var selectAll = document.getElementById('select-all-alumnos');
    var checkboxes = document.querySelectorAll('.cb-alumno');
    if (selectAll && checkboxes.length) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
        });
    }
})();
</script>
@endif
@endsection
