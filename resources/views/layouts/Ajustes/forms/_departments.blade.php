<div class="form-group">
    <label for="name">Nombre del Departamento</label>
    @php
        $currentValue = old('name', isset($item) ? $item->name : '');
        $activeInstitutionName = session('active_institution_name');
        $isUniversity = ($activeInstitutionName === 'Universidad Mundo Imperial');
    @endphp

    {{-- Select (API) para Pierre/Palacio/Princess --}}
    <select id="departmentNameSelect" name="name" required style="{{ $isUniversity ? 'display:none;' : '' }}">
        <option value="" selected>Selecione un Dato</option>
        @if(!empty($currentValue) && !$isUniversity)
            <option value="{{ $currentValue }}" selected>{{ $currentValue }}</option>
        @endif
    </select>

    {{-- Input (manual) para Universidad - se crea dinámicamente para que no exista en DOM si no aplica --}}
    <div id="departmentNameInputLabel" style="{{ $isUniversity ? '' : 'display:none;' }}; margin-top: 8px; margin-bottom: 6px; font-weight: 600; color: #BC8A55;">
        Escriba el nombre del departamento
    </div>
    <div id="departmentNameInputContainer" style="{{ $isUniversity ? '' : 'display:none;' }}"></div>
</div>

<input type="hidden" name="institution_id" value="{{ session('active_institution_id') }}">
@php

$institution = \App\Models\Users\Institution::find(
    session('active_institution_id')
);

$externalPropertyId =
    $institution->external_property_id ?? null;

@endphp

<script>
(function () {

    const select = document.getElementById('departmentNameSelect');
    const inputContainer = document.getElementById('departmentNameInputContainer');
    const inputLabel = document.getElementById('departmentNameInputLabel');

    if (!select || !inputContainer || !inputLabel) {
        return;
    }

    const currentValue =
        (@json($currentValue) || '').trim();

    const activeInstitutionName =
        (@json($activeInstitutionName) || '').trim();

    const externalPropertyId =
        @json($externalPropertyId);

    const isUniversity =
        activeInstitutionName === 'Universidad Mundo Imperial';

    /*
    |--------------------------------------------------------------------------
    | INPUT MANUAL PARA UNIVERSIDAD
    |--------------------------------------------------------------------------
    */

    function ensureInput() {

        let input =
            document.getElementById('departmentNameInput');

        if (input) {
            return input;
        }

        input = document.createElement('input');

        input.type = 'text';
        input.id = 'departmentNameInput';
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

    /*
    |--------------------------------------------------------------------------
    | UNIVERSIDAD = INPUT MANUAL
    |--------------------------------------------------------------------------
    */

    if (isUniversity) {

        select.style.display = 'none';
        select.disabled = true;
        select.required = false;

        inputLabel.style.display = 'block';
        inputContainer.style.display = 'block';

        ensureInput();

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | PROPIEDADES = SELECT DESDE API
    |--------------------------------------------------------------------------
    */

    inputLabel.style.display = 'none';

    inputContainer.style.display = 'none';

    inputContainer.innerHTML = '';

    select.style.display = 'block';

    select.disabled = false;

    select.required = true;

    /*
    |--------------------------------------------------------------------------
    | VALIDAR PROPERTY ID
    |--------------------------------------------------------------------------
    */

    if (!externalPropertyId) {

        console.error(
            'La institución no tiene external_property_id'
        );

        select.innerHTML =
            '<option value="">No existe external_property_id</option>';

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | CARGAR DEPARTAMENTOS
    |--------------------------------------------------------------------------
    */

    fetch(
        '/external-data?endpoint=/api/external/propiedades/' +
        externalPropertyId +
        '/departamentos',
        {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }
    )
    .then(r => {

        if (!r.ok) {

            throw new Error(
                'HTTP ' + r.status
            );
        }

        return r.json();
    })
    .then(deptData => {

        console.log(
            'Departamentos API:',
            deptData
        );

        let deps = [];

        if (Array.isArray(deptData)) {

            deps = deptData;

        } else if (Array.isArray(deptData?.data)) {

            deps = deptData.data;

        } else if (
            Array.isArray(deptData?.departamentos)
        ) {

            deps = deptData.departamentos;
        }

        if (!Array.isArray(deps)) {

            throw new Error(
                'La API no devolvió departamentos'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LIMPIAR SELECT
        |--------------------------------------------------------------------------
        */

        select.innerHTML = '';

        const ph =
            document.createElement('option');

        ph.value = '';

        ph.textContent =
            'Seleccione un Departamento';

        select.appendChild(ph);

        /*
        |--------------------------------------------------------------------------
        | INSERTAR DEPARTAMENTOS
        |--------------------------------------------------------------------------
        */

        deps.forEach(d => {

            const val = (
                d?.departamento_nombre ??
                d?.department_name ??
                d?.nombre ??
                d?.name ??
                ''
            ).toString().trim();

            if (!val) {
                return;
            }

            const opt =
                document.createElement('option');

            opt.value = val;

            opt.textContent = val;

            select.appendChild(opt);
        });

        /*
        |--------------------------------------------------------------------------
        | MODO EDICIÓN
        |--------------------------------------------------------------------------
        */

        if (currentValue) {

            const found =
                Array.from(select.options)
                .some(o => o.value === currentValue);

            if (found) {

                select.value = currentValue;
            }
        }

    })
    .catch(err => {

        console.error(
            'Error cargando departamentos:',
            err
        );

        select.innerHTML =
            '<option value="">Error cargando departamentos</option>';
    });

})();
</script>
