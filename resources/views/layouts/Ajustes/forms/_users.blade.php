@php
    $isUniversity = $isActiveInstitutionUniversity ?? false;

    $nombreValor = old('nombre', $item->nombre ?? '');
    $aspirantesCrm = isset($aspirantes_crm_list) ? collect($aspirantes_crm_list) : collect();

    $tipoCreacion = old('tipo_usuario_creacion');
    if ($tipoCreacion === null && isset($item)) {
        $tipoCreacion = $item->hasRole('estudiante') ? 'alumno' : 'normal';
    }
    if ($tipoCreacion === null || ! in_array($tipoCreacion, ['normal', 'alumno'], true)) {
        $tipoCreacion = 'normal';
    }
    if (! $isUniversity) {
        $tipoCreacion = 'normal';
    }

    $selectedAspiranteLeadId = null;
    if (isset($item) && $tipoCreacion === 'alumno' && $aspirantesCrm->isNotEmpty()) {
        $selectedAspiranteLeadId = $aspirantesCrm->first(function ($a) use ($item) {
            $curpItem = strtoupper(preg_replace('/\s+/', '', (string) ($item->curp ?? '')));
            $curpLead = strtoupper(preg_replace('/\s+/', '', (string) ($a->alumno_curp ?? '')));
            $nombreOk = strcasecmp(trim((string) $a->alumno_nombre), trim((string) $item->nombre)) === 0
                && strcasecmp(trim((string) $a->alumno_paterno), trim((string) $item->apellido_paterno)) === 0
                && strcasecmp(trim((string) ($a->alumno_materno ?? '')), trim((string) ($item->apellido_materno ?? ''))) === 0;
            if (! $nombreOk) {
                return false;
            }
            if ($curpItem !== '' && $curpLead !== '') {
                return $curpItem === $curpLead;
            }

            return true;
        })?->id;
    }
@endphp
@if ($isUniversity)
<p class="form-intro-question" style="font-weight: 600; color: #BC8A55; margin: 0 0 12px 0; font-size: 0.95rem;">
    ¿Qué usuario vas a crear?
</p>
@endif
@if ($errors->any())
    <div style="margin: 0 0 10px 0; padding: 8px 10px; border: 1px solid #dc3545; border-radius: 6px; background: #fff5f5; color: #a61d2a; font-size: 0.9rem;">
        {{ $errors->first() }}
    </div>
@endif
<input type="hidden" name="tipo_usuario_creacion" id="tipo_usuario_creacion" value="{{ $tipoCreacion }}">
@if ($isUniversity)
<div class="form-group" style="margin-bottom: 14px;">
    <div class="tipo-usuario-inline" style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px;">
        <span style="display: inline-flex; align-items: center; gap: 6px;">
            <input type="checkbox" id="chk_usuario_normal" {{ $tipoCreacion === 'normal' ? 'checked' : '' }}>
            <label for="chk_usuario_normal" style="margin: 0; font-weight: 500; cursor: pointer;">Administrativo</label>
        </span>
        <span aria-hidden="true" style="color: #888; font-weight: 500; user-select: none;">/</span>
        <span style="display: inline-flex; align-items: center; gap: 6px;">
            <input type="checkbox" id="chk_alumno"
                {{ $tipoCreacion === 'alumno' ? 'checked' : '' }}
                @if(! $isUniversity) disabled @endif
                @if(! $isUniversity) title="Solo disponible en contexto Universidad" @endif>
            <label for="chk_alumno" style="margin: 0; font-weight: 500; cursor: pointer; {{ ! $isUniversity ? 'opacity: 0.6;' : '' }}">Alumno</label>
        </span>
    </div>
    @error('tipo_usuario_creacion')
        <span class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.85em; margin-top: 5px;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
@endif
<div class="form-group" id="nombre-form-group">
    <div class="nombre-row" style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label class="nombre-label" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Nombre(s)</label>
        <div class="nombre-field-slot" style="flex: 1 1 auto; min-width: 0;">
            <div id="nombre-wrapper-text" style="{{ $tipoCreacion === 'alumno' ? 'display: none;' : '' }}">
                <input type="text"
                       class="form-control"
                       id="nombre_text"
                       style="width: 100%;"
                       @if ($tipoCreacion !== 'alumno') name="nombre" required @endif
                       value="{{ $nombreValor }}"
                       maxlength="255"
                       placeholder="Escriba el nombre(s)"
                       autocomplete="given-name">
            </div>
            <div id="nombre-wrapper-select" style="{{ $tipoCreacion === 'alumno' ? '' : 'display: none;' }}">
                <input type="hidden"
                       id="nombre_alumno_submit"
                       value="{{ $nombreValor }}"
                       @if ($tipoCreacion === 'alumno') name="nombre" required @else disabled @endif>
                <select class="form-control"
                        id="nombre_select"
                        style="width: 100%;"
                        @if ($tipoCreacion === 'alumno') required @endif>
                    <option value="" disabled {{ $selectedAspiranteLeadId === null && ($nombreValor === '' || $nombreValor === null) ? 'selected' : '' }}>Seleccione el nombre</option>
                    @foreach ($aspirantesCrm as $asp)
                        <option value="{{ $asp->id }}"
                                data-nombre="{{ e($asp->alumno_nombre) }}"
                                data-paterno="{{ e($asp->alumno_paterno ?? '') }}"
                                data-materno="{{ e($asp->alumno_materno ?? '') }}"
                                data-curp="{{ e(strtoupper(preg_replace('/\s+/', '', (string) ($asp->alumno_curp ?? '')))) }}"
                            {{ (int) $selectedAspiranteLeadId === (int) $asp->id ? 'selected' : '' }}>
                            {{ $asp->alumno_nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="nombre-wrapper-api-select" style="{{ ! $isUniversity ? '' : 'display: none;' }}">
                <input type="hidden"
                       id="nombre_api_submit"
                       value="{{ $nombreValor }}"
                       @if (! $isUniversity) name="nombre" required @else disabled @endif>
                <select class="form-control"
                        id="nombre_api_select"
                        style="width: 100%;"
                        @if (! $isUniversity) required @endif>
                    <option value="" selected disabled>Seleccione el nombre</option>
                </select>
            </div>
        </div>
    </div>
    @error('nombre')
        <span class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.85em; margin-top: 5px;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
    <style>
        /* Contorno ovalado tipo “píldora” + sombra */
        #nombre-form-group #nombre_text,
        #nombre-form-group #nombre_select {
            border-radius: 999px;
            padding-left: 1rem;
            padding-right: 1rem;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        #nombre-form-group #nombre_text:focus,
        #nombre-form-group #nombre_select:focus {
            border-color: #cfd6e6;
            box-shadow: 0 4px 14px rgba(34, 63, 112, 0.16);
            outline: none;
        }
        /* Texto gris mientras no hay nombre elegido (placeholder del select) */
        #nombre-form-group #nombre_select:invalid {
            color: #888;
        }
        #nombre-form-group #nombre_select:valid {
            color: #212529;
        }
    </style>
