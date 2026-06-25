@extends('layouts.app')
@section('title', 'Ajustes - ' . session('active_institution_name'))
@vite(['resources/css/Cursos/courses.css', 'resources/js/app.js'])
@section('content')
<div class="main-content-area">
    
    {{-- Cabecera con título y botones --}}
    <header class="main-header">
        <h3>{{ strtoupper($page_title) }}</h3>
        <div class="header-actions">
            <form method="GET" action="{{ route('ajustes.show', ['seccion' => $seccion]) }}" class="search-form" id="searchForm">
                <span class="search-form-icon">
                    <img src="{{ asset('images/icons/magnifying-glass-svgrepo-com.svg') }}" alt="Buscar" width="18" height="18">
                </span>
                <input type="text" name="search" placeholder="Buscar por..." value="{{ request('search') }}" id="searchInput" autocomplete="off">
            </form>
            <button id="openModalBtn" class="btn-primary">
                + Agregar {{ $singular_title }}
            </button>
        </div>
    </header>
    {{-- Contenedor de la tabla (scroll interno + responsive) --}}
    <div class="table-container">
        <div class="ajustes-table-scroll">
        <table class="main-table @if($seccion === 'users') main-table--users-wide @endif">
            <thead>
                <tr>
                    @if ($seccion === 'institutions')
                        <th>ID</th>
                        <th>Nombre de Unidad</th>
                        <th>Logo</th>
                    @elseif ($seccion === 'departments')
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Unidad de Negocio</th>
                    @elseif ($seccion === 'workstations')
                        <th>ID</th>
                        <th>Nombre del Puesto</th>
                        <th>Departamento</th>
                    @elseif ($seccion === 'periods')
                      
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Mensualidades</th>
                        <th>Estatus</th>
                    @elseif ($seccion === 'users')
                        <th>Usuario</th>
                        <th>Unidad de Negocio</th>
                        <th>Nombre</th>
                        <th>A. Paterno</th>
                        <th>A. Materno</th>
                        <th>Rol</th>
                    @endif
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="ajustes-table-body">
                @forelse ($data as $item)
                    <tr>
                        @if ($seccion === 'institutions')
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>
                                @if($item->logo_path)
                                    <img src="{{ asset('storage/' . $item->logo_path) }}" alt="Logo" class="table-logo">
                                @else
                                    <span>Sin logo</span>
                                @endif
                            </td>
                        @elseif ($seccion === 'departments')
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->institution->name ?? 'N/A' }}</td> 
                        @elseif ($seccion === 'workstations')
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->department->name ?? 'N/A' }}</td>
                        @elseif ($seccion === 'periods')
                            <td>{{ ucfirst(optional($item->start_date)?->isoFormat('MMMM YYYY')) }}</td>
                            <td>{{ ucfirst(optional($item->end_date)?->isoFormat('MMMM YYYY')) }}</td>
                            <td>{{ $item->monthly_payments_count ?? 'N/A' }}</td>
                            <td class="status-toggle-cell">
                                <div class="status-content-wrapper">
                                  
                                    <form action="{{ route('ajustes.periods.toggleStatus', $item->id) }}" 
                                          method="POST" 
                                          class="inline-form"
                                          onsubmit="return confirm('¿Estás seguro de cambiar el estatus de este periodo?');">
                                        @csrf
                                        @method('POST')
                                        
                                        <label class="switch" title="{{ $item->is_active ? 'Activo' : 'Inactivo' }}">
                                            <input 
                                                type="checkbox" 
                                                {{ $item->is_active ? 'checked' : '' }}
                                                onchange="this.form.submit()" 
                                            >
                                            <span class="slider"></span>
                                        </label>
                                    </form>

                                  
                                    
                                </div> 
                            </td>
                        @elseif ($seccion === 'users')
                            <td>{{ $item->RFC }}</td>
                            <td>{{ $item->institutions->first()->name ?? 'N/A' }}</td>
                            <td>{{ $item->nombre }}</td>
                            <td>{{ $item->apellido_paterno }}</td>
                            <td>{{ $item->apellido_materno }}</td>
                            <td>{{ $item->roleDisplayNameForAjustes() }}</td>
                            
                        @endif
                        
                        {{-- =================================================== --}}
                        {{-- BLOQUE DE ACCIONES  --}}
                        {{-- =================================================== --}}
                        <td> 
                            <div class="actions">
                            {{-- 1. Botón "Editar" --}}
                            <a href="#" 
                                title="Editar" 
                                class="btn-icon btn-edit"
                                data-id="{{ $item->id }}"> 
                                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
                            </a>
                            
                            {{-- 2. Botón "Eliminar" --}}
                            <form action="{{ route('ajustes.destroy', ['seccion' => $seccion, 'id' => $item->id]) }}" 
                                method="POST" 
                                class="inline-form"
                                onsubmit="return confirm('ADVERTENCIA: ¿Estás seguro de ELIMINAR PERMANENTEMENTE este registro? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar Permanente" class="btn-delete">
                                    <img src="{{ asset('images/icons/delete-left-solid-full.svg') }}" alt="Eliminar">
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr> 
                @empty
                    <tr>
                        <td colspan="10" class="text-center">
                            No hay datos disponibles en la sección de {{ strtolower($page_title) }}.
                        </td>
                    </tr>
                @endforelse 
            </tbody>
        </table>
        </div>
        @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->hasPages())
            <div class="pagination-container">
                {{ $data->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    #formModal #modalTitle {
        color: #0d2240;
        font-weight: 700;
    }
</style>
<div id="formModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="modalTitle"></h2>
        
        <form id="modalForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div id="modalBody">
                
            </div>
            <button type="submit" class="btn-primary">Guardar</button>
        </form>
    </div>
</div>


<script>
   (function initAjustesModalScript() {
        
        const modal = document.getElementById('formModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const modalForm = document.getElementById('modalForm');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModal = document.querySelector('.close-modal');
        
        
        function executeScriptsIn(container) {
            const scripts = container.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                if (oldScript.textContent) newScript.textContent = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }
        
        if (!modal || !openModalBtn) return;
        
        const seccion = @json($seccion);
        const singularName = @json($singular_title);
        const baseUrl = @json(url('ajustes'));
        
        function clearMethodInput() {
            const oldMethodInput = modalForm.querySelector('input[name="_method"]');
            if (oldMethodInput) oldMethodInput.remove();
        }

      
       modalForm.addEventListener('submit', async function(e) {
            e.preventDefault();

           
            modalForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            modalForm.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

            const formData = new FormData(modalForm);
            const url = modalForm.action;

            try {
                const response = await fetch(url, {
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
                    return; 
                }

                
                if (response.status === 422) {
                    
                    const data = await response.json();
                    console.log('Errores recibidos:', data.errors);

                    
                    if (data.errors.modules_enabled) {
                        
                        const modulesWrapper = document.getElementById('admin-modules-wrapper');
                        
                        
                        if (modulesWrapper) {
                        
                            const oldError = modulesWrapper.querySelector('.invalid-feedback');
                            if (oldError) oldError.remove();

                        
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback';
                            errorDiv.style.display = 'block';
                            errorDiv.style.color = '#dc3545';
                            errorDiv.style.marginTop = '10px';
                            errorDiv.innerHTML = `<strong>${data.errors.modules_enabled[0]}</strong>`;
                            modulesWrapper.appendChild(errorDiv);
                        } else {
                         
                            alert(data.errors.modules_enabled[0]);
                        }
                    }
                    
                    Object.keys(data.errors).forEach(field => {
                        const input = modalForm.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const errorSpan = document.createElement('span');
                            errorSpan.classList.add('invalid-feedback');
                            errorSpan.style.display = 'block';
                            errorSpan.style.color = '#dc3545';
                            errorSpan.style.fontSize = '0.85em';
                            errorSpan.innerHTML = `<strong>${data.errors[field][0]}</strong>`;
                            input.parentElement.appendChild(errorSpan);
                        }
                    });
                } else {
                   
                    alert('Ocurrió un error inesperado en el servidor.');
                }

            } catch (error) {
                console.error('Error de red:', error);
                alert('Error de conexión. Intente de nuevo.');
            }
        });
        
        
    
        openModalBtn.addEventListener('click', async function () {
            modalTitle.textContent = `Agregar ${singularName}`;
            clearMethodInput();
            modalForm.action = `${baseUrl}/${seccion}`; 
            
            try {
                const response = await fetch(`${baseUrl}/${seccion}/create-form`);
                if (!response.ok) throw new Error('Error al cargar el formulario');
                modalBody.innerHTML = await response.text();
                
                modalBody.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
                const submitBtn = modalForm.querySelector('button[type="submit"]');
                if(submitBtn) submitBtn.style.display = 'block';

                executeScriptsIn(modalBody); 
                modal.style.display = 'block';
            } catch (error) {
                console.error(error);
                alert('No se pudo cargar el formulario.');
            }
        });

      
        document.addEventListener('click', async function (e) {
            const btn = e.target.closest('.btn-edit, .btn-view');
            if (!btn) return;
            e.preventDefault();
            const itemId = btn.dataset.id;
            const isViewButton = btn.classList.contains('btn-view');
            modalTitle.textContent = isViewButton
                ? `Ver ${singularName} #${itemId}`
                : `Editar ${singularName} #${itemId}`;
            modalForm.action = `${baseUrl}/${seccion}/${itemId}`;
            clearMethodInput();
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            modalForm.prepend(methodInput);
            try {
                const response = await fetch(`${baseUrl}/${seccion}/${itemId}/edit-form`);
                if (!response.ok) throw new Error('Error al cargar formulario');
                modalBody.innerHTML = await response.text();
                if (isViewButton) {
                    modalBody.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
                    const submitBtn = modalForm.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.style.display = 'none';
                } else {
                    modalBody.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
                    const submitBtn = modalForm.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.style.display = 'block';
                }
                executeScriptsIn(modalBody);
                modal.style.display = 'block';
            } catch (error) {
                console.error(error);
                alert('No se pudo cargar el formulario.');
            }
        });

       
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
            modalBody.innerHTML = ''; 
        });

        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                modal.style.display = 'none';
                modalBody.innerHTML = ''; 
            }
        });
    })();

    (function searchFormAjax() {
        const seccion = @json($seccion);
        if (seccion !== 'users') return;
        const form = document.getElementById('searchForm');
        const input = document.getElementById('searchInput');
        const tbody = document.getElementById('ajustes-table-body');
        if (!form || !input || !tbody) return;

        function fetchTable() {
            const url = form.action + (input.value.trim() ? '?' + new URLSearchParams({ search: input.value.trim() }) : '');
            fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                tbody.innerHTML = html;
            })
            .catch(function(err) {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="10" class="text-center">Error al buscar. Recarga la página.</td></tr>';
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchTable();
        });

        let debounceTimer;
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchTable, 400);
        });
    })();
</script>
@endsection