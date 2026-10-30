@extends('layouts.app')

@section('title', 'Horarios - ' . $course->title)

@vite(['resources/css/Cursos/horarios.css'])

@section('content')

<div class="container-fluid horarios-view">

    {{-- ENCABEZADO (con botón Salir, mejora de Erick) --}}
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:25px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">
                Horarios: {{ $course->title }}
            </h1>
            <p style="color: #64748b;">
                Gestión de sesiones y control de asistencia
            </p>
        </div>
        <a href="{{ url('/cursos') }}" class="btn-salir">
            <i class="fas fa-arrow-left"></i>
            Salir
        </a>
    </div>

    {{-- ALERTA --}}
    @if(session('success'))
        <div style="padding: 15px; background-color: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #22c55e;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
    <div style="padding: 15px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 15px;">
        <i class="fas fa-exclamation-circle"></i>
        {{ $errors->first('error') ?: $errors->first() }}
    </div>
    @endif

    {{-- LAYOUT PRINCIPAL --}}
    <div class="horarios-layout">

        {{-- ================= TABLA ================= --}}
        <div class="horarios-tabla-section">

            <div class="card-custom">

            <div class="card-header-custom">
                <i class="fas fa-list-ul"></i>
                <strong>Sesiones Registradas</strong>
            </div>

            <div class="table-responsive">
                <table class="table-custom-sessions">

                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Instructor</th>
                        <th class="text-center">Asist.</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($sessions as $session)

                        {{-- FILA NORMAL --}}
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                            <td>{{ $session->instructor_name }}</td>

                            {{-- ASISTENCIA --}}
                            <td class="text-center">
                                {{-- Activar / desactivar la asistencia (QR) de este horario --}}
                                <form action="{{ route('courses.sessions.toggle', [$course, $session]) }}" method="POST" style="display:inline; margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="{{ $session->attendance_enabled ? 'Asistencia activa — clic para desactivar' : 'Asistencia inactiva — clic para activar' }}"
                                            style="border:none; background:none; padding:0; cursor:pointer; font-size:1.6rem; line-height:1;">
                                        <i class="fas {{ $session->attendance_enabled ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                    </button>
                                </form>
                            </td>

                            {{-- ACCIONES --}}
                            <td class="text-center" style="display:flex; gap:6px; justify-content:center;">

                                {{-- EDITAR --}}
                                <button onclick="toggleEditRow(event, '{{ $session->id }}')"
                                        class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @php
                                    $horarioLabel = \Carbon\Carbon::parse($session->date)->format('d/m/Y') . ' · '
                                        . \Carbon\Carbon::parse($session->start_time)->format('H:i') . ' - '
                                        . \Carbon\Carbon::parse($session->end_time)->format('H:i');
                                    $grupoHorario = $session->groups->first();
                                    $totalParticipantes = $grupoHorario ? $grupoHorario->participants()->count() : 0;
                                @endphp

                                {{-- LISTA DE PARTICIPANTES (gris): Propiedad → Departamento → Puesto → Anfitriones / Clasificación → Carrera → Materia → Alumnos --}}
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Lista de participantes{{ $grupoHorario ? ' (' . $totalParticipantes . ')' : '' }}"
                                        onclick="abrirListaParticipantes('{{ $session->id }}', @js($horarioLabel))">
                                    <i class="fas fa-chart-bar"></i>
                                </button>

                                {{-- ASIGNAR USUARIOS (verde): panel lateral igual que en virtual --}}
                                <button type="button"
                                        class="btn btn-sm btn-success"
                                        title="Asignar Usuarios"
                                        onclick="openUsersModal('{{ $session->id }}', @js($horarioLabel), {{ $session->attendance_enabled ? 'true' : 'false' }})">
                                    <i class="fa-solid fa-user-plus"></i>
                                </button>

                                <form id="delete-session-form-{{ $session->id }}"
                                      action="{{ route('courses.sessions.destroy', [$course->id, $session->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    {{-- Abre el modal de advertencia antes de eliminar --}}
                                    <button type="button" class="btn-delete-session"
                                        onclick="confirmarEliminarHorario(
                                        '{{ $session->id }}',
                                        '{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}',
                                        '{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}',
                                        '{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}'
                                        )">
                                        <i class="fa-solid fa-delete-left"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>

                        {{-- FILA EDIT --}}
                        <tr id="edit-row-{{ $session->id }}" style="display:none;" class="edit-row-active">
                            <td colspan="6">
                                <form action="{{ route('courses.sessions.update', [$course, $session]) }}" method="POST" style="display:flex; gap:10px;">
                                    @csrf
                                    @method('PUT')

                                    <input type="date" name="date" class="form-control-custom" value="{{ $session->date }}">
                                    <input type="time" name="start_time" class="form-control-custom" value="{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}" onchange="autoCalcEditTime(this, {{ $course->hours ?? 0 }})">
                                    <input type="time" name="end_time" class="form-control-custom" value="{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}">
                                    <input type="text" name="instructor_name" class="form-control-custom" value="{{ $session->instructor_name }}" placeholder="Instructor" maxlength="255" required>

                                    <button class="btn btn-primary btn-sm">OK</button>
                                </form>
                            </td>
                        </tr>

                    @endforeach

                    @if(count($sessions) === 0)
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #999;">
                                <div style="font-size: 2.5em; margin-bottom: 10px;">📅</div>
                                <div style="font-size: 1.1em;">No hay sesiones registradas aún.</div>
                            </td>
                        </tr>
                    @endif
                </tbody>

                </table>
            </div>

        </div>
    </div>

        {{-- ================= FORMULARIO ================= --}}

        <div class="horarios-form-section" style="order:-1;">{{-- Formulario a la izquierda, igual que Periodos de virtual --}}

            <div class="card-custom">

                {{-- HEADER --}}
                <div class="header-accent-blue">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nuevo Horario</span>
                </div>

                {{-- BODY --}}
                <div style="padding:20px;">

                    <form action="{{ route('courses.sessions.store', $course) }}" method="POST">
                        @csrf

                        <div class="form-group-custom">
                            <label class="label-custom">Fecha de la sesión</label>
                            <input type="date" name="date" class="form-control-custom" value="{{ old('date') }}" required oninvalid="this.setCustomValidity('Por favor, selecciona una fecha.')" oninput="this.setCustomValidity('')">
                        </div>

                        <div style="display:flex; gap:10px;">
                            <div class="form-group-custom" style="flex:1;">
                                <label class="label-custom">Hora inicio</label>
                                <input type="time" name="start_time" id="start_time" class="form-control-custom" value="{{ old('start_time') }}" required oninvalid="this.setCustomValidity('Falta la hora de inicio.')" oninput="this.setCustomValidity('')">
                            </div>

                            <div class="form-group-custom" style="flex:1;">
                                <label class="label-custom">Hora fin</label>
                                <input type="time" name="end_time" id="end_time" class="form-control-custom" value="{{ old('end_time') }}" required oninvalid="this.setCustomValidity('Falta la hora de término.')" oninput="this.setCustomValidity('')">
                            </div>
                        </div>

                        <div class="form-group-custom">
                            <label class="label-custom">Instructor</label>
                            <input type="text"
                                name="instructor_name"
                                class="form-control-custom"
                                placeholder="Nombre del instructor"
                                maxlength="255"
                                value="{{ old('instructor_name') }}"
                                required
                                oninvalid="this.setCustomValidity('Por favor, ingresa el nombre del instructor.')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-save"></i>
                            Registrar Horario
                        </button>

                    </form>

                </div>

            </div>
        </div>

    </div>
 </div>
{{-- Ventanas compartidas: eliminar, lista de participantes y asignar usuarios --}}
@include('layouts.Cursos.partials.participantes', [
    'pp_entity'     => 'horario',
    'pp_catalogUrl' => url('/sessions/__ID__/participants-catalog'),
    'pp_storeUrl'   => route('sessions.group.store'),
    'pp_usersUrl'   => url('/sessions/__ID__/usuarios'),
    'pp_toggleUrl'  => url('/sessions/__ID__/usuarios/toggle'),
])
</div>