</div>
<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="apellido_paterno" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Apellido Paterno</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <input type="text"
                   id="apellido_paterno"
                   name="apellido_paterno"
                   class="form-control"
                   style="width: 100%;"
                   required
                   value="{{ old('apellido_paterno', $item->apellido_paterno ?? '') }}">
        </div>
    </div>
    <style>
        #modalBody #apellido_paterno.form-control,
        #modalBody #apellido_materno.form-control,
        #modalBody #nombre_api_select.form-control,
        #modalBody #RFC.form-control,
        #modalBody #password.form-control {
            border: none;
            border-bottom: 2px solid #dc3545;
            border-radius: 0;
            box-shadow: none;
            transform: translateY(-5px);
        }
        #modalBody #apellido_paterno.form-control:focus,
        #modalBody #apellido_materno.form-control:focus,
        #modalBody #nombre_api_select.form-control:focus,
        #modalBody #RFC.form-control:focus,
        #modalBody #password.form-control:focus {
            border: none;
            border-bottom: 2px solid #b02a37;
            box-shadow: none;
            outline: none;
        }
    </style>
</div>
<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="apellido_materno" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Apellido Materno</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <input type="text"
                   id="apellido_materno"
                   name="apellido_materno"
                   class="form-control"
                   style="width: 100%;"
                   value="{{ old('apellido_materno', $item->apellido_materno ?? '') }}">
        </div>
    </div>
</div>

@php
    $selectedInstitutionId = old('institution_id');
    if ($selectedInstitutionId === null) {
        $selectedInstitutionId = isset($item) ? (optional($item->institutions)->first()->id ?? $item->institution_id ?? null) : null;
        $selectedInstitutionId = $selectedInstitutionId ?? session('active_institution_id');
    }
    $selectedInstitutionName = collect($institutions ?? [])->firstWhere('id', (int) $selectedInstitutionId)->name ?? '';
@endphp
<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="institution_id" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Unidad de Negocio</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <input type="text"
                   id="institution_id_display"
                   value="{{ $selectedInstitutionName }}"
                   style="width: 100%; margin-bottom: 8px; background-color: transparent; color: #444; cursor: not-allowed; border: none; border-bottom: 2px solid #dc3545; border-radius: 0; box-shadow: none; padding: 0.375rem 0;"
                   readonly>
            <input type="hidden" id="institution_id_hidden" name="institution_id" value="{{ $selectedInstitutionId }}">
            <div class="umi-form-select-pill" id="institution_id_pill" style="display: none;">
                <select id="institution_id" class="form-control umi-form-select-pill-inner" style="width: 100%;">
                    <option value="">Seleccione la ud. de negocio</option>
                    @foreach($institutions ?? [] as $inst)
                        <option value="{{ $inst->id }}" {{ $selectedInstitutionId == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @error('institution_id')
        <span class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.85em; margin-top: 6px; line-height: 1.25; white-space: normal; overflow-wrap: anywhere; word-break: break-word;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
    <style>
        .umi-form-select-pill {
            border-radius: 999px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .umi-form-select-pill:focus-within {
            border-color: #cfd6e6;
            outline: 0;
            box-shadow: 0 4px 14px rgba(34, 63, 112, 0.16);
        }
        .umi-form-select-pill .umi-form-select-pill-inner {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        #institution_id:invalid {
            color: #888;
        }
        #institution_id:valid {
            color: #212529;
        }
    </style>
</div>

<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="role_id_select" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Rol</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <div class="umi-form-select-pill" id="role_id_select_pill">
                <select id="role_id_select" name="role_id" required class="form-control umi-form-select-pill-inner" style="width: 100%;">
                    <option value="">Seleccione el rol</option>
                </select>
            </div>
        </div>
    </div>
    <style>
        #role_id_select:invalid {
            color: #888;
        }
        #role_id_select:valid {
            color: #212529;
        }
    </style>
</div>
<div class="form-group" style="margin-top: -4px;">
    <div style="display: flex; align-items: center; justify-content: flex-start; gap: 10px; width: 100%;">
        <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 6px;">
            <label for="role_switch_ui" style="position: relative; display: inline-block; width: 46px; height: 24px; margin: 0; cursor: pointer;">
                <input type="checkbox" id="role_switch_ui" style="opacity: 0; width: 0; height: 0;">
                <span style="position: absolute; inset: 0; background-color: #c6c6c6; border-radius: 999px; transition: 0.2s;"></span>
                <span style="position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25); transition: 0.2s;"></span>
            </label>
            <label id="role_switch_state" for="role_switch_ui" style="font-size: 0.9rem; color: #BC8A55; margin: 0; cursor: pointer;">Accesos</label>
        </div>
    </div>
