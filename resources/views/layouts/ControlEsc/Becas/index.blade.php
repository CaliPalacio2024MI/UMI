@extends('layouts.app')
@push('css')
    @vite('resources/css/ControlEsc/becas.css')
@endpush
@section('content')
<div class="container">
    <div id="umi-app-view">
        <div class="content-header"><div class="content-title"><h3>Becas</h3></div></div>
        @if(session('success')) <div class="becas-alert becas-alert--ok">{{ session('success') }}</div> @endif
        <div class="becas-toolbar">
            <form id="becas-search-form" method="GET" action="{{ request()->url() }}" style="flex:1;">
                <div class="becas-search-wrap">
                    <img src="{{ asset('images/icons/search.svg') }}" alt="" class="becas-search-icon" aria-hidden="true">
                    <input type="text" id="becas-search-input" name="search" value="{{ request('search') }}" class="becas-input" placeholder="Buscar Por...">
                </div>
            </form>
            <button class="becas-btn becas-btn--primary" type="button" onclick="openBecaModal()">+ Agregar</button>
        </div>
        @php
            // Agrupar docs enviados por alumno, filtrando por los requeridos de su beca
            $docsPorAlumno = ($submittedDocs ?? collect())->groupBy('user_id')->map(function($docs, $userId) use ($becaAsignaciones) {
                $asignacion = ($becaAsignaciones ?? collect())->get($userId);
                $becaReqIds = array_map('intval', $asignacion?->beca?->documentos_requeridos ?? []);
                if (empty($becaReqIds)) return $docs;
                return $docs->filter(fn($d) => in_array((int) $d->document_requirement_id, $becaReqIds))->values();
            });
            // Mapa req_id → nombre
            $reqNombres = ($expedienteConfig ?? collect())->pluck('nombre', 'id');
        @endphp

        <div class="becas-card">
            <div style="overflow-x:auto;">
                <table class="becas-table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Documentos enviados</th>
                            <th>Beca asignada</th>
                            <th>Estado beca</th>
                            <th style="width:90px;"></th>
                        </tr>
                    </thead>
                    <tbody id="becas-table-body">
                    @php $hayFilas = false; @endphp
                    @foreach($alumnos ?? [] as $alumno)
                        @php
                            $alumnoId     = $alumno->id;
                            $alumnoNombre = trim(($alumno->nombre ?? '').' '.($alumno->apellido_paterno ?? '').' '.($alumno->apellido_materno ?? ''));
                            $docsAlumno   = $docsPorAlumno->get($alumnoId, collect());
                            $asignacion   = ($becaAsignaciones ?? collect())->get($alumnoId);
                            if (!$asignacion) continue;
                            $hayFilas = true;
                            $becaReqCount = count($asignacion->beca?->documentos_requeridos ?? []);
                            $allAccepted = $becaReqCount > 0 && $docsAlumno->count() >= $becaReqCount && $docsAlumno->every(fn($d) => $d->validation_status === 'aceptado');
                        $becaStatus = $asignacion
                            ? ($asignacion->status === 'inactiva' ? 'inactiva' : ($allAccepted ? 'activa' : $asignacion->status))
                            : 'pendiente';
                            $becaNombre = $asignacion->beca->nombre ?? null;
                        @endphp
                        <tr>
                            <td style="font-weight:600;vertical-align:top;white-space:nowrap;">{{ $alumnoNombre }}</td>
                            <td style="vertical-align:top;">
                                @php
                                    $becaReqIdsRow = array_map('intval', $asignacion->beca?->documentos_requeridos ?? []);
                                    $subsPorReq = $docsAlumno->keyBy('document_requirement_id');
                                    $reqsAMostrar = $becaReqIdsRow
                                        ? ($expedienteConfig ?? collect())->filter(fn($r) => in_array((int)$r->id, $becaReqIdsRow))
                                        : collect();
                                @endphp
                                @if($reqsAMostrar->isEmpty())
                                    <span style="color:#9ca3af;font-size:.82rem;">Sin documentos requeridos</span>
                                @else
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                    @foreach($reqsAMostrar as $req)
                                        @php $sub = $subsPorReq->get($req->id); @endphp
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                            <span style="font-size:.82rem;color:#374151;font-weight:500;min-width:120px;">{{ $req->nombre }}</span>
                                            @if($sub && $sub->archivo_path)
                                                <a href="{{ asset('storage/'.$sub->archivo_path) }}" target="_blank" style="color:#223F70;font-size:.82rem;text-decoration:none;">{{ $sub->nombre_original ?? 'Ver archivo' }}</a>
                                                @if($sub->validation_status === 'aceptado')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Aceptado"><polyline points="20 6 9 17 4 12"/></svg>
                                                @elseif($sub->validation_status === 'rechazado')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Rechazado"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                @else
                                                    <span style="background:#e8ecf2;color:#666;font-size:.72rem;padding:2px 8px;border-radius:50px;">En revisión</span>
                                                @endif
                                            @else
                                                <span style="background:#fdeaea;color:#c0392b;font-size:.72rem;padding:2px 8px;border-radius:50px;">Pendiente</span>
                                            @endif
                                        </div>
                                    @endforeach
                                    </div>
                                @endif
                            </td>
                            <td style="vertical-align:top;white-space:nowrap;">
                                {{ $becaNombre ?? '—' }}
                            </td>
                            <td style="vertical-align:top;">
                                @if($becaStatus === 'activa')
                                    <span style="background:#27ae60;color:#fff;padding:3px 10px;border-radius:50px;font-size:.78rem;font-weight:600;cursor:pointer;" onclick="toggleBeca({{ $alumnoId }}, 'inactiva')" title="Clic para desactivar">Activa</span>
                                @elseif($becaStatus === 'inactiva')
                                    <span style="background:#c0392b;color:#fff;padding:3px 10px;border-radius:50px;font-size:.78rem;cursor:pointer;" onclick="toggleBeca({{ $alumnoId }}, 'activa')" title="Clic para activar">Inactiva</span>
                                @else
                                    <span style="background:#e8ecf2;color:#666;padding:3px 10px;border-radius:50px;font-size:.78rem;">Pendiente</span>
                                @endif
                            </td>
                            <td style="vertical-align:top;text-align:center;">
                                <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                                @if($docsAlumno->isNotEmpty())
                                    <button type="button"
                                        onclick="verDocumentos({{ $alumnoId }}, '{{ addslashes($alumnoNombre) }}')"
                                        title="Ver documentos"
                                        style="background:none;border:none;cursor:pointer;padding:4px;line-height:0;">
                                        <img src="{{ asset('images/icons/eye.svg') }}" alt="Ver" width="20" height="20">
                                    </button>
                                @endif
                                @if($asignacion)
                                    <button type="button" onclick="eliminarAsignacion({{ $alumnoId }})" title="Eliminar asignación" style="background:none;border:none;cursor:pointer;padding:4px;line-height:0;">
                                        <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar" width="18" height="18">
                                    </button>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if(!$hayFilas)
                        <tr><td colspan="4" style="text-align:center;color:#777;padding:20px;">Sin alumnos en becas.</td></tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal principal (tabs: Agregar alumno | Catálogo) ── --}}
