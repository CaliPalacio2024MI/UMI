@extends('layouts.app')

@section('title', 'CRM - Leads')

@section('content')

<div class="crm-leads">

    <!-- ENCABEZADO -->
    <div class="header-section">
        <div class="header-top">
            <h1 class="fw-bold mb-0">LEADS</h1>
        </div>
        
        <div class="search-filter-row">
        <div class="search-wrapper">
            <img 
                src="{{ asset('images/icons/search.svg') }}"
                class="search-icon"
                alt="Buscar"
            >
            <input type="text" class="form-control buscador" placeholder="Buscar">
        </div>
         <div class="filtros-clasificacion">
    <button class="chip-filtro activo" data-clasificacion="todos">Todos</button>
    @foreach($clasificaciones as $clasificacion)
        <button class="chip-filtro" data-clasificacion="{{ $clasificacion->name }}">
            {{ $clasificacion->name }}
        </button>
    @endforeach
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
                    <span>Carrera</span>
                    <span>Estado</span>
                    <span>Acciones</span>
                </div>
            </div>

            <!-- CUERPO -->
            <div class="leads-body">
                <div class="cuerpo-tabla">
                @php
                    $role = session('active_role_name');

                    $canAssignCTP = in_array($role, ['master', 'coordinador_ctp', 'control_administrativo']);
                    $canDeleteLead = in_array($role, ['master', 'coordinador_ctp', 'control_administrativo']);
                @endphp
                    @forelse($leads ?? [] as $lead)
                        <div class="fila-lead"
                            data-id="{{ $lead->id }}"
                            data-tiene-ctp="{{ $lead->ctp_id ? '1' : '0' }}"
                            data-ctp="{{ $lead->ctp_id }}"
                            data-clasificacion="{{ $lead->carrera->classification->name ?? '' }}"
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
                            data-comentario-reasignacion="{{ $lead->comentario_reasignacion }}"
                            data-carrera="{{ $lead->carrera->name ?? 'Sin carrera' }}"
                        >
                            <div>{{ $lead->alumno_nombre ?? 'N/A' }}</div>
                            <div>{{ $lead->alumno_paterno ?? 'N/A' }}</div>
                            <div>{{ $lead->carrera->name ?? 'Sin carrera' }}</div>
                            <div>
                            @if($lead->ctp_id)
                                {{ $lead->seguimientos->sortByDesc('id')->first()?->estado ?? 'Prospecto frío' }}
                            @else
                                Sin asignar
                            @endif
                        </div>

                            <div class="acciones">
                                <button class="btn btn-icon btn-flecha">
                                    <img src="{{ asset('images/icons/flecha.svg') }}" class="icon">
                                </button>
                                @if($canAssignCTP)
                                    <button 
                                        class="btn btn-icon btn-asignar-ctp"
                                        data-lead="{{ $lead->id }}"
                                        title="Asignar CTP"
                                    >
                                        <img src="{{ asset('images/icons/usuario_tag.svg') }}" class="icon">
                                    </button>
                                @endif

                                @if($canDeleteLead)
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
                </div>
            </div>

            <!-- DATOS GENERALES -->
            <div class="leads-header mt-3">
                <span class="titulo-lateral">DATOS GENERALES</span>
            </div>

            <div class="rfc-panel" id="datos-panel">
            </div>

        </div>

    </div>



<!-- MODAL CTP -->
<div id="modal-ctp" class="modal-ctp d-none">
    <div class="modal-content">

        <!-- Vista: ya tiene CTP asignado -->
        <div id="vista-asignado" class="d-none">
    <p class="modal-asignado-label">Asignado a:</p>
    <p id="modal-ctp-nombre" class="modal-asignado-nombre"></p>

    @if(in_array(session('active_role_name'), ['master', 'coordinador_ctp', 'control_administrativo']))
    <div id="historial-ultimo" class="historial-ultimo-wrapper d-none">
        <span class="historial-ultimo-label">Última reasignación:</span>
        <p id="modal-comentario-actual" class="modal-comentario-actual"></p>
    </div>
