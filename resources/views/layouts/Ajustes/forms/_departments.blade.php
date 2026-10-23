@php
    $currentValue = old('name', isset($item) ? $item->name : '');
    $activeInstitutionName = session('active_institution_name');
    $activeInstitutionId = session('active_institution_id');
    $isUniversity = ($activeInstitutionName === 'Universidad Mundo Imperial');


$selectedInstitutionIds = old('institution_ids');

if ($selectedInstitutionIds === null) {
    if (isset($item)) {
        $selectedInstitutionIds = $item->institutions()
            ->pluck('institutions.id')
            ->map(fn ($id) => (string) $id)
            ->all();

        // Mantener incluida la propiedad principal.
        if (!in_array((string) $item->institution_id, $selectedInstitutionIds, true)) {
            $selectedInstitutionIds[] = (string) $item->institution_id;
        }
    } else {
        $selectedInstitutionIds = [$activeInstitutionId];
    }
}

$selectedInstitutionIds = array_map(
    'strval',
    (array) $selectedInstitutionIds
);

@endphp

@if($isUniversity)
    <div class="form-group">
        <label for="departmentNameInput">Nombre del Departamento</label>
        <input
            type="text"
            id="departmentNameInput"
            name="name"
            required
            autocomplete="off"
            value="{{ $currentValue }}"
        >
    </div>
@else
    <div class="form-group">
        <label>Unidades de negocio:</label>
        <div class="department-properties-list"
            style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px 14px; width:100%; box-sizing:border-box; margin-top:10px;">

            @forelse(($institutions ?? collect()) as $institution)
                <label style="display:grid !important; grid-template-columns:18px minmax(0, 1fr) !important; align-items:center; justify-content:initial !important; gap:10px; width:100%; min-width:0; box-sizing:border-box; margin:0; padding:12px; border:1px solid #e2e5e9; border-radius:7px; cursor:pointer;">

                    <input
                        type="checkbox"
                        name="institution_ids[]"
                        value="{{ $institution->id }}"
                        data-name="{{ $institution->name }}"
                        {{ in_array((string) $institution->id, $selectedInstitutionIds, true) ? 'checked' : '' }}
                        style="width:16px; height:16px; margin:0; justify-self:center;"
                    >

                    <span style="display:block !important; width:100%; min-width:0; white-space:normal; overflow-wrap:break-word; word-break:normal; line-height:1.4; text-align:left;">
                        {{ $institution->name }}
                    </span>

                </label>
            @empty
                <p>No hay propiedades disponibles.</p>
            @endforelse

        </div>
    </div>


    <div class="form-group">
        <label for="departmentNameSelect">Departamento</label>
  <select id="departmentNameSelect" name="name" required>
    <option value="{{ $currentValue }}" selected>
        {{ $currentValue }}
    </option>
    </select>
    </div>

    @if($currentValue === '')
    <div class="bulk-actions">
        <button type="submit" class="btn-primary">Guardar</button>
        <button type="button" id="btnBulkDepartments" class="btn-primary">Registro Masivo</button>
    </div>
    <style>
    /* Solo en este formulario: ocultar el Guardar del modal (se usa el de abajo, a todo el ancho) */
    #modalForm:has(.bulk-actions) > button[type="submit"] { display: none !important; }
    #formModal .bulk-actions { display: grid; gap: 12px; margin-top: 22px; }
    #formModal .bulk-actions > button.btn-primary { width: 100%; margin: 0; }
</style>
    @endif
@endif

<input type="hidden" name="institution_id" value="{{ $activeInstitutionId }}">