<div id="becaModal" class="modal-overlay">
    <div class="modal-container" style="max-width:680px;min-height:auto;">
        <div class="modal-header" style="padding:0 20px;">
            {{-- Tabs --}}
            <div style="display:flex;gap:0;flex:1;">
                <button type="button" id="tab-asignar-btn"
                    onclick="switchTab('asignar')"
                    style="padding:16px 20px;font-size:.88rem;font-weight:600;border:none;background:transparent;cursor:pointer;border-bottom:3px solid #223F70;color:#223F70;">
                    Agregar alumno
                </button>
                <button type="button" id="tab-catalogo-btn"
                    onclick="switchTab('catalogo')"
                    style="padding:16px 20px;font-size:.88rem;font-weight:600;border:none;background:transparent;cursor:pointer;border-bottom:3px solid transparent;color:#9ca3af;">
                    Catálogo de Becas
                </button>
            </div>
            <button class="modal-close" type="button" onclick="closeBecaModal()" style="margin-left:auto;">&times;</button>
        </div>

        {{-- Tab: Agregar alumno --}}
        <div id="tab-asignar" class="modal-body" style="background:#fff;padding:20px;">
            <form id="becaForm">
                <div style="display:flex;flex-direction:column;gap:14px;">
                    <div>
                        <label style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Alumno</label>
                        <select id="beca_user_id" class="becas-input" name="user_id" required>
                            <option value="">Seleccione alumno</option>
                            @foreach(($todosAlumnos ?? collect()) as $alumno)
                                <option value="{{ $alumno->id }}">
                                    {{ trim(($alumno->nombre ?? '').' '.($alumno->apellido_paterno ?? '').' '.($alumno->apellido_materno ?? '')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Beca</label>
                        <select id="beca_beca_id" class="becas-input" name="beca_id" required onchange="onBecaChange(this.value)">
                            <option value="">Seleccione una beca</option>
                            @foreach(($becasCatalogo ?? collect()) as $beca)
                                <option value="{{ $beca->id }}" data-specs="{{ e($beca->especificaciones ?? '') }}">
                                    {{ $beca->nombre }}@if($beca->tipo) — {{ $beca->tipo }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="beca-specs-box" style="display:none;background:#f8f9fb;border:1px solid #e5e7eb;border-radius:8px;padding:12px;">
                        <p style="font-size:.78rem;font-weight:700;color:#B08955;margin:0 0 6px;text-transform:uppercase;letter-spacing:.04em;">Especificaciones</p>
                        <p id="beca-specs-texto" style="font-size:.83rem;color:#374151;margin:0;white-space:pre-line;"></p>
                    </div>
                </div>
                <div style="margin-top:18px;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="closeBecaModal()" class="becas-btn" style="background:#f3f4f6;color:#374151;">Cancelar</button>
                    <button type="submit" class="becas-btn becas-btn--primary">+ Agregar</button>
                </div>
            </form>
        </div>

        {{-- Tab: Catálogo de Becas --}}
        <div id="tab-catalogo" class="modal-body" style="background:#fff;padding:20px;display:none;">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
                <button class="becas-btn becas-btn--primary" type="button" onclick="openCatalogoModal()" style="font-size:.8rem;padding:6px 14px;">+ Nueva Beca</button>
            </div>
            <div style="overflow-x:auto;max-height:420px;overflow-y:auto;">
                @if(($becasCatalogo ?? collect())->isEmpty())
                    <p style="font-size:.85rem;color:#9ca3af;text-align:center;padding:20px 0;">No hay becas registradas.</p>
                @else
                <table class="becas-table">
                    <thead><tr><th>Nombre</th><th>Tipo</th><th>Documentos</th><th>Especificaciones</th><th style="width:100px;">Acciones</th></tr></thead>
                    <tbody>
                    @foreach($becasCatalogo as $beca)
                    <tr>
                        <td style="font-weight:600;">{{ $beca->nombre }}</td>
                        <td>{{ $beca->tipo ?: '—' }}</td>
                        <td>
                            @php $docsNombres = ($expedienteConfig ?? collect())->whereIn('id', $beca->documentos_requeridos ?? [])->pluck('nombre'); @endphp
                            @if($docsNombres->isEmpty())
                                <span style="color:#9ca3af;font-size:.82rem;">Ninguno</span>
                            @else
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    @foreach($docsNombres as $dn)
                                        <span style="background:#e8ecf2;color:#374151;font-size:.75rem;padding:2px 8px;border-radius:50px;">{{ $dn }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td style="font-size:.82rem;color:#374151;max-width:180px;white-space:pre-line;">{{ $beca->especificaciones ?: '—' }}</td>
                        <td>
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <button class="becas-btn" type="button" onclick="editarBeca({{ $beca->id }})" style="background:#e8ecf2;color:#374151;font-size:.75rem;padding:4px 10px;">Editar</button>
                                <button class="becas-btn becas-btn--danger" type="button" onclick="eliminarBeca({{ $beca->id }})" style="font-size:.75rem;padding:4px 10px;">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Crear/Editar Beca del catálogo ── --}}
<div id="catalogoModal" class="modal-overlay">
    <div class="modal-container" style="max-width:600px;">
        <div class="modal-header">
            <h3 id="catalogoModalTitle">Nueva Beca</h3>
            <button class="modal-close" type="button" onclick="closeCatalogoModal()">&times;</button>
        </div>
        <div class="modal-body" style="background:#fff;padding:20px;">
            <form id="catalogoForm" onsubmit="submitCatalogo(event)">
                <div style="display:flex;flex-direction:column;gap:14px;">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Nombre de la beca *</label>
                            <input type="text" name="nombre" id="cat_nombre" class="becas-input" placeholder="Ej. Beca Santander" required>
                        </div>
                        <div>
                            <label style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Tipo</label>
                            <input type="text" name="tipo" id="cat_tipo" class="becas-input" placeholder="Ej. Económica, Académica">
                        </div>
                    </div>

                    <div>
                        <label style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Especificaciones / Información de la beca</label>
                        <textarea name="especificaciones" id="cat_especificaciones" class="becas-input" rows="3" placeholder="Monto, requisitos, vigencia, datos relevantes..."></textarea>
                    </div>

                    <div>
                        <label style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Documentos requeridos</label>
                        <div style="display:flex;flex-direction:column;gap:6px;max-height:160px;overflow-y:auto;padding:2px;">
                            @forelse(($expedienteConfig ?? collect()) as $doc)
                            <label style="display:flex;align-items:center;gap:8px;font-size:.83rem;color:#374151;cursor:pointer;padding:6px 8px;border:1px solid #e5e7eb;border-radius:6px;">
                                <input type="checkbox" name="documentos_requeridos[]" value="{{ $doc->id }}" class="cat-doc-check" style="accent-color:#B08955;width:15px;height:15px;">
                                {{ $doc->nombre }}
                            </label>
                            @empty
                            <p style="font-size:.82rem;color:#9ca3af;margin:0;">No hay documentos en Expediente → Becas.</p>
                            @endforelse
                        </div>
                    </div>

                </div>

                <div style="margin-top:18px;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="closeCatalogoModal()" class="becas-btn" style="background:#f3f4f6;color:#374151;">Cancelar</button>
                    <button type="submit" class="becas-btn becas-btn--primary" id="catalogoSubmitBtn">Guardar beca</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Ver Documentos ── --}}
<div id="docsModal" class="modal-overlay">
    <div class="modal-container" style="max-width:560px;">
        <div class="modal-header">
            <h3 id="docsModalTitle">Documentos de becas</h3>
            <button class="modal-close" type="button" onclick="document.getElementById('docsModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body" style="background:#fff;padding:20px;">
            <div id="docsModalContent" style="display:flex;flex-direction:column;gap:16px;"></div>
            <div style="margin-top:18px;display:flex;justify-content:flex-end;">
                <button id="guardar-docs-btn" type="button" onclick="guardarCambiosDocumentos()" class="becas-btn becas-btn--primary">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script>
// Docs por alumno precargados para el modal
const DOCS_POR_ALUMNO = @json($docsPorAlumnoJs ?? []);

// Datos del catálogo indexados por ID para no depender de inline onclick con JSON
const BECAS_MAP = @json($becasMap ?? []);

// ── Modal Ver Documentos ──
function verDocumentos(alumnoId, alumnoNombre) {
    const docs = DOCS_POR_ALUMNO[alumnoId] || [];
    document.getElementById('docsModalTitle').textContent = alumnoNombre;

    const statusBadge = {
        'aceptado':   `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
        'rechazado':  `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
        'en_revision': `<span style="background:#e8ecf2;color:#666;font-size:.72rem;padding:2px 10px;border-radius:50px;">En revisión</span>`,
    };

    let html = '';
    docs.forEach(doc => {
        const badge  = statusBadge[doc.validation_status] || statusBadge['en_revision'];
        const isAcep = doc.validation_status === 'aceptado';

        html += `
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;">
            <p style="font-weight:700;color:#B08955;margin:0 0 8px;font-size:.88rem;">${doc.nombre}</p>
            ${doc.archivo_path
                ? `<a href="/storage/${doc.archivo_path}" target="_blank" style="color:#223F70;font-size:.83rem;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Ver documento</a>`
                : `<span style="color:#9ca3af;font-size:.82rem;display:block;margin-bottom:10px;">Sin archivo</span>`
            }
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.83rem;color:#374151;">
                <input type="checkbox" data-doc-id="${doc.id}" ${isAcep ? 'checked' : ''}
                    style="accent-color:#223F70;width:16px;height:16px;">
                Aceptar documento
            </label>
            <span id="doc-status-${doc.id}" style="display:inline-flex;align-items:center;margin-top:6px;">${badge}</span>
        </div>`;
    });

    document.getElementById('docsModalContent').innerHTML = html;
    document.getElementById('docsModal').style.display = 'flex';
}

async function guardarCambiosDocumentos() {
    const checkboxes = document.querySelectorAll('#docsModalContent input[type="checkbox"][data-doc-id]');
    const btn = document.getElementById('guardar-docs-btn');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    const requests = [...checkboxes].map(cb => {
        const status = cb.checked ? 'aceptado' : 'en_revision';
        return fetch(`/expediente-alumno/documento/${cb.dataset.docId}/validar`, {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });
    });

    try {
        const results = await Promise.all(requests);
        const allOk = results.every(r => r.ok);
        if (!allOk) throw new Error('Error al guardar algunos documentos.');
        location.reload();
    } catch (err) {
        alert(err.message || 'Error al guardar los cambios.');
        btn.disabled = false;
        btn.textContent = 'Guardar cambios';
    }
}

async function validarDoc(docId, status, checkbox) {
    const statusSpan = document.getElementById(`doc-status-${docId}`);
    try {
        const res = await fetch(`/expediente-alumno/documento/${docId}/validar`, {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });
        if (!res.ok) throw new Error('Error al validar');
        if (statusSpan) {
            if (status === 'aceptado') {
                statusSpan.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
                statusSpan.style.background = 'none';
            } else {
                statusSpan.innerHTML = 'En revisión';
                statusSpan.style.background = '#e8ecf2'; statusSpan.style.color = '#666';
            }
        }
    } catch (err) {
        alert('Error al validar el documento.');
        if (checkbox) checkbox.checked = !checkbox.checked;
    }
}

// ── onBecaChange: muestra especificaciones y sección de docs al seleccionar beca ──
function onBecaChange(becaId) {
    const specsBox   = document.getElementById('beca-specs-box');
    const specsTexto = document.getElementById('beca-specs-texto');

    if (!becaId) {
        specsBox.style.display = 'none';
        return;
    }

    const opt = document.querySelector(`#beca_beca_id option[value="${becaId}"]`);
    if (!opt) return;

    const specs = (opt.dataset.specs || '').trim();
    if (specs) {
        specsTexto.textContent = specs;
        specsBox.style.display = 'block';
    } else {
        specsBox.style.display = 'none';
    }
}

// ── Modal Catálogo ──
let catalogoEditId = null;

function openCatalogoModal() {
    catalogoEditId = null;
    document.getElementById('catalogoModalTitle').textContent = 'Nueva Beca';
    document.getElementById('catalogoForm').reset();
    document.querySelectorAll('.cat-doc-check').forEach(c => c.checked = false);
    // Asegura que el modal principal esté abierto en el tab catálogo
    if (document.getElementById('becaModal').style.display !== 'flex') {
        openBecaModal('catalogo');
    }
    document.getElementById('catalogoModal').style.display = 'flex';
}

function closeCatalogoModal() {
    document.getElementById('catalogoModal').style.display = 'none';
    catalogoEditId = null;
}

function editarBeca(id) {
    const beca = BECAS_MAP[id];
    if (!beca) return;
    catalogoEditId = id;
    document.getElementById('catalogoModalTitle').textContent = 'Editar Beca';
    document.getElementById('cat_nombre').value = beca.nombre;
    document.getElementById('cat_tipo').value = beca.tipo || '';
    document.getElementById('cat_especificaciones').value = beca.especificaciones || '';
    document.querySelectorAll('.cat-doc-check').forEach(c => {
        c.checked = (beca.documentos_requeridos || []).includes(parseInt(c.value));
    });
    document.getElementById('catalogoModal').style.display = 'flex';
}

async function submitCatalogo(e) {
    e.preventDefault();
    const btn = document.getElementById('catalogoSubmitBtn');
    btn.disabled = true; btn.textContent = 'Guardando...';

    const nombre = document.getElementById('cat_nombre').value.trim();
    const tipo = document.getElementById('cat_tipo').value.trim();
    const especificaciones = document.getElementById('cat_especificaciones').value.trim();
    const docsChecked = [...document.querySelectorAll('.cat-doc-check:checked')].map(c => parseInt(c.value));
    const method = catalogoEditId ? 'PUT' : 'POST';
    const url = catalogoEditId
        ? '{{ url("control-escolar/becas/catalogo") }}/' + catalogoEditId
        : '{{ route("escolar.becas.catalogo.store") }}';

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ nombre, tipo, especificaciones, documentos_requeridos: docsChecked })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error al guardar');
        closeCatalogoModal();
        location.reload();
    } catch (err) {
        alert(err.message || 'Error al guardar la beca.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar beca';
    }
}

