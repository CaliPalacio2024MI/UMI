@php
    $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $grilla = [];
    for ($d = 1; $d <= 7; $d++) {
        for ($hr = 7; $hr <= 20; $hr++) {
            $grilla[$d][$hr] = [];
        }
    }
    foreach ($horarios as $horario) {
        $horario->loadMissing(['materia', 'aula', 'franjas']);
        foreach ($horario->franjas ?? [] as $f) {
            if (empty($f->hora_inicio) || empty($f->hora_fin)) continue;
            try {
                $start = \Carbon\Carbon::parse($f->hora_inicio);
                $end = \Carbon\Carbon::parse($f->hora_fin);
            } catch (\Exception $e) {
                continue;
            }
            $diasRaw = $f->dias_semana;
            $dias = is_array($diasRaw) ? $diasRaw : (isset($diasRaw) ? [$diasRaw] : []);
            $dias = array_filter(array_map(function ($d) {
                $d = (int) $d;
                return ($d >= 1 && $d <= 7) ? $d : null;
            }, $dias));
            $fStart = $start->hour * 60 + $start->minute;
            $fEnd = $end->hour * 60 + $end->minute;
            foreach ($dias as $dia) {
                // Mostrar la clase en cada celda que la franja abarca (desde hora_inicio hasta hora_fin)
                for ($hr = 7; $hr <= 20; $hr++) {
                    $slotStart = $hr * 60;
                    $slotEnd = ($hr + 1) * 60;
                    $haySolapamiento = $fStart < $slotEnd && $fEnd > $slotStart;
                    if ($haySolapamiento && isset($grilla[$dia][$hr])) {
                        $yaEsta = false;
                        foreach ($grilla[$dia][$hr] as $h) {
                            if (isset($h->id) && isset($horario->id) && $h->id === $horario->id) {
                                $yaEsta = true;
                                break;
                            }
                        }
                        if (!$yaEsta) {
                            $grilla[$dia][$hr][] = $horario;
                        }
                    }
                }
            }
        }
    }
    // Solo las horas que tienen al menos una materia asignada: al exportar no se generan hojas vacías
    $horasConClase = [];
    for ($hr = 7; $hr <= 20; $hr++) {
        for ($d = 1; $d <= 7; $d++) {
            if (!empty($grilla[$d][$hr])) {
                $horasConClase[] = $hr;
                break;
            }
        }
    }
    $horasConClase = array_values(array_unique($horasConClase));
    sort($horasConClase);
@endphp

<div class="teacher-horario-modal-header">
    <div class="teacher-horario-modal-header-text">
        <div class="teacher-horario-modal-career">{{ trim($user->nombre . ' ' . ($user->apellido_paterno ?? '') . ' ' . ($user->apellido_materno ?? '')) ?: '—' }}</div>
        <div class="teacher-horario-modal-period">Enero 2026 - Abril 2026</div>
    </div>
    <button type="button" class="teacher-horario-modal-export-btn">
        <i class="fa-solid fa-file-export" style="margin-right: 8px;"></i> Exportar
    </button>
</div>

@if($horarios->isEmpty())
    <p class="teacher-horarios-grilla-empty">No se encontraron horarios para este {{ isset($esAlumno) && $esAlumno ? 'alumno' : 'docente' }}.</p>
@else
<div class="teacher-horarios-grilla-wrapper">
    <div class="teacher-horarios-grilla-scroll">
        <table class="teacher-horarios-grilla" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="grilla-col-hora">HORA</th>
                    @foreach($diasSemana as $num => $nombre)
                        <th>{{ $nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($horasConClase as $h)
                    @php
                        $hNext = $h + 1;
                        $hStr = sprintf('%02d:00', $h);
                        $hNextStr = sprintf('%02d:00', $hNext);
                    @endphp
                    <tr>
                        <td class="grilla-col-hora"><span class="grilla-hora-line">{{ $hStr }}</span><span class="grilla-hora-line">{{ $hNextStr }}</span></td>
                        @foreach($diasSemana as $num => $nombre)
                            <td class="grilla-cell">
                                @if(!empty($grilla[$num][$h]))
                                    @foreach($grilla[$num][$h] as $horario)
                                        <div class="grilla-clase-card">
                                            <div class="grilla-clase-nombre">{{ $horario->materia->nombre ?? '—' }}</div>
                                            <div class="grilla-clase-aula">{{ $horario->aula->numero_aula ?? '—' }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="grilla-col-hora" colspan="8">Sin horarios asignados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
