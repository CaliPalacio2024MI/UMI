
@php
    $currentValue = old('name', isset($item) ? $item->name : '');
    $activeInstitutionName = session('active_institution_name');
    $isUniversity = ($activeInstitutionName === 'Universidad Mundo Imperial');
@endphp

@if($isUniversity)
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
        <input
            type="text"
            id="workstationNameInput"
            name="name"
            required
            autocomplete="off"
            value="{{ $currentValue }}"
        >
    </div>
@else
<div class="form-group" style="display:none;">
    <label for="propiedadSelect">Propiedad</label>
    <select id="propiedadSelect" required>
        <option value="">-- Seleccione propiedad --</option>
    </select>
</div>

    <div class="form-group">
        <label for="department_id">Departamento</label>
        <select id="department_id" name="department_id" required>
            <option value="">-- Seleccione departamento --</option>
            @foreach($departments as $department)
                <option
                    value="{{ $department->id }}"
                    data-name="{{ $department->name }}"
                    {{ (isset($item) && $item->department_id == $department->id) ? 'selected' : '' }}
                >
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="workstationNameSelect">Nombre del Puesto</label>
        <select id="workstationNameSelect" name="name" required disabled>
            <option value="">-- Primero seleccione un departamento --</option>
            @if($currentValue !== '')
                <option value="{{ $currentValue }}" selected>{{ $currentValue }}</option>
            @endif
        </select>

        <input
            type="text"
            id="workstationNameManual"
            class="form-control"
            placeholder="Escriba el nombre del puesto"
            autocomplete="off"
            style="display:none; margin-top:10px;"
        >
    </div>

    @if($currentValue === '')
    <div class="bulk-actions">
        <button type="submit" class="btn-primary">Guardar</button>
        <button type="button" id="btnBulkWorkstations" class="btn-primary">Registro Masivo de Puestos</button>
    </div>
    <style>
    /* Solo en este formulario: ocultar el Guardar del modal (se usa el de abajo, a todo el ancho) */
    #modalForm:has(.bulk-actions) > button[type="submit"] { display: none !important; }
    #formModal .bulk-actions { display: grid; gap: 12px; margin-top: 22px; }
    #formModal .bulk-actions > button.btn-primary { width: 100%; margin: 0; }
</style>
    <div class="form-group">

        <div id="bulkWorkstationsPanel"
             style="display:none; margin-top:12px; padding:12px; border:1px solid #e2e5e9; border-radius:8px; background:#fafbfc;">
            <strong style="display:block; margin-bottom:4px;">Selecciona departamentos para registro masivo</strong>
            <small style="display:block; color:#555; margin-bottom:10px;">
                Puedes elegir varios departamentos y registrar todos sus puestos de la API en un solo proceso.
                Los puestos ya registrados se omiten.
            </small>
            <div style="display:flex; gap:8px; margin-bottom:10px;">
                <button type="button" id="bulkWsSelectAll" class="bulk-btn-outline">Seleccionar todos</button>
                <button type="button" id="bulkWsClear" class="bulk-btn-outline">Limpiar</button>
            </div>
            <div id="bulkWsList" style="max-height:220px; overflow-y:auto; display:grid; gap:6px;">
                @foreach($departments as $department)
                    <label style="display:flex; align-items:center; gap:10px; margin:0; padding:8px 10px; border:1px solid #e2e5e9; border-radius:7px; background:#fff; cursor:pointer;">
                        <input type="checkbox" class="bulk-ws-dept" value="{{ $department->id }}" data-name="{{ $department->name }}" style="width:16px; height:16px; margin:0;">
                        <span>{{ $department->name }}</span>
                    </label>
                @endforeach
            </div>
            <div style="display:flex; gap:8px; margin-top:12px;">
                <button type="button" id="bulkWsCancel" class="bulk-btn-outline">Cancelar</button>
                <button type="button" id="bulkWsConfirm" class="bulk-btn-solid">Registrar seleccionados</button>
            </div>
            <style>
                #bulkWorkstationsPanel .bulk-btn-outline,
                #bulkWorkstationsPanel .bulk-btn-solid {
                    flex: 1; margin: 0; padding: 9px 12px; border-radius: 7px;
                    border: 1px solid #0d2240; font-weight: 600; cursor: pointer;
                }
                #bulkWorkstationsPanel .bulk-btn-outline { background: #fff; color: #0d2240; }
                #bulkWorkstationsPanel .bulk-btn-solid { background: #0d2240; color: #fff; }
                #bulkWorkstationsPanel button:disabled { opacity: .6; cursor: wait; }
            </style>
            <p id="bulkWsStatus" style="margin:10px 0 0; color:#223f70; font-weight:600; display:none;"></p>
        </div>
    </div>
    @endif
