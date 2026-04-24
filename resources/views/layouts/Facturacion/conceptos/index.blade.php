@extends('layouts.app')
@section('title', 'Conceptos y montos - ' . session('active_institution_name'))
@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
<div class="main-content-area">
    
    <header class="main-header">
        <h1>Conceptos y Montos</h1>
        <div class="header-actions">
            <form method="GET" action="{{ route('facturacion.conceptos.index') }}" class="search-form" id="concept-search-form">
                <div style="position: relative; display: inline-flex; align-items: center;">
                    <img
                        src="{{ asset('images/icons/magnifying-glass-svgrepo-com.svg') }}"
                        alt=""
                        aria-hidden="true"
                        style="position: absolute; left: 10px; width: 16px; height: 16px; opacity: 1; pointer-events: none;"
                    >
                    <input
                        type="text"
                        name="search"
                        id="concept-search-input"
                        placeholder="Buscar por..."
                        value="{{ request('search') }}"
                        style="padding-left: 34px;"
                    >
                </div>
            </form>
            <button id="openModalBtn" class="btn-primary">
                + Agregar Concepto
            </button>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px;">{{ session('error') }}</div>
    @endif

    <div class="table-container">
        <table class="main-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Cargo moratorio</th>
                    <th>Cargo monetario</th>
                    <th>Descripción</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="concepts-table-body">
                @include('layouts.Facturacion.conceptos.partials.table_rows', ['conceptos' => $conceptos])
            </tbody>
        </table>
        {{-- Paginación eliminada intencionalmente --}}
    </div>
</div>

