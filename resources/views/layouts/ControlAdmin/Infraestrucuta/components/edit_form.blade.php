@php
    $carreras = $carreras ?? collect();
@endphp
<form id="editFacilityForm" method="POST" action="{{ route('control.facilities.update', $facility) }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="edit_nombre_aula">Nombre del aula</label>
        <input type="text" id="edit_nombre_aula" name="nombre_aula" class="form-control" maxlength="255" required value="{{ old('nombre_aula', $facility->nombre_aula) }}">
    </div>
    <div class="form-group">
        <label for="edit_career_id">Carrera</label>
        <select id="edit_career_id" name="career_id" class="form-control">
            <option value="">Ingrese la carrera</option>
            @foreach ($carreras as $c)
                <option value="{{ $c->id }}" @selected(old('career_id', $facility->career_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="edit_tipo_materia">Materia</label>
        <select id="edit_tipo_materia" name="tipo_materia" class="form-control" data-preselected="{{ e($facility->tipo_materia ?? '') }}">
            <option value="">Cargando…</option>
        </select>
    </div>

    <div class="modal-footer-custom" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">+ Guardar</button>
    </div>
</form>

<div id="formMessages" style="margin-top: 15px;"></div>
