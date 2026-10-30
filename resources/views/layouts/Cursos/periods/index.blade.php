@extends('layouts.app')

@section('title', 'Períodos - ' . $course->title)

@vite(['resources/css/Cursos/horarios.css'])

@section('content')
<div class="container-fluid horarios-view">
    
    {{-- ENCABEZADO --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">
                Períodos: {{ $course->title }}
            </h1>
            <p style="color: #64748b;">
                Gestión de períodos y acceso de usuarios
            </p>
        </div>
        <a href="{{ route('Cursos.index') }}" class="btn-salir">
            <i class="fas fa-arrow-left"></i>
            Salir
        </a>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div style="padding: 15px; background-color: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #22c55e;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
    <div style="padding: 15px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 15px;">
        <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
    </div>
    @endif

    {{-- LAYOUT PRINCIPAL (Dos columnas) --}}
    <div class="horarios-layout">

        {{-- ================= IZQUIERDA: FORMULARIO ================= --}}
        <div class="horarios-form-section">
            <div class="card-custom">
                <div class="header-accent-blue">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Nuevo Período</span>
                </div>

                <div style="padding:20px;">
                    <form action="{{ route('courses.periods.store', $course->id) }}" method="POST">
                        @csrf
                        <div class="form-group-custom" style="margin-bottom: 15px;">
                            <label class="label-custom">Fecha Inicio</label>
                            <input type="date" name="start_date" min="{{ now()->format('Y-m-d') }}" class="form-control-custom" required>
                        </div>
                        
                        <div class="form-group-custom" style="margin-bottom: 20px;">
                            <label class="label-custom">Fecha Fin</label>
                            <input type="date" name="end_date" min="{{ now()->format('Y-m-d') }}" class="form-control-custom" required>
                        </div>

                        <button type="submit" class="btn-primary-custom" style="width: 100%;">
                            <i class="fas fa-save"></i>
                            Crear Período
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= DERECHA: TABLA ================= --}}
        <div class="horarios-tabla-section">
            <div class="card-custom">
                <div class="card-header-custom">
                    <i class="fas fa-list-ul"></i>
                    <strong>Períodos Registrados</strong>
                </div>

                <div class="table-responsive">
                    <table class="table-custom-sessions">
                        <thead>
                            <tr>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($periods) > 0)
                                @foreach($periods as $period)
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
                                    <tr>
                                        <td>{{ $period->start_date->format('d/m/Y') }}</td>
                                        <td>{{ $period->end_date->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <span style="background: {{ $color }}20; color: {{ $color }}; padding: 4px 10px; border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                {{ $icon }} {{ $estado }}
                                            </span>
                                        </td>
                                        <td class="text-center" style="display:flex; gap:6px; justify-content:center;">
                                            
                                            {{-- Botón Editar --}}
                                            <button onclick="toggleEditRow(event, '{{ $period->id }}')" class="btn btn-sm btn-outline-primary" title="Editar Período">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Botón Ver Asistencia --}}
                                            <a href="{{ route('courses.periods.attendance', [$course->id, $period->id]) }}" 
                                               class="btn btn-sm btn-outline-secondary" title="Asistencia">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                            
                                            {{-- Botón Asignar Usuarios (Le inyectamos la variable $estado) --}}
                                            <button 
                                                onclick="openUsersModal({{ $period->id }}, '{{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}', '{{ $estado }}')"
                                                class="btn btn-sm btn-success" title="Asignar Usuarios">
                                                <i class="fa-solid fa-user-plus"></i>
                                            </button>
                                            
                                            {{-- Botón Eliminar modificado para usar el nuevo Modal --}}
                                            <form id="delete-period-form-{{ $period->id }}" action="{{ route('courses.periods.destroy', [$course->id, $period->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn-delete-session" title="Eliminar"
                                                    onclick="confirmarEliminarPeriodo(
                                                    '{{ $period->id }}',
                                                    '{{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}'
                                                    )">
                                                    <i class="fa-solid fa-delete-left"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- FILA OCULTA PARA EDITAR --}}
                                    <tr id="edit-row-{{ $period->id }}" style="display:none;" class="edit-row-active">
                                        <td colspan="4">
                                            <form action="{{ route('courses.periods.update', [$course->id, $period->id]) }}" method="POST" style="display:flex; gap:10px; align-items:center;">
                                                @csrf
                                                @method('PUT')
                                                <input type="date" name="start_date" class="form-control-custom" value="{{ $period->start_date->format('Y-m-d') }}" required>
                                                <input type="date" name="end_date" class="form-control-custom" value="{{ $period->end_date->format('Y-m-d') }}" required>
                                                <button type="submit" class="btn btn-primary btn-sm">OK</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" style="padding: 40px; text-align: center; color: #999;">
                                        <div style="font-size: 2.5em; margin-bottom: 10px;">📅</div>
                                        <div style="font-size: 1.1em;">No hay períodos creados aún.</div>
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

