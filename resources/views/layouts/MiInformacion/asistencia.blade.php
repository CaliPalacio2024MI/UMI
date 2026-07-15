@extends('layouts.app')

@section('title', 'Alumnos - ' . session('active_institution_name'))

@push('styles')
    @vite(['resources/css/Mi_Informacion/asistencia.css'])
@endpush

@section('content')
<div class="main-content">
    <div class="asistencia-header">
        <div>
            <div class="asistencia-page-title">ALUMNOS</div>
            <div class="asistencia-subtitulo">{{ $clase->materia->nombre ?? 'Materia' }}</div>
        </div>
        <div class="asistencia-subtitulo">¡Bienvenido(a) {{ $user->nombre }}!</div>
    </div>

    <form action="{{ route('MiInformacion.clases.asistencia.guardar', $clase) }}" method="POST">
        @csrf
        <div class="asistencia-toolbar">
            <input type="text" id="asistencia-buscador" class="search-input" placeholder="Buscar por...">
            <div class="action-group">
                <a href="{{ route('MiInformacion.clases.asistencia.export', $clase) }}" class="btn">
                    <i class="fa-solid fa-download"></i> Descargar
                </a>
                <span class="btn" style="background-color: #e69a37 !important; cursor: default;">
                    Asistencia {{ \Carbon\Carbon::parse($hoy)->format('d/m/Y') }}
                </span>
                <button type="submit" class="btn">Guardar asistencia</button>
            </div>
        </div>

        <div class="asistencia-container">
            <div class="asistencia-card">
                <table class="asistencia-table main-table" id="asistencia-tabla">
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            <th>Nombre</th>
                            <th>Apellido Paterno</th>
                            <th>Apellido Materno</th>
                            <th>Semestre</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alumnos as $alumno)
                            @php $asistencia = $asistencias->get($alumno->id); @endphp
                            <tr data-nombre="{{ mb_strtolower($alumno->nombre . ' ' . $alumno->apellido_paterno . ' ' . $alumno->apellido_materno) }}">
                                <td>{{ $alumno->academicProfile?->career?->name ?? '—' }}</td>
                                <td>{{ $alumno->nombre }}</td>
                                <td>{{ $alumno->apellido_paterno }}</td>
                                <td>{{ $alumno->apellido_materno }}</td>
                                <td>{{ $alumno->academicProfile?->semestre ?? '—' }}</td>
                                <td>
                                    <div class="asistencia-detalles">
                                        <a href="{{ route('MiInformacion.clases') }}" title="Ver contenido de la materia">
                                            <i class="fa-solid fa-eye" style="color:#e69a37;"></i>
                                        </a>
                                        <input type="checkbox" class="asistencia-check" name="presentes[]" value="{{ $alumno->id }}"
                                            @checked($asistencia?->presente) title="Presente hoy">
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="no-data-centered">No hay alumnos asignados a esta clase.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('asistencia-buscador').addEventListener('input', function () {
    var filtro = this.value.trim().toLowerCase();
    document.querySelectorAll('#asistencia-tabla tbody tr[data-nombre]').forEach(function (row) {
        row.style.display = row.dataset.nombre.includes(filtro) ? '' : 'none';
    });
});
</script>
@endsection
