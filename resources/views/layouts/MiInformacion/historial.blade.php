@extends('layouts.app')

@section('title', 'Historial Académico - ' . session('active_institution_name'))

{{-- Inyectamos el CSS limpio --}}
@push('styles')
    @vite(['resources/css/Mi_Informacion/historial_academico.css'])
@endpush

@section('content')
<div class="main-content">
    
    {{-- HEADER --}}
    <div class="historial-header">
        <div class="historial-titles-container">
            <div class="historial-page-title">HISTORIAL ACADÉMICO</div>
        </div>
        <div class="historial-welcome-container">
            <div class="historial-welcome-message">¡Bienvenido(a) {{ $user->nombre }}!</div>
            <div class="historial-action-buttons">
                <a href="{{ route('MiInformacion.reticula') }}" class="historial-btn">
                    <i class="fa-solid fa-book-open" style="margin-right: 5px;"></i> Retícula
                </a>
                <a href="{{ route('MiInformacion.boletas') }}" class="historial-btn">
                    <i class="fa-solid fa-file-lines" style="margin-right: 5px;"></i> Boleta
                </a>
            </div>
        </div>
    </div>

    {{-- TARJETA DE INFORMACIÓN --}}
    <div class="historial-info-card">
        <div class="student-info-grid">
            <div class="info-item">
                <span class="info-label">Matrícula:</span>
                <span class="info-value">{{ $user->RFC }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ $user->nombre }} {{ $user->apellido_paterno }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Semestre actual:</span>
                <span class="info-value">{{ $user->academicProfile->semestre ?? '1' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Carrera:</span>
                <span class="info-value">{{ $user->academicProfile->career->name ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- TABLA INTEGRADA --}}
    <div class="historial-table-wrapper">
        
        {{-- Encabezados (Alineados con el contenido derecho) --}}
        <div class="table-header">
            <div class="header-item col-materia">Materia</div>
            <div class="header-item col-creditos">Créditos</div>
            <div class="header-item col-calificacion">Calif.</div>
            <div class="header-item col-evaluacion">Eval.</div>
            <div class="header-item col-observaciones">Observaciones</div>
        </div>

        <div class="materias-scroll-container">
            @forelse($semestres as $sem)
                <div class="semester-card">
                    <div class="semester-left-panel">
                        <div class="semester-number">{{ $sem->numero }}</div>
                        <div class="semester-period">{!! nl2br(e($sem->periodo)) !!}</div>
                        <div class="semester-grade-label">Promedio:</div>
                        <div class="semester-grade-value">{{ $sem->promedio }}</div>
                    </div>

                    <div class="semester-content">
                        @forelse($sem->materias as $mat)
                            <div class="materia-row">
                                <div class="col-materia">{{ $mat->nombre }}</div>
                                <div class="cell-divider"></div>
                                <div class="col-creditos">{{ $mat->creditos }}</div>
                                <div class="cell-divider"></div>
                                <div class="col-calificacion">{{ $mat->calificacion }}</div>
                                <div class="cell-divider"></div>
                                <div class="col-evaluacion">{{ $mat->evaluacion }}</div>
                                <div class="cell-divider"></div>
                                <div class="col-observaciones">{{ $mat->observaciones }}</div>
                            </div>
                        @empty
                            <div class="materia-row"><div class="col-materia">Sin materias con calificación.</div></div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding:40px; color:#666; font-family:'Poppins',sans-serif;">
                    Aún no tienes calificaciones registradas.
                </div>
            @endforelse
        </div>
    </div>

    {{-- FOOTER (Calificación Final) --}}
    <div class="historial-footer">
        <div class="final-grade-badge">
            Promedio Final: {{ $promedioFinal }}
        </div>
    </div>

</div>
@endsection