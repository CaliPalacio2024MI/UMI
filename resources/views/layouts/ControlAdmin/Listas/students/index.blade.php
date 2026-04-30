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
        <form action="{{ request()->url() }}" method="GET" class="umi-toolbar-search-form" id="umi-search-form" style="display: flex; align-items: center; gap: 12px; flex-grow: 1; max-width: 580px;">
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
                <select name="filter_status" id="filter_status" class="umi-filter-select" style="flex: 1; padding: 6px 12px 6px 6px; border: none; background: transparent; color: #555; font-size: 0.9rem; cursor: pointer; outline: none; appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%2712%27 viewBox=%270 0 12 12%27%3E%3Cpath fill=%27%23555%27 d=%27M6 8L1 3h10z%27/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center;">
                    <option value="" {{ (request('filter_status') ?? '') === '' ? 'selected' : '' }}>Estatus:</option>
                    <option value="activos" {{ request('filter_status') === 'activos' ? 'selected' : '' }}>Alumno</option>
                    <option value="aspirantes" {{ request('filter_status') === 'aspirantes' ? 'selected' : '' }}>Aspirante</option>
                    <option value="inactivos" {{ request('filter_status') === 'inactivos' ? 'selected' : '' }}>Baja</option>
                </select>
            </div>
        </form>

        {{-- Exportar: los parámetros se copian desde #umi-search-form al enviar (la búsqueda en vivo no vuelve a renderizar el servidor). --}}
        <form id="umi-export-form" action="{{ request()->routeIs('control.*') ? route('control.students.export') : route('escolar.students.export') }}" method="GET" style="display: inline;">
            <button type="submit" class="umi-btn-secondary">
                <img src="{{ asset('images/icons/export-icon.svg') }}" alt="" width="20" height="20" style="vertical-align: middle;"> Exportar
            </button>
        </form>

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
                        <th style="min-width: 160px;">Clasificación</th>
                        <th style="text-align:center; min-width: 240px;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="data-table-body" id="students-table-body">
                    @include('layouts.ControlAdmin.Listas.students.partials.table_body', ['dataList' => $dataList])
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($dataList, 'hasPages') && $dataList->hasPages())
        <div id="students-pagination" class="d-flex justify-content-center py-3 px-2">
            {{ $dataList->withQueryString()->links() }}
        </div>
    @endif
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
            <h3>Ver Expediente</h3>
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

            <div class="student-details-tabs-wrap">
                <div class="student-details-tabs" role="tablist" aria-label="Secciones del expediente">
                    <button type="button" class="student-details-tab-btn is-active" role="tab" aria-selected="true" aria-controls="studentDetailsTabPersonal" id="studentDetailsTabBtnPersonal" data-student-tab="personal">
                        Información Personal
                    </button>
                    <button type="button" class="student-details-tab-btn" role="tab" aria-selected="false" aria-controls="studentDetailsTabAcademico" id="studentDetailsTabBtnAcademico" data-student-tab="academico">
                        Académico
                    </button>
                    <button type="button" class="student-details-tab-btn" role="tab" aria-selected="false" aria-controls="studentDetailsTabDocs" id="studentDetailsTabBtnDocs" data-student-tab="docs">
                        Documentación
                    </button>
                </div>

                <div id="studentDetailsTabPersonal" class="student-details-tab-panel is-active" role="tabpanel" aria-labelledby="studentDetailsTabBtnPersonal">
                    <div class="detail-item">
                        <label>Nombre:</label> <span id="modalNombre" style="font-weight: normal; color: #000;">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Ap. Paterno:</label> <span id="modalApellidoPaterno" style="font-weight: normal; color: #000;">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Ap. Materno:</label> <span id="modalApellidoMaterno" style="font-weight: normal; color: #000;">-</span>
                    </div>
                    <div class="detail-item">
                        <label>CURP:</label> <span id="modalCurp" style="font-weight: normal; color: #000;">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Teléfono:</label> <span id="modalPhone">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Correo:</label> <span id="modalEmail">-</span>
                    </div>
                </div>

                <div id="studentDetailsTabAcademico" class="student-details-tab-panel" role="tabpanel" aria-labelledby="studentDetailsTabBtnAcademico">
                    <div class="detail-item">
                        <label>Carrera:</label> <span id="modalCareer">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Semestre:</label> <span id="modalSemester">-</span>
                    </div>
                </div>

                <div id="studentDetailsTabDocs" class="student-details-tab-panel" role="tabpanel" aria-labelledby="studentDetailsTabBtnDocs">
                    <div class="docs-list">
                        <button id="btnDocActa" class="doc-btn hidden">Acta de Nacimiento</button>
                        <button id="btnDocCert" class="doc-btn hidden">Certificado Prepa</button>
                        <button id="btnDocCurp" class="doc-btn hidden">CURP</button>
                        <button id="btnDocIne" class="doc-btn hidden">INE</button>
                        <button id="btnDocFicha" class="doc-btn hidden">Ficha / comprobante de pago</button>

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
{{-- MODAL 2: Editar Aspirante (misma vista que Ver expediente) --}}
<div id="leadEditModal" class="modal-overlay" style="display: none; z-index: 9998;">
    <div class="modal-container expediente-modal">
        <div class="modal-header">
            <h3>Editar Expediente</h3>
            <button type="button" class="modal-close" onclick="closeLeadEditModal()">&times;</button>
        </div>
        <div class="modal-body-scroll">
            <form id="leadEditForm" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 0;">
                <input type="hidden" name="lead_edit_docs_interacted" id="leadEditDocsInteracted" value="0">
                <div class="student-summary" style="margin-bottom: 15px;">
                    <div class="avatar-placeholder"><i class="fa-solid fa-user"></i></div>
                    <div class="student-info-header">
                        <h2 id="leadEditModalName" style="margin:0 0 6px 0; font-size: 1.25rem;">-</h2>
                        <span id="leadEditModalStatusBadge" class="badge-status badge-orange">Aspirante</span>
                    </div>
                </div>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                <div class="details-grid lead-edit-details-grid">
                    <div class="details-column lead-edit-tabs-wrap">
                        <div class="lead-edit-tabs" role="tablist" aria-label="Secciones del expediente">
                            <button type="button" class="lead-edit-tab-btn is-active" role="tab" aria-selected="true" aria-controls="leadEditTabPersonal" id="leadEditTabBtnPersonal" data-lead-tab="personal">Personal</button>
                            <button type="button" class="lead-edit-tab-btn" role="tab" aria-selected="false" aria-controls="leadEditTabAcademico" id="leadEditTabBtnAcademico" data-lead-tab="academico">Académico</button>
                            <button type="button" class="lead-edit-tab-btn" role="tab" aria-selected="false" aria-controls="leadEditTabDocs" id="leadEditTabBtnDocs" data-lead-tab="docs">Documentación</button>
                        </div>
                        <div id="leadEditTabPersonal" class="lead-edit-tab-panel is-active" role="tabpanel" aria-labelledby="leadEditTabBtnPersonal">
                            <div class="detail-item"><label>Nombre:</label><input type="text" name="alumno_nombre" id="leadAlumnoNombre" required style="flex:1; padding:6px 10px; border:none; border-radius:6px; max-width:200px;"></div>
                            <div class="detail-item"><label>Ap. Paterno:</label><input type="text" name="alumno_paterno" id="leadAlumnoPaterno" required style="flex:1; padding:6px 10px; border:none; border-radius:6px; max-width:200px;"></div>
                            <div class="detail-item"><label>Ap. Materno:</label><input type="text" name="alumno_materno" id="leadAlumnoMaterno" required style="flex:1; padding:6px 10px; border:none; border-radius:6px; max-width:200px;"></div>
                            <div class="detail-item"><label>CURP:</label><input type="text" name="alumno_curp" id="leadAlumnoCurp" maxlength="18" style="flex:1; padding:6px 10px; border:none; border-radius:6px; max-width:200px;"></div>
                            <div class="detail-item"><label>Teléfono:</label><input type="text" name="telefono1" id="leadTelefono1" required maxlength="20" style="flex:1; padding:6px 10px; border:none; border-radius:6px; max-width:200px;"></div>
                            <div class="detail-item"><label>Correro:</label><input type="email" name="alumno_email" id="leadAlumnoEmail" autocomplete="off" placeholder="Correo del alumno (inscripción o edición aquí)" title="Se guarda en la cuenta del estudiante al pulsar + Guardar" style="flex:1; padding:6px 10px; border:none; border-radius:6px; max-width:260px;"></div>
                        </div>
                        <div id="leadEditTabAcademico" class="lead-edit-tab-panel" role="tabpanel" aria-labelledby="leadEditTabBtnAcademico">
                            <div class="detail-item"><label>Carrera:</label>
                                <select name="carrera_id" id="leadCarreraId" style="flex:1; padding:6px 10px; border:1px solid #ddd; border-radius:6px; max-width:220px;">
                                    <option value="">Sin asignar</option>
                                    @foreach(\App\Models\Users\Career::orderBy('name')->get() as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="detail-item"><label>Semestre:</label>
                                <input type="number" name="semestre" id="leadSemestre" min="1" max="12" value="1" style="flex:1; padding:6px 10px; border:none; border-radius:6px; max-width:100px;">
                            </div>
                        </div>
                        <div id="leadEditTabDocs" class="lead-edit-tab-panel" role="tabpanel" aria-labelledby="leadEditTabBtnDocs">
                            <div id="leadDocsContainer" class="lead-docs-list">
                                <div class="lead-doc-item" data-doc-field="doc_acta_nacimiento">
                                    <label>Acta Nacimiento</label>
                                    <div class="lead-doc-links" id="leadDocActaLink"></div>
                                    <label class="lead-aceptar-doc-wrap">
                                        <input type="hidden" name="aceptar_doc_acta_nacimiento" value="0">
                                        <input type="checkbox" name="aceptar_doc_acta_nacimiento" value="1" class="lead-aceptar-doc-cb">
                                        <span>Aceptar Documento</span>
                                    </label>
                                    <input type="file" name="doc_acta_nacimiento" accept=".pdf,.jpg,.jpeg,.png" class="lead-doc-input">
                                </div>
                                <div class="lead-doc-item" data-doc-field="doc_certificado_prepa">
                                    <label>Certificado Prepa</label>
                                    <div class="lead-doc-links" id="leadDocCertLink"></div>
                                    <label class="lead-aceptar-doc-wrap">
                                        <input type="hidden" name="aceptar_doc_certificado_prepa" value="0">
                                        <input type="checkbox" name="aceptar_doc_certificado_prepa" value="1" class="lead-aceptar-doc-cb">
                                        <span>Aceptar Documento</span>
                                    </label>
                                    <input type="file" name="doc_certificado_prepa" accept=".pdf,.jpg,.jpeg,.png" class="lead-doc-input">
                                </div>
                                <div class="lead-doc-item" data-doc-field="doc_curp">
                                    <label>CURP</label>
                                    <div class="lead-doc-links" id="leadDocCurpLink"></div>
                                    <label class="lead-aceptar-doc-wrap">
                                        <input type="hidden" name="aceptar_doc_curp" value="0">
                                        <input type="checkbox" name="aceptar_doc_curp" value="1" class="lead-aceptar-doc-cb">
                                        <span>Aceptar Documento</span>
                                    </label>
                                    <input type="file" name="doc_curp" accept=".pdf,.jpg,.jpeg,.png" class="lead-doc-input">
                                </div>
                                <div class="lead-doc-item" data-doc-field="doc_ine">
                                    <label>INE</label>
                                    <div class="lead-doc-links" id="leadDocIneLink"></div>
                                    <label class="lead-aceptar-doc-wrap">
                                        <input type="hidden" name="aceptar_doc_ine" value="0">
                                        <input type="checkbox" name="aceptar_doc_ine" value="1" class="lead-aceptar-doc-cb">
                                        <span>Aceptar Documento</span>
                                    </label>
                                    <input type="file" name="doc_ine" accept=".pdf,.jpg,.jpeg,.png" class="lead-doc-input">
                                </div>
                                <div class="lead-doc-item" data-doc-field="doc_ficha_pago">
                                    <label>Ficha de pago / comprobante (PDF)</label>
                                    <div class="lead-doc-links" id="leadDocFichaLink"></div>
                                    <label class="lead-aceptar-doc-wrap">
                                        <input type="hidden" name="aceptar_doc_ficha_pago" value="0">
                                        <input type="checkbox" name="aceptar_doc_ficha_pago" value="1" class="lead-aceptar-doc-cb">
                                        <span>Aceptar Documento</span>
                                    </label>
                                    <input type="file" name="doc_ficha_pago" accept=".pdf" class="lead-doc-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lead-edit-form-actions">
                    <button type="submit" class="btn btn--primary">+ Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

<div id="acceptAspiranteModal" class="modal-overlay" style="display: none; z-index: 10001;">
    <div class="modal-container accept-aspirante-modal">
        <div class="modal-header accept-aspirante-modal__header">
            <button type="button" class="modal-close" onclick="closeAcceptAspiranteModal()">&times;</button>
        </div>
        <div class="modal-body-scroll accept-aspirante-modal__body">
            <div class="accept-aspirante-modal__icon" aria-hidden="true">
                <span aria-hidden="true">✓</span>
            </div>
            <p id="acceptAspiranteText" class="accept-aspirante-modal__text">
                ¿Deseas aceptar este aspirante para pasarlo de aspirante como alumno?
            </p>
            <div class="accept-aspirante-modal__actions">
                <button type="button" id="btnConfirmAcceptAspirante" class="btn btn--primary">Aceptar</button>
            </div>
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
    /* Aceptar aspirante: colores por estado de documentación (solo esta lista) */
    #umi-app-view button.accept-aspirante-btn.add-time-slot-btn {
        box-shadow: none;
    }
    /* Separar ligeramente la palomita de la línea izquierda de la celda */
    #umi-app-view .umi-actions-icons button.accept-aspirante-btn {
        margin-left: 10px;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--empty {
        background-color: #aeb4bd !important;
        color: #fff !important;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--empty svg,
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--empty .add-time-slot-btn__icon {
        stroke: #fff !important;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--empty:hover:not(:active) {
        background-color: #aeb4bd !important;
    }
    /* Toda la documentación y pago listos: puede aceptarse */
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--ready:not(:disabled) {
        background-color: #2e7d32 !important;
        color: #fff !important;
        cursor: pointer;
        pointer-events: auto;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--ready:not(:disabled) svg,
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--ready:not(:disabled) .add-time-slot-btn__icon {
        stroke: #fff !important;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--ready:not(:disabled):hover:not(:active) {
        background-color: #256628 !important;
    }
    #umi-app-view button.accept-aspirante-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed !important;
        pointer-events: none;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno:disabled {
        opacity: 1;
        cursor: default !important;
    }
    /* Ya aceptado como alumno: azul marino fijo (no cambia en hover) */
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno {
        background-color: #001f3f !important;
        color: #fff !important;
        cursor: default;
        pointer-events: none;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno svg,
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno .add-time-slot-btn__icon {
        stroke: #fff !important;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno:hover,
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno:active {
        background-color: #001f3f !important;
        color: #fff !important;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno:hover svg,
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno:active svg,
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno:hover .add-time-slot-btn__icon,
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--alumno:active .add-time-slot-btn__icon {
        stroke: #fff !important;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--rejected {
        background-color: #f0d56a !important;
        color: #4a3b08 !important;
    }
    #umi-app-view button.accept-aspirante-btn.accept-aspirante-btn--rejected:hover:not(:active) {
        background-color: #e8c85a !important;
    }
    #umi-app-view button.accept-aspirante-btn.add-time-slot-btn:active {
        background-color: #001f3f !important;
        color: #fff !important;
    }
    #umi-app-view button.accept-aspirante-btn.add-time-slot-btn:active svg,
    #umi-app-view button.accept-aspirante-btn.add-time-slot-btn:active .add-time-slot-btn__icon {
        stroke: #fff !important;
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
        flex: 1;
        text-align: center;
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

    /* Modal confirmar aspirante (estilo limpio tipo confirmación) */
    #acceptAspiranteModal .accept-aspirante-modal {
        max-width: 460px;
        max-height: 300px;
    }
    #acceptAspiranteModal .accept-aspirante-modal__header {
        background: #ffffff;
        justify-content: flex-end;
    }
    #acceptAspiranteModal .accept-aspirante-modal__body {
        padding: 14px 18px 12px;
        text-align: center;
    }
    #acceptAspiranteModal .accept-aspirante-modal__icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        margin: 0 auto 10px;
        border: 2px solid #cfe6c8;
        color: #8ec58d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    #acceptAspiranteModal .accept-aspirante-modal__text {
        margin: 0;
        color: #223F70;
        font-weight: 600;
        line-height: 1.4;
    }
    #acceptAspiranteModal .accept-aspirante-modal__actions {
        margin-top: 12px;
        display: flex;
        justify-content: center;
        gap: 10px;
    }


    /* Botones tipo "confirmación" ovalados azul marino */
    #acceptAspiranteModal .btn {
        border: none;
        border-radius: 50px;
        padding: 10px 22px;
        font-weight: 600;
        cursor: pointer;
        background: #223F70;
        color: #fff;
        transition: background-color 0.2s ease, transform 0.1s ease;
    }
    #acceptAspiranteModal .btn:hover {
        background: #1a3055;
    }
    
    .btn-secondary-modal { background: #e0e0e0; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; color: #333; font-weight: 600; }
    .btn-secondary-modal:hover { background: #d0d0d0; }

    .doc-btn { display: flex; align-items: center; width: 100%; padding: 12px 15px; margin-bottom: 10px; background: #fbfbfb; color: #2c3e50; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.2s; }
    .doc-btn:hover { background: #e3f2fd; border-color: #3498db; color: #223F70; transform: translateX(5px); }
    .doc-btn i { margin-right: 12px; font-size: 1.2rem; color: #e74c3c; }
    #btnDocCert { justify-content: flex-start; text-align: left; }
    .hidden { display: none !important; }
    .no-docs { text-align: center; color: #aaa; padding: 15px; border: none; }

    /* Badges Status */
    .badge-status { border: 1px solid; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 0.8rem; }
    .badge-green { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
    .badge-orange { background: #fff3e0; color: #ef6c00; border-color: #ffe0b2; }
    .badge-gray { background: #223F70; color: #fff; border-color: #223F70; }

    /* Badge "Alumno" en modal de edición: azul marino + letras blancas */
    #leadEditModal .badge-green {
        background: #223F70 !important;
        color: #fff !important;
        border-color: #223F70 !important;
    }

    /* Badge "Aspirante" en modal editar expediente: azul marino + texto blanco */
    #leadEditModal .badge-orange {
        background: #223F70 !important;
        color: #fff !important;
        border-color: #1a3258 !important;
    }

    /* Badge "Alumno" en modal de ver expediente: azul marino + letras blancas */
    #studentDetailsModal .badge-green {
        background: #223F70 !important;
        color: #fff !important;
        border-color: #223F70 !important;
    }

    /* Badge "Aspirante" en modal ver expediente: azul marino + texto blanco */
    #studentDetailsModal .badge-orange {
        background: #223F70 !important;
        color: #fff !important;
        border-color: #1a3258 !important;
    }

    .student-summary { display: flex; align-items: center; gap: 15px; }
    .avatar-placeholder { width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 20px; color: white; }
    
    /* ALINEACIÓN HEADER */
    .student-info-header {
        display: flex; 
        align-items: center; 
        gap: 15px; 
    }
    .student-info-header .badge-status { margin-left: 20px; }
    .student-info-header h2 {
        margin: 0; font-size: 1.4rem; color: #223F70;
    }

    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
    #leadEditModal .lead-edit-details-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }
    .detail-item { margin-bottom: 12px; display: flex; align-items: center; gap: 12px; }
    .detail-item label { font-weight: 600; color: #BC8A55; width: 80px; flex-shrink: 0; }

    #leadEditModal .lead-doc-item {
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e0e0e0;
    }
    #leadEditModal .lead-aceptar-doc-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0;
        font-size: 0.85rem;
        color: #444;
        cursor: pointer;
        font-weight: 500;
        line-height: 1.2;
    }
    /* Solo el check en verde (el texto "Aceptar documento" queda neutro) */
    #leadEditModal .lead-aceptar-doc-wrap input.lead-aceptar-doc-cb {
        margin: 0;
        flex-shrink: 0;
        width: 1.05em;
        height: 1.05em;
        accent-color: #2e7d32;
        cursor: pointer;
    }
    #leadEditModal .lead-edit-form-actions {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #fff;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Botones modal editar aspirante: azul marino, forma ovalada */
    #leadEditModal .btn,
    #leadEditModal button.btn--primary {
        background: #223F70;
        color: #fff;
        border: 1px solid #fff;
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    #leadEditModal .btn:hover,
    #leadEditModal button.btn--primary:hover {
        background: #1a3258;
    }

    #leadEditModal .lead-doc-item > label:first-child {
        display: block;
        font-weight: 600;
        color: #BC8A55;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }
    #leadEditModal .lead-doc-links {
        margin-bottom: 6px;
    }
    #leadEditModal .lead-doc-links .btn-ver-doc {
        color: #2980b9;
        text-decoration: none;
        font-size: 0.85rem;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        font: inherit;
    }
    #leadEditModal .lead-doc-links .btn-ver-doc:hover {
        text-decoration: underline;
    }
    #leadEditModal .lead-doc-input {
        font-size: 0.85rem;
        padding: 4px 0;
    }

    /* Pestañas modal editar aspirante */
    #leadEditModal .lead-edit-tabs-wrap {
        display: flex;
        flex-direction: column;
        gap: 0;
        min-height: 0;
    }
    #leadEditModal .lead-edit-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 14px;
        border-bottom: 2px solid #e8e0d8;
        flex-shrink: 0;
    }
    #leadEditModal .lead-edit-tab-btn {
        flex: 1;
        padding: 10px 10px;
        border: none;
        background: transparent;
        color: #888;
        font-weight: 600;
        font-size: 0.82rem;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: color 0.2s, border-color 0.2s;
    }
    #leadEditModal .lead-edit-tab-btn:hover {
        color: #BC8A55;
    }
    #leadEditModal .lead-edit-tab-btn.is-active {
        color: #223F70;
        border-bottom-color: #BC8A55;
    }
    #leadEditModal .lead-edit-tab-panel {
        display: none;
        padding-top: 4px;
    }
    #leadEditModal .lead-edit-tab-panel.is-active {
        display: block;
    }

    /* Pestañas modal detalles alumno */
    #studentDetailsModal .student-details-tabs-wrap {
        display: flex;
        flex-direction: column;
        gap: 0;
        min-height: 0;
    }
    #studentDetailsModal .student-details-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 14px;
        border-bottom: 2px solid #e8e0d8;
        flex-shrink: 0;
    }
    #studentDetailsModal .student-details-tab-btn {
        flex: 1;
        padding: 10px 10px;
        border: none;
        background: transparent;
        color: #888;
        font-weight: 600;
        font-size: 0.82rem;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: color 0.2s, border-color 0.2s;
        text-align: center;
    }
    #studentDetailsModal .student-details-tab-btn:hover {
        color: #BC8A55;
    }
    #studentDetailsModal .student-details-tab-btn.is-active {
        color: #223F70;
        border-bottom-color: #BC8A55;
    }
    #studentDetailsModal .student-details-tab-panel {
        display: none;
        padding-top: 4px;
    }
    #studentDetailsModal .student-details-tab-panel.is-active {
        display: block;
    }
    
