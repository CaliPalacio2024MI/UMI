<div id="viewMateriaModal_{{ $registro->id }}" class="modal-overlay modal-view-materia">
    <div class="modal-content-container modal-view-materia__container">
        <div class="modal-header-custom modal-view-materia__header">
            <h5 id="viewMateriaModalLabel" class="modal-view-materia__title">Información de la Materia</h5>
            <button type="button" class="close-custom btn-close-view modal-view-materia__close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body-custom modal-view-materia__body">
            <dl class="materia-view-dl">
                <div class="materia-view-row">
                    <dt>Carrera:</dt>
                    <dd>{{ $registro->career?->name ?? '—' }}</dd>
                </div>
                <div class="materia-view-row">
                    <dt>Nombre:</dt>
                    <dd>{{ $registro->nombre ?? '—' }}</dd>
                </div>
                <div class="materia-view-row">
                    <dt>No. de créditos:</dt>
                    <dd>{{ $registro->creditos ?? '—' }}</dd>
                </div>
                <div class="materia-view-row">
                    <dt>Semestre:</dt>
                    <dd>{{ $registro->semestre ?? '—' }}</dd>
                </div>
                <div class="materia-view-row">
                    <dt>Modalidad:</dt>
                    <dd>{{ $registro->type ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
