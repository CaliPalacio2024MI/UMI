@extends('layouts.app')
@push('css')
    @vite('resources/css/ControlEsc/titulacion.css')
@endpush
@section('content')
<div class="container">
    <div id="umi-app-view">
        <div class="content-header"><div class="content-title"><h3>Titulación</h3></div></div>
        {{-- Panel de documentos requeridos (configurados en Expediente) --}}
        @if(!empty($expedienteConfig) && $expedienteConfig->isNotEmpty())
        <div style="background:#f8fafc;border:1px solid #dde6f0;border-radius:10px;padding:14px 18px;margin-bottom:18px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-weight:700;color:#223F70;font-size:0.95rem;">Documentos requeridos para Titulación</span>
                <span style="font-size:0.8rem;color:#888;">Configurados en Ajustes → Expediente</span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                @foreach($expedienteConfig as $req)
                <div style="background:#fff;border:1px solid #d0d8ea;border-radius:8px;padding:10px 14px;min-width:180px;flex:1;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <div>
                        <div style="font-weight:600;color:#223F70;font-size:0.88rem;">{{ $req->nombre }}</div>
                        <div style="font-size:0.75rem;color:#888;margin-top:2px;">
                            {{ strtoupper(implode(', ', $req->tiposArchivoArray())) }}
                            @if($req->obligatorio)
                                &nbsp;<span style="color:#c0392b;font-weight:600;">Obligatorio</span>
                            @else
                                &nbsp;<span style="color:#888;">Opcional</span>
                            @endif
                        </div>
                    </div>
                    <button class="titulacion-btn titulacion-btn--primary" type="button"
                        style="font-size:0.78rem;padding:4px 10px;white-space:nowrap;"
                        onclick="openTitulacionModalConDoc('{{ addslashes($req->nombre) }}')">
                        + Subir
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="titulacion-toolbar">
            <form id="titulacion-search-form" method="GET" action="{{ request()->url() }}" style="flex:1;">
                <div class="titulacion-search-wrap">
                    <img src="{{ asset('images/icons/search.svg') }}" alt="" class="titulacion-search-icon" aria-hidden="true">
                    <input type="text" id="titulacion-search-input" name="search" value="{{ request('search') }}" class="titulacion-input" placeholder="Buscar por...">
                </div>
            </form>
            <button class="titulacion-btn titulacion-btn--primary" type="button" onclick="openTitulacionModal()">+ Agregar</button>
        </div>
        <div class="titulacion-card">
            <table class="titulacion-table">
                <thead><tr><th>Alumno</th><th>Documento</th><th>Archivo</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
                <tbody id="titulacion-table-body">
                {{-- Docs subidos por alumnos desde su expediente --}}
                @foreach($submittedDocs ?? [] as $sub)
                <tr>
                    <td>{{ trim(($sub->user->nombre ?? '') . ' ' . ($sub->user->apellido_paterno ?? '') . ' ' . ($sub->user->apellido_materno ?? '')) }}</td>
                    <td>{{ $sub->requirement->nombre ?? '—' }}</td>
                    <td>
                        <a href="{{ asset('storage/' . $sub->archivo_path) }}" target="_blank" class="titulacion-btn titulacion-btn--icon" title="Ver archivo">
                            <img src="{{ asset('images/icons/eye.svg') }}" alt="Ver">
                        </a>
                    </td>
                    <td>
                        @if($sub->validation_status === 'aceptado')
                            <span style="background:#223F70;color:#fff;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;">Aceptado</span>
                        @elseif($sub->validation_status === 'rechazado')
                            <span style="background:#c0392b;color:#fff;padding:3px 10px;border-radius:50px;font-size:0.78rem;">Rechazado</span>
                        @else
                            <span style="background:#e8ecf2;color:#666;padding:3px 10px;border-radius:50px;font-size:0.78rem;">Pendiente</span>
                        @endif
                    </td>
                    <td>{{ optional($sub->created_at)->format('d/m/Y') }}</td>
                    <td style="white-space:nowrap;">
                        @if($sub->validation_status !== 'aceptado')
                        <button class="titulacion-btn titulacion-btn--icon" type="button" title="Aceptar"
                            onclick="validarDocTitulacion({{ $sub->id }}, 'aceptado')"
                            style="background:#e6f6ec;border:1px solid #27ae60;border-radius:6px;padding:4px 8px;">
                            <img src="{{ asset('images/icons/check.svg') }}" alt="Aceptar" style="width:16px;height:16px;" onerror="this.outerHTML='✓'">
                        </button>
                        @endif
                        @if($sub->validation_status !== 'rechazado')
                        <button class="titulacion-btn titulacion-btn--icon titulacion-btn--danger" type="button" title="Rechazar"
                            onclick="validarDocTitulacion({{ $sub->id }}, 'rechazado')"
                            style="background:#fdeaea;border:1px solid #c0392b;border-radius:6px;padding:4px 8px;">
                            <img src="{{ asset('images/icons/delete.svg') }}" alt="Rechazar" style="width:16px;height:16px;" onerror="this.outerHTML='✗'">
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
                    @include('layouts.ControlEsc.Titulacion.partials.table_rows', ['dataList' => $dataList])
                </tbody>
            </table>
            <div id="titulacion-pagination" style="margin-top:16px;">{{ $dataList->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

<div id="titulacionModal" class="modal-overlay">
    <div class="modal-container" style="max-width:560px;min-height:auto;">
        <div class="modal-header"><h3 id="titulacionModalTitle">Agregar documento de titulación</h3><button class="modal-close" type="button" onclick="closeTitulacionModal()">&times;</button></div>
        <div class="modal-body" style="background:#fff;padding:16px;">
            <form id="titulacionForm" method="POST" action="{{ route('escolar.titulacion.store') }}" enctype="multipart/form-data">
                @csrf
                <input id="titulacion_method" type="hidden" name="_method" value="">
                <div>
                    <label>Alumno</label>
                    <select id="titulacion_user_id" class="titulacion-input" name="user_id" required>
                        <option value="">Seleccione alumno</option>
                        @foreach(($alumnos ?? collect()) as $alumno)
                            <option value="{{ $alumno->id }}">
                                {{ trim(($alumno->nombre ?? '') . ' ' . ($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? '')) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(!empty($expedienteConfig) && $expedienteConfig->isNotEmpty())
                    <div class="titulacion-config-guide" style="background:#f0f7f2;border:1px solid #cbe6d5;border-radius:8px;padding:10px 12px;margin:4px 0 10px;font-size:0.85rem;color:#1e7e45;">
                        <strong><img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" width="14" height="14" style="vertical-align:middle;"> Documentos requeridos (Titulación):</strong>
                        <ul style="margin:6px 0 0;padding-left:18px;">
                            @foreach($expedienteConfig as $req)
                                <li>{{ $req->nombre }} — {{ strtoupper(implode(', ', $req->tiposArchivoArray())) }}{{ $req->obligatorio ? ' (obligatorio)' : '' }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div>
                    <label>Nombre documento</label>
                    <input id="titulacion_nombre_documento" class="titulacion-input" type="text" name="nombre_documento"
                           list="titulacion_docs_list" autocomplete="off" required>
                    <datalist id="titulacion_docs_list">
                        @foreach(($expedienteConfig ?? collect()) as $req)
                            <option value="{{ $req->nombre }}"></option>
                        @endforeach
                    </datalist>
                    <small id="titulacion_doc_hint" style="display:none;color:#888;margin-top:4px;"></small>
                </div>
                <div><label>Descripción</label><textarea id="titulacion_descripcion" class="titulacion-input" name="descripcion"></textarea></div>
                <div><label>Archivo</label><input id="titulacion_archivo" type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png"></div>
                <div style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="submit" class="titulacion-btn titulacion-btn--primary">+ Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $titulacionDocsConfig = ($expedienteConfig ?? collect())->mapWithKeys(fn ($r) => [
        mb_strtolower($r->nombre) => [
            'tipos' => $r->tiposArchivoArray(),
            'cantidad' => $r->cantidad,
            'obligatorio' => (bool) $r->obligatorio,
        ],
    ]);
@endphp
<script>
const TITULACION_DOCS_CONFIG = @json($titulacionDocsConfig);
const TITULACION_ACCEPT_MAP = { pdf: '.pdf', jpg: '.jpg,.jpeg', png: '.png', doc: '.doc,.docx', xls: '.xls,.xlsx' };
function titulacionApplyDocConfig(){
    const nombre = (document.getElementById('titulacion_nombre_documento').value || '').trim().toLowerCase();
    const archivo = document.getElementById('titulacion_archivo');
    const hint = document.getElementById('titulacion_doc_hint');
    const cfg = TITULACION_DOCS_CONFIG[nombre];
    if (cfg) {
        archivo.accept = (cfg.tipos || []).map(t => TITULACION_ACCEPT_MAP[t] || ('.' + t)).join(',') || '.pdf,.jpg,.jpeg,.png';
        hint.textContent = 'Tipos permitidos: ' + (cfg.tipos || []).map(t => t.toUpperCase()).join(', ') + ' · Máx ' + cfg.cantidad + ' archivo(s)';
        hint.style.display = 'block';
    } else {
        archivo.accept = '.pdf,.jpg,.jpeg,.png';
        hint.style.display = 'none';
    }
}
async function validarDocTitulacion(docId, status) {
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

document.getElementById('titulacion_nombre_documento')?.addEventListener('input', titulacionApplyDocConfig);
document.getElementById('titulacion_nombre_documento')?.addEventListener('change', titulacionApplyDocConfig);

function openTitulacionModal(){ document.getElementById('titulacionForm').reset(); document.getElementById('titulacionForm').action='{{ route('escolar.titulacion.store') }}'; document.getElementById('titulacion_method').value=''; document.getElementById('titulacion_archivo').required=true; document.getElementById('titulacionModalTitle').textContent='Agregar documento de titulación'; document.getElementById('titulacionModal').style.display='flex'; }
function openTitulacionModalConDoc(nombre){ openTitulacionModal(); document.getElementById('titulacion_nombre_documento').value = nombre; titulacionApplyDocConfig(); }
function closeTitulacionModal(){ document.getElementById('titulacionModal').style.display='none'; }
async function editTitulacion(id){
  const res = await fetch(`/control-escolar/titulacion/documentos/${id}`,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
  if(!res.ok){ alert('No se pudo cargar'); return; }
  const row = (await res.json()).data;
  document.getElementById('titulacionForm').action = `/control-escolar/titulacion/documentos/${id}`;
  document.getElementById('titulacion_method').value = 'PUT';
  document.getElementById('titulacion_user_id').value = row.user_id || '';
  document.getElementById('titulacion_nombre_documento').value = row.nombre_documento || '';
  document.getElementById('titulacion_descripcion').value = row.descripcion || '';
  document.getElementById('titulacion_archivo').required = false;
  titulacionApplyDocConfig();
  document.getElementById('titulacionModalTitle').textContent='Editar documento de titulación';
  document.getElementById('titulacionModal').style.display='flex';
}

document.getElementById('titulacionForm')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const form = e.currentTarget;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const prevText = submitBtn ? submitBtn.textContent : '';
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Guardando...';
  }

  try {
    const res = await fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.ok) {
      throw new Error(data.message || 'No se pudo guardar el documento.');
    }

    closeTitulacionModal();
    const currentUrl = window.location.href;
    if (typeof refreshTitulacionTable === 'function') {
      refreshTitulacionTable(currentUrl);
    }

    const successModal = document.getElementById('careerSuccessModal');
    const successModalMessage = document.getElementById('careerSuccessModalMessage');
    if (successModal && successModalMessage) {
      successModalMessage.textContent = data.message || 'Operación completada.';
      successModal.style.display = 'flex';
    }
  } catch (err) {
    alert(err.message || 'Error al guardar.');
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = prevText || '+ Guardar';
    }
  }
});

(function () {
  const form = document.getElementById('titulacion-search-form');
  const input = document.getElementById('titulacion-search-input');
  const tbody = document.getElementById('titulacion-table-body');
  const pagination = document.getElementById('titulacion-pagination');
  if (!form || !input || !tbody || !pagination) return;

  let timer = null;

  window.refreshTitulacionTable = function (url) {
    fetch(url, {
      method: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then((r) => r.json())
      .then((data) => {
        tbody.innerHTML = data.tbody || '';
        pagination.innerHTML = data.pagination || '';
        window.history.replaceState({}, '', url);
      })
      .catch((e) => console.error(e));
  };

  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () {
      const url = new URL(form.action, window.location.origin);
      const value = (input.value || '').trim();
      if (value) url.searchParams.set('search', value);
      window.refreshTitulacionTable(url.toString());
    }, 280);
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const url = new URL(form.action, window.location.origin);
    const value = (input.value || '').trim();
    if (value) url.searchParams.set('search', value);
    window.refreshTitulacionTable(url.toString());
  });

  document.addEventListener('click', function (e) {
    const link = e.target.closest('#titulacion-pagination a');
    if (!link) return;
    e.preventDefault();
    window.refreshTitulacionTable(link.href);
  });
})();
</script>
@endsection
