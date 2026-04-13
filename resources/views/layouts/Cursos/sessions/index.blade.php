@extends('layouts.app')

@section('title', 'Horarios - ' . $course->title)

@vite(['resources/css/Cursos/horarios.css'])

@section('content')

<div class="container-fluid horarios-view">

    {{-- ENCABEZADO --}}
    <div style="margin-bottom: 25px;">
        <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">
            Horarios: {{ $course->title }}
        </h1>
        <p style="color: #64748b;">
            Gestión de sesiones y control de asistencia
        </p>
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
        {{ $errors->first('error') }}
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

                            {{-- ASISTENCIA --}}
                            <td class="text-center">
                                <i class="fas {{ $session->attendance_enabled ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                            </td>

                            {{-- ACCIONES --}}
                            <td class="text-center" style="display:flex; gap:6px; justify-content:center;">

                                {{-- EDITAR --}}
                                <button onclick="toggleEditRow(event, '{{ $session->id }}')"
                                        class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#crearGrupoModal"
                                    onclick="setGrupoSession(
                                        '{{ $session->id }}',
                                        '{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}',
                                        '{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}',
                                        '{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}'
                                        )">
                                    <i class="fa-solid fa-plus"></i>  Grupo
                                </button>

                                {{-- AGREGAR PARTICIPANTES --}}
                                <button
                                    type="button"
                                    class="btn btn-sm btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#participantsModal"
                                    onclick="setParticipantsSession(
                                    '{{ $session->id }}',
                                    '{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}',
                                    '{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}',
                                    '{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}'
                                    )">
                                    <i class="fa-solid fa-user-plus"></i>
                                </button>

                                <form action="{{ route('courses.sessions.destroy', [$course->id, $session->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn-delete-session">
                                        <i class="fa-solid fa-delete-left"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>

                        {{-- FILA EDIT --}}
                        <tr id="edit-row-{{ $session->id }}" style="display:none;" class="edit-row-active">
                            <td colspan="5">
                                <form action="{{ route('courses.sessions.update', [$course, $session]) }}" method="POST" style="display:flex; gap:10px;">
                                    @csrf
                                    @method('PUT')

                                    <input type="date" name="date" class="form-control-custom" value="{{ $session->date }}">
                                    <input type="time" name="start_time" class="form-control-custom" value="{{ $session->start_time }}">
                                    <input type="time" name="end_time" class="form-control-custom" value="{{ $session->end_time }}">

                                    <button class="btn btn-primary btn-sm">OK</button>
                                </form>
                            </td>
                        </tr>

                    @endforeach
                </tbody>

                </table>
            </div>

        </div>
    </div>

        {{-- ================= FORMULARIO ================= --}}

        <div class="horarios-form-section">

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
                            <input type="date" name="date" class="form-control-custom" required>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <div class="form-group-custom" style="flex:1;">
                                <label class="label-custom">Hora inicio</label>
                                <input type="time" name="start_time" id="start_time" class="form-control-custom" required>
                            </div>

                            <div class="form-group-custom" style="flex:1;">
                                <label class="label-custom">Hora fin</label>
                                <input type="time" name="end_time" id="end_time" class="form-control-custom" required readonly>
                            </div>
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
 <div class="modal fade" id="participantsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5>
                    Participantes -
                    <span id="participantsHorarioInfo"></span>
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form action="{{ route('groups.addParticipants') }}" method="POST">
                    @csrf

                        <input type="hidden" name="session_id" id="participants_session_id">
                        <div class="mb-3">
                            <label>Departamentos</label>
                            <select id="participants_departments" class="form-control" multiple></select>
                        </div>

                        <div class="mb-3">
                            <label>Puestos</label>
                            <select id="participants_workstations" class="form-control" multiple></select>
                        </div>

                        <div class="mb-3">
                            <label>ID Participantes</label>
                            <select name="users[]" class="form-control" multiple required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button class="btn btn-primary w-100">
                            Guardar participantes
                        </button>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="crearGrupoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                {{-- HEADER --}}
                <div class="modal-header">
                    <h5 class="modal-title">
                        Horario:
                       <span id="modalHorarioInfo" style="font-weight:600; color:#38bdf8;"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">

                <form action="{{ route('sessions.group.store') }}" method="POST">
                    @csrf

                    <input type="hidden" name="session_id" id="session_id">

                    <div class="row">

                       {{-- COLUMNA 1 --}}
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Departamentos</label>
                                <select id="departments" name="departments[]" class="form-control" multiple required>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                          </div>
                        </div>

                        {{-- COLUMNA 2 --}}
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Puestos</label>
                                <select id="workstations" name="workstations[]" class="form-control" multiple required>
                                    <option disabled>Selecciona un departamento primero</option>
                                </select>
                            </div>
                        </div>

                        {{-- COLUMNA 3 --}}
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Afitriones</label>
                                <select name="users[]" class="form-control" multiple required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                   {{-- SEGUNDA FILA --}}
                    <div class="row mt-3">

                        <div class="col-md-6">
                            <label class="form-label">Tipo de grupo</label>
                            <select name="type" class="form-control" required>
                                <option value="abierto">Abierto</option>
                                <option value="cerrado">Cerrado</option>
                           </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Mínimo</label>
                            <input type="number" name="min_participants" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Máximo</label>
                            <input type="number" name="max_participants" class="form-control" min="1" required>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4">
                        Guardar
                    </button>

                </form>

            </div>

        </div>
    </div>
</div>
</div>

<script>
// EDIT ROW
function toggleEditRow(e, id) {
    e.preventDefault();
    const row = document.getElementById('edit-row-' + id);
    row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
}
</script>

<script>
// DEPARTAMENTOS → PUESTOS
document.addEventListener("DOMContentLoaded", function () {

    const departmentsSelect = document.getElementById("departments");
    const workstationsSelect = document.getElementById("workstations");

    if (!departmentsSelect) return;

    departmentsSelect.addEventListener("change", function () {

        const selected = Array.from(this.selectedOptions).map(o => o.value);

        fetch(`/api/workstations-by-departments`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ departments: selected })
        })
        .then(res => res.json())
        .then(data => {

            workstationsSelect.innerHTML = "";

            data.forEach(w => {
                const option = document.createElement("option");
                option.value = w.id;
                option.textContent = w.name;
                workstationsSelect.appendChild(option);
            });

        });

    });

});
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
<script>
function setParticipantsSession(id, date, start, end) {

    document.getElementById('participants_session_id').value = id;

    document.getElementById('participantsHorarioInfo').innerText =
        `${date} | ${start} - ${end}`;

    // FETCH DATA DEL GRUPO
    fetch(`/sessions/${id}/group-data`)
    .then(res => res.json())
    .then(data => {

        let depSelect = document.getElementById('participants_departments');
        let workSelect = document.getElementById('participants_workstations');

        depSelect.innerHTML = '';
        workSelect.innerHTML = '';

        data.departments.forEach(d => {
            let option = new Option(d.name, d.id);
            depSelect.add(option);
        });

        data.workstations.forEach(w => {
            let option = new Option(w.name, w.id);
            workSelect.add(option);
        });

    });
}
</script>
<script>
function setGrupoSession(id, date, start, end) {

    document.getElementById('session_id').value = id;

    document.getElementById('modalHorarioInfo').innerText =
        `${date} | ${start} - ${end}`;
}
</script>
@endsection
