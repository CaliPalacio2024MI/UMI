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
                <span style="position: relative; display: block; width: 100%;">
                    <img src="{{ asset('images/icons/magnifying-glass-svgrepo-com.svg') }}"
                         alt="Buscar"
                         style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; opacity: 0.65; pointer-events: none;">
                <input type="text" name="search" class="umi-search-input" 
                       placeholder="Buscar por..." 
                       value="{{ request('search') }}"
                       style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #ccc; border-radius: 999px;">
                </span>
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
                        <th style="padding: 12px; text-align: center; width: 13%;">Matrícula</th>
                        <th style="padding: 12px; text-align: center; width: 10%;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="data-table-body" id="matriculas-table-body">
                    @include('layouts.ControlEsc.Matriculas.partials.table_rows', ['dataList' => $dataList])
                </tbody>
            </table>
        </div>
        
        <div id="matriculas-pagination" style="margin-top: 20px;">
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

    // --- BÚSQUEDA/PAGINACIÓN AJAX (sin recargar toda la página) ---
    async function refreshMatriculasTable(url) {
        const tbody = document.getElementById('matriculas-table-body');
        const pagination = document.getElementById('matriculas-pagination');
        if (!tbody || !pagination) return;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            if (!response.ok) {
                throw new Error('No se pudo actualizar la tabla.');
            }

            const data = await response.json();
            tbody.innerHTML = data.tbody ?? '';
            pagination.innerHTML = data.pagination ?? '';
            window.history.replaceState({}, '', url);
        } catch (error) {
            console.error(error);
        }
    }

    function initMatriculasAjaxSearch() {
        const input = document.querySelector('input[name="search"]');
        const form = input ? input.closest('form') : null;
        const pagination = document.getElementById('matriculas-pagination');
        if (!input || !pagination) return;

        let searchTimeout = null;
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const url = new URL(window.location.href);
                const value = (input.value || '').trim();
                if (value.length > 0) {
                    url.searchParams.set('search', value);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('page');
                refreshMatriculasTable(url.toString());
            }, 280);
        });

        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const url = new URL(window.location.href);
                const value = (input.value || '').trim();
                if (value.length > 0) {
                    url.searchParams.set('search', value);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('page');
                refreshMatriculasTable(url.toString());
            });
        }

        document.addEventListener('click', function(event) {
            const link = event.target.closest('#matriculas-pagination a');
            if (!link) return;
            event.preventDefault();
            refreshMatriculasTable(link.href);
        });
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
        initMatriculasAjaxSearch();
    });

    // Si usas Livewire y el mensaje de éxito se recarga por AJAX, reactívalo:
    if (window.Livewire) {
        window.Livewire.hook('message.processed', (message, component) => {
            initSuccessAlerts();
        });
    }

</script>
@endsection