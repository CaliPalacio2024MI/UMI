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
        <form method="get" action="{{ route('escolar.boletas.index') }}" style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; flex: 1;">
            <div class="boletas-esc__pill">
                <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="#223F70" stroke-width="1.91"><circle cx="9.14" cy="9.14" r="7.64"/><line x1="22.5" y1="22.5" x2="14.39" y2="14.39"/></svg>
                <select name="materia_id" style="border: none; outline: none; font-size: 0.95rem; min-width: 200px; cursor: pointer; background: transparent;" onchange="this.form.submit()">
                    <option value="">Buscar materia</option>
                    @foreach($materias as $mat)
                        <option value="{{ $mat->id }}" @selected((int) ($materiaId ?? 0) === (int) $mat->id)>
                            {{ $mat->nombre }}@if($mat->clave) ({{ $mat->clave }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="boletas-esc__pill boletas-esc__pill--dark">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9.14" cy="9.14" r="7.64"/><line x1="22.5" y1="22.5" x2="14.39" y2="14.39"/></svg>
                <select name="periodo_id" aria-label="Periodo de estudios" onchange="this.form.submit()">
                    <option value="">Periodo de estudios</option>
                    @foreach($periodos as $per)
                        <option value="{{ $per->id }}" @selected((int) ($periodoId ?? 0) === (int) $per->id)>
                            {{ $per->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <form method="get" action="{{ route('escolar.boletas.export') }}" style="display: inline;">
            @if($materiaId)
                <input type="hidden" name="materia_id" value="{{ $materiaId }}">
            @endif
            @if($periodoId)
                <input type="hidden" name="periodo_id" value="{{ $periodoId }}">
            @endif
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
                        <th>Nombre</th>
                        <th class="boletas-col-divider">Parciales</th>
                        <th class="boletas-col-divider">Calificación Final</th>
                        <th class="boletas-col-divider">Evaluación</th>
                        <th class="boletas-col-divider">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>
                                {{ $r->alumno_nombre }}
                                @if(!empty($r->materia) && $r->materia !== '—')
                                    <div style="font-size: 0.78rem; font-weight: 500; color: #666;">{{ $r->materia }}</div>
                                @endif
                            </td>
                            <td class="boletas-col-divider">{{ $r->parciales }}</td>
                            <td class="boletas-col-divider">{{ $r->final }}</td>
                            <td class="boletas-col-divider">{{ $r->evaluacion }}</td>
                            <td class="boletas-col-divider">{{ $r->observaciones }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2.5rem; color: #666;">
                                No hay registros con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($rows->hasPages())
        <div class="d-flex justify-content-center py-3 px-2">
            {{ $rows->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
