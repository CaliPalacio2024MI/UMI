@extends('layouts.app')

@section('title', 'Boleta de calificaciones - ' . session('active_institution_name'))

@section('content')
<div id="umi-app-view" class="boletas-esc">
    <div class="content-title">
        <h3>Boleta de calificaciones</h3>
    </div>

    @if($periodoSeleccionado)
        <p class="boletas-esc__meta">
            Periodo seleccionado: <strong>{{ $periodoSeleccionado->name }}</strong>
            @if($periodoSeleccionado->start_date && $periodoSeleccionado->end_date)
                ({{ $periodoSeleccionado->start_date->format('M Y') }} – {{ $periodoSeleccionado->end_date->format('M Y') }})
            @endif
        </p>
    @endif

    <div class="boletas-esc__toolbar">
        <form id="boletas-search-form" method="get" action="{{ route('escolar.boletas.index') }}" style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; flex: 1;">
            <div class="boletas-esc__pill">
                <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="#223F70" stroke-width="1.91"><circle cx="9.14" cy="9.14" r="7.64"/><line x1="22.5" y1="22.5" x2="14.39" y2="14.39"/></svg>
                <input
                    type="text"
                    id="boletas-search-input"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Buscar por..."
                    style="border: none; outline: none; font-size: 0.95rem; min-width: 200px; background: transparent;"
                >
            </div>

            <div class="boletas-esc__pill boletas-esc__pill--dark">
                <select id="boletas-clasificacion-select" name="clasificacion_id" aria-label="Clasificación">
                    <option value="">Clasificación</option>
                    @foreach($clasificaciones as $cl)
                        <option value="{{ $cl->id }}" @selected((int) ($clasificacionId ?? 0) === (int) $cl->id)>
                            {{ $cl->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="boletas-esc__pill boletas-esc__pill--dark">
                <select id="boletas-carrera-select" name="carrera_id" aria-label="Carrera" disabled>
                    <option value="">Carrera</option>
                </select>
            </div>

            <div class="boletas-esc__pill boletas-esc__pill--dark">
                <select id="boletas-materia-select" name="materia_id" aria-label="Materia" disabled>
                    <option value="">Materia</option>
                </select>
            </div>

            <div class="boletas-esc__pill boletas-esc__pill--dark">
                <select id="boletas-periodo-select" name="periodo_id" aria-label="Periodo de estudios">
                    <option value="">Periodo de estudios</option>
                    @foreach($periodos as $per)
                        <option value="{{ $per->id }}" @selected((int) ($periodoId ?? 0) === (int) $per->id)>
                            {{ $per->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <form id="boletas-export-form" method="get" action="{{ route('escolar.boletas.export') }}" style="display: inline;">
            <button type="submit" class="boletas-esc__export">
                <img src="{{ asset('images/icons/export-icon.svg') }}" alt="" width="20" height="20" style="filter: brightness(0) invert(1);">
                Exportar
            </button>
        </form>
    </div>

    <div class="boletas-esc__card">
        <div class="boletas-esc__table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Alumno</th>
                        @for($i = 1; $i <= ($materiaSeleccionada->num_parciales ?? 3); $i++)
                            <th class="boletas-col-divider">Parcial {{ $i }}</th>
                        @endfor
                        <th class="boletas-col-divider">Calificación Final</th>
                        <th class="boletas-col-divider">Evaluación</th>
                        <th class="boletas-col-divider">Observaciones</th>
                    </tr>
                </thead>
                <tbody id="boletas-table-body">
                    @include('layouts.ControlEsc.Boletas.partials.table_rows', ['rows' => $rows])
                </tbody>
            </table>
        </div>
    </div>

    <div id="boletas-pagination" class="d-flex justify-content-center py-3 px-2">
        @if($rows->hasPages())
            {{ $rows->withQueryString()->links() }}
        @endif
    </div>
</div>
<script>
(function () {
    const form = document.getElementById('boletas-search-form');
    const input = document.getElementById('boletas-search-input');
    const clasificacionSelect = document.getElementById('boletas-clasificacion-select');
    const carreraSelect = document.getElementById('boletas-carrera-select');
    const materiaSelect = document.getElementById('boletas-materia-select');
    const periodoSelect = document.getElementById('boletas-periodo-select');
    const tbody = document.getElementById('boletas-table-body');
    const pagination = document.getElementById('boletas-pagination');
    const thead = document.querySelector('.boletas-esc__table-wrap thead tr');
    const exportForm = document.getElementById('boletas-export-form');
    if (!form || !input || !tbody || !pagination || !exportForm) return;

    let timer = null;

    function syncExportFilters() {
        exportForm.querySelectorAll('input[data-boletas-sync="1"]').forEach(function(el) { el.remove(); });
        var fields = {search: input.value, clasificacion_id: clasificacionSelect.value, carrera_id: carreraSelect.value, materia_id: materiaSelect.value, periodo_id: periodoSelect.value};
        for (var key in fields) {
            if ((fields[key] || '').trim() !== '') {
                var h = document.createElement('input');
                h.type = 'hidden'; h.name = key; h.value = fields[key].trim();
                h.setAttribute('data-boletas-sync', '1');
                exportForm.appendChild(h);
            }
        }
    }

    function updateThead(numParciales) {
        var html = '<th>Alumno</th>';
        for (var i = 1; i <= numParciales; i++) {
            html += '<th class="boletas-col-divider">Parcial ' + i + '</th>';
        }
        html += '<th class="boletas-col-divider">Calificación Final</th>';
        html += '<th class="boletas-col-divider">Evaluación</th>';
        html += '<th class="boletas-col-divider">Observaciones</th>';
        thead.innerHTML = html;
    }

    function refreshTable(url) {
        fetch(url, {method: 'GET', headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}})
            .then(function(r) { return r.json(); })
            .then(function(data) {
                tbody.innerHTML = data.tbody || '';
                pagination.innerHTML = data.pagination || '';
                if (data.numParciales) updateThead(data.numParciales);
                window.history.replaceState({}, '', url);
                syncExportFilters();
            })
            .catch(function() {});
    }

    function buildUrl(pageUrl) {
        var url = new URL(pageUrl || form.action, window.location.origin);
        var params = {search: input.value, clasificacion_id: clasificacionSelect.value, carrera_id: carreraSelect.value, materia_id: materiaSelect.value, periodo_id: periodoSelect.value};
        for (var key in params) {
            if ((params[key] || '').trim() !== '') url.searchParams.set(key, params[key].trim());
            else url.searchParams.delete(key);
        }
        if (!pageUrl) url.searchParams.delete('page');
        return url.toString();
    }

    clasificacionSelect.addEventListener('change', function() {
        carreraSelect.innerHTML = '<option value="">Carrera</option>';
        carreraSelect.disabled = true;
        materiaSelect.innerHTML = '<option value="">Materia</option>';
        materiaSelect.disabled = true;
        if (!this.value) { refreshTable(buildUrl()); return; }
        fetch('{{ route("escolar.boletas.carreras") }}?clasificacion_id=' + this.value, {headers: {'Accept': 'application/json'}})
            .then(function(r) { return r.json(); })
            .then(function(data) {
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id; opt.textContent = c.name;
                    carreraSelect.appendChild(opt);
                });
                carreraSelect.disabled = false;
            });
        refreshTable(buildUrl());
    });

    carreraSelect.addEventListener('change', function() {
        materiaSelect.innerHTML = '<option value="">Materia</option>';
        materiaSelect.disabled = true;
        if (!this.value) { refreshTable(buildUrl()); return; }
        fetch('{{ route("escolar.boletas.materias") }}?carrera_id=' + this.value, {headers: {'Accept': 'application/json'}})
            .then(function(r) { return r.json(); })
            .then(function(data) {
                data.forEach(function(m) {
                    var opt = document.createElement('option');
                    opt.value = m.id; opt.textContent = m.nombre + (m.clave ? ' (' + m.clave + ')' : '');
                    opt.setAttribute('data-parciales', m.num_parciales);
                    materiaSelect.appendChild(opt);
                });
                materiaSelect.disabled = false;
            });
        refreshTable(buildUrl());
    });

    materiaSelect.addEventListener('change', function() {
        refreshTable(buildUrl());
    });

    periodoSelect.addEventListener('change', function() {
        refreshTable(buildUrl());
    });

    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(function() { refreshTable(buildUrl()); }, 300);
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        refreshTable(buildUrl());
    });

    document.addEventListener('click', function(e) {
        var link = e.target.closest('#boletas-pagination a');
        if (!link) return;
        e.preventDefault();
        refreshTable(buildUrl(link.href));
    });

    exportForm.addEventListener('submit', syncExportFilters);
})();
</script>
@endsection
