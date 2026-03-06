<div id="viewCareerModal_{{ $career->id }}" class="modal-overlay modal-view-career">
    <div class="modal-content-container modal-view-career__container">
        <div class="modal-header-custom modal-view-career__header">
            <h5 id="viewCareerModalLabel" class="modal-view-career__title">Informacion de Carrera</h5>
            <button type="button" class="close-custom btn-close-view modal-view-career__close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body-custom modal-view-career__body">
            <dl class="career-view-dl career-view-dl--styled">
                <div class="career-view-row">
                    <dt>Nombre:</dt>
                    <dd>{{ $career->name }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>RVOE (Acuerdo número)</dt>
                    <dd>{{ $career->official_id ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Profesionalización y empleabilidad</dt>
                    <dd>{{ $career->description1 ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Objetivo General</dt>
                    <dd>{{ $career->description2 ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Elige Ser</dt>
                    <dd>{{ $career->description3 ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Modalidad</dt>
                    <dd>{{ $career->type ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>No. de semestres</dt>
                    <dd>{{ $career->semesters ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
