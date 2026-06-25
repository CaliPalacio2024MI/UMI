<aside class="sidebar" role="navigation" aria-label="Menú de navegación principal">
    {{-- =================================================================== --}}
    {{-- BLOQUE LÓGICO: PRE-CALCULAR PERMISOS --}}
    {{-- =================================================================== --}}
    @php
        $user = Auth::user();

        // --- 1. CONTEXTO ---
        $universityName = 'Universidad Mundo Imperial';
        $activeInstitutionId = (int) session('active_institution_id', 0);
        $activeInstitution = $activeInstitutionId > 0
            ? \App\Models\Users\Institution::query()->find($activeInstitutionId)
            : null;
        $isUniversity = (bool) ($activeInstitution?->is_universidad ?? false);
        // Fallback por compatibilidad con datos antiguos/sesión.
        if (! $isUniversity) {
            $isUniversity = (session('active_institution_name') == $universityName);
        }

        // --- 2. ROLES ---
        $isMaster       = $user->hasActiveRole('master');
        $isControlAdmin = $user->hasActiveRole('control_administrativo'); 
        
        // Agrupando "Control Administrativo" y "Gerente TH"
        $isControlGroup = $isControlAdmin || $user->hasActiveRole('gerente_th');
        
        // Agrupando "Docente" y "Gerente Capacitación"
        $isDocenteGroup = $user->hasActiveRole('docente') || $user->hasActiveRole('gerente_capacitacion');
        
        // Agrupando "Estudiante" y "Anfitrión"
        $isStudentGroup = $user->hasActiveRole('estudiante') || $user->hasActiveRole('anfitrion');

        // --- 3. BANDERAS DE VISIBILIDAD ---

        // Submenú "Facturación": Solo en Universidad; Master y Control Admin ven submenú, resto enlace directo
        $hasFacturacionSubmenu = $isUniversity && ($isMaster || $isControlAdmin);

        // Submenú "Mi Información"
        $hasInfoSubmenu = $isStudentGroup || $isDocenteGroup || ($isMaster && $isUniversity);

        // Menú "Control Administrativo"
        $showControlMenu = ($isMaster || $isControlGroup) && $isUniversity;

        // Módulos (Checkboxes)
        $userModules = $user->academicProfile->modules ?? [];
        $canSeeEscolar    = $isMaster || ($isControlGroup && in_array('control_escolar', $userModules));
        $canSeeAcademico  = $isMaster || ($isControlGroup && in_array('control_academico', $userModules));
        $canSeePlaneacion = $isMaster || ($isControlGroup && in_array('planeacion_vinculacion', $userModules));

        // Menú "Ajustes"
        $showSettings = $isMaster || $isControlGroup;

        //CRM CTP
        $isCTP = $user->hasActiveRole('ctp');
        $isCoordinatorCTP = $user->hasActiveRole('coordinador_ctp');

        // Usuario aspirante (rol estudiante aún no aceptado como alumno):
        // sidebar: solo título institución, cerrar sesión y logo Mundo Imperial (sin menú).
        $activeRoleName = strtolower((string) session('active_role_name'));
        $academicStatus = trim((string) ($user->academicProfile->status ?? ''));
        $isAspiranteUser = $activeRoleName === 'estudiante'
            && ! in_array($academicStatus, ['Alumno', 'Alumno Activo', 'Alumno Inactivo'], true);

    @endphp

    {{-- =================================================================== --}}
    {{-- PARTE SUPERIOR (aspirante: solo título universidad; resto: logo institución) --}}
    {{-- =================================================================== --}}
    @if($isAspiranteUser)
        <div class="sidebar-top sidebar-top--aspirante" style="padding: 18px 14px 16px; text-align: center;">
            <span class="sidebar-aspirante-institution" style="display: block; font-weight: 400; color: #6b6b6b; font-size: 1rem; line-height: 1.35;">
                {{ session('active_institution_name', $universityName) }}
            </span>
        </div>
    @else
        <div class="sidebar-top">
            <div class="brand" style="margin-bottom: 5px">
                @if(session('active_institution_logo'))
                    <img src="{{ asset('storage/' . session('active_institution_logo')) }}" alt="Logo Institución" style="width: 100%; height: auto; max-width: 130px;" loading="lazy">
                @else
                    <span>{{ session('active_institution_name', 'Logo') }}</span>
                @endif
            </div>
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- MENÚ LATERAL --}}
    {{-- =================================================================== --}}
    <nav class="menu" aria-label="Menú principal">
        @if(!$isAspiranteUser)
        <ul>

            {{-- 1. MI INFORMACIÓN --}}
            @if($isUniversity)
                {{-- CASO UNIVERSIDAD: Muestra Submenú con Horario, Clases, etc. --}}
                <li class="has-submenu {{ request()->routeIs('MiInformacion.*') ? 'active' : '' }}">
                    <a href="{{ route('Facturacion.index') }}">
                        <span class="icon" aria-hidden="true">
                            <img src="{{ asset('images/icons/user-solid-full.svg') }}" alt="Info Icon" style="width:24px;height:24px" loading="lazy">
                        </span>
                        <span class="text">Mi Información</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i> </a>
                    <ul class="submenu">
                        <li class="{{ request()->routeIs('MiInformacion.index') ? 'active-submenu' : '' }}">
                            <a href="{{ route('MiInformacion.index') }}">Perfil</a>
                        </li>
                        {{-- Opciones académicas solo visibles en contexto Universidad --}}
                        @if(!$isCoordinatorCTP && !$isCTP)
                        <li class="{{ request()->routeIs('MiInformacion.clases') ? 'active-submenu' : '' }}">
                            <a href="{{ route('MiInformacion.clases') }}">Clases</a>
                        </li>
                        <li class="{{ request()->routeIs('MiInformacion.horario') ? 'active-submenu' : '' }}">
                            <a href="{{ route('MiInformacion.horario') }}">Horario</a>
                        </li>
                        <li class="{{ request()->routeIs('MiInformacion.historial') ? 'active-submenu' : '' }}">
                            <a href="{{ route('MiInformacion.historial') }}">Historial Académico</a>
                        </li>
                       
                        {{-- Opciones Extra (Boletas) --}}
                        @if($isDocenteGroup || $isStudentGroup)
                             <li class="{{ request()->routeIs('MiInformacion.boletas') ? 'active-submenu' : '' }}">
                                <a href="#">Boletas</a>
                            </li>
                        @endif
                        @endif
                    </ul>
                </li>
            @else
                {{-- CASO CORPORATIVO / ANFITRIÓN: Link Directo al Perfil (Sin flecha, sin submenú) --}}
                <li class="{{ request()->routeIs('MiInformacion.index') ? 'active' : '' }}">
                    <a href="{{ route('MiInformacion.index') }}">
                        <span class="icon" aria-hidden="true">
                            <img src="{{ asset('images/icons/user-solid-full.svg') }}" alt="Info Icon" style="width:24px;height:24px" loading="lazy">
                        </span>
                        <span class="text">Mi Información</span>
                    </a>
                </li>
            @endif

            {{-- 2. CURSOS --}}
            @if(!$isCoordinatorCTP && !$isCTP)
            <li class="has-submenu {{ request()->routeIs('Cursos.*') || request()->routeIs('courses.certificates.*') ? 'active' : '' }}">
                <a href="#">
                    <span class="icon" aria-hidden="true">
                        <img src="{{ asset('images/icons/desktop-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
                    </span>
                    <span class="text">Cursos</span>
                </a>
                
                {{-- Submenú --}}
                <ul class="submenu">
                    {{-- Opción 1: Cursos Disponibles (Ruta original) --}}
                    <li class="{{ request()->routeIs('Cursos.index') ? 'active-submenu' : '' }}">
                        <a href="{{ route('Cursos.index') }}">Cursos Disponibles</a>
                    </li>

                    {{-- Opción 2: Mis Certificados (Nueva Ruta) --}}
                    <li class="{{ request()->routeIs('courses.certificates.index') ? 'active-submenu' : '' }}">
                        <a href="{{ route('courses.certificates.index') }}">Mis Certificados</a> 
                    </li>
                    {{--Boton bilbioteca de temas--}}
                    <li>
                        <a href="{{ route('templates.index') }}">
                            <span>Biblioteca de temas</span>
                        </a>
                    </li>
                     {{--Boton bilbioteca de subtemas--}}
                    <li>
                        <a href="{{ route('subtopics_template.index') }}">
                            <span>Biblioteca de subtemas</span>
                        </a>
                    </li>

                </ul>
            </li>
            @endif

            {{-- 3. FACTURACIÓN (solo unidad Universidad Mundo Imperial) --}}
            @if($isUniversity && !$isCoordinatorCTP && !$isCTP)
            @if($hasFacturacionSubmenu)
                {{-- CASO A: Master y Control Administrativo (submenú debajo, mismo patrón que Cursos / Mi Información) --}}
                <li class="has-submenu {{ request()->routeIs('Facturacion.*', 'facturacion.conceptos.*') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icon" aria-hidden="true">
                            <img src="{{ asset('images/icons/money-bill-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
                        </span>
                        <span class="text">Facturación</span>
                    </a>
                    
                    <ul class="submenu">
                        <li class="{{ request()->routeIs('Facturacion.*') ? 'active-submenu' : '' }}">
                            <a href="{{ route('Facturacion.index') }}">Facturacion</a>
                        </li>
                        <li class="{{ request()->routeIs('facturacion.conceptos.*') ? 'active-submenu' : '' }}">
                            <a href="{{ route('facturacion.conceptos.index') }}">Conceptos y montos</a>
                        </li>
                    </ul>
                </li>
            @else
                {{-- CASO B: Alumnos, Docentes (Enlace directo sin submenú) --}}
                <li class="@if(request()->routeIs('Facturacion.*', 'facturacion.conceptos.*')) active @endif">
                    <a href="{{ route('Facturacion.index') }}">
                        <span class="icon" aria-hidden="true">
                            <img src="{{ asset('images/icons/money-bill-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
                        </span>
                        <span class="text">Facturacion</span>
                    </a>
                </li>
            @endif
            @endif

            {{-- 4. CONTROL ADMINISTRATIVO --}}
            @if($showControlMenu)
                <li class="has-submenu {{ request()->routeIs('control.*') || request()->routeIs('escolar.*') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icon" aria-hidden="true">
                            <img src="{{ asset('images/icons/school-circle-check-solid-full.svg') }}" alt="Control Icon" style="width:24px;height:24px" loading="lazy">
                        </span>
                        <span class="text">Control Administrativo</span>
                    </a>
                    
                    <ul class="submenu">
                        {{-- Orden: Control Académico → Control Escolar → Planeación y Vinculación --}}
                        @if($canSeeAcademico)
                            <li class="has-submenu {{ request()->routeIs('control.*') && !request()->routeIs('control.planeacion.*') ? 'active' : '' }}">
                                <a href="#">Control Académico</a>
                                <ul class="submenu">
                                    <li class="{{ request()->routeIs('control.careers.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.careers.index') }}">Carreras</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.subjects.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.subjects.index') }}">Materias</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.facilities.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.facilities.index') }}">Aulas</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.schedules.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.schedules.index') }}">Horarios</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.classes.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.classes.index') }}">Clases</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.teachers.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.teachers.index') }}">Docentes</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.students.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.students.index') }}">Alumnos</a>
                                    </li>
                                    <li class="{{ request()->is('control/academico/planeacion') ? 'active-submenu' : '' }}">
                                        <a href="#">Planeación Escolar</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if($canSeeEscolar)
                            <li class="has-submenu {{ request()->routeIs('escolar.*') ? 'active' : '' }}">
                                <a href="#">Control Escolar</a>
                                <ul class="submenu">
                                    <li class="{{ request()->routeIs('escolar.students.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('escolar.students.index') }}">Alumnos</a>
                                    </li>
                                    <li class="{{ request()->routeIs('escolar.boletas.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('escolar.boletas.index') }}">Boletas de calificaciones</a>
                                    </li>
                                    <li class="{{ request()->is('control-escolar/becas*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('escolar.becas.index') }}">Becas</a>
                                    </li>
                                    <li class="{{ request()->is('control-escolar/titulacion*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('escolar.titulacion.index') }}">Titulación</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if($canSeePlaneacion)
                            <li class="has-submenu {{ request()->is('control/planeacion/*') ? 'active' : '' }}">
                                <a href="#">Planeación y Vinc.</a>
                                <ul class="submenu">
                                    <li class="{{ request()->is('control/planeacion/presupuesto*') ? 'active-submenu' : '' }}">
                                        <a href="#">Presupuesto</a>
                                    </li>
                                    <li class="{{ request()->is('control/planeacion/practicas-profesionales*') ? 'active-submenu' : '' }}">
                                        <a href="#">Prácticas profesionales</a>
                                    </li>
                                    <li class="{{ request()->is('control/planeacion/servicio-social*') ? 'active-submenu' : '' }}">
                                        <a href="#">Servicio Social</a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
           <!--CRM-- sidebar-->
{{-- 4.5 CRM --}}
@if(($isMaster || $isControlGroup || $isCoordinatorCTP || $isCTP) && $isUniversity)
    <li class="has-submenu {{ request()->routeIs('crm.*') ? 'active' : '' }}">
        <a href="#">
            <span class="icon" aria-hidden="true">
                <img src="{{ asset('images/icons/crm.svg') }}"
                alt="CRM Icon"
                style="width:24px;height:24px"
                loading="lazy">
            </span>
            <span class="text">CRM</span>
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </a>

        <ul class="submenu">
            {{-- Leads: todos ven --}}
            <li class="{{ request()->routeIs('crm.leads') ? 'active-submenu' : '' }}">
                <a href="{{ route('crm.leads') }}">Leads</a>
            </li>

            {{-- Prospectos: solo Master y Coordinador --}}
            @if($isMaster || $isCoordinatorCTP || $isControlAdmin)
                <li class="{{ request()->routeIs('crm.prospectos') ? 'active-submenu' : '' }}">
                    <a href="{{ route('crm.prospectos') }}">Prospectos</a>
                </li>
            @endif

            {{-- Comisiones: Master y Coordinador --}}
            @if($isMaster || $isCoordinatorCTP || $isControlAdmin)
                <li class="{{ request()->routeIs('crm.comisiones') ? 'active-submenu' : '' }}">
                    <a href="{{ route('crm.comisiones') }}">Comisiones</a>
                </li>
            @endif

            {{-- Estadísticas: Master, Coordinador y CTP --}}
                @if($isMaster || $isCoordinatorCTP || $isCTP || $isControlAdmin)
                    <li class="{{ request()->routeIs('crm.estadisticas') ? 'active-submenu' : '' }}">
                        <a href="{{ route('crm.estadisticas') }}">Estadísticas</a>
                    </li>
                        @endif
                    </ul>
                </li>  {{-- ← este faltaba --}}
            @endif
