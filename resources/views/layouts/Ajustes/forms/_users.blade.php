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

    // En modo edición el nombre ya existe — siempre mostrar campo de texto, no el select de CRM.
    $modoEdicion = isset($item) && !empty($item->id);
    $nombreConTexto = $tipoCreacion !== 'alumno' || $modoEdicion;
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

@php
    $selectedInstitutionId = old('institution_id');
    if ($selectedInstitutionId === null) {
        $selectedInstitutionId = isset($item) ? (optional($item->institutions)->first()->id ?? $item->institution_id ?? null) : null;
        $selectedInstitutionId = $selectedInstitutionId ?? session('active_institution_id');
    }
    $selectedInstitutionName = collect($institutions ?? [])->firstWhere('id', (int) $selectedInstitutionId)->name ?? '';
@endphp

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
{{-- 1. Unidad de negocio (propiedad) --}}
<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="institution_id" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">{{ $isUniversity ? 'Unidad de Negocio' : 'Propiedad' }}</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <input type="text"
                   id="institution_id_display"
                   value="{{ $selectedInstitutionName }}"
                   style="{{ $isUniversity ? '' : 'display: none;' }} width: 100%; margin-bottom: 8px; background-color: transparent; color: #444; cursor: not-allowed; border: none; border-bottom: 2px solid #dc3545; border-radius: 0; box-shadow: none; padding: 0.375rem 0;"
                   readonly>
            <input type="hidden" id="institution_id_hidden" name="institution_id" value="{{ $selectedInstitutionId }}">
            <div class="umi-form-select-pill" id="institution_id_pill" style="{{ $isUniversity ? 'display: none;' : '' }}">
                <select id="institution_id" class="form-control umi-form-select-pill-inner" style="width: 100%;">
                    <option value="">{{ $isUniversity ? 'Seleccione la ud. de negocio' : 'Seleccione una propiedad' }}</option>
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
        /* Igual que los demás campos del modal (Departamento, Puesto...) */
        #modalBody #institution_id_pill,
        #modalBody #role_id_select_pill {
            border-radius: 8px;
            border: none;
            box-shadow: none;
            overflow: visible;
        }
        #modalBody #role_id_select_pill select#role_id_select,
        #modalBody #institution_id_pill select#institution_id {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px !important;
            border: 1px solid #cfd6e6 !important;
            border-radius: 8px;
            background-color: #fff !important;
        }
        #modalBody #role_id_select_pill select#role_id_select:focus,
        #modalBody #institution_id_pill select#institution_id:focus {
            border-color: #223f70 !important;
            box-shadow: 0 0 0 2px rgba(34, 63, 112, 0.12) !important;
            outline: none;
        }
        /* La clase global .form-control quita la flechita (appearance: none); aquí se regresa */
        #modalBody select.form-control,
        #modalBody select#institution_id {
            appearance: auto !important;
            -webkit-appearance: menulist !important;
            -moz-appearance: menulist !important;
        }
        /* Guardar a todo el ancho, como en Departamentos y Puestos */
        #modalForm:has(#nombre-form-group) > button[type="submit"] {
            width: 100%;
            margin: 24px 0 0;
        }
        #institution_id:invalid {
            color: #888;
        }
        #institution_id:valid {
            color: #212529;
        }
    </style>
</div>
{{-- 2-6. Departamento, Puesto, Nombre, Apellidos --}}
<div id="department-field-wrapper" class="form-group" 
     style="{{ $isUniversity ? 'display: none;' : '' }}">
    <label for="department_id">Departamento</label>
    <input type="hidden" id="department_api_name" name="department_api_name" value="">
    <select id="department_id" name="department_id" class="form-control">
        <option value="">N/A</option>
@foreach ($departments as $department)
    <option value="{{ $department->id }}"
        {{ (string) old('department_id', $item->department_id ?? '') === (string) $department->id ? 'selected' : '' }}>
        {{ $department->name }}
    </option>
@endforeach
    </select>
</div>
<div id="workstation-field-wrapper" class="form-group">
    <label for="workstation_id">Puesto</label>
    <input type="hidden" id="workstation_api_name"
           name="workstation_api_name" value="">

    <select id="workstation_id" name="workstation_id"
            class="form-control">
        <option value="">Selecciona un puesto</option>
    </select>
