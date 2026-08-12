@extends('layouts.app')
@push('css')
    @vite($cssFile)
@endpush
@section('content')
<div class="container">
    <div id="umi-app-view">
        <div class="content-header"><div class="content-title"><h3>{{ $titulo }}</h3></div></div>
        <div class="becas-toolbar">
            <form method="GET" action="{{ request()->url() }}" style="flex:1;">
                <div class="becas-search-wrap">
                    <img src="{{ asset('images/icons/search.svg') }}" alt="" class="becas-search-icon" aria-hidden="true">
                    <input type="text" name="search" value="{{ request('search') }}" class="becas-input" placeholder="Buscar Por...">
                </div>
            </form>
            <button class="becas-btn becas-btn--primary" type="button" onclick="openModuloModal()">+ Agregar</button>
        </div>
        @php
            $submittedByReq = ($submittedDocs ?? collect())->groupBy(function ($doc) {
                return $doc->user_id . '-' . $doc->document_requirement_id;
            });
        @endphp

        <div class="becas-card">
            <div style="overflow-x:auto;">
                <table class="becas-table">
                    <thead><tr><th>Alumno</th><th>Documento</th><th>Archivo</th><th>Estado</th><th>{{ $titulo }}</th><th>Validación</th></tr></thead>
                    <tbody>
                    @php $hayFilas = false; @endphp
                    @foreach($alumnos ?? [] as $alumno)
                        @php
                            $alumnoId = $alumno->id;
                            $alumnoNombre = trim(($alumno->nombre ?? '') . ' ' . ($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? ''));
                            $tieneAlgo = false;
                            foreach ($expedienteConfig ?? [] as $req) {
                                $key = $alumnoId . '-' . $req->id;
                                if ($submittedByReq->has($key)) { $tieneAlgo = true; break; }
                            }
                            $asignacion = ($asignaciones ?? collect())->get($alumnoId);
                            $moduloStatus = $asignacion->status ?? 'pendiente';
                            if (!$tieneAlgo && !$asignacion) continue;
                        @endphp
                        @foreach($expedienteConfig ?? [] as $req)
                            @php
                                $key = $alumnoId . '-' . $req->id;
                                $sub = $submittedByReq->has($key) ? $submittedByReq->get($key)->first() : null;
                                $hayFilas = true;
                            @endphp
                            <tr>
                                <td>{{ $alumnoNombre }}</td>
                                <td>{{ $req->nombre }}</td>
                                <td>
                                    @if($sub && $sub->archivo_path)
                                        <a href="{{ asset('storage/' . $sub->archivo_path) }}" target="_blank" style="color:#223F70;text-decoration:none;font-size:0.85rem;display:inline-flex;align-items:center;gap:4px;">
                                            <img src="{{ asset('images/icons/eye.svg') }}" width="14" height="14" style="vertical-align:middle;flex-shrink:0;">
                                            {{ $sub->nombre_original ?? $req->nombre }}
                                        </a>
                                    @else
                                        <span style="color:#aaa;font-size:0.82rem;">Sin archivo</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$sub)
                                        <span style="background:#e8ecf2;color:#666;padding:3px 10px;border-radius:50px;font-size:0.78rem;">Pendiente</span>
                                    @elseif($sub->validation_status === 'aceptado')
                                        <span style="background:#223F70;color:#fff;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;">Aceptado</span>
                                    @elseif($sub->validation_status === 'rechazado')
                                        <span style="background:#c0392b;color:#fff;padding:3px 10px;border-radius:50px;font-size:0.78rem;">Rechazado</span>
                                    @else
                                        <span style="background:#e8ecf2;color:#666;padding:3px 10px;border-radius:50px;font-size:0.78rem;">En revisión</span>
                                    @endif
                                </td>
                                <td>
                                    @if($moduloStatus === 'activa')
                                        <span style="background:#27ae60;color:#fff;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;cursor:pointer;" onclick="toggleModulo({{ $alumnoId }}, 'inactiva')" title="Clic para desactivar">Activa</span>
                                    @elseif($moduloStatus === 'inactiva')
                                        <span style="background:#c0392b;color:#fff;padding:3px 10px;border-radius:50px;font-size:0.78rem;cursor:pointer;" onclick="toggleModulo({{ $alumnoId }}, 'activa')" title="Clic para activar">Inactiva</span>
                                    @else
                                        <span style="background:#e8ecf2;color:#666;padding:3px 10px;border-radius:50px;font-size:0.78rem;">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sub)
                                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:nowrap;">
                                            <button class="becas-btn becas-btn--primary" type="button" onclick="validarDoc({{ $sub->id }}, 'aceptado')" style="font-size:0.72rem;padding:3px 8px;">Aceptar</button>
                                            <button class="becas-btn becas-btn--danger" type="button" onclick="validarDoc({{ $sub->id }}, 'rechazado')" style="font-size:0.72rem;padding:3px 8px;">Rechazar</button>
                                        </div>
                                    @else
                                        <span style="color:#aaa;font-size:0.82rem;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    @if(!$hayFilas)
                        <tr><td colspan="6" style="text-align:center;color:#777;padding:20px;">Sin registros.</td></tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="moduloModal" class="modal-overlay">
    <div class="modal-container" style="max-width:460px;min-height:auto;">
        <div class="modal-header"><h3>Agregar alumno a {{ $titulo }}</h3><button class="modal-close" type="button" onclick="closeModuloModal()">&times;</button></div>
        <div class="modal-body" style="background:#fff;padding:16px;">
            <form id="moduloForm">
                <div>
                    <label>Alumno</label>
                    <select id="modulo_user_id" class="becas-input" name="user_id" required>
                        <option value="">Seleccione alumno</option>
                        @foreach(($alumnos ?? collect()) as $alumno)
                            <option value="{{ $alumno->id }}">
                                {{ trim(($alumno->nombre ?? '') . ' ' . ($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? '')) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="submit" class="becas-btn becas-btn--primary">+ Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const MODULO_TOGGLE_URL = '{{ route("escolar.modulo.toggle." . $modulo) }}';

async function toggleModulo(userId, status) {
    const msg = status === 'activa' ? '¿Activar para este alumno?' : '¿Desactivar para este alumno?';
    if (!confirm(msg)) return;
    try {
        const res = await fetch(MODULO_TOGGLE_URL, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ user_id: userId, status })
        });
        if (!res.ok) throw new Error('Error al cambiar estado');
        location.reload();
    } catch (err) { alert(err.message || 'Error.'); }
}

async function validarDoc(docId, status) {
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

function openModuloModal(){ document.getElementById('moduloForm').reset(); document.getElementById('moduloModal').style.display='flex'; }
function closeModuloModal(){ document.getElementById('moduloModal').style.display='none'; }

document.getElementById('moduloForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const userId = document.getElementById('modulo_user_id').value;
    if (!userId) { alert('Seleccione un alumno.'); return; }
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Agregando...'; }
    try {
        const res = await fetch(MODULO_TOGGLE_URL, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ user_id: parseInt(userId), status: 'pendiente' })
        });
        if (!res.ok) throw new Error('Error al agregar alumno');
        closeModuloModal();
        location.reload();
    } catch (err) { alert(err.message || 'Error.'); }
    finally { if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = '+ Agregar'; } }
});
</script>
@endsection
