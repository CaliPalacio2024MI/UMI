@extends('layouts.app')

@section('title', 'Grupos')

@vite(['resources/css/group.css'])

@section('content')

<div class="groups-wrapper">

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

            {{-- CONFIGURACIÓN --}}
            <div class="table-box">

                <h3>CONFIGURACIÓN</h3>

                <h4>Departamentos</h4>
                    <label class="select-all-label">
                        <input type="checkbox" id="select-all-departments">
                        Seleccionar todo
                    </label>

                <input type="text"
                       id="department-search"
                       class="search-input"
                       placeholder="Buscar departamento...">

                <div id="departments-list" class="checklist checklist-box">
                    @foreach($departments as $dept)
                        <label class="check-item">
                            <input type="checkbox"
                                   value="{{ $dept->id }}"
                                   data-name="{{ $dept->name }}"
                                   {{ in_array($dept->id, $selectedDepartments ?? []) ? 'checked' : '' }}>

                            <span>{{ $dept->name }}</span>
                        </label>
                    @endforeach
                </div>

                <h4>Puestos</h4>
                    <label class="select-all-label">
                        <input type="checkbox" id="select-all-workstations">
                        Seleccionar todo
                    </label>

                <div id="workstations-list" class="checklist checklist-box">
                    <p class="empty">Selecciona departamentos</p>
                </div>

                <h4>Anfitriones</h4>
                    <label class="select-all-label">
                        <input type="checkbox" id="select-all-hosts">
                        Seleccionar todo
                    </label>

                <div id="hosts-list" class="checklist checklist-box">
                    <p class="empty">Selecciona departamentos y puestos</p>
                </div>

            </div>

            {{-- SELECCIÓN FINAL --}}
            <div class="table-box">

                <h3>SELECCIÓN FINAL</h3>

                <div class="selected-users-table-wrapper">

                    <table class="selected-users-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <th>Puesto</th>
                            </tr>
                        </thead>

                        <tbody id="selected-hosts-display">
                            <tr>
                                <td colspan="3" class="empty">
                                    No hay anfitriones seleccionados
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <div id="hidden-departments"></div>
                <div id="hidden-workstations"></div>
                <div id="hidden-hosts"></div>

                <button type="submit" class="btn-save">
                    Guardar todo
                </button>

            </div>

        </div>

    </form>

