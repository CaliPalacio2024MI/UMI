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

<script>
    (function () {
        const select = document.getElementById('departmentNameSelect');
        const inputContainer = document.getElementById('departmentNameInputContainer');
        const inputLabel = document.getElementById('departmentNameInputLabel');
        if (!select || !inputContainer || !inputLabel) return;

        const currentValue = (@json($currentValue) || '').trim();
        const activeInstitutionName = (@json($activeInstitutionName) || '').trim();
        const isUniversity = activeInstitutionName === 'Universidad Mundo Imperial';

        function ensureInput() {
            let input = document.getElementById('departmentNameInput');
            if (input) return input;

            input = document.createElement('input');
            input.type = 'text';
            input.id = 'departmentNameInput';
            input.name = 'name';
            input.required = true;
            input.autocomplete = 'off';
            if (currentValue) input.value = currentValue;

            inputContainer.innerHTML = '';
            inputContainer.appendChild(input);
            return input;
        }

        if (isUniversity) {
            // Universidad: manual
            select.style.display = 'none';
            select.disabled = true;
            select.required = false;

            inputLabel.style.display = 'block';
            inputContainer.style.display = 'block';
            ensureInput();
            return;
        }

        // Pierre/Palacio/Princess: API
        inputLabel.style.display = 'none';
        inputContainer.style.display = 'none';
        inputContainer.innerHTML = '';

        select.style.display = 'block';
        select.disabled = false;
        select.required = true;

        // 1) Traer propiedades de la API externa
        fetch('/external-data?endpoint=/api/external/propiedades', {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(r => {
            if (!r.ok) throw new Error('Propiedades: HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            const props = Array.isArray(data) ? data : (data?.propiedades ?? data?.data ?? []);
            if (!Array.isArray(props) || props.length === 0) throw new Error('La API no devolvió propiedades');

            const instLower = (activeInstitutionName || '').toLowerCase();

            // Para Palacio Mundo Imperial: buscar propiedad que contenga "palacio"
            let match = null;
            if (instLower.includes('palacio')) {
                match = props.find(p => {
                    const n = (p?.nombre ?? p?.name ?? p?.descripcion ?? '').toString().toLowerCase();
                    return n.includes('palacio');
                });
                if (!match) throw new Error('No se encontró propiedad "palacio" en la API');
            } else if (instLower.includes('princess')) {
                match = props.find(p => {
                    const n = (p?.nombre ?? p?.name ?? p?.descripcion ?? '').toString().toLowerCase();
                    return n.includes('princess');
                });
                if (!match) throw new Error('No se encontró propiedad "princess" en la API');
            } else if (instLower.includes('pierre')) {
                match = props.find(p => {
                    const n = (p?.nombre ?? p?.name ?? p?.descripcion ?? '').toString().toLowerCase();
                    return n.includes('pierre');
                });
                if (!match) throw new Error('No se encontró propiedad "pierre" en la API');
            } else {
                // Sin fallback: nunca mezclar departamentos de otra propiedad.
                throw new Error(
                    'Catálogo de departamentos por API solo aplica en Palacio, Princess o Pierre. ' +
                    'Unidad activa: "' + (activeInstitutionName || '(sin nombre)') + '".'
                );
            }

            const propId =
                match?.id ??
                match?.id_propiedad ??
                match?.propertyId ??
                match?.property_id ??
                match?.propiedad_id ??
                null;
            if (propId == null || propId === '') {
                throw new Error('No se encontró ID de propiedad para ' + (activeInstitutionName || 'esta unidad'));
            }

            // 2) Traer departamentos de esa propiedad (Palacio / Princess / Pierre)
            return fetch('/external-data?endpoint=/api/external/propiedades/' + encodeURIComponent(propId) + '/departamentos', {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }).then(r => {
                if (!r.ok) throw new Error('Departamentos: HTTP ' + r.status);
                return r.json();
            }).then(deptData => ({ deptData, propId }));
        })
        .then(({ deptData, propId }) => {
            // Intentamos varias rutas comunes para encontrar un array de departamentos
            let deps = [];
            if (Array.isArray(deptData)) deps = deptData;
            else if (Array.isArray(deptData?.departamentos)) deps = deptData.departamentos;
            else if (Array.isArray(deptData?.departamento)) deps = deptData.departamento;
            else if (Array.isArray(deptData?.departments)) deps = deptData.departments;
            else if (Array.isArray(deptData?.items)) deps = deptData.items;
            else if (Array.isArray(deptData?.results)) deps = deptData.results;
            else if (Array.isArray(deptData?.data?.departamentos)) deps = deptData.data.departamentos;
            else if (Array.isArray(deptData?.data?.items)) deps = deptData.data.items;
            else if (Array.isArray(deptData?.data)) deps = deptData.data;

            if (!Array.isArray(deps)) throw new Error('La API no devolvió lista de departamentos');
            if (deps.length === 0) throw new Error('La API devolvió 0 departamentos para propId=' + propId);

            select.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = 'Selecione un Dato';
            select.appendChild(ph);

            deps.forEach(d => {
                const val = (
                    d?.departamento_nombre ??
                    d?.departamentoNombre ??
                    d?.department_nombre ??
                    d?.department_name ??
                    d?.nombre ??
                    d?.name ??
                    d?.descripcion ??
                    d?.titulo ??
                    ''
                ).toString().trim();
                if (!val) return;
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val;
                select.appendChild(opt);
            });

            if (currentValue) {
                const found = Array.from(select.options).some(o => o.value === currentValue);
                if (found) select.value = currentValue;
            }
        })
        .catch(err => {
            console.error('No se pudo cargar departamentos desde la API:', err);
            select.innerHTML = '<option value="">Selecione un Dato</option><option value="" disabled>— Error al cargar. Revisa consola (F12). —</option>';
        });
    })();
</script>