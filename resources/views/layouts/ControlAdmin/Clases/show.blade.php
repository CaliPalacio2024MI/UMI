@extends('layouts.app')

@section('title', 'Ver Clase - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
@php
    $diasNombres = ['1' => 'Lunes', '2' => 'Martes', '3' => 'Miércoles', '4' => 'Jueves', '5' => 'Viernes', '6' => 'Sábado', '7' => 'Domingo'];
@endphp
<div class="container" style="max-width: 900px; margin: 0 auto;">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="modal-view-career__container" style="max-width: 100%;">
        <div class="modal-view-career__header" style="display: flex; align-items: center; justify-content: center; position: relative;">
            <h5 class="modal-view-career__title" style="margin: 0; text-align: center;">Detalle de la clase</h5>
            <a href="{{ route('control.classes.index') }}" class="modal-view-career__close" aria-label="Cerrar" style="position: absolute; right: 1rem; text-decoration: none; color: rgba(255,255,255,0.9); font-size: 1.5rem;">&times;</a>
        </div>
        <div class="modal-view-career__body">
            <dl class="career-view-dl career-view-dl--styled">
                <div class="career-view-row">
                    <dt>Carrera:</dt>
                    <dd>{{ $clase->carrera->name ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Materia:</dt>
                    <dd>{{ $clase->materia->nombre ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Docente:</dt>
                    <dd>{{ $clase->user->nombre ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Aula:</dt>
                    <dd>{{ $clase->aula->numero_aula ?? '—' }}</dd>
                </div>
                <div class="career-view-row career-view-row--no-border">
                    <dt class="career-view-dt--gold">Franjas horarias:</dt>
                    <dd>
                        @if($clase->franjas->isEmpty())
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
                                    @foreach($clase->franjas as $f)
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

            <h6 style="margin: 1.25rem 0 0.5rem;">Alumnos inscritos ({{ $clase->alumnos->count() }})</h6>
            @if($clase->alumnos->isEmpty())
                <p style="color: #666;">Ningún alumno inscrito. Use "Gestionar alumnos" o "Inscribir todos" para agregar.</p>
            @else
                <table class="tabla-base tabla-rayas tabla-bordes" style="width: 100%; margin-top: 0.5rem;">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Matrícula</th>
                            <th>Semestre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clase->alumnos as $alumno)
                            <tr>
                                <td>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                                <td>{{ $alumno->academicProfile->matricula ?? '—' }}</td>
                                <td>{{ $alumno->academicProfile->semestre ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div style="margin-top: 1.25rem; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
                <a href="{{ route('control.classes.edit', $clase->id) }}" class="btn btn-primary" style="border-radius: 25px; padding: 8px 20px;">Gestionar alumnos</a>
                <form action="{{ route('control.classes.inscribir-todos', $clase->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="border-radius: 25px; padding: 8px 20px;">Inscribir todos los elegibles</button>
                </form>
                <a href="{{ route('control.classes.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 25px; padding: 8px 20px;">Volver a Clases</a>
            </div>
        </div>
    </div>
</div>
@endsection
