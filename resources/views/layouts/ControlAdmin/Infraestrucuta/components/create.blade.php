<form id="createFacilityForm">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    
    <div class="form-group">
        <label for="numero_aula">Número de Aula</label>
        <input type="text" id="numero_aula" name="numero_aula" class="form-control" maxlength="10" required placeholder="Agregue el número del aula">
    </div>

    <div class="form-group">
        <label for="tipo">Tipo de Aula</label>
        <select id="tipo" name="tipo" class="form-control" required>
            <option value="" disabled selected hidden>Seleccione su Tipo:</option>
            <option value="Aula">Aula</option>
            <option value="Laboratorio">Laboratorio</option>
            <option value="Otro">Otro</option>
        </select>
    </div>

    <div class="form-group">
        <label for="seccion">Sección</label>
        <input type="text" id="seccion" name="seccion" class="form-control" maxlength="255" placeholder="Agregue el grado y grupo (ej. 3° A)">
    </div>

    <div class="form-group">
        <label for="capacidad">Capacidad</label>
        <input type="number" id="capacidad" name="capacidad" class="form-control" min="0" placeholder="Agregue cantidad de alumno">
    </div>
    
    <div class="modal-footer-custom" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">+ Guardar</button>
    </div>
</form>

<div id="formMessages" style="margin-top: 15px;"></div>