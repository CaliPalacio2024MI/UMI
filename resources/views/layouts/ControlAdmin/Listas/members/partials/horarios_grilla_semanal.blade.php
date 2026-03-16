@php
    $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $horaInicio = 7;
    $horaFin = 21; // 7:00 a 21:00 — celdas por hora
    $grilla = [];
    for ($d = 1; $d <= 7; $d++) {
        for ($hr = $horaInicio; $hr < $horaFin; $hr++) {
            $grilla[$d][$hr] = [];
        }
    }
    // Datos desde el módulo de clases (HorarioClase con materia, aula, franjas)
    $horariosCol = $horarios ?? collect();
    foreach ($horariosCol as $horario) {
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
                for ($hr = $horaInicio; $hr < $horaFin; $hr++) {
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
                                            <div class="grilla-clase-nombre">{{ $horario->materia->nombre ?? '—' }}</div>
                                            <div class="grilla-clase-aula">aula {{ $horario->aula->numero_aula ?? '—' }}</div>
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
