@extends('layouts.app')

@section('title', 'Horarios - ' . $course->title)

@vite(['resources/css/Cursos/horarios.css'])

@section('content')

<div class="container-fluid horarios-view">
    {{-- ENCABEZADO --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
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

    <div class="horarios-layout">

        {{-- FORMULARIO --}}
        <div class="horarios-form-section">
            <div class="card-custom">
                <div class="header-accent-blue">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nuevo Horario</span>
                </div>

                <div style="padding:20px;">
                    <form action="{{ route('courses.sessions.store', $course->id) }}" method="POST">
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
                                <input type="time" name="end_time" id="end_time" class="form-control-custom" required>
                            </div>
                        </div>

                        <div class="form-group-custom">
                            <label class="label-custom">Instructor</label>
                            <input type="text" name="instructor_name" class="form-control-custom" placeholder="Nombre del instructor" required>
                        </div>

                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-save"></i>
                            Registrar Horario
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- TABLA --}}
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
                        @if(count($sessions) > 0)
                            @foreach($sessions as $session)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                                    <td>{{ $session->instructor_name }}</td>

                                    <td class="text-center">
                                        <form action="{{ route('courses.sessions.toggle', [$course->id,$session->id]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" style="background:none; border:none; cursor:pointer;">
                                                @if($session->attendance_enabled)
                                                    <i class="fas fa-toggle-on text-success" style="font-size:22px;"></i>
                                                @else
                                                    <i class="fas fa-toggle-off text-muted" style="font-size:22px;"></i>
                                                @endif
                                            </button>
                                        </form>
                                    </td>

                                    <td class="text-center" style="display:flex; gap:6px; justify-content:center;">
                                        <button onclick="toggleEditRow(event, '{{ $session->id }}')" class="btn btn-sm btn-outline-primary" title="Editar Sesión">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button type="button" onclick="openUsersModal('{{ $session->id }}', '{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}')" class="btn btn-sm btn-success" title="Asignar Participantes">
                                            <i class="fa-solid fa-user-plus"></i>
                                        </button>

                                        <form action="{{ route('courses.sessions.destroy', [$course->id,$session->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete-session" title="Eliminar Sesión">
                                                <i class="fa-solid fa-delete-left"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- FILA OCULTA PARA EDITAR --}}
                                <tr id="edit-row-{{ $session->id }}" style="display:none;" class="edit-row-active">
                                    <td colspan="6">
                                        <form action="{{ route('courses.sessions.update', [$course->id,$session->id]) }}" method="POST" style="display:flex; gap:10px;">
                                            @csrf
                                            @method('PUT')
                                            <input type="date" name="date" class="form-control-custom" value="{{ $session->date }}">
                                            
                                            <input type="time" name="start_time" class="form-control-custom" value="{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}" onchange="autoCalcEditTime(this, {{ $course->hours ?? 0 }})">
                                            
                                            <input type="time" name="end_time" class="form-control-custom" value="{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}">
                                            
                                            <input type="text" name="instructor_name" class="form-control-custom" value="{{ $session->instructor_name }}" placeholder="Instructor">
                                            <button type="submit" class="btn btn-primary btn-sm">OK</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @else
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
    </div>
</div>

{{-- MODAL DE USUARIOS --}}
<div id="usersModal" style="display: none; position: fixed; top: 0; right: 0; width: 450px; height: 100vh; background: white; box-shadow: -2px 0 15px rgba(0,0,0,0.3); z-index: 9999; overflow-y: auto;">
    <div style="padding: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 15px;">
            <div>
                <h3 style="margin: 0; color: #333;">👥 Asignar Asistentes</h3>
                <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #666;" id="sessionDate"></p>
            </div>
            <button onclick="closeUsersModal()" type="button" style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: #999;">✕</button>
        </div>
        
        <div style="margin-bottom: 20px;">
            <input type="text" id="searchUsers" placeholder="🔍 Buscar participante..." onkeyup="filterUsers()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
        </div>
        
        <div id="usersLoading" style="text-align: center; padding: 40px; color: #999;">
            <div style="font-size: 2em; margin-bottom: 10px;">⏳</div>
            <div>Cargando participantes...</div>
        </div>
        
        <div id="usersList" style="display: none;"></div>
    </div>