</div>
<div class="form-group" id="nombre-form-group">
        <div class="nombre-row" style="display: flex; align-items: center; gap: 12px; width: 100%; margin-bottom: 8px;">
        <label class="nombre-label" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Nombre(s)</label>
        <div class="nombre-field-slot" style="flex: 1 1 auto; min-width: 0;">
            <div id="nombre-wrapper-text" style="{{ $nombreConTexto ? '' : 'display: none;' }}">
            <input type="text"
                class="form-control"
                id="nombre_text"
                name="nombre"
                required
                style="width: 100%;"
                value="{{ $nombreValor }}"
                maxlength="255"
                placeholder="Escriba el nombre(s)"
                autocomplete="given-name">
            </div>
            <div id="nombre-wrapper-select" style="{{ $tipoCreacion === 'alumno' && !$modoEdicion ? '' : 'display: none;' }}">
                <input type="hidden"
                       id="nombre_alumno_submit"
                       value="{{ $nombreValor }}"
                       @if ($tipoCreacion === 'alumno' && !$modoEdicion) name="nombre" required @else disabled @endif>
                <select class="form-control"
                        id="nombre_select"
                        style="width: 100%;"
                        @if ($tipoCreacion === 'alumno' && !$modoEdicion) required @endif>
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
            <div id="nombre-wrapper-api-select" style="{{ ! $isUniversity && !$modoEdicion ? '' : 'display: none;' }}">
                <input type="hidden"
                       id="nombre_api_submit"
                       value="{{ $nombreValor }}"
                        @if (! $isUniversity && !$modoEdicion) name="nombre" required @else disabled @endif>
                <select class="form-control"
                        id="nombre_api_select"
                        style="width: 100%;"
                        @if (! $isUniversity && !$modoEdicion) required @endif>
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
<div class="form-group" style="margin-bottom: 18px;">
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
            #modalBody #nombre_text.form-control,
            #modalBody #apellido_paterno.form-control,
            #modalBody #apellido_materno.form-control,
            #modalBody #nombre_api_select.form-control,
            #modalBody #RFC.form-control,
            #modalBody #password.form-control {
                border: 1px solid #cfd6e6;
                border-radius: 8px;
                box-shadow: none;
                transform: none;
                margin-top: 0;
                padding: 10px 12px;
                min-height: 42px;
                box-sizing: border-box;
                background-color: #fff;
            }

            #modalBody #nombre_text.form-control:focus,
            #modalBody #apellido_paterno.form-control:focus,
            #modalBody #apellido_materno.form-control:focus,
            #modalBody #nombre_api_select.form-control:focus,
            #modalBody #RFC.form-control:focus,
            #modalBody #password.form-control:focus {
                border: 1px solid #223f70;
                box-shadow: 0 0 0 2px rgba(34, 63, 112, 0.12);
                outline: none;
            }
    </style>
</div>
<div class="form-group" style="margin-bottom: 18px;">
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

{{-- 7. RFC --}}
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

{{-- 8. Contraseña --}}
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

{{-- 9. Rol y módulos --}}
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
            #modalBody #role_id_select.form-control {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            border: 1px solid #cfd6e6;
            border-radius: 8px;
            background-color: #fff;
            color: #212529;
            box-shadow: none;
        }

        #modalBody #role_id_select.form-control:focus {
            border-color: #223f70;
            outline: none;
            box-shadow: 0 0 0 2px rgba(34, 63, 112, 0.12);
        }
        #role_id_select:invalid {
            color: #888;
        }
        #role_id_select:valid {
            color: #212529;
        }
    </style>
</div>
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

{{-- 10. Agregar propiedades adicionales --}}
<div class="form-group" id="extra-properties-switch" style="margin-top: 6px;">
    <div style="display: flex; align-items: center; gap: 14px; width: 100%;">
        <label id="role_switch_state" for="role_switch_ui" style="margin: 0; font-weight: 600; white-space: nowrap; cursor: pointer;">Agregar Propiedades Adicionales</label>
        <label for="role_switch_ui" style="position: relative; display: inline-block; width: 46px; height: 24px; margin: 0; cursor: pointer; flex: 0 0 auto;">
            <input type="checkbox" id="role_switch_ui" style="opacity: 0; width: 0; height: 0;">
            <span style="position: absolute; inset: 0; background-color: #c6c6c6; border-radius: 999px; transition: 0.2s;"></span>
            <span style="position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25); transition: 0.2s;"></span>
        </label>
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
        const userInstitutionIds = @json(array_map('strval', $user_institution_ids ?? []));

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
            if (!institutionAccessList) return;
            institutionAccessList.innerHTML = '';

            const normalized = normalizeInstitutionList(sourceList);
            // La "principal" es la unidad por defecto (donde se registra el usuario).
            const primaryValue = String((institutionHidden && institutionHidden.value) || selectedInstitutionIdFromServer || '');

            normalized.forEach(item => {
                const row = document.createElement('label');
                row.style.display = 'inline-flex';
                row.style.alignItems = 'center';
                row.style.gap = '10px';
                row.style.cursor = 'pointer';
                row.style.margin = '0';

                const isPrimary = primaryValue !== '' && primaryValue === item.value;

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.name = 'institution_access[]';
                input.value = item.value;
                input.style.width = '16px';
                input.style.height = '16px';

                if (isPrimary) {
                    // La principal siempre incluida (se envía vía institution_id).
                    input.checked = true;
                    input.disabled = true;
                } else {
                    // Multi-selección: se pueden habilitar varias propiedades extra.
                    input.checked = userInstitutionIds.includes(item.value);
                }

                const text = document.createElement('span');
                text.textContent = item.text + (isPrimary ? ' (principal)' : '');
                text.style.fontSize = '0.98rem';
                text.style.color = '#444';

                row.appendChild(input);
                row.appendChild(text);
                institutionAccessList.appendChild(row);
            });
        }

        function ensureInstitutionSelection() {
            if (!institutionSelect) return;
            if (institutionSelect.value) return;
            const firstValid = Array.from(institutionSelect.options).find(opt => String(opt.value || '').trim() !== '');
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
                institutionDisplay.value = selectedOption ? (selectedOption.textContent || '').trim() : '';
            }
        }

        function paintSwitch() {
            const container = roleSwitch.nextElementSibling;
            const knob = container?.nextElementSibling;
            if (container && knob) {
                container.style.backgroundColor = roleSwitch.checked ? '#e0b84f' : '#c6c6c6';
                knob.style.transform = roleSwitch.checked ? 'translateX(22px)' : 'translateX(0)';
            }
            roleSwitchState.textContent = 'Agregar Propiedades Adicionales';

            // Solo mostrar/ocultar la lista de propiedades extra; la principal no cambia.
            if (institutionAccessWrapper) {
                institutionAccessWrapper.style.display = roleSwitch.checked ? 'block' : 'none';
            }
        }

        const modalForm = document.getElementById('modalForm');
        if (modalForm) {
            modalForm.addEventListener('submit', function () {
                // Evita bloqueo HTML5 por campos required ocultos (display:none, etc.)
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
                // Mensaje útil para detectar qué campo HTML5 está bloqueando el submit.
                const invalidField = modalForm.querySelector(':invalid');
                if (invalidField) {
                    const fieldName = invalidField.getAttribute('name') || invalidField.getAttribute('id') || 'campo desconocido';
                    alert('No se puede guardar. Falta completar o corregir el campo: ' + fieldName);
                    invalidField.focus();
                }
            });
        }
        if (institutionSelect) {
            institutionSelect.addEventListener('change', function () {
                syncInstitutionFields();
                // Al cambiar la principal, re-render para marcarla como "(principal)".
                renderInstitutionAccessList(allInstitutions);
            });
        }
        roleSwitch.addEventListener('change', paintSwitch);

        // Render inicial de la lista de propiedades extra.
        renderInstitutionAccessList(allInstitutions);

        // Si el usuario ya tiene propiedades extra (edición), abrir la lista para mostrarlas.
        const primaryNow = String((institutionHidden && institutionHidden.value) || selectedInstitutionIdFromServer || '');
        const hasExtras = userInstitutionIds.some(id => id !== primaryNow);
        if (hasExtras) roleSwitch.checked = true;

        paintSwitch();
        syncInstitutionFields();
    })();
