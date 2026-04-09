@extends('layouts.app')

@section('title', 'Retícula escolar - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@push('css')
<style>
.reticula-page { max-width: 1400px; margin: 0 auto; }
.reticula-close-btn {
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    width: 40px; height: 40px;
    border: none; border-radius: 50%; background: transparent;
    color: #6b7280; font-size: 1.35rem; line-height: 1; text-decoration: none;
}
.reticula-close-btn:hover,
.reticula-close-btn:active {
    background: transparent; color: #6b7280; text-decoration: none;
}
.reticula-toolbar {
    display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem 1.5rem; margin-bottom: 1.25rem;
}
.reticula-toolbar label { font-weight: 600; color: #b8860b; display: block; margin-bottom: 0.35rem; font-size: 0.9rem; }
.reticula-toolbar select {
    min-width: 280px; border-radius: 20px; border: 1px solid #ddd; padding: 8px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08); background: #fff;
}
.reticula-board {
    background: #e8ecef; border-radius: 20px; padding: 1rem 1rem 1.25rem;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.06);
}
/* Una sola barra azul para los 8 semestres */
.reticula-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 0.25rem;
}
.reticula-head-bar {
    display: flex;
    width: 100%;
    min-width: calc(8 * 120px + 7 * 10px);
    background: #223F70;
    color: #fff;
    border-radius: 14px 14px 0 0;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(34, 63, 112, 0.25);
}
.reticula-head-cell {
    flex: 1 1 0;
    min-width: 120px;
    text-align: center;
    font-weight: 700;
    font-size: 0.72rem;
    line-height: 1.25;
    padding: 12px 8px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    background: #223F70;
    border-right: 1px solid #223F70;
}
.reticula-head-cell:last-child {
    border-right: none;
}
.reticula-grid {
    display: grid;
    grid-template-columns: repeat(8, minmax(120px, 1fr));
    gap: 10px;
    width: 100%;
    min-width: calc(8 * 120px + 7 * 10px);
    padding-top: 10px;
}
.reticula-col { min-width: 0; display: flex; flex-direction: column; gap: 8px; }
.reticula-col-body { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.reticula-card {
    background: #fff; border-radius: 12px;
    border: 1px solid #9aa5b4;
    padding: 12px 10px; text-align: center; min-height: 72px;
    box-shadow:
        0 2px 4px rgba(0, 0, 0, 0.06),
        0 6px 14px rgba(34, 63, 112, 0.1);
    display: flex; flex-direction: column; justify-content: center; gap: 6px;
}
.reticula-card--ultimo { border: 2px solid #c9a227; box-shadow: 0 2px 6px rgba(201, 162, 39, 0.25); }
.reticula-card--vacío {
    border-style: dashed; border-color: #cfd6de; color: #9ca3af; font-size: 0.8rem; min-height: 56px;
}
.reticula-card-title {
    font-size: 0.82rem; font-weight: 600; color: #DB5865; line-height: 1.3;
    margin: 0;
}
.reticula-card-code {
    font-size: 0.72rem; color: #6b7280; font-family: ui-monospace, monospace; margin: 0;
}
.reticula-footer {
    display: flex; justify-content: flex-end; margin-top: 0.75rem;
}
.reticula-footer-box {
    display: flex; justify-content: flex-end; align-items: center; gap: 12px; flex-wrap: wrap;
    background: #ECF0F1; border-radius: 999px; padding: 10px 18px;
}
.reticula-stat {
    background: #fff; border-radius: 999px; padding: 8px 16px; font-size: 0.88rem; font-weight: 600;
    color: #111; border: 1px solid #dee2e6; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
@media (max-width: 1200px) {
    .reticula-grid { grid-template-columns: repeat(8, minmax(108px, 1fr)); min-width: calc(8 * 108px + 7 * 10px); }
    .reticula-head-bar { min-width: calc(8 * 108px + 7 * 10px); }
    .reticula-head-cell { min-width: 108px; }
}
@media (max-width: 768px) {
    .reticula-toolbar select { min-width: 100%; }
}
</style>
@endpush

@section('content')
<div class="container reticula-page">
    <div class="content-header">
        <div class="content-title">
            <h3>Retícula escolar</h3>
        </div>
        <a href="{{ route('control.careers.index') }}" class="reticula-close-btn" title="Volver a carreras" aria-label="Cerrar retícula y volver al listado de carreras"><span aria-hidden="true">&times;</span></a>
    </div>

    <div class="reticula-toolbar">
        <div>
            <label for="reticula_carrera_id">Carrera</label>
            <select id="reticula_carrera_id" class="form-control" aria-label="Filtrar por carrera"
                onchange="if (this.value) window.location.href = this.options[this.selectedIndex].dataset.url;">
                @foreach ($carreras as $c)
                    @php $url = route('control.careers.reticula', $c->id); @endphp
                    <option value="{{ $c->id }}" data-url="{{ $url }}" {{ (int) $c->id === (int) $carrera->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="reticula-board">
        <div class="reticula-scroll">
            <div class="reticula-head-bar" role="row" aria-label="Semestres">
                @for ($s = 1; $s <= 8; $s++)
                    <div class="reticula-head-cell">{{ $semestreLabels[$s] ?? ($s . 'º Sem.') }}</div>
                @endfor
            </div>
            <div class="reticula-grid">
                @for ($s = 1; $s <= 8; $s++)
                    <div class="reticula-col">
                        <div class="reticula-col-body">
                            @forelse ($porSemestre[$s] ?? [] as $mat)
                                <article class="reticula-card {{ $s === 8 ? 'reticula-card--ultimo' : '' }}">
                                    <h4 class="reticula-card-title">{{ $mat->nombre }}</h4>
                                    <p class="reticula-card-code">{{ filled($mat->clave) ? $mat->clave : 'Matrixxxxx' }}</p>
                                </article>
                            @empty
                                <div class="reticula-card reticula-card--vacío" aria-hidden="true">Sin materias</div>
                            @endforelse
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    <div class="reticula-footer">
        <div class="reticula-footer-box">
            <span class="reticula-stat">Materias: {{ $totalMaterias }}</span>
            <span class="reticula-stat">Créditos: {{ $totalCreditos > 0 ? $totalCreditos : '—' }}</span>
        </div>
    </div>
</div>
@endsection