</div>
<div class="form-group" id="institution-access-wrapper" style="margin-top: 6px;">
    <div id="institution-access-list" style="display: grid; gap: 8px;"></div>
</div>
<script>
(function () {
    const roleSwitch = document.getElementById('role_switch_ui');
    const roleSwitchState = document.getElementById('role_switch_state');
    const institutionSelect = document.getElementById('institution_id');
    const institutionHidden = document.getElementById('institution_id_hidden');
    const institutionDisplay = document.getElementById('institution_id_display');
    const institutionAccessWrapper = document.getElementById('institution-access-wrapper');
    const institutionAccessList = document.getElementById('institution-access-list');

    const selectedInstitutionIdFromServer = @json((string) ($selectedInstitutionId ?? ''));
    const allInstitutions = @json($all_institutions ?? []);

    if (!roleSwitch || !roleSwitchState) return;

    const defaultInstitutionOptions = institutionSelect
        ? Array.from(institutionSelect.options).map(option => ({
            value: option.value,
            text: option.textContent
        }))
        : [];

    function normalizeInstitutionList(sourceList) {
        return (sourceList || [])
            .map(item => ({
                value: String(item.value ?? item.id ?? ''),
                text: String(item.text ?? item.name ?? '')
            }))
            .filter(item => item.value !== '' && item.text.trim() !== '');
    }

    function populateInstitutionOptions(sourceList, selectedValue) {
        if (!institutionSelect) return;

        institutionSelect.innerHTML = '';

        normalizeInstitutionList(sourceList).forEach(item => {
            const option = document.createElement('option');
            option.value = item.value;
            option.textContent = item.text;

            if (String(selectedValue) !== '' && String(selectedValue) === option.value) {
                option.selected = true;
            }

            institutionSelect.appendChild(option);
        });

        if (!institutionSelect.value && institutionSelect.options.length > 0) {
            institutionSelect.selectedIndex = 0;
        }
    }

    function renderInstitutionAccessList(sourceList) {
        if (!institutionAccessList || !institutionSelect) return;

        institutionAccessList.innerHTML = '';

        const normalized = normalizeInstitutionList(sourceList);
        const selectedValue = String(
            institutionSelect.value ||
            selectedInstitutionIdFromServer ||
            ''
        );

        normalized.forEach(item => {
            const row = document.createElement('label');
            row.style.display = 'inline-flex';
            row.style.alignItems = 'center';
            row.style.gap = '10px';
            row.style.cursor = 'pointer';
            row.style.margin = '0';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = 'institution_access[]';
            input.value = item.value;

            // Marca por defecto la institución actual
            input.checked = selectedValue !== '' && selectedValue === item.value;

            input.style.width = '16px';
            input.style.height = '16px';

            const text = document.createElement('span');
            text.textContent = item.text;
            text.style.fontSize = '0.98rem';
            text.style.color = '#444';

            input.addEventListener('change', function () {
                const checked = institutionAccessList.querySelectorAll(
                    'input[name="institution_access[]"]:checked'
                );

                // Siempre debe quedar al menos una unidad seleccionada
                if (checked.length === 0) {
                    input.checked = true;
                    return;
                }

                // La última unidad marcada queda como unidad principal visible
                if (input.checked) {
                    institutionSelect.value = input.value;
                    syncInstitutionFields();
                }
            });

            row.appendChild(input);
            row.appendChild(text);
            institutionAccessList.appendChild(row);
        });
    }

    function ensureInstitutionSelection() {
        if (!institutionSelect) return;
        if (institutionSelect.value) return;

        const firstValid = Array.from(institutionSelect.options).find(opt =>
            String(opt.value || '').trim() !== ''
        );

        if (firstValid) {
            institutionSelect.value = firstValid.value;
        }
    }

    function syncInstitutionFields() {
        const selectedOption = institutionSelect?.options[institutionSelect.selectedIndex];

        if (institutionHidden) {
            institutionHidden.value = institutionSelect?.value || '';
        }

        if (institutionDisplay) {
            institutionDisplay.value = selectedOption
                ? (selectedOption.textContent || '').trim()
                : '';
        }
    }

    function paintSwitch() {
        const container = roleSwitch.nextElementSibling;
        const knob = container?.nextElementSibling;

        if (!container || !knob) return;

        container.style.backgroundColor = roleSwitch.checked ? '#e0b84f' : '#c6c6c6';
        knob.style.transform = roleSwitch.checked ? 'translateX(22px)' : 'translateX(0)';
        roleSwitchState.textContent = 'Accesos';

        if (!institutionSelect) return;

        if (roleSwitch.checked) {
            if (institutionAccessWrapper) {
                institutionAccessWrapper.style.display = 'block';
            }

            populateInstitutionOptions(
                allInstitutions,
                institutionSelect.value || selectedInstitutionIdFromServer
            );

            ensureInstitutionSelection();
            renderInstitutionAccessList(allInstitutions);
            syncInstitutionFields();

        } else {
            if (institutionAccessWrapper) {
                institutionAccessWrapper.style.display = 'none';
            }

            populateInstitutionOptions(
                defaultInstitutionOptions,
                institutionSelect.value || selectedInstitutionIdFromServer
            );

            ensureInstitutionSelection();
            renderInstitutionAccessList(defaultInstitutionOptions);
            syncInstitutionFields();
        }
    }

    const modalForm = document.getElementById('modalForm');

    if (modalForm) {
        modalForm.addEventListener('submit', function () {
            const requiredFields = modalForm.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                const isVisible = field.offsetParent !== null;

                if (!isVisible) {
                    field.dataset.wasRequired = '1';
                    field.removeAttribute('required');
                }
            });

            ensureInstitutionSelection();
            syncInstitutionFields();

            // Si no activó accesos, manda al menos la unidad principal
            const checkedAccess = modalForm.querySelectorAll(
                'input[name="institution_access[]"]:checked'
            );

            if (checkedAccess.length === 0 && institutionHidden?.value) {
                const hiddenAccess = document.createElement('input');
                hiddenAccess.type = 'hidden';
                hiddenAccess.name = 'institution_access[]';
                hiddenAccess.value = institutionHidden.value;
                modalForm.appendChild(hiddenAccess);
            }

            const invalidField = modalForm.querySelector(':invalid');

            if (invalidField) {
                const fieldName =
                    invalidField.getAttribute('name') ||
                    invalidField.getAttribute('id') ||
                    'campo desconocido';

                alert('No se puede guardar. Falta completar o corregir el campo: ' + fieldName);
                invalidField.focus();
            }
        });
    }

    if (institutionSelect) {
        institutionSelect.addEventListener('change', syncInstitutionFields);
    }

    roleSwitch.addEventListener('change', paintSwitch);

    paintSwitch();
    syncInstitutionFields();
})();
</script>
<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="RFC" id="rfc_field_label" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Usuario (RFC o CURP)</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <input type="text"
                   id="RFC"
                   name="RFC"
                   required
                   maxlength="18"
                   minlength="12"
                   class="form-control @error('RFC') is-invalid @enderror"
                   value="{{ old('RFC', $item->RFC ?? '') }}"
                   style="width: 100%; text-transform: uppercase;"
                   autocomplete="username">
        </div>
    </div>
    @error('RFC')
        <span class="invalid-feedback" role="alert" style="color: #dc3545; font-size: 0.85em; display: block; margin-top: 5px;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
