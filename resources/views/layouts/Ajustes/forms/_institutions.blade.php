<div class="form-group">
    <label for="name">Nombre de Unidad</label>

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

    <div class="form-group" style="margin-top: 6px; margin-bottom: 10px;">
        <select id="unidadTipo" name="unidad_tipo" style="width: 100%;">
            <option value="" {{ $defaultUnidadTipo === '' ? 'selected' : '' }}>Selecione un Dato</option>
            <option value="pierre" {{ $defaultUnidadTipo === 'pierre' ? 'selected' : '' }}>Pierre</option>
            <option value="palacio" {{ $defaultUnidadTipo === 'palacio' ? 'selected' : '' }}>Palacio</option>
            <option value="princes" {{ $defaultUnidadTipo === 'princes' ? 'selected' : '' }}>Princess</option>
            <option value="universidad" {{ $defaultUnidadTipo === 'universidad' ? 'selected' : '' }}>Universidad</option>
        </select>
    </div>

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
        const inputContainer = document.getElementById('nameInputContainer');
        const nameInputLabel = document.getElementById('nameInputLabel');
        if (!select || !unidadTipoSelect || !inputContainer || !nameInputLabel) return;

        // Valor inicial que viene del backend (old('name') o $item->name)
        const currentValue = (@json($currentValue) || '').trim();
        let cachedPropiedades = null; // Cacheamos para no consumir la API más de una vez
        let input = null;

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
            return unidadTipoSelect.value; // si es "" (placeholder), regresamos "" y no mostramos label de Uni
        }

        unidadTipoSelect.addEventListener('change', async () => {
            const unidadTipo = getSelectedUnidadTipo();
            setMode(unidadTipo);

            try {
                await loadPropiedadesIfNeeded(unidadTipo);
            } catch (e) {
                console.error('No se pudo cargar propiedades para el select:', e);
            }
        });

        // Inicializar estado según la selección actual del formulario.
        const initialUnidadTipo = getSelectedUnidadTipo();
        setMode(initialUnidadTipo);
        if (initialUnidadTipo !== 'universidad') {
            loadPropiedadesIfNeeded(initialUnidadTipo).catch(e => {
                console.error('No se pudo cargar propiedades al inicializar:', e);
            });
        }
    })();
</script>