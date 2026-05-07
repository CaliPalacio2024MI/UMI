@extends('layouts.app')

@section('title', 'Períodos - ' . $course->title)

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 30px;">
    
    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 30px; color: white; margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0 0 10px 0; font-size: 2em;">📅 Gestión de Vigencia</h1>
                <p style="margin: 0; opacity: 0.9;">{{ $course->title }}</p>
            </div>
            <a href="{{ route('Cursos.index') }}" style="background: rgba(255,255,255,0.2); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                ← Volver a Cursos
            </a>
        </div>
    </div>

    {{-- Formulario crear período --}}
    <div style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0;">➕ Crear Nueva Vigencia</h3>
        <form action="{{ route('courses.periods.store', $course) }}" method="POST" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
            @csrf
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Fecha Inicio</label>
                <input type="date" name="start_date" min="{{ now()->format('Y-m-d') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Fecha Fin</label>
                <input type="date" name="end_date" min="{{ now()->format('Y-m-d') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                ✓ Crear vigencia
            </button>
        </form>
    </div>

    {{-- Lista de períodos --}}
    <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333;">Fecha Inicio</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333;">Fecha Fin</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333;">Estado</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333;">Acciones</th>
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
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 15px; text-align: center; font-weight: 500;">{{ $period->start_date->format('d/m/Y') }}</td>
                    <td style="padding: 15px; text-align: center; font-weight: 500;">{{ $period->end_date->format('d/m/Y') }}</td>
                    <td style="padding: 15px; text-align: center;">
                        <span style="background: {{ $color }}20; color: {{ $color }}; padding: 6px 14px; border-radius: 5px; font-size: 0.9em; font-weight: 600;">
                            {{ $icon }} {{ $estado }}
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            {{-- Botón Ver Asistencia --}}
                            <a href="{{ route('courses.periods.attendance', [$course, $period]) }}" 
                               style="background: #667eea; color: white; border: none; padding: 8px 14px; border-radius: 5px; text-decoration: none; font-size: 0.9em; display: inline-flex; align-items: center; gap: 5px;">
                                📊 Asistencia
                            </a>
                            
                            {{-- Botón Asignar Usuarios --}}
                            <button 
                                onclick="openUsersModal({{ $period->id }}, '{{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}')"
                                style="background: #28a745; color: white; border: none; padding: 8px 14px; border-radius: 5px; cursor: pointer; font-size: 0.9em; display: inline-flex; align-items: center; gap: 5px;">
                                👥 Usuarios
                            </button>
                            
                            {{-- Botón Eliminar --}}
                            <form action="{{ route('courses.periods.destroy', [$course, $period]) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este período? Los usuarios asignados perderán acceso.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #dc3545; color: white; border: none; padding: 8px 14px; border-radius: 5px; cursor: pointer; font-size: 0.9em; display: inline-flex; align-items: center; gap: 5px;">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </div>
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