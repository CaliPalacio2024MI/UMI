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

                                {{-- LISTA DE ASISTENCIA (gris): se abre aquí mismo en una ventana --}}
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Lista de asistencia"
                                        onclick="abrirListaAsistencia('{{ $session->id }}', @js($horarioLabel))">
                                    <i class="fas fa-chart-bar"></i>
                                </button>

                                {{-- ASIGNAR PARTICIPANTES (verde): Propiedad → Departamento → Puesto → Anfitriones / Clasificación → Carrera → Materia → Alumnos --}}
                                <button type="button"
                                        class="btn btn-sm btn-success"
                                        title="Asignar participantes{{ $grupoHorario ? ' (' . $totalParticipantes . ')' : '' }}"
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

        <div class="horarios-form-section" style="order:-1;">{{-- Formulario a la izquierda, igual que Periodos de virtual --}}

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
{{-- Ventanas compartidas: eliminar y asignar participantes --}}
@include('layouts.Cursos.partials.participantes', [
    'pp_entity'     => 'horario',
    'pp_catalogUrl' => url('/sessions/__ID__/participants-catalog'),
    'pp_storeUrl'   => route('sessions.group.store'),
])
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


{{-- ================= VENTANA: LISTA DE ASISTENCIA DEL HORARIO (botón gris) ================= --}}
@php
    $puedeDescargarLista = in_array(strtolower((string) session('active_role_name')), ['master', 'gerente_capacitacion', 'docente'], true);
    // Horarios del curso, para los filtros de fecha y horario de la ventana
    $horariosAsistencia = $sessions->map(function ($h) {
        return [
            'id'         => $h->id,
            'fecha'      => \Carbon\Carbon::parse($h->date)->format('Y-m-d'),
            'label'      => \Carbon\Carbon::parse($h->date)->format('d/m/Y') . ' · ' . \Carbon\Carbon::parse($h->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($h->end_time)->format('H:i'),
            'instructor' => $h->instructor_name,
        ];
    })->values();
@endphp
<div id="asisModal" class="umi-gm-overlay" role="dialog" aria-modal="true" aria-labelledby="asisModalTitle" aria-hidden="true">
    <div class="umi-gm-box">
        <div class="umi-gm-header">
            <div>
                <h5 id="asisModalTitle">Lista de asistencia</h5>
                <span id="asisModalHorario" class="umi-gm-sub"></span>
            </div>
            <button type="button" class="umi-gm-close" onclick="cerrarListaAsistencia()" aria-label="Cerrar">&times;</button>
        </div>

        <div class="umi-gm-body">
            <div id="asisLoading" class="umi-gm-loading">Cargando información…</div>
            <div id="asisError" class="umi-gm-error" style="display:none;"></div>

            <div id="asisContent" style="display:none;">
                <div class="asis-stats">
                    <div class="asis-card">
                        <span class="asis-card-label" style="color:#2f4b7c;"><i class="fa-solid fa-users"></i> Total inscritos</span>
                        <strong class="asis-card-value" id="asisTotal">0</strong>
                    </div>
                    <div class="asis-card">
                        <span class="asis-card-label" style="color:#1e8449;"><i class="fa-solid fa-user-check"></i> Asistieron</span>
                        <strong class="asis-card-value" id="asisPresentes">0</strong>
                    </div>
                    <div class="asis-card">
                        <span class="asis-card-label" style="color:#b7791f;"><i class="fa-solid fa-percent"></i> Porcentaje de asistencia</span>
                        <strong class="asis-card-value" id="asisPorcentaje">0%</strong>
                        <div class="asis-bar"><div id="asisBarra" style="width:0%;"></div></div>
                    </div>
                </div>

                <div class="asis-filters">
                    <div class="asis-filter">
                        <label for="asisBuscar">Buscar por nombre o RFC</label>
                        <input type="text" id="asisBuscar" class="umi-gm-input" placeholder="Escribe un nombre o RFC…">
                    </div>
                    <div class="asis-filter asis-filter-sm">
                        <label for="asisFecha">Fecha</label>
                        <input type="date" id="asisFecha" class="umi-gm-input">
                    </div>
                    <div class="asis-filter">
                        <label for="asisHorario">Horario</label>
                        <select id="asisHorario" class="umi-gm-input"></select>
                    </div>
                    @if($puedeDescargarLista)
                        <div class="asis-filter asis-filter-btn">
                            <a id="asisPdf" href="#" class="asis-btn-pdf">
                                <i class="fa-solid fa-file-pdf"></i> Descargar PDF
                            </a>
                        </div>
                    @endif
                </div>

                <div class="asis-table-title" id="asisTitulo"></div>
                <div class="asis-table-wrap">
                    <table class="table-custom-sessions asis-table">
                        <thead id="asisHead"></thead>
                        <tbody id="asisBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="umi-gm-footer">
            <button type="button" class="umi-gm-btn-outline" onclick="cerrarListaAsistencia()">Cerrar</button>
        </div>
    </div>
</div>

<style>
    .asis-stats { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:14px; margin-bottom:14px; }
    .asis-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; box-shadow:0 2px 6px rgba(0,0,0,.06); }
    .asis-card-label { display:flex; align-items:center; gap:8px; font-size:.85rem; font-weight:700; }
    .asis-card-value { display:block; font-size:1.8rem; color:#1e293b; margin-top:4px; }
    .asis-bar { height:7px; background:#edf2f7; border-radius:99px; overflow:hidden; margin-top:6px; }
    .asis-bar > div { height:100%; background:#2f4b7c; border-radius:99px; }
    .asis-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:12px 16px; margin-bottom:14px; }
    .asis-filter { flex:1 1 220px; display:flex; flex-direction:column; gap:5px; }
    .asis-filter label { font-size:.8rem; font-weight:600; color:#475569; }
    .asis-filter-sm { flex:0 1 180px; }
    .asis-filter-btn { flex:0 0 auto; }
    .asis-btn-pdf { display:inline-flex; align-items:center; gap:8px; min-height:42px; padding:0 18px; border-radius:8px; background:#2f4b7c; color:#fff !important; font-weight:700; text-decoration:none; white-space:nowrap; }
    .asis-btn-pdf:hover { background:#0d2240; }
    .asis-btn-pdf.is-disabled { opacity:.5; pointer-events:none; cursor:not-allowed; }
    .asis-table-title { font-weight:700; color:#1e293b; padding:12px 4px 10px; }
    .asis-table-wrap { max-height: 42vh; overflow:auto; border:1px solid #e2e8f0; border-radius:10px; }
    .asis-table thead th { position: sticky; top: 0; z-index: 1; }
    .asis-badge { display:inline-block; padding:4px 12px; border-radius:99px; font-size:.8rem; font-weight:700; white-space:nowrap; }
    .asis-badge.is-ok { background:#dcfce7; color:#166534; }
    .asis-badge.is-no { background:#fee2e2; color:#991b1b; }
    .asis-empty { padding:30px !important; text-align:center; color:#94a3b8; }
    @media (max-width: 768px) { .asis-stats { grid-template-columns: 1fr; } }
</style>

<script>
(function () {
    const overlay = document.getElementById('asisModal');
    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let filas = [];
    let universidad = false;
    // Horarios del curso, para los filtros de fecha y horario
    const horarios = @json($horariosAsistencia);

    function llenarHorarios(fecha, seleccionado) {
        const sel = $('asisHorario');
        const lista = horarios.filter((h) => !fecha || h.fecha === fecha);
        sel.innerHTML = lista.length
            ? lista.map((h) => `<option value="${h.id}" ${String(h.id) === String(seleccionado) ? 'selected' : ''}>${esc(h.label)}</option>`).join('')
            : '<option value="">Sin horarios en esa fecha</option>';
        return lista;
    }

    window.abrirListaAsistencia = async function (sessionId, label) {
        const h = horarios.find((x) => String(x.id) === String(sessionId));
        $('asisModalHorario').textContent = label || '';
        $('asisTitulo').textContent = h ? `Horario del ${h.label}${h.instructor ? ' · Instructor: ' + h.instructor : ''}` : '';
        $('asisFecha').value = h ? h.fecha : '';
        llenarHorarios(h ? h.fecha : '', sessionId);
        $('asisBuscar').value = '';
        $('asisLoading').style.display = 'block';
        $('asisError').style.display = 'none';
        $('asisContent').style.display = 'none';
        if ($('asisPdf')) $('asisPdf').classList.add('is-disabled');
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');

        try {
            const res = await fetch(`/sessions/${sessionId}/asistencia`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            filas = data.rows || [];
            universidad = !!data.universidad;
            $('asisTotal').textContent = data.total;
            $('asisPresentes').textContent = data.asistieron;
            $('asisPorcentaje').textContent = data.porcentaje + '%';
            $('asisBarra').style.width = Math.min(100, data.porcentaje) + '%';
            if ($('asisPdf')) {
                // Siempre visible para quien puede descargar; desactivado si el horario no tiene participantes
                $('asisPdf').href = filas.length ? data.pdf_url : '#';
                $('asisPdf').classList.toggle('is-disabled', !filas.length);
                $('asisPdf').title = filas.length ? 'Descargar la lista de asistencia en PDF' : 'Asigna participantes (botón verde) para poder descargar la lista';
            }
            pintar('');
            $('asisLoading').style.display = 'none';
            $('asisContent').style.display = 'block';
        } catch (e) {
            console.error('Lista de asistencia:', e);
            $('asisLoading').style.display = 'none';
            $('asisError').textContent = 'No se pudo cargar la lista de asistencia. Intenta de nuevo.';
            $('asisError').style.display = 'block';
        }
    };

    function pintar(q) {
        const cols = universidad ? ['#', 'Nombre completo', 'RFC', 'Carrera', 'Fecha y hora', 'Estado']
                                 : ['#', 'Nombre completo', 'RFC', 'Departamento', 'Puesto', 'Fecha y hora', 'Estado'];
        $('asisHead').innerHTML = '<tr>' + cols.map((c) => `<th>${c}</th>`).join('') + '</tr>';
        const lista = filas.filter((r) => !q || (r.nombre + ' ' + r.rfc).toLowerCase().includes(q));
        if (!filas.length) {
            $('asisBody').innerHTML = `<tr><td colspan="${cols.length}" class="asis-empty">Este horario aún no tiene participantes asignados (botón verde).</td></tr>`;
            return;
        }
        if (!lista.length) {
            $('asisBody').innerHTML = `<tr><td colspan="${cols.length}" class="asis-empty">No hay coincidencias con la búsqueda.</td></tr>`;
            return;
        }
        $('asisBody').innerHTML = lista.map((r, i) => `<tr>
            <td>${i + 1}</td>
            <td>${esc(r.nombre)}</td>
            <td>${esc(r.rfc)}</td>
            <td>${esc(r.area)}</td>
            ${universidad ? '' : `<td>${esc(r.puesto)}</td>`}
            <td>${r.hora ? esc(r.hora) : '—'}</td>
            <td><span class="asis-badge ${r.asistio ? 'is-ok' : 'is-no'}">${r.asistio ? 'Asistió' : 'No asistió'}</span></td>
        </tr>`).join('');
    }

    $('asisBuscar').addEventListener('input', function () { pintar(this.value.trim().toLowerCase()); });

    // Cambiar de horario dentro de la misma ventana
    $('asisHorario').addEventListener('change', function () {
        const h = horarios.find((x) => String(x.id) === this.value);
        if (h) abrirListaAsistencia(h.id, h.label);
    });

    // Fecha: deja solo los horarios de ese día y abre el primero
    $('asisFecha').addEventListener('change', function () {
        const lista = llenarHorarios(this.value, null);
        if (lista.length) abrirListaAsistencia(lista[0].id, lista[0].label);
    });

    window.cerrarListaAsistencia = function () {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
    };
    overlay.addEventListener('mousedown', (e) => { if (e.target === overlay) cerrarListaAsistencia(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && overlay.classList.contains('is-open')) cerrarListaAsistencia(); });
})();
</script>

@endsection