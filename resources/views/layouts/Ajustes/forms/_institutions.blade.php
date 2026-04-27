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
            <label for="flag_administrativo">Propiedades</label>
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
        if (!select || !flagAdministrativo || !flagUniversidad || !inputContainer || !nameInputLabel) return;

        if (flagAdministrativo.checked && flagUniversidad.checked) {
            flagUniversidad.checked = false;
        }

        // Valor inicial que viene del backend (old('name') o $item->name)
        const currentValue = (@json($currentValue) || '').trim();
        /** Unidad en la que está logueado el usuario (Palacio / Pierre / Princess / …). */
        const activeInstitutionName = (@json($activeInstitutionName ?? '') || '').trim();
        let cachedPropiedades = null; // Cacheamos para no consumir la API más de una vez
        let input = null;

        function isUniversidadFlagOn() {
            return flagUniversidad.checked;
        }

        function isAdministrativoFlagOn() {
            return flagAdministrativo.checked;
        }

        function ensureInputElement() {
            if (input) return input;

            input = document.createElement('input');
            input.type = 'text';
            input.id = 'nameInput';
            input.name = 'name';
            input.autocomplete = 'off';
            input.required = true;
            input.disabled = false;

            inputContainer.innerHTML = '';
            inputContainer.appendChild(input);
            inputContainer.style.display = 'block';

            // Si venías editando una institución, preservamos el valor actual.
            if (currentValue && !input.value) input.value = currentValue;

            return input;
        }

        function setMode() {
            if (isUniversidadFlagOn()) {
                // Universidad: crear input escribible, sin consumir API
                const uniInput = ensureInputElement();
                uniInput.disabled = false;
                uniInput.required = true;

                select.disabled = true;
                select.required = false;
                select.style.display = 'none';

                nameInputLabel.style.display = 'block';

                // No tocamos el select; solo deshabilitamos para que no se envíe.
                return;
            }

            // Si no es universidad, removemos input manual.
            if (input) {
                input.remove();
                input = null;
            }
            inputContainer.style.display = 'none';
            nameInputLabel.style.display = 'none';

            // Propiedades (administrativo): mostrar select.
            if (isAdministrativoFlagOn()) {
                select.disabled = false;
                select.required = true;
                select.style.display = 'block';
                return;
            }

            // Ninguna opción seleccionada: ocultar select.
            select.disabled = true;
            select.required = false;
            select.style.display = 'none';
        }

        /** Palabra clave según la unidad activa (misma lógica que puestos/departamentos). */
        function keywordForActiveInstitution(instName) {
            const x = (instName || '').toLowerCase();
            if (x.includes('palacio')) return 'palacio';
            if (x.includes('princess')) return 'princess';
            if (x.includes('pierre')) return 'pierre';
            return '';
        }

        function filterPropiedadesForActiveUnit(list) {
            if (!Array.isArray(list)) return list;
            const key = keywordForActiveInstitution(activeInstitutionName);
            if (!key) return list;
            const filtered = list.filter(p => {
                const n = (p?.nombre ?? p?.name ?? p?.descripcion ?? '').toString().toLowerCase();
                return n.includes(key);
            });
            if (filtered.length === 0) {
                console.warn('Ninguna propiedad de la API coincide con la unidad activa:', activeInstitutionName, 'keyword:', key);
            }
            return filtered;
        }

        function populateSelectFromPropiedades(propiedades) {
            const raw = Array.isArray(propiedades)
                ? propiedades
                : (propiedades?.propiedades ?? propiedades?.data ?? propiedades);

            if (!Array.isArray(raw)) {
                console.warn('Respuesta inesperada al cargar propiedades:', propiedades);
                return;
            }

            const list = filterPropiedadesForActiveUnit(raw);

            select.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = '-- Seleccione Unidad de Negocio --';
            select.appendChild(placeholder);

            list.forEach(p => {
                const optionValue = (p?.nombre ?? p?.name ?? p?.descripcion ?? String(p?.id ?? '')).trim();
                const optionText = (p?.nombre ?? p?.name ?? p?.descripcion ?? `ID ${p?.id ?? ''}`).toString();
                if (!optionValue) return;

                const opt = document.createElement('option');
                opt.value = optionValue;
                opt.textContent = optionText;
                select.appendChild(opt);
            });

            if (currentValue) {
                // Si el valor actual existe en el select, lo re-seleccionamos.
                const found = Array.from(select.options).some(o => o.value === currentValue);
                if (found) select.value = currentValue;
            }
        }

        async function loadPropiedadesIfNeeded() {
            if (!isAdministrativoFlagOn() || isUniversidadFlagOn()) return;
            if (cachedPropiedades) {
                populateSelectFromPropiedades(cachedPropiedades);
                return;
            }

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
            const list = data?.propiedades ?? data?.data ?? data;
            cachedPropiedades = list;

            populateSelectFromPropiedades(cachedPropiedades);
        }

        async function applyModeFromFlags() {
            setMode();
            if (isAdministrativoFlagOn() && !isUniversidadFlagOn()) {
                try {
                    await loadPropiedadesIfNeeded();
                } catch (e) {
                    console.error('No se pudo cargar propiedades para el select:', e);
                }
            }
        }

        flagAdministrativo.addEventListener('change', () => {
            if (flagAdministrativo.checked) {
                flagUniversidad.checked = false;
            }
            applyModeFromFlags();
        });

        flagUniversidad.addEventListener('change', () => {
            if (flagUniversidad.checked) {
                flagAdministrativo.checked = false;
            }
            applyModeFromFlags();
        });

        // Inicializar:
        // - Universidad: input manual
        // - Propiedades: select desde API
        // - Ninguna: ocultar ambos campos
        setMode();
        if (isAdministrativoFlagOn() && !isUniversidadFlagOn()) {
            loadPropiedadesIfNeeded().catch(e => {
                console.error('No se pudo cargar propiedades al inicializar:', e);
            });
        }
    })();
</script>