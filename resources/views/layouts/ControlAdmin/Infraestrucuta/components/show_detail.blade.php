<div class="aulas-facility-detail-readonly">
    <div class="form-group">
        <label for="view_nombre_aula">Nombre del aula</label>
        <input type="text" id="view_nombre_aula" class="form-control" maxlength="255" readonly value="{{ $facility->nombre_aula ?? '—' }}">
    </div>
    <div class="form-group">
        <label for="view_career_id">Carrera</label>
        <input type="text" id="view_career_id" class="form-control" readonly value="{{ $facility->career->name ?? '—' }}">
    </div>
    <div class="form-group">
        <label for="view_tipo_materia">Materia</label>
        <input type="text" id="view_tipo_materia" class="form-control" readonly value="{{ $facility->tipo_materia ?: '—' }}">
    </div>
</div>