@endif

    <button id="btn-reasignar" class="btn btn-outline-primary w-100 mt-2">
        Reasignar
    </button>
</div>

        <!-- Vista: seleccionar CTP (nueva asignación o reasignación) -->
        <div id="vista-seleccionar" class="d-none">
            <h5 id="modal-titulo">Asignar CTP</h5>
            <select id="ctp-select" class="form-control mt-2">
                <option value="">Selecciona un CTP</option>
                @foreach($ctps as $ctp)
                <option value="{{ $ctp->id }}" data-nombre="{{ $ctp->nombre }} {{ $ctp->apellido_paterno }}">
    {{ $ctp->nombre }} {{ $ctp->apellido_paterno }}
</option>
                @endforeach
            </select>

            <!-- Comentario (solo visible al reasignar) -->
            <div id="comentario-wrapper" class="d-none mt-3">
                <label class="form-label fw-bold" style="font-size:13px;">
                    Motivo del cambio:
                </label>
                <textarea 
                    id="ctp-comentario" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Escribe brevemente el motivo del cambio..."
                    style="resize:none; font-size:13px;"
                ></textarea>
            </div>

            <div class="modal-actions mt-3">
                <button id="guardar-ctp" class="btn btn-primary">Asignar</button>
                <button id="cerrar-ctp" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>

    </div>
    </div>
</div>

<!-- MODAL COMENTARIO SEGUIMIENTO -->
<div id="modal-seguimiento" class="modal-ctp d-none">
    <div class="modal-content">
        <h5 style="color:var(--crm-primary);font-weight:700;margin-bottom:12px;">
            Registrar estado
        </h5>
        <p id="modal-seg-estado-label" style="font-size:13px;color:#666;margin-bottom:10px;"></p>

        <label style="font-size:13px;font-weight:700;color:#333;display:block;margin-bottom:6px;">
            Comentario <span style="color:#999;font-weight:400;">(opcional)</span>:
        </label>
        <textarea
            id="seg-comentario"
            class="form-control"
            rows="3"
            placeholder="Escribe un comentario sobre este seguimiento..."
            style="resize:none;font-size:13px;border-radius:10px;"
        ></textarea>

        <div class="modal-actions mt-3">
            <button id="guardar-seguimiento-btn" class="btn btn-primary">Guardar</button>
            <button id="cancelar-seguimiento-btn" class="btn btn-secondary">Cancelar</button>
        </div>
    </div>
</div>

<!-- MODAL VER COMENTARIO -->
<div id="modal-ver-comentario" class="modal-ctp d-none">
    <div class="modal-content">
        <h5 style="color:var(--crm-primary);font-weight:700;margin-bottom:4px;" id="modal-ver-estado"></h5>
        <p style="font-size:12px;color:#999;margin-bottom:12px;" id="modal-ver-fecha"></p>

        <label style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#999;display:block;margin-bottom:6px;">
            Comentario:
        </label>
        <div id="modal-ver-texto" class="modal-comentario-actual" style="min-height:50px;"></div>

        <div class="text-end mt-3">
            <button id="cerrar-ver-comentario" class="btn btn-secondary btn-sm">Cerrar</button>
        </div>
    </div>
</div>
</div>


{{-- ─────────────────────────────────────────────
     VARIABLES GLOBALES
────────────────────────────────────────────── --}}

