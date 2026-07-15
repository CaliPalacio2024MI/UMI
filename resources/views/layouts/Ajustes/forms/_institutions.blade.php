@php
    $currentValue = old('name', isset($item) ? $item->name : '');

    // Tipo actual: en edición se deriva de las banderas guardadas.
    $currentType = old('unit_type');
    if (!$currentType && isset($item)) {
        $currentType = $item->is_universidad ? 'universidad'
            : ($item->is_administrativo ? 'propiedad' : '');
    }
@endphp

{{-- =================================================================== --}}
{{-- 1. TIPO DE UNIDAD: Universidad o Propiedad                          --}}
{{--    Estas banderas ocultas se sincronizan por JS según la selección. --}}
{{-- =================================================================== --}}
<div class="form-group">
    <label for="unitTypeSelect">Universidad o Propiedad</label>
    <input type="hidden" name="is_universidad" id="is_universidad" value="{{ $currentType === 'universidad' ? 1 : 0 }}">
    <input type="hidden" name="is_administrativo" id="is_administrativo" value="{{ $currentType === 'propiedad' ? 1 : 0 }}">
    <select id="unitTypeSelect" required>
        <option value="">-- Seleccione una opción --</option>
        <option value="universidad" @selected($currentType === 'universidad')>Universidad</option>
        <option value="propiedad" @selected($currentType === 'propiedad')>Propiedad</option>
    </select>
</div>

{{-- =================================================================== --}}
{{-- 2. NOMBRE DE LA UNIDAD (depende del tipo)                           --}}
{{--    - Propiedad  -> select llenado desde la API                      --}}
{{--    - Universidad -> campo de texto abierto                          --}}
{{-- =================================================================== --}}
<div class="form-group">
    {{-- Propiedad: opciones recuperadas de la API --}}
    <div id="propiedadWrapper" style="display:none;">
        <label for="nameSelect">Propiedad</label>
        <select id="nameSelect" name="name" disabled>
            <option value="">-- Seleccione propiedad --</option>
            @if(!empty($currentValue))
                <option value="{{ $currentValue }}" selected>{{ $currentValue }}</option>
            @endif
        </select>
    </div>

    {{-- Universidad: nombre escrito manualmente --}}
    <div id="universidadWrapper" style="display:none;">
        <label for="nameInput">Nombre de la universidad</label>
        <input type="text" id="nameInput" name="name" autocomplete="off" value="{{ $currentType === 'universidad' ? $currentValue : '' }}" disabled>
    </div>
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
        const typeSelect = document.getElementById('unitTypeSelect');
        const isUniversidad = document.getElementById('is_universidad');
        const isAdministrativo = document.getElementById('is_administrativo');
        const propiedadWrapper = document.getElementById('propiedadWrapper');
        const universidadWrapper = document.getElementById('universidadWrapper');
        const nameSelect = document.getElementById('nameSelect');
        const nameInput = document.getElementById('nameInput');

        if (!typeSelect || !isUniversidad || !isAdministrativo ||
            !propiedadWrapper || !universidadWrapper || !nameSelect || !nameInput) {
            return;
        }

        const currentValue = (@json($currentValue) || '').trim();
        let cachedPropiedades = null; // Se consume la API una sola vez.

        // --- UNIVERSIDAD: campo de texto abierto ---
        function showUniversidad() {
            isUniversidad.value = '1';
            isAdministrativo.value = '0';

            universidadWrapper.style.display = 'block';
            propiedadWrapper.style.display = 'none';

            nameInput.disabled = false;
            nameInput.required = true;
            nameSelect.disabled = true;   // deshabilitado => no se envía
            nameSelect.required = false;

            if (currentValue && !nameInput.value) nameInput.value = currentValue;
        }

        // --- PROPIEDAD: select desde la API ---
        function showPropiedad() {
            isUniversidad.value = '0';
            isAdministrativo.value = '1';

            propiedadWrapper.style.display = 'block';
            universidadWrapper.style.display = 'none';

            nameSelect.disabled = false;
            nameSelect.required = true;
            nameInput.disabled = true;    // deshabilitado => no se envía
            nameInput.required = false;

            loadPropiedades();
        }

        // --- Sin tipo seleccionado: ocultar ambos ---
        function hideBoth() {
            isUniversidad.value = '0';
            isAdministrativo.value = '0';

            propiedadWrapper.style.display = 'none';
            universidadWrapper.style.display = 'none';

            nameSelect.disabled = true;
            nameSelect.required = false;
            nameInput.disabled = true;
            nameInput.required = false;
        }

        function applyType() {
            switch (typeSelect.value) {
                case 'universidad': showUniversidad(); break;
                case 'propiedad':   showPropiedad();   break;
                default:            hideBoth();         break;
            }
        }

        function populateSelect(propiedades) {
            const raw = Array.isArray(propiedades)
                ? propiedades
                : (propiedades?.propiedades ?? propiedades?.data ?? propiedades);

            if (!Array.isArray(raw)) {
                console.warn('Respuesta inesperada al cargar propiedades:', propiedades);
                return;
            }

            nameSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = '-- Seleccione propiedad --';
            nameSelect.appendChild(placeholder);

            raw.forEach(p => {
                const optionValue = (p?.nombre ?? p?.name ?? p?.descripcion ?? String(p?.id ?? '')).trim();
                const optionText = (p?.nombre ?? p?.name ?? p?.descripcion ?? `ID ${p?.id ?? ''}`).toString();
                if (!optionValue) return;

                const opt = document.createElement('option');
                opt.value = optionValue;
                opt.textContent = optionText;
                nameSelect.appendChild(opt);
            });

            // Re-seleccionar el valor actual si existe (modo edición).
            if (currentValue && Array.from(nameSelect.options).some(o => o.value === currentValue)) {
                nameSelect.value = currentValue;
            }
        }

        async function loadPropiedades() {
            if (cachedPropiedades) {
                populateSelect(cachedPropiedades);
                return;
            }

            try {
                const r = await fetch('/external-data?endpoint=/api/external/propiedades', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                const data = await r.json();
                cachedPropiedades = data?.propiedades ?? data?.data ?? data;
                populateSelect(cachedPropiedades);
            } catch (e) {
                console.error('No se pudo cargar propiedades desde la API:', e);
            }
        }

        typeSelect.addEventListener('change', applyType);

        // Inicializa según el tipo (relevante en modo edición).
        applyType();
    })();
</script>
