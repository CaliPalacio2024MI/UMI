@extends('layouts.app')

@section('title', 'Lista de Alumnos - ' . session('active_institution_name'))

@section('content')

{{-- El ID "umi-app-view" es la clave para que el CSS de arriba aplique solo aquí --}}
<div id="umi-app-view">

    {{-- TÍTULO (Fijo) --}}
    <div class="umi-header">
        <h5 style="font-size: 2rem; font-weight: 700;">ALUMNOS</h5>
    </div>

    {{-- TOOLBAR (Fija) --}}
    <div class="umi-toolbar">
        <form action="{{ request()->url() }}" method="GET" class="umi-toolbar-search-form" style="display: flex; align-items: center; gap: 12px; flex-grow: 1; max-width: 580px;">
            <div class="umi-search-wrapper umi-search-wrapper--icon-left" style="flex: 1; max-width: none;">
                <span class="umi-search-icon umi-search-icon--left" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.91" stroke-miterlimit="10"><circle cx="9.14" cy="9.14" r="7.64"/><line x1="22.5" y1="22.5" x2="14.39" y2="14.39"/></svg>
                </span>
                <input type="text" name="search" id="search" class="umi-search-input"
                        placeholder="Buscar por..."
                        value="{{ request('search') }}">
            </div>
            <div class="umi-status-filter-wrapper" style="display: flex; align-items: center; border-radius: 50px; border: 1px solid #e0e0e0; background: #fff; box-shadow: 0 4px 14px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08); min-width: 200px; overflow: hidden;">
                <span class="umi-status-filter-icon-wrap" style="display: flex; align-items: center; justify-content: center; padding-left: 12px; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#002A4E" stroke-miterlimit="10" stroke-width="1.91"><circle cx="9.14" cy="9.14" r="7.64"/><line x1="22.5" y1="22.5" x2="14.39" y2="14.39"/></svg>
                </span>
                <select name="filter_status" onchange="this.form.submit()" class="umi-filter-select" style="flex: 1; padding: 6px 12px 6px 6px; border: none; background: transparent; color: #555; font-size: 0.9rem; cursor: pointer; outline: none; appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%2712%27 viewBox=%270 0 12 12%27%3E%3Cpath fill=%27%23555%27 d=%27M6 8L1 3h10z%27/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center;">
                    <option value="" {{ (request('filter_status') ?? '') === '' ? 'selected' : '' }}>Estatus</option>
                    <option value="activos" {{ request('filter_status') === 'activos' ? 'selected' : '' }}>Alumno activo</option>
                    <option value="inactivos" {{ request('filter_status') === 'inactivos' ? 'selected' : '' }}>Alumno inactivo</option>
                </select>
            </div>
        </form>

        {{-- Exportar a Excel (CSV) con filtros actuales --}}
        <form action="{{ request()->routeIs('control.*') ? route('control.students.export') : route('escolar.students.export') }}" method="GET" style="display: inline;">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request('filter_status'))
                <input type="hidden" name="filter_status" value="{{ request('filter_status') }}">
            @endif
            <button type="submit" class="umi-btn-secondary">
                <img src="{{ asset('images/icons/export-icon.svg') }}" alt="" width="20" height="20" style="vertical-align: middle;"> Exportar
            </button>
        </form>

        {{-- Botón abre modal Nuevo Registro de Aspirante (data-action para app.js SPA) --}}
        <button type="button" class="umi-btn" style="margin-left: auto;"
                data-action="open-modal-inscripcion"
                data-inscription-url="{{ route('escolar.inscripcion.create', ['modal' => 1]) }}">
            <i class="fa-solid fa-plus"></i> +Agregar alumnos
        </button>
    </div>

    {{-- CARD QUE CONTIENE LA TABLA (Área de crecimiento flexible) --}}
    <div class="umi-table-card">
        <div class="umi-table-scroll">
            <table style="width: 100%; table-layout: fixed;">
                <thead>
                    <tr>
                        <th>Curp</th>
                        <th>Nombre</th>
                        <th>Apellido<br>Paterno</th>
                        <th>Apellido<br>Materno</th>
                        <th style="text-align:center">Estatus</th>
                        <th style="min-width: 180px;">Carrera</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="data-table-body">
                    @forelse ($dataList as $user)
                        <tr>
                            <td style="font-family: monospace; font-size: 0.9rem; font-weight: bold;">
                                {{ $user->curp ?? '—' }}
                            </td>
                            <td style="font-weight: 700;">
                                {{ $user->nombre }}
                            </td>
                            <td style="font-weight: 700;">{{ $user->apellido_paterno }}</td>
                            <td style="font-weight: 700;">{{ $user->apellido_materno }}</td>
                            
                            {{-- Status con Lógica de Negocio Visual --}}
                            <td style="text-align:center">
                                @php
                                    $status = $user->academicProfile->status ?? 'Aspirante';
                                    $matricula = $user->academicProfile->matricula ?? null;
                                    
                                    $statusColor = match($status) {
                                        'Alumno Activo' => '#27ae60', // Verde
                                        'Alumno Inactivo' => '#e74c3c', // Rojo
                                        'Baja' => '#7f8c8d', // Gris
                                        'Egresado' => '#3498db', // Azul
                                        default => '#f39c12', // Naranja (Aspirante)
                                    };
                                @endphp

                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="color: #000; font-weight: bold; border: none; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; width: fit-content;">
                                        {{ $status }}
                                    </span>
                                </div>
                            </td>
                            <td style="font-weight: 600; color: #555;">
                                <div class="career-cell">
                                    {{ $user->academicProfile?->career?->name ?? 'Sin Asignar' }}
                                </div>
                            </td>
                            <td style="text-align:center; vertical-align: middle;">
                                <div class="actions-row umi-actions-icons" style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: nowrap;">
                                {{-- Botón Horario (mismo diseño que Horario de Docente) --}}
                                <button type="button" class="btn-icon data-btn-clock-student" title="Horario"
                                    data-student-horarios-url="{{ request()->routeIs('control.*') ? route('control.students.horarios', $user->id) : route('escolar.students.horarios', $user->id) }}"
                                    data-student-name="{{ trim($user->nombre . ' ' . ($user->apellido_paterno ?? '') . ' ' . ($user->apellido_materno ?? '')) }}"
                                    style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center;">
                                    <img src="{{ asset('images/icons/clock-solid-full-092034.svg') }}" alt="Horario" width="20" height="20">
                                </button>
                                {{-- Botón Ver Expediente --}}
                                <button type="button" class="btn-icon" title="Ver Expediente" style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center;"
                                    data-action="open-expediente" 
                                    data-name="{{ $user->nombre }} {{ $user->apellido_paterno }} {{ $user->apellido_materno }}"
                                    data-email="{{ $user->email }}"
                                    data-phone="{{ $user->telefono ?? 'N/A' }}"
                                    data-career="{{ $user->academicProfile->career->name ?? 'Sin Carrera' }}"
                                    data-semester="{{ $user->academicProfile->semestre ?? '1' }}"
                                    data-status="{{ $user->academicProfile->status ?? 'Pendiente' }}"
                                    data-matricula="{{ $user->academicProfile->matricula ?? 'No Asignada' }}"
                                    data-doc-acta="{{ $user->academicProfile->doc_acta_nacimiento ? Storage::url($user->academicProfile->doc_acta_nacimiento) : '' }}"
                                    data-doc-cert="{{ $user->academicProfile->doc_certificado_prepa ? Storage::url($user->academicProfile->doc_certificado_prepa) : '' }}"
                                    data-doc-curp="{{ $user->academicProfile->doc_curp ? Storage::url($user->academicProfile->doc_curp) : '' }}"
                                    data-doc-ine="{{ $user->academicProfile->doc_ine ? Storage::url($user->academicProfile->doc_ine) : '' }}">
                                    <img src="{{ asset('images/icons/eye-solid-full-bc8a55.svg') }}" alt="Ver">
                                </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                                No se encontraron alumnos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL 1: DETALLES DEL ALUMNO (EXPEDIENTE)                 --}}
{{-- ========================================================= --}}
<div id="modalInscripcion" class="modal-overlay" style="display: none; z-index: 10000;">
    <div class="modal-container" style="max-width: 95%; width: 900px; height: 90vh; display: flex; flex-direction: column;">
        <div style="flex: 1; min-height: 0;">
            <iframe id="iframeInscripcion" src="about:blank" style="width: 100%; height: 100%; min-height: 75vh; border: none;"></iframe>
        </div>
    </div>
