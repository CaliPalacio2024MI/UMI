@extends('layouts.app')

@section('title', 'Boletas - ' . session('active_institution_name'))

@push('styles')
    @vite(['resources/css/Mi_Informacion/boletas.css'])
@endpush

@section('content')
<div class="main-content">
    <div class="boletas-header">
        <div>
            <div class="boletas-page-title">BOLETA DE CALIFICACIONES</div>
            <div class="boletas-subtitulo">
                @if($esPeriodoVigente)
                    Periodo vigente
                @else
                    Consulta de periodo anterior (solo lectura)
                @endif
            </div>
        </div>
        <div class="boletas-subtitulo">¡Bienvenido(a) {{ $user->nombre }}!</div>
    </div>

    <div class="boletas-toolbar">
        <form method="GET" action="{{ route('MiInformacion.boletas') }}" style="display:flex; gap:10px; align-items:center;">
            <label style="font-weight:600; color:#223F70; font-family:'Poppins',sans-serif;">Periodo:</label>
            <select name="periodo_id" class="filter-select" style="min-width:240px;" onchange="this.form.submit()">
                @foreach($periodos as $per)
                    <option value="{{ $per->id }}" @selected((int)$periodoId === (int)$per->id)>
                        {{ $per->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="boletas-container">
        <div class="boletas-card">
            <table class="boletas-table main-table">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Docente</th>
                        <th>Detalle de parciales</th>
                        <th>Calificación final</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clases as $clase)
                        @php
                            $notas = ($calificaciones[$clase->id] ?? collect())->sortBy('parcial');
                            $capturadas = $notas->filter(fn($n) => $n->calificacion !== null);
                            $final = $capturadas->isNotEmpty() ? round($capturadas->avg('calificacion')) : null;
                            $confirmada = $notas->isNotEmpty() && $notas->every(fn($n) => $n->confirmada);
                        @endphp
                        <tr>
                            <td>{{ $clase->materia->nombre ?? 'Materia' }}</td>
                            <td>{{ $clase->user->nombre ?? 'Sin asignar' }}</td>
                            <td>
                                @if($notas->isNotEmpty())
                                    <span class="boletas-detalle-parciales">
                                        @foreach($notas as $n)
                                            P{{ $n->parcial }}: {{ $n->calificacion ?? '--' }}@if(!$loop->last) &nbsp;·&nbsp; @endif
                                        @endforeach
                                    </span>
                                @else
                                    <span class="boletas-detalle-parciales">Sin calificaciones</span>
                                @endif
                            </td>
                            <td><strong>{{ $final !== null ? $final : '--' }}</strong></td>
                            <td>
                                @if($confirmada)
                                    <span class="boletas-badge boletas-badge--confirmada">En firme</span>
                                @else
                                    <span class="boletas-badge boletas-badge--pendiente">Preliminar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="no-data-centered">No hay calificaciones para este periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
