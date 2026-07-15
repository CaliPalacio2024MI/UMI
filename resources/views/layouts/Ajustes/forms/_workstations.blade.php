@php
    $currentValue = old('name', isset($item) ? $item->name : '');
    $activeInstitutionName = session('active_institution_name');
    $isUniversity = ($activeInstitutionName === 'Universidad Mundo Imperial');
@endphp

@if($isUniversity)
    {{-- =============================================================== --}}
    {{-- UNIVERSIDAD: Departamento (local) + Nombre del Puesto manual     --}}
    {{-- =============================================================== --}}
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
        <label for="workstationNameInput">Nombre del Puesto</label>
        <input type="text" id="workstationNameInput" name="name" required autocomplete="off" value="{{ $currentValue }}">
    </div>
@else
    {{-- =============================================================== --}}
    {{-- PROPIEDAD: 1) propiedad  2) departamento  3) puesto (API)        --}}
    {{-- =============================================================== --}}
    <div class="form-group">
        <label for="propiedadSelect">Propiedad</label>
        <select id="propiedadSelect" required>
            <option value="">-- Seleccione propiedad --</option>
        </select>
    </div>

    <div class="form-group">
        <label for="department_id">Departamento</label>
        {{-- El value es el id LOCAL del departamento (para guardar); data-name = nombre para buscar en la API --}}
        <select id="department_id" name="department_id" required>
            <option value="">-- Seleccione departamento --</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" data-name="{{ $department->name }}"
                        {{ (isset($item) && $item->department_id == $department->id) ? 'selected' : '' }}>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="workstationNameSelect">Nombre del Puesto</label>
        <select id="workstationNameSelect" name="name" required disabled>
            <option value="">-- Primero seleccione un departamento --</option>
            @if(!empty($currentValue))
                <option value="{{ $currentValue }}" selected>{{ $currentValue }}</option>
            @endif
        </select>
    </div>
@endif

@unless($isUniversity)
<script>
    (function () {
        const propSelect = document.getElementById('propiedadSelect');
        const depSelect = document.getElementById('department_id');
        const posSelect = document.getElementById('workstationNameSelect');
        if (!propSelect || !depSelect || !posSelect) return;

        const currentValue = (@json($currentValue) || '').trim();
        const activeInstitutionName = (@json($activeInstitutionName) || '').trim();

        function fetchJson(endpoint) {
            return fetch('/external-data?endpoint=' + encodeURIComponent(endpoint), {
                method: 'GET',
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
        }

        function extractArray(data, keys) {
            if (Array.isArray(data)) return data;
            for (const k of keys) { if (Array.isArray(data?.[k])) return data[k]; }
            if (Array.isArray(data?.data)) return data.data;
            for (const k of keys) { if (Array.isArray(data?.data?.[k])) return data.data[k]; }
            return [];
        }

        function normalize(str) {
            return (str ?? '').toString()
                .normalize('NFD').replace(/\p{Diacritic}/gu, '')
                .replace(/[^a-zA-Z0-9 ]/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
        }

        // --- 1) Cargar propiedades y preseleccionar la de la unidad activa ---
        fetchJson('/api/external/propiedades')
            .then(data => {
                const props = extractArray(data, ['propiedades']);
                if (props.length === 0) throw new Error('La API no devolvió propiedades');

                propSelect.innerHTML = '<option value="">-- Seleccione propiedad --</option>';
                const activeNorm = normalize(activeInstitutionName);
                let preselectId = '';

                props.forEach(p => {
                    const id = p?.id ?? p?.id_propiedad ?? p?.propiedad_id ?? p?.property_id ?? '';
                    const name = (p?.nombre ?? p?.name ?? p?.descripcion ?? ('Propiedad ' + id)).toString();
                    if (id === '') return;
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = name;
                    propSelect.appendChild(opt);
                    // Preseleccionar la propiedad que coincide con la unidad activa.
                    if (activeNorm && normalize(name) && (normalize(name).includes(activeNorm) || activeNorm.includes(normalize(name)))) {
                        preselectId = String(id);
                    }
                });

                if (preselectId) {
                    propSelect.value = preselectId;
                    // Si venías editando (ya hay departamento), carga los puestos.
                    if (depSelect.value) loadPositions();
                }
            })
            .catch(err => {
                console.error('No se pudieron cargar las propiedades desde la API:', err);
                propSelect.innerHTML = '<option value="">— Error al cargar propiedades (F12) —</option>';
            });

        // --- 3) Cargar los puestos (posiciones) del departamento elegido ---
        async function loadPositions() {
            const propId = propSelect.value;
            const depName = depSelect.options[depSelect.selectedIndex]?.dataset?.name
                || depSelect.options[depSelect.selectedIndex]?.text || '';

            if (!propId) {
                posSelect.disabled = true;
                posSelect.innerHTML = '<option value="">-- Primero seleccione una propiedad --</option>';
                return;
            }
            if (!depSelect.value || !depName) {
                posSelect.disabled = true;
                posSelect.innerHTML = '<option value="">-- Primero seleccione un departamento --</option>';
                return;
            }

            posSelect.disabled = true;
            posSelect.innerHTML = '<option value="">Cargando...</option>';

            try {
                // Buscar el id del departamento en la API por su nombre.
                const deptData = await fetchJson('/api/external/propiedades/' + encodeURIComponent(propId) + '/departamentos');
                const deps = extractArray(deptData, ['departamentos', 'departamento', 'departments', 'items', 'results']);
                const wanted = normalize(depName);
                const match = deps.find(d => normalize(d?.departamento_nombre ?? d?.department_name ?? d?.nombre ?? d?.name) === wanted)
                    || deps.find(d => normalize(d?.departamento_nombre ?? d?.department_name ?? d?.nombre ?? d?.name).includes(wanted));

                const apiDeptId = match?.id_departamento ?? match?.departamento_id ?? match?.department_id ?? match?.id ?? null;
                if (apiDeptId == null || apiDeptId === '') throw new Error('No se encontró el departamento "' + depName + '" en la API');

                // Traer las posiciones (puestos) de ese departamento.
                const posData = await fetchJson('/api/external/propiedades/' + encodeURIComponent(propId) + '/departamentos/' + encodeURIComponent(apiDeptId) + '/posiciones');
                const positions = extractArray(posData, ['posiciones', 'posicion', 'positions', 'puestos', 'items', 'results']);

                posSelect.innerHTML = '<option value="">-- Seleccione puesto --</option>';
                positions.forEach(p => {
                    const val = (p?.posicion_nombre ?? p?.position_name ?? p?.nombre ?? p?.name ?? p?.descripcion ?? '').toString().trim();
                    if (!val) return;
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = val;
                    posSelect.appendChild(opt);
                });

                if (positions.length === 0) {
                    posSelect.innerHTML = '<option value="">— Este departamento no tiene puestos —</option>';
                }

                if (currentValue && Array.from(posSelect.options).some(o => o.value === currentValue)) {
                    posSelect.value = currentValue;
                }

                posSelect.disabled = false;
            } catch (e) {
                console.error('No se pudieron cargar los puestos desde la API:', e);
                posSelect.innerHTML = '<option value="">— Error al cargar puestos (F12) —</option>';
                posSelect.disabled = false;
            }
        }

        propSelect.addEventListener('change', loadPositions);
        depSelect.addEventListener('change', loadPositions);
    })();
</script>
@endunless