</script>

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
    const modoEdicion = @json($modoEdicion);

    
    
    const allWorkstations = @json($workstations ?? []);
    const allDepartments = @json($departments ?? []);
    

    const currentDepartmentId = @json(old('department_id', $item->department_id ?? null));
    const currentWorkstationId = @json(old('workstation_id', $item->workstation_id ?? null));

    let anfitrionesCatalogList = [];

    
    console.log('--- DEBUG DATOS DE BLADE ---');
    console.log('Todos los Roles:', allRoles);
    console.log('Todos los Puestos:', allWorkstations);
   


    // Toggle nombre text/select cuando cambia Administrativo ↔ Alumno,
    // independiente del guard de variables para que siempre funcione.
    (function wireNombreToggleEarly() {
        const _chkNormal  = document.getElementById('chk_usuario_normal');
        const _chkAlumno  = document.getElementById('chk_alumno');
        const _tipoHidden = document.getElementById('tipo_usuario_creacion');
        const _wrapText   = document.getElementById('nombre-wrapper-text');
        const _wrapSelect = document.getElementById('nombre-wrapper-select');
        const _wrapApi    = document.getElementById('nombre-wrapper-api-select');
        const _inputText  = document.getElementById('nombre_text');
        const _inputHidden= document.getElementById('nombre_alumno_submit');
        const _inputSel   = document.getElementById('nombre_select');

        function _switchToAdmin() {
            if (_tipoHidden) _tipoHidden.value = 'normal';
            if (_wrapText)   _wrapText.style.display   = '';
            if (_wrapSelect) _wrapSelect.style.display  = 'none';
            if (_wrapApi)    _wrapApi.style.display     = 'none';
            if (_inputText)  { _inputText.setAttribute('name','nombre'); _inputText.setAttribute('required','required'); }
            if (_inputHidden){ _inputHidden.removeAttribute('name'); _inputHidden.removeAttribute('required'); _inputHidden.setAttribute('disabled','disabled'); }
            if (_inputSel)   { _inputSel.removeAttribute('name'); _inputSel.removeAttribute('required'); }
        }
        function _switchToAlumno() {
            if (_tipoHidden) _tipoHidden.value = 'alumno';
            // En edición el nombre ya existe — mantener campo de texto.
            if (typeof modoEdicion !== 'undefined' && modoEdicion) return;
            if (_wrapText)   _wrapText.style.display   = 'none';
            if (_wrapSelect) _wrapSelect.style.display  = '';
            if (_wrapApi)    _wrapApi.style.display     = 'none';
            if (_inputText)  { _inputText.removeAttribute('name'); _inputText.removeAttribute('required'); }
            if (_inputHidden){ _inputHidden.removeAttribute('disabled'); _inputHidden.setAttribute('name','nombre'); _inputHidden.setAttribute('required','required'); }
            if (_inputSel)   _inputSel.setAttribute('required','required');
        }

        if (_chkNormal) {
            _chkNormal.addEventListener('change', function () {
                if (this.checked) { if (_chkAlumno && !_chkAlumno.disabled) _chkAlumno.checked = false; _switchToAdmin(); }
                else this.checked = true;
            });
        }
        if (_chkAlumno && !_chkAlumno.disabled) {
            _chkAlumno.addEventListener('change', function () {
                if (this.checked) { if (_chkNormal) _chkNormal.checked = false; _switchToAlumno(); }
                else this.checked = true;
            });
        }
    })();

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
    const v =
        a?.departamento_nombre ??
        a?.departamentoNombre ??
        a?.DepartamentoNombre ??
        a?.department_nombre ??
        a?.department_name ??
        a?.departamento?.nombre ??
        a?.departamento?.name ??
        a?.Departamento?.Nombre ??
        a?.Departamento?.nombre ??
        a?.department?.name ??
        a?.department?.nombre ??
        a?.departamento ??
        a?.Departamento ??
        '';

    if (typeof v === 'object' && v !== null) {
        return String(v.nombre ?? v.name ?? v.descripcion ?? '').trim();
    }

    return String(v).trim();
}