<script>
(function () {

window.ROLE_ACTIVO = "{{ session('active_role_name') }}";
window.CSRF_TOKEN  = "{{ csrf_token() }}";

const ESTADOS = ['Prospecto frío','Prospecto caliente','Aspirante','Alumno'];

// ===== ELIMINAR LEAD =====
document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', function () {
        const leadId = this.dataset.id;
        if (!confirm('¿Eliminar este lead?')) return;
        fetch(`/crm/leads/${leadId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => { if (data.success) this.closest('.fila-lead').remove(); })
        .catch(() => alert('Error al eliminar el lead'));
    });
});

// ===== BUSCADOR y FILTRO CLASIFICACIÓN =====
let filtroActivo = 'todos';

const buscador = document.querySelector('.buscador');
if (buscador) buscador.addEventListener('input', aplicarFiltros);

document.querySelectorAll('.chip-filtro').forEach(chip => {
    chip.addEventListener('click', function () {
        document.querySelectorAll('.chip-filtro').forEach(c => c.classList.remove('activo'));
        this.classList.add('activo');
        filtroActivo = this.dataset.clasificacion;
        aplicarFiltros();
    });
});

function aplicarFiltros() {
    const texto = buscador?.value.toLowerCase().trim() ?? '';
    document.querySelectorAll('.fila-lead').forEach(fila => {
        const coincideTexto  = fila.innerText.toLowerCase().includes(texto);
        const coincideClasif = filtroActivo === 'todos' ||
            (fila.dataset.clasificacion ?? '').toLowerCase() === filtroActivo.toLowerCase();
        fila.style.display = (coincideTexto && coincideClasif) ? '' : 'none';
    });
}

// ===== RENDERIZADO =====
function puedeEditarSeguimiento() {
    const filaActiva = document.querySelector('.fila-lead.activo');
    if (!filaActiva) return false;

    // Master y coordinador pueden editar cualquier lead
    if (['master', 'coordinador_ctp', 'control_administrativo'].includes(window.ROLE_ACTIVO)) return true;

    // CTP solo puede editar sus leads asignados
    if (window.ROLE_ACTIVO === 'ctp') {
        return filaActiva.dataset.ctp == "{{ auth()->id() }}";
    }

    return false;
}

function renderizarSeguimiento(fila) {
    const seguimientoBody = document.querySelector('.seguimiento-body');
    seguimientoBody.innerHTML = '';
    const seguimientos = JSON.parse(fila.dataset.seguimientos || '[]');
    const tieneCTP     = fila.dataset.tieneCtp === '1';
    let estadoActualIndex = -1;
    seguimientos.forEach(s => {
        const i = ESTADOS.indexOf(s.estado);
        if (i > estadoActualIndex) estadoActualIndex = i;
    });
    const estadoActual = estadoActualIndex >= 0 ? ESTADOS[estadoActualIndex] : null;
    ESTADOS.forEach((estado, index) => {
        const registro = seguimientos.find(s => s.estado === estado);
        let habilitado = false;
        if (tieneCTP) {
            if (index === 0 && !estadoActual)                      habilitado = true;
            if (index > 0 && ESTADOS[index - 1] === estadoActual) habilitado = true;
        }
        let accion;
        if (registro) {
            const mostrarOjo = estado !== 'Prospecto frío';
            accion = `
                <i class="bi bi-check-circle-fill icon-check activo" title="Completado"></i>
                ${mostrarOjo ? `<img src="/images/icons/eye.svg" class="icon-eye" title="Ver comentario"
                     data-estado="${estado}" data-fecha="${registro.fecha ?? ''}"
                     data-hora="${registro.hora ?? ''}"
                     data-comentario="${encodeURIComponent(registro.comentario ?? '')}">` : ''}
            `;
        } else if (habilitado && puedeEditarSeguimiento() && estado !== 'Prospecto frío' && estado !== 'Alumno') {
            accion = `<i class="bi bi-check-circle icon-check clickeable" data-estado="${estado}" title="Marcar como ${estado}"></i>`;
        } else {
            accion = `<i class="bi bi-circle icon-disabled"></i>`;
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

function renderizarDatos(fila) {
    const d = fila.dataset;
    document.getElementById('datos-panel').innerHTML = `
    <div class="datos-card">
        <h6 class="titulo-seccion">Datos del Tutor</h6>
        <div class="datos-grid-3">
            <div class="dato-item"><label>Nombre:</label><p>${d.tutorNombre || '---'}</p></div>
            <div class="dato-item"><label>Apellido Paterno:</label><p>${d.tutorPaterno || '---'}</p></div>
            <div class="dato-item"><label>Apellido Materno:</label><p>${d.tutorMaterno || '---'}</p></div>
        </div>
        <div class="datos-grid-4 mt-3">
            <div class="dato-item"><label>CURP:</label><p>${d.tutorCurp || '---'}</p></div>
            <div class="dato-item"><label>Teléfono 1:</label><p>${d.telefono1 || '---'}</p></div>
            <div class="dato-item"><label>Teléfono 2:</label><p>${d.telefono2 || '---'}</p></div>
            <div class="dato-item"><label>Correo electrónico:</label><p>${d.tutorEmail || '---'}</p></div>
        </div>
    </div>
    <div class="datos-card">
        <h6 class="titulo-seccion">Datos del Aspirante a Alumno</h6>
        <div class="datos-grid-3">
            <div class="dato-item"><label>Nombre:</label><p>${d.alumnoNombre || '---'}</p></div>
            <div class="dato-item"><label>Apellido Paterno:</label><p>${d.alumnoPaterno || '---'}</p></div>
            <div class="dato-item"><label>Apellido Materno:</label><p>${d.alumnoMaterno || '---'}</p></div>
        </div>
        <div class="datos-curp-centrado mt-3">
            <label>CURP:</label>
            <p style="font-weight:400;letter-spacing:0;">${d.alumnoCurp || '---'}</p>
        </div>
        <div class="carrera-panel mt-2">
            <label>Plan de estudios / Carrera:</label>
            <p>${d.carrera || '---'}</p>
        </div>
    </div>`;
}

window.renderizarSeguimiento = renderizarSeguimiento;

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
window._pendienteEstado = null;

const cuerpoLeads = document.querySelector('.crm-leads');
if (cuerpoLeads) {
    cuerpoLeads.addEventListener('click', function(e) {
        const check = e.target.closest('.icon-check.clickeable');
        if (check) {
            window._pendienteEstado = check.dataset.estado;
            document.getElementById('modal-seg-estado-label').textContent =
                `Estás marcando este lead como: ${window._pendienteEstado}`;
            document.getElementById('seg-comentario').value = '';
            document.getElementById('modal-seguimiento').classList.remove('d-none');
            return;
        }

        const ojo = e.target.classList.contains('icon-eye')
            ? e.target
            : e.target.closest('.icon-eye');
        if (ojo) {
            const comentario = decodeURIComponent(ojo.dataset.comentario || '');
            document.getElementById('modal-ver-estado').textContent = ojo.dataset.estado;
            document.getElementById('modal-ver-fecha').textContent  = `${ojo.dataset.fecha} ${ojo.dataset.hora}`;
            document.getElementById('modal-ver-texto').textContent  = comentario || '(Sin comentario)';
            document.getElementById('modal-ver-comentario').classList.remove('d-none');
            return;
        }
    });
}

// ===== GUARDAR SEGUIMIENTO =====
document.getElementById('guardar-seguimiento-btn')?.addEventListener('click', () => {
    const filaActiva = document.querySelector('.fila-lead.activo');
    const leadId     = filaActiva?.dataset.id;
    if (!leadId || !window._pendienteEstado) return;
    const comentario = document.getElementById('seg-comentario').value.trim();
    document.getElementById('modal-seguimiento').classList.add('d-none');
    fetch(`/crm/leads/${leadId}/seguimiento`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
        body: JSON.stringify({ estado: window._pendienteEstado, comentario })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            filaActiva.dataset.seguimientos = JSON.stringify(data.seguimientos);
            window.renderizarSeguimiento(filaActiva);
        } else {
            alert('Error al guardar el seguimiento');
        }
    })
    .catch(() => alert('Error de conexión'));
    window._pendienteEstado = null;
});

document.getElementById('cancelar-seguimiento-btn')?.addEventListener('click', () => {
    document.getElementById('modal-seguimiento').classList.add('d-none');
    window._pendienteEstado = null;
});

document.getElementById('cerrar-ver-comentario')?.addEventListener('click', () => {
    document.getElementById('modal-ver-comentario').classList.add('d-none');
});

// ===== ASIGNAR CTP =====
let LEAD_SELECCIONADO = null;
let ES_REASIGNACION   = false;

document.querySelectorAll('.btn-asignar-ctp').forEach(btn => {
    btn.addEventListener('click', () => {
        LEAD_SELECCIONADO = btn.dataset.lead;
        ES_REASIGNACION   = false;
        const fila        = document.querySelector(`.fila-lead[data-id="${LEAD_SELECCIONADO}"]`);
        const tieneCTP    = fila?.getAttribute('data-tiene-ctp') === '1';
        const ctpActualId = fila?.getAttribute('data-ctp');
        document.querySelectorAll('#ctp-select option').forEach(opt => {
    opt.style.display = '';
});

if (ctpActualId) {
    const actual = document.querySelector(`#ctp-select option[value="${ctpActualId}"]`);
    if (actual) actual.style.display = 'none';
}
        let nombreActual = '---';
        if (tieneCTP) {
            const opcion = document.querySelector(`#ctp-select option[value="${fila.dataset.ctp}"]`);
            if (opcion) nombreActual = opcion.dataset.nombre || opcion.text;
        }
        document.getElementById('ctp-select').value     = '';
        document.getElementById('ctp-comentario').value = '';
        document.getElementById('comentario-wrapper').classList.add('d-none');
        if (tieneCTP) {
            const comentario  = fila.getAttribute('data-comentario-reasignacion');
            const historialEl = document.getElementById('historial-ultimo');
            if (historialEl) {
                if (comentario) {
                    document.getElementById('modal-comentario-actual').textContent = `"${comentario}"`;
                    historialEl.classList.remove('d-none');
                } else {
                    historialEl.classList.add('d-none');
                }
            }
            document.getElementById('modal-ctp-nombre').textContent = nombreActual;
            document.getElementById('vista-asignado').classList.remove('d-none');
            document.getElementById('vista-seleccionar').classList.add('d-none');
        } else {
            document.getElementById('modal-titulo').textContent = 'Asignar CTP';
            document.getElementById('vista-asignado').classList.add('d-none');
            document.getElementById('vista-seleccionar').classList.remove('d-none');
        }
        document.getElementById('modal-ctp').classList.remove('d-none');
    });
});

document.getElementById('btn-reasignar')?.addEventListener('click', () => {
    ES_REASIGNACION = true;
    document.getElementById('modal-titulo').textContent = 'Reasignar CTP';
    document.getElementById('vista-asignado').classList.add('d-none');
    document.getElementById('vista-seleccionar').classList.remove('d-none');
    document.getElementById('comentario-wrapper').classList.remove('d-none');
});

document.getElementById('cerrar-ctp')?.addEventListener('click', () => {
    document.getElementById('modal-ctp').classList.add('d-none');
});

document.getElementById('guardar-ctp')?.addEventListener('click', () => {
    const ctpId      = document.getElementById('ctp-select').value;
    const comentario = document.getElementById('ctp-comentario').value.trim();
    if (!ctpId || !LEAD_SELECCIONADO) return;
    if (ES_REASIGNACION && !comentario) return;
    fetch(`/crm/leads/${LEAD_SELECCIONADO}/asignar-ctp`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Content-Type': 'application/json' },
        body: JSON.stringify({ ctp_id: ctpId, comentario: ES_REASIGNACION ? comentario : null })
    })
    .then(res => res.json())
    .then(() => location.reload())
    .catch(err => console.error('Error fetch:', err));
});

})();
</script>

@endsection