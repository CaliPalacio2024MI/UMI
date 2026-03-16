@php
    $diasNombres = [
        '' => 'Todos',
        '1' => 'Lunes',
        '2' => 'Martes',
        '3' => 'Miércoles',
        '4' => 'Jueves',
        '5' => 'Viernes',
        '6' => 'Sábado',
        '7' => 'Domingo',
    ];
@endphp

<div class="Table-view">
    <table class="tabla-base tabla-rayas tabla-bordes" style="width: 100%;">
        <thead class="encabezado-tabla">
            <tr>
                <th>Carrera</th>
                <th>Materia</th>
                <th>Aula</th>
                <th>Franjas</th>
                <th style="text-align:center;">Acciones</th>
            </tr>
        </thead>
        <tbody class="cuerpo-tabla">
            @forelse($horarios as $horario)
                <tr>
                    <td>{{ $horario->carrera->name ?? '—' }}</td>
                    <td>{{ $horario->materia->nombre ?? '—' }}</td>
                    <td>{{ $horario->aula->numero_aula ?? '—' }}</td>
                    <td>
                        @if($horario->franjas->isEmpty())
                            <span style="color:#666;">Sin franjas</span>
                        @else
                            <div style="display:flex; flex-direction:column; gap: 0.25rem;">
                                @foreach($horario->franjas as $f)
                                    @php
                                        $dias = is_array($f->dias_semana) ? $f->dias_semana : [$f->dias_semana];
                                        $diaStr = implode(', ', array_map(fn($d) => ($diasNombres[(string)$d] ?? ('Día ' . $d)), $dias));
                                        $hIni = \Carbon\Carbon::parse($f->hora_inicio)->format('h:i A');
                                        $hFin = \Carbon\Carbon::parse($f->hora_fin)->format('h:i A');
                                    @endphp
                                    <div>{{ $diaStr }} — {{ $hIni }} a {{ $hFin }}</div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td style="text-align:center; white-space: nowrap;">
                        <a href="{{ route('control.schedules.show', $horario->id) }}" class="data-action-btn data-btn-view" title="Ver horario">
                            <img src="{{ asset('images/icons/eye-solid-full.svg') }}" alt="Ver" style="width:22px;height:22px" loading="lazy">
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding: 1rem;">
                        No se encontraron horarios para este docente.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
