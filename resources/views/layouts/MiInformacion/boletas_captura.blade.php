@extends('layouts.app')

@section('title', 'Captura de calificaciones - ' . session('active_institution_name'))

@push('styles')
    @vite(['resources/css/Mi_Informacion/boletas.css'])
@endpush

@section('content')
<div class="main-content">
    <div class="boletas-header">
        <div>
            <div class="boletas-page-title">{{ $clase->materia->nombre ?? 'Materia' }}</div>
            <div class="boletas-subtitulo">Captura de calificaciones ({{ $numParciales }} parciales)</div>
        </div>
        <a href="{{ route('MiInformacion.boletas') }}" class="boletas-btn-mini">Volver</a>
    </div>

    <div class="boletas-container">
        <div class="boletas-card">
            <table class="boletas-table main-table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        @for($p = 1; $p <= $numParciales; $p++)
                            <th>Parcial {{ $p }}</th>
                        @endfor
                        <th>Final</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                        @php
                            $notas = ($calificaciones[$alumno->id] ?? collect())->keyBy('parcial');
                            $confirmada = $notas->isNotEmpty() && $notas->every(fn($n) => $n->confirmada);
                            $valores = [];
                            for ($p = 1; $p <= $numParciales; $p++) { $valores[$p] = $notas[$p]->calificacion ?? null; }
                            $capturadas = collect($valores)->filter(fn($v) => $v !== null);
                            $final = $capturadas->isNotEmpty() ? round($capturadas->avg()) : null;
                        @endphp
                        <tr>
                            <td>
                                <form id="form-cal-{{ $alumno->id }}" action="{{ route('MiInformacion.boletas.guardar', [$clase, $alumno]) }}" method="POST">
                                    @csrf
                                </form>
                                {{ trim($alumno->nombre.' '.$alumno->apellido_paterno.' '.$alumno->apellido_materno) }}
                            </td>
                            @for($p = 1; $p <= $numParciales; $p++)
                                <td>
                                    <input type="number" min="0" max="100" name="parciales[{{ $p }}]" form="form-cal-{{ $alumno->id }}"
                                        class="boletas-parcial-input" value="{{ $valores[$p] }}" {{ $confirmada ? 'disabled' : '' }}>
                                </td>
                            @endfor
                            <td><strong>{{ $final !== null ? $final : '--' }}</strong></td>
                            <td>
                                @if($confirmada)
                                    <span class="boletas-badge boletas-badge--confirmada">Confirmada</span>
                                @else
                                    <span class="boletas-badge boletas-badge--pendiente">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @php $nombreAlumno = trim($alumno->nombre.' '.$alumno->apellido_paterno); @endphp
                                <div class="boletas-acciones">
                                    @if($confirmada)
                                        <span class="boletas-badge boletas-badge--confirmada">En firme</span>
                                        <button type="button" class="boletas-accion-ojo js-boleta-reabrir" title="Reabrir para editar (por corrección o reclamo)"
                                            data-form="form-reabrir-{{ $alumno->id }}" data-alumno="{{ $nombreAlumno }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <form id="form-reabrir-{{ $alumno->id }}" action="{{ route('MiInformacion.boletas.reabrir', [$clase, $alumno]) }}" method="POST" style="display:none;">
                                            @csrf
                                        </form>
                                    @else
                                        <button type="submit" form="form-cal-{{ $alumno->id }}" class="boletas-btn-mini">Guardar</button>
                                        <button type="button" class="boletas-btn-mini boletas-btn-mini--confirmar js-boleta-confirmar"
                                            data-form="form-confirmar-{{ $alumno->id }}" data-alumno="{{ $nombreAlumno }}">
                                            Confirmar
                                        </button>
                                        <form id="form-confirmar-{{ $alumno->id }}" action="{{ route('MiInformacion.boletas.confirmar', [$clase, $alumno]) }}" method="POST" style="display:none;">
                                            @csrf
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $numParciales + 4 }}" class="no-data-centered">No hay alumnos en esta materia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('click', function (e) {
    var confirmarBtn = e.target.closest('.js-boleta-confirmar');
    if (confirmarBtn) {
        var alumno = confirmarBtn.dataset.alumno || 'este alumno';
        var formId = confirmarBtn.dataset.form;
        Swal.fire({
            icon: 'question',
            iconColor: '#223F70',
            title: 'Confirmar calificación',
            html: 'Vas a dejar <b>en firme</b> la calificación de <b>' + alumno + '</b>.<br>Si necesitas cambiarla después, podrás reabrirla con el botón del ojo.',
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#223F70',
            cancelButtonColor: '#adb5bd',
            reverseButtons: true,
            backdrop: 'rgba(0,0,0,0.5)'
        }).then(function (r) {
            if (r.isConfirmed) document.getElementById(formId).submit();
        });
        return;
    }

    var reabrirBtn = e.target.closest('.js-boleta-reabrir');
    if (reabrirBtn) {
        var alumnoR = reabrirBtn.dataset.alumno || 'este alumno';
        var formIdR = reabrirBtn.dataset.form;
        Swal.fire({
            icon: 'warning',
            iconColor: '#e69a37',
            title: 'Reabrir calificación',
            html: 'La calificación de <b>' + alumnoR + '</b> ya está confirmada.<br>¿Deseas reabrirla para editarla? Úsalo si hubo un error al confirmar o un reclamo del alumno.',
            showCancelButton: true,
            confirmButtonText: 'Sí, reabrir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#e69a37',
            cancelButtonColor: '#adb5bd',
            reverseButtons: true,
            backdrop: 'rgba(0,0,0,0.5)'
        }).then(function (r) {
            if (r.isConfirmed) document.getElementById(formIdR).submit();
        });
    }
});
</script>
@endpush
@endsection
