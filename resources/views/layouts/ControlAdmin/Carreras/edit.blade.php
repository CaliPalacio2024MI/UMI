<div id="editCareerModal_{{ $career->id }}" class="modal-overlay"> 
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="editCareerModalLabel">Editar Carrera</h5>
            <button type="button" id="closeEditModalBtn_{{ $career->id }}" class="close-custom">&times;</button>
        </div>
        
        <div class="modal-body-custom" id="modalBodyContentEdit">
            
            <form method="post" action="{{ route('control.careers.update', $career->id) }}">
                @csrf
                @method('PUT')

                @include('layouts.ControlAdmin.Carreras.partials.form_errors_alert')

                @php
                    $selClassId = old('career_classification_id', $career->career_classification_id);
                @endphp
                <div class="form-field">
                    <label for="career_classification_id_{{ $career->id }}">Clasificación:</label>
                    <select id="career_classification_id_{{ $career->id }}" name="career_classification_id"
                        class="@error('career_classification_id') validation-error @enderror @if(blank($selClassId)) placeholder @endif">
                        <option value="" class="placeholder-option">Seleccione una clasificación</option>
                        @foreach ($careerClassifications ?? [] as $clasificacion)
                            <option value="{{ $clasificacion->id }}" @selected((string) $selClassId === (string) $clasificacion->id)>
                                {{ $clasificacion->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 1. Nombre --}}
                <div class="form-field">
                    <label for="name_{{ $career->id }}">Nombre:</label>
                    <input type="text" id="name_{{ $career->id }}" name="name" 
                           pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+"
                           title="Solo letras y espacios"
                           class="@error('name') validation-error @enderror"
                           value="{{ old('name', $career->name) }}">
                </div>

                {{-- 2. RVOE --}}
                <div class="form-field">
                    <label for="official_id_{{ $career->id }}">RVOE: Acuerdo Número:</label>
                    <input type="text" id="official_id_{{ $career->id }}" name="official_id" 
                           pattern="[A-Za-z0-9\/\-\s]+"
                           title="Solo letras, números, espacios, diagonal y guion"
                           class="@error('official_id') validation-error @enderror"
                           value="{{ old('official_id', $career->official_id) }}">
                </div>

                {{-- 3. Descripción --}}
                <div class="form-field">
                    <label for="description_{{ $career->id }}">Descripción:</label>
                    <textarea id="description_{{ $career->id }}" name="description" rows="3"
                           class="@error('description') validation-error @enderror">{{ old('description', $career->description1) }}</textarea>
                </div>

                <div class="options">
                    {{-- 6. Modalidad (Radios) --}}
                    <div class="Checkboxes form-field">
                        <label>Modalidad:</label>
                        @php $currentType = old('type', $career->type); @endphp
                        <div>
                            <input type="radio" id="type_presencial_{{ $career->id }}" name="type" value="Presencial" 
                                   {{ $currentType == 'Presencial' ? 'checked' : '' }}>
                            <label for="type_presencial_{{ $career->id }}">Presencial:</label>
                        </div>
                        <div>
                            <input type="radio" id="type_enlinea_{{ $career->id }}" name="type" value="En linea" 
                                   {{ $currentType == 'En linea' ? 'checked' : '' }}>
                            <label for="type_enlinea_{{ $career->id }}">En linea:</label>
                        </div>
                    </div>

                    {{-- 7. Semestres (select) --}}
                    <div class="lists form-field">
                        <label for="semesters_{{ $career->id }}">No. de semestres:</label>
                        @php $currentSemesters = old('semesters', $career->semesters); @endphp
                        <input type="number" id="semesters_{{ $career->id }}" name="semesters" min="1" step="1"
                            class="js-career-semestres @error('semesters') validation-error @enderror"
                            value="{{ $currentSemesters }}">
                    </div>
                </div>

                @php
                    $montoEdit = old('monto_mensualidad', $career->monto_mensualidad);
                    $semEdit = (int) old('semesters', $career->semesters);
                    $pricingModeEdit = old('pricing_mode', $career->pricing_mode ?: 'uniform');
                    $monthlyPricesEdit = old('monthly_prices', $career->monthly_prices ?? []);
                    $pctMoratorioEdit = old('porcentaje_cargo_moratorio', $career->porcentaje_cargo_moratorio ?? 0);
                    $cargoPreview = '';
                    if ($pricingModeEdit === 'per_month' && is_array($monthlyPricesEdit)) {
                        $precioTotalEdit = (float) collect($monthlyPricesEdit)->sum();
                        $cargoPreview = number_format($precioTotalEdit * ((float) $pctMoratorioEdit / 100), 2, '.', '');
                    } elseif ($montoEdit !== null && is_numeric($montoEdit)) {
                        $precioTotalEdit = (float) $montoEdit * max(1, $semEdit);
                        $cargoPreview = number_format($precioTotalEdit * ((float) $pctMoratorioEdit / 100), 2, '.', '');
                    }
                @endphp
                <div class="form-field">
                    <label for="pricing_mode_{{ $career->id }}">Configuración de mensualidad:</label>
                    <select id="pricing_mode_{{ $career->id }}" name="pricing_mode"
                        class="js-career-pricing-mode @error('pricing_mode') validation-error @enderror">
                        <option value="uniform" {{ $pricingModeEdit === 'uniform' ? 'selected' : '' }}>Precio único</option>
                        <option value="per_month" {{ $pricingModeEdit === 'per_month' ? 'selected' : '' }}>Precio por mes</option>
                    </select>
                </div>
                <div class="form-field js-career-uniform-wrap" @if($pricingModeEdit === 'per_month') style="display: none;" @endif>
                    <label for="monto_mensualidad_{{ $career->id }}">Monto mensualidad (general):</label>
                    <input type="number" id="monto_mensualidad_{{ $career->id }}" name="monto_mensualidad" step="0.01" min="0"
                        placeholder="0.00"
                        class="js-career-monto @error('monto_mensualidad') validation-error @enderror"
                        value="{{ $montoEdit !== null && is_numeric($montoEdit) ? $montoEdit : '' }}">
                </div>
                <div class="form-field js-career-months-wrap" @if($pricingModeEdit !== 'per_month') style="display: none;" @endif>
                    <label>Precio por mes:</label>
                    <div class="js-career-months-fields" data-old-monthly='@json($monthlyPricesEdit)'></div>
                </div>
                <div class="form-field">
                    <label for="porcentaje_cargo_moratorio_{{ $career->id }}">Porcentaje de cargo moratorio:</label>
                    <input type="number" id="porcentaje_cargo_moratorio_{{ $career->id }}" name="porcentaje_cargo_moratorio" step="0.01" min="0" max="100"
                        placeholder="0.00"
                        class="js-career-porcentaje @error('porcentaje_cargo_moratorio') validation-error @enderror"
                        value="{{ is_numeric($pctMoratorioEdit) ? $pctMoratorioEdit : 0 }}">
                </div>
                <div class="form-field">
                    <label for="cargo_monetario_{{ $career->id }}">Cargo moratorio:</label>
                    <input type="text" id="cargo_monetario_{{ $career->id }}" class="js-career-cargo-out" readonly tabindex="-1"
                        value="{{ $cargoPreview }}"
                        style="background: #f5f5f5; cursor: default;">
                </div>
                @php
                    $fvMoratorio = $career->fecha_vencimiento_moratorio;
                    $fvMoratorioTxt = $fvMoratorio ? \Carbon\Carbon::parse($fvMoratorio)->format('d/m/Y') : '—';
                @endphp
                <div class="form-field">
                    <label for="fecha_vencimiento_moratorio_display_{{ $career->id }}">Fecha vencimiento (asignada por sistema):</label>
                    <input type="text" id="fecha_vencimiento_moratorio_display_{{ $career->id }}" readonly tabindex="-1"
                        value="{{ $fvMoratorioTxt }}"
                        style="background: #f5f5f5; cursor: default;">
                </div>
                
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">+ Actualizar</button>
                </div>
            </form>

        </div>
    </div>
</div>