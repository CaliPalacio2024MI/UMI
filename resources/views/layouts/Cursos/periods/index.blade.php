@extends('layouts.app')

@section('title', 'Períodos - ' . $course->title)

@vite(['resources/css/Cursos/horarios.css'])

@section('content')
<div class="container-fluid horarios-view">
    
    {{-- ENCABEZADO --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">
                Períodos: {{ $course->title }}
            </h1>
            <p style="color: #64748b;">
                Gestión de períodos y acceso de usuarios
            </p>
        </div>
        <a href="{{ route('Cursos.index') }}" class="btn-salir">
            <i class="fas fa-arrow-left"></i>
            Salir
        </a>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div style="padding: 15px; background-color: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #22c55e;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
    <div style="padding: 15px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 15px;">
        <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
    </div>
    @endif

    {{-- LAYOUT PRINCIPAL (Dos columnas) --}}
    <div class="horarios-layout">

        {{-- ================= IZQUIERDA: FORMULARIO ================= --}}
        <div class="horarios-form-section">
            <div class="card-custom">
                <div class="header-accent-blue">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Nuevo Período</span>
                </div>

                <div style="padding:20px;">
                    <form action="{{ route('courses.periods.store', $course->id) }}" method="POST">
                        @csrf
                        <div class="form-group-custom" style="margin-bottom: 15px;">
                            <label class="label-custom">Fecha Inicio</label>
                            <input type="date" name="start_date" min="{{ now()->format('Y-m-d') }}" class="form-control-custom" required>
                        </div>
                        
                        <div class="form-group-custom" style="margin-bottom: 20px;">
                            <label class="label-custom">Fecha Fin</label>
                            <input type="date" name="end_date" min="{{ now()->format('Y-m-d') }}" class="form-control-custom" required>
                        </div>

                        <button type="submit" class="btn-primary-custom" style="width: 100%;">
                            <i class="fas fa-save"></i>
                            Crear Período
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= DERECHA: TABLA ================= --}}
        <div class="horarios-tabla-section">
            <div class="card-custom">
                <div class="card-header-custom">
                    <i class="fas fa-list-ul"></i>
                    <strong>Períodos Registrados</strong>
                </div>

                <div class="table-responsive">
                    <table class="table-custom-sessions">
                        <thead>
                            <tr>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($periods) > 0)
                                @foreach($periods as $period)
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
                                    <tr>
                                        <td>{{ $period->start_date->format('d/m/Y') }}</td>
                                        <td>{{ $period->end_date->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <span style="background: {{ $color }}20; color: {{ $color }}; padding: 4px 10px; border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                {{ $icon }} {{ $estado }}
                                            </span>
                                        </td>
                                        <td class="text-center" style="display:flex; gap:6px; justify-content:center;">
                                            
                                            {{-- Botón Editar --}}
                                            <button onclick="toggleEditRow(event, '{{ $period->id }}')" class="btn btn-sm btn-outline-primary" title="Editar Período">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            @php
                                                $periodoLabel = $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y');
                                            @endphp

                                            {{-- Lista de asistencia (gris): se abre aquí mismo, igual que en Horarios de presencial --}}
                                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Lista de asistencia"
                                                    onclick="abrirListaAsistencia('{{ $period->id }}', @js($periodoLabel), true)">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>

                                            {{-- Asignar participantes (verde): mismos filtros que en Horarios --}}
                                            <button type="button" class="btn btn-sm btn-success" title="Asignar participantes"
                                                    onclick="abrirListaParticipantes('{{ $period->id }}', @js($periodoLabel))">
                                                <i class="fa-solid fa-user-plus"></i>
                                            </button>
                                            
                                            {{-- Botón Eliminar --}}
                                            <form action="{{ route('courses.periods.destroy', [$course->id, $period->id]) }}" method="POST" onsubmit="return confirm('¿Eliminar este período? Los usuarios asignados perderán acceso.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete-session" title="Eliminar">
                                                    <i class="fa-solid fa-delete-left"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- FILA OCULTA PARA EDITAR --}}
                                    <tr id="edit-row-{{ $period->id }}" style="display:none;" class="edit-row-active">
                                        <td colspan="4">
                                            <form action="{{ route('courses.periods.update', [$course->id, $period->id]) }}" method="POST" style="display:flex; gap:10px; align-items:center;">
                                                @csrf
                                                @method('PUT')
                                                <input type="date" name="start_date" class="form-control-custom" value="{{ $period->start_date->format('Y-m-d') }}" required>
                                                <input type="date" name="end_date" class="form-control-custom" value="{{ $period->end_date->format('Y-m-d') }}" required>
                                                <button type="submit" class="btn btn-primary btn-sm">OK</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" style="padding: 40px; text-align: center; color: #999;">
                                        <div style="font-size: 2.5em; margin-bottom: 10px;">📅</div>
                                        <div style="font-size: 1.1em;">No hay períodos creados aún.</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Asignar Usuarios --}}
