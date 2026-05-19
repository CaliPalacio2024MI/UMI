@php
    $currentValue = old('name', isset($item) ? $item->name : '');
@endphp

<div class="form-group tipo-unidad-flags-group">
    <p class="tipo-unidad-question" id="tipoUnidadQuestion">Tipo de Unidad</p>
    <input type="hidden" name="is_administrativo" value="0">
    <input type="hidden" name="is_universidad" value="0">
    <div class="tipo-unidad-checkboxes-row" role="group" aria-labelledby="tipoUnidadQuestion">
        <div class="checkbox-inline">
            <input type="checkbox" id="flag_administrativo" name="is_administrativo" value="1" @checked(old('is_administrativo', isset($item) && $item->is_administrativo ? '1' : '0') === '1')>
            <label for="flag_administrativo">Propiedad</label>
        </div>
        <span class="tipo-unidad-separator" aria-hidden="true">/</span>
        <div class="checkbox-inline">
            <input type="checkbox" id="flag_universidad" name="is_universidad" value="1" @checked(old('is_universidad', isset($item) && $item->is_universidad ? '1' : '0') === '1')>
            <label for="flag_universidad">Universidad</label>
        </div>
    </div>
</div>

<div class="form-group">
    {{-- Campo para Pierre/Palacio/Princess (se llena desde la API) --}}
    <select id="nameSelect" name="name" disabled>
        <option value="">-- Seleccione Unidad de Negocio --</option>
        @if(!empty($currentValue))
            <option value="{{ $currentValue }}" selected>{{ $currentValue }}</option>
        @endif
    </select>

    {{-- Campo para Universidad (escribible) - se crea dinámicamente en cliente --}}
    <div id="nameInputLabel" style="display:none; margin-top: 8px; margin-bottom: 6px; font-weight: 600; color: #BC8A55;">
        Escriba su unidad de negocio
    </div>
    <div id="nameInputContainer" style="display:none;"></div>
</div>
<div class="form-group">
    <label for="logo_path">Logo</label>
    <input type="file" id="logo_path" name="logo_path" accept="image/*">


    @if(isset($item) && $item->logo_path)
        <div style="margin-top: 10px;">
            <img src="{{ asset('storage/' . $item->logo_path) }}" alt="Logo actual" style="max-width: 100px; max-height: 50px; border-radius: 4px;">
            <small style="display: block; color: #555;">Logo actual. Selecciona un archivo para reemplazarlo.</small>
        </div>
    @endif
</div>
<script>
(function () {

    const select = document.getElementById('nameSelect');
    const flagAdministrativo = document.getElementById('flag_administrativo');
    const flagUniversidad = document.getElementById('flag_universidad');
    const inputContainer = document.getElementById('nameInputContainer');
    const nameInputLabel = document.getElementById('nameInputLabel');

    if (
        !select ||
        !flagAdministrativo ||
        !flagUniversidad ||
        !inputContainer ||
        !nameInputLabel
    ) {
        return;
    }

    const currentValue =
        (@json($currentValue) || '').trim();

    const activeInstitutionId =
        @json(session('active_institution_id'));

    let cachedPropiedades = null;
    let input = null;

    function isUniversidadFlagOn() {
        return flagUniversidad.checked;
    }

    function isAdministrativoFlagOn() {
        return flagAdministrativo.checked;
    }

    function ensureInputElement() {

        if (input) {
            return input;
        }

        input = document.createElement('input');

        input.type = 'text';
        input.id = 'nameInput';
        input.name = 'name';
        input.required = true;
        input.autocomplete = 'off';

        if (currentValue) {
            input.value = currentValue;
        }

        inputContainer.innerHTML = '';
        inputContainer.appendChild(input);

        return input;
    }

    function setMode() {

        if (isUniversidadFlagOn()) {

            ensureInputElement();

            inputContainer.style.display = 'block';
            nameInputLabel.style.display = 'block';

            select.style.display = 'none';
            select.disabled = true;

            return;
        }

        inputContainer.style.display = 'none';
        nameInputLabel.style.display = 'none';

        if (input) {
            input.remove();
            input = null;
        }

        if (isAdministrativoFlagOn()) {

            select.style.display = 'block';
            select.disabled = false;

            return;
        }

        select.style.display = 'none';
        select.disabled = true;
    }

    function populateSelect(propiedades) {

        select.innerHTML = '';

        const placeholder =
            document.createElement('option');

        placeholder.value = '';
        placeholder.textContent =
            '-- Seleccione Unidad de Negocio --';

        select.appendChild(placeholder);

        propiedades.forEach(propiedad => {

            const optionValue =
                (
                    propiedad?.nombre ??
                    propiedad?.name ??
                    ''
                ).toString().trim();

            if (!optionValue) {
                return;
            }

            const option =
                document.createElement('option');

            option.value = optionValue;
            option.textContent = optionValue;

            select.appendChild(option);
        });

        if (currentValue) {
            select.value = currentValue;
        }
    }

    async function loadPropiedades() {

        if (
            !isAdministrativoFlagOn() ||
            isUniversidadFlagOn()
        ) {
            return;
        }

        if (cachedPropiedades) {

            populateSelect(cachedPropiedades);

            return;
        }

        try {

            const response = await fetch(
                '/external-data?endpoint=/api/external/propiedades',
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) {
                throw new Error(
                    'HTTP ' + response.status
                );
            }

            const json = await response.json();

            let propiedades =
                json?.data ??
                json?.propiedades ??
                json;

            if (!Array.isArray(propiedades)) {
                throw new Error(
                    'La API no devolvió un arreglo'
                );
            }

            /*
            FILTRAR SOLO LA PROPIEDAD
            RELACIONADA A LA INSTITUCIÓN ACTUAL
            */

            propiedades = propiedades.filter(p => {

                return Number(
                    p.id_propiedad
                ) === Number(
                    @json(
                        \App\Models\Users\Institution::find(
                            session('active_institution_id')
                        )->external_property_id ?? 0
                    )
                );

            });

            cachedPropiedades = propiedades;

            populateSelect(propiedades);

        } catch (error) {

            console.error(
                'Error cargando propiedades:',
                error
            );

            select.innerHTML =
                '<option>Error cargando datos</option>';
        }
    }

    async function applyMode() {

        setMode();

        if (
            isAdministrativoFlagOn() &&
            !isUniversidadFlagOn()
        ) {
            await loadPropiedades();
        }
    }

    flagAdministrativo.addEventListener(
        'change',
        () => {

            if (flagAdministrativo.checked) {
                flagUniversidad.checked = false;
            }

            applyMode();
        }
    );

    flagUniversidad.addEventListener(
        'change',
        () => {

            if (flagUniversidad.checked) {
                flagAdministrativo.checked = false;
            }

            applyMode();
        }
    );

    applyMode();

})();
</script>
