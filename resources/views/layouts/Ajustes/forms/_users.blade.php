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
<p class="form-intro-question" style="font-weight: 600; color: #BC8A55; margin: 0 0 12px 0; font-size: 0.95rem;">
    ¿Qué usuario vas a crear?
</p>
<input type="hidden" name="tipo_usuario_creacion" id="tipo_usuario_creacion" value="{{ $tipoCreacion }}">
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
@endphp
<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="institution_id" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">Unidad de Negocio</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <div class="umi-form-select-pill" id="institution_id_pill">
                <select id="institution_id" name="institution_id" required class="form-control umi-form-select-pill-inner" style="width: 100%;">
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
        <span class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.85em; margin-top: 5px;">
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

<div class="form-group">
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <label for="RFC" id="rfc_field_label" style="flex: 0 0 auto; margin: 0; font-weight: 600; white-space: nowrap;">{{ $tipoCreacion === 'alumno' ? 'Usuario (RFC o CURP)' : 'Usuario (RFC)' }}</label>
        <div style="flex: 1 1 auto; min-width: 0;">
            <input type="text"
                   id="RFC"
                   name="RFC"
                   required
                   maxlength="{{ $tipoCreacion === 'alumno' ? 18 : 13 }}"
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
        <small style="display: block; color: #555; margin-top: 6px;">Dejar en blanco para no cambiar la contraseña.</small>
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
    <select id="department_id" name="department_id">
        <option value="">N/A</option>
        @foreach($departments as $department)
            <option value="{{ $department->id }}"
                    {{ (isset($item) && $item->department_id == $department->id) ? 'selected' : '' }}>
                {{ $department->name }}
            </option>
        @endforeach
    </select>
</div>


<div id="workstation-field-wrapper" class="form-group" 
     style="{{ $isUniversity ? 'display: none;' : '' }}">
    <label for="workstation_id">Puesto (Opcional)</label>
    
    
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

    
    
    const allWorkstations = @json($workstations ?? []); 
    
    const currentWorkstationId = @json(old('workstation_id', $item->workstation_id ?? null));

    
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
    const rfcInput = document.getElementById('RFC');
    const rfcLabel = document.getElementById('rfc_field_label');
    const apellidoPaternoInput = document.getElementById('apellido_paterno');
    const apellidoMaternoInput = document.getElementById('apellido_materno');

    function updateRfcFieldForTipo() {
        if (!rfcInput || !rfcLabel || !tipoHidden) return;
        const esAlumno = tipoHidden.value === 'alumno';
        rfcLabel.textContent = esAlumno ? 'Usuario (RFC o CURP)' : 'Usuario (RFC)';
        rfcInput.setAttribute('maxlength', esAlumno ? '18' : '13');
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

    function updateNombreFieldMode() {
        if (!tipoHidden || !nombreText || !nombreSelect || !nombreWrapperText || !nombreWrapperSelect) return;
        const esAlumno = tipoHidden.value === 'alumno';
        if (esAlumno) {
            nombreWrapperText.style.display = 'none';
            nombreWrapperSelect.style.display = '';
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
                'coordinador_ctp'
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

      
        if (selectedDepartmentId) {
            
            const filteredWorkstations = allWorkstations.filter(workstation => {
                
                return workstation.department_id == selectedDepartmentId;
            });

            console.log('Puestos filtrados:', filteredWorkstations);

            
            filteredWorkstations.forEach(workstation => {
                const option = document.createElement('option');
                option.value = workstation.id;
                option.textContent = workstation.name;
                
               
                if (currentWorkstationId && workstation.id == currentWorkstationId) {
                    option.selected = true;
                }
                workstationSelect.appendChild(option);
            });
        }
    }
    

    
    roleSelect.addEventListener('change', updateModuleVisibility);
    
    
    departmentSelect.addEventListener('change', updateWorkstationDropdown);

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

    updateNombreFieldMode();

    updateWorkstationDropdown();

}, 0);
</script>