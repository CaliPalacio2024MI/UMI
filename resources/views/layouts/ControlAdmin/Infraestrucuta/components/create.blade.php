@php
    $carreras = $carreras ?? collect();
@endphp
<form id="createFacilityForm">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div class="form-group">
        <label for="nombre_aula">Nombre del aula</label>
        <input type="text" id="nombre_aula" name="nombre_aula" class="form-control" maxlength="255" required placeholder="Ingrese el nombre del salón">
    </div>

    <div class="form-group">
        <label for="career_id">Carrera</label>
        <select id="career_id" name="career_id" class="form-control">
            <option value="">Ingrese la carrera</option>
            @foreach ($carreras as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="tipo_materia">Materia</label>
        <select id="tipo_materia" name="tipo_materia" class="form-control" disabled>
            <option value="">Seleccione la carrera</option>
        </select>
    </div>

    <div class="modal-footer-custom" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">+ Guardar</button>
    </div>
</form>

<div id="formMessages" style="margin-top: 15px;"></div>
