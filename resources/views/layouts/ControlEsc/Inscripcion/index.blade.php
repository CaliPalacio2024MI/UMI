{{-- Solo layout mínimo (sin panel) cuando viene embebido en modal del admin (?modal=1). La ruta /inscripcion/nuevo debe usar layouts.app para que el aspirante vea nombre + cerrar sesión. --}}
@extends(request('modal') ? 'layouts.iframe_content' : 'layouts.app')

@php
    // En "Nuevo Registro" (inscripción) el controlador puede pasar $alumno para prellenar datos,
    // pero sin que sea una reinscripción.
    $esReinscripcion = (bool) ($modoReinscripcion ?? isset($alumno));
@endphp

@section('title', $esReinscripcion ? 'Proceso de Reinscripción' : 'Inscripción')

@if(!request('modal'))
@vite(['resources/css/ControlEsc/base.css','resources/js/app.js'])
@endif

@section('content')

    {{-- 0. ÉXITO EN MODAL: cerrar modal y refrescar lista en el padre --}}
    @if(request('modal') && request('success') && session('success'))
        <div class="form-container form-container--inscripcion" style="padding: 2rem; text-align: center;">
            <p style="font-size: 1.1rem; color: #27ae60; margin-bottom: 1rem;">{{ session('success') }}</p>
            <p style="color: #666;">Cerrando ventana...</p>
        </div>
        <script>
            (function() {
                if (window.parent && window.parent.cerrarModalInscripcion) {
                    window.parent.cerrarModalInscripcion();
                    window.parent.location.reload();
                }
            })();
        </script>
    @else
    {{-- 1. BLOQUE DE ERROR (CANDADO) --}}
    @if(session('error'))
        <div class="umi-error-card">
            <h1><i class="fa-solid fa-lock"></i></h1>
            
            <h3>
                {{-- Título dinámico según el tipo de error --}}
                {{ Str::contains(session('error'), 'Periodo') ? 'Acción Bloqueada' : 'Error del Sistema' }}
            </h3>
            
            <p style="font-size: 1.1rem;">{{ session('error') }}</p>
            
            {{-- Solo mostramos el botón si es error de Periodo --}}
            @if(Str::contains(session('error'), 'Periodo'))
                <a href="{{ route('ajustes.show', ['seccion' => 'periods']) }}" class="umi-btn-error">
                    <i class="fa-solid fa-gear"></i> Ir a Ajustes para Activar Periodo
                </a>
            @endif
        </div> 
    
    {{-- 2. SI NO HAY ERROR, MOSTRAMOS EL FORMULARIO --}}
    @else
    <div class="form-container form-container--inscripcion">
        <div class="header-section">
            <div>
                <h2 class="form-title" style="text-align: center; margin: 0;">
                    {{-- Título Dinámico --}}
                    @if($esReinscripcion)
                         Reinscripción de Alumno <span style="font-size: 0.8em; opacity: 0.8;">(Al Semestre {{ $alumno->semestre + 1 }})</span>
                    @else
                         Inscripción
                    @endif
                </h2>
            </div>

            {{-- Badge de Status para Reinscripciones --}}
            @if($esReinscripcion)
                <div class="status-bar" style="margin-top: 10px; background: #fff3cd; padding: 8px 15px; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <strong>Status Actual:</strong> 
                    <span class="badge-status {{ strtolower($alumno->status) }}">{{ $alumno->status ?? 'Inactivo' }}</span>
                    <span style="font-size: 0.85rem; margin-left: 10px; color: #666;">
                        <i class="fa-solid fa-info-circle"></i> Al completar este proceso y validar el pago, el alumno pasará a <strong>Activo</strong>.
                    </span>
                </div>
            @endif
        </div>
        
        <div class="form-body">
            <style>
                #inscriptionForm .submit-button,
                #inscriptionForm .submit-button:hover {
                    box-shadow: none;
                }
                #inscriptionForm .submit-button:hover {
                    background-color: #1a3055;
                    color: white;
                }
            </style>
            {{-- Formulario Único: Maneja tanto STORE (Nuevo) como UPDATE (Reinscripción) --}}
            <form method="POST" 
                  action="{{ $esReinscripcion ? route('escolar.inscripcion.update', $alumno->id) : route('escolar.inscripcion.store') }}" 
                  class="registration-form" 
                  id="inscriptionForm"
                  enctype="multipart/form-data"
                  target="_self"
                  data-es-nuevo-registro="{{ $esReinscripcion ? '0' : '1' }}">
                @if(request('modal'))
                <input type="hidden" name="modal" value="1">
                @endif
                @csrf
                @if($esReinscripcion)
                    @method('PUT')
                @endif

                {{-- Si ya existe el usuario (ej. el rol activo es "estudiante"), evitamos recrearlo. --}}
                @if(!$esReinscripcion && isset($alumno))
                    <input type="hidden" name="existing_user_id" value="{{ $alumno->id }}">
                @endif
                
                {{-- Mensajes de Feedback --}}
                @if (session('success')) <div class="message-success">{{ session('success') }}</div> @endif
                @if (session('error')) <div class="message-error">{{ session('error') }}</div> @endif

                @if(!empty($bloqueadoPorAceptacion) && $bloqueadoPorAceptacion)
                    {{-- Overlay: bloquea edición hasta que Master acepte --}}
                    <div id="pendingAcceptanceOverlay"
                         style="position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 10060; display:flex; align-items:center; justify-content:center; padding: 20px;">
                        <div style="background:#fff; border-radius: 16px; max-width: 560px; width: 100%; box-shadow: 0 18px 50px rgba(0,0,0,0.35); padding: 22px 20px; text-align:center;">
                            <div style="width:72px; height:72px; border-radius:50%; background:#eaf7ea; margin: 0 auto 14px; display:flex; align-items:center; justify-content:center; border: 3px solid #cfe6c8; color:#2e7d32; font-size: 38px; font-weight: 800;">
                                ✓
                            </div>
                            <h3 style="margin: 0; color:#223F70; font-size: 1.25rem;">Información enviada con éxito</h3>
                            <p style="margin: 10px 0 0; color:#666; font-weight: 600; line-height: 1.5;">
                                Tu registro está en espera de aprobación por Control Escolar.
                            </p>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var form = document.getElementById('inscriptionForm');
                            if (!form) return;
                            form.querySelectorAll('input, select, textarea, button').forEach(function(el) {
                                el.disabled = true;
                            });
                        });
                    </script>
                @endif
                @if ($errors->any())
                    <div class="message-error">
                        <ul>@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                    </div>
                @endif

                {{-- DATOS PERSONALES --}}
                <h3> Datos Personales</h3>
                <hr>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Nombre(s)</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $alumno->nombre ?? '') }}" required placeholder="Ej. Juan Pablo">
                    </div>
                    <div class="form-field">
                        <label>Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno', $alumno->apellido_paterno ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno', $alumno->apellido_materno ?? '') }}" required>
                    </div>
                </div>

                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Email Personal</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $alumno->email ?? '') }}" required>
                        <small id="email_helper" style="display:none; color: #2980b9;">Este email está vinculado a la cuenta existente.</small>
                    </div>
                    <div class="form-field">
                        <label>Teléfono Celular</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $alumno->telefono ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>CURP</label>
                        <input type="text" name="curp" id="inputCurp" 
                            value="{{ old('curp', $alumno->curp ?? '') }}" 
                            placeholder="Clave Única de Registro de Población">
                    </div>
                </div>

                <div class="form-group-double">
                    <div class="form-field">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                               value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Edad</label>
                        <input type="number" id="edad" name="edad" value="{{ old('edad', $alumno->edad ?? '') }}" readonly style="background-color: #eee; cursor: not-allowed;">
                    </div>
                </div>

                {{-- 3. DIRECCIÓN --}}
                <h3> Domicilio</h3>
                <hr>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Calle y Número</label>
                        <input type="text" name="calle" value="{{ old('calle', $alumno->calle ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Colonia / Asentamiento</label>
                        <input type="text" name="colonia" value="{{ old('colonia', $alumno->colonia ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Código Postal</label>
                        <input type="text" name="codigo_postal" value="{{ old('codigo_postal', $alumno->codigo_postal ?? '') }}" required>
                    </div>
                </div>
                <div class="form-group-double">
                    <div class="form-field">
                        <label>Ciudad / Municipio</label>
                        <input type="text" name="ciudad" value="{{ old('ciudad', $alumno->ciudad ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Estado</label>
                        <input type="text" name="estado" value="{{ old('estado', $alumno->estado ?? '') }}" required>
                    </div>
                </div>

                {{-- 4. ACADÉMICO Y DOCUMENTOS --}}
                <h3> Datos Académicos y Documentación</h3>
                <hr>
                <div class="form-group-double">
                    <div class="form-field">
                        <label>Carrera a Cursar</label>
                        <select name="carrera_id" id="carrera_id" required>
                            <option value="">Seleccione una carrera...</option>
                            @foreach ($carreras as $carrera)
                                @php
                                    $precioTotalCarrera = (($carrera->pricing_mode ?? 'uniform') === 'per_month')
                                        ? (float) collect(is_array($carrera->monthly_prices) ? $carrera->monthly_prices : [])->sum()
                                        : ((float) ($carrera->monto_mensualidad ?? 0) * (int) ($carrera->semesters ?? 0));
                                @endphp
                                <option value="{{ $carrera->id }}"
                                        data-semesters="{{ (int) ($carrera->semesters ?? 1) }}"
                                        data-cargo-monetario="{{ $precioTotalCarrera > 0 ? $precioTotalCarrera : '' }}"
                                        {{ old('carrera_id', $alumno->carrera_id ?? '') == $carrera->id ? 'selected' : '' }}>
                                    {{ $carrera->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Semestre a Inscribir</label>
                        <input type="number" id="semestre" name="semestre" 
                               value="{{ old('semestre', $esReinscripcion ? ($alumno->semestre + 1) : 1) }}" 
                               readonly style="background-color: #e9ecef; font-weight: bold; border-color: #ced4da;">
                        @if($esReinscripcion)
                            <small style="color: #666;">(Avanza del semestre {{ $alumno->semestre }} al {{ $alumno->semestre + 1 }})</small>
                        @endif
                    </div>
                </div>

                {{-- CARGA DE DOCUMENTOS --}}
                <div class="docs-container" style="background: #ffffff; padding: 20px; border: 1px dashed #3498db; border-radius: 8px; margin-top: 20px;">
                    <h4 style="margin-top:0; color: #2980b9;"><i class="fa-solid fa-cloud-arrow-up"></i> Documentación Requerida</h4>
                    @if(isset($alumno) && (!empty($alumno->doc_acta_rechazado) || !empty($alumno->doc_certificado_rechazado) || !empty($alumno->doc_curp_rechazado) || !empty($alumno->doc_ine_rechazado) || !empty($alumno->doc_ficha_pago_rechazado ?? false) || !empty($alumno->doc_factura_xml_rechazado ?? false)))
                        <div class="doc-rechazo-banner" style="background: #fdecea; border: 1px solid #e74c3c; color: #922b21; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 0.95rem;">
                            <strong>Atención:</strong> Control escolar marcó uno o más documentos como incorrectos. Sube de nuevo los archivos indicados abajo.
                        </div>
                    @endif
                    <div class="form-group-double">
                        <div class="form-field">
                            <label>Acta de Nacimiento (PDF)</label>
                            @if(isset($alumno) && !empty($alumno->doc_acta_rechazado))
                                <p class="doc-rechazo-field-msg" style="color:#c0392b; font-size:0.88rem; margin:4px 0 8px;"><i class="fa-solid fa-circle-exclamation"></i> Documento rechazado — adjunta un archivo nuevo.</p>
                            @endif
                            <input type="file" name="doc_acta_nacimiento" accept=".pdf">
                            @if(isset($alumno) && $alumno->doc_acta_nacimiento)
                                <a href="{{ asset('storage/'.$alumno->doc_acta_nacimiento) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                        <div class="form-field">
                            <label>Certificado de Preparatoria (PDF)</label>
                            @if(isset($alumno) && !empty($alumno->doc_certificado_rechazado))
                                <p class="doc-rechazo-field-msg" style="color:#c0392b; font-size:0.88rem; margin:4px 0 8px;"><i class="fa-solid fa-circle-exclamation"></i> Documento rechazado — adjunta un archivo nuevo.</p>
                            @endif
                            <input type="file" name="doc_certificado_prepa" accept=".pdf">
                            @if(isset($alumno) && $alumno->doc_certificado_prepa)
                                <a href="{{ asset('storage/'.$alumno->doc_certificado_prepa) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="form-group-double">
                        <div class="form-field">
                            <label>CURP (PDF)</label>
                            @if(isset($alumno) && !empty($alumno->doc_curp_rechazado))
                                <p class="doc-rechazo-field-msg" style="color:#c0392b; font-size:0.88rem; margin:4px 0 8px;"><i class="fa-solid fa-circle-exclamation"></i> Documento rechazado — adjunta un archivo nuevo.</p>
                            @endif
                            <input type="file" name="doc_curp" accept=".pdf">
                            @if(isset($alumno) && $alumno->doc_curp)
                                <a href="{{ asset('storage/'.$alumno->doc_curp) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                        <div class="form-field">
                            <label>INE (Opcional)</label>
                            @if(isset($alumno) && !empty($alumno->doc_ine_rechazado))
                                <p class="doc-rechazo-field-msg" style="color:#c0392b; font-size:0.88rem; margin:4px 0 8px;"><i class="fa-solid fa-circle-exclamation"></i> Documento rechazado — adjunta un archivo nuevo.</p>
                            @endif
                            <input type="file" name="doc_ine" accept=".pdf,.jpg,.png">
                            @if(isset($alumno) && $alumno->doc_ine)
                                <a href="{{ asset('storage/'.$alumno->doc_ine) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 6. FACTURACIÓN DINÁMICA --}}
                <div class="billing-container" style="background: #fdf2f2; padding: 20px; border: 1px solid #e74c3c; border-radius: 8px; margin-top: 20px;">
                    <h4 style="margin-top:0; color: #c0392b;"><i class="fa-solid fa-money-bill-wave"></i> Ficha de Pago / Facturación</h4>
                    <hr style="border-top: 1px solid #e74c3c; opacity: 0.3;">
                    
                    <div style="display: flex; gap: 15px; align-items: flex-start;">
                        <div style="flex: 0 0 auto; margin-top: 5px;">
                            <input type="checkbox" name="generar_factura" id="generar_factura" value="1" style="width: 20px; height: 20px; cursor: pointer;">
                        </div>
                        <div style="width: 100%;">
                            <label for="generar_factura" style="font-weight: bold; cursor: pointer; color: #c0392b; font-size: 1.05em;">
                                Generar Ficha de Pago para este Movimiento
                            </label>
                            <p style="font-size: 0.9em; color: #666; margin: 5px 0;">
                                Marque esta opción para crear una cuenta por cobrar. Los Anfitriones generalmente <u>no requieren</u> este cargo.
                            </p>
                            
                            {{-- DETALLES DINÁMICOS DE FACTURACIÓN --}}
                            <div id="billing-details" style="display: none; margin-top: 15px; background: white; padding: 15px; border-radius: 6px; border: 1px dashed #e74c3c;">
                                
                                {{-- 1. Período Activo --}}
                                <label for="modal_period_id" style="font-weight:bold; display:block; margin-top:10px;">Período Activo:</label>
                                <select id="modal_period_id" name="period_id" class="filter-select" style="width:100%; background-color: #e9ecef; pointer-events: none;" readonly tabindex="-1">
                                    @if(isset($periods))
                                        @foreach ($periods as $period)
                                            @if($period->is_active == 1)
                                                <option value="{{ $period->id }}" selected>{{ $period->name }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>

                                {{-- 2. Concepto --}}
                                <label for="modal_concepto" style="font-weight:bold; display:block; margin-top:10px;">Concepto:</label>
                                {{-- Solo concepto ligado a la carrera (monto desde data-cargo-monetario); sin catálogo billing_concepts --}}
                                <select id="modal_concepto" name="concepto" class="filter-select" style="width: 100%; padding: 8px;">
                                    <option value="Inscripción" data-from-career="1" selected>Inscripción</option>
                                </select>

                                {{-- 3. Monto --}}
                                <label for="modal_monto_visible" style="font-weight:bold; display:block; margin-top:10px;">Monto de tiempo normal:</label>
                                <input type="text" 
                                       id="modal_monto_visible" 
                                       readonly 
                                       placeholder="$ 0.00"
                                       style="width: 100%; padding: 10px; background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; color: #333; transition: background-color 0.3s;">
                                <input type="hidden" id="modal_monto" name="monto">

                                {{-- 4. Fecha Vencimiento --}}
                                <strong style="display:block; margin-top: 15px;">Fecha Vencimiento (Asignada por sistema):</strong>
                                <p id="texto_fecha_vencimiento" style="font-weight: bold; color: #223F70; margin: 5px 0 15px 0; font-size: 1.1em;">
                                    {{ \Carbon\Carbon::now()->addDays(7)->format('d/m/Y') }}
                                </p>

                                {{-- 5. Estado --}}
                                <label for="modal_status" style="font-weight:bold; display:block; margin-top:10px;">Estado:</label>
                                <select id="modal_status" name="status" style="width: 100%; padding: 8px; margin-bottom: 20px;">
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Pagada">Pagada</option>
                                </select>
                            </div>

                            {{-- Archivos de facturación: fuera del bloque colapsable para poder corregir sin volver a marcar la casilla --}}
                            <div id="billing-files-block" class="billing-files-block" style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed rgba(231,76,60,0.35);">
                                <label for="modal_archivo_pdf" style="font-weight:bold; display:block; margin-top:6px;">Ficha de pago / comprobante (PDF):</label>
                                @if(isset($alumno) && !empty($alumno->doc_ficha_pago_rechazado ?? false))
                                    <p class="doc-rechazo-field-msg" style="color:#c0392b; font-size:0.88rem; margin:4px 0 8px;"><i class="fa-solid fa-circle-exclamation"></i> Documento rechazado — adjunta un PDF nuevo.</p>
                                @endif
                                <input type="file" id="modal_archivo_pdf" name="archivo" accept=".pdf" style="width: 100%;">
                                <small style="color: #666;">Solo archivos .pdf</small>
                                @if(isset($alumno) && $alumno->doc_ficha_pago ?? false)
                                    <div style="margin-top:6px;"><a href="{{ asset('storage/'.$alumno->doc_ficha_pago) }}" target="_blank" class="link-view-doc"><i class="fa-regular fa-eye"></i> Ver ficha actual</a></div>
                                @endif

                                @if(isset($alumno) && ($alumno->doc_factura_xml ?? false))
                                    <div style="margin-top:6px;"><a href="{{ asset('storage/'.$alumno->doc_factura_xml) }}" target="_blank" class="link-view-doc"><i class="fa-regular fa-eye"></i> Ver factura actual</a></div>
                                @endif
                                <small id="billing-files-help" style="display:block; color:#666; margin-top:8px;">
                                    Estos archivos solo se adjuntan cuando el estado está en "Pagada".
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN HISTORIAL (Solo para Reinscripciones) --}}
                @if(isset($alumno) && isset($historialInscripciones))
                    <div class="history-container" style="margin-top: 30px;">
                        <h3> Historial de Inscripciones Anteriores</h3>
                        <hr>
                        <div style="overflow-x: auto;">
                            <table class="umi-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                                <thead style="background: #f1f1f1;">
                                    <tr>
                                        <th style="padding: 10px; text-align: left;">Semestre</th>
                                        <th style="padding: 10px; text-align: left;">Fecha</th>
                                        <th style="padding: 10px; text-align: left;">Carrera</th>
                                        <th style="padding: 10px; text-align: center;">Documentos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($historialInscripciones as $hist)
                                        <tr style="border-bottom: 1px solid #eee;">
                                            <td style="padding: 10px;">Semestre {{ $hist->semestre }}</td>
                                            <td style="padding: 10px;">{{ \Carbon\Carbon::parse($hist->created_at)->format('d/m/Y') }}</td>
                                            <td style="padding: 10px;">{{ $hist->carrera->name ?? 'N/A' }}</td>
                                            <td style="padding: 10px; text-align: center;">
                                                <a href="#" style="color: #3498db; text-decoration: none;">
                                                    <i class="fa-solid fa-folder-open"></i> Ver Expediente
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="padding: 15px; text-align: center; color: #999;">
                                                No hay registros anteriores disponibles.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="form-action-buttons">
                    <button type="submit" class="submit-button">
                        <i class="fa-solid fa-save"></i> {{ $esReinscripcion ? 'Enviar formulario' : 'Enviar formulario' }}
                    </button>
                </div>
                </form>
            </div>
        </div>

<script>
    function ejecutarLogicaInscripcion() {
        // ELEMENTOS PERSONALES
        const emailHelper = document.getElementById('email_helper');
        const inputFechaNac = document.getElementById('fecha_nacimiento');
        const inputEdad = document.getElementById('edad');

        // ELEMENTOS FACTURACIÓN
        const checkFactura = document.getElementById('generar_factura');
        const billingDetails = document.getElementById('billing-details');
        const conceptoSelect = document.getElementById('modal_concepto');
        const montoVisible = document.getElementById('modal_monto_visible');
        const montoHidden = document.getElementById('modal_monto');
        const carreraSelect = document.getElementById('carrera_id');
        const statusSelect = document.getElementById('modal_status');
        const billingFilesBlock = document.getElementById('billing-files-block');

        // --- LÓGICA DE FACTURACIÓN (DESPLIEGUE DEL MENÚ) ---
        // Esta función ahora muestra/oculta el bloque de detalles de la factura.
        function toggleFactura() {
            if (checkFactura && billingDetails) {
                if (checkFactura.checked) {
                    billingDetails.style.display = 'block';
                } else {
                    billingDetails.style.display = 'none';
                }
            }
            toggleBillingFilesByStatus();
        }

        function toggleBillingFilesByStatus() {
            if (!billingFilesBlock || !statusSelect || !checkFactura) return;
            const estado = (statusSelect.value || '').trim().toLowerCase();
            const debeMostrar = checkFactura.checked && estado === 'pagada';
            billingFilesBlock.style.display = debeMostrar ? 'block' : 'none';
            billingFilesBlock.querySelectorAll('input[type="file"]').forEach(function(inp) {
                inp.disabled = !debeMostrar;
                if (!debeMostrar) inp.value = '';
            });
        }
        
        // Listener para el checkbox de Factura: establece que el usuario lo ha cambiado
        if (checkFactura) {
             checkFactura.addEventListener('change', function() {
                this.dataset.userChanged = 'true'; // El usuario ha interactuado
                toggleFactura();
            });
        }
        if (statusSelect) {
            statusSelect.addEventListener('change', toggleBillingFilesByStatus);
        }

        function obtenerCargoMonetarioCarreraSeleccionada() {
            if (!carreraSelect || carreraSelect.selectedIndex < 0) return null;
            const opt = carreraSelect.options[carreraSelect.selectedIndex];
            const raw = opt && opt.getAttribute('data-cargo-monetario');
            if (raw === null || raw === '') return null;
            const n = parseFloat(raw);
            return isNaN(n) ? null : n;
        }

        function aplicarMontoFacturacion() {
            if (!conceptoSelect || !montoVisible || !montoHidden) return;
            const selectedOption = conceptoSelect.options[conceptoSelect.selectedIndex];
            const usaCarrera = selectedOption && selectedOption.getAttribute('data-from-career') === '1';
            let amount = null;
            if (usaCarrera) {
                amount = obtenerCargoMonetarioCarreraSeleccionada();
            } else {
                const a = selectedOption && selectedOption.getAttribute('data-amount');
                if (a !== null && a !== '') {
                    const n = parseFloat(a);
                    amount = isNaN(n) ? null : n;
                }
            }
            if (amount !== null && amount >= 0) {
                montoVisible.value = '$ ' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                montoHidden.value = String(amount);
            } else {
                montoVisible.value = '$ 0.00';
                montoHidden.value = '';
            }
        }

        if (conceptoSelect) {
            conceptoSelect.addEventListener('change', aplicarMontoFacturacion);
        }

        // Factura obligatoria por defecto al cargar
        if (checkFactura && billingDetails) {
            checkFactura.checked = true;
            billingDetails.style.display = 'block';
        }
        toggleBillingFilesByStatus();

        // --- LÓGICA CÁLCULO DE EDAD ---
        if (inputFechaNac && inputEdad) {
            function calcularEdad() {
                const fechaNac = inputFechaNac.value;
                if (fechaNac) {
                    // Parseo LOCAL para evitar desfase por zona horaria (YYYY-MM-DD)
                    const parts = fechaNac.split('-').map(Number);
                    const birthDate = (parts.length === 3) ? new Date(parts[0], parts[1] - 1, parts[2]) : null;
                    if (!birthDate || Number.isNaN(birthDate.getTime())) {
                        inputEdad.value = '';
                        return;
                    }

                    const today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const hasHadBirthday =
                        (today.getMonth() > birthDate.getMonth()) ||
                        (today.getMonth() === birthDate.getMonth() && today.getDate() >= birthDate.getDate());
                    if (!hasHadBirthday) age--;
                    inputEdad.value = age >= 0 ? age : 0;
                } else {
                    inputEdad.value = '';
                }
            }

            inputFechaNac.addEventListener('change', calcularEdad);
            // Ejecutar al inicio por si hay un valor pre-cargado
            calcularEdad();
        }

        // --- LÓGICA: semestre a inscribir ---
        // En nuevo registro de aspirante siempre semestre 1; en reinscripción se mantiene el valor del servidor
        const inputSemestre = document.getElementById('semestre');
        const formInscripcion = document.getElementById('inscriptionForm');
        const esNuevoRegistro = formInscripcion && formInscripcion.getAttribute('data-es-nuevo-registro') === '1';

        function actualizarSemestreSegunCarrera() {
            if (!inputSemestre) return;
            if (esNuevoRegistro) {
                inputSemestre.value = '1';
                return;
            }
            // Reinscripción: mantener valor actual (viene del servidor)
            const opt = carreraSelect && carreraSelect.options[carreraSelect.selectedIndex];
            const totalSemestres = opt ? parseInt(opt.getAttribute('data-semesters') || '0', 10) : 0;
            if (totalSemestres > 0) {
                const actual = parseInt(inputSemestre.value || '1', 10) || 1;
                inputSemestre.value = Math.min(actual, totalSemestres);
            }
        }

        if (carreraSelect && inputSemestre) {
            carreraSelect.addEventListener('change', function() {
                actualizarSemestreSegunCarrera();
                aplicarMontoFacturacion();
            });
            actualizarSemestreSegunCarrera();
        } else if (carreraSelect) {
            carreraSelect.addEventListener('change', aplicarMontoFacturacion);
        }

        aplicarMontoFacturacion();

    }
    ejecutarLogicaInscripcion();

</script>
    
@endif
@endif
@endsection