<div id="usersModal" style="display: none; position: fixed; top: 0; right: 0; width: 450px; height: 100vh; background: white; box-shadow: -2px 0 15px rgba(0,0,0,0.3); z-index: 9999; overflow-y: auto;">
    <div style="padding: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 15px;">
            <div>
                <h3 style="margin: 0; color: #333;">👥 Asignar Usuarios</h3>
                <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #666;" id="periodRange"></p>
            </div>
            <button onclick="closeUsersModal()" style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: #999;">✕</button>
        </div>
        <div style="margin-bottom: 20px;">
            <input type="text" id="searchUsers" placeholder="🔍 Buscar usuario..." onkeyup="filterUsers()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
        </div>
        <div id="usersLoading" style="text-align: center; padding: 40px; color: #999;">
            <div style="font-size: 2em; margin-bottom: 10px;">⏳</div>
            <div>Cargando usuarios...</div>
        </div>
        <div id="usersList" style="display: none;"></div>
    </div>
</div>

<div id="modalOverlay" onclick="closeUsersModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background: rgba(0,0,0,0.5); z-index: 9998;"></div>

<script>
// Lógica de Edición Rápida (Toggle)
function toggleEditRow(e, id) {
    e.preventDefault();
    const row = document.getElementById('edit-row-' + id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}
</script>

{{-- Ventanas compartidas con Horarios: eliminar y asignar participantes --}}
@include('layouts.Cursos.partials.participantes', [
    'pp_entity'     => 'período',
    'pp_catalogUrl' => url('/cursos/' . $course->id . '/periodos/__ID__/participantes-catalogo'),
    'pp_storeUrl'   => url('/cursos/' . $course->id . '/periodos/__ID__/participantes'),
])

{{-- ================= VENTANA: LISTA DE ASISTENCIA DEL PERÍODO (botón gris, igual que en presencial) ================= --}}
@php
    $puedeDescargarLista = in_array(strtolower((string) session('active_role_name')), ['master', 'gerente_capacitacion', 'docente'], true);
    $periodosAsistencia = collect($periods)->map(function ($pp) {
        return [
            'id'     => $pp->id,
            'inicio' => $pp->start_date->format('Y-m-d'),
            'fin'    => $pp->end_date->format('Y-m-d'),
            'label'  => $pp->start_date->format('d/m/Y') . ' - ' . $pp->end_date->format('d/m/Y'),
        ];
    })->values();
@endphp
<div id="asisModal" class="umi-gm-overlay" role="dialog" aria-modal="true" aria-labelledby="asisModalTitle" aria-hidden="true">
    <div class="umi-gm-box">
        <div class="umi-gm-header">
            <div>
                <h5 id="asisModalTitle">Lista de asistencia</h5>
                <span id="asisModalPeriodo" class="umi-gm-sub"></span>
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
                        <span class="asis-card-label" style="color:#1e8449;"><i class="fa-solid fa-circle-check"></i> Completados</span>
                        <strong class="asis-card-value" id="asisCompletados">0</strong>
                    </div>
                    <div class="asis-card">
                        <span class="asis-card-label" style="color:#b7791f;"><i class="fa-solid fa-hourglass-half"></i> En progreso</span>
                        <strong class="asis-card-value" id="asisProgreso">0</strong>
                    </div>
                </div>

                <div class="asis-filters">
                    <div class="asis-filter">
                        <label for="asisBuscar">Buscar por nombre o RFC</label>
                        <input type="text" id="asisBuscar" class="umi-gm-input" placeholder="Escribe un nombre o RFC…">
                    </div>
                    <div class="asis-filter asis-filter-sm">
                        <label for="asisDesde">Fecha inicio</label>
                        <input type="date" id="asisDesde" class="umi-gm-input">
                    </div>
                    <div class="asis-filter asis-filter-sm">
                        <label for="asisHasta">Fecha fin</label>
                        <input type="date" id="asisHasta" class="umi-gm-input">
                    </div>
                    <div class="asis-filter">
                        <label for="asisPeriodo">Período</label>
                        <select id="asisPeriodo" class="umi-gm-input"></select>
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
    .asis-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:12px 16px; margin-bottom:14px; }
    .asis-filter { flex:1 1 200px; display:flex; flex-direction:column; gap:5px; }
    .asis-filter label { font-size:.8rem; font-weight:600; color:#475569; }
    .asis-filter-sm { flex:0 1 165px; }
    .asis-filter-btn { flex:0 0 auto; }
    .asis-btn-pdf { display:inline-flex; align-items:center; gap:8px; min-height:42px; padding:0 18px; border-radius:8px; background:#2f4b7c; color:#fff !important; font-weight:700; text-decoration:none; white-space:nowrap; }
    .asis-btn-pdf:hover { background:#0d2240; }
    .asis-btn-pdf.is-disabled { opacity:.5; pointer-events:none; cursor:not-allowed; }
    .asis-table-title { font-weight:700; color:#1e293b; padding:12px 4px 10px; }
    .asis-table-wrap { max-height: 42vh; overflow:auto; border:1px solid #e2e8f0; border-radius:10px; }
    .asis-table thead th { position: sticky; top: 0; z-index: 1; }
    .asis-badge { display:inline-block; padding:4px 12px; border-radius:99px; font-size:.8rem; font-weight:700; white-space:nowrap; }
    .asis-badge.is-ok { background:#dcfce7; color:#166534; }
    .asis-badge.is-mid { background:#fef3c7; color:#92400e; }
    .asis-badge.is-no { background:#fee2e2; color:#991b1b; }
    .asis-prog { display:flex; align-items:center; gap:8px; min-width:110px; }
    .asis-prog-bar { flex:1; height:7px; background:#edf2f7; border-radius:99px; overflow:hidden; }
    .asis-prog-bar > div { height:100%; background:#2f4b7c; }
    .asis-empty { padding:30px !important; text-align:center; color:#94a3b8; }
    @media (max-width: 768px) { .asis-stats { grid-template-columns: 1fr; } }
</style>

<script>
(function () {
    const overlay = document.getElementById('asisModal');
    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const urlTpl = @json(url('/cursos/' . $course->id . '/periodos/__ID__/asistencia-lista'));
    const periodos = @json($periodosAsistencia);
    let filas = [];
    let universidad = false;

    // Períodos que se cruzan con el rango de fechas elegido
    function llenarPeriodos(seleccionado) {
        const desde = $('asisDesde').value, hasta = $('asisHasta').value;
        const lista = periodos.filter((p) => (!desde || p.fin >= desde) && (!hasta || p.inicio <= hasta));
        $('asisPeriodo').innerHTML = lista.length
            ? lista.map((p) => `<option value="${p.id}" ${String(p.id) === String(seleccionado) ? 'selected' : ''}>${esc(p.label)}</option>`).join('')
            : '<option value="">Sin períodos en esas fechas</option>';
        return lista;
    }

    window.abrirListaAsistencia = async function (periodId, label, reiniciarFechas) {
        if (reiniciarFechas) { $('asisDesde').value = ''; $('asisHasta').value = ''; }
        const p = periodos.find((x) => String(x.id) === String(periodId));
        $('asisModalPeriodo').textContent = label || (p ? p.label : '');
        $('asisTitulo').textContent = p ? `Período del ${p.label}` : '';
        llenarPeriodos(periodId);
        $('asisBuscar').value = '';
        $('asisLoading').style.display = 'block';
        $('asisError').style.display = 'none';
        $('asisContent').style.display = 'none';
        if ($('asisPdf')) $('asisPdf').classList.add('is-disabled');
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');

        try {
            const res = await fetch(urlTpl.replace('__ID__', periodId), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            filas = data.rows || [];
            universidad = !!data.universidad;
            $('asisTotal').textContent = data.total;
            $('asisCompletados').textContent = data.completados;
            $('asisProgreso').textContent = data.en_progreso;
            if ($('asisPdf')) {
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
        const cols = universidad ? ['#', 'Nombre completo', 'RFC', 'Carrera', 'Progreso', 'Calificación', 'Estado']
                                 : ['#', 'Nombre completo', 'RFC', 'Departamento', 'Puesto', 'Progreso', 'Calificación', 'Estado'];
        $('asisHead').innerHTML = '<tr>' + cols.map((c) => `<th>${c}</th>`).join('') + '</tr>';
        if (!filas.length) {
            $('asisBody').innerHTML = `<tr><td colspan="${cols.length}" class="asis-empty">Este período aún no tiene participantes asignados (botón verde).</td></tr>`;
            return;
        }
        const lista = filas.filter((r) => !q || (r.nombre + ' ' + r.rfc).toLowerCase().includes(q));
        if (!lista.length) {
            $('asisBody').innerHTML = `<tr><td colspan="${cols.length}" class="asis-empty">No hay coincidencias con la búsqueda.</td></tr>`;
            return;
        }
        const clase = { 'Completado': 'is-ok', 'En progreso': 'is-mid', 'Sin iniciar': 'is-no' };
        $('asisBody').innerHTML = lista.map((r, i) => `<tr>
            <td>${i + 1}</td>
            <td>${esc(r.nombre)}</td>
            <td>${esc(r.rfc)}</td>
            <td>${esc(r.area)}</td>
            ${universidad ? '' : `<td>${esc(r.puesto)}</td>`}
            <td><div class="asis-prog"><div class="asis-prog-bar"><div style="width:${Number(r.progreso) || 0}%"></div></div><span>${Number(r.progreso) || 0}%</span></div></td>
            <td>${r.calificacion !== null && r.calificacion !== undefined ? esc(r.calificacion) : '—'}</td>
            <td><span class="asis-badge ${clase[r.estado] || 'is-no'}">${esc(r.estado)}</span></td>
        </tr>`).join('');
    }

    $('asisBuscar').addEventListener('input', function () { pintar(this.value.trim().toLowerCase()); });

    $('asisPeriodo').addEventListener('change', function () {
        const p = periodos.find((x) => String(x.id) === this.value);
        if (p) abrirListaAsistencia(p.id, p.label);
    });

    // Fecha inicio / fin: deja solo los períodos que caen en ese rango y abre el primero
    ['asisDesde', 'asisHasta'].forEach((id) => $(id).addEventListener('change', function () {
        const actual = $('asisPeriodo').value;
        const lista = llenarPeriodos(actual);
        if (lista.length && !lista.some((p) => String(p.id) === String(actual))) abrirListaAsistencia(lista[0].id, lista[0].label);
    }));

    window.cerrarListaAsistencia = function () {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
    };
    overlay.addEventListener('mousedown', (e) => { if (e.target === overlay) cerrarListaAsistencia(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && overlay.classList.contains('is-open')) cerrarListaAsistencia(); });
})();
</script>

@endsection
```eof