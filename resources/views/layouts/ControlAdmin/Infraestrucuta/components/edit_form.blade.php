@php
    $clasificaciones = $clasificaciones ?? collect();
    $carreras = $carreras ?? collect();
    $selectedCareerIds = $facility->careers->pluck('id')->toArray();
    $selectedMateriaIds = $facility->materias->pluck('id')->toArray();
@endphp
<form id="editFacilityForm" method="POST" action="{{ route('control.facilities.update', $facility) }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="edit_nombre_aula">Nombre del aula</label>
        <input type="text" id="edit_nombre_aula" name="nombre_aula" class="form-control" maxlength="255" required value="{{ old('nombre_aula', $facility->nombre_aula) }}">
    </div>

    <div class="form-group">
        <label for="edit_clasificacion_id">Clasificación</label>
        <select id="edit_clasificacion_id" name="career_classification_id" class="form-control aula-clasificacion-select">
            <option value="">-- Todas las clasificaciones --</option>
            @foreach ($clasificaciones as $cl)
                <option value="{{ $cl->id }}" @selected(old('career_classification_id', $facility->career_classification_id) == $cl->id)>{{ $cl->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Carreras</label>
        <div class="aula-carreras-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; border-radius: 4px;">
            @foreach ($carreras as $c)
                <label class="aula-check-item" data-classification="{{ $c->career_classification_id }}" style="display: flex; align-items: center; padding: 8px 12px; margin: 0; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.15s;">
                    <input class="aula-career-check" type="checkbox" name="career_ids[]" value="{{ $c->id }}" style="margin-right: 10px; width: 16px; height: 16px; accent-color: #b87a2b;"
                        @checked(in_array($c->id, $selectedCareerIds))>
                    <span>{{ $c->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="form-group">
        <label>Materias</label>
        <div class="aula-materias-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; border-radius: 4px; padding: 10px;"
             data-preselected="{{ json_encode($selectedMateriaIds) }}">
            <span class="text-muted">Cargando materias…</span>
        </div>
    </div>

    <div class="modal-footer-custom" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">+ Guardar</button>
    </div>
</form>

<div id="formMessages" style="margin-top: 15px;"></div>

<script>
(function () {
    var form = document.getElementById('editFacilityForm');
    if (form && window.umiAulasLoadMaterias) {
        var container = form.querySelector('.aula-materias-container');
        var pre = container ? JSON.parse(container.dataset.preselected || '[]') : [];
        window.umiAulasLoadMaterias(form, pre);
    }
    // Aplicar filtro de clasificación inicial
    if (form && window.umiAulasFilterCarreras) {
        var clasSel = form.querySelector('.aula-clasificacion-select');
        if (clasSel) window.umiAulasFilterCarreras(form, clasSel.value);
    }
})();
</script>