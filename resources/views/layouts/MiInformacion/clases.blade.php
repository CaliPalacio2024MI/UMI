@extends('layouts.app')

@section('title', 'Mis Clases - ' . session('active_institution_name'))

{{-- Inyectamos el CSS específico --}}
@push('styles')
    @vite(['resources/css/MiInformacion/clases.css'])
@endpush

@section('content')
<div class="main-content">
    
    <div class="clases-header">
        <div class="clases-header-top">
            <div class="clases-titles-container">
                <div class="clases-page-title">CLASES</div>
                {{-- (Aquí podrías hacer dinámico el periodo si lo tuvieras en la sesión) --}}
                <div class="clases-period-subtitle">Agosto 2025 – Febrero 2026</div>
            </div>
            
            <div class="clases-welcome-container">
                <div class="clases-welcome-message">¡Bienvenido(a) {{ $user->nombre }}!</div>
                
                {{-- Botón de Tareas (Si aún no tienes la ruta, deja el #) --}}
                <a href="#" class="clases-tasks-section">
                    <div class="clases-tasks-icon">
                        <img src="{{ asset('images/icons/clipboard-check-solid.svg') }}" alt="Tareas">
                    </div>
                    <div class="clases-tasks-text">Tareas</div>
                </a>
            </div>
        </div>
    </div>

    <div class="clases-container">
        <div class="clases-grid">
            
            {{-- BUCLE REAL: Muestra las clases de la base de datos --}}
            {{-- (Si $clases está vacío, mostrará el bloque @empty) --}}
            @forelse($clases as $clase)
                @php
                    $diasLargo = [1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab', 7 => 'Dom'];
                    $franjasResumen = [];
                    foreach (($clase->franjas ?? collect()) as $f) {
                        $dias = $f->dias_semana;
                        if (is_string($dias)) { $dias = json_decode($dias, true); }
                        if (!is_array($dias)) { $dias = $dias !== null && $dias !== '' ? [(int)$dias] : []; }
                        $diasTxt = collect($dias)->map(fn($d) => $diasLargo[(int)$d] ?? ('Dia ' . $d))->implode(', ');
                        $inicio = \Carbon\Carbon::parse($f->hora_inicio)->format('H:i');
                        $fin = \Carbon\Carbon::parse($f->hora_fin)->format('H:i');
                        $franjasResumen[] = trim($diasTxt . ' ' . $inicio . '-' . $fin);
                    }
                @endphp
                <div class="class-card">
                    <div class="class-icon-container">
                        <img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="Ícono">
                    </div>
                    <div class="class-content">
                        <div class="class-title">{{ $clase->materia->nombre ?? 'Materia' }}</div>
                        @if($clase->carrera)
                            <div class="class-subtitle">{{ $clase->carrera->name }}</div>
                        @endif
                        <div class="class-meta"><strong>Docente:</strong> {{ $clase->user->nombre ?? 'Sin asignar' }}</div>
                        <div class="class-meta"><strong>Aula:</strong> {{ $clase->aula?->nombre_aula ?? 'Sin asignar' }}</div>
                        <div class="class-meta class-meta--horario"><strong>Horario:</strong> {{ !empty($franjasResumen) ? implode(' | ', array_unique($franjasResumen)) : 'Sin horario' }}</div>
                        <div class="class-orange-line"></div>
                        <div class="class-footer">
                            {{-- Botones de acción con Font Awesome --}}
                            <div class="class-icon-placeholder" title="Ver detalles">
                                <i class="fa-solid fa-eye"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Si no hay clases reales, mostramos el mensaje --}}
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    <p>No tienes clases asignadas en este periodo.</p>
                </div>
            @endforelse

        </div>
        
        <a href="#" class="previous-classes-btn">Clases anteriores</a>
    </div>
</div>
@endsection