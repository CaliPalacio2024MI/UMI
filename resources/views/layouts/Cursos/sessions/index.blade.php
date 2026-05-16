@extends('layouts.app')

@section('title', 'Horarios - ' . $course->title)

@vite(['resources/css/Cursos/horarios.css'])

@section('content')

<div class="container-fluid horarios-view">
    {{-- ENCABEZADO --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">

        {{-- IZQUIERDA (TÍTULO) --}}
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">
                Horarios: {{ $course->title }}
            </h1>
            <p style="color: #64748b;">
                Gestión de sesiones y control de asistencia
            </p>
        </div>
        {{-- DERECHA (BOTÓN SALIR) --}}
        <a href="{{ route('courses.index') }}" class="btn-back">
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
        {{ $errors->first('error') }}
    </div>
    @endif

    {{-- LAYOUT PRINCIPAL --}}
    <div class="horarios-layout">

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
                                <input type="time" name="end_time" id="end_time" class="form-control-custom" readonly>
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

                                <form action="{{ route('courses.sessions.toggle', [$course, $session]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" style="background:none; border:none; cursor:pointer;">

                                        <i class="fas
                                            {{ $session->attendance_enabled
                                                ? 'fa-toggle-on text-success'
                                                : 'fa-toggle-off text-muted'
                                            }}"
                                            style="font-size:22px;">
                                        </i>

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

                                {{-- AGREGAR PARTICIPANTES --}}
                                <a href="{{ route('sessions.groups', $session->id) }}"
                                    class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-user-plus"></i>
                                </a>

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
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ==============================
    // 1. EDITAR FILA (toggle)
    // ==============================
    window.toggleEditRow = function (e, id) {
        e.preventDefault();

        const row = document.getElementById('edit-row-' + id);

        if (!row) return;

        row.style.display =
            (row.style.display === 'none' || row.style.display === '')
            ? 'table-row'
            : 'none';
    };


    // ==============================
    // 2. HORA AUTOMÁTICA
    // ==============================
    const startInput = document.getElementById("start_time");
    const endInput   = document.getElementById("end_time");

    const courseHours = {{ $course->hours ?? 0 }};

    if (startInput && endInput) {

        startInput.addEventListener("change", function () {

            if (!this.value) return;

            let [h, m] = this.value.split(":").map(Number);

            let date = new Date();
            date.setHours(h);
            date.setMinutes(m);

            // sumar horas del curso
            date.setHours(date.getHours() + courseHours);

            let endH = String(date.getHours()).padStart(2, '0');
            let endM = String(date.getMinutes()).padStart(2, '0');

            endInput.value = `${endH}:${endM}`;
        });
    }

});
</script>
@endsection
