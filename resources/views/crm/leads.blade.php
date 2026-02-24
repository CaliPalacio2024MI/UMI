@extends('layouts.app')

@section('title', 'CRM - Leads')

@push('css')
    @vite('resources/css/CRM/leads.css')
@endpush

@section('content')

<div class="crm-leads">

    <!-- ENCABEZADO -->
    <div class="header-section">
        <div class="header-top">
            <h1 class="fw-bold mb-0">LEADS</h1>
        </div>
        
        <div class="search-wrapper">
            <img 
                src="{{ asset('images/icons/search.svg') }}"
                class="search-icon"
                alt="Buscar"
            >
            <input type="text" class="form-control buscador" placeholder="Buscar">
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL EN DOS COLUMNAS -->
    <div class="contenido-principal">

        <!-- COLUMNA IZQUIERDA: LISTA DE LEADS -->
        <div class="card-container">

            <!-- HEADER AZUL -->
            <div class="leads-header">
                <div class="encabezado-fila">
                    <span>Nombre</span>
                    <span>Apellido Paterno</span>
                    <span>Apellido Materno</span>
                    <span>Teléfono 1</span>
                    <span>Teléfono 2</span>
                    <span>Acciones</span>
                </div>
            </div>

            <!-- CUERPO -->
            <div class="leads-body">
                <div class="cuerpo-tabla">
                    @forelse($leads ?? [] as $lead)
                        <div class="fila-lead"
                            data-id="{{ $lead->id }}"
                            data-tiene-ctp="{{ $lead->ctp_id ? '1' : '0' }}"
                            data-ctp="{{ $lead->ctp_id }}"
                            data-clasificacion="{{ $lead->clasificacion }}"
                            data-tutor-nombre="{{ $lead->tutor_nombre }}"
                            data-tutor-paterno="{{ $lead->tutor_paterno }}"
                            data-tutor-materno="{{ $lead->tutor_materno }}"
                            data-telefono1="{{ $lead->telefono1 }}"
                            data-telefono2="{{ $lead->telefono2 }}"
                            data-alumno-nombre="{{ $lead->alumno_nombre }}"
                            data-alumno-paterno="{{ $lead->alumno_paterno }}"
                            data-alumno-materno="{{ $lead->alumno_materno }}"
                            data-seguimientos='@json($lead->seguimientos)'
                            data-tutor-curp="{{ $lead->tutor_curp }}"
                            data-tutor-email="{{ $lead->tutor_email }}"
                            data-alumno-curp="{{ $lead->alumno_curp }}"
                        >
                            <div>{{ $lead->alumno_nombre ?? 'N/A' }}</div>
                            <div>{{ $lead->alumno_paterno ?? 'N/A' }}</div>
                            <div>{{ $lead->alumno_materno ?? 'N/A' }}</div>
                            <div>{{ $lead->telefono1 ?? 'N/A' }}</div>
                            <div>{{ $lead->telefono2 ?? 'N/A' }}</div>

                            <div class="acciones">
                                @if(in_array(session('active_role_name'), ['master', 'coordinador_ctp']))
                                    <button 
                                        class="btn btn-icon btn-asignar-ctp"
                                        data-lead="{{ $lead->id }}"
                                        title="Asignar CTP"
                                    >
                                        <img src="{{ asset('images/icons/usuario_tag.svg') }}" class="icon">
                                    </button>
                                @endif

                                <button class="btn btn-icon btn-flecha">
                                    <img src="{{ asset('images/icons/flecha.svg') }}" class="icon">
                                </button>

                                @if(in_array(session('active_role_name'), ['master', 'coordinador_ctp']))
                                    <button class="btn btn-icon btn-eliminar" data-id="{{ $lead->id }}">
                                        <img src="{{ asset('images/icons/delete.svg') }}" class="icon">
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                    @if(session('active_role_name') === 'ctp')
        <p class="text-center text-muted py-5">No tienes leads asignados</p>
    @else
        <p class="text-center text-muted py-5">No hay leads registrados</p>
    @endif
                    @endforelse
                </div>
            </div>

        </div>

        <!-- COLUMNA DERECHA -->
        <div class="card-container" id="right-column">

            <!-- SEGUIMIENTO -->
            <div class="leads-header">
                <span class="titulo-lateral">SEGUIMIENTO</span>
            </div>

            <div class="rfc-panel" id="seguimiento-panel">
                <div class="seguimiento-header">
                    <span>Estado</span>
                    <span>Fecha</span>
                    <span>Hora</span>
                    <span>Acciones</span>
                </div>
                <div class="seguimiento-body">
                    <!-- JS inyecta filas -->
                </div>
            </div>

            <!-- DATOS GENERALES -->
            <div class="leads-header mt-3">
                <span class="titulo-lateral">DATOS GENERALES</span>
            </div>

            <div class="rfc-panel" id="datos-panel">
                <!-- JS inyecta datos -->
            </div>

        </div>

    </div>