</div>
{{-- ========================================================= --}}
{{-- MODAL 1: DETALLES DEL ALUMNO (EXPEDIENTE)                 --}}
{{-- ========================================================= --}}
<div id="studentDetailsModal" class="modal-overlay" style="display: none; z-index: 9999;">
    <div class="modal-container expediente-modal"> 
        <div class="modal-header">
            <h3> Expediente del Alumno</h3>
            <button type="button" class="modal-close" onclick="closeStudentDetails()">&times;</button>
        </div>
        
        <div class="modal-body-scroll">
            {{-- Encabezado del Alumno --}}
            <div class="student-summary">
                <div class="avatar-placeholder">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="student-info-header">
                    <h2 id="modalName" style="margin:0;">-</h2>
                    <span id="modalStatusBadge" class="badge-status">-</span>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

            <div class="details-grid">
                {{-- Columna Izquierda: Datos --}}
                <div class="details-column">
                    <h4 style="color:#666; border-bottom:1px solid #eee; padding-bottom:5px; margin-bottom:15px;">
                        <i class="fa-solid fa-id-card"></i> Información Personal
                    </h4>
                    <div class="detail-item">
                        <label>Matrícula:</label> <span id="modalMatricula" style="font-weight: bold; color: #2c3e50;">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label> <span id="modalEmail">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Teléfono:</label> <span id="modalPhone">-</span>
                    </div>
                    
                    <h4 style="color:#666; border-bottom:1px solid #eee; padding-bottom:5px; margin-bottom:15px; margin-top:20px;">
                        <i class="fa-solid fa-graduation-cap"></i> Académico
                    </h4>
                    <div class="detail-item">
                        <label>Carrera:</label> <span id="modalCareer">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Semestre:</label> <span id="modalSemester">-</span>
                    </div>
                </div>

                {{-- Columna Derecha: Documentos --}}
                <div class="details-column">
                    <h4 style="color:#666; border-bottom:1px solid #eee; padding-bottom:5px; margin-bottom:15px;">
                        <i class="fa-solid fa-folder-open"></i> Documentación
                    </h4>
                    <div class="docs-list">
                        <button id="btnDocActa" class="doc-btn hidden"><i class="fa-solid fa-file-pdf"></i> Acta de Nacimiento</button>
                        <button id="btnDocCert" class="doc-btn hidden"><i class="fa-solid fa-file-certificate"></i> Certificado Prepa</button>
                        <button id="btnDocCurp" class="doc-btn hidden"><i class="fa-solid fa-passport"></i> CURP</button>
                        <button id="btnDocIne" class="doc-btn hidden"><i class="fa-solid fa-id-card"></i> INE</button>
                        
                        <div id="noDocsMsg" class="no-docs" style="display:none;">
                            No hay documentos digitales cargados.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL 2: VISOR DE DOCUMENTOS (ENCIMA DEL PRIMER MODAL)    --}}
{{-- ========================================================= --}}
<div id="docViewerModal" class="modal-overlay" style="display: none; z-index: 10000;">
    <div class="modal-container" style="height: 90vh; width: 80%; max-width: 1000px;">
        <div class="modal-header" style="background: #333; color: white; border-radius: 8px 8px 0 0;">
            <h3 id="docViewerTitle" style="margin: 0; font-size: 1.1rem;">Visualizando Documento</h3>
            <button type="button" class="modal-close" onclick="closeDocViewer()" style="color: white;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0; height: 100%; background: #525659;">
            <iframe id="docViewerFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
        </div>
    </div>
