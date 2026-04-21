@php
    $currentValue = old('name', isset($item) ? $item->name : '');
    $defaultUnidadTipo = '';

    if ($currentValue === 'Pierre Mundo Imperial') {
        $defaultUnidadTipo = 'pierre';
    } elseif ($currentValue === 'Palacio Mundo Imperial') {
        $defaultUnidadTipo = 'palacio';
    } elseif ($currentValue === 'Princess Mundo Imperial') {
        $defaultUnidadTipo = 'princes';
    } elseif ($currentValue === 'Universidad Mundo Imperial') {
        $defaultUnidadTipo = 'universidad';
    }
@endphp

<div class="form-group tipo-unidad-flags-group">
    <p class="tipo-unidad-question" id="tipoUnidadQuestion">Tipo de Unidad</p>
    <input type="hidden" name="is_administrativo" value="0">
    <input type="hidden" name="is_universidad" value="0">
    <div class="tipo-unidad-checkboxes-row" role="group" aria-labelledby="tipoUnidadQuestion">
        <div class="checkbox-inline">
            <input type="checkbox" id="flag_administrativo" name="is_administrativo" value="1" @checked(old('is_administrativo', isset($item) && $item->is_administrativo ? '1' : '0') === '1')>
            <label for="flag_administrativo">Administrativo</label>
        </div>
        <span class="tipo-unidad-separator" aria-hidden="true">/</span>
        <div class="checkbox-inline">
            <input type="checkbox" id="flag_universidad" name="is_universidad" value="1" @checked(old('is_universidad', isset($item) && $item->is_universidad ? '1' : '0') === '1')>
            <label for="flag_universidad">Universidad</label>
        </div>
    </div>
</div>

@php
    $isAdminChecked = old('is_administrativo', isset($item) && $item->is_administrativo ? '1' : '0') === '1';
    $isUniChecked = old('is_universidad', isset($item) && $item->is_universidad ? '1' : '0') === '1';
    $showUnidadTipoSelect = $isAdminChecked && ! $isUniChecked;
@endphp

<div class="form-group" id="unidadTipoGroup" style="{{ $showUnidadTipoSelect ? '' : 'display: none;' }}">
    <select id="unidadTipo" name="unidad_tipo" style="width: 100%;" aria-label="Tipo de Unidad" {{ $showUnidadTipoSelect ? '' : 'disabled' }}>
        <option value="" {{ $defaultUnidadTipo === '' ? 'selected' : '' }}>Selecione un Dato</option>
        <option value="pierre" {{ $defaultUnidadTipo === 'pierre' ? 'selected' : '' }}>Pierre</option>
        <option value="palacio" {{ $defaultUnidadTipo === 'palacio' ? 'selected' : '' }}>Palacio</option>
        <option value="princes" {{ $defaultUnidadTipo === 'princes' ? 'selected' : '' }}>Princess</option>
    </select>
</div>

