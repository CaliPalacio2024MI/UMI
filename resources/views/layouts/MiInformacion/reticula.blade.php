@extends('layouts.app')

@section('title', 'Retícula - ' . session('active_institution_name'))

@vite(['resources/css/Control Admin/base.css', 'resources/js/app.js'])

@push('css')
<style>
.reticula-page { max-width: 1400px; margin: 0 auto; }
.reticula-board {
    background: #e8ecef; border-radius: 20px; padding: 1rem 1rem 1.25rem;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.06);
}
.reticula-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 0.25rem; }
.reticula-head-bar {
    display: flex; width: 100%;
    min-width: calc(var(--reticula-semesters, 8) * 120px + (var(--reticula-semesters, 8) - 1) * 10px);
    background: #223F70; color: #fff; border-radius: 14px 14px 0 0; overflow: hidden;
    box-shadow: 0 2px 6px rgba(34, 63, 112, 0.25);
}
.reticula-head-cell {
    flex: 1 1 0; min-width: 120px; text-align: center; font-weight: 700; font-size: 0.72rem;
    line-height: 1.25; padding: 12px 8px; text-transform: uppercase; letter-spacing: 0.02em;
    background: #223F70; border-right: 1px solid #223F70;
}
.reticula-head-cell:last-child { border-right: none; }
.reticula-grid {
    display: grid; grid-template-columns: repeat(var(--reticula-semesters, 8), minmax(120px, 1fr));
    gap: 10px; width: 100%;
    min-width: calc(var(--reticula-semesters, 8) * 120px + (var(--reticula-semesters, 8) - 1) * 10px);
    padding-top: 10px;
}
.reticula-col { min-width: 0; display: flex; flex-direction: column; gap: 8px; }
.reticula-col-body { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.reticula-card {
    background: #fff; border-radius: 12px; border: 1px solid #9aa5b4;
    padding: 12px 10px; text-align: center; min-height: 72px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06), 0 6px 14px rgba(34, 63, 112, 0.1);
    display: flex; flex-direction: column; justify-content: center; gap: 6px;
}
.reticula-card--ultimo { border: 2px solid #c9a227; box-shadow: 0 2px 6px rgba(201, 162, 39, 0.25); }
.reticula-card--vacío { border-style: dashed; border-color: #cfd6de; color: #9ca3af; font-size: 0.8rem; min-height: 56px; }
.reticula-card-title { font-size: 0.82rem; font-weight: 600; color: #DB5865; line-height: 1.3; margin: 0; }
.reticula-card-code { font-size: 0.72rem; color: #6b7280; font-family: ui-monospace, monospace; margin: 0; }
.reticula-card-actions { margin-top: 6px; }
.reticula-info-btn {
    border: 1px solid #223F70; background: #fff; color: #223F70; font-size: 0.72rem;
    border-radius: 999px; padding: 4px 10px; cursor: pointer;
}
.reticula-materia-modal .modal-content-container { max-width: 640px; }
.reticula-materia-modal .reticula-materia-list { display: grid; gap: 10px; }
.reticula-materia-modal .reticula-materia-row dt { color: #BC8A55; font-weight: 700; margin-bottom: 2px; }
.reticula-materia-modal .reticula-materia-row dd { margin: 0; color: #2d3748; white-space: pre-wrap; }
.reticula-materia-modal .reticula-materia-row { border-bottom: 2px solid #DB5865; padding-bottom: 10px; }
.reticula-footer { display: flex; justify-content: flex-end; margin-top: 0.75rem; }
.reticula-footer-box {
    display: flex; justify-content: flex-end; align-items: center; gap: 12px; flex-wrap: wrap;
    background: #ECF0F1; border-radius: 999px; padding: 10px 18px;
}
.reticula-stat {
    background: #fff; border-radius: 999px; padding: 8px 16px; font-size: 0.88rem; font-weight: 600;
    color: #111; border: 1px solid #dee2e6; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
@media (max-width: 1200px) {
    .reticula-grid { grid-template-columns: repeat(var(--reticula-semesters, 8), minmax(108px, 1fr)); min-width: calc(var(--reticula-semesters, 8) * 108px + (var(--reticula-semesters, 8) - 1) * 10px); }
    .reticula-head-bar { min-width: calc(var(--reticula-semesters, 8) * 108px + (var(--reticula-semesters, 8) - 1) * 10px); }
    .reticula-head-cell { min-width: 108px; }
}
</style>
@endpush

@section('content')
<div class="container reticula-page">
    <div class="content-header">
        <div class="content-title">
            <h3>Retícula — {{ $carrera->name }}</h3>
        </div>
    </div>

    <div class="reticula-board" style="--reticula-semesters: {{ $totalSemesters }};">
        <div class="reticula-scroll">
            <div class="reticula-head-bar" role="row" aria-label="Semestres">
                @for ($s = 1; $s <= $totalSemesters; $s++)
                    <div class="reticula-head-cell">{{ $semestreLabels[$s] ?? ($s . 'º Sem.') }}</div>
                @endfor
            </div>
            <div class="reticula-grid">
                @for ($s = 1; $s <= $totalSemesters; $s++)
                    <div class="reticula-col">
                        <div class="reticula-col-body">
                            @forelse ($porSemestre[$s] ?? [] as $mat)
                                <article class="reticula-card {{ $s === $totalSemesters ? 'reticula-card--ultimo' : '' }}">
                                    <h4 class="reticula-card-title">{{ $mat->nombre }}</h4>
                                    <p class="reticula-card-code">{{ filled($mat->clave) ? $mat->clave : '—' }}</p>
                                    <div class="reticula-card-actions">
                                        <button type="button" class="reticula-info-btn js-open-reticula-materia" data-reticula-materia-id="{{ $mat->id }}">Ver info</button>
                                    </div>
                                </article>
                                <div id="reticulaMateriaModal_{{ $mat->id }}" class="modal-overlay reticula-materia-modal">
                                    <div class="modal-content-container">
                                        <div class="modal-header-custom">
                                            <h5>Información de la materia</h5>
                                            <button type="button" class="close-custom" aria-label="Cerrar">&times;</button>
                                        </div>
                                        <div class="modal-body-custom">
                                            <dl class="reticula-materia-list">
                                                <div class="reticula-materia-row"><dt>Materia</dt><dd>{{ $mat->nombre ?? '—' }}</dd></div>
                                                <div class="reticula-materia-row"><dt>Semestre</dt><dd>{{ $mat->semestre ?? $s }}</dd></div>
                                                <div class="reticula-materia-row"><dt>Créditos</dt><dd>{{ $mat->creditos ?? '—' }}</dd></div>
                                                <div class="reticula-materia-row"><dt>Descripción general</dt><dd>{{ filled($mat->descripcion) ? $mat->descripcion : '—' }}</dd></div>
                                                <div class="reticula-materia-row"><dt>Objetivo</dt><dd>{{ filled($mat->objetivo) ? $mat->objetivo : '—' }}</dd></div>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.js-open-reticula-materia').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = document.getElementById('reticulaMateriaModal_' + btn.getAttribute('data-reticula-materia-id'));
            if (modal) modal.style.display = 'flex';
        });
    });
    document.querySelectorAll('.reticula-materia-modal .close-custom').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = btn.closest('.modal-overlay');
            if (modal) modal.style.display = 'none';
        });
    });
    document.querySelectorAll('.reticula-materia-modal').forEach(function(modal) {
        modal.addEventListener('click', function(e) { if (e.target === modal) modal.style.display = 'none'; });
    });
});
</script>
@endpush