<hr style="margin: 15px 0;">


{{-- Campo: Contraseña --}}
<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="password" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Contraseña</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   minlength="8"
                   style="width: 100%;"
                   {{ isset($item) ? '' : 'required' }}>
        </div>
    </div>
    @error('password')
        <span class="invalid-feedback" role="alert" style="color: #dc3545; font-size: 0.85em; display: block; margin-top: 5px;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror

    @if(isset($item))
        <small style="display: block; color: #555; margin-top: 6px;">Dejar en blanco para no cambiar la contraseña. Si capturas una nueva: mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.</small>
    @else
        <small style="display: block; color: #555; margin-top: 6px;">Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.</small>
    @endif
</div>

<hr style="margin: 15px 0;">


<div id="admin-modules-wrapper" class="form-group" style="display: none; border: 1px solid #eee; padding: 10px; border-radius: 4px; background-color: #f9f9f9;">
    <label>Módulos a Habilitar para Control Administrativo:</label>
    @php
        $modules = old('modules_enabled', $enabled_modules ?? []);
    @endphp
    <div class="checkbox-line">
        <input type="checkbox" id="module_control_academico" name="modules_enabled[]" value="control_academico"
               {{ in_array('control_academico', $modules) ? 'checked' : '' }}>
        <label for="module_control_academico">Control Académico</label>
    </div>
    <div class="checkbox-line">
        <input type="checkbox" id="module_planeacion_vinculacion" name="modules_enabled[]" value="planeacion_vinculacion"
               {{ in_array('planeacion_vinculacion', $modules) ? 'checked' : '' }}>
        <label for="module_planeacion_vinculacion">Planeación y Vinculación</label>
    </div>
    <div class="checkbox-line">
        <input type="checkbox" id="module_control_escolar" name="modules_enabled[]" value="control_escolar"
               {{ in_array('control_escolar', $modules) ? 'checked' : '' }}>
        <label for="module_control_escolar">Control Escolar</label>
    </div>

    @error('modules_enabled')
        <span class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.85em; margin-top: 5px;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

<div id="department-field-wrapper" class="form-group"
     style="{{ $isUniversity ? 'display: none;' : '' }}">
    <label for="department_id">Departamento (Opcional)</label>
    <input type="hidden" id="department_api_name" name="department_api_name" value="">
    <select id="department_id" name="department_id">
        <option value="">N/A</option>
        @if ($isUniversity)
            @foreach($departments as $department)
                <option value="{{ $department->id }}"
                        {{ (isset($item) && $item->department_id == $department->id) ? 'selected' : '' }}>
                    {{ $department->name }}
                </option>
            @endforeach
        @elseif(isset($item) && $item->department_id)
            <option value="{{ $item->department_id }}" selected>{{ optional($item->department)->name ?? 'Departamento' }}</option>
        @endif
    </select>
</div>


<div id="workstation-field-wrapper" class="form-group"
     style="{{ $isUniversity ? 'display: none;' : '' }}">
    <label for="workstation_id">Puesto (Opcional)</label>
    <input type="hidden" id="workstation_api_name" name="workstation_api_name" value="">


    <select id="workstation_id" name="workstation_id">
        <option value="">N/A</option>



    </select>
</div>


