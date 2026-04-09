{{-- Misma estructura visual que edit_form, solo lectura --}}
<div class="aulas-facility-detail-readonly">
    <div class="form-group">
        <label for="view_numero_aula">Número de Aula</label>
        <input type="text" id="view_numero_aula" class="form-control" maxlength="10" readonly value="{{ $facility->numero_aula }}">
    </div>
    <div class="form-group">
        <label for="view_tipo">Tipo</label>
        <select id="view_tipo" class="form-control" disabled>
            @foreach (['Aula', 'Laboratorio', 'Otro'] as $t)
                <option value="{{ $t }}" @selected($facility->tipo === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="view_seccion">Sección</label>
        <input type="text" id="view_seccion" class="form-control" maxlength="255" readonly value="{{ $facility->seccion === 'Sin sección' ? '' : $facility->seccion }}" placeholder="—">
    </div>
    <div class="form-group">
        <label for="view_capacidad">Capacidad</label>
        <input type="text" id="view_capacidad" class="form-control" readonly value="{{ $facility->capacidad !== null ? $facility->capacidad : '—' }}">
    </div>
</div>
