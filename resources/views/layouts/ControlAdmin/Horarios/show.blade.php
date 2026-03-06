@extends('layouts.app')

@section('title', 'Ver Horario - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
@php
    $diasNombres = ['1' => 'Lunes', '2' => 'Martes', '3' => 'Miércoles', '4' => 'Jueves', '5' => 'Viernes', '6' => 'Sábado', '7' => 'Domingo'];
@endphp
<div class="container" style="max-width: 640px; margin: 0 auto;">
    <div class="modal-view-career__container" style="max-width: 100%;">
        <div class="modal-view-career__header" style="display: flex; align-items: center; justify-content: center; position: relative;">
            <h5 class="modal-view-career__title" style="margin: 0; text-align: center;">Detalle del horario</h5>
            <a href="{{ route('control.schedules.index') }}" class="modal-view-career__close" aria-label="Cerrar" style="position: absolute; right: 1rem; text-decoration: none; color: rgba(255,255,255,0.9); font-size: 1.5rem; line-height: 1; padding: 0.25rem; border-radius: 6px;">&times;</a>
        </div>
        <div class="modal-view-career__body">
            <dl class="career-view-dl career-view-dl--styled">
                <div class="career-view-row">
                    <dt>Carrera:</dt>
                    <dd>{{ $horario->carrera->name ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Materia:</dt>
                    <dd>{{ $horario->materia->nombre ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Docente:</dt>
                    <dd>{{ $horario->user->nombre ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Aula:</dt>
                    <dd>{{ $horario->aula->numero_aula ?? '—' }}</dd>
                </div>
                <div class="career-view-row career-view-row--no-border">
                    <dt class="career-view-dt--gold">Horario:</dt>
                    <dd>
                        @if($horario->franjas->isEmpty())
                            <p style="color: #666;">No hay franjas registradas.</p>
                        @else
                            <table class="tabla-base tabla-rayas tabla-bordes" style="width: 100%; margin-top: 0.5rem;">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Hora inicio</th>
                                        <th>Hora fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($horario->franjas as $f)
                                        @php
                                            $dias = is_array($f->dias_semana) ? $f->dias_semana : [$f->dias_semana];
                                            $diaStr = implode(', ', array_map(fn($d) => $diasNombres[(string)$d] ?? 'Día ' . $d, $dias));
                                            $hIni = \Carbon\Carbon::parse($f->hora_inicio)->format('h:i A');
                                            $hFin = \Carbon\Carbon::parse($f->hora_fin)->format('h:i A');
                                        @endphp
                                        <tr>
                                            <td>{{ $diaStr }}</td>
                                            <td>{{ $hIni }}</td>
                                            <td>{{ $hFin }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