<script>
setTimeout(function() {

    const roleSelect = document.getElementById('role_id_select');
    const adminModulesWrapper = document.getElementById('admin-modules-wrapper');


    const departmentSelect = document.getElementById('department_id');
    const workstationSelect = document.getElementById('workstation_id');


    if (!roleSelect || !adminModulesWrapper || !departmentSelect || !workstationSelect) {
        console.error("Error inicializando script: Faltan elementos (roleSelect, adminModulesWrapper, departmentSelect, o workstationSelect).");
        return;
    }


    const allRoles = @json($all_roles ?? []);
    const currentRoleId = @json(old('role_id', $item->role_id ?? null));
    const universityName = @json($universityName);
    const adminRoleName = @json($adminRoleName);
    const activeInstitutionName = @json($activeInstitutionName);
const externalPropertyId = @json(
    optional(
        \App\Models\Users\Institution::find(
            session('active_institution_id')
        )
    )->external_property_id
);

console.log(
    'externalPropertyId:',
    externalPropertyId
);



    const allWorkstations = @json($workstations ?? []);
    const allDepartments = @json($departments ?? []);

    const currentDepartmentId = @json(old('department_id', $item->department_id ?? null));
    const currentWorkstationId = @json(old('workstation_id', $item->workstation_id ?? null));

    let anfitrionesCatalogList = [];


    console.log('--- DEBUG DATOS DE BLADE ---');
    console.log('Todos los Roles:', allRoles);
    console.log('Todos los Puestos:', allWorkstations);



    if (!Array.isArray(allRoles) || !universityName || !adminRoleName || !activeInstitutionName || !Array.isArray(allWorkstations)) {
        console.error('Error: Faltan variables clave de Blade (roles, nombres, o allWorkstations).');
        return;
    }


    function getSelectedRoleName() {
        const selectedRoleId = roleSelect.value;
        if (!selectedRoleId) return null;
        const selectedRole = allRoles.find(role => role.id == selectedRoleId);
        return selectedRole ? selectedRole.name : null;
    }


    function updateModuleVisibility() {
        const selectedRoleName = getSelectedRoleName();
        if (activeInstitutionName === universityName && selectedRoleName === adminRoleName) {
            adminModulesWrapper.style.display = 'block';
        } else {
            adminModulesWrapper.style.display = 'none';
        }
    }

    const tipoHidden = document.getElementById('tipo_usuario_creacion');
    const chkNormal = document.getElementById('chk_usuario_normal');
    const chkAlumno = document.getElementById('chk_alumno');
    const nombreWrapperText = document.getElementById('nombre-wrapper-text');
    const nombreWrapperSelect = document.getElementById('nombre-wrapper-select');
    const nombreText = document.getElementById('nombre_text');
    const nombreSelect = document.getElementById('nombre_select');
    const nombreAlumnoHidden = document.getElementById('nombre_alumno_submit');
    const nombreWrapperApiSelect = document.getElementById('nombre-wrapper-api-select');
    const nombreApiSelect = document.getElementById('nombre_api_select');
    const nombreApiHidden = document.getElementById('nombre_api_submit');
    const departmentApiHidden = document.getElementById('department_api_name');
    const workstationApiHidden = document.getElementById('workstation_api_name');
    const rfcInput = document.getElementById('RFC');
    const rfcLabel = document.getElementById('rfc_field_label');
    const apellidoPaternoInput = document.getElementById('apellido_paterno');
    const apellidoMaternoInput = document.getElementById('apellido_materno');

    function updateRfcFieldForTipo() {
        if (!rfcInput || !rfcLabel || !tipoHidden) return;
        rfcLabel.textContent = 'Usuario (RFC o CURP)';
        rfcInput.setAttribute('maxlength', '18');
        rfcInput.setAttribute('minlength', '12');
    }

    function syncNombreHiddenDesdeSelect() {
        if (!nombreSelect || !nombreAlumnoHidden) return;
        const opt = nombreSelect.options[nombreSelect.selectedIndex];
        if (!opt || !opt.value) {
            return;
        }
        nombreAlumnoHidden.value = opt.dataset.nombre || '';
    }

    /** Solo al elegir otro aspirante: rellena apellidos y CURP en el campo de usuario. */
    function aplicarDatosAspiranteDesdeSelect() {
        if (!nombreSelect || !nombreAlumnoHidden) return;
        const opt = nombreSelect.options[nombreSelect.selectedIndex];
        if (!opt || !opt.value) {
            return;
        }
        nombreAlumnoHidden.value = opt.dataset.nombre || '';
        if (apellidoPaternoInput) {
            apellidoPaternoInput.value = opt.dataset.paterno || '';
        }
        if (apellidoMaternoInput) {
            apellidoMaternoInput.value = opt.dataset.materno || '';
        }
        if (rfcInput && opt.dataset.curp) {
            rfcInput.value = (opt.dataset.curp || '').toUpperCase();
        }
    }

    function normalizeCatalogLabel(s) {
        return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, ' ').trim();
    }

    function anfitrionDepartamentoNombre(a) {
        const v = a?.departamento_nombre ?? a?.departamentoNombre ?? a?.DepartamentoNombre
            ?? a?.department_nombre ?? a?.department_name ?? a?.departamento ?? a?.Departamento ?? '';
        return v.toString().trim();
    }

    function anfitrionPuestoNombre(a) {
        const v = a?.puesto_nombre ?? a?.puestoNombre ?? a?.PuestoNombre ?? a?.nombre_puesto ?? a?.nombrePuesto
            ?? a?.puesto ?? a?.Puesto ?? a?.posicion_nombre ?? a?.posicion ?? a?.cargo ?? '';
        return v.toString().trim();
    }

    function findLocalDepartmentId(apiDeptName) {
        const n = normalizeCatalogLabel(apiDeptName);
        const d = allDepartments.find((x) => normalizeCatalogLabel(x.name) === n);
        return d ? String(d.id) : '';
    }

    function findLocalWorkstationId(apiDeptName, apiPuestoName) {
        const deptId = findLocalDepartmentId(apiDeptName);
        if (!deptId) return '';
        const pn = normalizeCatalogLabel(apiPuestoName);
        const w = allWorkstations.find((x) => String(x.department_id) === deptId && normalizeCatalogLabel(x.name) === pn);
        return w ? String(w.id) : '';
    }

    function syncApiCatalogHiddenNames() {
        if (activeInstitutionName === universityName) return;
        if (departmentApiHidden) {
            const depOpt = departmentSelect ? departmentSelect.options[departmentSelect.selectedIndex] : null;
            const depName = (depOpt?.dataset?.apiDeptName || depOpt?.textContent || '').trim();
            departmentApiHidden.value = depName && depName !== 'N/A' ? depName : '';
        }
        if (workstationApiHidden) {
            const wsOpt = workstationSelect ? workstationSelect.options[workstationSelect.selectedIndex] : null;
            const wsName = (wsOpt?.dataset?.apiPuestoName || wsOpt?.textContent || '').trim();
            workstationApiHidden.value = wsName && wsName !== 'N/A' ? wsName : '';
        }
    }

    function populateDepartmentWorkstationSelectsFromAnfitriones(list) {
        if (activeInstitutionName === universityName || !departmentSelect) return;
        anfitrionesCatalogList = Array.isArray(list) ? list : [];
        const uniqueApiDepts = [];
        const seen = new Set();
        anfitrionesCatalogList.forEach((a) => {
            const d = anfitrionDepartamentoNombre(a);
            const key = normalizeCatalogLabel(d);
            if (!d || seen.has(key)) return;
            seen.add(key);
            uniqueApiDepts.push(d);
        });
        uniqueApiDepts.sort((a, b) => a.localeCompare(b, 'es'));
        departmentSelect.innerHTML = '<option value="">N/A</option>';
        uniqueApiDepts.forEach((apiDept) => {
            const localId = findLocalDepartmentId(apiDept);
            const opt = document.createElement('option');
            opt.value = localId || '';
            opt.textContent = apiDept;
            opt.dataset.apiDeptName = apiDept;
            departmentSelect.appendChild(opt);
        });
        if (currentDepartmentId) {
            const asStr = String(currentDepartmentId);
            for (let i = 0; i < departmentSelect.options.length; i++) {
                if (departmentSelect.options[i].value === asStr) {
                    departmentSelect.selectedIndex = i;
                    break;
                }
            }
        }
        updateWorkstationDropdown();
    }

    function applyAnfitrionFromApiSelect() {
        if (!nombreApiSelect || !nombreApiHidden) return;
        const opt = nombreApiSelect.options[nombreApiSelect.selectedIndex];
        if (!opt || !opt.value) return;
        const nombre = (opt.dataset.nombre || opt.textContent || '').trim();
        if (nombre) nombreApiHidden.value = nombre;
        if (apellidoPaternoInput) apellidoPaternoInput.value = (opt.dataset.paterno || '').trim();
        if (apellidoMaternoInput) apellidoMaternoInput.value = (opt.dataset.materno || '').trim();
        if (rfcInput && opt.dataset.rfc) {
            rfcInput.value = (opt.dataset.rfc || '').toUpperCase();
        }
        const dApi = (opt.dataset.apiDepartamento || '').trim();
        const pApi = (opt.dataset.apiPuesto || '').trim();
        if (!dApi || activeInstitutionName === universityName || !departmentSelect || !workstationSelect) return;
        for (let i = 0; i < departmentSelect.options.length; i++) {
            const o = departmentSelect.options[i];
            if (normalizeCatalogLabel(o.dataset.apiDeptName || '') === normalizeCatalogLabel(dApi)) {
                departmentSelect.selectedIndex = i;
                break;
            }
        }
        updateWorkstationDropdown();
        if (!pApi) {
            if (workstationSelect.options.length > 1) workstationSelect.selectedIndex = 1;
            return;
        }
        for (let i = 0; i < workstationSelect.options.length; i++) {
            const o = workstationSelect.options[i];
            const label = (o.dataset.apiPuestoName || o.textContent || '').trim();
            if (normalizeCatalogLabel(label) === normalizeCatalogLabel(pApi) || normalizeCatalogLabel(o.textContent) === normalizeCatalogLabel(pApi)) {
                workstationSelect.selectedIndex = i;
                break;
            }
        }
        syncApiCatalogHiddenNames();
    }

    function keywordForInstitution(instName) {
        const x = (instName || '').toLowerCase();
        if (x.includes('palacio')) return 'palacio';
        if (x.includes('princess')) return 'princess';
        if (x.includes('pierre')) return 'pierre';
        if (x.includes('forum')) return 'forum';
        if (x.includes('arena')) return 'arena';
        if (x.includes('mundo imperial')) return 'mundo imperial';
        return '';
    }

    function extractAnfitrionesList(data) {
        if (Array.isArray(data)) return data;
        if (Array.isArray(data?.anfitriones)) return data.anfitriones;
        if (Array.isArray(data?.usuarios)) return data.usuarios;
        if (Array.isArray(data?.empleados)) return data.empleados;
        if (Array.isArray(data?.trabajadores)) return data.trabajadores;
        if (Array.isArray(data?.items)) return data.items;
        if (Array.isArray(data?.results)) return data.results;
        if (Array.isArray(data?.data?.anfitriones)) return data.data.anfitriones;
        if (Array.isArray(data?.data?.usuarios)) return data.data.usuarios;
        if (Array.isArray(data?.data?.empleados)) return data.data.empleados;
        if (Array.isArray(data?.data?.trabajadores)) return data.data.trabajadores;
        if (Array.isArray(data?.data?.items)) return data.data.items;
        if (Array.isArray(data?.data)) return data.data;
        return [];
    }

   async function loadAnfitrionesForBusinessUnit() {
    if (!nombreApiSelect) return;
    if (activeInstitutionName === universityName) return;

    try {
        nombreApiSelect.innerHTML = '<option value="" selected disabled>Cargando nombres...</option>';

        if (!externalPropertyId) {
            throw new Error('La institución activa no tiene external_property_id');
        }

        const res = await fetch(
            '/external-data?endpoint=' + encodeURIComponent('/api/external/propiedades/' + externalPropertyId + '/anfitriones'),
            {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }
        );

        if (!res.ok) {
            throw new Error('Usuarios HTTP ' + res.status);
        }

        const raw = await res.json();
        const list = extractAnfitrionesList(raw);

        if (!Array.isArray(list) || list.length === 0) {
            throw new Error('Sin usuarios para esta propiedad');
        }

        nombreApiSelect.innerHTML = '<option value="" selected disabled>Seleccione el nombre</option>';

        list.forEach((a, i) => {
            const rfc = (
                a?.RFC ??
                a?.rfc ??
                a?.Rfc ??
                a?.curp ??
                'XAXX010101000'
            ).toString().trim().toUpperCase();

            const nombreDirecto = (
                a?.Nombre ??
                a?.nombre ??
                a?.name ??
                a?.nombre_completo ??
                a?.full_name ??
                ''
            ).toString().trim();

            const paterno = (
                a?.ApellidoPaterno ??
                a?.apellido_paterno ??
                a?.primer_apellido ??
                a?.paterno ??
                ''
            ).toString().trim();

            const materno = (
                a?.ApellidoMaterno ??
                a?.apellido_materno ??
                a?.segundo_apellido ??
                a?.materno ??
                ''
            ).toString().trim();

            const nombre = nombreDirecto || [
                a?.primer_nombre,
                a?.first_name,
                paterno,
                materno
            ].filter(Boolean).join(' ').trim();

            if (!nombre || !rfc) return;

            const opt = document.createElement('option');
            opt.value = String(a?.id ?? i + 1);
            opt.textContent = nombre;
            opt.dataset.nombre = nombre;
            opt.dataset.rfc = rfc;
            opt.dataset.paterno = paterno;
            opt.dataset.materno = materno;
            opt.dataset.apiDepartamento = anfitrionDepartamentoNombre(a);
            opt.dataset.apiPuesto = anfitrionPuestoNombre(a);

            nombreApiSelect.appendChild(opt);
        });

        if (nombreApiSelect.options.length <= 1) {
            throw new Error('API sin usuarios válidos');
        }

        populateDepartmentWorkstationSelectsFromAnfitriones(list);

    } catch (e) {
        console.error('No se pudieron cargar usuarios desde API:', e);
        nombreApiSelect.innerHTML = '<option value="" selected disabled>No se pudieron cargar usuarios</option>';
    }
}

    function updateNombreFieldMode() {
        if (!tipoHidden || !nombreText || !nombreSelect || !nombreWrapperText || !nombreWrapperSelect) return;
        if (activeInstitutionName !== universityName) {
            nombreWrapperText.style.display = 'none';
            nombreWrapperSelect.style.display = 'none';
            if (nombreWrapperApiSelect) nombreWrapperApiSelect.style.display = '';
            nombreText.removeAttribute('name');
            nombreText.removeAttribute('required');
            nombreSelect.removeAttribute('name');
            nombreSelect.removeAttribute('required');
            if (nombreAlumnoHidden) {
                nombreAlumnoHidden.removeAttribute('name');
                nombreAlumnoHidden.removeAttribute('required');
                nombreAlumnoHidden.setAttribute('disabled', 'disabled');
            }
            if (nombreApiHidden) {
                nombreApiHidden.removeAttribute('disabled');
                nombreApiHidden.setAttribute('name', 'nombre');
                nombreApiHidden.setAttribute('required', 'required');
            }
            return;
        }
        const esAlumno = tipoHidden.value === 'alumno';
        if (esAlumno) {
            nombreWrapperText.style.display = 'none';
            nombreWrapperSelect.style.display = '';
            if (nombreWrapperApiSelect) nombreWrapperApiSelect.style.display = 'none';
            nombreText.removeAttribute('name');
            nombreText.removeAttribute('required');
            nombreSelect.removeAttribute('name');
            nombreSelect.setAttribute('required', 'required');
            if (nombreAlumnoHidden) {
                nombreAlumnoHidden.removeAttribute('disabled');
                nombreAlumnoHidden.setAttribute('name', 'nombre');
                nombreAlumnoHidden.setAttribute('required', 'required');
                if (!nombreSelect.value && nombreText.value.trim()) {
                    nombreAlumnoHidden.value = nombreText.value.trim();
                } else {
                    syncNombreHiddenDesdeSelect();
                }
            }
        } else {
            nombreWrapperSelect.style.display = 'none';
            nombreWrapperText.style.display = '';
            if (nombreWrapperApiSelect) nombreWrapperApiSelect.style.display = 'none';
            nombreSelect.removeAttribute('name');
            nombreSelect.removeAttribute('required');
            nombreText.setAttribute('name', 'nombre');
            nombreText.setAttribute('required', 'required');
            if (nombreAlumnoHidden) {
                nombreAlumnoHidden.removeAttribute('name');
                nombreAlumnoHidden.removeAttribute('required');
                nombreAlumnoHidden.setAttribute('disabled', 'disabled');
            }
            const opt = nombreSelect.options[nombreSelect.selectedIndex];
            if (opt && opt.dataset && opt.dataset.nombre && !nombreText.value.trim()) {
                nombreText.value = opt.dataset.nombre;
            }
        }
        updateRfcFieldForTipo();
    }

    function applyAlumnoRole() {
        if (activeInstitutionName !== universityName) return;
        const r = allRoles.find(role => role.name === 'estudiante');
        if (r) {
            roleSelect.value = String(r.id);
            updateModuleVisibility();
        }
    }

    function wireTipoUsuarioCheckboxes() {
        if (!tipoHidden || !chkNormal) return;
        chkNormal.addEventListener('change', function () {
            if (this.checked) {
                if (chkAlumno && !chkAlumno.disabled) chkAlumno.checked = false;
                tipoHidden.value = 'normal';
                updateNombreFieldMode();
                roleSelect.value = '';
                updateModuleVisibility();
            } else {
                this.checked = true;
            }
        });
        if (chkAlumno && !chkAlumno.disabled) {
            chkAlumno.addEventListener('change', function () {
                if (this.checked) {
                    chkNormal.checked = false;
                    tipoHidden.value = 'alumno';
                    applyAlumnoRole();
                    updateNombreFieldMode();
                } else {
                    this.checked = true;
                }
            });
        }
    }


    function updateRolesDropdown() {
        let filteredRoles = [];
        roleSelect.innerHTML = '<option value="">Seleccione el rol</option>';

        if (activeInstitutionName === universityName) {
            console.log("Filtro: Universidad");
            const uniRoles = [
                'estudiante',
                'docente',
                'control_administrativo',
                'control_escolar',
                'ctp',
                'coordinador_ctp',
                'master'
            ];

            filteredRoles = allRoles.filter(role => uniRoles.includes(role.name));
        } else {
            console.log("Filtro: Corporativo");
            const corpRoles = ['anfitrion', 'master', 'gerente_capacitacion', 'gerente_th'];
            filteredRoles = allRoles.filter(role => corpRoles.includes(role.name));
        }

        console.log('Roles Filtrados:', filteredRoles);
        filteredRoles.forEach(role => {
            const option = document.createElement('option');
            option.value = role.id;
            option.textContent = role.display_name;
            if (currentRoleId && role.id == currentRoleId) {
                option.selected = true;
            }
            roleSelect.appendChild(option);
        });
        updateModuleVisibility();
    }


    function updateWorkstationDropdown() {
        const selectedDepartmentId = departmentSelect.value;
        workstationSelect.innerHTML = '<option value="">N/A</option>';

        if (activeInstitutionName !== universityName && anfitrionesCatalogList.length > 0) {
            const selOpt = departmentSelect.selectedOptions[0];
            let apiDeptName = selOpt && selOpt.dataset ? (selOpt.dataset.apiDeptName || '').trim() : '';
            if (!apiDeptName && nombreApiSelect) {
                const selectedNombreOpt = nombreApiSelect.options[nombreApiSelect.selectedIndex];
                apiDeptName = (selectedNombreOpt?.dataset?.apiDepartamento || '').trim();
            }
            if (!apiDeptName) return;

            const puestos = [];
            const seenP = new Set();
            anfitrionesCatalogList.forEach((a) => {
                if (normalizeCatalogLabel(anfitrionDepartamentoNombre(a)) !== normalizeCatalogLabel(apiDeptName)) return;
                const p = anfitrionPuestoNombre(a);
                if (!p) return;
                const k = normalizeCatalogLabel(p);
                if (seenP.has(k)) return;
                seenP.add(k);
                puestos.push(p);
            });
            puestos.sort((a, b) => a.localeCompare(b, 'es'));
            puestos.forEach((apiPuesto) => {
                const wsId = findLocalWorkstationId(apiDeptName, apiPuesto);
                const opt = document.createElement('option');
                opt.value = wsId || '';
                opt.textContent = apiPuesto;
                opt.dataset.apiPuestoName = apiPuesto;
                if (currentWorkstationId && wsId && String(wsId) === String(currentWorkstationId)) {
                    opt.selected = true;
                }
                workstationSelect.appendChild(opt);
            });
            syncApiCatalogHiddenNames();
            return;
        }

        if (selectedDepartmentId) {
            const filteredWorkstations = allWorkstations.filter(workstation => {
                return String(workstation.department_id) === String(selectedDepartmentId);
            });

            console.log('Puestos filtrados:', filteredWorkstations);

            filteredWorkstations.forEach(workstation => {
                const option = document.createElement('option');
                option.value = workstation.id;
                option.textContent = workstation.name;

                if (currentWorkstationId && String(workstation.id) === String(currentWorkstationId)) {
                    option.selected = true;
                }
                workstationSelect.appendChild(option);
            });
        }
        syncApiCatalogHiddenNames();
    }



    roleSelect.addEventListener('change', updateModuleVisibility);


    departmentSelect.addEventListener('change', updateWorkstationDropdown);
    departmentSelect.addEventListener('change', syncApiCatalogHiddenNames);
    workstationSelect.addEventListener('change', syncApiCatalogHiddenNames);

    wireTipoUsuarioCheckboxes();

    if (nombreSelect) {
        nombreSelect.addEventListener('change', function () {
            aplicarDatosAspiranteDesdeSelect();
        });
    }
    if (nombreApiSelect) {
        nombreApiSelect.addEventListener('change', function () {
            applyAnfitrionFromApiSelect();
        });
    }

    updateRolesDropdown();

    if (tipoHidden && tipoHidden.value === 'alumno') {
        applyAlumnoRole();
    }

    loadAnfitrionesForBusinessUnit();
    updateNombreFieldMode();

    if (activeInstitutionName === universityName) {
        updateWorkstationDropdown();
    }
    syncApiCatalogHiddenNames();

}, 0);
</script>