</div>

<div id="modalOverlay" onclick="closeUsersModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background: rgba(0,0,0,0.5); z-index: 9998;"></div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    window.toggleEditRow = function (e, id) {
        e.preventDefault();
        const row = document.getElementById('edit-row-' + id);
        if (!row) return;
        row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
    };

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
            date.setHours(date.getHours() + courseHours);
            let endH = String(date.getHours()).padStart(2, '0');
            let endM = String(date.getMinutes()).padStart(2, '0');
            endInput.value = `${endH}:${endM}`;
        });
    }
});

let currentSessionId = null;
let allUsers = [];

window.openUsersModal = function(sessionId, sessionDate) {
    currentSessionId = sessionId;
    document.getElementById('sessionDate').textContent = 'Fecha: ' + sessionDate;
    document.getElementById('usersModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('usersLoading').style.display = 'block';
    document.getElementById('usersList').style.display = 'none';
    
    const url = `/cursos/{{ $course->id }}/sesiones/${sessionId}/usuarios`;
    
    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            return res.json();
        })
        .then(data => {
            if (data.success === false) throw new Error(data.error || 'Error desconocido');
            allUsers = data.users || [];
            renderUsers();
            document.getElementById('usersLoading').style.display = 'none';
            document.getElementById('usersList').style.display = 'block';
        })
        .catch(err => {
            document.getElementById('usersLoading').innerHTML = `
                <div style="color: #dc3545; text-align: center; padding: 20px;">
                    <div style="font-size: 2em; margin-bottom: 10px;">⚠️</div>
                    <div><strong>No hay ruta asignada</strong></div>
                    <div style="font-size: 0.85em; margin-top: 5px;">El servidor aún no tiene la ruta para usuarios de sesión.</div>
                </div>
            `;
        });
}

window.closeUsersModal = function() {
    document.getElementById('usersModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
    currentSessionId = null;
}

window.renderUsers = function(filteredUsers = null) {
    const users = filteredUsers || allUsers;
    const container = document.getElementById('usersList');
    
    if (users.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">No hay participantes disponibles</div>';
        return;
    }
    
    container.innerHTML = users.map(user => `
        <div class="user-item" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 10px;">
            <div style="flex: 1;">
                <div style="font-weight: 600; color: #333; margin-bottom: 3px;">${user.nombre}</div>
                <div style="font-size: 0.85em; color: #666; margin-bottom: 2px;">📋 No. Empleado: ${user.no_anfitrion || '-'}</div>
            </div>
            <label style="cursor: pointer; display: flex; align-items: center;">
                <input type="checkbox" ${user.assigned ? 'checked' : ''} onchange="toggleUser(${user.id}, this.checked)" style="width: 20px; height: 20px; cursor: pointer;">
            </label>
        </div>
    `).join('');
}

window.filterUsers = function() {
    const search = document.getElementById('searchUsers').value.toLowerCase();
    const filtered = allUsers.filter(user => user.nombre.toLowerCase().includes(search));
    renderUsers(filtered);
}

window.toggleUser = function(userId, assigned) {
    fetch(`/cursos/{{ $course->id }}/sesiones/${currentSessionId}/usuarios/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ user_id: userId, assigned: assigned })
    }).then(res => res.json()).catch(err => alert('Error al actualizar participante'));
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeUsersModal();
});

window.autoCalcEditTime = function(startInput, courseHours) {
    if (!startInput.value) return;

    const form = startInput.closest('form');
    const endInput = form.querySelector('input[name="end_time"]');

    if (endInput) {
        let [h, m] = startInput.value.split(":").map(Number);
        
        let date = new Date();
        date.setHours(h);
        date.setMinutes(m);
        
        date.setHours(date.getHours() + courseHours);

        let endH = String(date.getHours()).padStart(2, '0');
        let endM = String(date.getMinutes()).padStart(2, '0');

        endInput.value = `${endH}:${endM}`;
    }
};
</script>
@endsection