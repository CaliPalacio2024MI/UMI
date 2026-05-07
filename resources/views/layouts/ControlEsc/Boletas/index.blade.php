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
                    style="border: none; outline: none; font-size: 0.95rem; min-width: 280px; background: transparent;"
                >
            </div>

            <div class="boletas-esc__pill boletas-esc__pill--dark">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9.14" cy="9.14" r="7.64"/><line x1="22.5" y1="22.5" x2="14.39" y2="14.39"/></svg>
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
                        <th class="boletas-col-divider">Parciales</th>
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
    const periodoSelect = document.getElementById('boletas-periodo-select');
    const tbody = document.getElementById('boletas-table-body');
    const pagination = document.getElementById('boletas-pagination');
    const exportForm = document.getElementById('boletas-export-form');
    if (!form || !input || !tbody || !pagination || !exportForm) return;

    let timer = null;

    function syncExportFilters() {
        exportForm.querySelectorAll('input[name="search"], input[name="periodo_id"], input[data-boletas-sync="1"]').forEach((el) => el.remove());
        const searchVal = (input.value || '').trim();
        const periodoVal = periodoSelect ? (periodoSelect.value || '').trim() : '';

        if (searchVal !== '') {
            const h = document.createElement('input');
            h.type = 'hidden';
            h.name = 'search';
            h.value = searchVal;
            h.setAttribute('data-boletas-sync', '1');
            exportForm.appendChild(h);
        }
        if (periodoVal !== '') {
            const h = document.createElement('input');
            h.type = 'hidden';
            h.name = 'periodo_id';
            h.value = periodoVal;
            h.setAttribute('data-boletas-sync', '1');
            exportForm.appendChild(h);
        }
    }

    function refreshTable(url) {
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then((r) => r.json())
            .then((data) => {
                tbody.innerHTML = data.tbody || '';
                pagination.innerHTML = data.pagination || '';
                window.history.replaceState({}, '', url);
                syncExportFilters();
            })
            .catch(() => {});
    }

    function buildUrl(pageUrl) {
        const url = new URL(pageUrl || form.action, window.location.origin);
        const searchVal = (input.value || '').trim();
        const periodoVal = periodoSelect ? (periodoSelect.value || '').trim() : '';
        if (searchVal !== '') url.searchParams.set('search', searchVal);
        else url.searchParams.delete('search');
        if (periodoVal !== '') url.searchParams.set('periodo_id', periodoVal);
        else url.searchParams.delete('periodo_id');
        if (!pageUrl) url.searchParams.delete('page');
        return url.toString();
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => refreshTable(buildUrl()), 300);
    });

    if (periodoSelect) {
        periodoSelect.addEventListener('change', function () {
            refreshTable(buildUrl());
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        refreshTable(buildUrl());
    });

    document.addEventListener('click', function (e) {
        const link = e.target.closest('#boletas-pagination a');
        if (!link) return;
        e.preventDefault();
        refreshTable(buildUrl(link.href));
    });

    exportForm.addEventListener('submit', syncExportFilters);
})();
</script>
@endsection
