@php
    $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $defaultInicio = 7;
    $defaultFin = 21;

    $materiaLabels = $materiaLabels ?? [];
    $horarioResumenPorClase = $horarioResumenPorClase ?? [];

    $horariosCol = $horarios ?? collect();
    $placements = [];

    foreach ($horariosCol as $horario) {
        $horario->loadMissing(['materia', 'aula', 'franjas']);
        $resumen = $horarioResumenPorClase[$horario->id] ?? null;
        $fromResumen = ($resumen !== null && trim((string) $resumen) !== '')
            ? \App\Support\HorarioResumenParser::intervalsFromResumen($resumen)
            : [];

        if ($fromResumen !== []) {
            foreach ($fromResumen as $iv) {
                $placements[] = [$horario, $iv['dia'], $iv['startMin'], $iv['endMin']];
            }
        } else {
            foreach ($horario->franjas ?? [] as $f) {
                if (empty($f->hora_inicio) || empty($f->hora_fin)) {
                    continue;
                }
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
                if ($fEnd <= $fStart) {
                    continue;
                }
                foreach ($dias as $dia) {
                    $placements[] = [$horario, $dia, $fStart, $fEnd];
                }
            }
        }
    }

    $horaInicio = $defaultInicio;
    $horaFin = $defaultFin;
    if ($placements !== []) {
        $minH = 23;
        $maxSlotStart = 0;
        foreach ($placements as $p) {
            $fStart = $p[2];
            $fEnd = $p[3];
            $minH = min($minH, intdiv($fStart, 60));
            $maxSlotStart = max($maxSlotStart, intdiv(max(0, $fEnd - 1), 60));
        }
        $horaInicio = max(0, min($defaultInicio, $minH));
        $horaFin = min(24, max($defaultFin, $maxSlotStart + 1, $horaInicio + 1));
    }

    $grilla = [];
    for ($d = 1; $d <= 7; $d++) {
        for ($hr = $horaInicio; $hr < $horaFin; $hr++) {
            $grilla[$d][$hr] = [];
        }
    }

    foreach ($placements as $p) {
        $horario = $p[0];
        $dia = $p[1];
        $fStart = $p[2];
        $fEnd = $p[3];
        for ($hr = $horaInicio; $hr < $horaFin; $hr++) {
            $slotStart = $hr * 60;
            $slotEnd = ($hr + 1) * 60;
            $haySolapamiento = $fStart < $slotEnd && $fEnd > $slotStart;
            if (!$haySolapamiento || !isset($grilla[$dia][$hr])) {
                continue;
            }
            $yaEsta = false;
            foreach ($grilla[$dia][$hr] as $h) {
                if (isset($h->id, $horario->id) && $h->id === $horario->id) {
                    $yaEsta = true;
                    break;
                }
            }
            if (!$yaEsta) {
                $grilla[$dia][$hr][] = $horario;
            }
        }
    }

    $horasConClase = range($horaInicio, $horaFin - 1);
@endphp

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
                @foreach($horasConClase as $h)
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
                                            <div class="grilla-clase-nombre">{{ $materiaLabels[$horario->id] ?? ($horario->materia->nombre ?? '—') }}</div>
                                            <div class="grilla-clase-aula">{{ \App\Support\AulaHorarioPresenter::grillaLine($horario->aula) }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