</div>

<!-- MODAL CTP -->
<div id="modal-ctp" class="modal-ctp hidden">
    <div class="modal-content">
        <h5>Asignar CTP</h5>
        <select id="ctp-select" class="form-control">
            <option value="">Selecciona un CTP</option>
            @foreach($ctps as $ctp)
                <option value="{{ $ctp->id }}">{{ $ctp->name }}</option>
            @endforeach
        </select>
        <div class="modal-actions">
            <button id="guardar-ctp" class="btn btn-primary">Asignar</button>
            <button id="cerrar-ctp" class="btn btn-secondary">Cancelar</button>
        </div>
    </div>
</div>


{{-- ─────────────────────────────────────────────
     VARIABLES GLOBALES
────────────────────────────────────────────── --}}
<script>
    window.ROLE_ACTIVO  = "{{ session('active_role_name') }}";
    window.CSRF_TOKEN   = "{{ csrf_token() }}";
</script>


{{-- ─────────────────────────────────────────────
     ELIMINAR LEAD
────────────────────────────────────────────── --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', function () {
            const leadId = this.dataset.id;
            if (!confirm('¿Eliminar este lead?')) return;

            fetch(`/crm/leads/${leadId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) this.closest('.fila-lead').remove();
            })
            .catch(() => alert('Error al eliminar el lead'));
        });
    });

});
</script>


{{-- ─────────────────────────────────────────────
     BUSCADOR
────────────────────────────────────────────── --}}
<script>
(function () {
    const buscador = document.querySelector('.buscador');
    if (!buscador) return;

    buscador.addEventListener('input', function () {
        const texto = this.value.toLowerCase().trim();
        document.querySelectorAll('.fila-lead').forEach(fila => {
            fila.style.display = fila.innerText.toLowerCase().includes(texto) ? '' : 'none';
        });
    });
})();
</script>


{{-- ─────────────────────────────────────────────
     ESTADOS Y RENDERIZADO DEL PANEL
────────────────────────────────────────────── --}}
<script>
const ESTADOS = [
    'Prospecto',
    'Prospecto frío',
    'Prospecto caliente',
    'Aspirante'
];

// Rol puede editar seguimiento
function puedeEditarSeguimiento() {
    return ['ctp', 'master'].includes(window.ROLE_ACTIVO);
}

// Renderiza el panel de seguimiento para la fila activa
function renderizarSeguimiento(fila) {
    const seguimientoBody = document.querySelector('.seguimiento-body');
    seguimientoBody.innerHTML = '';

    const seguimientos = JSON.parse(fila.dataset.seguimientos || '[]');
    const tieneCTP     = fila.dataset.tieneCtp === '1';

    // ✅ FIX: tomar el estado registrado de MAYOR índice (no el último del array)
    let estadoActualIndex = -1;
    seguimientos.forEach(s => {
        const i = ESTADOS.indexOf(s.estado);
        if (i > estadoActualIndex) estadoActualIndex = i;
    });
    const estadoActual = estadoActualIndex >= 0 ? ESTADOS[estadoActualIndex] : null;

    ESTADOS.forEach((estado, index) => {

        const registro = seguimientos.find(s => s.estado === estado);

        // Lógica de habilitación:
        // - Prospecto: habilitado si no hay ningún estado aún
        // - Los siguientes: habilitados solo si el anterior ya está registrado
        let habilitado = false;
        if (tieneCTP) {
            if (index === 0 && !estadoActual)                        habilitado = true;
            if (index > 0 && ESTADOS[index - 1] === estadoActual)   habilitado = true;
        }

        // Icono según estado:
        // - Ya registrado   → check estático (sin click)
        // - Habilitado      → check clickeable (solo si puede editar)
        // - Bloqueado        → círculo opaco
        let accion;
        if (registro) {
            accion = `<img src="/images/icons/check.svg" class="icon-check activo" title="Completado">`;
        } else if (habilitado && puedeEditarSeguimiento()) {
            accion = `<img src="/images/icons/check.svg" class="icon-check clickeable" data-estado="${estado}" title="Marcar como ${estado}">`;
        } else {
            accion = `<span class="icon-disabled">○</span>`;
        }

        seguimientoBody.innerHTML += `
            <div class="seguimiento-row ${registro ? 'registrado' : habilitado ? 'habilitado' : 'muted'}">
            <span>${estado}</span>
                <span>${registro?.fecha ?? '---'}</span>
                <span>${registro?.hora ?? '---'}</span>
                <span class="accion">${accion}</span>
            </div>
        `;
    });
}

// Renderiza datos generales
function renderizarDatos(fila) {
    const d = fila.dataset;

    document.getElementById('datos-panel').innerHTML = `
        <div class="datos-card">

            <h6 class="titulo-seccion">Datos del Tutor</h6>

            <div class="datos-grid-3">
                <div class="dato-item">
                    <label>Nombre:</label>
                    <p>${d.tutorNombre || '---'}</p>
                </div>
                <div class="dato-item">
                    <label>Apellido Paterno:</label>
                    <p>${d.tutorPaterno || '---'}</p>
                </div>
                <div class="dato-item">
                    <label>Apellido Materno:</label>
                    <p>${d.tutorMaterno || '---'}</p>
                </div>
            </div>

            <div class="datos-grid-4 mt-3">
                <div class="dato-item">
                    <label>CURP:</label>
                    <p>${d.tutorCurp || '---'}</p>
                </div>
                <div class="dato-item">
                    <label>Teléfono 1:</label>
                    <p>${d.telefono1 || '---'}</p>
                </div>
                <div class="dato-item">
                    <label>Teléfono 2:</label>
                    <p>${d.telefono2 || '---'}</p>
                </div>
                <div class="dato-item">
                    <label>Correo electrónico:</label>
                    <p>${d.tutorEmail || '---'}</p>
                </div>
            </div>

            <hr class="separador-datos">

            <h6 class="titulo-seccion">Datos del Aspirante a Alumno</h6>

            <div class="datos-grid-3">
                <div class="dato-item">
                    <label>Nombre:</label>
                    <p>${d.alumnoNombre || '---'}</p>
                </div>
                <div class="dato-item">
                    <label>Apellido Paterno:</label>
                    <p>${d.alumnoPaterno || '---'}</p>
                </div>
                <div class="dato-item">
                    <label>Apellido Materno:</label>
                    <p>${d.alumnoMaterno || '---'}</p>
                </div>
            </div>

            <div class="datos-curp-centrado mt-3">
                <label>CURP:</label>
                <p>${d.alumnoCurp || '---'}</p>
            </div>

        </div>
    `;
}

// Click en flecha → seleccionar lead y mostrar panel
document.querySelectorAll('.fila-lead').forEach(fila => {
    const btnFlecha = fila.querySelector('.btn-flecha');
    if (!btnFlecha) return;

    btnFlecha.addEventListener('click', () => {
        document.querySelectorAll('.fila-lead').forEach(f => f.classList.remove('activo'));
        fila.classList.add('activo');
        renderizarSeguimiento(fila);
        renderizarDatos(fila);
    });
});
</script>


{{-- ─────────────────────────────────────────────
     CLICK EN CHECK → GUARDAR SEGUIMIENTO
────────────────────────────────────────────── --}}
<script>
document.addEventListener('click', function (e) {

    const check = e.target.closest('.icon-check.clickeable');
    if (!check) return;

    const nuevoEstado = check.dataset.estado;
    const filaActiva  = document.querySelector('.fila-lead.activo');
    const leadId      = filaActiva?.dataset.id;
    if (!leadId || !nuevoEstado) return;

    check.classList.remove('clickeable');
    check.style.opacity = '0.5';

    fetch(`/crm/leads/${leadId}/seguimiento`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            filaActiva.dataset.seguimientos = JSON.stringify(data.seguimientos);
            renderizarSeguimiento(filaActiva);
        } else {
            alert('Error al guardar el seguimiento');
            check.classList.add('clickeable');
            check.style.opacity = '';
        }
    })
    .catch(() => {
        alert('Error de conexión');
        check.classList.add('clickeable');
        check.style.opacity = '';
    });

});
</script>


{{-- ─────────────────────────────────────────────
     ASIGNAR CTP
────────────────────────────────────────────── --}}
<script>
let LEAD_SELECCIONADO = null;

document.querySelectorAll('.btn-asignar-ctp').forEach(btn => {
    btn.addEventListener('click', () => {
        LEAD_SELECCIONADO = btn.dataset.lead;
        document.getElementById('modal-ctp').classList.remove('hidden');
    });
});

document.getElementById('cerrar-ctp').addEventListener('click', () => {
    document.getElementById('modal-ctp').classList.add('hidden');
});

document.getElementById('guardar-ctp').addEventListener('click', () => {
    const ctpId = document.getElementById('ctp-select').value;

    if (!ctpId || !LEAD_SELECCIONADO) {
        alert('Selecciona un CTP');
        return;
    }

    fetch(`/crm/leads/${LEAD_SELECCIONADO}/asignar-ctp`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ctp_id: ctpId })
    })
    .then(() => location.reload());
});
</script>

@endsection