@extends('layouts.app')

@section('title', 'Entregas de la tarea - ' . session('active_institution_name'))

@push('styles')
    @vite(['resources/css/Mi_Informacion/tareas.css'])
@endpush

@section('content')
<div class="main-content">
    <div class="tareas-header">
        <div>
            <div class="tareas-page-title">{{ $tarea->titulo }}</div>
            <div style="color:#666; font-family:'Poppins',sans-serif;">
                {{ $tarea->horarioClase->materia->nombre ?? 'Materia' }} &middot;
                Vence: {{ $tarea->fecha_vencimiento->format('d/m/Y H:i') }} &middot;
                Puntaje máximo: {{ $tarea->puntaje_maximo }}
            </div>
        </div>
        <a href="{{ route($routePrefixClase, $tarea->horarioClase) }}" class="btn-pill-primary" style="text-decoration:none;">Volver</a>
    </div>

    <div class="tareas-container">
        <div class="tareas-card">
            <table class="tareas-table main-table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Fecha de entrega</th>
                        <th>Puntaje obtenido</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                        @php $entrega = $entregas->get($alumno->id); @endphp
                        <tr>
                            <td>{{ $alumno->nombre }}</td>
                            <td>{{ $entrega && $entrega->fecha_entrega ? $entrega->fecha_entrega->format('d/m/Y - H:i') : '------------' }}</td>
                            <td>
                                <form action="{{ route($routePrefix . '.calificar', [$tarea, $alumno]) }}" method="POST" style="display:flex; gap:8px; align-items:center;">
                                    @csrf
                                    <input type="number" name="puntaje_obtenido" min="0" max="{{ $tarea->puntaje_maximo }}"
                                        value="{{ $entrega->puntaje_obtenido ?? '' }}" style="width:70px; padding:4px 6px; border:1px solid #ccc; border-radius:6px;">
                                    <span>/ {{ $tarea->puntaje_maximo }}</span>
                                    <button type="submit" class="tareas-accion-btn" title="Guardar puntaje">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="no-data-centered">No hay alumnos asignados a esta clase.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