@unless($isUniversity)
<script>
(function () {
    const deptSelect = document.getElementById('departmentNameSelect');
    const propertyCheckboxes = Array.from(
        document.querySelectorAll('input[name="institution_ids[]"]')
    );

    if (!deptSelect || !propertyCheckboxes.length) return;

    const currentValue = (@json($currentValue) || '').trim();

    const registeredDepartmentNames = (
        @json($registeredDepartmentNames ?? []) || []
    ).map(normalizeDepartmentName);

    // Departamentos ya registrados en el sistema, por unidad (id local).
    // Se usan para las unidades que NO existen en la API (Universidad, UTA...).
    const localDepartmentsByInstitution = @json($localDepartmentsByInstitution ?? []) || {};

    function normalizeDepartmentName(name) {
        return String(name ?? '')
            .trim()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function fetchJson(endpoint) {
        return fetch(
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
        ).then(async response => {
            const data = await response.json();

            if (!response.ok || data?.error) {
                throw new Error(data?.error || ('HTTP ' + response.status));
            }

            return data;
        });
    }

    function extractArray(data, keys) {
        if (Array.isArray(data)) return data;

        for (const key of keys) {
            if (Array.isArray(data?.[key])) return data[key];
        }

        if (Array.isArray(data?.data)) return data.data;

        for (const key of keys) {
            if (Array.isArray(data?.data?.[key])) {
                return data.data[key];
            }
        }

        return [];
    }

    // La API externa usa SUS PROPIOS ids de propiedad (ej. Palacio = 2),
    // distintos a los ids locales de la tabla institutions. Se relacionan por nombre.
    let propertyMapPromise = null;

    function getExternalPropertyMap() {
        if (!propertyMapPromise) {
            propertyMapPromise = fetchJson('/api/external/propiedades')
                .then(data => {
                    const map = new Map();
                    extractArray(data, ['propiedades', 'properties', 'items', 'results'])
                        .forEach(property => {
                            const id = property?.id
                                ?? property?.id_propiedad
                                ?? property?.propiedad_id
                                ?? property?.property_id;
                            const name = property?.nombre
                                ?? property?.propiedad_nombre
                                ?? property?.name
                                ?? property?.descripcion;
                            if (id != null && name) {
                                map.set(normalizeDepartmentName(name), String(id));
                            }
                        });
                    return map;
                })
                .catch(error => {
                    propertyMapPromise = null;
                    throw error;
                });
        }
        return propertyMapPromise;
    }

    function getSelectedUnits() {
        return propertyCheckboxes
            .filter(checkbox => checkbox.checked)
            .map(checkbox => ({
                localId: checkbox.value.trim(),
                name: (checkbox.dataset.name || '').trim()
            }));
    }

    function fillSelect(message, departmentsByName) {
        deptSelect.innerHTML = '';

        if (currentValue) {
            const normalizedCurrent = normalizeDepartmentName(currentValue);
            if (!departmentsByName.has(normalizedCurrent)) {
                departmentsByName.set(normalizedCurrent, currentValue);
            }
        }

        if (!departmentsByName.size) {
            deptSelect.add(new Option(message, ''));
            return;
        }

        if (!currentValue) {
            deptSelect.add(new Option('-- Seleccione departamento --', ''));
        }

        Array.from(departmentsByName.values())
            .sort((a, b) => a.localeCompare(b, 'es'))
            .forEach(name => deptSelect.add(new Option(name, name)));

        if (currentValue) deptSelect.value = currentValue;
    }

    async function loadDepartments() {
        const selectedUnits = getSelectedUnits();

        deptSelect.disabled = true;
        deptSelect.innerHTML = '';

        if (!selectedUnits.length) {
            deptSelect.add(new Option('-- Seleccione una unidad de negocio --', ''));
            deptSelect.disabled = false;
            return;
        }

        deptSelect.add(new Option('Cargando departamentos...', ''));

        try {
            const propertyMap = await getExternalPropertyMap();

            const externalIds = [];
            const localNames = [];

            selectedUnits.forEach(unit => {
                const externalId = propertyMap.get(normalizeDepartmentName(unit.name));
                if (externalId) {
                    if (!externalIds.includes(externalId)) externalIds.push(externalId);
                } else {
                    // No está en la API: usar sus departamentos ya registrados.
                    localNames.push(...(localDepartmentsByInstitution[unit.localId] || []));
                }
            });

            const results = await Promise.allSettled(
                externalIds.map(async id => extractArray(
                    await fetchJson('/api/external/propiedades/' + encodeURIComponent(id) + '/departamentos'),
                    ['departamentos', 'departamento', 'departments', 'items', 'results']
                ))
            );

            results.forEach((result, index) => {
                if (result.status === 'rejected') {
                    console.error('Error al cargar departamentos de la propiedad externa:', externalIds[index], result.reason);
                }
            });

            const apiNames = results
                .filter(result => result.status === 'fulfilled')
                .flatMap(result => result.value)
                .map(department => String(
                    department?.departamento_nombre
                    ?? department?.departamentoNombre
                    ?? department?.department_name
                    ?? department?.nombre
                    ?? department?.name
                    ?? department?.descripcion
                    ?? ''
                ).trim());

            const departmentsByName = new Map();

            apiNames.concat(localNames).forEach(rawName => {
                const name = String(rawName ?? '').trim();
                if (!name) return;

                const normalized = normalizeDepartmentName(name);

                // Oculta los ya registrados en la unidad activa (excepto el que se edita).
                if (
                    registeredDepartmentNames.includes(normalized)
                    && normalized !== normalizeDepartmentName(currentValue)
                ) {
                    return;
                }

                if (!departmentsByName.has(normalized)) {
                    departmentsByName.set(normalized, name);
                }
            });

            fillSelect(
                results.length && results.every(result => result.status === 'rejected') && !localNames.length
                    ? '-- Error al cargar departamentos --'
                    : '-- No hay departamentos disponibles --',
                departmentsByName
            );
        } catch (error) {
            console.error('No se pudieron cargar los departamentos:', error);
            fillSelect('-- Error al cargar departamentos --', new Map());
        } finally {
            deptSelect.disabled = false;
        }
    }

    // ---------- Registro masivo (como MyQuality) ----------
    const bulkBtn = document.getElementById('btnBulkDepartments');
    if (bulkBtn) {
        bulkBtn.addEventListener('click', async function () {
            const names = Array.from(deptSelect.options)
                .map(option => option.value.trim())
                .filter(Boolean);
            const institutionIds = propertyCheckboxes
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            if (!institutionIds.length) {
                alert('Selecciona al menos una unidad de negocio.');
                return;
            }
            if (!names.length) {
                alert('No hay departamentos nuevos para registrar en las unidades seleccionadas.');
                return;
            }
            if (!confirm('Se registrarán de golpe ' + names.length + ' departamentos de las unidades seleccionadas. ¿Deseas continuar?')) {
                return;
            }

            bulkBtn.disabled = true;
            bulkBtn.textContent = 'Registrando departamentos, por favor espera...';

            try {
                const response = await fetch('/ajustes/departments/bulk', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ names: names, institution_ids: institutionIds })
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || ('HTTP ' + response.status));
                }

                const lista = (data.registrados || []).slice(0, 12).map(n => '  • ' + n).join('\n');
                const extra = (data.registrados || []).length > 12 ? '\n  ... y ' + (data.registrados.length - 12) + ' más.' : '';
                alert(
                    'Resumen de registro masivo\n\n' +
                    'Departamentos en la lista: ' + data.total + '\n' +
                    'Registrados: ' + data.registrados.length + '\n' +
                    'Omitidos (ya registrados): ' + data.omitidos.length +
                    (lista ? '\n\nRegistrados:\n' + lista + extra : '')
                );
                window.location.reload();
            } catch (error) {
                console.error('Error en registro masivo de departamentos:', error);
                alert('No se pudo completar el registro masivo: ' + error.message);
                bulkBtn.disabled = false;
                bulkBtn.textContent = 'Registro Masivo';
            }
        });
    }

    propertyCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', loadDepartments);
    });

    loadDepartments();
})();
</script>
@endunless
