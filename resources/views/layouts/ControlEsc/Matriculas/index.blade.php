@extends('layouts.app')
@section('content')


<div class="container">
<div id="umi-app-view">

    {{-- TÍTULO --}}
    <div class="content-header">
        <div class="content-title">
            <h3>Matrículas</h3>
        </div>
    </div>

    {{-- MENSAJES DE ÉXITO (Desaparece en 3 segundos) --}}
    @if(session('success'))
        <div id="success-alert" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb; text-align: center;">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TOOLBAR --}}
    <div class="umi-toolbar" style="display: flex; justify-content: space-between; margin-bottom: 20px; align-items: center;">
        <div class="umi-search-wrapper" style="flex: 1;">
            <form action="{{ request()->url() }}" method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="umi-search-input" 
                       placeholder="Buscar por Nombre o Correo..." 
                       value="{{ request('search') }}"
                       style="width: 100%; padding: 10px 14px; border: 1px solid #ccc; border-radius: 999px;">
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="umi-table-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="umi-table-scroll" style="overflow-x: auto; min-height: 300px;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed;">
                <thead style="background-color: #223F70; color: white;">
                    <tr>
                        <th style="padding: 12px; text-align: center; width: 21%;">Alumno</th>
                        <th style="padding: 12px; text-align: center; width: 16%;">Carrera</th>
                        <th style="padding: 12px; text-align: center; width: 12%;">Clasificación</th>
                        <th style="padding: 12px; text-align: center; width: 9%;">Status Pago</th>
                        <th style="padding: 12px; text-align: center; width: 19%;">Documentación</th>
                        <th style="padding: 12px; text-align: center; width: 13%;">Asignación Matrícula</th>
                        <th style="padding: 12px; text-align: center; width: 10%;">Acción</th>
                    </tr>
                </thead>
                <tbody class="data-table-body">
                    @forelse ($dataList as $student)
                        <tr style="border-bottom: 1px solid #eee;">
                            
                            {{-- 1. Datos del Aspirante --}}
                            <td style="padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                    <strong style="color: #333; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                        {{ $student->nombre }} {{ $student->apellido_paterno }} {{ $student->apellido_materno }}
                                    </strong>
                                    <small style="color: #777; margin-top: 4px;">
                                        <i class="fa-regular fa-envelope"></i> {{ $student->email }}
                                    </small>
                                </div>
                            </td>
                            
                            {{-- 2. Carrera --}}
                            <td style="padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; text-align: center;">
                                <span style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.35; max-height: 2.7em; font-size: 0.9rem;">
                                    {{ $student->academicProfile?->career?->name ?? 'Sin Carrera Asignada' }}
                                </span>
                            </td>

                            {{-- 3. Clasificación --}}
                            <td style="padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; text-align: center;">
                                <span style="display: block; line-height: 1.4; font-size: 0.9rem;">
                                    {{ $student->academicProfile?->career?->classification?->name ?? 'Sin clasificación' }}
                                </span>
                            </td>

                            {{-- 4. Validación de Pago --}}
                            <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
                                @php
                                    $pagoStatus = $student->billing_status ?? 'Pendiente'; 
                                    $colorPago = $pagoStatus === 'Pagado' ? '#27ae60' : '#e74c3c';
                                    $bgPago = $pagoStatus === 'Pagado' ? '#eafaf1' : '#fdedec';
                                @endphp
                                <span style="color: {{ $colorPago }}; background-color: {{ $bgPago }}; font-weight: bold; border: 1px solid {{ $colorPago }}; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block;">
                                    {{ $pagoStatus }}
                                </span>
                            </td>

                            {{-- 5. DOCUMENTACIÓN (CON VISOR MODAL Y VALIDACIÓN DE PAGO) --}}
                            <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
                                <div style="background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px dashed #ccc; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    
                                    {{-- ÍCONOS --}}
                                    <div style="display: flex; justify-content: center; gap: 10px; width: 100%;">
                                        <i class="fa-solid fa-file-certificate" style="font-size: 1.1rem; {{ $student->doc_certificado ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="Certificado"></i>
                                        <i class="fa-solid fa-id-card" style="font-size: 1.1rem; {{ $student->doc_acta ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="Acta"></i>
                                        <i class="fa-solid fa-passport" style="font-size: 1.1rem; {{ $student->doc_curp ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="CURP"></i>
                                    </div>
                                    <hr style="width: 80%; border: 0; border-top: 1px solid #eee; margin: 2px 0;">

                                    {{-- VALIDACIÓN PRINCIPAL: SOLO SI ESTÁ PAGADO PUEDE INTERACTUAR --}}
                                    @if($pagoStatus === 'Pagado')

                                        @if($student->academicProfile && $student->academicProfile->documentoSEP_path)
                                            
                                            {{-- A) SI YA EXISTE ARCHIVO --}}
                                            <div style="width: 100%;">
                                                
                                                {{-- BOTÓN QUE ABRE EL MODAL --}}
                                                <a href="javascript:void(0)" 
                                                   onclick="openDocViewer('{{ asset('storage/' . $student->academicProfile->documentoSEP_path) }}', '{{ $student->nombre }} {{ $student->apellido_paterno }}')"
                                                   class="umi-btn" 
                                                   style="background-color: #223F70; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; display: block; width: 100%; box-sizing: border-box; text-align: center; margin-bottom: 5px; font-weight: bold;">
                                                    <i class="fa-solid fa-eye"></i> VER DOCUMENTO
                                                </a>

                                                {{-- Formulario para cambiar --}}
                                                <form id="form-doc-{{ $student->id }}" action="{{ route('escolar.documentacion.upload', $student->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="file" 
                                                           id="file-upload-{{ $student->id }}" 
                                                           name="documento_pdf" 
                                                           class="pdf-uploader" 
                                                           data-form-id="form-doc-{{ $student->id }}"
                                                           accept="application/pdf" 
                                                           style="display: none;">
                                                    
                                                    <label for="file-upload-{{ $student->id }}" style="cursor: pointer; color: #777; font-size: 0.75rem; text-decoration: underline; display: block; margin-top: 5px;">
                                                        <i class="fa-solid fa-rotate"></i> Cambiar archivo
                                                    </label>
                                                </form>
                                            </div>

                                        @else

                                            {{-- B) SI NO HAY ARCHIVO (PERO ESTÁ PAGADO) --}}
                                            <form id="form-doc-{{ $student->id }}" action="{{ route('escolar.documentacion.upload', $student->id) }}" method="POST" enctype="multipart/form-data" style="width: 100%;">
                                                @csrf
                                                <input type="file" 
                                                       id="file-upload-{{ $student->id }}" 
                                                       name="documento_pdf" 
                                                       class="pdf-uploader" 
                                                       data-form-id="form-doc-{{ $student->id }}"
                                                       accept="application/pdf" 
                                                       style="display: none;">

                                                <label for="file-upload-{{ $student->id }}" 
                                                       style="cursor: pointer; background: #e0e0e0; color: #333; padding: 8px 10px; border-radius: 4px; font-size: 0.8rem; border: 1px solid #ccc; font-weight: 600; display: block; width: 100%; box-sizing: border-box; text-align: center;">
                                                    <i class="fa-solid fa-cloud-arrow-up"></i> SUBIR PDF
                                                </label>
                                                <small style="display: block; color: #999; font-size: 0.7rem; margin-top: 3px;">(Max 10MB)</small>
                                            </form>

                                        @endif

                                    @else
                                        {{-- C) SI NO HA PAGADO: BLOQUEADO --}}
                                        <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #eee; font-size: 0.75rem; color: #95a5a6; text-align: center;">
                                            <i class="fa-solid fa-lock" style="font-size: 1.2rem; margin-bottom: 5px; display: block;"></i> 
                                            Pago Requerido
                                        </div>
                                    @endif

                                </div>
                            </td>

                            {{-- 6. INPUT MATRÍCULA --}}
                            <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
                                <form id="form-matricula-{{ $student->id }}" 
                                      action="{{ $student->academicProfile?->matricula ? route('escolar.matriculas.update', $student->id) : route('escolar.matriculas.store', $student->id) }}" 
                                      method="POST">
                                    @csrf
                                    @if($student->academicProfile?->matricula)
                                        @method('PUT')
                                    @endif
                                    
                                    @if($pagoStatus === 'Pagado')
                                        <input type="text" name="matricula" 
                                               value="{{ $student->academicProfile?->matricula }}" 
                                               placeholder="Ej. 2025-001"
                                               style="padding: 8px; border: 1px solid #223F70; border-radius: 4px; width: 100%; box-sizing: border-box; text-align: center; font-weight: bold; color: #223F70;">
                                    @else
                                        <div style="background: #f8f9fa; padding: 8px; border-radius: 4px; border: 1px solid #eee; font-size: 0.8rem; color: #95a5a6;">
                                            <i class="fa-solid fa-lock"></i> Pago Pendiente
                                        </div>
                                    @endif
                                </form>
                            </td>

                            {{-- 7. ACCIÓN --}}
                            <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
                                <div style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
                                    <button type="button"
                                            class="umi-btn"
                                            onclick="openMatriculaDetail({{ $student->id }})"
                                            style="background: #5c6bc0; color: white; border: none; padding: 7px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px; width: 100%; justify-content: center;">
                                        <i class="fa-solid fa-eye"></i> Ver
                                    </button>
                                    
                                    @if($pagoStatus === 'Pagado')
                                        <button type="submit" form="form-matricula-{{ $student->id }}" class="umi-btn" style="background: #223F70; color: white; border: none; padding: 7px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; transition: background 0.3s; display: inline-flex; align-items: center; gap: 5px; width: 100%; justify-content: center;">
                                            <i class="fa-solid fa-save"></i> {{ $student->academicProfile?->matricula ? 'Editar' : 'Alta' }}
                                        </button>
                                    @else
                                        <button disabled style="opacity: 0.4; cursor: not-allowed; border: 1px solid #ccc; background: #eee; padding: 7px 10px; border-radius: 4px; color: #777; display: inline-flex; align-items: center; gap: 5px; width: 100%; justify-content: center;">
                                            <i class="fa-solid fa-ban"></i> Bloqueado
                                        </button>
                                    @endif

                                    @if($student->academicProfile?->matricula)
                                        <form id="form-delete-matricula-{{ $student->id }}"
                                              action="{{ route('escolar.matriculas.destroy', $student->id) }}"
                                              method="POST"
                                              style="width: 100%; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="confirmDeleteMatricula({{ $student->id }}, '{{ $student->nombre }} {{ $student->apellido_paterno }}')"
                                                    style="background: #c0392b; color: white; border: none; padding: 7px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px; width: 100%; justify-content: center;">
                                                <i class="fa-solid fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 50px; color: #666; background-color: #fafafa;">
                                <i class="fa-solid fa-users-slash" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
                                <p style="font-size: 1.1rem; margin: 0;">No se encontraron aspirantes que coincidan con los filtros.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px;">
            {{ $dataList->appends(request()->query())->links() }}
        </div>
    </div>