<!--termina CRM-->

            {{-- 5. AJUSTES --}}
            @if($showSettings)
                <li class="has-submenu {{ request()->routeIs('ajustes.*') ? 'active open' : '' }}">
                    <a href="#">
                        <span class="icon" aria-hidden="true">
                            <img src="{{ asset('images/icons/user-gear-solid-full.svg') }}" alt="Ajustes Icon" style="width:24px;height:24px" loading="lazy">
                        </span>
                        <span class="text">Ajustes</span>
                    </a>
                    <ul class="submenu">
                        @if($isMaster)
                            @if($isUniversity)
                                <li class="{{ request()->is('ajustes/departments') ? 'active-submenu' : '' }}">
                                    <a href="{{ route('ajustes.show', 'departments') }}">Departamentos</a>
                                </li>
                                <li class="{{ request()->is('ajustes/periods') ? 'active-submenu' : '' }}">
                                    <a href="{{ route('ajustes.show', 'periods') }}">Periodos</a>
                                </li>
                            @else
                                <li class="{{ request()->is('ajustes/institutions') ? 'active-submenu' : '' }}">
                                    <a href="{{ route('ajustes.show', 'institutions') }}">Unidades de Negocio</a>
                                </li>
                                <li class="{{ request()->is('ajustes/departments') ? 'active-submenu' : '' }}">
                                    <a href="{{ route('ajustes.show', 'departments') }}">Departamentos</a>
                                </li>
                                <li class="{{ request()->is('ajustes/workstations') ? 'active-submenu' : '' }}">
                                    <a href="{{ route('ajustes.show', 'workstations') }}">Puestos</a>
                                </li>
                            @endif
                            <li class="{{ request()->is('ajustes/users') ? 'active-submenu' : '' }}">
                                <a href="{{ route('ajustes.show', 'users') }}">Usuarios</a>
                            </li>
                        @elseif($isControlGroup)
                            <li class="{{ request()->is('ajustes/users') ? 'active-submenu' : '' }}">
                                <a href="{{ route('ajustes.show', 'users') }}">Usuarios</a>
                            </li>
                            <li class="{{ request()->is('ajustes/periods') ? 'active-submenu' : '' }}">
                                <a href="{{ route('ajustes.show', 'periods') }}">Periodos</a>
                            </li>
                            <li class="{{ request()->is('ajustes/departments') ? 'active-submenu' : '' }}">
                                <a href="{{ route('ajustes.show', 'departments') }}">Departamentos</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

        </ul>
        @endif
    </nav>

    {{-- =================================================================== --}}
    {{-- PARTE INFERIOR --}}
    {{-- =================================================================== --}}
    <div class="sidebar-bottom">
        <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
            @csrf
            <button type="submit" class="btn-logout" aria-label="Cerrar sesión">
                <span class="icon" aria-hidden="true">
                    <img src="{{ asset('images/icons/right-to-bracket-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
                </span>
                <span class="text">Cerrar sesión</span>
            </button>
        </form>

        <div class="brand-bottom">
            <img src="{{ asset('images/LOGO3.png') }}" alt="Logo Mundo Imperial" loading="lazy">
        </div>
    </div>
</aside>