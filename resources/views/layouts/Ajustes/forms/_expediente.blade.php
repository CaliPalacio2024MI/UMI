@php
    $isEdit          = isset($item) && $item;
    $procesos        = $procesos ?? \App\Models\DocumentRequirement::PROCESOS;
    $tiposArchivo    = $tiposArchivo ?? \App\Models\DocumentRequirement::TIPOS_ARCHIVO;
    $currentProceso  = $currentProceso ?? array_key_first($procesos);

    $vProceso     = old('proceso', $isEdit ? $item->proceso : $currentProceso);
    $vNombre      = old('nombre', $isEdit ? $item->nombre : '');
    $vDescripcion = old('descripcion', $isEdit ? $item->descripcion : '');
    $vCantidad    = old('cantidad', $isEdit ? $item->cantidad : 1);
    $vOrden       = old('orden', $isEdit ? $item->orden : 0);
    $vObligatorio = old('obligatorio', $isEdit ? $item->obligatorio : true);
    $vActivo      = old('activo', $isEdit ? $item->activo : true);

    // Tipos seleccionados (arreglo de extensiones)
    $selectedTipos = old('tipos_archivo');
    if (!is_array($selectedTipos)) {
        $selectedTipos = $isEdit ? $item->tiposArchivoArray() : [];
    }
@endphp

<div class="form-group">
    <label for="expProceso">Proceso</label>
    <select id="expProceso" name="proceso" required>
        @foreach($procesos as $slug => $label)
            <option value="{{ $slug }}" @selected($vProceso === $slug)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="expNombre">Nombre del documento</label>
    <input type="text" id="expNombre" name="nombre" required autocomplete="off"
           placeholder="Ej. CURP, Acta de nacimiento, Comprobante de domicilio…"
           value="{{ $vNombre }}">
</div>

<div class="form-group">
    <label for="expDescripcion">Descripción <span style="color:#888;font-weight:400;">(opcional)</span></label>
    <textarea id="expDescripcion" name="descripcion" rows="2" autocomplete="off"
              placeholder="Indicaciones para quien sube el documento…">{{ $vDescripcion }}</textarea>
</div>

<div class="form-group">
    <label>Tipos de archivo permitidos</label>
    <div class="exp-checkbox-grid">
        @foreach($tiposArchivo as $ext => $label)
            <label class="exp-checkbox">
                <input type="checkbox" name="tipos_archivo[]" value="{{ $ext }}"
                       @checked(in_array($ext, $selectedTipos, true))>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="exp-inline-fields">
    <div class="form-group">
        <label for="expCantidad">Cantidad de archivos esperados</label>
        <input type="number" id="expCantidad" name="cantidad" min="1" max="20" required
               value="{{ $vCantidad }}">
    </div>
    <div class="form-group">
        <label for="expOrden">Orden <span style="color:#888;font-weight:400;">(opcional)</span></label>
        <input type="number" id="expOrden" name="orden" min="0" max="999"
               value="{{ $vOrden }}">
    </div>
</div>

<div class="exp-toggle-row">
    <span class="exp-toggle-label">¿Obligatorio?</span>
    <label class="switch" title="Obligatorio">
        {{-- hidden garantiza envío de "0" cuando el switch está apagado --}}
        <input type="hidden" name="obligatorio" value="0">
        <input type="checkbox" name="obligatorio" value="1" @checked($vObligatorio)>
        <span class="slider"></span>
    </label>
</div>

<div class="exp-toggle-row">
    <span class="exp-toggle-label">¿Activo?</span>
    <label class="switch" title="Activo">
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" name="activo" value="1" @checked($vActivo)>
        <span class="slider"></span>
    </label>
</div>

<input type="hidden" name="institution_id" value="{{ session('active_institution_id') }}">

<style>
    #modalBody .exp-checkbox-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        margin-top: 6px;
    }
    #modalBody .exp-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 400;
        cursor: pointer;
        white-space: nowrap;
    }
    #modalBody .exp-checkbox input { width: auto; margin: 0; }
    #modalBody .exp-inline-fields {
        display: flex;
        gap: 16px;
    }
    #modalBody .exp-inline-fields .form-group { flex: 1; }
    #modalBody .exp-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 10px 0;
    }
    #modalBody .exp-toggle-label { font-weight: 600; color: #0d2240; }
</style>