async function eliminarBeca(id) {
    if (!confirm('¿Eliminar esta beca del catálogo?')) return;
    try {
        const res = await fetch('{{ url("control-escolar/becas/catalogo") }}/' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        if (!res.ok) throw new Error('Error al eliminar');
        location.reload();
    } catch (err) { alert(err.message || 'Error al eliminar la beca.'); }
}

// ── Beca Asignacion ──
async function toggleBeca(userId, status) {
    const msg = status === 'activa' ? '¿Activar la beca para este alumno?' : '¿Desactivar la beca para este alumno?';
    if (!confirm(msg)) return;
    try {
        const res = await fetch('{{ route("escolar.becas.toggle") }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ user_id: userId, status })
        });
        if (!res.ok) throw new Error('Error al cambiar estado');
        location.reload();
    } catch (err) { alert(err.message || 'Error al cambiar estado de beca.'); }
}

async function eliminarAsignacion(userId) {
    if (!confirm('¿Eliminar la asignación de beca para este alumno?')) return;
    try {
        const res = await fetch(`/control-escolar/becas/asignacion/${userId}`, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        });
        if (!res.ok) throw new Error('Error al eliminar');
        location.reload();
    } catch (err) { alert(err.message || 'Error al eliminar la asignación.'); }
}

async function validarDocBeca(docId, status) {
    if (!confirm(status === 'aceptado' ? '¿Aceptar este documento?' : '¿Rechazar este documento?')) return;
    try {
        const res = await fetch(`/expediente-alumno/documento/${docId}/validar`, {
            method: 'PATCH',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ status })
        });
        if (!res.ok) throw new Error('Error al validar');
        location.reload();
    } catch (err) { alert(err.message || 'Error al validar documento.'); }
}