<script>
// Al editar un horario, recalcular la hora fin con las horas del curso (mejora de Erick)
window.autoCalcEditTime = function (startInput, courseHours) {
    if (!startInput.value) return;
    const endInput = startInput.closest('form')?.querySelector('input[name="end_time"]');
    if (!endInput) return;
    const [h, m] = startInput.value.split(':').map(Number);
    const date = new Date();
    date.setHours(h, m, 0, 0);
    date.setHours(date.getHours() + Number(courseHours || 0));
    endInput.value = String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0');
};
</script>

<script>
// EDIT ROW
function toggleEditRow(e, id) {
    e.preventDefault();
    const row = document.getElementById('edit-row-' + id);
    row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
}
</script>


<script>
// AUTO CALCULAR HORA FIN
document.addEventListener('DOMContentLoaded', function () {

    const startInput = document.getElementById('start_time');
    const endInput = document.getElementById('end_time');

    if (!startInput) return;

    startInput.addEventListener('change', function () {

        let hours = {{ $course->hours }};
        let start = this.value;

        if (!start) return;

        let [h, m] = start.split(':');

        let date = new Date();
        date.setHours(parseInt(h));
        date.setMinutes(parseInt(m));

        date.setHours(date.getHours() + hours);

        let endH = String(date.getHours()).padStart(2, '0');
        let endM = String(date.getMinutes()).padStart(2, '0');

        endInput.value = `${endH}:${endM}`;
    });
});
</script>

@endsection