{{-- MODAL: ADVERTENCIA AL ELIMINAR PERIODO (Sin dependencias externas) --}}
<div id="deletePeriodModal" class="umi-confirm-overlay" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="umi-confirm-box">
        <div class="umi-confirm-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h5>¿Eliminar este período?</h5>
        <p class="umi-confirm-info"><strong id="deletePeriodInfo"></strong></p>
        <p class="umi-confirm-text">Los usuarios asignados perderán acceso. Esta acción no se puede deshacer.</p>
        <div class="umi-confirm-actions">
            <button type="button" id="btnCancelDeletePeriod" class="umi-confirm-cancel">Cancelar</button>
            <button type="button" id="btnConfirmDeletePeriod" class="umi-confirm-danger">Sí, eliminar</button>
        </div>
    </div>
</div>

<style>
    .umi-confirm-overlay { position: fixed; inset: 0; z-index: 2000; display: none; align-items: center; justify-content: center; background: rgba(13, 34, 64, .45); padding: 16px; }
    .umi-confirm-overlay.is-open { display: flex; }
    .umi-confirm-box { width: 100%; max-width: 420px; background: #fff; border-radius: 12px; padding: 28px; text-align: center; box-shadow: 0 12px 35px rgba(13, 34, 64, .25); }
    .umi-confirm-icon { font-size: 42px; color: #E74C3C; line-height: 1; margin-bottom: 12px; }
    .umi-confirm-box h5 { font-weight: 700; color: #0d2240; margin: 0 0 10px; font-size: 1.2rem; }
    .umi-confirm-info { margin: 0 0 6px; color: #444; }
    .umi-confirm-text { margin: 0; color: #666; font-size: .92rem; }
    .umi-confirm-actions { display: flex; justify-content: center; gap: 10px; margin-top: 22px; }
    .umi-confirm-actions button { min-width: 120px; padding: 9px 16px; border-radius: 7px; font-weight: 600; cursor: pointer; }
    .umi-confirm-cancel { background: #fff; color: #0d2240; border: 1px solid #0d2240; }
    .umi-confirm-danger { background: #E74C3C; color: #fff; border: 1px solid #E74C3C; }
    .umi-confirm-danger:disabled { opacity: .7; cursor: wait; }
</style>

<script>
    (function () {
        const overlay = document.getElementById('deletePeriodModal');
        const info = document.getElementById('deletePeriodInfo');
        const btnConfirm = document.getElementById('btnConfirmDeletePeriod');
        const btnCancel = document.getElementById('btnCancelDeletePeriod');
        let periodoPorEliminar = null;

        function cerrar() {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            periodoPorEliminar = null;
        }

        window.confirmarEliminarPeriodo = function (periodId, fechas) {
            periodoPorEliminar = periodId;
            info.textContent = fechas;
            btnConfirm.disabled = false;
            btnConfirm.textContent = 'Sí, eliminar';
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            btnCancel.focus();
        };

        btnCancel.addEventListener('click', cerrar);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) cerrar(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) cerrar();
        });

        btnConfirm.addEventListener('click', function () {
            const form = periodoPorEliminar && document.getElementById('delete-period-form-' + periodoPorEliminar);
            if (!form) return;
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Eliminando...';
            form.submit();
        });
    })();
</script>

{{-- Modal Asignar Usuarios --}}
<div id="usersModal" style="display: none; position: fixed; top: 0; right: 0; width: 450px; height: 100vh; background: white; box-shadow: -2px 0 15px rgba(0,0,0,0.3); z-index: 9999; overflow-y: auto;">
    <div style="padding: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 15px;">
            <div>
                <h3 style="margin: 0; color: #333;">👥 Asignar Usuarios</h3>
                <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #666;" id="periodRange"></p>
            </div>
            <button onclick="closeUsersModal()" style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: #999;">✕</button>
        </div>
        <div style="margin-bottom: 20px;">
            <input type="text" id="searchUsers" placeholder="🔍 Buscar usuario..." onkeyup="filterUsers()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
        </div>
        <div id="usersLoading" style="text-align: center; padding: 40px; color: #999;">
            <div style="font-size: 2em; margin-bottom: 10px;">⏳</div>
            <div>Cargando usuarios...</div>
        </div>
        <div id="usersList" style="display: none;"></div>
    </div>
</div>

<div id="modalOverlay" onclick="closeUsersModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background: rgba(0,0,0,0.5); z-index: 9998;"></div>

<script>
// Lógica de Edición Rápida (Toggle)
function toggleEditRow(e, id) {
    e.preventDefault();
    const row = document.getElementById('edit-row-' + id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}

let currentPeriodId = null;
let currentPeriodStatus = '';
let allUsers = [];

function openUsersModal(periodId, periodRange, status) {
    currentPeriodId = periodId;
    currentPeriodStatus = status; // Almacenamos el estado (Activo, Finalizado, No iniciado)
    
    document.getElementById('periodRange').textContent = periodRange;
    
    // Inyectamos el texto de advertencia rojo si el período no está activo
    if(status !== 'Activo') {
        document.getElementById('periodRange').innerHTML += `<br><span style="color:#dc3545; font-weight:bold; font-size: 0.9em;">⚠️ Asistencia bloqueada (Período ${status})</span>`;
    }

    document.getElementById('usersModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('usersLoading').style.display = 'block';
    document.getElementById('usersList').style.display = 'none';
    
    const url = `/cursos/{{ $course->id }}/periodos/${periodId}/usuarios`;
    
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
                    <div><strong>Error:</strong> ${err.message}</div>
                </div>
            `;
        });
}

function closeUsersModal() {
    document.getElementById('usersModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
    currentPeriodId = null;
    currentPeriodStatus = '';
}

function renderUsers(filteredUsers = null) {
    const users = filteredUsers || allUsers;
    const container = document.getElementById('usersList');
    
    if (users.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">No hay anfitriones disponibles</div>';
        return;
    }
    
    // Lógica para bloquear visualmente y funcionalmente si el período no está Activo
    const isLocked = currentPeriodStatus !== 'Activo';
    const disableAttr = isLocked ? 'disabled' : '';
    const opacityStyle = isLocked ? 'opacity: 0.55; filter: grayscale(100%);' : '';
    const cursorStyle = isLocked ? 'not-allowed' : 'pointer';
    
    container.innerHTML = users.map(user => `
        <div class="user-item" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 10px; ${opacityStyle}">
            <div style="flex: 1;">
                <div style="font-weight: 600; color: #333; margin-bottom: 3px;">${user.nombre}</div>
                <div style="font-size: 0.85em; color: #666; margin-bottom: 2px;">📋 No. Anfitrión: ${user.no_anfitrion}</div>
                <div style="font-size: 0.8em; color: #999;">${user.departamento} | ${user.posicion}</div>
            </div>
            <label style="cursor: ${cursorStyle}; display: flex; align-items: center;">
                <input type="checkbox" ${user.assigned ? 'checked' : ''} ${disableAttr} onchange="toggleUser(${user.id}, this.checked)" style="width: 20px; height: 20px; cursor: ${cursorStyle};">
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
        body: JSON.stringify({ user_id: userId, assigned: assigned })
    }).then(res => res.json()).catch(err => alert('Error al actualizar usuario'));
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeUsersModal();
});
</script>

<style>
.user-item {
    transition: all 0.2s ease-in-out;
}
.user-item:hover {
    background: #f8f9fa;
    border-color: #667eea !important;
}
</style>
@endsection
```eof