<div class="form-group">
    {{-- Campo para Pierre/Palacio/Princess (se llena desde la API) --}}
    <select id="nameSelect" name="name" disabled>
        <option value="">-- Seleccione Unidad de Negocio --</option>
        @if(!empty($currentValue) && in_array($defaultUnidadTipo, ['pierre','palacio','princes']))
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
        const unidadTipoSelect = document.getElementById('unidadTipo');
        const unidadTipoGroup = document.getElementById('unidadTipoGroup');
        const flagAdministrativo = document.getElementById('flag_administrativo');
        const flagUniversidad = document.getElementById('flag_universidad');
        const inputContainer = document.getElementById('nameInputContainer');
        const nameInputLabel = document.getElementById('nameInputLabel');
        if (!select || !unidadTipoSelect || !unidadTipoGroup || !flagAdministrativo || !flagUniversidad || !inputContainer || !nameInputLabel) return;

        if (flagAdministrativo.checked && flagUniversidad.checked) {
            flagUniversidad.checked = false;
        }

        // Valor inicial que viene del backend (old('name') o $item->name)
        const currentValue = (@json($currentValue) || '').trim();
        let cachedPropiedades = null; // Cacheamos para no consumir la API más de una vez
        let input = null;

        function isUniversidadFlagOn() {
            return flagUniversidad.checked;
        }

        function isAdministrativoFlagOn() {
            return flagAdministrativo.checked;
        }

        /** Muestra el select Pierre/Palacio/Princess solo con Administrativo y sin modo Universidad. */
        function syncUnidadTipoGroup() {
            const show = isAdministrativoFlagOn() && !isUniversidadFlagOn();
            unidadTipoGroup.style.display = show ? '' : 'none';
            unidadTipoSelect.disabled = !show;
            if (!show) {
                unidadTipoSelect.value = '';
            }
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

        function keywordFilterForUnidadTipo(unidadTipo) {
            // Ajusta estas palabras clave si la API regresa otros textos.
            const map = {
                pierre: 'Pierre',
                palacio: 'Palacio',
                princes: 'Princess',
            };
            return map[unidadTipo] || '';
        }

        function setMode(unidadTipo) {
            // Caso: aún no eligió (placeholder vacío)
            if (!unidadTipo) {
                if (input) {
                    input.remove();
                    input = null;
                }
                inputContainer.style.display = 'none';
                nameInputLabel.style.display = 'none';

                select.disabled = true;
                select.required = false;
                select.style.display = 'none';
                return;
            }

            const isUni = unidadTipo === 'universidad';
            if (isUni) {
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

            // Pierre/Palacio/Princess: select habilitado
            if (input) {
                input.remove();
                input = null;
            }
            inputContainer.style.display = 'none';
            nameInputLabel.style.display = 'none';

            select.disabled = false;
            select.required = true;
            select.style.display = 'block';
        }

        function populateSelectFromPropiedades(propiedades, unidadTipo) {
            // Para Pierre/Palacio/Princess pediste mostrar TODAS las unidades que regrese la API.
            // Por eso NO filtramos por keywords aquí.
            const list = Array.isArray(propiedades)
                ? propiedades
                : (propiedades?.propiedades ?? propiedades?.data ?? propiedades);

            if (!Array.isArray(list)) {
                console.warn('Respuesta inesperada al cargar propiedades:', propiedades);
                return;
            }

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

        async function loadPropiedadesIfNeeded(unidadTipo) {
            if (unidadTipo === 'universidad') return;
            if (cachedPropiedades) {
                populateSelectFromPropiedades(cachedPropiedades, unidadTipo);
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

            populateSelectFromPropiedades(cachedPropiedades, unidadTipo);
        }
        function getSelectedUnidadTipo() {
            return unidadTipoSelect.disabled ? '' : unidadTipoSelect.value;
        }

        /** Modo efectivo: checkbox Universidad usa flujo escribible sin el desplegable administrativo. */
        function getEffectiveUnidadTipo() {
            if (isUniversidadFlagOn()) {
                return 'universidad';
            }
            return getSelectedUnidadTipo();
        }

        async function applyTipoFromFlags() {
            syncUnidadTipoGroup();
            const effective = getEffectiveUnidadTipo();
            setMode(effective);
            if (effective && effective !== 'universidad') {
                try {
                    await loadPropiedadesIfNeeded(effective);
                } catch (e) {
                    console.error('No se pudo cargar propiedades para el select:', e);
                }
            }
        }

        unidadTipoSelect.addEventListener('change', async () => {
            if (unidadTipoSelect.disabled) return;
            const unidadTipo = getSelectedUnidadTipo();
            setMode(unidadTipo);
            try {
                await loadPropiedadesIfNeeded(unidadTipo);
            } catch (e) {
                console.error('No se pudo cargar propiedades para el select:', e);
            }
        });

        flagAdministrativo.addEventListener('change', () => {
            if (flagAdministrativo.checked) {
                flagUniversidad.checked = false;
            }
            applyTipoFromFlags();
        });

        flagUniversidad.addEventListener('change', () => {
            if (flagUniversidad.checked) {
                flagAdministrativo.checked = false;
            }
            applyTipoFromFlags();
        });

        // Inicializar: desplegable solo visible con Administrativo; Universidad activa flujo escribible.
        syncUnidadTipoGroup();
        const initialEffective = getEffectiveUnidadTipo();
        setMode(initialEffective);
        if (initialEffective && initialEffective !== 'universidad') {
            loadPropiedadesIfNeeded(initialEffective).catch(e => {
                console.error('No se pudo cargar propiedades al inicializar:', e);
            });
        }
    })();
</script>