@extends('layouts.app')

@section('title', 'Horarios - ' . $course->title)

@vite(['resources/css/Cursos/horarios.css'])

@section('content')

<div class="container-fluid horarios-view">

    {{-- ENCABEZADO (con botón Salir, mejora de Erick) --}}
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:25px;">
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

    {{-- ALERTA --}}
    @if(session('success'))
        <div style="padding: 15px; background-color: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #22c55e;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
    <div style="padding: 15px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 15px;">
        <i class="fas fa-exclamation-circle"></i>
        {{ $errors->first('error') ?: $errors->first() }}
    </div>
    @endif

    {{-- LAYOUT PRINCIPAL --}}
    <div class="horarios-layout">

        {{-- ================= TABLA ================= --}}
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
                    @foreach($sessions as $session)

                        {{-- FILA NORMAL --}}
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                            <td>{{ $session->instructor_name }}</td>

                            {{-- ASISTENCIA --}}
                            <td class="text-center">
                                {{-- Activar / desactivar la asistencia (QR) de este horario --}}
                                <form action="{{ route('courses.sessions.toggle', [$course, $session]) }}" method="POST" style="display:inline; margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="{{ $session->attendance_enabled ? 'Asistencia activa — clic para desactivar' : 'Asistencia inactiva — clic para activar' }}"
                                            style="border:none; background:none; padding:0; cursor:pointer; font-size:1.6rem; line-height:1;">
                                        <i class="fas {{ $session->attendance_enabled ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                    </button>
                                </form>
                            </td>

                            {{-- ACCIONES --}}
                            <td class="text-center" style="display:flex; gap:6px; justify-content:center;">

                                {{-- EDITAR --}}
                                <button onclick="toggleEditRow(event, '{{ $session->id }}')"
                                        class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @php
                                    $horarioLabel = \Carbon\Carbon::parse($session->date)->format('d/m/Y') . ' · '
                                        . \Carbon\Carbon::parse($session->start_time)->format('H:i') . ' - '
                                        . \Carbon\Carbon::parse($session->end_time)->format('H:i');
                                    $grupoHorario = $session->groups->first();
                                    $totalParticipantes = $grupoHorario ? $grupoHorario->participants()->count() : 0;
                                @endphp

                                {{-- GRUPO / LISTA DE PARTICIPANTES (Propiedad → Departamento → Puesto → Anfitriones) --}}
                                <button type="button"
                                        class="btn btn-sm btn-warning"
                                        title="Configurar grupo y lista de participantes"
                                        onclick="abrirListaParticipantes('{{ $session->id }}', @js($horarioLabel))">
                                    <i class="fa-solid fa-users"></i>
                                    {{ $grupoHorario ? 'Grupo (' . $totalParticipantes . ')' : '+ Grupo' }}
                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-success"
                                        title="Agregar o quitar participantes"
                                        onclick="abrirListaParticipantes('{{ $session->id }}', @js($horarioLabel))">
                                    <i class="fa-solid fa-user-plus"></i>
                                </button>

                                <form id="delete-session-form-{{ $session->id }}"
                                      action="{{ route('courses.sessions.destroy', [$course->id, $session->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    {{-- Abre el modal de advertencia antes de eliminar --}}
                                    <button type="button" class="btn-delete-session"
                                        onclick="confirmarEliminarHorario(
                                        '{{ $session->id }}',
                                        '{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}',
                                        '{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}',
                                        '{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}'
                                        )">
                                        <i class="fa-solid fa-delete-left"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>

                        {{-- FILA EDIT --}}
                        <tr id="edit-row-{{ $session->id }}" style="display:none;" class="edit-row-active">
                            <td colspan="6">
                                <form action="{{ route('courses.sessions.update', [$course, $session]) }}" method="POST" style="display:flex; gap:10px;">
                                    @csrf
                                    @method('PUT')

                                    <input type="date" name="date" class="form-control-custom" value="{{ $session->date }}">
                                    <input type="time" name="start_time" class="form-control-custom" value="{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}" onchange="autoCalcEditTime(this, {{ $course->hours ?? 0 }})">
                                    <input type="time" name="end_time" class="form-control-custom" value="{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}">
                                    <input type="text" name="instructor_name" class="form-control-custom" value="{{ $session->instructor_name }}" placeholder="Instructor" maxlength="255" required>

                                    <button class="btn btn-primary btn-sm">OK</button>
                                </form>
                            </td>
                        </tr>

                    @endforeach

                    @if(count($sessions) === 0)
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

        {{-- ================= FORMULARIO ================= --}}

        <div class="horarios-form-section">

            <div class="card-custom">

                {{-- HEADER --}}
                <div class="header-accent-blue">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nuevo Horario</span>
                </div>

                {{-- BODY --}}
                <div style="padding:20px;">

                    <form action="{{ route('courses.sessions.store', $course) }}" method="POST">
                        @csrf

                        <div class="form-group-custom">
                            <label class="label-custom">Fecha de la sesión</label>
                            <input type="date" name="date" class="form-control-custom" value="{{ old('date') }}" required oninvalid="this.setCustomValidity('Por favor, selecciona una fecha.')" oninput="this.setCustomValidity('')">
                        </div>

                        <div style="display:flex; gap:10px;">
                            <div class="form-group-custom" style="flex:1;">
                                <label class="label-custom">Hora inicio</label>
                                <input type="time" name="start_time" id="start_time" class="form-control-custom" value="{{ old('start_time') }}" required oninvalid="this.setCustomValidity('Falta la hora de inicio.')" oninput="this.setCustomValidity('')">
                            </div>

                            <div class="form-group-custom" style="flex:1;">
                                <label class="label-custom">Hora fin</label>
                                <input type="time" name="end_time" id="end_time" class="form-control-custom" value="{{ old('end_time') }}" required oninvalid="this.setCustomValidity('Falta la hora de término.')" oninput="this.setCustomValidity('')">
                            </div>
                        </div>

                        <div class="form-group-custom">
                            <label class="label-custom">Instructor</label>
                            <input type="text"
                                name="instructor_name"
                                class="form-control-custom"
                                placeholder="Nombre del instructor"
                                maxlength="255"
                                value="{{ old('instructor_name') }}"
                                required
                                oninvalid="this.setCustomValidity('Por favor, ingresa el nombre del instructor.')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-save"></i>
                            Registrar Horario
                        </button>

                    </form>

                </div>

            </div>
        </div>

    </div>
 </div>
{{-- MODAL: ADVERTENCIA AL ELIMINAR HORARIO (no depende de Bootstrap JS) --}}
<div id="deleteSessionModal" class="umi-confirm-overlay" role="dialog" aria-modal="true"
     aria-labelledby="deleteSessionModalTitle" aria-hidden="true">
    <div class="umi-confirm-box">
        <div class="umi-confirm-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h5 id="deleteSessionModalTitle">¿Eliminar este horario?</h5>
        <p class="umi-confirm-info"><strong id="deleteSessionInfo"></strong></p>
        <p class="umi-confirm-text">
            Se eliminará el horario y su información asociada. Esta acción no se puede deshacer.
        </p>
        <div class="umi-confirm-actions">
            <button type="button" id="btnCancelDeleteSession" class="umi-confirm-cancel">Cancelar</button>
            <button type="button" id="btnConfirmDeleteSession" class="umi-confirm-danger">Sí, eliminar</button>
        </div>
    </div>