</div>
</div>

{{-- MODAL HTML --}}
<div id="docViewerModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="docViewerTitle" style="margin: 0; font-size: 1rem; font-weight: normal;">Visualizando Documento</h3>
            <button type="button" class="modal-close" onclick="closeDocViewer()">&times;</button>
        </div>
        <div class="modal-body" style="flex: 1; background: #525659; position: relative;">
            <iframe id="docViewerFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
        </div>
    </div>
</div>

{{-- MODAL DETALLE MATRÍCULA --}}
<div id="matriculaDetailModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 640px; min-height: auto;">
        <div class="modal-header">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Detalle de Matrícula</h3>
            <button type="button" class="modal-close" onclick="closeMatriculaDetail()">&times;</button>
        </div>
        <div class="modal-body" style="background: #fff; padding: 16px; color: #2c3e50;">
            <div id="matricula-detail-content" style="font-size: 0.9rem; line-height: 1.6;">
                Cargando...
            </div>
        </div>
    </div>
</div>

<script>
    // =============================================================
    // 1. LÓGICA VISUAL (Modales y Utilidades)
    // =============================================================

    // --- VISOR DE DOCUMENTOS ---
    function openDocViewer(url, title) {
        const modal = document.getElementById('docViewerModal');
        if (modal) {
            document.getElementById('docViewerFrame').src = url;
            document.getElementById('docViewerTitle').innerText = 'Documento: ' + title;
            modal.style.display = 'flex';
        }
    }

    function closeDocViewer() {
        const modal = document.getElementById('docViewerModal');
        if (modal) {
            modal.style.display = 'none';
            document.getElementById('docViewerFrame').src = ''; 
        }
    }

    // --- DETALLE DE MATRÍCULA ---
    async function openMatriculaDetail(studentId) {
        const modal = document.getElementById('matriculaDetailModal');
        const content = document.getElementById('matricula-detail-content');
        if (!modal || !content) return;

        content.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cargando detalle...';
        modal.style.display = 'flex';

        try {
            const response = await fetch(`/control-escolar/matriculas/${studentId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) {
                throw new Error('No se pudo obtener el detalle de matrícula.');
            }

            const data = await response.json();
            const siNo = (v) => v ? 'Sí' : 'No';
            const docSep = data.documento_sep_path
                ? `<a href="/storage/${data.documento_sep_path}" target="_blank" rel="noopener">Ver documento SEP</a>`
                : 'No cargado';

            content.innerHTML = `
                <p><strong>Alumno:</strong> ${data.nombre || '-'}</p>
                <p><strong>Correo:</strong> ${data.email || '-'}</p>
                <p><strong>Carrera:</strong> ${data.career || '-'}</p>
                <p><strong>Matrícula:</strong> ${data.matricula || '<span style="color:#999;">Sin asignar</span>'}</p>
                <p><strong>Status de pago:</strong> ${data.billing_status || '-'}</p>
                <hr style="border:0; border-top:1px solid #eee;">
                <p><strong>Acta de nacimiento:</strong> ${siNo(data.documentos?.doc_acta_nacimiento)}</p>
                <p><strong>Certificado prepa:</strong> ${siNo(data.documentos?.doc_certificado_prepa)}</p>
                <p><strong>CURP:</strong> ${siNo(data.documentos?.doc_curp)}</p>
                <p><strong>INE:</strong> ${siNo(data.documentos?.doc_ine)}</p>
                <p><strong>Documento SEP:</strong> ${docSep}</p>
            `;
        } catch (error) {
            content.innerHTML = `<span style="color:#c0392b;">${error.message}</span>`;
        }
    }

    function closeMatriculaDetail() {
        const modal = document.getElementById('matriculaDetailModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function confirmDeleteMatricula(studentId, studentName) {
        const ok = confirm(`¿Eliminar matrícula de ${studentName}? Esta acción quitará el número de matrícula asignado.`);
        if (!ok) return;

        const form = document.getElementById(`form-delete-matricula-${studentId}`);
        if (form) form.submit();
    }

    // --- ALERTAS DE ÉXITO (Auto-hide) ---
    function initSuccessAlerts() {
        const successAlert = document.getElementById('success-alert');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.opacity = '0';
                setTimeout(() => {
                    successAlert.style.display = 'none';
                }, 500);
            }, 3000);
        }
    }

    // =============================================================
    // 2. SUPER LISTENER DE "CLICS" (Delegación) 
    //    Maneja: Cierre de Modales y Apertura de Visor
    // =============================================================
    document.addEventListener('click', function(event) {
        
        // A. CERRAR MODAL (Botón X o Clic afuera)
        if (event.target.closest('.modal-close') || event.target.classList.contains('modal-overlay')) {
            closeDocViewer();
            closeMatriculaDetail();
            return;
        }

        // B. ABRIR VISOR (Si usaras data-attributes en lugar de onclick inline en el futuro)
        // Por ahora tu botón "VER DOCUMENTO" usa onclick inline, así que funciona bien.
    });

    // =============================================================
    // 3. SUPER LISTENER DE "CAMBIOS" (Delegación)
    //    Maneja: Subida automática de archivos (File Uploads)
    // =============================================================
    document.addEventListener('change', function(event) {
        
        // Verificamos si el elemento que cambió tiene la clase 'pdf-uploader'
        if (event.target && event.target.matches('.pdf-uploader')) {
            
            const input = event.target; // El input file
            
            // Validamos que haya archivo seleccionado
            if (input.files && input.files[0]) {
                
                // 1. Feedback Visual: Cambiar el texto del label a "Subiendo..."
                // Buscamos el label asociado usando el ID del input
                const label = document.querySelector(`label[for="${input.id}"]`);
                if (label) {
                    label.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subiendo...';
                    label.style.opacity = '0.7';
                    label.style.pointerEvents = 'none'; // Evitar doble clic
                }

                // 2. Enviar el Formulario
                const formId = input.getAttribute('data-form-id');
                const form = document.getElementById(formId);
                
                if (form) {
                    form.submit();
                } else {
                    console.error('No se encontró el formulario con ID:', formId);
                }
            }
        }
    });

    // =============================================================
    // 4. PUNTO DE ENTRADA (Solo para lo que no es evento delegado)
    // =============================================================
    document.addEventListener('DOMContentLoaded', () => {
        initSuccessAlerts();
    });

    // Si usas Livewire y el mensaje de éxito se recarga por AJAX, reactívalo:
    if (window.Livewire) {
        window.Livewire.hook('message.processed', (message, component) => {
            initSuccessAlerts();
        });
    }

</script>
@endsection