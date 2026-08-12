@extends('layouts.app')

@section('title', 'Clases - Control Administrativo - ' . session('active_institution_name'))

{{-- El layout ya carga app.css (incluye courses + Control Admin base) y app.js; duplicar @vite aquí alteraba el orden CSS al refrescar. --}}

@push('css')
<style>
#career_classification_id,
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
#form-filtros #career_classification_id,
#form-filtros #carrera_id {
    text-align: center !important;
    text-align-last: center !important;
}
#form-filtros #semestre_id {
    text-align: center !important;
    text-align-last: center !important;
}
#form-filtros select#career_classification_id.clasificacion-placeholder,
#form-filtros select#semestre_id.semestre-placeholder,
#form-filtros select#semestre_id.placeholder {
    color: #ACACAC !important;
}
#form-filtros select#career_classification_id:not(.clasificacion-placeholder) {
    color: #212529 !important;
}
#form-filtros select#career_classification_id option {
    color: #212529;
}
#form-filtros select#career_classification_id option[value=""] {
    color: #ACACAC;
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
.btn-agregar-clase { border-radius: 25px; padding: 8px 20px; display: inline-flex; align-items: center; gap: 8px; }
.checkbox-cell { width: 44px; text-align: center; }
/* Tabla #clases-tabla-filtros: solo en resources/css/Control Admin/base.css */
.col-nombre-dos-lineas { line-height: 1.5; min-height: 2.6em; vertical-align: top; color: #333; }
.btn-accion-circular { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; padding: 0; border: none; border-radius: 50%; background-color: #fff; color: #001f3f; box-shadow: 0 1px 3px rgba(0,0,0,0.2); text-decoration: none; transition: box-shadow 0.2s, background-color 0.2s; }
.btn-accion-circular:hover { background-color: #f0f0f0; color: #001f3f; box-shadow: 0 2px 5px rgba(0,0,0,0.25); }
.btn-accion-circular svg,
.btn-accion-circular .btn-accion-icon { width: 18px; height: 18px; display: block; object-fit: contain; }
/* Botones .add-time-slot-btn en #form-filtros: Control Admin/base.css */
#horarios-materia-box .btn-horario-seleccionar:hover { background: #9a7210 !important; color: #fff; box-shadow: 0 3px 8px rgba(184,134,11,0.45); }
#horarios-materia-box .horario-item--selected { background: #e8eef3; border: 2px solid #001f3f; padding: 0.6rem !important; }
#horarios-materia-box .btn-horario-seleccionar--selected:hover { background: #001a33 !important; color: #fff; box-shadow: 0 3px 8px rgba(0,31,63,0.45); }
.clases-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
/* Filtros: columna izq. (Clasificación, Carrera, Materia) | columna der. (Semestre, Horario) */
.clases-filtros-columnas { display: flex; flex-direction: column; gap: 1rem; width: 100%; }
.clases-filtros-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 559px));
    gap: 1rem 1.5rem;
    align-items: start;
    width: 100%;
}
.clases-filtros-grid__col {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    min-width: 0;
}
.clases-filtro-grupo { min-width: 0; }
.clases-filtro-grupo > label:first-child {
    font-weight: 600;
    color: #b8860b;
    display: block;
    margin-bottom: 0.5rem;
}
.clases-filtros-cuerpo {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 559px));
    gap: 1rem 1.5rem;
    align-items: stretch;
    width: 100%;
}
.clases-filtros-cuerpo__col {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    min-width: 0;
}
.clases-filtros-cuerpo__col .caja-gris-filtros {
    flex: 1 1 auto;
    min-height: 391px;
    display: flex;
    flex-direction: column;
}
.clases-filtros-cuerpo__col #clases-tabla-filtros .filtros-tabla-scroll {
    flex: 1 1 auto;
    min-height: 0;
}
.clases-filtros-cuerpo__acciones { text-align: center; flex-shrink: 0; }
.clases-filtro-select {
    width: 100%;
    border-radius: 20px;
    border: 1px solid #ddd;
    background-color: #fff !important;
    padding: 8px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    text-align: center;
    text-align-last: center;
}
#form-filtros .clases-filtro-select:disabled {
    background-color: #fff !important;
    opacity: 1;
}
.caja-materia-abajo { width: 100% !important; max-width: 559px; height: auto !important; display: flex; flex-direction: column; min-height: 0; }
.caja-materia-abajo #horarios-materia-box { flex: 1 1 auto; min-height: 0; max-height: none !important; }
@media (max-width: 1100px) {
    .clases-filtros-grid,
    .clases-filtros-cuerpo { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 991px) {
    .clases-filtros-grid,
    .clases-filtros-cuerpo { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .clases-layout { flex-direction: column; gap: 1.5rem; }
    .schedule-table { min-width: 0; width: 100%; }
    .clases-table-scroll .tabla-base { min-width: 280px; font-size: 0.9rem; }
    .clases-table-scroll .tabla-base th,
    .clases-table-scroll .tabla-base td { padding: 8px 6px; }
}
@media (max-width: 576px) {
    .clases-filtros-grid,
    .clases-filtros-cuerpo { grid-template-columns: 1fr !important; }
    .clases-table-scroll .tabla-base th,
    .clases-table-scroll .tabla-base td { padding: 6px 4px; font-size: 0.85rem; }
}
#form-filtros select {
    cursor: pointer;
}
#form-filtros select:disabled {
    cursor: default;
}
.col-matricula {
    max-width: 200px;
    font-size: 0.78rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.col-acciones {
    min-width: 110px;
    text-align: center;
}
.btn-seleccionar-todo-clases {
    font-size: 0.7rem;
    padding: 4px 8px;
    white-space: nowrap;
}
#clases-tabla-filtros .filtros-tabla-scroll,
#clases-tabla-filtros .filtros-tabla-scroll .tabla-base {
    max-width: 100% !important;
}
</style>
@endpush