</style>
{{-- SCRIPTS --}}
<script>
    const UMI_STUDENTS_LEADS_BASE = @json(
        str_contains(request()->path(), 'lista-estudiantes')
            ? url('/control-administrativo/lista-estudiantes/leads')
            : url('/control-escolar/lista-alumnos/leads')
    );
    // =============================================================
    // 1. LÓGICA VISUAL (Funciones que muestran/ocultan)
    // =============================================================

    /** URLs de documentos desde data-doc-* (getAttribute + respaldo dataset; evita casos donde dataset queda vacío). */
    function umiExpedienteDocsFromBtn(btn) {
        function pick(kebabSuffix) {
            var v = btn.getAttribute('data-doc-' + kebabSuffix);
            if (v !== null && String(v).trim() !== '') return String(v).trim();
            var camel = { acta: 'docActa', cert: 'docCert', curp: 'docCurp', ine: 'docIne', ficha: 'docFicha', xml: 'docXml' }[kebabSuffix];
            if (camel && btn.dataset[camel]) return String(btn.dataset[camel]).trim();
            return '';
        }
        return {
            docActa: pick('acta'),
            docCert: pick('cert'),
            docCurp: pick('curp'),
            docIne: pick('ine'),
            docFicha: pick('ficha'),
            docXml: pick('xml')
        };
    }
    
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
        document.getElementById('modalNombre').innerText = data.alumnoNombre || '-';
        document.getElementById('modalApellidoPaterno').innerText = data.alumnoPaterno || '-';
        document.getElementById('modalApellidoMaterno').innerText = data.alumnoMaterno || '-';
        document.getElementById('modalCurp').innerText = data.alumnoCurp || '-';
        document.getElementById('modalEmail').innerText = data.email;
        document.getElementById('modalPhone').innerText = data.phone;
        document.getElementById('modalCareer').innerText = data.career;
        var semVal = data.semester;
        if (semVal === undefined || semVal === null || String(semVal).trim() === '' || semVal === '—') {
            semVal = '1';
        }
        document.getElementById('modalSemester').innerText = String(semVal);
        
        // 2. Configurar Badge (Etiqueta de color)
        const badge = document.getElementById('modalStatusBadge');
        var uiStatus = data.status || 'Aspirante';
        // En tu sistema, a veces el backend manda 'Pendiente' para alumnos aún no inscritos.
        // Para el modal, lo mostramos como 'Aspirante'.
        if (uiStatus === 'Pendiente') {
            uiStatus = 'Aspirante';
        }
        badge.innerText = uiStatus;
        badge.className = 'badge-status'; 
        badge.classList.remove('badge-green', 'badge-orange', 'badge-gray');
        
        if(uiStatus === 'Alumno Activo' || uiStatus === 'Alumno') badge.classList.add('badge-green');
        else if(uiStatus === 'Aspirante') badge.classList.add('badge-orange');
        else badge.classList.add('badge-gray');

        // 3. Configurar Botones de Documentos
        let docsCount = 0;
        const configureBtn = (btnId, url, title) => {
            const modal = document.getElementById('studentDetailsModal');
            const btn = modal ? modal.querySelector('#' + btnId) : document.getElementById(btnId);
            if (!btn) return;
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
        configureBtn('btnDocFicha', data.docFicha, 'Ficha de pago / comprobante');

        document.getElementById('noDocsMsg').style.display = (docsCount === 0) ? 'block' : 'none';
        resetStudentDetailsTabsToPersonal();
        document.getElementById('studentDetailsModal').style.display = 'flex';
    }

    function closeStudentDetails() {
        document.getElementById('studentDetailsModal').style.display = 'none';
    }

    function resetStudentDetailsTabsToPersonal() {
        var modal = document.getElementById('studentDetailsModal');
        if (!modal) return;
        modal.querySelectorAll('.student-details-tab-btn').forEach(function(b) {
            var on = b.getAttribute('data-student-tab') === 'personal';
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        modal.querySelectorAll('.student-details-tab-panel').forEach(function(p) {
            p.classList.toggle('is-active', p.id === 'studentDetailsTabPersonal');
        });
    }

    (function initStudentDetailsTabs() {
        var modal = document.getElementById('studentDetailsModal');
        if (!modal) return;
        modal.addEventListener('click', function(e) {
            var btn = e.target.closest('.student-details-tab-btn');
            if (!btn || !modal.contains(btn)) return;
            e.preventDefault();
            var tab = btn.getAttribute('data-student-tab');
            modal.querySelectorAll('.student-details-tab-btn').forEach(function(b) {
                var on = b.getAttribute('data-student-tab') === tab;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            modal.querySelectorAll('.student-details-tab-panel').forEach(function(p) {
                var show = (tab === 'personal' && p.id === 'studentDetailsTabPersonal')
                    || (tab === 'academico' && p.id === 'studentDetailsTabAcademico')
                    || (tab === 'docs' && p.id === 'studentDetailsTabDocs');
                p.classList.toggle('is-active', show);
            });
        });
    })();

    function resetLeadEditTabsToPersonal() {
        var modal = document.getElementById('leadEditModal');
        if (!modal) return;
        modal.querySelectorAll('.lead-edit-tab-btn').forEach(function(b) {
            var on = b.getAttribute('data-lead-tab') === 'personal';
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        modal.querySelectorAll('.lead-edit-tab-panel').forEach(function(p) {
            p.classList.toggle('is-active', p.id === 'leadEditTabPersonal');
        });
    }

    function openLeadEditModal(data) {
        resetLeadEditTabsToPersonal();
        var fullName = [data.alumnoNombre, data.alumnoPaterno, data.alumnoMaterno].filter(Boolean).join(' ') || '-';
        // Pintar badge según estatus (Alumno / Aspirante / etc.)
        var status = data.status || 'Aspirante';
        // Normalización de estatus para la UI:
        // En tu sistema, a veces aparece como 'Pendiente' pero para el modal debe verse como 'Aspirante'.
        if (status === 'Pendiente') {
            status = 'Aspirante';
        }
        var badge = document.getElementById('leadEditModalStatusBadge');
        if (badge) {
            badge.innerText = status;
            badge.className = 'badge-status';
            badge.classList.remove('badge-green', 'badge-orange', 'badge-gray');
            if (status === 'Alumno Activo' || status === 'Alumno') badge.classList.add('badge-green');
            else if (status === 'Aspirante') badge.classList.add('badge-orange');
            else badge.classList.add('badge-gray');
        }
        document.getElementById('leadEditModalName').innerText = fullName;
        document.getElementById('leadAlumnoNombre').value = data.alumnoNombre || '';
        document.getElementById('leadAlumnoPaterno').value = data.alumnoPaterno || '';
        document.getElementById('leadAlumnoMaterno').value = data.alumnoMaterno || '';
        document.getElementById('leadAlumnoCurp').value = data.alumnoCurp || '';
        document.getElementById('leadTelefono1').value = data.telefono1 || '';
        document.getElementById('leadAlumnoEmail').value = data.alumnoEmail || '';
        document.getElementById('leadCarreraId').value = data.carreraId || '';
        document.getElementById('leadSemestre').value = data.semestre || '1';
        document.getElementById('leadEditForm').dataset.leadId = data.leadId;
        var docsFlag = document.getElementById('leadEditDocsInteracted');
        if (docsFlag) docsFlag.value = '0';

        function setDocLink(id, url, title) {
            var el = document.getElementById(id);
            el.innerHTML = '';
            if (url && url.trim() !== '') {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-ver-doc';
                btn.textContent = 'Ver documento';
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openDocViewer(url, title);
                });
                el.appendChild(btn);
            }
        }
        setDocLink('leadDocActaLink', data.docActa, 'Acta de Nacimiento');
        setDocLink('leadDocCertLink', data.docCert, 'Certificado Preparatoria');
        setDocLink('leadDocCurpLink', data.docCurp, 'CURP');
        setDocLink('leadDocIneLink', data.docIne, 'INE');
        setDocLink('leadDocFichaLink', data.docFicha, 'Ficha de pago / comprobante');

        function leadEditSetAceptacionRow(field, docUrl, rechVal) {
            var item = document.querySelector('#leadEditModal .lead-doc-item[data-doc-field="' + field + '"]');
            if (!item) return;
            var wrap = item.querySelector('.lead-aceptar-doc-wrap');
            var cb = wrap && wrap.querySelector('input[type=checkbox]');
            var wasRej = rechVal === '1' || rechVal === 1 || rechVal === true;
            var hasDoc = docUrl && String(docUrl).trim() !== '';
            if (wrap) {
                wrap.style.display = 'flex';
            }
            if (cb) {
                // Marcado = aceptado: documento presente y no rechazado en BD.
                cb.checked = hasDoc && !wasRej;
            }
        }
        leadEditSetAceptacionRow('doc_acta_nacimiento', data.docActa, data.docRechActa);
        leadEditSetAceptacionRow('doc_certificado_prepa', data.docCert, data.docRechCert);
        leadEditSetAceptacionRow('doc_curp', data.docCurp, data.docRechCurp);
        leadEditSetAceptacionRow('doc_ine', data.docIne, data.docRechIne);
        leadEditSetAceptacionRow('doc_ficha_pago', data.docFicha, data.docRechFicha);

        document.querySelectorAll('#leadEditForm .lead-doc-input').forEach(function(inp) { inp.value = ''; });
        document.getElementById('leadEditModal').style.display = 'flex';
    }

    function closeLeadEditModal() {
        document.getElementById('leadEditModal').style.display = 'none';
    }

    (function initLeadEditModalTabs() {
        var modal = document.getElementById('leadEditModal');
        if (!modal) return;
        modal.addEventListener('click', function(e) {
            var btn = e.target.closest('.lead-edit-tab-btn');
            if (!btn || !modal.contains(btn)) return;
            e.preventDefault();
            var tab = btn.getAttribute('data-lead-tab');
            modal.querySelectorAll('.lead-edit-tab-btn').forEach(function(b) {
                var on = b.getAttribute('data-lead-tab') === tab;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            modal.querySelectorAll('.lead-edit-tab-panel').forEach(function(p) {
                var show = (tab === 'personal' && p.id === 'leadEditTabPersonal')
                    || (tab === 'academico' && p.id === 'leadEditTabAcademico')
                    || (tab === 'docs' && p.id === 'leadEditTabDocs');
                p.classList.toggle('is-active', show);
            });
        });
    })();

    (function initLeadCurpInputNormalize() {
        var el = document.getElementById('leadAlumnoCurp');
        if (!el) return;
        el.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/\s/g, '');
        });
    })();

    (function initLeadDocAceptacionFileCheck() {
        var modal = document.getElementById('leadEditModal');
        if (!modal) return;
        modal.addEventListener('change', function(e) {
            if (e.target.classList.contains('lead-doc-input') || e.target.classList.contains('lead-aceptar-doc-cb')) {
                var df = document.getElementById('leadEditDocsInteracted');
                if (df) df.value = '1';
            }
            if (!e.target.classList.contains('lead-doc-input')) return;
            if (e.target.files && e.target.files.length) {
                var item = e.target.closest('.lead-doc-item');
                var cb = item && item.querySelector('.lead-aceptar-doc-cb');
                if (cb) cb.checked = true;
            }
        });
    })();

    let acceptAspiranteUrl = '';

    function openAcceptAspiranteModal(data) {
        acceptAspiranteUrl = data.url || '';
        const text = document.getElementById('acceptAspiranteText');
        const nombre = (data.name || '').trim();
        text.textContent = nombre
            ? `¿Deseas aceptar a "${nombre}" para pasarlo de aspirante como alumno?`
            : '¿Deseas aceptar este aspirante para pasarlo de aspirante como alumno?';
        document.getElementById('acceptAspiranteModal').style.display = 'flex';
    }

    function closeAcceptAspiranteModal() {
        acceptAspiranteUrl = '';
        document.getElementById('acceptAspiranteModal').style.display = 'none';
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
        
        // --- CASO A: ABRIR EL EXPEDIENTE (VER) ---
        const openBtn = event.target.closest('[data-action="open-expediente"]');
        if (openBtn) {
            const d = openBtn.dataset;
            var docs = umiExpedienteDocsFromBtn(openBtn);
            openStudentDetails({
                name: d.name, email: d.email, phone: d.phone,
                career: d.career, semester: d.semester, status: d.status,
                alumnoNombre: d.alumnoNombre,
                alumnoPaterno: d.alumnoPaterno,
                alumnoMaterno: d.alumnoMaterno,
                alumnoCurp: d.alumnoCurp,
                docActa: docs.docActa, docCert: docs.docCert,
                docCurp: docs.docCurp, docIne: docs.docIne,
                docFicha: docs.docFicha || ''
            });
            return;
        }

        // --- CASO A2: ABRIR EXPEDIENTE EDIT (ASPIRANTE) ---
        const editLeadBtn = event.target.closest('[data-action="open-expediente-edit"]');
        if (editLeadBtn) {
            const d = editLeadBtn.dataset;
            var docsEdit = umiExpedienteDocsFromBtn(editLeadBtn);
            openLeadEditModal({
                leadId: d.leadId,
                status: d.status,
                alumnoNombre: d.alumnoNombre,
                alumnoPaterno: d.alumnoPaterno,
                alumnoMaterno: d.alumnoMaterno,
                alumnoCurp: d.alumnoCurp,
                telefono1: d.telefono1,
                alumnoEmail: d.alumnoEmail || '',
                carreraId: d.carreraId,
                semestre: d.semestre || '1',
                docActa: docsEdit.docActa || '',
                docCert: docsEdit.docCert || '',
                docCurp: docsEdit.docCurp || '',
                docIne: docsEdit.docIne || '',
                docRechActa: d.docRechActa || '0',
                docRechCert: d.docRechCert || '0',
                docRechCurp: d.docRechCurp || '0',
                docRechIne: d.docRechIne || '0',
                docFicha: docsEdit.docFicha || '',
                docRechFicha: d.docRechFicha || '0'
            });
            return;
        }

        // --- CASO A3: ABRIR CONFIRMACIÓN ACEPTAR ASPIRANTE ---
        const acceptBtn = event.target.closest('[data-action="accept-aspirante"]');
        if (acceptBtn) {
            if (acceptBtn.disabled) {
                return;
            }
            const d = acceptBtn.dataset;
            if (!d.acceptUrl) {
                alert('Este registro no tiene un lead CRM vinculado para aceptar.');
                return;
            }
            openAcceptAspiranteModal({
                url: d.acceptUrl,
                name: d.leadName || ''
            });
            return;
        }

        // --- CASO B: CERRAR (Botón X) — solo el modal afectado (no cerrar expediente al cerrar el PDF) ---
        const modalCloseBtn = event.target.closest('.modal-close');
        if (modalCloseBtn) {
            if (modalCloseBtn.closest('#docViewerModal')) {
                closeDocViewer();
            } else if (modalCloseBtn.closest('#modalInscripcion')) {
                cerrarModalInscripcion();
            } else if (modalCloseBtn.closest('#leadEditModal')) {
                closeLeadEditModal();
            } else if (modalCloseBtn.closest('#acceptAspiranteModal')) {
                closeAcceptAspiranteModal();
            } else if (modalCloseBtn.closest('#studentDetailsModal')) {
                closeStudentDetails();
            }
            return;
        }

        // --- CASO C: CERRAR (Clic afuera / Fondo oscuro) ---
        if (event.target.classList.contains('modal-overlay')) {
            const oid = event.target.id;
            if (oid === 'modalInscripcion') cerrarModalInscripcion();
            else if (oid === 'leadEditModal') closeLeadEditModal();
            else if (oid === 'acceptAspiranteModal') closeAcceptAspiranteModal();
            else if (oid === 'docViewerModal') closeDocViewer();
            else if (oid === 'studentDetailsModal') closeStudentDetails();
            return;
        }
    });

    document.getElementById('btnConfirmAcceptAspirante')?.addEventListener('click', function() {
        if (!acceptAspiranteUrl) return;
        const btn = this;
        const original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Procesando...';

        fetch(acceptAspiranteUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(r) { return r.json().then(function(j){ return { ok: r.ok, data: j }; }); })
        .then(function(res) {
            if (!res.ok || !res.data?.success) {
                throw new Error(res.data?.message || 'No se pudo aceptar el aspirante.');
            }
            closeAcceptAspiranteModal();
            const form = document.getElementById('umi-search-form');
            if (form) {
                const params = new URLSearchParams(new FormData(form));
                const url = form.action + (params.toString() ? '?' + params.toString() : '');
                fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                    .then(function(r) { return r.text(); })
                    .then(function(html) {
                        const tbody = document.getElementById('students-table-body');
                        if (tbody) tbody.innerHTML = html;
                    });
            }
        })
        .catch(function(err) { alert(err.message || 'Error al aceptar aspirante'); })
        .finally(function() {
            btn.disabled = false;
            btn.textContent = original || 'Aceptar';
        });
    });

    // --- ENVÍO FORMULARIO EDITAR ASPIRANTE ---
    document.getElementById('leadEditForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const leadId = this.dataset.leadId;
        if (!leadId) {
            alert('Este alumno no tiene un lead CRM vinculado para editar desde este modal.');
            return;
        }
        const formData = new FormData(this);
        formData.delete('alumno_email');
        const submitBtn = this.querySelector('button[type="submit"]');
        const origText = submitBtn?.textContent;
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Guardando...'; }

        fetch(UMI_STUDENTS_LEADS_BASE + '/' + leadId + '/expediente', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(r) {
            return r.json().then(function(j) {
                if (!r.ok) {
                    var msg = j.message || (j.errors && Object.values(j.errors).flat().join(' ')) || (r.status === 403 ? 'No tienes permiso para editar.' : 'Error al guardar');
                    throw new Error(msg);
                }
                return j;
            });
        })
        .then(function() {
            const emailVal = (document.getElementById('leadAlumnoEmail') && document.getElementById('leadAlumnoEmail').value) ? document.getElementById('leadAlumnoEmail').value.trim() : '';
            const curpVal = (document.getElementById('leadAlumnoCurp') && document.getElementById('leadAlumnoCurp').value) ? document.getElementById('leadAlumnoCurp').value.trim() : '';
            if (!emailVal || !leadId) {
                return Promise.resolve();
            }
            return fetch(UMI_STUDENTS_LEADS_BASE + '/' + leadId + '/alumno-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ alumno_email: emailVal, alumno_curp: curpVal })
            }).then(function(sr) {
                return sr.json().then(function(j) {
                    if (!sr.ok) {
                        throw new Error(j.message || (j.errors && Object.values(j.errors).flat().join(' ')) || 'No se pudo guardar el correo del alumno');
                    }
                    return j;
                });
            });
        })
        .then(function() {
            closeLeadEditModal();
            var showSuccessModal = function() {
                var successModal = document.getElementById('careerSuccessModal');
                var successModalMessage = document.getElementById('careerSuccessModalMessage');
                if (successModal && successModalMessage) {
                    successModalMessage.textContent = 'Expediente actualizado correctamente.';
                    successModal.style.display = 'flex';
                }
            };
            var refreshStudentsTable = function() {
                var form = document.getElementById('umi-search-form');
                if (!form) {
                    showSuccessModal();
                    return;
                }
                var params = new URLSearchParams(new FormData(form));
                var url = form.action + (params.toString() ? '?' + params.toString() : '');
                fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                    .then(function(r) { return r.text(); })
                    .then(function(html) {
                        var tbody = document.getElementById('students-table-body');
                        if (tbody) tbody.innerHTML = html;
                    })
                    .finally(function() {
                        showSuccessModal();
                    });
            };
            refreshStudentsTable();
        })
        .catch(function(err) { alert(err.message); })
        .finally(function() {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = origText || '+ Guardar'; }
        });
    });

    // =============================================================
    // 3. BÚSQUEDA EN VIVO: actualizar solo la tabla por AJAX (sin recargar la página)
    // =============================================================
    (function() {
        const form = document.getElementById('umi-search-form');
        const input = document.getElementById('search');
        const filterSelect = form ? form.querySelector('select[name="filter_status"]') : null;
        const classificationSelect = form ? form.querySelector('select[name="filter_classification"]') : null;
        const tbody = document.getElementById('students-table-body');
        if (!form || !input || !tbody) return;

        let debounceTimer;
        function refreshTable() {
            const search = input.value.trim();
            const filterStatus = filterSelect ? filterSelect.value : '';
            const filterClassification = classificationSelect ? classificationSelect.value : '';
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (filterStatus) params.set('filter_status', filterStatus);
            if (filterClassification) params.set('filter_classification', filterClassification);
            const url = form.action + (params.toString() ? '?' + params.toString() : '');

            fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            })
            .then(function(r) { if (!r.ok) throw new Error('Error'); return r.text(); })
            .then(function(html) {
                tbody.innerHTML = html;
                var pag = document.getElementById('students-pagination');
                if (pag) pag.style.display = 'none';
                if (typeof history !== 'undefined' && history.replaceState) {
                    history.replaceState(null, '', url);
                }
            })
            .catch(function() {
                form.submit();
            });
        }

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(refreshTable, 400);
        });
        if (filterSelect) {
            filterSelect.addEventListener('change', refreshTable);
        }
        if (classificationSelect) {
            classificationSelect.addEventListener('change', refreshTable);
        }
    })();

    (function syncExportWithToolbar() {
        var exportForm = document.getElementById('umi-export-form');
        var searchForm = document.getElementById('umi-search-form');
        if (!exportForm || !searchForm) return;
        exportForm.addEventListener('submit', function () {
            exportForm.querySelectorAll('input[data-toolbar-sync]').forEach(function (el) { el.remove(); });
            // 1) Sincroniza valores visibles del toolbar (prioridad alta).
            ['search', 'filter_status', 'filter_classification'].forEach(function (name) {
                var el = searchForm.querySelector('[name="' + name + '"]');
                if (!el) return;
                var val = el.value;
                if (val === '' || val === null || val === undefined) return;
                var h = document.createElement('input');
                h.type = 'hidden';
                h.name = name;
                h.value = val;
                h.setAttribute('data-toolbar-sync', '1');
                exportForm.appendChild(h);
            });

            // 2) Copia cualquier otro query param activo de la URL (excepto paginación),
            // para exportar exactamente lo que el usuario tiene filtrado en pantalla.
            var params = new URLSearchParams(window.location.search || '');
            params.delete('page');
            params.forEach(function (value, key) {
                if (!key || value === '' || value == null) return;
                if (exportForm.querySelector('input[name="' + key + '"]')) return;
                var h = document.createElement('input');
                h.type = 'hidden';
                h.name = key;
                h.value = value;
                h.setAttribute('data-toolbar-sync', '1');
                exportForm.appendChild(h);
            });
        });
    })();

</script>

@endsection