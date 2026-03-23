<div class="form-group">
    <label for="department_id">Departamento</label>
    <select id="department_id" name="department_id" required>
        <option value="">Seleccione un departamento</option>
        
        @foreach($departments as $department)
            <option value="{{ $department->id }}"
                    {{ (isset($item) && $item->department_id == $department->id) ? 'selected' : '' }}>
                {{ $department->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="name">Nombre del Puesto</label>
    @php
        $currentValue = old('name', isset($item) ? $item->name : '');
        $activeInstitutionName = session('active_institution_name');
        $isUniversity = ($activeInstitutionName === 'Universidad Mundo Imperial');
    @endphp

    {{-- Select (API) para Pierre/Palacio/Princess --}}
    <select id="workstationNameSelect" name="name" required style="{{ $isUniversity ? 'display:none;' : '' }}">
        <option value="" selected>seleciona un Dato</option>
        @if(!empty($currentValue) && !$isUniversity)
            <option value="{{ $currentValue }}" selected>{{ $currentValue }}</option>
        @endif
    </select>

    {{-- Input (manual) para Universidad - se crea dinámicamente para que no estorbe --}}
    <div id="workstationNameInputLabel" style="{{ $isUniversity ? '' : 'display:none;' }}; margin-top: 8px; margin-bottom: 6px; font-weight: 600; color: #BC8A55;">
        Escriba el nombre del puesto
    </div>
    <div id="workstationNameInputContainer" style="{{ $isUniversity ? '' : 'display:none;' }}"></div>
</div>

<script>
    (function () {
        const select = document.getElementById('workstationNameSelect');
        const depSelect = document.getElementById('department_id');
        const inputContainer = document.getElementById('workstationNameInputContainer');
        const inputLabel = document.getElementById('workstationNameInputLabel');
        if (!select || !depSelect || !inputContainer || !inputLabel) return;

        const currentValue = (@json($currentValue) || '').trim();
        const activeInstitutionName = (@json($activeInstitutionName) || '').trim();
        const isUniversity = activeInstitutionName === 'Universidad Mundo Imperial';

        let input = null;

        function ensureInputElement() {
            if (input) return input;

            input = document.createElement('input');
            input.type = 'text';
            input.id = 'workstationNameInput';
            input.name = 'name';
            input.autocomplete = 'off';
            input.required = true;

            if (currentValue) input.value = currentValue;

            inputContainer.innerHTML = '';
            inputContainer.appendChild(input);
            return input;
        }

        if (isUniversity) {
            // Universidad: manual, no consume API
            select.style.display = 'none';
            select.disabled = true;
            select.required = false;

            inputLabel.style.display = 'block';
            inputContainer.style.display = 'block';
            ensureInputElement();
            return;
        }

        // Pierre/Palacio/Princess: API
        inputLabel.style.display = 'none';
        inputContainer.style.display = 'none';
        inputContainer.innerHTML = '';

        select.style.display = 'block';
        select.disabled = false;
        select.required = true;

        function keywordForInstitution(inst) {
            const x = (inst || '').toLowerCase();
            if (x.includes('palacio')) return 'palacio';
            if (x.includes('princess')) return 'princess';
            if (x.includes('pierre')) return 'pierre';
            return '';
        }

        async function fetchJson(endpoint) {
            const r = await fetch('/external-data?endpoint=' + encodeURIComponent(endpoint), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            if (!r.ok) throw new Error('HTTP ' + r.status + ' en ' + endpoint);
            return r.json();
        }

        async function getPropertyId() {
            const instKey = keywordForInstitution(activeInstitutionName);
            const data = await fetchJson('/api/external/propiedades');
            const props = Array.isArray(data) ? data : (data?.propiedades ?? data?.data ?? []);
            if (!Array.isArray(props) || props.length === 0) throw new Error('API: no devolvió propiedades');

            let match = null;
            if (instKey) {
                match = props.find(p => {
                    const n = (p?.nombre ?? p?.name ?? p?.descripcion ?? '').toString().toLowerCase();
                    return n.includes(instKey);
                });
            }
            if (!match) match = props[0];

            const propId =
                match?.id ??
                match?.id_propiedad ??
                match?.propertyId ??
                match?.property_id ??
                null;
            if (propId == null || propId === '') throw new Error('No se encontró propId');

            return propId;
        }

        async function getExternalDepartmentId(propId, internalDepartmentName) {
            const deptData = await fetchJson('/api/external/propiedades/' + propId + '/departamentos');
            const deps = Array.isArray(deptData) ? deptData : (deptData?.departamentos ?? deptData?.data ?? []);
            if (!Array.isArray(deps) || deps.length === 0) throw new Error('API: no devolvió departamentos');

            const normalize = (str) => {
                return (str ?? '')
                    .toString()
                    .normalize('NFD')
                    .replace(/\p{Diacritic}/gu, '')
                    .replace(/[^a-zA-Z0-9 ]/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim()
                    .toLowerCase();
            };

            const wanted = normalize(internalDepartmentName);
            if (!wanted) throw new Error('No hay nombre de departamento interno');

            // intentamos match por nombre del departamento
            const match = deps.find(d => {
                const n = normalize(d?.departamento_nombre ?? d?.departamentoNombre ?? d?.department_nombre ?? d?.department_name ?? d?.nombre ?? d?.name ?? '');
                return n && n === wanted;
            }) || deps.find(d => {
                const n = normalize(d?.departamento_nombre ?? d?.departamentoNombre ?? d?.department_nombre ?? d?.department_name ?? d?.nombre ?? d?.name ?? '');
                return n && n.includes(wanted);
            });

            const deptId =
                match?.id ??
                match?.id_departamento ??
                match?.departmentId ??
                match?.department_id ??
                match?.departamento_id ??
                null;

            if (deptId == null || deptId === '') throw new Error('No se encontró external deptId para: ' + internalDepartmentName);
            return deptId;
        }

        async function loadPositions() {
            const internalDepartmentName = depSelect.options[depSelect.selectedIndex]?.text || '';

            // Si no seleccionaste un departamento real (placeholder), no consultes la API.
            if (!depSelect.value || !internalDepartmentName || depSelect.selectedIndex === 0) {
                select.innerHTML = '';
                const ph2 = document.createElement('option');
                ph2.value = '';
                ph2.textContent = 'No hay puesto';
                ph2.disabled = true;
                ph2.selected = true;
                select.appendChild(ph2);
                return;
            }

            select.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = 'Cargando...';
            select.appendChild(ph);

            try {
                const propId = await getPropertyId();
                const externalDeptId = await getExternalDepartmentId(propId, internalDepartmentName);
                const posData = await fetchJson('/api/external/propiedades/' + propId + '/departamentos/' + externalDeptId + '/posiciones');

                const positions = Array.isArray(posData)
                    ? posData
                    : (posData?.posiciones ?? posData?.data ?? posData?.results ?? []);

                if (!Array.isArray(positions)) throw new Error('API: formato inesperado de posiciones');
                if (positions.length === 0) throw new Error('API: 0 posiciones');

                select.innerHTML = '';
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'seleciona un Dato';
                select.appendChild(placeholder);

                positions.forEach(p => {
                    const val = (
                        p?.posicion_nombre ??
                        p?.posicionNombre ??
                        p?.position_name ??
                        p?.department_name ??
                        p?.nombre ??
                        p?.name ??
                        p?.descripcion ??
                        p?.titulo ??
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
            } catch (e) {
                console.error('No se pudieron cargar posiciones:', e);
                select.innerHTML = '';
                const ph2 = document.createElement('option');
                ph2.value = '';
                ph2.textContent = 'No hay puesto';
                ph2.disabled = true;
                select.appendChild(ph2);
            }
        }

        // cargar inicial
        loadPositions().catch(() => {});

        // recargar cuando cambie el departamento interno
        depSelect.addEventListener('change', () => {
            loadPositions().catch(() => {});
        });
    })();
</script>