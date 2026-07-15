@php
    $currentValue = old('name', isset($item) ? $item->name : '');
    $activeInstitutionName = session('active_institution_name');
    $isUniversity = ($activeInstitutionName === 'Universidad Mundo Imperial');
@endphp

@if($isUniversity)
    {{-- =============================================================== --}}
    {{-- UNIVERSIDAD: nombre del departamento escrito manualmente         --}}
    {{-- =============================================================== --}}
    <div class="form-group">
        <label for="departmentNameInput">Nombre del Departamento</label>
        <input type="text" id="departmentNameInput" name="name" required autocomplete="off" value="{{ $currentValue }}">
    </div>
@else
    {{-- =============================================================== --}}
    {{-- PROPIEDAD: 1) elegir propiedad  2) elegir departamento (API)     --}}
    {{-- =============================================================== --}}
    <div class="form-group">
        <label for="propiedadSelect">Propiedad</label>
        <select id="propiedadSelect" required>
            <option value="">-- Seleccione propiedad --</option>
        </select>
    </div>

    <div class="form-group">
        <label for="departmentNameSelect">Departamento</label>
        {{-- El departamento seleccionado llena el campo "name" --}}
        <select id="departmentNameSelect" name="name" required disabled>
            <option value="">-- Primero seleccione una propiedad --</option>
            @if(!empty($currentValue))
                <option value="{{ $currentValue }}" selected>{{ $currentValue }}</option>
            @endif
        </select>
    </div>
@endif

<input type="hidden" name="institution_id" value="{{ session('active_institution_id') }}">

@unless($isUniversity)
<script>
    (function () {
        const propSelect = document.getElementById('propiedadSelect');
        const deptSelect = document.getElementById('departmentNameSelect');
        if (!propSelect || !deptSelect) return;

        const currentValue = (@json($currentValue) || '').trim();

        function fetchJson(endpoint) {
            return fetch('/external-data?endpoint=' + encodeURIComponent(endpoint), {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }).then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            });
        }

        // Busca un array dentro de la respuesta probando claves comunes.
        function extractArray(data, keys) {
            if (Array.isArray(data)) return data;
            for (const k of keys) { if (Array.isArray(data?.[k])) return data[k]; }
            if (Array.isArray(data?.data)) return data.data;
            for (const k of keys) { if (Array.isArray(data?.data?.[k])) return data.data[k]; }
            return [];
        }

        // --- 1) Cargar la lista de propiedades ---
        fetchJson('/api/external/propiedades')
            .then(data => {
                const props = extractArray(data, ['propiedades']);
                if (props.length === 0) throw new Error('La API no devolvió propiedades');

                propSelect.innerHTML = '<option value="">-- Seleccione propiedad --</option>';
                props.forEach(p => {
                    const id = p?.id ?? p?.id_propiedad ?? p?.propiedad_id ?? p?.property_id ?? '';
                    const name = (p?.nombre ?? p?.name ?? p?.descripcion ?? ('Propiedad ' + id)).toString();
                    if (id === '') return;
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = name;
                    propSelect.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('No se pudieron cargar las propiedades desde la API:', err);
                propSelect.innerHTML = '<option value="">— Error al cargar propiedades (F12) —</option>';
            });

        // --- 2) Al elegir una propiedad, cargar sus departamentos ---
        propSelect.addEventListener('change', function () {
            const propId = propSelect.value;

            deptSelect.disabled = true;
            if (!propId) {
                deptSelect.innerHTML = '<option value="">-- Primero seleccione una propiedad --</option>';
                return;
            }

            deptSelect.innerHTML = '<option value="">Cargando...</option>';

            fetchJson('/api/external/propiedades/' + encodeURIComponent(propId) + '/departamentos')
                .then(data => {
                    const deps = extractArray(data, ['departamentos', 'departamento', 'departments', 'items', 'results']);

                    deptSelect.innerHTML = '<option value="">-- Seleccione departamento --</option>';
                    deps.forEach(d => {
                        const val = (
                            d?.departamento_nombre ??
                            d?.departamentoNombre ??
                            d?.department_name ??
                            d?.nombre ??
                            d?.name ??
                            d?.descripcion ??
                            ''
                        ).toString().trim();
                        if (!val) return;
                        const opt = document.createElement('option');
                        opt.value = val;
                        opt.textContent = val;
                        deptSelect.appendChild(opt);
                    });

                    if (deps.length === 0) {
                        deptSelect.innerHTML = '<option value="">— Esta propiedad no tiene departamentos —</option>';
                    }

                    // Modo edición: re-seleccionar el valor actual si existe.
                    if (currentValue && Array.from(deptSelect.options).some(o => o.value === currentValue)) {
                        deptSelect.value = currentValue;
                    }

                    deptSelect.disabled = false;
                })
                .catch(err => {
                    console.error('No se pudieron cargar los departamentos desde la API:', err);
                    deptSelect.innerHTML = '<option value="">— Error al cargar departamentos (F12) —</option>';
                    deptSelect.disabled = false;
                });
        });
    })();
</script>
@endunless
