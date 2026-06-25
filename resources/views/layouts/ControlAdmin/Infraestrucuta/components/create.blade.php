@php
    $clasificaciones = $clasificaciones ?? collect();
    $carreras = $carreras ?? collect();
@endphp
<form id="createFacilityForm">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div class="form-group">
        <label for="nombre_aula">Nombre del aula</label>
        <input type="text" id="nombre_aula" name="nombre_aula" class="form-control" maxlength="255" required placeholder="Ingrese el nombre del aula">
    </div>

    <div class="form-group">
        <label for="clasificacion_id">Clasificación</label>
        <select id="clasificacion_id" name="career_classification_id" class="form-control aula-clasificacion-select">
            <option value="">-- Todas las clasificaciones --</option>
            @foreach ($clasificaciones as $cl)
                <option value="{{ $cl->id }}">{{ $cl->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Carreras</label>
        <div class="aula-carreras-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; border-radius: 4px;">
            @foreach ($carreras as $c)
                <label class="aula-check-item" data-classification="{{ $c->career_classification_id }}" style="display: flex; align-items: center; padding: 8px 12px; margin: 0; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.15s;">
                    <input class="aula-career-check" type="checkbox" name="career_ids[]" value="{{ $c->id }}" style="margin-right: 10px; width: 16px; height: 16px; accent-color: #b87a2b;">
                    <span>{{ $c->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="form-group">
        <label>Materias</label>
        <div class="aula-materias-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; border-radius: 4px; padding: 10px;">
            <span class="text-muted">Seleccione al menos una carrera.</span>
        </div>
    </div>

    <div class="modal-footer-custom" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">+ Guardar</button>
    </div>
</form>

<div id="formMessages" style="margin-top: 15px;"></div>