function anfitrionPuestoNombre(a) {
    const v =
        a?.puesto_nombre ??
        a?.puestoNombre ??
        a?.PuestoNombre ??
        a?.nombre_puesto ??
        a?.nombrePuesto ??
        a?.puesto?.nombre ??
        a?.puesto?.name ??
        a?.Puesto?.Nombre ??
        a?.Puesto?.nombre ??
        a?.position?.name ??
        a?.position?.nombre ??
        a?.posicion_nombre ??
        a?.posicion ??
        a?.cargo ??
        a?.puesto ??
        a?.Puesto ??
        '';

    if (typeof v === 'object' && v !== null) {
        return String(v.nombre ?? v.name ?? v.descripcion ?? '').trim();
    }

    return String(v).trim();
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
            const depName = (depOpt?.dataset?.apiDeptName || '').trim();
            departmentApiHidden.value = depName && depName !== 'N/A' ? depName : '';
        }
        if (workstationApiHidden) {
            const wsOpt = workstationSelect ? workstationSelect.options[workstationSelect.selectedIndex] : null;
            const wsName = (wsOpt?.dataset?.apiPuestoName || '').trim();
            workstationApiHidden.value = wsName && wsName !== 'N/A' ? wsName : '';
        }
    }

function populateDepartmentWorkstationSelectsFromAnfitriones(list) {
    if (activeInstitutionName === universityName || !departmentSelect) return;

    anfitrionesCatalogList = Array.isArray(list) ? list : [];

    const apiDepartments = anfitrionesCatalogList
        .map(a => anfitrionDepartamentoNombre(a))
        .filter(Boolean);

    const departments = apiDepartments.length
        ? [...new Map(apiDepartments.map(name => [
            normalizeCatalogLabel(name), name
        ])).values()]
        : allDepartments.map(d => d.name);

    departmentSelect.replaceChildren(new Option('Selecciona un departamento', ''));

    departments.sort((a, b) => a.localeCompare(b, 'es')).forEach(name => {
        const local = allDepartments.find(
            d => normalizeCatalogLabel(d.name) === normalizeCatalogLabel(name)
        );

        const option = new Option(name, local ? local.id : '');
        option.dataset.apiDeptName = name;
        departmentSelect.appendChild(option);
    });

    if (currentDepartmentId) {
        departmentSelect.value = String(currentDepartmentId);
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
            let list = [];
            try {
                const primaryRes = await fetch('/external-data?endpoint=' + encodeURIComponent('/api/external/propiedades/1/anfitriones'), {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (primaryRes.ok) {
                    const primaryRaw = await primaryRes.json();
                    list = extractAnfitrionesList(primaryRaw);
                }
            } catch (e) {
                list = [];
            }

            if (!Array.isArray(list) || list.length === 0) {
                const propsRes = await fetch('/external-data?endpoint=/api/external/propiedades', {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!propsRes.ok) throw new Error('Propiedades HTTP ' + propsRes.status);
                const propsData = await propsRes.json();
                const props = Array.isArray(propsData) ? propsData : (propsData?.propiedades ?? propsData?.data ?? []);
                if (!Array.isArray(props) || props.length === 0) throw new Error('Sin propiedades');

                const key = keywordForInstitution(activeInstitutionName);
                let prop = null;
                if (key) {
                    prop = props.find(p => {
                        const n = (p?.nombre ?? p?.name ?? p?.descripcion ?? '').toString().toLowerCase();
                        return n.includes(key);
                    }) || null;
                }
                if (!prop) prop = props[0];

                const getPropId = (p) => p?.id ?? p?.id_propiedad ?? p?.property_id ?? p?.propiedad_id ?? null;
                const propIdsToTry = [];
                const firstPropId = getPropId(prop);
                if (firstPropId) propIdsToTry.push(String(firstPropId));
                props.forEach((p) => {
                    const id = getPropId(p);
                    if (id && !propIdsToTry.includes(String(id))) propIdsToTry.push(String(id));
                });
                if (propIdsToTry.length === 0) throw new Error('Sin property id');

                let lastErr = null;
                for (const propId of propIdsToTry) {
                    const endpoints = [
                        '/api/external/propiedades/' + propId + '/anfitriones',
                        '/api/external/propiedades/' + propId + '/usuarios',
                        '/api/external/propiedades/' + propId + '/empleados',
                        '/api/external/anfitriones?propiedad_id=' + propId,
                        '/api/external/usuarios?propiedad_id=' + propId,
                    ];
                    for (const ep of endpoints) {
                        try {
                            const res = await fetch('/external-data?endpoint=' + encodeURIComponent(ep), {
                                method: 'GET',
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                credentials: 'same-origin'
                            });
                            if (!res.ok) {
                                lastErr = new Error(ep + ' HTTP ' + res.status);
                                continue;
                            }
                            const raw = await res.json();
                            const extracted = extractAnfitrionesList(raw);
                            if (Array.isArray(extracted) && extracted.length > 0) {
                                list = extracted;
                                break;
                            }
                            lastErr = new Error(ep + ' sin datos');
                        } catch (err) {
                            lastErr = err;
                        }
                    }
                    if (list.length > 0) break;
                }
                if (!Array.isArray(list) || list.length === 0) throw (lastErr || new Error('Sin anfitriones'));
            }

            nombreApiSelect.innerHTML = '';
            list.forEach((a, i) => {
                const rfc = (
                    a?.RFC ??
                    a?.rfc ??
                    a?.Rfc ??
                    a?.curp ??
                    ''
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
                    a?.Nombre,
                    a?.nombre,
                    a?.primer_nombre,
                    a?.first_name,
                    paterno,
                    materno
                ].filter(Boolean).join(' ').trim();
                // Contrato de integración: solo opciones con RFC y Nombre válidos.
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
            console.log('Anfitriones recibidos:', list.length, list[0]);

            populateDepartmentWorkstationSelectsFromAnfitriones(list);
            filtrarNombresPorDepartamentoYPuesto();

            if (nombreApiSelect.options.length <= 1) {
                throw new Error('API sin anfitriones válidos (requiere RFC y Nombre)');
            }

} catch (e) {
    console.error('No se pudieron cargar nombres desde API:', e);

    // Usar los catálogos locales de UMI como alternativa.
    if (departmentSelect) {
        departmentSelect.innerHTML =
            '<option value="">Selecciona un departamento</option>';

        allDepartments.forEach(department => {
            const option = document.createElement('option');
            option.value = String(department.id);
            option.textContent = department.name;

            if (
                currentDepartmentId &&
                String(currentDepartmentId) === String(department.id)
            ) {
                option.selected = true;
            }

            departmentSelect.appendChild(option);
        });
    }

    if (workstationSelect) {
        updateWorkstationDropdown();
    }

    syncApiCatalogHiddenNames();

    if (nombreApiSelect) {
        nombreApiSelect.innerHTML =
            '<option value="" selected disabled>La lista de nombres requiere conexión con la API</option>';
    }
}

    }


function filtrarNombresPorDepartamentoYPuesto() {
    if (!nombreApiSelect) return;

    const departamentoOption =
        departmentSelect.options[departmentSelect.selectedIndex];

    const puestoOption =
        workstationSelect.options[workstationSelect.selectedIndex];

    const departamentoSeleccionado = normalizeCatalogLabel(
        departamentoOption?.dataset?.apiDeptName ||
        departamentoOption?.textContent ||
        ''
    );

    const puestoSeleccionado = normalizeCatalogLabel(
        puestoOption?.dataset?.apiPuestoName ||
        puestoOption?.textContent ||
        ''
    );

    const opciones = Array.from(nombreApiSelect.options);

    // Quitar mensajes anteriores sin borrar los nombres originales.
    opciones.forEach(option => {
        if (option.dataset.mensajeFiltro === '1') {
            option.remove();
        }
    });

    let coincidencias = 0;

    opciones.forEach(option => {
        if (!option.value) return;

        const departamentoPersona = normalizeCatalogLabel(
            option.dataset.apiDepartamento || ''
        );

        const puestoPersona = normalizeCatalogLabel(
            option.dataset.apiPuesto || ''
        );

        const mostrar =
            departamentoSeleccionado !== '' &&
            departamentoSeleccionado !== 'n/a' &&
            puestoSeleccionado !== '' &&
            puestoSeleccionado !== 'n/a' &&
            departamentoPersona === departamentoSeleccionado &&
            puestoPersona === puestoSeleccionado;

        option.hidden = !mostrar;
        option.disabled = !mostrar;

        if (mostrar) coincidencias++;
    });

    const seleccionActual = nombreApiSelect.selectedOptions[0];

    if (
        !seleccionActual ||
        seleccionActual.disabled ||
        seleccionActual.hidden ||
        !seleccionActual.value
    ) {
        nombreApiSelect.value = '';

        if (nombreApiHidden) nombreApiHidden.value = '';
        if (apellidoPaternoInput) apellidoPaternoInput.value = '';
        if (apellidoMaternoInput) apellidoMaternoInput.value = '';
        if (rfcInput) rfcInput.value = '';
    }

    if (coincidencias === 0) {
        const mensaje = document.createElement('option');
        mensaje.value = '';
        mensaje.textContent =
            'Selecciona un departamento y puesto con personas registradas';
        mensaje.disabled = true;
        mensaje.selected = true;
        mensaje.dataset.mensajeFiltro = '1';

        nombreApiSelect.insertBefore(mensaje, nombreApiSelect.firstChild);
    }
}


    function updateNombreFieldMode() {
    if (!tipoHidden || !nombreText || !nombreSelect ||
        !nombreWrapperText || !nombreWrapperSelect) return;

    if (modoEdicion) {
        nombreWrapperText.style.display = '';
        nombreWrapperSelect.style.display = 'none';

        if (nombreWrapperApiSelect) {
            nombreWrapperApiSelect.style.display = 'none';
        }

        nombreText.setAttribute('name', 'nombre');
        nombreText.setAttribute('required', 'required');

        if (nombreApiHidden) {
            nombreApiHidden.removeAttribute('name');
            nombreApiHidden.removeAttribute('required');
            nombreApiHidden.disabled = true;
        }

        if (nombreAlumnoHidden) {
            nombreAlumnoHidden.removeAttribute('name');
            nombreAlumnoHidden.removeAttribute('required');
            nombreAlumnoHidden.disabled = true;
        }

        return;
    }

if (activeInstitutionName !== universityName) {
    nombreWrapperText.style.display = 'none';
    nombreWrapperSelect.style.display = 'none';

    if (nombreWrapperApiSelect) {
        nombreWrapperApiSelect.style.display = '';
    }

    nombreText.removeAttribute('name');
    nombreText.removeAttribute('required');

    nombreSelect.removeAttribute('name');
    nombreSelect.removeAttribute('required');

    if (nombreAlumnoHidden) {
        nombreAlumnoHidden.removeAttribute('name');
        nombreAlumnoHidden.removeAttribute('required');
        nombreAlumnoHidden.disabled = true;
    }

    if (nombreApiHidden) {
        nombreApiHidden.disabled = false;
        nombreApiHidden.setAttribute('name', 'nombre');
        nombreApiHidden.setAttribute('required', 'required');
    }

    return;
}
        
        const esAlumno = tipoHidden.value === 'alumno';
        if (esAlumno && !modoEdicion) {
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
    const selectedOption =
        departmentSelect.options[departmentSelect.selectedIndex];
    const departmentId = departmentSelect.value;
    const apiDepartmentName = selectedOption?.dataset?.apiDeptName || '';

    workstationSelect.innerHTML = '<option value="">N/A</option>';

 if (
    activeInstitutionName !== universityName &&
    !departmentId &&
    apiDepartmentName &&
    anfitrionesCatalogList.length > 0
) {
        const puestos = new Map();

        anfitrionesCatalogList.forEach(a => {
            const departamento = anfitrionDepartamentoNombre(a);
            const puesto = anfitrionPuestoNombre(a);

            if (
                normalizeCatalogLabel(departamento) ===
                    normalizeCatalogLabel(apiDepartmentName) &&
                puesto
            ) {
                puestos.set(normalizeCatalogLabel(puesto), puesto);
            }
        });

        [...puestos.values()]
            .sort((a, b) => a.localeCompare(b, 'es'))
            .forEach(puesto => {
                const option = document.createElement('option');
                option.value = puesto;
                option.textContent = puesto;
                option.dataset.apiPuestoName = puesto;
                workstationSelect.appendChild(option);
            });

        if (puestos.size > 0) {
    syncApiCatalogHiddenNames();
    return;
}
    }

    if (departmentId) {
        allWorkstations
            .filter(w => String(w.department_id) === String(departmentId))
            .forEach(w => {
                const option = document.createElement('option');
                option.value = w.id;
                option.textContent = w.name;

                if (
                    currentWorkstationId &&
                    String(w.id) === String(currentWorkstationId)
                ) {
                    option.selected = true;
                }

                workstationSelect.appendChild(option);
            });
    }

    syncApiCatalogHiddenNames();
}
    

    // =====================================================================
    // CASCADA (no universidad): Propiedad -> Departamento -> Puesto -> Nombre
    // Todo sale de la API externa, filtrado por la propiedad elegida.
    // Al elegir el nombre se llenan apellidos y RFC.
    // =====================================================================
    const propertySelect = document.getElementById('institution_id');
    const apiCache = new Map();

    async function apiGet(endpoint) {
        if (apiCache.has(endpoint)) return apiCache.get(endpoint);
        const promise = fetch('/external-data?endpoint=' + encodeURIComponent(endpoint), {
            method: 'GET',
            cache: 'no-store',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok || data?.error) throw new Error(data?.error || ('HTTP ' + res.status));
            return data;
        });
        apiCache.set(endpoint, promise);
        promise.catch(() => apiCache.delete(endpoint));
        return promise;
    }

    function apiArray(data, keys) {
        if (Array.isArray(data)) return data;
        for (const k of keys) if (Array.isArray(data?.[k])) return data[k];
        if (Array.isArray(data?.data)) return data.data;
        for (const k of keys) if (Array.isArray(data?.data?.[k])) return data.data[k];
        return [];
    }

    const sameId = (a, b) => String(a ?? '').replace(/^0+/, '') === String(b ?? '').replace(/^0+/, '');

    function setOptions(select, placeholder, items, selectedName) {
        select.innerHTML = '';
        select.add(new Option(placeholder, ''));
        items.forEach((item) => {
            const opt = new Option(item.label, '');
            Object.entries(item.data || {}).forEach(([k, v]) => { opt.dataset[k] = v ?? ''; });
            select.add(opt);
            if (selectedName && normalizeCatalogLabel(item.label) === normalizeCatalogLabel(selectedName)) {
                opt.selected = true;
            }
        });
        select.disabled = false;
    }

    function setMessage(select, message) {
        select.innerHTML = '';
        select.add(new Option(message, ''));
    }

    // Nombres actuales (al editar) para preseleccionar.
    const currentDeptName = (allDepartments.find(d => String(d.id) === String(currentDepartmentId)) || {}).name || '';
    const currentPuestoName = (allWorkstations.find(w => String(w.id) === String(currentWorkstationId)) || {}).name || '';

    let apiPropertyId = null;
    let propertyAnfitriones = [];

    async function getApiPropertyId(localName) {
        const props = apiArray(await apiGet('/api/external/propiedades'), ['propiedades', 'properties', 'items', 'results']);
        const wanted = normalizeCatalogLabel(localName);
        const getId = p => p?.id_propiedad ?? p?.id ?? p?.propiedad_id ?? p?.property_id;
        const getName = p => p?.nombre ?? p?.propiedad_nombre ?? p?.name ?? p?.descripcion ?? '';
        const found = props.find(p => normalizeCatalogLabel(getName(p)) === wanted);
        return found ? String(getId(found)) : null;
    }

    async function getAnfitriones(propId) {
        const endpoints = [
            '/api/external/propiedades/' + propId + '/anfitriones',
            '/api/external/anfitriones?propiedad_id=' + propId,
            '/api/external/propiedades/' + propId + '/usuarios',
            '/api/external/propiedades/' + propId + '/empleados'
        ];
        for (const ep of endpoints) {
            try {
                const list = extractAnfitrionesList(await apiGet(ep));
                if (list.length) return list;
            } catch (e) { /* probar el siguiente */ }
        }
        console.warn('No se encontraron anfitriones para la propiedad', propId);
        return [];
    }

    function selectedData(select) {
        const opt = select.options[select.selectedIndex];
        return opt ? opt.dataset : {};
    }

    async function onPropertyChange() {
        const propOpt = propertySelect.options[propertySelect.selectedIndex];
        const localName = propOpt && propOpt.value ? propOpt.textContent.trim() : '';

        apiPropertyId = null;
        propertyAnfitriones = [];
        setMessage(workstationSelect, 'Esperando selección previa...');
        setMessage(nombreApiSelect, 'Esperando selección previa...');
        clearPersonFields();

        if (!localName) {
            setMessage(departmentSelect, 'Esperando selección previa...');
            syncApiCatalogHiddenNames();
            return;
        }

        setMessage(departmentSelect, 'Cargando departamentos...');
        try {
            apiPropertyId = await getApiPropertyId(localName);
            if (!apiPropertyId) {
                setMessage(departmentSelect, '-- Esta propiedad no está en la API --');
                syncApiCatalogHiddenNames();
                return;
            }

            const [deptData, anfitriones] = await Promise.all([
                apiGet('/api/external/propiedades/' + apiPropertyId + '/departamentos'),
                getAnfitriones(apiPropertyId)
            ]);
            propertyAnfitriones = anfitriones;

            const departments = apiArray(deptData, ['departamentos', 'departments', 'items', 'results'])
                .map(d => ({
                    label: String(d?.departamento_nombre ?? d?.nombre ?? d?.name ?? '').trim(),
                    data: { apiDeptName: String(d?.departamento_nombre ?? d?.nombre ?? d?.name ?? '').trim(),
                            apiDeptId: String(d?.id_departamento ?? d?.departamento_id ?? d?.id ?? '') }
                }))
                .filter(d => d.label)
                .sort((a, b) => a.label.localeCompare(b.label, 'es'));

            if (!departments.length) {
                setMessage(departmentSelect, '-- Sin departamentos en esta propiedad --');
            } else {
                setOptions(departmentSelect, 'Seleccione un departamento', departments, currentDeptName);
            }
        } catch (e) {
            console.error('Error al cargar departamentos de la propiedad:', e);
            setMessage(departmentSelect, '-- Error al cargar departamentos --');
        }

        syncApiCatalogHiddenNames();
        if (departmentSelect.selectedIndex > 0) await onDepartmentChange();
    }

    async function onDepartmentChange() {
        const dept = selectedData(departmentSelect);
        setMessage(nombreApiSelect, 'Esperando selección previa...');
        clearPersonFields();
        syncApiCatalogHiddenNames();

        if (!apiPropertyId || !dept.apiDeptId) {
            setMessage(workstationSelect, 'Esperando selección previa...');
            return;
        }

        setMessage(workstationSelect, 'Cargando puestos...');
        try {
            const data = await apiGet('/api/external/propiedades/' + apiPropertyId +
                '/departamentos/' + encodeURIComponent(dept.apiDeptId) + '/posiciones');
            const puestos = [...new Map(
                apiArray(data, ['posiciones', 'positions', 'puestos', 'items', 'results'])
                    .map(p => {
                        const label = String(p?.posicion_nombre ?? p?.puesto_nombre ?? p?.nombre ?? p?.name ?? '').trim();
                        return [normalizeCatalogLabel(label), {
                            label,
                            data: { apiPuestoName: label,
                                    apiPuestoId: String(p?.id_posicion ?? p?.posicion_id ?? p?.id_puesto ?? p?.id ?? '') }
                        }];
                    })
                    .filter(([k]) => k)
            ).values()].sort((a, b) => a.label.localeCompare(b.label, 'es'));

            if (!puestos.length) {
                setMessage(workstationSelect, '-- Este departamento no tiene puestos --');
            } else {
                setOptions(workstationSelect, 'Seleccione un puesto', puestos, currentPuestoName);
            }
        } catch (e) {
            console.error('Error al cargar puestos:', e);
            setMessage(workstationSelect, '-- Error al cargar puestos --');
        }

        syncApiCatalogHiddenNames();
        if (workstationSelect.selectedIndex > 0) onPuestoChange();
    }

    function anfitrionField(a, keys) {
        for (const k of keys) {
            const v = a?.[k];
            if (v !== undefined && v !== null && String(v).trim() !== '') return String(v).trim();
        }
        return '';
    }

    function onPuestoChange() {
        const dept = selectedData(departmentSelect);
        const puesto = selectedData(workstationSelect);
        clearPersonFields();
        syncApiCatalogHiddenNames();

        if (!puesto.apiPuestoName) {
            setMessage(nombreApiSelect, 'Esperando selección previa...');
            return;
        }

        const people = propertyAnfitriones.filter(a => {
            const estatus = normalizeCatalogLabel(a?.estatus ?? 'activo');
            if (estatus && estatus !== 'activo') return false;

            const aDeptId = anfitrionField(a, ['departamento_id', 'id_departamento', 'department_id']);
            const aPuestoId = anfitrionField(a, ['id_posicion', 'posicion_id', 'id_puesto', 'puesto_id']);
            const deptOk = (aDeptId && dept.apiDeptId && sameId(aDeptId, dept.apiDeptId))
                || normalizeCatalogLabel(anfitrionDepartamentoNombre(a)) === normalizeCatalogLabel(dept.apiDeptName);
            const puestoOk = (aPuestoId && puesto.apiPuestoId && sameId(aPuestoId, puesto.apiPuestoId))
                || normalizeCatalogLabel(anfitrionPuestoNombre(a)) === normalizeCatalogLabel(puesto.apiPuestoName);
            return deptOk && puestoOk;
        });

        const items = people.map(a => {
            const nombre = anfitrionField(a, ['Nombre', 'nombre', 'nombres', 'name', 'first_name']);
            const paterno = anfitrionField(a, ['apellido_paterno', 'ApellidoPaterno', 'primer_apellido', 'paterno']);
            const materno = anfitrionField(a, ['apellido_materno', 'ApellidoMaterno', 'segundo_apellido', 'materno']);
            const rfc = anfitrionField(a, ['RFC', 'rfc', 'Rfc', 'curp']).toUpperCase();
            return {
                label: nombre, // solo el nombre; los apellidos se llenan en sus campos
                data: { nombre, paterno, materno, rfc }
            };
        }).filter(i => i.data.nombre)
          .sort((a, b) => a.label.localeCompare(b.label, 'es'));

        if (!items.length) {
            setMessage(nombreApiSelect, '-- No hay personas con este puesto --');
            if (!propertyAnfitriones.length) console.warn('La API no devolvió anfitriones para esta propiedad.');
            return;
        }
        setOptions(nombreApiSelect, 'Seleccione un nombre', items, '');
    }

    function clearPersonFields() {
        if (nombreApiHidden) nombreApiHidden.value = '';
        if (apellidoPaternoInput) apellidoPaternoInput.value = '';
        if (apellidoMaternoInput) apellidoMaternoInput.value = '';
        if (rfcInput) rfcInput.value = '';
    }

    function onNombreChange() {
        const d = selectedData(nombreApiSelect);
        if (!d.nombre) { clearPersonFields(); return; }
        if (nombreApiHidden) nombreApiHidden.value = d.nombre;
        if (apellidoPaternoInput) apellidoPaternoInput.value = d.paterno || '';
        if (apellidoMaternoInput) apellidoMaternoInput.value = d.materno || '';
        if (rfcInput) rfcInput.value = d.rfc || '';
    }

    function initPropertyCascade() {
        if (!propertySelect || !nombreApiSelect) return;
        propertySelect.addEventListener('change', onPropertyChange);
        departmentSelect.addEventListener('change', onDepartmentChange);
        workstationSelect.addEventListener('change', onPuestoChange);
        nombreApiSelect.addEventListener('change', onNombreChange);
        onPropertyChange();
    }

    roleSelect.addEventListener('change', updateModuleVisibility);
    
    

if (activeInstitutionName === universityName) {
    departmentSelect.addEventListener('change', updateWorkstationDropdown);
    // Cargar los puestos al abrir el formulario, también al editar.
    updateWorkstationDropdown();
}

    wireTipoUsuarioCheckboxes();

    if (nombreSelect) {
        nombreSelect.addEventListener('change', function () {
            aplicarDatosAspiranteDesdeSelect();
        });
    }

    updateRolesDropdown();

    if (tipoHidden && tipoHidden.value === 'alumno') {
        applyAlumnoRole();
    }

    if (activeInstitutionName !== universityName) initPropertyCascade();
    updateNombreFieldMode();

    if (activeInstitutionName === universityName) {
        updateWorkstationDropdown();
    }
    syncApiCatalogHiddenNames();

}, 0);
</script>