@section('content')
<div class="container">
    @if(session('success'))
        {{-- Modal de éxito (estilo: icono, título, mensaje, botón OK) --}}
        <div id="clasesSuccessModal" class="modal-overlay modal-overlay--center" style="display: none; z-index: 10000;" aria-hidden="true">
            <div class="modal-content-container clases-success-modal__box">
                <div class="clases-success-modal__icon-wrap">
                    <svg class="clases-success-modal__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </div>
                <h5 class="clases-success-modal__title">Operación exitosa</h5>
                <p class="clases-success-modal__message">{{ session('success') }}</p>
                <div class="clases-success-modal__footer">
                    <button type="button" class="clases-success-modal__btn-ok btn-close-success-modal">OK</button>
                </div>
            </div>
        </div>
        <style>
        .clases-success-modal__box { text-align: center; padding: 1.5rem 1.75rem; max-width: 420px; }
        .clases-success-modal__icon-wrap {
            width: 56px; height: 56px; margin: 0 auto 1rem;
            border: 2px solid #86efac; background: #dcfce7;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
        }
        .clases-success-modal__icon { width: 28px; height: 28px; color: #16a34a; }
        .clases-success-modal__title { font-size: 1.25rem; font-weight: bold; color: #374151; margin: 0 0 0.5rem; }
        .clases-success-modal__message { font-size: 0.95rem; color: #4b5563; margin: 0 0 1.25rem; line-height: 1.4; }
        .clases-success-modal__footer { display: flex; justify-content: center; }
        .clases-success-modal__btn-ok {
            padding: 0.5rem 2rem; font-size: 0.9rem; font-weight: 600; text-transform: uppercase;
            color: #fff; background: #001f3f; border: none; border-radius: 6px; cursor: pointer;
        }
        .clases-success-modal__btn-ok:hover { background: #001a33; color: #fff; }
        </style>
        <script>
        (function() {
            var modal = document.getElementById('clasesSuccessModal');
            if (!modal) return;
            function showModal() { modal.classList.add('is-visible'); modal.style.display = ''; modal.setAttribute('aria-hidden', 'false'); }
            function hideModal() { modal.classList.remove('is-visible'); modal.style.display = 'none'; modal.setAttribute('aria-hidden', 'true'); }
            document.querySelectorAll('.btn-close-success-modal').forEach(function(btn) { btn.addEventListener('click', hideModal); });
            modal.addEventListener('click', function(e) { if (e.target === modal) hideModal(); });
            showModal();
        })();
        </script>
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
            <div class="filtros-columnas clases-filtros-columnas">
                <div class="clases-filtros-grid">
                    <div class="clases-filtros-grid__col clases-filtros-grid__col--izq">
                        <div class="clases-filtro-grupo">
                            <label for="career_classification_id">Clasificación:</label>
                            <select name="career_classification_id" id="career_classification_id" class="form-control clases-filtro-select {{ !$classificationId ? 'clasificacion-placeholder' : '' }}">
                                <option value="">Todas las clasificaciones</option>
                                @foreach($clasificaciones ?? [] as $clas)
                                    <option value="{{ $clas->id }}" {{ (int)($classificationId ?? 0) === (int)$clas->id ? 'selected' : '' }}>{{ $clas->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="clases-filtro-grupo">
                            <label for="carrera_id">Carrera:</label>
                            <select name="carrera_id" id="carrera_id" class="form-control clases-filtro-select {{ !$carreraId ? 'carrera-placeholder' : '' }}">
                                <option value="">Seleccione el nombre de la carrera</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id }}" {{ $carreraId == $carrera->id ? 'selected' : '' }}>{{ $carrera->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="clases-filtro-grupo">
                            <label for="semestre_id">Semestre:</label>
                            <select name="semestre" id="semestre_id" class="form-control clases-filtro-select {{ !$semestre ? 'semestre-placeholder' : '' }}">
                                <option value="">Seleccione el número del semestre</option>
                                @foreach($semestresCarrera ?? [1,2,3,4,5,6,7,8] as $s)
                                    <option value="{{ $s }}" {{ (string)$semestre === (string)$s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="clases-filtros-grid__col clases-filtros-grid__col--der">
                        <div class="clases-filtro-grupo">
                            <label for="materia_id">Materia:</label>
                            <select name="materia_id" id="materia_id" class="form-control clases-filtro-select {{ !$materiaId ? 'materia-placeholder' : '' }}" {{ !$carreraId ? 'disabled' : '' }}>
                                <option value="">Seleccione el nombre de la materia</option>
                                @foreach($materias as $materia)
                                    <option value="{{ $materia->id }}" data-semestre="{{ $materia->semestre }}" {{ $materiaId == $materia->id ? 'selected' : '' }}>{{ $materia->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="clases-filtro-grupo">
                            <label for="clase_id">Horarios:</label>
                            <select name="clase_id" id="clase_id" class="form-control clases-filtro-select select-horarios {{ empty($claseIds) ? 'clase-placeholder' : '' }}" {{ !$materiaId || $semestre === null || $semestre === '' ? 'disabled' : '' }}>
                                <option value="">Seleccione el horario</option>
                                @if($materiaId && ($clasesParaSelect ?? collect())->isNotEmpty())
                                @php
                                        $diasLargo = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                        $opcionesPorPaquete = [];
                                        foreach ($clasesParaSelect as $hc) {
                                            if ($hc->franjas->isEmpty()) continue;
                                            $gruposHora = [];
                                            foreach ($hc->franjas as $f) {
                                                $dias = $f->dias_semana;
                                                if (is_string($dias)) { $dias = json_decode($dias, true); }
                                                if (!is_array($dias)) { $dias = $dias !== null && $dias !== '' ? [(int)$dias] : []; }
                                                $inicio = \Carbon\Carbon::parse($f->hora_inicio)->format('H:i');
                                                $fin = \Carbon\Carbon::parse($f->hora_fin)->format('H:i');
                                                $claveHora = $inicio . '-' . $fin;
                                                if (!isset($gruposHora[$claveHora])) {
                                                    $gruposHora[$claveHora] = ['inicio' => $inicio, 'fin' => $fin, 'dias' => []];
                                                }
                                                foreach ($dias as $d) {
                                                    $num = (int) $d;
                                                    if ($num >= 1 && $num <= 7 && !in_array($num, $gruposHora[$claveHora]['dias'])) {
                                                        $gruposHora[$claveHora]['dias'][] = $num;
                                                    }
                                                }
                                            }
                                            $partes = [];
                                            foreach ($gruposHora as $g) {
                                                sort($g['dias']);
                                                $nombresD = array_map(fn($n) => $diasLargo[$n] ?? '', $g['dias']);
                                                $partes[] = implode(', ', $nombresD) . ' ' . $g['inicio'] . ' – ' . $g['fin'];
                                            }
                                            if (!empty($partes)) {
                                                $texto = implode(' | ', $partes);
                                                $opcionesPorPaquete[] = ['texto' => $texto, 'clase_id' => $hc->id];
                                            }
                                        }
                                    @endphp
                                    @foreach($opcionesPorPaquete as $op)
                                        <option value="{{ $op['clase_id'] }}" data-texto="{{ e($op['texto']) }}" {{ in_array($op['clase_id'], $claseIds ?? []) ? 'selected' : '' }}>{{ $op['texto'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div id="clase_id_resumen" class="mt-1 small" style="display: none; white-space: pre-line; color: #555; min-height: 1.5em;" aria-live="polite"></div>
                        </div>
                    </div>
                </div>

                <div class="clases-filtros-cuerpo">
                    <div class="clases-filtros-cuerpo__col clases-filtros-cuerpo__col--izq">
                    <div class="caja-gris-filtros" id="clases-tabla-filtros">
                        <div class="filtros-tabla-scroll" style="width: 100%; max-width: 559px;">
                        <table class="tabla-base tabla-rayas tabla-bordes" style="width: 100%; max-width: 559px;">
                            <thead class="encabezado-tabla">
                                <tr>
                                    <th class="col-carrera">Carrera</th>
                                    <th class="col-materia">Alumno</th>
                                    <th class="col-matricula">CURP</th>
                                    <th class="col-acciones">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $diasLargoTabla = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                @endphp
                                @forelse(($alumnosParaTabla ?? collect()) as $al)
                                    @php
                                        $careerId = (int) ($al->academicProfile->career_id ?? 0);
                                        $c = ($clasesParaTabla ?? collect())->firstWhere('career_id', $careerId)
                                            ?? ($clases ?? collect())->firstWhere('career_id', $careerId);
                                        $__filaKey = $c ? ((int) $c->id . '_' . (int) $al->id) : '';
                                    @endphp
                                    @continue(!$c)
                                    @continue(!empty($filasOcultasKeys[$__filaKey] ?? false))
                                    @php
                                        $nomCarrera = $al?->academicProfile?->career?->name ?? ($c->carrera->name ?? '—');
                                        $nomAlumno1 = $al ? trim(($al->nombre ?? '') . ' ' . ($al->apellido_paterno ?? '')) : '';
                                        $nomAlumno2 = $al ? trim(($al->apellido_materno ?? '')) : '';
                                        $matricula = $al->curp ?? null;
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
                                        $semestreFila = (string) ($al->academicProfile->semestre ?? $semestre ?? '');
                                        $materiaNombreFila = (string) ($c->materia->nombre ?? '—');
                                        $alumnoNombreFila = trim($nomAlumno1 . ' ' . $nomAlumno2);
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
                                            @if($loop->first)
                                            <button type="button" class="btn-seleccionar-todo-clases" id="btn-seleccionar-todo-clases">Seleccionar<br>todo</button>
                                            @endif
                                            <div class="col-acciones-wrap">
                                                @php
                                                    $claseIdsInt = array_map('intval', $claseIds ?? []);
                                                    $alumnoCtxInt = array_map('intval', $alumnoContextIds ?? []);
                                                    $filaBtnSeleccionada = in_array((int) $c->id, $claseIdsInt, true)
                                                        && (count($alumnoCtxInt) === 0 || in_array((int) $al->id, $alumnoCtxInt, true));
                                                @endphp
                                                <button type="button" class="add-time-slot-btn {{ $filaBtnSeleccionada ? 'add-time-slot-btn--selected' : '' }}" aria-label="Seleccionar clase" title="Seleccionar clase" aria-pressed="{{ $filaBtnSeleccionada ? 'true' : 'false' }}" data-clase-id="{{ $c->id }}" data-alumno-id="{{ $al->id }}" data-carrera="{{ e($nomCarrera) }}" data-nombre="{{ e($alumnoNombreFila) }}" data-alumno-nombre="{{ e($alumnoNombreFila) }}" data-matricula="{{ e(!empty($matricula) ? $matricula : 'Pendiente') }}" data-semestre="{{ e($semestreFila) }}" data-materia="{{ e($materiaNombreFila) }}" data-horario="{{ e($textoHorarioClase) }}" data-href="{{ route('control.classes.index', array_filter(['career_classification_id' => $classificationId, 'carrera_id' => $carreraId, 'semestre' => $semestre, 'materia_id' => $materiaId, 'clase_id' => $c->id], fn($v) => $v !== null && $v !== '')) }}">
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
                    <div class="clases-filtros-cuerpo__acciones">
                        <button type="button" class="submit-button" id="btn-guardar-carrera">+ Agregar clase</button>
                    </div>
                    </div>
                    <div class="clases-filtros-cuerpo__col clases-filtros-cuerpo__col--der">
                        <div class="caja-gris-filtros caja-materia-abajo" style="background-color: #ECF0F1; border-radius: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 0.75rem; border: none;">
                            <div id="horarios-materia-box" style="width: 100%; height: 100%; background-color: #fff; border-radius: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08); overflow: auto; padding: 1rem;"></div>
                        </div>
                        <div class="clases-filtros-cuerpo__acciones">
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
        var clasif = form.querySelector('#career_classification_id');
        var carrera = form.querySelector('#carrera_id');
        var semestre = form.querySelector('#semestre_id');
        if (clasif) clasif.classList.toggle('clasificacion-placeholder', clasif.value === '');
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
                var docClasif = doc.getElementById('career_classification_id');
                var currentClasif = document.getElementById('career_classification_id');
                if (docClasif && currentClasif) {
                    currentClasif.innerHTML = docClasif.innerHTML;
                    currentClasif.className = docClasif.className;
                    currentClasif.classList.toggle('clasificacion-placeholder', currentClasif.value === '');
                }
                var docCarreraSel = doc.getElementById('carrera_id');
                var currentCarreraSel = document.getElementById('carrera_id');
                if (docCarreraSel && currentCarreraSel) {
                    currentCarreraSel.innerHTML = docCarreraSel.innerHTML;
                    currentCarreraSel.className = docCarreraSel.className;
                    currentCarreraSel.classList.toggle('carrera-placeholder', currentCarreraSel.value === '');
                }
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
                    currentMateria.disabled = !form || !form.querySelector('#carrera_id').value;
                }
                var docSemestre = doc.getElementById('semestre_id');
                var currentSemestre = document.getElementById('semestre_id');
                if (currentSemestre && form) {
                    if (docSemestre) {
                        currentSemestre.className = docSemestre.className;
                    }
                    currentSemestre.disabled = false;
                    currentSemestre.classList.toggle('semestre-placeholder', currentSemestre.value === '');
                }
                var docClase = doc.getElementById('clase_id');
                var currentClase = document.getElementById('clase_id');
                if (docClase && currentClase) {
                    currentClase.innerHTML = docClase.innerHTML;
                    currentClase.className = docClase.className;
                    currentClase.classList.toggle('placeholder', currentClase.value === '');
                    var semVal = currentSemestre && currentSemestre.value;
                    currentClase.disabled = !currentMateria || !currentMateria.value || !semVal;
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
    document.getElementById('career_classification_id')?.addEventListener('change', function(e){
        e.preventDefault();
        var carrera = document.getElementById('carrera_id');
        if (carrera) carrera.value = '';
        var materia = document.getElementById('materia_id');
        if (materia) { materia.value = ''; materia.disabled = true; }
        var semestre = document.getElementById('semestre_id');
        if (semestre) { semestre.value = ''; semestre.disabled = false; }
        var clase = document.getElementById('clase_id');
        if (clase) { clase.value = ''; clase.disabled = true; }
        refrescarSoloTablaClases();
    });
    document.getElementById('carrera_id')?.addEventListener('change', function(e){
        e.preventDefault();
        var materia = document.getElementById('materia_id');
        if (materia) materia.value = '';
        var semestre = document.getElementById('semestre_id');
        if (semestre) semestre.value = '';
        var clase = document.getElementById('clase_id');
        if (clase) clase.value = '';
        refrescarSoloTablaClases();
    });
    document.getElementById('semestre_id')?.addEventListener('change', function(e){
        e.preventDefault();
        var clase = document.getElementById('clase_id');
        if (clase) clase.value = '';
        refrescarSoloTablaClases();
    });
    document.getElementById('materia_id')?.addEventListener('change', function(e){
        e.preventDefault();
        var sel = this;
        var opt = sel.options[sel.selectedIndex];
        var semSel = document.getElementById('semestre_id');
        if (semSel) {
            if (!sel.value) {
                semSel.value = '';
            } else if (opt && opt.dataset && opt.dataset.semestre !== undefined && opt.dataset.semestre !== '') {
                semSel.value = String(opt.dataset.semestre);
            }
        }
        var clase = document.getElementById('clase_id');
        if (clase) clase.value = '';
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
    (function inicialSemestreDesdeMateria() {
        var m = document.getElementById('materia_id');
        var s = document.getElementById('semestre_id');
        if (!m || !s || !m.value || s.value !== '') return;
        var opt = m.options[m.selectedIndex];
        if (!opt || !opt.dataset || opt.dataset.semestre === undefined || opt.dataset.semestre === '') return;
        s.value = String(opt.dataset.semestre);
        // Evitar un fetch inmediato al cargar/refrescar la página.
        // El servidor ya renderiza el estado inicial; refrescar aquí genera
        // una segunda carga visual (cursor "ocupado") innecesaria.
        s.classList.toggle('semestre-placeholder', s.value === '');
    })();
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
        /* No sincronizar por data-clase-id: varios alumnos pueden compartir la misma clase y deben marcarse por fila. */
        var baseUrl = new URL(btn.dataset.href, window.location.origin);
        baseUrl.searchParams.delete('clase_id');
        baseUrl.searchParams.delete('alumno_context_id');
        baseUrl.searchParams.delete('alumno_context_id[]');
        var seenIds = {};
        document.querySelectorAll('#form-filtros button.add-time-slot-btn.add-time-slot-btn--selected[data-href]').forEach(function(b) {
            var id = b.getAttribute('data-clase-id');
            if (id && !seenIds[id]) {
                seenIds[id] = true;
                baseUrl.searchParams.append('clase_id[]', id);
            }
            var aid = b.getAttribute('data-alumno-id');
            if (aid) {
                baseUrl.searchParams.append('alumno_context_id[]', aid);
            }
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
        var clasif = formFiltros.querySelector('#career_classification_id');
        var carrera = formFiltros.querySelector('#carrera_id');
        var semestre = formFiltros.querySelector('#semestre_id');
        var materia = formFiltros.querySelector('#materia_id');
        if (clasif && clasif.value) baseUrl.searchParams.set('career_classification_id', clasif.value);
        if (carrera && carrera.value) baseUrl.searchParams.set('carrera_id', carrera.value);
        if (semestre && semestre.value) baseUrl.searchParams.set('semestre', semestre.value);
        if (materia && materia.value) baseUrl.searchParams.set('materia_id', materia.value);
        baseUrl.searchParams.delete('clase_id');
        var seenClase = {};
        clasesAgregadas.forEach(function(x) {
            var cid = String(x.id);
            if (seenClase[cid]) return;
            seenClase[cid] = true;
            baseUrl.searchParams.append('clase_id[]', cid);
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
                    return '<li class="mb-2" data-clase-id="' + String(f.id) + '" data-alumno-id="' + String(f.alumnoId || '') + '" style="cursor: pointer;">' +
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
    function readCajitaItemFromButton(b) {
        var hid = parseInt(b.getAttribute('data-clase-id') || '0', 10);
        var aid = parseInt(b.getAttribute('data-alumno-id') || '0', 10);
        if (!hid || !aid) return null;
        return {
            horario_clase_id: hid,
            alumno_id: aid,
            carrera_nombre: b.getAttribute('data-carrera') || '',
            semestre: b.getAttribute('data-semestre') || '',
            matricula: b.getAttribute('data-matricula') || '',
            materia_nombre: b.getAttribute('data-materia') || '',
            horario_resumen: b.getAttribute('data-horario') || '',
            alumno_nombre: b.getAttribute('data-alumno-nombre') || b.getAttribute('data-nombre') || ''
        };
    }
    function readCajitaItemFromCaja(x) {
        if (!x || !x.id || !x.alumnoId) return null;
        return {
            horario_clase_id: x.id,
            alumno_id: x.alumnoId,
            carrera_nombre: x.carrera || '',
            semestre: x.semestre || '',
            matricula: x.matricula || '',
            materia_nombre: x.materia || '',
            horario_resumen: x.horario || '',
            alumno_nombre: x.nombre || ''
        };
    }
    document.getElementById('btn-guardar')?.addEventListener('click', function(e) {
        e.preventDefault();
        var form = document.getElementById('form-guardar-cajita');
        if (!form) return;
        Array.from(form.querySelectorAll('input[name^="cajita_items"]')).forEach(function(i) { i.remove(); });
        Array.from(form.querySelectorAll('input[name="clase_ids[]"]')).forEach(function(i) { i.remove(); });
        Array.from(form.querySelectorAll('input[name="career_classification_id"], input[name="carrera_id"], input[name="semestre"], input[name="materia_id"]')).forEach(function(i) { i.remove(); });
        var merged = {};
        function addCajitaItem(it) {
            if (!it || !it.horario_clase_id || !it.alumno_id) return;
            var k = it.horario_clase_id + '-' + it.alumno_id;
            if (!merged[k]) merged[k] = it;
        }
        clasesAgregadas.forEach(function(x) { addCajitaItem(readCajitaItemFromCaja(x)); });
        document.querySelectorAll('#form-filtros button.add-time-slot-btn.add-time-slot-btn--selected[data-clase-id]').forEach(function(b) {
            addCajitaItem(readCajitaItemFromButton(b));
        });
        var list = Object.values(merged);
        if (list.length === 0) {
            alert('No hay selección para guardar. Marque al menos una fila (o agréguela con «+ Agregar clase») y vuelva a intentar.');
            return;
        }
        var idx = 0;
        var fields = ['horario_clase_id', 'alumno_id', 'carrera_nombre', 'semestre', 'matricula', 'materia_nombre', 'horario_resumen', 'alumno_nombre'];
        list.forEach(function(it) {
            fields.forEach(function(f) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'cajita_items[' + idx + '][' + f + ']';
                inp.value = it[f] != null ? String(it[f]) : '';
                form.appendChild(inp);
            });
            idx++;
        });
        // enviar filtros para volver al mismo estado (sin mostrar clase seleccionada)
        var formFiltros = document.getElementById('form-filtros');
        if (formFiltros) {
            var clasif = formFiltros.querySelector('#career_classification_id');
            var carrera = formFiltros.querySelector('#carrera_id');
            var semestre = formFiltros.querySelector('#semestre_id');
            var materia = formFiltros.querySelector('#materia_id');
            if (clasif && clasif.value) {
                var i0 = document.createElement('input'); i0.type = 'hidden'; i0.name = 'career_classification_id'; i0.value = clasif.value; form.appendChild(i0);
            }
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
            var alumnoId = parseInt(b.getAttribute('data-alumno-id') || '0', 10);
            if (!id) return;
            var yaExiste = clasesAgregadas.some(function(x) { return x.id === id && x.alumnoId === alumnoId; });
            if (!yaExiste) {
                clasesAgregadas.push({
                    id: id,
                    alumnoId: alumnoId,
                    carrera: (b.getAttribute('data-carrera') || '').trim(),
                    nombre: (b.getAttribute('data-nombre') || '—').trim() || '—',
                    matricula: (b.getAttribute('data-matricula') || 'Pendiente').trim() || 'Pendiente',
                    semestre: (b.getAttribute('data-semestre') || '').trim(),
                    materia: (b.getAttribute('data-materia') || '').trim(),
                    horario: (b.getAttribute('data-horario') || '').trim()
                });
            }
            var tr = b.closest('tr');
            if (tr) tr.style.display = 'none';
        });
        actualizarHorariosMateriaBox();

        // 2) Cargar panel dinámico con la primera clase agregada
        refrescarPanelDerechoDesdeAgregadas();

        // 3) Modal global careerSuccessModal (mismo que Carreras/Materías)
        var successModal = document.getElementById('careerSuccessModal');
        var successModalMessage = document.getElementById('careerSuccessModalMessage');
        if (successModal && successModalMessage) {
            var cnt = selectedBtns.length;
            successModalMessage.textContent = cnt === 1
                ? 'Se agregó la clase seleccionada a la caja de horarios.'
                : 'Se agregaron las clases seleccionadas a la caja de horarios.';
            successModal.style.display = 'flex';
        }
    });

    document.getElementById('horarios-materia-box')?.addEventListener('click', function(e) {
        var li = e.target.closest('li[data-clase-id]');
        if (!li) return;
        var id = parseInt(li.getAttribute('data-clase-id') || '0', 10);
        var alumnoId = parseInt(li.getAttribute('data-alumno-id') || '0', 10);
        if (!id) return;

        // Quitar de la caja solo la fila de ese alumno (misma clase puede tener varios alumnos)
        clasesAgregadas = clasesAgregadas.filter(function(x) {
            return !(x.id === id && x.alumnoId === alumnoId);
        });

        document.querySelectorAll('#form-filtros button.add-time-slot-btn[data-clase-id="' + String(id) + '"]').forEach(function(btn) {
            var aid = parseInt(btn.getAttribute('data-alumno-id') || '0', 10);
            if (aid !== alumnoId) return;
            btn.classList.remove('add-time-slot-btn--selected');
            btn.setAttribute('aria-pressed', 'false');
            var tr = btn.closest('tr');
            if (tr) tr.style.display = '';
        });

        actualizarHorariosMateriaBox();
        refrescarPanelDerechoDesdeAgregadas();
    });
    // Restaurar cajita desde sesión después de refrescar la página
    (function() {
        var idsServidor = @json(session('clases_agregadas_cajita', []));
        if (!Array.isArray(idsServidor) || !idsServidor.length) return;
        idsServidor.forEach(function(entry) {
            var id = typeof entry === 'object' && entry !== null && entry.id != null
                ? parseInt(entry.id, 10)
                : parseInt(entry, 10);
            var alumnoId = typeof entry === 'object' && entry !== null && entry.alumno_id != null
                ? parseInt(entry.alumno_id, 10)
                : 0;
            if (!id) return;
            var btnSelector = '#form-filtros button.add-time-slot-btn[data-clase-id="' + String(id) + '"]';
            if (alumnoId) {
                btnSelector += '[data-alumno-id="' + String(alumnoId) + '"]';
            }
            var btn = document.querySelector(btnSelector);
            if (!btn) btn = document.querySelector('#form-filtros button.add-time-slot-btn[data-clase-id="' + String(id) + '"]');
            if (!btn) return;
            var aidFinal = alumnoId || parseInt(btn.getAttribute('data-alumno-id') || '0', 10);
            var yaExiste = clasesAgregadas.some(function(x) { return x.id === id && x.alumnoId === aidFinal; });
            if (!yaExiste) {
                clasesAgregadas.push({
                    id: id,
                    alumnoId: aidFinal,
                    carrera: (btn.getAttribute('data-carrera') || '').trim(),
                    nombre: (btn.getAttribute('data-nombre') || '—').trim() || '—',
                    matricula: (btn.getAttribute('data-matricula') || 'Pendiente').trim() || 'Pendiente',
                    semestre: (btn.getAttribute('data-semestre') || '').trim(),
                    materia: (btn.getAttribute('data-materia') || '').trim(),
                    horario: (btn.getAttribute('data-horario') || '').trim()
                });
            }
            if (alumnoId) {
                var bOne = document.querySelector('#form-filtros button.add-time-slot-btn[data-clase-id="' + String(id) + '"][data-alumno-id="' + String(alumnoId) + '"]');
                if (bOne) {
                    var tr = bOne.closest('tr');
                    if (tr) tr.style.display = 'none';
                }
            } else {
                document.querySelectorAll('#form-filtros button.add-time-slot-btn[data-clase-id="' + String(id) + '"]').forEach(function(b) {
                    var tr = b.closest('tr');
                    if (tr) tr.style.display = 'none';
                });
            }
        });
    })();
    actualizarHorariosMateriaBox();
    </script>
<div id="clases-dynamic-content"></div>
@endsection