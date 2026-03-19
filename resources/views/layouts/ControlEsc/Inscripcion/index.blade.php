@extends(request('modal') ? 'layouts.iframe_content' : 'layouts.app')

@section('title', isset($alumno) ? 'Proceso de Reinscripción' : 'Registro de Aspirante')

@if(!request('modal'))
@vite(['resources/css/ControlEsc/base.css','resources/js/app.js'])
@endif

@section('content')

    {{-- 0. ÉXITO EN MODAL: cerrar modal y refrescar lista en el padre --}}
    @if(request('modal') && request('success') && session('success'))
        <div class="form-container" style="padding: 2rem; text-align: center;">
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
    <div class="form-container">
        <div class="header-section">
            <div style="display: grid; grid-template-columns: 32px 1fr 32px; align-items: center;">
                <span aria-hidden="true"></span>
                <h2 class="form-title" style="text-align: center; margin: 0;">
                    {{-- Título Dinámico --}}
                    @if(isset($alumno))
                         Reinscripción de Alumno <span style="font-size: 0.8em; opacity: 0.8;">(Al Semestre {{ $alumno->semestre + 1 }})</span>
                    @else
                         Nuevo Registro de Aspirante
                    @endif
                </h2>
                @if(request('modal'))
                <a href="#" onclick="if(window.parent && window.parent.cerrarModalInscripcion) window.parent.cerrarModalInscripcion(); return false;"
                   class="btn-back"
                   aria-label="Cerrar"
                   style="text-decoration: none; color: #666; font-size: 1.8rem; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; justify-self: end;">
                    &times;
                </a>
                @else
                <a href="{{ route('escolar.students.index') }}"
                   class="btn-back"
                   aria-label="Cerrar"
                   style="text-decoration: none; color: #666; font-size: 1.8rem; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; justify-self: end;">
                    &times;
                </a>
                @endif
            </div>

            {{-- Badge de Status para Reinscripciones --}}
            @if(isset($alumno))
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
                  action="{{ isset($alumno) ? route('escolar.inscripcion.update', $alumno->id) : route('escolar.inscripcion.store') }}" 
                  class="registration-form" 
                  id="inscriptionForm"
                  enctype="multipart/form-data"
                  target="_self"
                  data-es-nuevo-registro="{{ isset($alumno) ? '0' : '1' }}">
                @if(request('modal'))
                <input type="hidden" name="modal" value="1">
                @endif
                @csrf
                @if(isset($alumno))
                    @method('PUT')
                @endif
                
                {{-- Mensajes de Feedback --}}
                @if (session('success')) <div class="message-success">{{ session('success') }}</div> @endif
                @if (session('error')) <div class="message-error">{{ session('error') }}</div> @endif
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
                                <option value="{{ $carrera->id }}"
                                        data-semesters="{{ (int) ($carrera->semesters ?? 1) }}"
                                        {{ old('carrera_id', $alumno->carrera_id ?? '') == $carrera->id ? 'selected' : '' }}>
                                    {{ $carrera->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Semestre a Inscribir</label>
                        <input type="number" id="semestre" name="semestre" 
                               value="{{ old('semestre', isset($alumno) ? ($alumno->semestre + 1) : 1) }}" 
                               readonly style="background-color: #e9ecef; font-weight: bold; border-color: #ced4da;">
                        @if(isset($alumno))
                            <small style="color: #666;">(Avanza del semestre {{ $alumno->semestre }} al {{ $alumno->semestre + 1 }})</small>
                        @endif
                    </div>
                </div>

                {{-- CARGA DE DOCUMENTOS --}}
                <div class="docs-container" style="background: #ffffff; padding: 20px; border: 1px dashed #3498db; border-radius: 8px; margin-top: 20px;">
                    <h4 style="margin-top:0; color: #2980b9;"><i class="fa-solid fa-cloud-arrow-up"></i> Documentación Requerida</h4>
                    
                    <div class="form-group-double">
                        <div class="form-field">
                            <label>Acta de Nacimiento (PDF)</label>
                            <input type="file" name="doc_acta_nacimiento" accept=".pdf">
                            @if(isset($alumno) && $alumno->doc_acta_nacimiento)
                                <a href="{{ asset('storage/'.$alumno->doc_acta_nacimiento) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                        <div class="form-field">
                            <label>Certificado de Preparatoria (PDF)</label>
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
                            <input type="file" name="doc_curp" accept=".pdf">
                            @if(isset($alumno) && $alumno->doc_curp)
                                <a href="{{ asset('storage/'.$alumno->doc_curp) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                        <div class="form-field">
                            <label>INE (Opcional)</label>
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
                                <select id="modal_concepto" name="concepto" class="filter-select" style="width: 100%; padding: 8px;">
                                    <option value="" data-amount="">-- Seleccione un concepto --</option>
                                    <option value="Inscripción de nuevo ingreso" data-amount="80000">Inscripción de nuevo ingreso</option>
                                    @if(isset($conceptosDisponibles) && $conceptosDisponibles->isNotEmpty())
                                        @foreach($conceptosDisponibles as $c)
                                            <option value="{{ $c->concept }}" data-amount="{{ $c->amount }}">
                                                {{ $c->concept }} — $ {{ number_format((float)$c->amount, 2) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>

                                {{-- 3. Monto --}}
                                <label for="modal_monto_visible" style="font-weight:bold; display:block; margin-top:10px;">Monto:</label>
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

                                {{-- 6. Archivos (OPCIONALES) --}}
                                <label for="modal_archivo_pdf" style="font-weight:bold; display:block; margin-top:10px;">Archivo (PDF) (Opcional):</label>
                                <input type="file" id="modal_archivo_pdf" name="archivo" accept=".pdf" style="width: 100%;">
                                <small style="color: #666;">Solo archivos .pdf</small>

                                <label for="modal_archivo_xml" style="font-weight:bold; display:block; margin-top:10px;">Subir XML (Opcional):</label>
                                <input type="file" id="modal_archivo_xml" name="archivo_xml" accept=".xml,text/xml" style="width: 100%;">
                                <small style="color: #666;">Solo archivos .xml</small>
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
                        <i class="fa-solid fa-save"></i> {{ isset($alumno) ? 'Guardar Reinscripción' : 'Registrar Aspirante' }}
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
        }
        
        // Listener para el checkbox de Factura: establece que el usuario lo ha cambiado
        if (checkFactura) {
             checkFactura.addEventListener('change', function() {
                this.dataset.userChanged = 'true'; // El usuario ha interactuado
                toggleFactura();
            });
        }

        // Listener para el selector de concepto: actualiza el monto
        if (conceptoSelect) {
            conceptoSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const amount = selectedOption.getAttribute('data-amount');
                
                if (montoVisible && montoHidden) {
                    if (amount) {
                        // Formatear el monto para visualización
                        montoVisible.value = '$ ' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        montoHidden.value = amount; // Valor limpio para el backend
                    } else {
                        montoVisible.value = '$ 0.00';
                        montoHidden.value = '';
                    }
                }
            });
        }


        // Factura obligatoria por defecto al cargar
        if (checkFactura && billingDetails) {
            checkFactura.checked = true;
            billingDetails.style.display = 'block';
        }

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
        const carreraSelect = document.getElementById('carrera_id');
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
            carreraSelect.addEventListener('change', actualizarSemestreSegunCarrera);
            actualizarSemestreSegunCarrera();
        }

    }
    ejecutarLogicaInscripcion();

</script>
    
@endif
@endif
@endsection