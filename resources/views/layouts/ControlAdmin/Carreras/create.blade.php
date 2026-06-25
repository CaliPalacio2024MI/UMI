<div id="createCareerModal" class="modal-overlay">
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="createCareerModalLabel">Agregar Carrera</h5>
            <button type="button" id="closeModalBtn" class="close-custom">&times;</button>
        </div>

        <div class="modal-body-custom" id="modalBodyContent">

            <form method="post" action="{{ route('control.careers.store') }}">
                @csrf

                @include('layouts.ControlAdmin.Carreras.partials.form_errors_alert')

                <div class="form-field">
                    <label for="career_classification_id" class="career-classification-label--opens-modal" title="Clic para agregar o administrar clasificaciones">Clasificación:</label>
                    @isset($lockedClassification)
                        {{-- Estás dentro de esta clasificación: queda fija --}}
                        <input type="hidden" name="career_classification_id" value="{{ $lockedClassification->id }}">
                        <select id="career_classification_id" disabled style="background:#f5f5f5; cursor:default;">
                            <option selected>{{ $lockedClassification->name }}</option>
                        </select>
                    @else
                        <select id="career_classification_id" name="career_classification_id"
                            class="@error('career_classification_id') validation-error @enderror @if(blank(old('career_classification_id'))) placeholder @endif">
                            <option value="" class="placeholder-option">Seleccione una clasificación</option>
                            @foreach ($careerClassifications ?? [] as $clasificacion)
                                <option value="{{ $clasificacion->id }}" @selected(old('career_classification_id') == $clasificacion->id)>
                                    {{ $clasificacion->name }}
                                </option>
                            @endforeach
                        </select>
                    @endisset
                </div>

                <div class="form-field">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" placeholder="Ingrese el nombre de la carrera"
                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+"
                        title="Solo letras y espacios"
                        class="@error('name') validation-error @enderror" value="{{ old('name') }}">
                </div>

                <div class="form-field">
                    <label for="official_id">RVOE: Acuerdo Número:</label>
                    <input type="text" id="official_id" name="official_id" placeholder="Ingrese el RVOE"
                        pattern="[A-Za-z0-9\/\-\s]+"
                        title="Solo letras, números, espacios, diagonal y guion"
                        class="@error('official_id') validation-error @enderror" value="{{ old('official_id') }}">
                </div>

                <div class="form-field">
                    <label for="description">Descripción:</label>
                    <textarea id="description" name="description" rows="3" placeholder="Ingrese la descripción"
                        class="@error('description') validation-error @enderror">{{ old('description') }}</textarea>
                </div>

                <div class="options">

                    <div class="form-field Checkboxes">
                        <label>Modalidad:</label>
                        <div>
                            <input type="radio" id="type_presencial" name="type" value="Presencial" {{ old('type') == 'Presencial' ? 'checked' : '' }}>
                            <label for="type_presencial">Presencial:</label>
                        </div>
                        <div>
                            <input type="radio" id="type_enlinea" name="type" value="En linea" {{ old('type') == 'En linea' ? 'checked' : '' }}>
                            <label for="type_enlinea">En linea:</label>
                        </div>
                    </div>

                    <div class="form-field lists">
                        <label for="semesters">No. de semestres:</label>
                        <input type="number" id="semesters" name="semesters" min="1" step="1"
                            class="js-career-semestres @error('semesters') validation-error @enderror"
                            value="{{ old('semesters', 1) }}">
                    </div>
                </div>

                @php
                    $pricingMode = old('pricing_mode', 'uniform');
                @endphp
                <div class="form-field">
                    <label for="pricing_mode">Configuración de mensualidad:</label>
                    <select id="pricing_mode" name="pricing_mode"
                        class="js-career-pricing-mode @error('pricing_mode') validation-error @enderror">
                        <option value="uniform" {{ $pricingMode === 'uniform' ? 'selected' : '' }}>Precio único</option>
                        <option value="per_month" {{ $pricingMode === 'per_month' ? 'selected' : '' }}>Precio por mes</option>
                    </select>
                </div>
                <div class="form-field js-career-uniform-wrap" @if($pricingMode === 'per_month') style="display: none;" @endif>
                    <label for="monto_mensualidad">Precio total :</label>
                    <input type="number" id="monto_mensualidad" name="monto_mensualidad" step="0.01" min="0"
                        placeholder="0.00"
                        class="js-career-monto @error('monto_mensualidad') validation-error @enderror"
                        value="{{ old('monto_mensualidad') }}">
                </div>
                <div class="form-field js-career-months-wrap" @if($pricingMode !== 'per_month') style="display: none;" @endif>
                    <label>Precio por mes:</label>
                    <div class="js-career-months-fields" data-old-monthly='@json(old("monthly_prices", []))'></div>
                </div>
                <div class="form-field">
                    <label for="porcentaje_cargo_moratorio">Porcentaje de cargo moratorio:</label>
                    <input type="number" id="porcentaje_cargo_moratorio" name="porcentaje_cargo_moratorio" step="0.01" min="0" max="100"
                        placeholder="0.00"
                        class="js-career-porcentaje @error('porcentaje_cargo_moratorio') validation-error @enderror"
                        value="{{ old('porcentaje_cargo_moratorio', 0) }}">
                </div>
                <div class="form-field">
                    <label for="createCareerCargoMonetario">Cargo moratorio:</label>
                    <input type="text" id="createCareerCargoMonetario" class="js-career-cargo-out" readonly tabindex="-1"
                        value=""
                        style="background: #f5f5f5; cursor: default;">
                </div>

                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">+ Agregar</button>
                </div>
            </form>

        </div>
    </div>
</div>