</div>

<style>
    .umi-confirm-overlay {
        position: fixed; inset: 0; z-index: 2000;
        display: none; align-items: center; justify-content: center;
        background: rgba(13, 34, 64, .45); padding: 16px;
    }
    .umi-confirm-overlay.is-open { display: flex; }
    .umi-confirm-box {
        width: 100%; max-width: 420px; background: #fff; border-radius: 12px;
        padding: 28px; text-align: center; box-shadow: 0 12px 35px rgba(13, 34, 64, .25);
    }
    .umi-confirm-icon { font-size: 42px; color: #E74C3C; line-height: 1; margin-bottom: 12px; }
    .umi-confirm-box h5 { font-weight: 700; color: #0d2240; margin: 0 0 10px; font-size: 1.2rem; }
    .umi-confirm-info { margin: 0 0 6px; color: #444; }
    .umi-confirm-text { margin: 0; color: #666; font-size: .92rem; }
    .umi-confirm-actions { display: flex; justify-content: center; gap: 10px; margin-top: 22px; }
    .umi-confirm-actions button {
        min-width: 120px; padding: 9px 16px; border-radius: 7px; font-weight: 600; cursor: pointer;
    }
    .umi-confirm-cancel { background: #fff; color: #0d2240; border: 1px solid #0d2240; }
    .umi-confirm-danger { background: #E74C3C; color: #fff; border: 1px solid #E74C3C; }
    .umi-confirm-danger:disabled { opacity: .7; cursor: wait; }
</style>

<script>
    (function () {
        const overlay = document.getElementById('deleteSessionModal');
        const info = document.getElementById('deleteSessionInfo');
        const btnConfirm = document.getElementById('btnConfirmDeleteSession');
        const btnCancel = document.getElementById('btnCancelDeleteSession');
        let horarioPorEliminar = null;

        function cerrar() {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            horarioPorEliminar = null;
        }

        window.confirmarEliminarHorario = function (sessionId, fecha, inicio, fin) {
            horarioPorEliminar = sessionId;
            info.textContent = fecha + '  ·  ' + inicio + ' - ' + fin;
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
            const form = horarioPorEliminar && document.getElementById('delete-session-form-' + horarioPorEliminar);
            if (!form) return;
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Eliminando...';
            form.submit();
        });
    })();
</script>

{{-- ===================================================================
     MODAL: LISTA DE PARTICIPANTES DEL HORARIO (sin Bootstrap)
     Propiedad:   Departamentos → Puestos → Anfitriones
     Universidad: Clasificaciones → Carreras → Materias → Alumnos
     Solo aparece lo registrado en la unidad elegida. Se puede elegir uno o
     varios de cada nivel; al elegir un nivel se marcan sus hijos y las
     personas que coinciden, y se puede desmarcar a quien no vaya.
     =================================================================== --}}
<div id="groupModal" class="umi-gm-overlay" role="dialog" aria-modal="true" aria-labelledby="groupModalTitle" aria-hidden="true">
    <div class="umi-gm-box">
        <div class="umi-gm-header">
            <div>
                <h5 id="groupModalTitle">Lista de participantes</h5>
                <span id="groupModalHorario" class="umi-gm-sub"></span>
            </div>
            <button type="button" class="umi-gm-close" id="groupModalClose" aria-label="Cerrar">&times;</button>
        </div>

        <div class="umi-gm-body">
            <div id="groupModalLoading" class="umi-gm-loading">Cargando información…</div>
            <div id="groupModalError" class="umi-gm-error" style="display:none;"></div>

            <div id="groupModalContent" style="display:none;">
                <div class="umi-gm-row">
                    <label for="gmProperty" id="gmPropertyLabel">Propiedad</label>
                    <select id="gmProperty" class="umi-gm-input"></select>
                </div>

                <div id="gmColumns" class="umi-gm-columns"></div>

                <div class="umi-gm-settings">
                    <div class="umi-gm-row">
                        <label for="gmType">Tipo de grupo</label>
                        <select id="gmType" class="umi-gm-input">
                            <option value="abierto">Abierto</option>
                            <option value="cerrado">Cerrado</option>
                        </select>
                    </div>
                    <div class="umi-gm-row">
                        <label for="gmMin">Mínimo</label>
                        <input type="number" id="gmMin" class="umi-gm-input" min="1">
                    </div>
                    <div class="umi-gm-row">
                        <label for="gmMax">Máximo</label>
                        <input type="number" id="gmMax" class="umi-gm-input" min="1">
                    </div>
                </div>

                <p id="gmCounter" class="umi-gm-counter"></p>
            </div>
        </div>

        <div class="umi-gm-footer">
            <button type="button" class="umi-gm-btn-outline" id="groupModalCancel">Cancelar</button>
            <button type="button" class="umi-gm-btn" id="groupModalSave" disabled>Guardar lista</button>
        </div>
    </div>
</div>

<style>
    .umi-gm-overlay { position: fixed; inset: 0; z-index: 2000; display: none; align-items: center; justify-content: center; background: rgba(13, 34, 64, .45); padding: 16px; }
    .umi-gm-overlay.is-open { display: flex; }
    .umi-gm-box { width: 100%; max-width: 1180px; max-height: calc(100vh - 32px); display: flex; flex-direction: column; background: #fff; border-radius: 12px; box-shadow: 0 12px 35px rgba(13, 34, 64, .25); }
    .umi-gm-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 20px 24px 14px; border-bottom: 1px solid #e5e9f0; }
    .umi-gm-header h5 { margin: 0; color: #0d2240; font-weight: 700; font-size: 1.2rem; }
    .umi-gm-sub { display: block; margin-top: 2px; color: #223f70; font-weight: 600; font-size: .92rem; }
    .umi-gm-close { border: none; background: none; font-size: 28px; line-height: 1; color: #888; cursor: pointer; }
    .umi-gm-body { padding: 18px 24px; overflow-y: auto; }
    .umi-gm-loading, .umi-gm-error { padding: 14px; border-radius: 8px; text-align: center; }
    .umi-gm-loading { color: #555; }
    .umi-gm-error { background: #fee2e2; color: #991b1b; }
    .umi-gm-row { display: flex; flex-direction: column; gap: 6px; }
    .umi-gm-row label { font-weight: 600; color: #0d2240; margin: 0; }
    .umi-gm-input { width: 100%; min-height: 42px; padding: 9px 12px; border: 1px solid #cfd6e6; border-radius: 8px; background: #fff; box-sizing: border-box; }
    .umi-gm-input:focus { outline: none; border-color: #223f70; box-shadow: 0 0 0 2px rgba(34, 63, 112, .12); }
    .umi-gm-columns { display: grid; grid-template-columns: repeat(var(--gm-cols, 3), minmax(0, 1fr)); gap: 14px; margin-top: 16px; }
    .umi-gm-panel { border: 1px solid #e2e5e9; border-radius: 8px; padding: 10px; display: flex; flex-direction: column; min-width: 0; }
    .umi-gm-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; color: #0d2240; }
    .umi-gm-links { white-space: nowrap; }
    .umi-gm-links button { border: none; background: none; color: #223f70; font-size: .82rem; font-weight: 600; cursor: pointer; padding: 0 2px; }
    .umi-gm-links button:hover { text-decoration: underline; }
    .umi-gm-search { min-height: 36px; padding: 6px 10px; margin-bottom: 8px; }
    .umi-gm-list { height: 260px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; }
    .umi-gm-item { display: flex; align-items: flex-start; gap: 8px; margin: 0; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-size: .92rem; color: #333; }
    .umi-gm-item:hover { background: #f3f6fb; }
    .umi-gm-item input { margin-top: 3px; width: 16px; height: 16px; flex: 0 0 16px; }
    .umi-gm-item small { display: block; color: #6b7280; font-size: .78rem; }
    .umi-gm-group-title { margin: 6px 0 2px; font-size: .78rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .02em; }
    .umi-gm-empty { color: #888; font-size: .88rem; padding: 8px; }
    .umi-gm-settings { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px; margin-top: 16px; }
    .umi-gm-counter { margin: 14px 0 0; font-weight: 600; color: #223f70; }
    .umi-gm-counter.is-warning { color: #b45309; }
    .umi-gm-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 24px 20px; border-top: 1px solid #e5e9f0; }
    .umi-gm-btn, .umi-gm-btn-outline { min-width: 140px; padding: 10px 16px; border-radius: 7px; font-weight: 600; cursor: pointer; border: 1px solid #0d2240; }
    .umi-gm-btn { background: #0d2240; color: #fff; }
    .umi-gm-btn:disabled { opacity: .6; cursor: not-allowed; }
    .umi-gm-btn-outline { background: #fff; color: #0d2240; }
    @media (max-width: 960px) {
        .umi-gm-columns { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .umi-gm-columns { grid-template-columns: 1fr; }
        .umi-gm-list { height: 180px; }
        .umi-gm-settings { grid-template-columns: 1fr 1fr; }
        .umi-gm-settings .umi-gm-row:first-child { grid-column: 1 / -1; }
        .umi-gm-footer { flex-direction: column-reverse; }
        .umi-gm-btn, .umi-gm-btn-outline { width: 100%; }
    }
</style>

<script>
(function () {
    const overlay = document.getElementById('groupModal');
    if (!overlay) return;

    const $ = (id) => document.getElementById(id);
    const el = {
        horario: $('groupModalHorario'), loading: $('groupModalLoading'), error: $('groupModalError'),
        content: $('groupModalContent'), property: $('gmProperty'), propertyLabel: $('gmPropertyLabel'),
        columns: $('gmColumns'), type: $('gmType'), min: $('gmMin'), max: $('gmMax'), counter: $('gmCounter'),
        save: $('groupModalSave'), cancel: $('groupModalCancel'), close: $('groupModalClose'),
    };
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
    const storeUrl = @json(route('sessions.group.store'));

    let sessionId = null;
    let catalog = null;
    let selected = {};          // { nivel: Set(ids), people: Set(ids) }
    let seenPeople = new Set(); // personas ya mostradas (para no volver a marcar a quien se desmarcó)
    let searchTerm = '';

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const norm = (v) => String(v ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

    function open(id, label) {
        sessionId = id;
        el.horario.textContent = label || '';
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        load(null, true);
    }

    function close() {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
    }

    async function load(institutionId, useSaved) {
        el.loading.style.display = 'block';
        el.error.style.display = 'none';
        el.content.style.display = 'none';
        el.save.disabled = true;

        try {
            const url = new URL(`/sessions/${sessionId}/participants-catalog`, window.location.origin);
            if (institutionId) url.searchParams.set('institution_id', institutionId);
            const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            catalog = await res.json();
        } catch (e) {
            console.error('No se pudo cargar la lista de participantes:', e);
            el.loading.style.display = 'none';
            el.error.textContent = 'No se pudo cargar la información. Intenta de nuevo.';
            el.error.style.display = 'block';
            return;
        }

        selected = { people: new Set() };
        catalog.levels.forEach((lv) => { selected[lv.key] = new Set(); });
        seenPeople = new Set();
        searchTerm = '';

        const saved = catalog.group;
        const sameUnitAsSaved = saved && Number(saved.institution_id) === Number(catalog.institution_id);

        if (useSaved) {
            el.type.value = saved?.type || 'abierto';
            el.min.value = saved?.min_participants ?? '';
            el.max.value = saved?.max_participants ?? '';
        }
        if (sameUnitAsSaved) {
            catalog.levels.forEach((lv) => (saved.selections?.[lv.key] || []).forEach((id) => selected[lv.key].add(Number(id))));
            (saved.participants || []).forEach((id) => selected.people.add(Number(id)));
        }

        el.propertyLabel.textContent = catalog.unit_label || 'Propiedad';
        el.property.innerHTML = '';
        if (!catalog.properties.length) el.property.add(new Option('No tienes unidades asignadas', ''));
        catalog.properties.forEach((p) => el.property.add(new Option(p.name, p.id, false, Number(p.id) === Number(catalog.institution_id))));

        buildColumns();
        // Con un grupo guardado, las personas visibles ya se "vieron": respetar a quien se desmarcó
        if (sameUnitAsSaved) visiblePeople().forEach((p) => seenPeople.add(p.id));

        el.loading.style.display = 'none';
        el.content.style.display = 'block';
        render();
    }

    function buildColumns() {
        const cols = catalog.levels.map((lv) => ({ key: lv.key, title: lv.title }))
            .concat([{ key: 'people', title: catalog.people_title || 'Participantes' }]);
        el.columns.style.setProperty('--gm-cols', cols.length);
        el.columns.innerHTML = cols.map((c) => `
            <div class="umi-gm-panel">
                <div class="umi-gm-panel-head">
                    <strong>${esc(c.title)}</strong>
                    <span class="umi-gm-links">
                        <button type="button" data-gm-all="${c.key}">Todos</button>
                        <button type="button" data-gm-none="${c.key}">Ninguno</button>
                    </span>
                </div>
                ${c.key === 'people' ? '<input type="search" id="gmSearch" class="umi-gm-input umi-gm-search" placeholder="Buscar por nombre o RFC">' : ''}
                <div class="umi-gm-list" data-gm-list="${c.key}"></div>
            </div>`).join('');
        $('gmSearch').addEventListener('input', (e) => { searchTerm = norm(e.target.value); render(); });
    }

    // Elementos de cada nivel cuyo padre está elegido (el primer nivel siempre se muestra)
    function availableItems(levelIndex) {
        const lv = catalog.levels[levelIndex];
        if (levelIndex === 0) return lv.items;
        const parentKey = catalog.levels[levelIndex - 1].key;
        return lv.items.filter((it) => selected[parentKey].has(Number(it.parent)));
    }

    // Personas que coinciden con todos los niveles en los que tienen datos
    function visiblePeople() {
        return catalog.people.filter((p) => catalog.levels.every((lv) => {
            const ids = p.links?.[lv.key] || [];
            return ids.length === 0 || ids.some((id) => selected[lv.key].has(Number(id)));
        }));
    }

    function listHtml(items, kind, emptyText) {
        if (!items.length) return `<div class="umi-gm-empty">${esc(emptyText)}</div>`;
        return items.map((it) => it.title
            ? `<div class="umi-gm-group-title">${esc(it.title)}</div>`
            : `<label class="umi-gm-item"${it.hidden ? ' style="display:none"' : ''}>
                   <input type="checkbox" data-kind="${kind}" value="${it.id}" ${selected[kind].has(Number(it.id)) ? 'checked' : ''}>
                   <span>${esc(it.name)}${it.sub ? `<small>${esc(it.sub)}</small>` : ''}</span>
               </label>`).join('');
    }

    function setList(kind, html) {
        const box = el.columns.querySelector(`[data-gm-list="${kind}"]`);
        const top = box.scrollTop;
        box.innerHTML = html;
        box.scrollTop = top;
    }

    function render() {
        catalog.levels.forEach((lv, i) => {
            const items = availableItems(i);
            const validIds = new Set(items.map((it) => Number(it.id)));
            [...selected[lv.key]].forEach((id) => { if (!validIds.has(id)) selected[lv.key].delete(id); });

            let rows = items;
            if (i > 0) {   // agrupar por el nivel anterior
                const parentLv = catalog.levels[i - 1];
                const parentName = Object.fromEntries(parentLv.items.map((p) => [Number(p.id), p.name]));
                const order = Object.fromEntries(availableItems(i - 1).map((p, idx) => [Number(p.id), idx]));
                rows = [];
                let last = null;
                [...items].sort((a, b) => (order[Number(a.parent)] ?? 0) - (order[Number(b.parent)] ?? 0)).forEach((it) => {
                    if (Number(it.parent) !== last) { last = Number(it.parent); rows.push({ title: parentName[last] || '' }); }
                    rows.push(it);
                });
            }
            const emptyText = i === 0 ? lv.empty : (selected[catalog.levels[i - 1].key].size ? lv.empty : `Elige ${catalog.levels[i - 1].title.toLowerCase()}.`);
            setList(lv.key, listHtml(rows, lv.key, emptyText));
        });

        // Personas: las nuevas aparecen marcadas (nivel completo); se puede desmarcar a quien no vaya
        const people = visiblePeople();
        const anyLevelSelected = catalog.levels.some((lv) => selected[lv.key].size);
        const shown = anyLevelSelected ? people : [];
        shown.forEach((p) => { if (!seenPeople.has(p.id)) { seenPeople.add(p.id); selected.people.add(p.id); } });
        const shownIds = new Set(shown.map((p) => p.id));
        [...selected.people].forEach((id) => { if (!shownIds.has(id)) selected.people.delete(id); });

        const lastLevel = catalog.levels[catalog.levels.length - 1];
        setList('people', listHtml(
            shown.map((p) => ({ id: p.id, name: p.name, sub: p.sub, hidden: searchTerm && !norm(p.name + ' ' + (p.sub || '')).includes(searchTerm) })),
            'people',
            anyLevelSelected ? `No hay ${(catalog.people_title || 'participantes').toLowerCase()} registrados con esa selección.` : `Elige ${lastLevel.title.toLowerCase()}.`
        ));

        updateCounter(shown.length);
    }

    function updateCounter(total) {
        if (total === undefined) total = catalog.levels.some((lv) => selected[lv.key].size) ? visiblePeople().length : 0;
        const count = selected.people.size;
        const max = parseInt(el.max.value, 10);
        const who = (catalog.people_title || 'participantes').toLowerCase();
        let text = `${count} de ${total} ${who} seleccionado(s)`;
        const over = Number.isFinite(max) && count > max;
        if (over) text += ` — supera el máximo de ${max}`;
        el.counter.textContent = text;
        el.counter.classList.toggle('is-warning', over);
        el.save.disabled = !catalog || !catalog.properties.length;
    }

    // Marcar/desmarcar en cascada: un elemento arrastra a todos sus descendientes
    function cascade(levelIndex, ids, checked) {
        for (let i = levelIndex + 1; i < catalog.levels.length && ids.length; i++) {
            const lv = catalog.levels[i];
            const children = lv.items.filter((it) => ids.includes(Number(it.parent))).map((it) => Number(it.id));
            children.forEach((id) => checked ? selected[lv.key].add(id) : selected[lv.key].delete(id));
            ids = children;
        }
    }

    overlay.addEventListener('change', (e) => {
        const cb = e.target.closest('input[type="checkbox"][data-kind]');
        if (!cb) return;
        const kind = cb.dataset.kind;
        const id = Number(cb.value);
        if (cb.checked) selected[kind].add(id); else selected[kind].delete(id);
        if (kind === 'people') { updateCounter(); return; }
        cascade(catalog.levels.findIndex((lv) => lv.key === kind), [id], cb.checked);
        render();
    });

    overlay.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-gm-all],[data-gm-none]');
        if (!btn) return;
        const all = btn.hasAttribute('data-gm-all');
        const kind = all ? btn.dataset.gmAll : btn.dataset.gmNone;
        if (kind === 'people') {
            if (all) visiblePeople().forEach((p) => selected.people.add(p.id)); else selected.people.clear();
            render();
            return;
        }
        const idx = catalog.levels.findIndex((lv) => lv.key === kind);
        const ids = availableItems(idx).map((it) => Number(it.id));
        ids.forEach((id) => all ? selected[kind].add(id) : selected[kind].delete(id));
        cascade(idx, ids, all);
        render();
    });

    el.max.addEventListener('input', () => updateCounter());
    el.property.addEventListener('change', () => load(el.property.value, false));
    el.cancel.addEventListener('click', close);
    el.close.addEventListener('click', close);
    overlay.addEventListener('mousedown', (e) => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && overlay.classList.contains('is-open')) close(); });

    el.save.addEventListener('click', async () => {
        const users = [...selected.people];
        const who = (catalog.people_title || 'participantes').toLowerCase();
        if (!users.length && !confirm(`No hay ${who} seleccionados. ¿Guardar el grupo con la lista vacía?`)) return;

        el.save.disabled = true;
        el.save.textContent = 'Guardando…';
        el.error.style.display = 'none';

        const selections = {};
        catalog.levels.forEach((lv) => { selections[lv.key] = [...selected[lv.key]]; });

        try {
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                credentials: 'same-origin',
                body: JSON.stringify({
                    session_id: sessionId,
                    institution_id: el.property.value || null,
                    selections: selections,
                    users: users,
                    type: el.type.value,
                    min_participants: el.min.value || null,
                    max_participants: el.max.value || null,
                }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const first = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'HTTP ' + res.status);
                throw new Error(first);
            }
            window.location.reload();
        } catch (e) {
            el.error.textContent = 'No se pudo guardar: ' + e.message;
            el.error.style.display = 'block';
            el.save.disabled = false;
            el.save.textContent = 'Guardar lista';
        }
    });

    window.abrirListaParticipantes = open;
})();
</script>
</div>


<script>
// Al editar un horario, recalcular la hora fin con las horas del curso (mejora de Erick)
window.autoCalcEditTime = function (startInput, courseHours) {
    if (!startInput.value) return;
    const endInput = startInput.closest('form')?.querySelector('input[name="end_time"]');
    if (!endInput) return;
    const [h, m] = startInput.value.split(':').map(Number);
    const date = new Date();
    date.setHours(h, m, 0, 0);
    date.setHours(date.getHours() + Number(courseHours || 0));
    endInput.value = String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0');
};
</script>

<script>
// EDIT ROW
function toggleEditRow(e, id) {
    e.preventDefault();
    const row = document.getElementById('edit-row-' + id);
    row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
}
</script>


<script>
// AUTO CALCULAR HORA FIN
document.addEventListener('DOMContentLoaded', function () {

    const startInput = document.getElementById('start_time');
    const endInput = document.getElementById('end_time');

    if (!startInput) return;

    startInput.addEventListener('change', function () {

        let hours = {{ $course->hours }};
        let start = this.value;

        if (!start) return;

        let [h, m] = start.split(':');

        let date = new Date();
        date.setHours(parseInt(h));
        date.setMinutes(parseInt(m));

        date.setHours(date.getHours() + hours);

        let endH = String(date.getHours()).padStart(2, '0');
        let endM = String(date.getMinutes()).padStart(2, '0');

        endInput.value = `${endH}:${endM}`;
    });
});
</script>
@endsection