</div>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const departments = @json($departments ?? []);
    const selectedWorkstations = @json($selectedWorkstations ?? []);
    const selectedHosts = @json($selectedHosts ?? []);

    const departmentSearch = document.getElementById('department-search');

    const selectAllDepartments =
        document.getElementById('select-all-departments');

    const selectAllWorkstations =
        document.getElementById('select-all-workstations');

    const selectAllHosts =
        document.getElementById('select-all-hosts');


    // ==========================
    // BUSCADOR DE DEPARTAMENTOS
    // ==========================
    if (departmentSearch) {

        departmentSearch.addEventListener('keyup', function () {

            const value = this.value.toLowerCase();

            document.querySelectorAll(
                '#departments-list .check-item'
            ).forEach(item => {

                const text = item.innerText.toLowerCase();

                item.style.display =
                    text.includes(value)
                    ? 'flex'
                    : 'none';
            });
        });
    }


    // ==========================
    // DEPARTAMENTOS
    // ==========================
    function syncDepartments() {

        const container =
            document.getElementById('hidden-departments');

        container.innerHTML = '';

        document.querySelectorAll(
            '#departments-list input:checked'
        ).forEach(dept => {

            container.innerHTML += `
                <input
                    type="hidden"
                    name="departments[]"
                    value="${dept.value}">
            `;
        });
    }


    // ==========================
    // PUESTOS
    // ==========================
    function syncWorkstations() {

        const container =
            document.getElementById('hidden-workstations');

        container.innerHTML = '';

        document.querySelectorAll(
            '#workstations-list input:checked'
        ).forEach(ws => {

            container.innerHTML += `
                <input
                    type="hidden"
                    name="workstations[]"
                    value="${ws.value}">
            `;
        });

        loadHosts();
    }


    // ==========================
    // ANFITRIONES
    // ==========================
    function syncHosts() {

        const container =
            document.getElementById('hidden-hosts');

        const display =
            document.getElementById('selected-hosts-display');

        container.innerHTML = '';

        const checkedHosts = Array.from(
            document.querySelectorAll('.host-check:checked')
        );

        if (checkedHosts.length === 0) {

            display.innerHTML = `
                <tr>
                    <td colspan="3" class="empty">
                        No hay anfitriones seleccionados
                    </td>
                </tr>
            `;

            return;
        }

        display.innerHTML = '';

        checkedHosts.forEach(host => {

            container.innerHTML += `
                <input
                    type="hidden"
                    name="hosts[]"
                    value="${host.value}">
            `;

            display.innerHTML += `
                <tr>
                    <td>${host.dataset.name}</td>
                    <td>${host.dataset.department}</td>
                    <td>${host.dataset.workstation}</td>
                </tr>
            `;
        });
    }


    // ==========================
    // CARGAR PUESTOS
    // ==========================
    function loadWorkstations() {

        let html = '';

        const selectedDepartmentsList = Array.from(
            document.querySelectorAll(
                '#departments-list input:checked'
            )
        ).map(c => parseInt(c.value));

        if (selectedDepartmentsList.length === 0) {

            html = `
                <p class="empty">
                    Selecciona departamentos
                </p>
            `;

        } else {

            selectedDepartmentsList.forEach(id => {

                const dept =
                    departments.find(d => d.id == id);

                if (dept && dept.workstations) {

                    dept.workstations.forEach(w => {

                        const checked =
                            selectedWorkstations.includes(w.id)
                            ? 'checked'
                            : '';

                        html += `
                            <label class="check-item">

                                <input
                                    type="checkbox"
                                    value="${w.id}"
                                    data-name="${w.name}"
                                    ${checked}>

                                <span>${w.name}</span>

                            </label>
                        `;
                    });
                }
            });
        }

        const workstationsList =
            document.getElementById('workstations-list');

        workstationsList.innerHTML = html;

        if (selectAllWorkstations) {
            selectAllWorkstations.checked = false;
        }

        document.querySelectorAll(
            '#workstations-list input'
        ).forEach(check => {

            check.addEventListener(
                'change',
                syncWorkstations
            );
        });

        syncWorkstations();
    }


    // ==========================
    // CARGAR ANFITRIONES
    // ==========================
    async function loadHosts() {

        const selectedDepartmentsList = Array.from(
            document.querySelectorAll(
                '#departments-list input:checked'
            )
        ).map(c => parseInt(c.value));

        const selectedWorkstationsList = Array.from(
            document.querySelectorAll(
                '#workstations-list input:checked'
            )
        ).map(c => parseInt(c.value));

        const hostsList =
            document.getElementById('hosts-list');

        if (selectedDepartmentsList.length === 0) {

            hostsList.innerHTML = `
                <p class="empty">
                    Selecciona un departamento
                </p>
            `;

            syncHosts();

            return;
        }

        try {

            const response = await fetch(
                '{{ route("get.participants.by.filters") }}',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },

                    body: JSON.stringify({
                        departments: selectedDepartmentsList,
                        workstations: selectedWorkstationsList
                    })
                }
            );

            const result = await response.json();

            const hosts =
                result.anfitriones || [];

            if (hosts.length === 0) {

                hostsList.innerHTML = `
                    <p class="empty">
                        No hay anfitriones disponibles
                    </p>
                `;

                syncHosts();

                return;
            }

            let html = '';

            hosts.forEach(host => {

                const checked =
                    selectedHosts.includes(host.id)
                    ? 'checked'
                    : '';

                html += `
                    <label class="check-item">

                        <input
                            type="checkbox"
                            class="host-check"
                            value="${host.id}"

                            data-name="${host.name}"

                            data-department="${host.department_name || 'Sin departamento'}"

                            data-workstation="${host.workstation_name || 'Sin puesto'}"

                            ${checked}>

                        <span>${host.name}</span>

                    </label>
                `;
            });

            hostsList.innerHTML = html;

            if (selectAllHosts) {
                selectAllHosts.checked = false;
            }

            document.querySelectorAll(
                '.host-check'
            ).forEach(check => {

                check.addEventListener(
                    'change',
                    syncHosts
                );
            });

            syncHosts();

        } catch (error) {

            console.error(error);

            hostsList.innerHTML = `
                <p class="empty">
                    Error al cargar anfitriones
                </p>
            `;
        }
    }


    // ==========================
    // SELECCIONAR TODOS
    // DEPARTAMENTOS
    // ==========================
    if (selectAllDepartments) {

        selectAllDepartments.addEventListener(
            'change',
            function () {

                document.querySelectorAll(
                    '#departments-list input'
                ).forEach(input => {

                    input.checked = this.checked;
                });

                syncDepartments();
                loadWorkstations();
                loadHosts();
            }
        );
    }


    // ==========================
    // SELECCIONAR TODOS
    // PUESTOS
    // ==========================
    if (selectAllWorkstations) {

        selectAllWorkstations.addEventListener(
            'change',
            function () {

                document.querySelectorAll(
                    '#workstations-list input'
                ).forEach(input => {

                    input.checked = this.checked;
                });

                syncWorkstations();
            }
        );
    }


    // ==========================
    // SELECCIONAR TODOS
    // ANFITRIONES
    // ==========================
    if (selectAllHosts) {

        selectAllHosts.addEventListener(
            'change',
            function () {

                document.querySelectorAll(
                    '.host-check'
                ).forEach(input => {

                    input.checked = this.checked;
                });

                syncHosts();
            }
        );
    }


    // ==========================
    // EVENTOS DEPARTAMENTOS
    // ==========================
    document.querySelectorAll(
        '#departments-list input'
    ).forEach(check => {

        check.addEventListener(
            'change',
            () => {

                syncDepartments();
                loadWorkstations();
                loadHosts();
            }
        );
    });

    syncDepartments();
    loadWorkstations();

});
</script>

@endsection