{{-- MODAL --}}
<div id="formModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="modalTitle">Nuevo Concepto</h2>
        
        <form id="modalForm" method="POST" action="">
            @csrf
            <div id="methodContainer"></div>

            <div id="modalBody">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="concept" style="font-weight: bold; display: block; margin-bottom: 5px;">Nombre del Concepto <span style="color:red">*</span></label>
                    <input type="text" id="concept" name="concept" class="form-control" placeholder="Ej. Inscripción Semestral" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="amount" style="font-weight: bold; display: block; margin-bottom: 5px;">Monto (MXN) <span style="color:red">*</span></label>
                    <div class="input-group" style="display: flex;">
                        <span style="padding: 8px; background: #eee; border: 1px solid #ccc; border-right: none; border-radius: 4px 0 0 4px;">$</span>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 0 4px 4px 0;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="description" style="font-weight: bold; display: block; margin-bottom: 5px;">Descripción</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Detalles opcionales..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="porcentaje_cargo_moratorio" style="font-weight: bold; display: block; margin-bottom: 5px;">Porcentaje de cargo moratorio</label>
                    <input type="number" id="porcentaje_cargo_moratorio" name="porcentaje_cargo_moratorio" class="form-control" step="0.01" min="0" placeholder="0.00" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="cargo_monetario" style="font-weight: bold; display: block; margin-bottom: 5px;">Cargo moratorio:</label>
                    <input type="number" id="cargo_monetario" name="cargo_monetario" class="form-control" step="0.01" min="0" placeholder="0.00" readonly title="Calculado automáticamente: Monto × (Porcentaje / 100)" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background-color: #f8f9fa;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="fecha_vencimiento_moratorio" style="font-weight: bold; display: block; margin-bottom: 5px;">Fecha vencimiento (asignada por sistema):</label>
                    <input type="date" id="fecha_vencimiento_moratorio" name="fecha_vencimiento_moratorio" class="form-control"
                        style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                {{-- CORRECCIÓN DEL CHECKBOX --}}
                <div class="form-group" style="margin-top: 20px; display: flex; align-items: center; justify-content: flex-start; gap: 15px;">
    <label for="is_active" style="font-weight: bold; margin: 0; cursor: pointer;">¿Concepto Activo?</label>
    
    <label class="custom-switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
        <input type="checkbox" id="is_active" name="is_active" value="1" checked style="opacity: 0; width: 0; height: 0;">
        
        {{-- Quitamos los estilos en línea del span para que el CSS funcione --}}
        <span class="slider round"></span>

        <style>
            /* Estilo base (Gris cuando está apagado) */
            .slider {
                position: absolute;
                cursor: pointer;
                top: 0; left: 0; right: 0; bottom: 0;
                background-color: #ccc; /* Color gris por defecto */
                transition: .4s;
                border-radius: 34px;
            }

            /* El circulo blanco */
            .slider:before {
                position: absolute;
                content: "";
                height: 18px; width: 18px;
                left: 4px; bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            /* Estilo ACTIVO (Azul cuando está encendido) */
            .custom-switch input:checked + .slider {
                background-color: #0d6efd; /* <--- AQUÍ ESTÁ EL AZUL */
            }

            /* Mover el círculo cuando está activo */
            .custom-switch input:checked + .slider:before {
                transform: translateX(24px);
            }
        </style>
    </label>
</div>
            
            <button type="submit" class="btn-primary" style="margin-top: 20px; width: 100%; padding: 10px;">Guardar</button>
        </form>
    </div>
</div>

<script>
   (function initConceptosScript() {
        const searchForm = document.getElementById('concept-search-form');
        const searchInput = document.getElementById('concept-search-input');
        const tableBody = document.getElementById('concepts-table-body');
        const modal = document.getElementById('formModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('modalForm');
        const methodContainer = document.getElementById('methodContainer');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModal = document.querySelector('.close-modal');
        
        // RUTAS
        const storeUrl = "{{ route('facturacion.conceptos.store') }}";
        // CORRECCIÓN: Usamos url() en lugar de route() para evitar el error de parámetro
        const baseUrl = "{{ url('facturacion/conceptos') }}"; 

        // Búsqueda en vivo: refresca solo la tabla.
        if (searchForm && searchInput && tableBody) {
            let debounceTimer;
            const refreshTable = function() {
                const search = (searchInput.value || '').trim();
                const params = new URLSearchParams();
                if (search !== '') params.set('search', search);
                const url = searchForm.action + (params.toString() ? '?' + params.toString() : '');
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    }
                })
                .then(function(r) { if (!r.ok) throw new Error('Error'); return r.text(); })
                .then(function(html) {
                    tableBody.innerHTML = html;
                })
                .catch(function() {
                    // Fallback: sin recargar toda la página, mantenemos estado actual.
                });
            };

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(refreshTable, 250);
            });
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                refreshTable();
            });
        }
        
        function resetForm() {
            modalForm.reset();
            methodContainer.innerHTML = ''; 
            modalForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            modalForm.querySelectorAll('.text-danger').forEach(el => el.remove());
            const check = document.getElementById('is_active');
            if(check) check.checked = true;
            const fvInput = document.getElementById('fecha_vencimiento_moratorio');
            if (fvInput) fvInput.value = '';
            recalcConceptCargoMoratorio();
        }

        /** Convierte respuesta API/JSON a valor yyyy-mm-dd para input[type=date]. */
        function fechaVencimientoToDateInputValue(raw) {
            if (!raw) return '';
            const s = String(raw).slice(0, 10);
            if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
            const d = new Date(raw);
            if (!Number.isNaN(d.getTime())) {
                const y = d.getFullYear();
                const mo = String(d.getMonth() + 1).padStart(2, '0');
                const da = String(d.getDate()).padStart(2, '0');
                return y + '-' + mo + '-' + da;
            }
            return '';
        }

        function recalcConceptCargoMoratorio() {
            const amountEl = document.getElementById('amount');
            const pctEl = document.getElementById('porcentaje_cargo_moratorio');
            const cargoEl = document.getElementById('cargo_monetario');
            if (!amountEl || !pctEl || !cargoEl) return;

            const amount = parseFloat(String(amountEl.value || '').replace(',', '.'));
            const pct = parseFloat(String(pctEl.value || '').replace(',', '.'));

            if (Number.isNaN(amount) || Number.isNaN(pct) || amount < 0 || pct < 0) {
                cargoEl.value = '';
                return;
            }

            const cargo = amount * (pct / 100);
            cargoEl.value = (Math.round(cargo * 100) / 100).toFixed(2);
        }

        if(openModalBtn) {
            openModalBtn.addEventListener('click', function () {
                modalTitle.textContent = "Agregar Concepto";
                resetForm();
                modalForm.action = storeUrl; 
                modal.style.display = 'block';
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-edit');
            if (!btn) return;
            e.preventDefault();

            const data = JSON.parse(btn.dataset.item);
            const itemId = btn.dataset.id;

            modalTitle.textContent = `Editar Concepto #${itemId}`;
            resetForm();

            // CORRECCIÓN: Construimos la URL manualmente
            modalForm.action = `${baseUrl}/${itemId}`;

            methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

            document.getElementById('concept').value = data.concept;
            document.getElementById('amount').value = data.amount;
            document.getElementById('description').value = data.description || '';
            document.getElementById('porcentaje_cargo_moratorio').value = data.porcentaje_cargo_moratorio ?? '';
            document.getElementById('cargo_monetario').value = data.cargo_monetario ?? '';
            recalcConceptCargoMoratorio();

            const fvInput = document.getElementById('fecha_vencimiento_moratorio');
            if (fvInput) {
                fvInput.value = fechaVencimientoToDateInputValue(data.fecha_vencimiento_moratorio ?? null);
            }

            const check = document.getElementById('is_active');
            if(check) check.checked = (data.is_active == 1);

            modal.style.display = 'block';
        });

        modalForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // 1. OBTENER EL BOTÓN Y DESHABILITARLO
            const submitBtn = modalForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerText = "Guardando..."; // Feedback visual opcional

            // Limpieza de errores previos
            modalForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            modalForm.querySelectorAll('.text-danger').forEach(el => el.remove());

            const formData = new FormData(modalForm);
            
            try {
                const response = await fetch(modalForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    // 2. SI FALLA, REACTIVAR EL BOTÓN
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Guardar";

                    if (response.status === 422) {
                        const data = await response.json();
                        Object.keys(data.errors).forEach(field => {
                            const input = modalForm.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const msg = document.createElement('div');
                                msg.className = 'text-danger';
                                msg.style.fontSize = '0.85em';
                                msg.style.marginTop = '5px';
                                msg.style.color = '#dc3545';
                                msg.innerText = data.errors[field][0];
                                input.parentNode.appendChild(msg);
                            }
                        });
                    } else {
                        alert('Ocurrió un error inesperado.');
                    }
                }
            } catch (error) {
                console.error(error);
                alert('Error de conexión.');
                
                // 3. SI HAY ERROR DE RED, REACTIVAR EL BOTÓN
                submitBtn.disabled = false;
                submitBtn.innerText = "Guardar";
            }
        });

        if(closeModal){
            closeModal.addEventListener('click', () => { modal.style.display = 'none'; });
        }
        ['amount', 'porcentaje_cargo_moratorio'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', recalcConceptCargoMoratorio);
                el.addEventListener('change', recalcConceptCargoMoratorio);
            }
        });
        window.addEventListener('click', (event) => {
            if (event.target == modal) { modal.style.display = 'none'; }
        });
    })();
</script>
@endsection