</div>

{{-- ESTILOS Y SCRIPTS --}}
<style>
    /* Input de búsqueda: borde, sombra, altura menor, icono a la izquierda */
    #umi-app-view .umi-search-wrapper--icon-left { position: relative; }
    #umi-app-view .umi-search-icon--left {
        position: absolute;
        left: 14px;
        right: auto;
        top: 50%;
        transform: translateY(-50%);
        color: #223F70;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }
    #umi-app-view .umi-search-icon--left svg { display: block; }
    #umi-app-view .umi-search-input {
        border: 1px solid #ddd !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        padding: 5px 14px 5px 44px !important;
    }
    #umi-app-view .umi-search-input:focus {
        box-shadow: 0 4px 12px rgba(34, 63, 112, 0.2) !important;
    }
    /* Encabezado tabla: una sola barra ovalada, sin separación entre celdas */
    #umi-app-view table thead th {
        padding: 10px 10px !important;
        border-radius: 0 !important;
        border: none !important;
        text-transform: none !important;
    }
    #umi-app-view table thead tr:first-child th:first-child {
        border-top-left-radius: 12px !important;
        border-bottom-left-radius: 12px !important;
    }
    #umi-app-view table thead tr:first-child th:last-child {
        border-top-right-radius: 12px !important;
        border-bottom-right-radius: 12px !important;
    }
    /* Columna CURP (primera): nowrap para no partir el código */
    #umi-app-view table thead th:nth-child(1) { white-space: nowrap; }
    /* Columna Carrera (6ª): texto completo visible */
    #umi-app-view table thead th:nth-child(6) { white-space: nowrap; }
    #umi-app-view table tbody td:nth-child(6) {
        white-space: normal;
        line-height: 1.2;
        word-break: break-word;
    }
    #umi-app-view table tbody td:nth-child(6) .career-cell{
        word-break: break-word;
        white-space: normal;
    }
    /* Altura automática: que la card se achique según contenido */
    #umi-app-view { height: auto !important; }
    #umi-app-view .umi-table-card { flex: 0 0 auto !important; min-height: unset !important; }
    #umi-app-view .umi-table-scroll { height: auto !important; overflow-y: visible !important; }
    /* Fondo blanco y líneas rojas gruesas en la tabla */
    #umi-app-view table tbody td {
        background: #fff !important;
        border: none !important;
    }
    #umi-app-view table tbody td:not(:last-child) {
        border-right: 2px solid var(--umi-red-danger, #E74C3C) !important;
    }

    /* Columna Acciones: alinear iconos en fila */
    #umi-app-view .umi-actions-icons {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px;
        min-height: 32px;
    }
    #umi-app-view .umi-actions-icons img {
        width: 20px;
        height: 20px;
        object-fit: contain;
        vertical-align: middle;
    }
    #umi-app-view .umi-actions-icons .btn-icon {
        padding: 4px;
        line-height: 0;
        min-width: 28px;
        min-height: 28px;
    }
    /* Botones de acción: sin cambio de color al pasar el mouse */
    #umi-app-view button.btn-icon[data-action="open-expediente"]{
        border-radius: 12px;
        padding: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
    }
    /* Botones Toolbar (Estilo corregido) */
    .umi-btn-secondary{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    /* Se asume que var(--umi-blue-dark) es el color institucional */
    background-color: var(--umi-blue-dark);
    color: #FFFFFF;
    border: none;
    font-weight: 500; 
    font-size: 16px; 
    border-radius: 50px; 
    padding: 10px 30px; 
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
    text-decoration: none;
    box-shadow: 0 4px 6px rgba(34, 63, 112, 0.2);
    white-space: nowrap; 
}
.umi-btn-secondary:hover { background-color: #1a3055; color: #FFFFFF; }
    /* Botón +Agregar alumnos: sin elevación ni cambio de color al pasar el mouse */
    #umi-app-view .umi-btn:hover { color: #FFFFFF !important; transform: none; }

    /* Estilos generales de los modales (overlay, cerrar, etc.) */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; backdrop-filter: blur(2px); }
    
    /* ESTILO BASE/GRANDE: Aplica al Modal del Visor de Documentos (y como fallback) */
    .modal-container { 
        background: white; 
        width: 90%; 
        max-width: 800px; 
        border-radius: 12px; 
        display: flex; 
        flex-direction: column; 
        max-height: 90vh; 
        box-shadow: 0 15px 30px rgba(0,0,0,0.3); 
    }

    /* ESTILO ESPECÍFICO: Aplica solo al Modal del Expediente (clase: expediente-modal) */
    .expediente-modal {
        max-width: 800px; /* Tamaño reducido para el Expediente */
        max-height: 63vh; 
    }
    
    /* >>>>>>>>>>>>>>> NUEVA REGLA PARA EL TÍTULO DEL MODAL <<<<<<<<<<<<<<< */
    /* Se aplica el color azul oscuro institucional al título H3 del Expediente */
    .expediente-modal .modal-header h3 {
        color: var(--umi-blue-dark, #223F70); /* Usa la variable si existe, sino un azul oscuro seguro */
        font-weight: 700;
        margin: 0;
    }
    
    /* Media Query para pantallas pequeñas */
    @media (max-width: 650px) {
        .details-grid {
            grid-template-columns: 1fr; 
        }
        .expediente-modal {
            max-height: 90vh; 
            width: 95%;
        }
    }
    
    /* Estilos de elementos internos restantes */
    .modal-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; border-radius: 12px 12px 0 0; }
    .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; }
    .modal-body-scroll { padding: 25px; overflow-y: auto; flex: 1; }
    .modal-footer { padding: 15px 20px; border-top: 1px solid #eee; text-align: right; background: #f8f9fa; border-radius: 0 0 12px 12px; }
    
    .btn-secondary-modal { background: #e0e0e0; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; color: #333; font-weight: 600; }
    .btn-secondary-modal:hover { background: #d0d0d0; }

    .doc-btn { display: flex; align-items: center; width: 100%; padding: 12px 15px; margin-bottom: 10px; background: #fbfbfb; color: #2c3e50; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.2s; }
    .doc-btn:hover { background: #e3f2fd; border-color: #3498db; color: #223F70; transform: translateX(5px); }
    .doc-btn i { margin-right: 12px; font-size: 1.2rem; color: #e74c3c; }
    .hidden { display: none !important; }
    .no-docs { text-align: center; color: #aaa; font-style: italic; padding: 15px; border: 1px dashed #eee; border-radius: 8px; }

    /* Badges Status */
    .badge-status { border: 1px solid; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 0.8rem; }
    .badge-green { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
    .badge-orange { background: #fff3e0; color: #ef6c00; border-color: #ffe0b2; }
    .badge-gray { background: #f5f5f5; color: #616161; border-color: #e0e0e0; }

    .student-summary { display: flex; align-items: center; gap: 15px; }
    .avatar-placeholder { width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 20px; color: white; }
    
    /* ALINEACIÓN HEADER */
    .student-info-header {
        display: flex; 
        align-items: center; 
        gap: 15px; 
    }
    .student-info-header h2 {
        margin: 0; font-size: 1.4rem; color: #2c3e50;
    }

    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
    .detail-item { margin-bottom: 12px; }
    .detail-item label { font-weight: 600; color: #7f8c8d; width: 80px; display: inline-block; }
    
</style>
{{-- SCRIPTS --}}
<script>
    // =============================================================
    // 1. LÓGICA VISUAL (Funciones que muestran/ocultan)
    // =============================================================
    
    // --- VISOR DE DOCUMENTOS ---
    function openDocViewer(url, title) {
        if (!url || url === '') return;
        document.getElementById('docViewerFrame').src = url;
        document.getElementById('docViewerTitle').innerText = title;
        document.getElementById('docViewerModal').style.display = 'flex';
    }

    function closeDocViewer() {
        document.getElementById('docViewerModal').style.display = 'none';
        document.getElementById('docViewerFrame').src = ""; 
    }

    // --- EXPEDIENTE ALUMNO ---
    function openStudentDetails(data) {
        // 1. Llenar Textos
        document.getElementById('modalName').innerText = data.name;
        document.getElementById('modalEmail').innerText = data.email;
        document.getElementById('modalPhone').innerText = data.phone;
        document.getElementById('modalCareer').innerText = data.career;
        document.getElementById('modalSemester').innerText = data.semester;
        document.getElementById('modalMatricula').innerText = data.matricula;
        
        // 2. Configurar Badge (Etiqueta de color)
        const badge = document.getElementById('modalStatusBadge');
        badge.innerText = data.status;
        badge.className = 'badge-status'; 
        badge.classList.remove('badge-green', 'badge-orange', 'badge-gray');
        
        if(data.status === 'Alumno Activo') badge.classList.add('badge-green');
        else if(data.status === 'Aspirante') badge.classList.add('badge-orange');
        else badge.classList.add('badge-gray');

        // 3. Configurar Botones de Documentos
        let docsCount = 0;
        const configureBtn = (btnId, url, title) => {
            const btn = document.getElementById(btnId);
            if (url && url.trim() !== '') {
                btn.classList.remove('hidden');
                // Asignamos acción al botón del documento
                btn.onclick = function(e) { 
                    e.stopPropagation(); // Evita que el clic cierre el modal de abajo
                    openDocViewer(url, title); 
                };
                docsCount++;
            } else {
                btn.classList.add('hidden');
            }
        };

        configureBtn('btnDocActa', data.docActa, 'Acta de Nacimiento');
        configureBtn('btnDocCert', data.docCert, 'Certificado Preparatoria');
        configureBtn('btnDocCurp', data.docCurp, 'CURP');
        configureBtn('btnDocIne', data.docIne, 'INE');

        document.getElementById('noDocsMsg').style.display = (docsCount === 0) ? 'block' : 'none';
        document.getElementById('studentDetailsModal').style.display = 'flex';
    }

    function closeStudentDetails() {
        document.getElementById('studentDetailsModal').style.display = 'none';
    }

    function openModalInscripcion() {
        document.getElementById('iframeInscripcion').src = '{{ route('escolar.inscripcion.create', ['modal' => 1]) }}';
        document.getElementById('modalInscripcion').style.display = 'flex';
    }
    function cerrarModalInscripcion() {
        document.getElementById('modalInscripcion').style.display = 'none';
        document.getElementById('iframeInscripcion').src = 'about:blank';
    }

    // =============================================================
    // 2. EL SUPER LISTENER (Controla TODO: Abrir y Cerrar)
    // =============================================================
    document.addEventListener('click', function(event) {
        
        // --- CASO A: ABRIR EL EXPEDIENTE ---
        const openBtn = event.target.closest('[data-action="open-expediente"]');
        if (openBtn) {
            const d = openBtn.dataset;
            openStudentDetails({
                name: d.name, email: d.email, phone: d.phone,
                career: d.career, semester: d.semester, status: d.status,
                matricula: d.matricula, docActa: d.docActa, docCert: d.docCert,
                docCurp: d.docCurp, docIne: d.docIne
            });
            return; // Ya hicimos el trabajo, terminamos.
        }

        // --- CASO B: CERRAR (Botón X) ---
        if (event.target.closest('#modalInscripcion .modal-close')) {
            cerrarModalInscripcion();
            return;
        }
        if (event.target.closest('.modal-close')) {
            closeStudentDetails();
            closeDocViewer();
            return;
        }

        // --- CASO C: CERRAR (Clic afuera / Fondo oscuro) ---
        if (event.target.classList.contains('modal-overlay')) {
            if (event.target.id === 'modalInscripcion') cerrarModalInscripcion();
            else { closeStudentDetails(); closeDocViewer(); }
            return;
        }
    });

</script>

@endsection