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
        <div class="becas-card">
            <table class="becas-table">
                <thead><tr><th>Alumno</th><th>Documento</th><th>Descripción</th><th>Archivo</th><th>Fecha</th><th>Acciones</th></tr></thead>
                <tbody id="becas-table-body">
                    @include('layouts.ControlEsc.Becas.partials.table_rows', ['dataList' => $dataList])
                </tbody>
            </table>
            <div id="becas-pagination" style="margin-top:16px;">{{ $dataList->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

<div id="becaModal" class="modal-overlay">
    <div class="modal-container" style="max-width:560px;min-height:auto;">
        <div class="modal-header"><h3 id="becaModalTitle">Agregar documento de beca</h3><button class="modal-close" type="button" onclick="closeBecaModal()">&times;</button></div>
        <div class="modal-body" style="background:#fff;padding:16px;">
            <form id="becaForm" method="POST" action="{{ route('escolar.becas.store') }}" enctype="multipart/form-data">
                @csrf
                <input id="beca_method" type="hidden" name="_method" value="">
                <div>
                    <label>Alumno</label>
                    <select id="beca_user_id" class="becas-input" name="user_id" required>
                        <option value="">Seleccione alumno</option>
                        @foreach(($alumnos ?? collect()) as $alumno)
                            <option value="{{ $alumno->id }}">
                                {{ trim(($alumno->nombre ?? '') . ' ' . ($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? '')) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div><label>Nombre documento</label><input id="beca_nombre_documento" class="becas-input" type="text" name="nombre_documento" required></div>
                <div><label>Descripción</label><textarea id="beca_descripcion" class="becas-input" name="descripcion"></textarea></div>
                <div><label>Archivo</label><input id="beca_archivo" type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png"></div>
                <div style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="submit" class="becas-btn becas-btn--primary">+ Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBecaModal(){ document.getElementById('becaForm').reset(); document.getElementById('becaForm').action='{{ route('escolar.becas.store') }}'; document.getElementById('beca_method').value=''; document.getElementById('beca_archivo').required=true; document.getElementById('becaModalTitle').textContent='Agregar documento de beca'; document.getElementById('becaModal').style.display='flex'; }
function closeBecaModal(){ document.getElementById('becaModal').style.display='none'; }
async function editBeca(id){
  const res = await fetch(`/control-escolar/becas/documentos/${id}`,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
  if(!res.ok){ alert('No se pudo cargar'); return; }
  const row = (await res.json()).data;
  document.getElementById('becaForm').action = `/control-escolar/becas/documentos/${id}`;
  document.getElementById('beca_method').value = 'PUT';
  document.getElementById('beca_user_id').value = row.user_id || '';
  document.getElementById('beca_nombre_documento').value = row.nombre_documento || '';
  document.getElementById('beca_descripcion').value = row.descripcion || '';
  document.getElementById('beca_archivo').required = false;
  document.getElementById('becaModalTitle').textContent='Editar documento de beca';
  document.getElementById('becaModal').style.display='flex';
}

document.getElementById('becaForm')?.addEventListener('submit', async function (e) {
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

    closeBecaModal();
    if (typeof window.refreshBecasTableFromCurrentFilters === 'function') {
      window.refreshBecasTableFromCurrentFilters();
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
  const form = document.getElementById('becas-search-form');
  const input = document.getElementById('becas-search-input');
  const tbody = document.getElementById('becas-table-body');
  const pagination = document.getElementById('becas-pagination');
  if (!form || !input || !tbody || !pagination) return;

  let timer = null;

  function refreshBecasTable(url) {
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
  }
  window.refreshBecasTableFromCurrentFilters = function () {
    const url = new URL(form.action, window.location.origin);
    const value = (input.value || '').trim();
    if (value) url.searchParams.set('search', value);
    refreshBecasTable(url.toString());
  };

  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () {
      const url = new URL(form.action, window.location.origin);
      const value = (input.value || '').trim();
      if (value) url.searchParams.set('search', value);
      refreshBecasTable(url.toString());
    }, 280);
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const url = new URL(form.action, window.location.origin);
    const value = (input.value || '').trim();
    if (value) url.searchParams.set('search', value);
    refreshBecasTable(url.toString());
  });

  document.addEventListener('click', function (e) {
    const link = e.target.closest('#becas-pagination a');
    if (!link) return;
    e.preventDefault();
    refreshBecasTable(link.href);
  });
})();
</script>
@endsection
