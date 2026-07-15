@extends('layouts.app')
@push('css')
    @vite('resources/css/ControlEsc/titulacion.css')
@endpush
@section('content')
<div class="container">
    <div id="umi-app-view">
        <div class="content-header"><div class="content-title"><h3>Titulación</h3></div></div>
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
                <thead><tr><th>Alumno</th><th>Documento</th><th>Descripción</th><th>Archivo</th><th>Fecha</th><th>Acciones</th></tr></thead>
                <tbody id="titulacion-table-body">
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
                        <strong><i class="fa-solid fa-circle-info"></i> Documentos requeridos (Titulación):</strong>
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
// Documentos configurados en Ajustes → Expediente (proceso Titulación).
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
document.getElementById('titulacion_nombre_documento')?.addEventListener('input', titulacionApplyDocConfig);
document.getElementById('titulacion_nombre_documento')?.addEventListener('change', titulacionApplyDocConfig);

function openTitulacionModal(){ document.getElementById('titulacionForm').reset(); document.getElementById('titulacionForm').action='{{ route('escolar.titulacion.store') }}'; document.getElementById('titulacion_method').value=''; document.getElementById('titulacion_archivo').required=true; document.getElementById('titulacionModalTitle').textContent='Agregar documento de titulación'; document.getElementById('titulacionModal').style.display='flex'; }
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
