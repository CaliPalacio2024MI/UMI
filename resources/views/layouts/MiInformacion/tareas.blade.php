@extends('layouts.app')

@section('title', ($tituloPagina ?? 'Tareas') . ' - ' . session('active_institution_name'))

@push('styles')
    @vite(['resources/css/Mi_Informacion/tareas.css'])
@endpush

@section('content')
@php
    $esDocente = $user->hasActiveRole('docente');
    $ahora = \Carbon\Carbon::now();
    $routePrefix = $routePrefix ?? 'MiInformacion.tareas';
    $tituloPagina = $tituloPagina ?? 'TAREAS';
@endphp
<div class="main-content">

    <div class="tareas-header">
        <div>
            <div class="tareas-page-title">{{ $tituloPagina }}</div>
        </div>
        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:10px;">
            <div class="tareas-welcome-message">¡Bienvenido(a) {{ $user->nombre }}!</div>
            @if($esDocente)
                <button type="button" class="btn-pill-primary" onclick="document.getElementById('modal-nueva-tarea').classList.add('is-open')">
                    + Nueva
                </button>
            @endif
        </div>
    </div>

    <div class="tareas-filtros">
        <button type="button" class="tareas-filtro-btn is-active" data-filter="todas">Todas</button>
        <button type="button" class="tareas-filtro-btn" data-filter="entregadas">Entregadas</button>
        <button type="button" class="tareas-filtro-btn" data-filter="vencidas">Vencidas</button>
    </div>

    <div class="tareas-container">
        <div class="tareas-card">
            <table class="tareas-table main-table">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Nombre</th>
                        <th>Fecha de vencimiento</th>
                        <th>Fecha de entrega</th>
                        <th>Puntaje obtenido</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tareas as $tarea)
                        @php
                            $entrega = $esDocente ? null : $tarea->entregas->first();
                            $vencida = $tarea->fecha_vencimiento->lt($ahora);
                            $estado = $entrega && $entrega->fecha_entrega ? 'entregadas' : ($vencida ? 'vencidas' : 'todas');
                        @endphp
                        <tr data-estado="{{ $estado }}">
                            <td>
                                <i class="fa-solid fa-bell tareas-materia-icon"></i>
                                {{ $tarea->horarioClase->materia->nombre ?? 'Materia' }}
                            </td>
                            <td>{{ $tarea->titulo }}</td>
                            <td>{{ $tarea->fecha_vencimiento->format('d/m/Y - H:i') }}</td>
                            <td>
                                @if($esDocente)
                                    {{ $tarea->entregas->whereNotNull('fecha_entrega')->count() }} / {{ $tarea->entregas->count() ?: '—' }} entregadas
                                @else
                                    {{ $entrega && $entrega->fecha_entrega ? $entrega->fecha_entrega->format('d/m/Y - H:i') : '------------' }}
                                @endif
                            </td>
                            <td>
                                @if($esDocente)
                                    —
                                @else
                                    {{ $entrega && $entrega->puntaje_obtenido !== null ? $entrega->puntaje_obtenido : '--' }}/{{ $tarea->puntaje_maximo }}
                                @endif
                            </td>
                            <td>
                                <div class="tareas-acciones">
                                    @if($esDocente)
                                        <a href="{{ route($routePrefix . '.entregas', $tarea) }}" class="tareas-accion-btn" title="Ver entregas">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <button type="button" class="tareas-accion-btn eliminar" title="Eliminar"
                                            onclick="if(confirm('¿Eliminar este registro?')) document.getElementById('form-eliminar-{{ $tarea->id }}').submit()">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <form id="form-eliminar-{{ $tarea->id }}" action="{{ route($routePrefix . '.destroy', $tarea) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @else
                                        @if(!$entrega || !$entrega->fecha_entrega)
                                            <form action="{{ route($routePrefix . '.entregar', $tarea) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="tareas-accion-btn entregar" title="Marcar como entregada">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="tareas-accion-btn" title="Ver detalles"
                                            onclick="alert('{{ addslashes($tarea->descripcion ?? 'Sin descripción adicional.') }}')">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-data-centered">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($esDocente)
<div class="modal" id="modal-nueva-tarea">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('modal-nueva-tarea').classList.remove('is-open')">&times;</span>
        <h2 class="modal-title">Nuevo registro</h2>
        <form action="{{ route($routePrefix . '.store') }}" method="POST">
            @csrf
            <label for="horario_clase_id">Materia</label>
            <select name="horario_clase_id" id="horario_clase_id" class="filter-select" style="width:100%;" required>
                @foreach($misClases as $clase)
                    <option value="{{ $clase->id }}">{{ $clase->materia->nombre ?? 'Materia' }}</option>
                @endforeach
            </select>

            <label for="titulo">Nombre</label>
            <input type="text" name="titulo" id="titulo" required>

            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="3"></textarea>

            <label for="fecha_vencimiento">Fecha de vencimiento</label>
            <input type="datetime-local" name="fecha_vencimiento" id="fecha_vencimiento" required>

            <label for="puntaje_maximo">Puntaje máximo</label>
            <input type="number" name="puntaje_maximo" id="puntaje_maximo" value="100" min="1" max="1000">

            <button type="submit" class="guardar">Guardar</button>
        </form>
    </div>
</div>
@endif

<script>
document.querySelectorAll('.tareas-filtro-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tareas-filtro-btn').forEach(function (b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        var filtro = btn.dataset.filter;
        document.querySelectorAll('.tareas-table tbody tr[data-estado]').forEach(function (row) {
            row.style.display = (filtro === 'todas' || row.dataset.estado === filtro) ? '' : 'none';
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
