<form id="editFacilityForm" method="POST" action="{{ route('control.facilities.update', $facility) }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="edit_numero_aula">Número de Aula</label>
        <input type="text" id="edit_numero_aula" name="numero_aula" class="form-control" maxlength="10" required value="{{ old('numero_aula', $facility->numero_aula) }}">
    </div>
    <div class="form-group">
        <label for="edit_tipo">Tipo</label>
        <select id="edit_tipo" name="tipo" class="form-control" required>
            @foreach (['Aula', 'Laboratorio', 'Otro'] as $t)
                <option value="{{ $t }}" @selected(old('tipo', $facility->tipo) === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="edit_seccion">Sección</label>
        <input type="text" id="edit_seccion" name="seccion" class="form-control" maxlength="255" value="{{ old('seccion', $facility->seccion === 'Sin sección' ? '' : $facility->seccion) }}" placeholder="Opcional">
    </div>
    <div class="form-group">
        <label for="edit_capacidad">Capacidad</label>
        <input type="number" id="edit_capacidad" name="capacidad" class="form-control" min="0" value="{{ old('capacidad', $facility->capacidad) }}">
    </div>

    <div class="modal-footer-custom" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">+ Guardar</button>
    </div>
</form>

<div id="formMessages" style="margin-top: 15px;"></div>
