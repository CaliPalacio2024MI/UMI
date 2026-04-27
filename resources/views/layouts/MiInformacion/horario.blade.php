@extends('layouts.app')

@section('title', 'Mi Horario - ' . session('active_institution_name'))

{{-- Inyectamos el CSS --}}
@push('styles')
    @vite(['resources/css/Mi_Informacion/horario.css'])
@endpush

@section('content')
<div class="main-content">
    @php
        $days = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $startHour = 7;
        $endHour = 20;
        $slots = [];
        foreach (range($startHour, $endHour) as $h) {
            foreach (range(1, 7) as $d) {
                $slots[$h][$d] = [];
            }
        }

        foreach (($clases ?? collect()) as $clase) {
            foreach (($clase->franjas ?? collect()) as $franja) {
                $dias = $franja->dias_semana;
                if (is_string($dias)) { $dias = json_decode($dias, true); }
                if (!is_array($dias)) { $dias = $dias !== null && $dias !== '' ? [(int)$dias] : []; }

                $hInicio = (int) \Carbon\Carbon::parse($franja->hora_inicio)->format('H');
                $hFin = (int) \Carbon\Carbon::parse($franja->hora_fin)->format('H');
                if ($hFin <= $hInicio) { $hFin = $hInicio + 1; }

                foreach ($dias as $dia) {
                    $dia = (int) $dia;
                    if ($dia < 1 || $dia > 7) { continue; }
                    for ($h = $hInicio; $h < $hFin; $h++) {
                        if ($h < $startHour || $h > $endHour) { continue; }
                        $slots[$h][$dia][] = [
                            'materia' => $clase->materia->nombre ?? 'Materia',
                            'aula' => $clase->aula?->nombre_aula ?? 'Sin aula',
                            'docente' => $clase->user->nombre ?? 'Sin docente',
                        ];
                    }
                }
            }
        }
    @endphp
    
    <div class="horario-header">
        <div>
            <div class="horario-page-title">HORARIOS</div>
            <div class="horario-period-subtitle">Agosto 2025 – Febrero 2026</div>
        </div>
        
        <button class="horario-export-btn" onclick="window.print()">
            <i class="fa-solid fa-file-export" style="margin-right: 8px;"></i> Exportar
        </button>
    </div>

    <div class="schedule-wrapper">
        {{-- Encabezados de Días --}}
        <div class="schedule-header">
            <div class="time-header">HORA</div>
            <div class="day-header">Lunes</div>
            <div class="day-header">Martes</div>
            <div class="day-header">Miércoles</div>
            <div class="day-header">Jueves</div>
            <div class="day-header">Viernes</div>
            <div class="day-header">Sábado</div>
            <div class="day-header">Domingo</div>
        </div>
        
        {{-- Cuerpo del Horario (Scrollable) --}}
        <div class="schedule-scroll-container">
            <div class="schedule-body">
                
                {{-- Columna de Horas (Fija) --}}
                <div class="time-labels-column">
                    {{-- Genera las horas de 7am a 9pm --}}
                    @for ($i = 7; $i <= 20; $i++)
                        <div class="time-label">
                            {{ sprintf("%02d:00", $i) }}<br>
                            {{ sprintf("%02d:00", $i+1) }}
                        </div>
                    @endfor
                </div>

                @for ($hour = $startHour; $hour <= $endHour; $hour++)
                    @for ($day = 1; $day <= 7; $day++)
                        <div class="schedule-cell">
                            @foreach (($slots[$hour][$day] ?? []) as $entry)
                                <div class="class-item">
                                    <div class="class-name">{{ $entry['materia'] }}</div>
                                    <div class="class-room">{{ $entry['aula'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endfor
                @endfor

            </div>
        </div>
    </div>
</div>
@endsection