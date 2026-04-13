@extends('layouts.app')

@section('title', 'Grupos')

@vite(['resources/css/group.css', ])

@section('content')

<div class="groups-wrapper">

    {{-- HEADER --}}
    <div class="header-groups">
        <h2>SECCIONES - {{ $session->date }}</h2>

        <a href="{{ route('courses.sessions.index', $session->course_id) }}" class="btn-back">
            Volver a horarios
        </a>
    </div>

<form action="{{ route('groups.store') }}" method="POST">
    @csrf

    <input type="hidden" name="session_id" value="{{ $session->id }}">

    <div class="tables-layout">

    {{-- ================= BLOQUE IZQUIERDO ================= --}}
    <div class="table-box">

        <h3>CONFIGURACIÓN</h3>


        {{-- ===== DEPARTAMENTOS ===== --}}
        <h4>Departamentos</h4>
        <div id="departments-list" class="checklist checklist-box">
            @foreach($departments as $dept)
                <label class="check-item">
                    <input type="checkbox"
                           value="{{ $dept->id }}"
                           {{ in_array($dept->id, $selectedDepartments) ? 'checked' : '' }}>
                    <span>{{ $dept->name }}</span>
                </label>
            @endforeach
        </div>

        {{-- ===== PUESTOS ===== --}}
        <h4>Puestos</h4>
        <div id="workstations-list" class="checklist checklist-box">
            <p class="empty">Selecciona departamentos</p>
        </div>


        {{-- ===== USUARIOS ===== --}}
        <h4>Usuarios</h4>
        <div class="users-box">
            <div class="users-list">
                @foreach($users as $user)
                    <div class="user-item
                        {{ in_array($user->id, $selectedUsers) ? 'active' : '' }}"
                        data-id="{{ $user->id }}">
                        {{ $user->name }}
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ================= BLOQUE DERECHO ================= --}}
    <div class="table-box">

        <h3>SELECCIÓN FINAL</h3>

        <p class="empty">
            Selecciona usuarios, departamentos y puestos.
        </p>

        {{-- INPUTS OCULTOS --}}
        <div id="hidden-departments"></div>
        <div id="hidden-users"></div>

        <button type="submit" class="btn-save">
            Guardar todo
        </button>

    </div>

</div>

    </form>

</div>


<script>
document.addEventListener("DOMContentLoaded", () => {

    const departments = @json($departments);
    const selectedDepartments = @json($selectedDepartments);
    const selectedWorkstations = @json($selectedWorkstations);
    const selectedUsers = @json($selectedUsers);

    // =========================
    // SINCRONIZAR DEPARTAMENTOS
    // =========================
    function syncDepartments() {
        const container = document.getElementById('hidden-departments');
        container.innerHTML = '';

        document.querySelectorAll('#departments-list input:checked').forEach(c => {
            container.innerHTML += `
                <input type="hidden" name="departments[]" value="${c.value}">
            `;
        });
    }

    // =========================
    // CARGAR PUESTOS
    // =========================
    function loadWorkstations() {

        let html = '';

        const selected = Array.from(
            document.querySelectorAll('#departments-list input:checked')
        ).map(c => parseInt(c.value));

        if (selected.length === 0) {
            html = `<p class="empty">Selecciona departamentos</p>`;
        } else {

            selected.forEach(id => {

                const dept = departments.find(d => d.id == id);

                if (dept && dept.workstations) {
                    dept.workstations.forEach(w => {

                        const checked = selectedWorkstations.includes(w.id) ? 'checked' : '';

                        html += `
                            <label class="check-item">
                                <input type="checkbox" name="workstations[]" value="${w.id}" ${checked}>
                                ${w.name}
                            </label>
                        `;
                    });
                }
            });
        }

        document.getElementById('workstations-list').innerHTML = html;
    }

    // =========================
    // EVENTO DEPARTAMENTOS
    // =========================
    document.querySelectorAll('#departments-list input').forEach(check => {
        check.addEventListener('change', () => {
            syncDepartments();
            loadWorkstations();
        });
    });

    // =========================
    // USUARIOS
    // =========================
    let activeUsers = [...selectedUsers];

    function syncUsers() {
        const container = document.getElementById('hidden-users');
        container.innerHTML = '';

        activeUsers.forEach(id => {
            container.innerHTML += `
                <input type="hidden" name="users[]" value="${id}">
            `;
        });
    }

    document.querySelectorAll('.user-item').forEach(user => {

        const id = parseInt(user.dataset.id);

        user.addEventListener('click', function() {

            if (activeUsers.includes(id)) {
                activeUsers = activeUsers.filter(u => u !== id);
                this.classList.remove('active');
            } else {
                activeUsers.push(id);
                this.classList.add('active');
            }

            syncUsers();
        });
    });

    // =========================
    // INIT
    // =========================
    syncDepartments();
    syncUsers();
    loadWorkstations();

});
</script>
@endsection
