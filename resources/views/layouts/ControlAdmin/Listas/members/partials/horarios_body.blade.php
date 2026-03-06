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

<div class="teacher-horarios-body">
    <div style="display:flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end; margin-bottom: 1rem;">
        <form method="GET" action="{{ route('control.teachers.horarios', $user->id) }}" style="display:flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end; width: 100%;">
            <div style="flex: 1 1 280px;">
                <label for="q" style="display:block; font-weight: 600; margin-bottom: 0.25rem;">Buscar</label>
                <input id="q" name="q" type="text" value="{{ request('q') }}" placeholder="Materia, carrera o aula..." autocomplete="off" class="form-control">
            </div>

            <div style="width: 220px;">
                <label for="dia" style="display:block; font-weight: 600; margin-bottom: 0.25rem;">Día</label>
                <select id="dia" name="dia" class="form-control">
                    @foreach($diasNombres as $k => $label)
                        <option value="{{ $k }}" @selected((string)request('dia','') === (string)$k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap: 0.5rem;">
                <button type="submit" class="btn btn--secondary" style="height: 38px;">Filtrar</button>
                <a href="{{ route('control.teachers.horarios', $user->id) }}" class="btn btn--secondary" style="height: 38px; display:inline-flex; align-items:center;">Limpiar</a>
            </div>
        </form>
    </div>

    @include('layouts.ControlAdmin.Listas.members.partials.horarios_grilla_semanal', ['horarios' => $horarios])
</div>
