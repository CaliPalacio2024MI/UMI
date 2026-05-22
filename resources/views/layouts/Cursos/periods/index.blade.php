@extends('layouts.app')

@vite('resources/css/Cursos/periods.css')
@vite('resources/css/Cursos/horarios.css')

@section('title', 'Períodos - ' . $course->title)

@section('content')
<div class="periods-container">

    {{-- Encabezado --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">

        {{-- Izquierda --}}
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">
                Vigencias: {{ $course->title }}
            </h1>

            <p style="color: #64748b;">
                Gestion de periodos y acceso del curso virtual
            </p>
        </div>

        {{-- Derecha --}}
        {{-- DERECHA (BOTÓN SALIR) --}}
        <a href="{{ route('courses.index') }}" class="btn-salir">
            <i class="fas fa-arrow-left"></i>
            Salir
        </a>
    </div>

    <div class="horarios-layout">
    {{-- Formulario crear período --}}
    <div class="horarios-form-section">
    <div class="card-custom">

        <div class="header-accent-blue">
            <i class="fas fa-calendar-plus"></i>
            <span>Nueva Periodo</span>
        </div>

        <div style="padding:20px;">

        <form action="{{ route('courses.periods.store',$course) }}" method="POST">

            @csrf

            <div style="display:flex; flex-direction:column; gap:15px;">

                {{-- Fecha de inicio --}}
                <div class="form-group">
                    <label class="label-custom">
                        Fecha inicio
                    </label>

                    <input type="date"
                            name="start_date"
                            min="{{ now()->format('Y-m-d') }}"
                            required
                            class="form-control-custom">
                </div>

                {{-- Fecha fin --}}
                <div class="form-group">
                    <label class="label-custom">
                        Fecha fin
                    </label>

                    <input  type="date"
                            name="end_date"
                            min="{{ now()->format('Y-m-d') }}"
                            required
                            class="form-control-custom">
                </div>

                {{-- Boton --}}
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-save"></i>
                    Registrar Vigencia
                </button>
            </div>
        </form>

    </div>
    </div>
    </div>


    <div class="horarios-tabla-section">

        <div class="card-custom">

            <div class="card-header-custom">
                <i class="fas fa-list-ul"></i>
                <strong>Periodos Registrados</strong>
            </div>

            {{-- Lista de períodos --}}
            <div class="table-responsive">
                <table class="table-custom-sessions">
                    <thead>
                        <tr>
                            <th style="padding: 18px; text-align: center; font-weight: 700; color: white;">Fecha Inicio</th>
                            <th style="padding: 18px; text-align: center; font-weight: 700; color: white;">Fecha Fin</th>
                            <th style="padding: 18px; text-align: center; font-weight: 700; color: white;">Asist.</th>
                            <th style="padding: 18px; text-align: center; font-weight: 700; color: white;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                        @php
                        $today = \Carbon\Carbon::today();
                        $estado = 'No iniciado';
                        $color = '#6c757d';
                        $icon = '⏳';

                        if ($today->lessThan($period->start_date)) {
                        $estado = 'No iniciado';
                        $color = '#6c757d';
                        $icon = '⏳';
                        } elseif ($today->greaterThan($period->end_date)) {
                        $estado = 'Finalizado';
                        $color = '#dc3545';
                        $icon = '✓';
                        } else {
                        $estado = 'Activo';
                        $color = '#28a745';
                        $icon = '🟢';
                        }
                        @endphp
                        <tr style="border-bottom: 1px solid #f0f0f0; transition:0.2s">
                            <td style="padding: 18px; text-align: center; font-weight: 500;">{{ $period->start_date->format('d/m/Y') }}</td>
                            <td style="padding: 18px; text-align: center; font-weight: 500;">{{ $period->end_date->format('d/m/Y') }}</td>
                            <td class="text-center">

                                <form action="{{ route('courses.periods.toggle', [$course, $period]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" style="background:none; border:none; cursor:pointer;">

                                        <i class="fas
                                            {{ $period->attendance_enabled
                                            ? 'fa-toggle-on text-success'
                                            : 'fa-toggle-off text-muted'
                                            }}"
                                            style="font-size:22px;">
                                        </i>

                                    </button>
                                </form>

                        </td>

                        <td class="text-center" style="display:flex; gap:6px; justify-content:center; align-items:center;">
                                {{-- Botón Ver Asistencia
                                <a href="{{ route('courses.periods.attendance', [$course, $period]) }}"
                                style="background: #667eea; color: white; border: none; padding: 8px 14px; border-radius: 5px; text-decoration: none; font-size: 0.9em; display: inline-flex; align-items: center; gap: 5px;">
                                📊 Asistencia
                                </a> --}}
                                {{-- Boton editar --}}
                                <button onclick="toggleEditRow(event, '{{ $period->id }}')"
                                        class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                {{-- Botón Asignar Usuarios anterior --}}
                                <a onclick="openUsersModal({{ $period->id }}, '{{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}')"
                                    class="btn btn-sm btn-success"
                                    style="cursos:pointer;">
                                <i class="fa-solid fa-user-plus"></i>
                                </a>
                                {{-- Boton de participantes
                                <a href="{{ route('sessions.groups', $period->id) }}"
                                    class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-user-plus"></i>
                                </a> --}}
                                {{-- Botón Eliminar --}}
                                <form action="{{ route('courses.periods.destroy', [$course, $period]) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este período? Los usuarios asignados perderán acceso.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-delete-session">
                                        <i class="fa-solid fa-delete-left"></i>
                                    </button>
                                </form>
                        </td>
                    </tr>

                    <tr id="edit-row-{{ $period->id }}" style="display:none;" class="edit-row-active">
                        <td colspan="4">

                            <form action="{{ route('courses.periods.update', [$course, $period]) }}" method="POST" style="display:flex; gap:10px;">
                                @csrf
                                @method('PUT')

                                <input type="date" name="start_date" value="{{ $period->start_date->format('Y-m-d') }}" class="form-control-custom">

                                <input type="date" name="end_date" value="{{ $period->end_date->format('Y-m-d') }}" class="form-control-custom">

                                <button class="btn btn-primary btn-sm">
                                    Ok
                                </button>

                            </form>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="4" style="padding: 40px; text-align: center; color: #999;">
                            <div style="font-size: 3em; margin-bottom: 10px;">📅</div>
                            <div style="font-size: 1.2em;">No hay períodos creados. Crea el primero arriba.</div>
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
</table>

            </div>
        </div>
    </div>
</div>


{{-- Modal Asignar Usuarios --}}
<div id="usersModal" style="display: none; position: fixed; top: 0; right: 0; width: 450px; height: 100vh; background: white; box-shadow: -2px 0 15px rgba(0,0,0,0.3); z-index: 9999; overflow-y: auto;">
    <div style="padding: 25px;">
        {{-- Header del modal --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 15px;">
            <div>
                <h3 style="margin: 0; color: #333;">👥 Asignar Usuarios</h3>
                <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #666;" id="periodRange"></p>
            </div>
            <button onclick="closeUsersModal()" style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: #999;">✕</button>
        </div>

        {{-- Buscador --}}
        <div style="margin-bottom: 20px;">
            <input
                type="text"
                id="searchUsers"
                placeholder="🔍 Buscar usuario..."
                onkeyup="filterUsers()"
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
        </div>

        {{-- Loading --}}
        <div id="usersLoading" style="text-align: center; padding: 40px; color: #999;">
            <div style="font-size: 2em; margin-bottom: 10px;">⏳</div>
            <div>Cargando usuarios...</div>
        </div>

        {{-- Lista de usuarios --}}
        <div id="usersList" style="display: none;">
            <!-- Se llena dinámicamente con JavaScript -->
        </div>
    </div>
</div>

{{-- Overlay para cerrar modal --}}
<div id="modalOverlay" onclick="closeUsersModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background: rgba(0,0,0,0.5); z-index: 9998;"></div>

<script>
let currentPeriodId = null;
let allUsers = [];

window.toggleEditRow = function (e, id) {
    e.preventDefault ();

    const row = document.getElementById('edit-row-' + id);

    if (!row) return;

    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
};

function openUsersModal(periodId, periodRange) {
    currentPeriodId = periodId;
    document.getElementById('periodRange').textContent = periodRange;
    document.getElementById('usersModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('usersLoading').style.display = 'block';
    document.getElementById('usersList').style.display = 'none';

    const url = `/cursos/{{ $course->id }}/periodos/${periodId}/usuarios`;
    console.log('🔗 Cargando usuarios desde:', url);

    fetch(url)
        .then(res => {
            console.log('📡 Respuesta:', res.status, res.statusText);
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('✅ Datos recibidos:', data);
            if (data.success === false) {
                throw new Error(data.error || 'Error desconocido');
            }
            allUsers = data.users || [];
            renderUsers();
            document.getElementById('usersLoading').style.display = 'none';
            document.getElementById('usersList').style.display = 'block';
        })
        .catch(err => {
            console.error('❌ Error cargando usuarios:', err);
            document.getElementById('usersLoading').innerHTML = `
                <div style="color: #dc3545; text-align: center; padding: 20px;">
                    <div style="font-size: 2em; margin-bottom: 10px;">⚠️</div>
                    <div><strong>Error:</strong> ${err.message}</div>
                    <div style="margin-top: 10px; font-size: 0.9em;">Revisa la consola para más detalles</div>
                </div>
            `;
        });
}

function closeUsersModal() {
    document.getElementById('usersModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
    currentPeriodId = null;
}

function renderUsers(filteredUsers = null) {
    const users = filteredUsers || allUsers;
    const container = document.getElementById('usersList');

    if (users.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">No hay anfitriones disponibles</div>';
        return;
    }

    container.innerHTML = users.map(user => `
        <div class="user-item" data-user-name="${user.nombre.toLowerCase()}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 10px; transition: all 0.2s;">
            <div style="flex: 1;">
                <div style="font-weight: 600; color: #333; margin-bottom: 3px;">${user.nombre}</div>
                <div style="font-size: 0.85em; color: #666; margin-bottom: 2px;">
                    📋 No. Anfitrión: ${user.no_anfitrion}
                </div>
                <div style="font-size: 0.8em; color: #999;">
                    ${user.departamento} • ${user.posicion}
                </div>
            </div>
            <label style="cursor: pointer; display: flex; align-items: center;">
                <input
                    type="checkbox"
                    ${user.assigned ? 'checked' : ''}
                    onchange="toggleUser(${user.id}, this.checked)"
                    style="width: 20px; height: 20px; cursor: pointer;">
            </label>
        </div>
    `).join('');
}

function filterUsers() {
    const search = document.getElementById('searchUsers').value.toLowerCase();
    const filtered = allUsers.filter(user => user.nombre.toLowerCase().includes(search));
    renderUsers(filtered);
}

function toggleUser(userId, assigned) {
    fetch(`/cursos/{{ $course->id }}/periodos/${currentPeriodId}/usuarios/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            user_id: userId,
            assigned: assigned
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const user = allUsers.find(u => u.id === userId);
            if (user) user.assigned = assigned;
            console.log(`✅ Usuario ${userId} ${assigned ? 'asignado' : 'desasignado'}`);
        }
    })
    .catch(err => {
        console.error('❌ Error:', err);
        alert('Error al actualizar usuario');
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUsersModal();
    }
});
</script>

<style>
.user-item:hover {
    background: #f8f9fa;
    border-color: #667eea !important;
}
</style>
@endsection