@endif

@unless($isUniversity)
<script>
(function () {
    const propSelect = document.getElementById('propiedadSelect');
    const depSelect = document.getElementById('department_id');
    const posSelect = document.getElementById('workstationNameSelect');
    const manualInput = document.getElementById('workstationNameManual');

    if (!propSelect || !depSelect || !posSelect || !manualInput) return;

    const currentValue = (@json($currentValue) || '').trim();
    const activeInstitutionName = (@json($activeInstitutionName) || '').trim();
    const allWorkstationNames = @json($allWorkstationNames ?? []) || [];
    const workstationNamesByDepartment = @json($workstationNamesByDepartment ?? []) || {};

    function normalize(value) {
        return String(value ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-zA-Z0-9 ]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    async function fetchJson(endpoint) {
        const response = await fetch(
            '/external-data?endpoint=' + encodeURIComponent(endpoint),
            {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }
        );

        const data = await response.json();

        if (!response.ok || data?.error) {
            throw new Error(data?.error || ('HTTP ' + response.status));
        }

        return data;
    }

    function extractArray(data, keys) {
        if (Array.isArray(data)) return data;

        for (const key of keys) {
            if (Array.isArray(data?.[key])) return data[key];
        }

        if (Array.isArray(data?.data)) return data.data;

        for (const key of keys) {
            if (Array.isArray(data?.data?.[key])) return data.data[key];
        }

        return [];
    }

    function getDepartmentName() {
        const option = depSelect.options[depSelect.selectedIndex];
        return (option?.dataset?.name || option?.text || '').trim();
    }

    function isQuality() {
        return normalize(getDepartmentName()) === normalize('Calidad');
    }

    // Modo manual: se escribe el puesto a mano (Calidad, o departamento que no existe en la API).
    function setManualMode(on) {
        manualInput.style.display = on ? 'block' : 'none';
        manualInput.required = on;
        posSelect.required = !on;
        if (!on) manualInput.value = '';
        // El formulario se envía con FormData, así que el "name" se cambia aquí, no en submit.
        if (on) {
            posSelect.removeAttribute('name');
            manualInput.name = 'name';
        } else {
            manualInput.removeAttribute('name');
            posSelect.name = 'name';
        }
    }

    function showPositionMessage(message, manual) {
        posSelect.innerHTML = '';
        posSelect.add(new Option(message, ''));
        posSelect.disabled = true;
        setManualMode(manual);
        if (manual && currentValue) manualInput.value = currentValue; // al editar
    }

    async function loadProperties() {
        propSelect.disabled = true;
        propSelect.innerHTML = '';
        propSelect.add(new Option('Cargando propiedades...', ''));

        try {
            const data = await fetchJson('/api/external/propiedades');
            const properties = extractArray(data, ['propiedades', 'properties', 'items', 'results']);

            propSelect.innerHTML = '';
            propSelect.add(new Option('-- Seleccione propiedad --', ''));

            let selectedId = '';

            properties.forEach(property => {
                const id = property?.id
                    ?? property?.id_propiedad
                    ?? property?.propiedad_id
                    ?? property?.property_id;

                const name = String(
                    property?.nombre ?? property?.propiedad_nombre ?? property?.name ?? property?.descripcion ?? ''
                ).trim();

                if (id == null || !name) return;

                propSelect.add(new Option(name, String(id)));

                if (normalize(name) === normalize(activeInstitutionName)) {
                    selectedId = String(id);
                }
            });

            if (selectedId) {
                propSelect.value = selectedId;
            }

            if (!properties.length) {
                propSelect.innerHTML = '';
                propSelect.add(new Option('-- No hay propiedades disponibles --', ''));
            }
        } catch (error) {
            console.error('Error al cargar propiedades:', error);
            propSelect.innerHTML = '';
            propSelect.add(new Option('-- Error al cargar propiedades --', ''));
        } finally {
            propSelect.disabled = false;
        }
    }

    const departmentsCache = new Map();

    async function getApiDepartments(propertyId) {
        if (!departmentsCache.has(propertyId)) {
            const data = await fetchJson(
                '/api/external/propiedades/' + encodeURIComponent(propertyId) + '/departamentos'
            );
            departmentsCache.set(propertyId, extractArray(data, [
                'departamentos', 'departamento', 'departments', 'items', 'results'
            ]));
        }
        return departmentsCache.get(propertyId);
    }

    const getApiName = department => normalize(
        department?.departamento_nombre ??
        department?.departamentoNombre ??
        department?.department_name ??
        department?.nombre ??
        department?.name ??
        department?.descripcion ??
        department?.departamento ??
        ''
    );

    const getApiId = department =>
        department?.id_departamento ??
        department?.departamento_id ??
        department?.department_id ??
        department?.id ??
        null;

    // Busca el departamento primero en la propiedad activa y, si no está,
    // en las demás propiedades de la API (el departamento pudo darse de alta desde otra).
    async function findApiDepartment(departmentName) {
        const wanted = normalize(departmentName);
        const propertyIds = [propSelect.value].concat(
            Array.from(propSelect.options)
                .map(option => option.value)
                .filter(value => value && value !== propSelect.value)
        );

        for (const propertyId of propertyIds) {
            let departments;
            try {
                departments = await getApiDepartments(propertyId);
            } catch (error) {
                console.warn('No se pudieron leer departamentos de la propiedad', propertyId, error);
                continue;
            }

            const match = departments.find(d => getApiName(d) === wanted)
                || departments.find(d => {
                    const name = getApiName(d);
                    return name && wanted && (name.includes(wanted) || wanted.includes(name));
                });

            const id = match ? getApiId(match) : null;
            if (id != null && id !== '') {
                return { propertyId, departmentId: String(id) };
            }
        }

        return null;
    }

    // Departamentos locales que en la API están repartidos en varios departamentos.
    // Llave: nombre local normalizado. Valor: id_departamento de la API (propiedad activa).
    const DEPARTMENT_API_MAP = {
        'talento humano': ['8024', '8054'],                  // Recursos Humanos Gerencial / Variable
        'alimentos y bebidas': [
            '2900', '2930',                                  // A&B Administracion Gerencial / Variable
            '2912', '2942',                                  // A&B Cocinas Gerencial / Variable
            '2913', '2943'                                   // A&B Stewards Gerencial / Variable
        ]
    };

    async function loadPositions() {
        const propertyId = propSelect.value;
        const departmentName = getDepartmentName();

        posSelect.disabled = true;
        posSelect.innerHTML = '';
        manualInput.value = '';

        if (!depSelect.value || !departmentName) {
            showPositionMessage('-- Primero seleccione departamento --', false);
            return;
        }

        // Calidad no existe en la API: el puesto se captura a mano.
        // Calidad no existe en la API: se listan los puestos ya registrados en el sistema.
        if (isQuality()) {
            const already = (workstationNamesByDepartment[depSelect.value] || []).map(normalize);
            const names = allWorkstationNames
                .filter(name => name && (!already.includes(normalize(name)) || name === currentValue));

            if (!names.length) {
                showPositionMessage('-- No hay puestos registrados; escriba el puesto abajo --', true);
                return;
            }

            posSelect.innerHTML = '';
            posSelect.add(new Option('-- Seleccione puesto --', ''));
            names.forEach(name => posSelect.add(new Option(name, name)));
            if (names.includes(currentValue)) posSelect.value = currentValue;
            posSelect.disabled = false;
            setManualMode(false);
            return;
        }

        if (!propertyId) {
            showPositionMessage('-- Propiedad no encontrada en la API; escriba el puesto --', true);
            return;
        }

        posSelect.add(new Option('Cargando puestos...', ''));

        try {
            let positions = [];

            const mappedIds = DEPARTMENT_API_MAP[normalize(departmentName)];

            if (mappedIds) {
                const responses = await Promise.allSettled(
                    mappedIds.map(id => fetchJson(
                        '/api/external/propiedades/' + encodeURIComponent(propertyId) +
                        '/departamentos/' + encodeURIComponent(id) + '/posiciones'
                    ))
                );
                responses.forEach((r, i) => {
                    if (r.status === 'rejected') console.warn('Sin puestos para el departamento API', mappedIds[i], r.reason);
                });
                positions = responses
                    .filter(r => r.status === 'fulfilled')
                    .flatMap(r => extractArray(r.value, [
                        'posiciones', 'posicion', 'positions', 'puestos', 'items', 'results'
                    ]));
            } else {
                const found = await findApiDepartment(departmentName);

                if (!found) {
                    console.warn('El departamento "' + departmentName + '" no existe en la API externa.');
                    showPositionMessage('-- No está en la API; escriba el puesto abajo --', true);
                    return;
                }

                const positionData = await fetchJson(
                    '/api/external/propiedades/' + encodeURIComponent(found.propertyId) +
                    '/departamentos/' + encodeURIComponent(found.departmentId) + '/posiciones'
                );
                positions = extractArray(positionData, [
                    'posiciones', 'posicion', 'positions', 'puestos', 'items', 'results'
                ]);
            }

            const names = [...new Set(positions.map(position => String(
                position?.posicion_nombre ??
                position?.position_name ??
                position?.nombre ??
                position?.name ??
                position?.descripcion ??
                ''
            ).trim()).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'es'));

            if (!names.length) {
                showPositionMessage('-- Sin puestos en la API; escriba el puesto abajo --', true);
                return;
            }

            posSelect.innerHTML = '';
            posSelect.add(new Option('-- Seleccione puesto --', ''));
            names.forEach(name => posSelect.add(new Option(name, name)));
            if (names.includes(currentValue)) posSelect.value = currentValue;

            posSelect.disabled = false;
            setManualMode(false);
        } catch (error) {
            console.error('Error al cargar puestos:', error);
            showPositionMessage('-- Error al cargar puestos; escriba el puesto abajo --', true);
        }
    }

    manualInput.addEventListener('input', function () {
        if (manualInput.value.trim()) {
            posSelect.value = '';
        }
    });

    // ---------- Registro masivo de puestos (como MyQuality) ----------
    async function getApiPositionNames(departmentName) {
        const propertyId = propSelect.value;
        if (!propertyId) return [];

        let positions = [];
        const mappedIds = DEPARTMENT_API_MAP[normalize(departmentName)];

        if (mappedIds) {
            const responses = await Promise.allSettled(mappedIds.map(id => fetchJson(
                '/api/external/propiedades/' + encodeURIComponent(propertyId) +
                '/departamentos/' + encodeURIComponent(id) + '/posiciones'
            )));
            positions = responses
                .filter(r => r.status === 'fulfilled')
                .flatMap(r => extractArray(r.value, ['posiciones', 'posicion', 'positions', 'puestos', 'items', 'results']));
        } else {
            const found = await findApiDepartment(departmentName);
            if (!found) return [];
            try {
                const data = await fetchJson(
                    '/api/external/propiedades/' + encodeURIComponent(found.propertyId) +
                    '/departamentos/' + encodeURIComponent(found.departmentId) + '/posiciones'
                );
                positions = extractArray(data, ['posiciones', 'posicion', 'positions', 'puestos', 'items', 'results']);
            } catch (error) {
                console.warn('Sin puestos en la API para', departmentName, error);
                return [];
            }
        }

        return [...new Set(positions.map(p => String(
            p?.posicion_nombre ?? p?.position_name ?? p?.nombre ?? p?.name ?? p?.descripcion ?? ''
        ).trim()).filter(Boolean))];
    }

    const bulkBtn = document.getElementById('btnBulkWorkstations');
    const bulkPanel = document.getElementById('bulkWorkstationsPanel');
    const bulkStatus = document.getElementById('bulkWsStatus');
    const bulkChecks = () => Array.from(document.querySelectorAll('.bulk-ws-dept'));

    if (bulkBtn && bulkPanel) {
        bulkBtn.addEventListener('click', function () {
            const open = bulkPanel.style.display === 'none';
            bulkPanel.style.display = open ? 'block' : 'none';
            if (open && depSelect.value) {
                bulkChecks().forEach(c => { if (c.value === depSelect.value) c.checked = true; });
            }
        });
        document.getElementById('bulkWsSelectAll').addEventListener('click', () => bulkChecks().forEach(c => { c.checked = true; }));
        document.getElementById('bulkWsClear').addEventListener('click', () => bulkChecks().forEach(c => { c.checked = false; }));
        document.getElementById('bulkWsCancel').addEventListener('click', () => { bulkPanel.style.display = 'none'; });

        const confirmBtn = document.getElementById('bulkWsConfirm');
        confirmBtn.addEventListener('click', async function () {
            const selected = bulkChecks().filter(c => c.checked);
            if (!selected.length) {
                alert('Selecciona al menos un departamento para el registro masivo.');
                return;
            }
            if (!confirm('Se registrarán de golpe todas las posiciones disponibles de los ' + selected.length + ' departamentos seleccionados. ¿Deseas continuar?')) {
                return;
            }

            confirmBtn.disabled = true;
            bulkBtn.disabled = true;
            bulkStatus.style.display = 'block';

            try {
                const items = [];
                for (let i = 0; i < selected.length; i++) {
                    const name = selected[i].dataset.name;
                    bulkStatus.textContent = 'Consultando la API (' + (i + 1) + '/' + selected.length + '): ' + name + '...';
                    items.push({ department_id: Number(selected[i].value), names: await getApiPositionNames(name) });
                }

                bulkStatus.textContent = 'Registrando posiciones, por favor espera...';
                const response = await fetch('/ajustes/workstations/bulk', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ items: items })
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || ('HTTP ' + response.status));
                }

                const r = data.resumen || {};
                const detalle = (r.detalle || []).slice(0, 12)
                    .map(d => '  • ' + d.departamento + ': API ' + d.api + ', registrados ' + d.registrados + ', omitidos ' + d.omitidos)
                    .join('\n');
                const sinPuestos = (r.detalle || []).filter(d => d.api === 0).length;
                alert(
                    'Resumen de registro masivo de puestos\n\n' +
                    'Departamentos procesados: ' + r.departamentos + '\n' +
                    'Total API: ' + r.total_api + '\n' +
                    'Registrados: ' + r.registrados + '\n' +
                    'Omitidos (ya registrados): ' + r.omitidos + '\n' +
                    'Departamentos sin puestos en la API: ' + sinPuestos +
                    (detalle ? '\n\nDetalle por departamento:\n' + detalle : '')
                );
                window.location.reload();
            } catch (error) {
                console.error('Error en registro masivo de puestos:', error);
                alert('No se pudo completar el registro masivo: ' + error.message);
                bulkStatus.style.display = 'none';
                confirmBtn.disabled = false;
                bulkBtn.disabled = false;
            }
        });
    }

    propSelect.addEventListener('change', loadPositions);
    depSelect.addEventListener('change', loadPositions);

    loadProperties().then(() => {
        if (propSelect.value && depSelect.value) {
            loadPositions();
        }
    });
})();
</script>
@endunless
