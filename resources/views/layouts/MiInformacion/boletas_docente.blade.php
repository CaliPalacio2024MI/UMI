@extends('layouts.app')

@section('title', 'Boletas - ' . session('active_institution_name'))

@push('styles')
    @vite(['resources/css/Mi_Informacion/boletas.css'])
@endpush

@section('content')
<div class="main-content">
    <div class="boletas-header">
        <div>
            <div class="boletas-page-title">BOLETAS</div>
            <div class="boletas-subtitulo">Selecciona una materia para capturar calificaciones</div>
        </div>
        <div class="boletas-subtitulo">¡Bienvenido(a) {{ $user->nombre }}!</div>
    </div>

    <div class="boletas-container">
        <div class="boletas-card">
            <table class="boletas-table main-table">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Carrera</th>
                        <th>Parciales</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($misClases as $clase)
                        <tr>
                            <td>{{ $clase->materia->nombre ?? 'Materia' }}</td>
                            <td>{{ $clase->carrera->name ?? '—' }}</td>
                            <td>{{ $clase->materia->num_parciales ?? 3 }}</td>
                            <td>
                                <a href="{{ route('MiInformacion.boletas.materia', $clase) }}" class="boletas-btn-mini">
                                    <i class="fa-solid fa-pen"></i> Capturar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="no-data-centered">No tienes materias asignadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
