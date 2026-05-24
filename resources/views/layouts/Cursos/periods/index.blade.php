@extends('layouts.app')

@vite('resources/css/Cursos/periods.css')
@vite('resources/css/Cursos/horarios.css')

@section('title', 'Períodos - ' . $course->title)

@section('content')
<div class="periods-container">

    {{-- Encabezado --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">

        {{-- Izquierda --}}
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">
                Vigencias: {{ $course->title }}
            </h1>

            <p style="color: #64748b;">
                Gestion de periodos y acceso del curso virtual
            </p>
        </div>

        {{-- Derecha --}}
        {{-- DERECHA (BOTÓN SALIR) --}}
        <a href="{{ route('courses.index') }}" class="btn-salir">
            <i class="fas fa-arrow-left"></i>
            Salir
        </a>
    </div>

    <div class="horarios-layout">
    {{-- Formulario crear período --}}
    <div class="horarios-form-section">
    <div class="card-custom">

        <div class="header-accent-blue">
            <i class="fas fa-calendar-plus"></i>
            <span>Nueva Periodo</span>
        </div>

        <div style="padding:20px;">

        <form action="{{ route('courses.periods.store',$course) }}" method="POST">

            @csrf

            <div style="display:flex; flex-direction:column; gap:15px;">

                {{-- Fecha de inicio --}}
                <div class="form-group">
                    <label class="label-custom">
                        Fecha inicio
                    </label>

                    <input type="date"
                            name="start_date"
                            min="{{ now()->format('Y-m-d') }}"
                            required
                            class="form-control-custom">
                </div>

                {{-- Fecha fin --}}
                <div class="form-group">
                    <label class="label-custom">
                        Fecha fin
                    </label>

                    <input  type="date"
                            name="end_date"
                            min="{{ now()->format('Y-m-d') }}"
                            required
                            class="form-control-custom">
                </div>

                {{-- Boton --}}
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-save"></i>
                    Registrar Vigencia
                </button>
            </div>
        </form>

    </div>
    </div>
    </div>


    <div class="horarios-tabla-section">

        <div class="card-custom">

            <div class="card-header-custom">
                <i class="fas fa-list-ul"></i>
                <strong>Periodos Registrados</strong>
            </div>

            {{-- Lista de períodos --}}
            <div class="table-responsive">
                <table class="table-custom-sessions">
                    <thead>
                        <tr>
                            <th style="padding: 18px; text-align: center; font-weight: 700; color: white;">Fecha Inicio</th>
                            <th style="padding: 18px; text-align: center; font-weight: 700; color: white;">Fecha Fin</th>
                            <th style="padding: 18px; text-align: center; font-weight: 700; color: white;">Asist.</th>
                            <th style="padding: 18px; text-align: center; font-weight: 700; color: white;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
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
                        <tr style="border-bottom: 1px solid #f0f0f0; transition:0.2s">
                            <td style="padding: 18px; text-align: center; font-weight: 500;">{{ $period->start_date->format('d/m/Y') }}</td>
                            <td style="padding: 18px; text-align: center; font-weight: 500;">{{ $period->end_date->format('d/m/Y') }}</td>
                            <td class="text-center">

                                <form action="{{ route('courses.periods.toggle', [$course, $period]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" style="background:none; border:none; cursor:pointer;">

                                        <i class="fas
                                            {{ $period->attendance_enabled
                                            ? 'fa-toggle-on text-success'
                                            : 'fa-toggle-off text-muted'
                                            }}"
                                            style="font-size:22px;">
                                        </i>

                                    </button>
                                </form>

                        </td>

                        <td class="text-center" style="display:flex; gap:6px; justify-content:center; align-items:center;">

                                {{-- Boton editar --}}
                                <button onclick="toggleEditRow(event, '{{ $period->id }}')"
                                        class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                {{-- Botón Asignar Usuarios anterior --}}

                                <a href="{{ route('courses.periods.users.index', [$course, $period]) }}"
                                    class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-user-plus"></i>
                                </a>
                                {{-- Botón Eliminar --}}
                                <form action="{{ route('courses.periods.destroy', [$course, $period]) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este período? Los usuarios asignados perderán acceso.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-delete-session">
                                        <i class="fa-solid fa-delete-left"></i>
                                    </button>
                                </form>
                        </td>
                    </tr>

                    <tr id="edit-row-{{ $period->id }}" style="display:none;" class="edit-row-active">
                        <td colspan="4">

                            <form action="{{ route('courses.periods.update', [$course, $period]) }}" method="POST" style="display:flex; gap:10px;">
                                @csrf
                                @method('PUT')

                                <input type="date" name="start_date" value="{{ $period->start_date->format('Y-m-d') }}" class="form-control-custom">

                                <input type="date" name="end_date" value="{{ $period->end_date->format('Y-m-d') }}" class="form-control-custom">

                                <button class="btn btn-primary btn-sm">
                                    Ok
                                </button>

                            </form>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="4" style="padding: 40px; text-align: center; color: #999;">
                            <div style="font-size: 3em; margin-bottom: 10px;">📅</div>
                            <div style="font-size: 1.2em;">No hay períodos creados. Crea el primero arriba.</div>
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
</table>

            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Función para abrir/cerrar la fila de editar período
    window.toggleEditRow = function (e, id) {
        e.preventDefault();

        const row = document.getElementById('edit-row-' + id);

        if (!row) return;

        row.style.display =
            (row.style.display === 'none' || row.style.display === '')
                ? 'table-row'
                : 'none';
    };

});
</script>

@endsection
