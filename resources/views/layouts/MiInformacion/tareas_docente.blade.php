@extends('layouts.app')

@section('title', ($tituloPagina ?? 'Tareas') . ' - ' . session('active_institution_name'))

@push('styles')
    @vite(['resources/css/Mi_Informacion/tareas.css'])
@endpush

@section('content')
@php
    $ahora = \Carbon\Carbon::now();
    $etiqueta = $tituloPagina === 'EVALUACIONES' ? 'Evaluación' : 'Tarea';
    $columnaFechaApertura = $tituloPagina === 'EVALUACIONES' ? 'Fecha de creación' : 'Fecha de apertura';
@endphp
<div class="main-content">

    <div class="tareas-header">
        <div>
            <div class="tareas-page-title">{{ $tituloPagina }}</div>
            <div style="color:#666; font-family:'Poppins',sans-serif;">{{ $clase->materia->nombre ?? 'Materia' }}</div>
        </div>
        <button type="button" class="btn-pill-primary" onclick="document.getElementById('modal-nueva-tarea').classList.add('is-open')">
            + Crear {{ $etiqueta }}
        </button>
    </div>

    <div class="tareas-filtros">
        <button type="button" class="tareas-filtro-btn is-active" data-filter="todos">Todos</button>
        <button type="button" class="tareas-filtro-btn" data-filter="vencidas">Vencidas</button>
        <button type="button" class="tareas-filtro-btn" data-filter="cerradas">Cerradas</button>
    </div>

    <div class="tareas-container">
        <div class="tareas-card">
            <table class="tareas-table main-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>{{ $columnaFechaApertura }}</th>
                        <th>Fecha de vencimiento</th>
                        <th>Fecha de cierre</th>
                        <th>Puntaje máximo</th>
                        <th>%Cumplimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tareas as $tarea)
                        @php
                            $cerrada = $tarea->fecha_cierre && $tarea->fecha_cierre->lt($ahora);
                            $vencida = !$cerrada && $tarea->fecha_vencimiento->lt($ahora);
                            $estado = $cerrada ? 'cerradas' : ($vencida ? 'vencidas' : 'todos');
                        @endphp
                        <tr data-estado="{{ $estado }}">
                            <td><i class="fa-solid fa-bell tareas-materia-icon"></i>{{ $tarea->titulo }}</td>
                            <td>{{ ($tarea->fecha_apertura ?? $tarea->created_at)->format('d/m/Y - H:i') }}</td>
                            <td>{{ $tarea->fecha_vencimiento->format('d/m/Y - H:i') }}</td>
                            <td>{{ $tarea->fecha_cierre ? $tarea->fecha_cierre->format('d/m/Y - H:i') : '------------' }}</td>
                            <td>{{ $tarea->puntaje_maximo }}</td>
                            <td>{{ $tarea->cumplimiento }}%</td>
                            <td>
                                <div class="tareas-acciones">
                                    <a href="{{ route($routePrefix . '.entregas', $tarea) }}" class="tareas-accion-btn" title="Ver entregas">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </a>
                                    <button type="button" class="tareas-accion-btn" title="Editar"
                                        onclick="document.getElementById('modal-editar-{{ $tarea->id }}').classList.add('is-open')">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="tareas-accion-btn eliminar" title="Eliminar"
                                        onclick="if(confirm('¿Eliminar este registro?')) document.getElementById('form-eliminar-{{ $tarea->id }}').submit()">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <form id="form-eliminar-{{ $tarea->id }}" action="{{ route($routePrefix . '.destroy', $tarea) }}" method="POST" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal" id="modal-editar-{{ $tarea->id }}">
                            <div class="modal-content">
                                <span class="close" onclick="document.getElementById('modal-editar-{{ $tarea->id }}').classList.remove('is-open')">&times;</span>
                                <h2 class="modal-title">Editar {{ strtolower($etiqueta) }}</h2>
                                <form action="{{ route($routePrefix . '.update', $tarea) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <label>Nombre</label>
                                    <input type="text" name="titulo" value="{{ $tarea->titulo }}" required>

                                    <label>Descripción</label>
                                    <textarea name="descripcion" rows="3">{{ $tarea->descripcion }}</textarea>

                                    <label>Fecha de apertura</label>
                                    <input type="datetime-local" name="fecha_apertura" value="{{ $tarea->fecha_apertura?->format('Y-m-d\TH:i') }}">

                                    <label>Fecha de vencimiento</label>
                                    <input type="datetime-local" name="fecha_vencimiento" value="{{ $tarea->fecha_vencimiento->format('Y-m-d\TH:i') }}" required>

                                    <label>Fecha de cierre</label>
                                    <input type="datetime-local" name="fecha_cierre" value="{{ $tarea->fecha_cierre?->format('Y-m-d\TH:i') }}">

                                    <label>Puntaje máximo</label>
                                    <input type="number" name="puntaje_maximo" value="{{ $tarea->puntaje_maximo }}" min="1" max="1000">

                                    <button type="submit" class="guardar">Guardar</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="no-data-centered">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="modal-nueva-tarea">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('modal-nueva-tarea').classList.remove('is-open')">&times;</span>
        <h2 class="modal-title">Crear {{ strtolower($etiqueta) }}</h2>
        <form action="{{ route($routePrefix . '.store') }}" method="POST">
            @csrf
            <input type="hidden" name="horario_clase_id" value="{{ $clase->id }}">

            <label>Nombre</label>
            <input type="text" name="titulo" required>

            <label>Descripción</label>
            <textarea name="descripcion" rows="3"></textarea>

            <label>Fecha de apertura</label>
            <input type="datetime-local" name="fecha_apertura">

            <label>Fecha de vencimiento</label>
            <input type="datetime-local" name="fecha_vencimiento" required>

            <label>Fecha de cierre</label>
            <input type="datetime-local" name="fecha_cierre">

            <label>Puntaje máximo</label>
            <input type="number" name="puntaje_maximo" value="100" min="1" max="1000">

            <button type="submit" class="guardar">Guardar</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.tareas-filtro-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tareas-filtro-btn').forEach(function (b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        var filtro = btn.dataset.filter;
        document.querySelectorAll('.tareas-table tbody tr[data-estado]').forEach(function (row) {
            row.style.display = (filtro === 'todos' || row.dataset.estado === filtro) ? '' : 'none';
        });
    });
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal') && e.target.classList.contains('is-open')) {
        e.target.classList.remove('is-open');
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.is-open').forEach(function (m) { m.classList.remove('is-open'); });
    }
});
</script>
@endsection