function switchTab(tab) {
    const isAsignar = tab === 'asignar';
    document.getElementById('tab-asignar').style.display    = isAsignar ? 'block' : 'none';
    document.getElementById('tab-catalogo').style.display   = isAsignar ? 'none'  : 'block';
    document.getElementById('tab-asignar-btn').style.borderBottomColor = isAsignar ? '#223F70' : 'transparent';
    document.getElementById('tab-asignar-btn').style.color             = isAsignar ? '#223F70' : '#9ca3af';
    document.getElementById('tab-catalogo-btn').style.borderBottomColor= isAsignar ? 'transparent' : '#223F70';
    document.getElementById('tab-catalogo-btn').style.color            = isAsignar ? '#9ca3af' : '#223F70';
}

function openBecaModal(tab) {
    document.getElementById('becaForm').reset();
    document.getElementById('beca-specs-box').style.display = 'none';
    document.getElementById('becaModal').style.display = 'flex';
    switchTab(tab || 'asignar');
}
function closeBecaModal(){ document.getElementById('becaModal').style.display='none'; }

document.getElementById('becaForm')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const userId = document.getElementById('beca_user_id').value;
  const becaId = document.getElementById('beca_beca_id').value;
  if (!userId) { alert('Seleccione un alumno.'); return; }
  if (!becaId) { alert('Seleccione una beca.'); return; }
  const submitBtn = this.querySelector('button[type="submit"]');
  if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Agregando...'; }
  try {
    const res = await fetch('{{ route("escolar.becas.toggle") }}', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ user_id: parseInt(userId), beca_id: parseInt(becaId), status: 'pendiente' })
    });
    if (!res.ok) throw new Error('Error al agregar alumno');
    closeBecaModal();
    location.reload();
  } catch (err) { alert(err.message || 'Error al agregar alumno.'); }
  finally { if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = '+ Agregar'; } }
});

(function () {
  const input = document.getElementById('becas-search-input');
  const form  = document.getElementById('becas-search-form');
  if (!input || !form) return;

  let timer = null;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { form.submit(); }, 400);
  });
})();
</script>
@endsection
