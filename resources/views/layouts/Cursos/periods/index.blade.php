@extends('layouts.app')

@section('title', 'Períodos - ' . $course->title)

@vite(['resources/css/Cursos/horarios.css'])

@section('content')
<div class="container-fluid horarios-view">
    
    {{-- ENCABEZADO --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">
                Períodos: {{ $course->title }}
            </h1>
            <p style="color: #64748b;">
                Gestión de períodos y acceso de usuarios
            </p>
        </div>
        <a href="{{ route('Cursos.index') }}" class="btn-salir">
            <i class="fas fa-arrow-left"></i>
            Salir
        </a>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div style="padding: 15px; background-color: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #22c55e;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
    <div style="padding: 15px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 15px;">
        <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
    </div>
    @endif

    {{-- LAYOUT PRINCIPAL (Dos columnas) --}}
    <div class="horarios-layout">

        {{-- ================= IZQUIERDA: FORMULARIO ================= --}}
        <div class="horarios-form-section">
            <div class="card-custom">
                <div class="header-accent-blue">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Nuevo Período</span>
                </div>

                <div style="padding:20px;">
                    <form action="{{ route('courses.periods.store', $course->id) }}" method="POST">
                        @csrf
                        <div class="form-group-custom" style="margin-bottom: 15px;">
                            <label class="label-custom">Fecha Inicio</label>
                            <input type="date" name="start_date" min="{{ now()->format('Y-m-d') }}" class="form-control-custom" required>
                        </div>
                        
                        <div class="form-group-custom" style="margin-bottom: 20px;">
                            <label class="label-custom">Fecha Fin</label>
                            <input type="date" name="end_date" min="{{ now()->format('Y-m-d') }}" class="form-control-custom" required>
                        </div>

                        <button type="submit" class="btn-primary-custom" style="width: 100%;">
                            <i class="fas fa-save"></i>
                            Crear Período
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= DERECHA: TABLA ================= --}}
        <div class="horarios-tabla-section">
            <div class="card-custom">
                <div class="card-header-custom">
                    <i class="fas fa-list-ul"></i>
                    <strong>Períodos Registrados</strong>
                </div>

                <div class="table-responsive">
                    <table class="table-custom-sessions">
                        <thead>
                            <tr>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($periods) > 0)
                                @foreach($periods as $period)
                                    @php
                                        $today = \Carbon\Carbon::today();
                                        $estado = 'No iniciado';
                                        $color = '#6c757d';
                                        $icon = '⏳';
                                        
                                        if ($today->lessThan($period->start_date)) {
                                            $estado = 'No iniciado';
                                            $color = '#6c757d';
                                            $icon = '⏳';
                                        } elseif ($today->greaterThan($period->end_date)) {
                                            $estado = 'Finalizado';
                                            $color = '#dc3545';
                                            $icon = '✓';
                                        } else {
                                            $estado = 'Activo';
                                            $color = '#28a745';
                                            $icon = '🟢';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $period->start_date->format('d/m/Y') }}</td>
                                        <td>{{ $period->end_date->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <span style="background: {{ $color }}20; color: {{ $color }}; padding: 4px 10px; border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                {{ $icon }} {{ $estado }}
                                            </span>
                                        </td>
                                        <td class="text-center" style="display:flex; gap:6px; justify-content:center;">
                                            
                                            {{-- Botón Editar --}}
                                            <button onclick="toggleEditRow(event, '{{ $period->id }}')" class="btn btn-sm btn-outline-primary" title="Editar Período">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            @php
                                                $periodoLabel = $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y');
                                            @endphp

                                            {{-- Lista de participantes (gris): mismos filtros que en Horarios --}}
                                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Lista de participantes"
                                                    onclick="abrirListaParticipantes('{{ $period->id }}', @js($periodoLabel))">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>

                                            {{-- Asignar Usuarios (verde): panel lateral --}}
                                            <button type="button" class="btn btn-sm btn-success" title="Asignar Usuarios"
                                                    onclick="openUsersModal('{{ $period->id }}', @js($periodoLabel), {{ $estado === 'Activo' ? 'true' : 'false' }})">
                                                <i class="fa-solid fa-user-plus"></i>
                                            </button>

                                            {{-- Botón Eliminar --}}
                                            <form id="delete-period-form-{{ $period->id }}" action="{{ route('courses.periods.destroy', [$course->id, $period->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                {{-- Abre el modal de advertencia (sin confirm del navegador) --}}
                                                <button type="button" class="btn-delete-session" title="Eliminar"
                                                        onclick="confirmarEliminarPeriodo('{{ $period->id }}', @js($periodoLabel))">
                                                    <i class="fa-solid fa-delete-left"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- FILA OCULTA PARA EDITAR --}}
                                    <tr id="edit-row-{{ $period->id }}" style="display:none;" class="edit-row-active">
                                        <td colspan="4">
                                            <form action="{{ route('courses.periods.update', [$course->id, $period->id]) }}" method="POST" style="display:flex; gap:10px; align-items:center;">
                                                @csrf
                                                @method('PUT')
                                                <input type="date" name="start_date" class="form-control-custom" value="{{ $period->start_date->format('Y-m-d') }}" required>
                                                <input type="date" name="end_date" class="form-control-custom" value="{{ $period->end_date->format('Y-m-d') }}" required>
                                                <button type="submit" class="btn btn-primary btn-sm">OK</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" style="padding: 40px; text-align: center; color: #999;">
                                        <div style="font-size: 2.5em; margin-bottom: 10px;">📅</div>
                                        <div style="font-size: 1.1em;">No hay períodos creados aún.</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Lógica de Edición Rápida (Toggle)
function toggleEditRow(e, id) {
    e.preventDefault();
    const row = document.getElementById('edit-row-' + id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}
</script>

{{-- Ventanas compartidas con Horarios: eliminar, lista de participantes y asignar usuarios --}}
@include('layouts.Cursos.partials.participantes', [
    'pp_entity'     => 'período',
    'pp_catalogUrl' => url('/cursos/' . $course->id . '/periodos/__ID__/participantes-catalogo'),
    'pp_storeUrl'   => url('/cursos/' . $course->id . '/periodos/__ID__/participantes'),
    'pp_usersUrl'   => url('/cursos/' . $course->id . '/periodos/__ID__/asignar-usuarios'),
    'pp_toggleUrl'  => url('/cursos/' . $course->id . '/periodos/__ID__/usuarios/toggle'),
])
@endsection