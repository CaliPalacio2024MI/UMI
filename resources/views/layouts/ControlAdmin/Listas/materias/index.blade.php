@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class="container container--materias">
    <div class ="content-header">
        <div class="content-title">
            <h3>MATERIAS</h3>
        </div>
    </div>
    <div class="list-header-toolbar">
        <div class="toolbar__section toolbar__section--left">
            <div class="toolbar__search toolbar__search--materias">
                <img src="{{ asset('images/icons/magnifying-glass-svgrepo-com.svg') }}" alt="" class="toolbar__search-icon" aria-hidden="true">
                <input type="text" id="materiasSearchNombre" placeholder="Buscar por..." autocomplete="off">
            </div>
        </div>
        <div class="toolbar__section toolbar__section--right">
            <div class="toolbar__actions">
                @if(Auth::user()->hasAnyRole(['master']))
                    <button type="button" id="openCreateMateriaBtn" class="btn btn--primary">+ Agregar Materia</button>
                @endif
            </div>
            @include('layouts.ControlAdmin.Listas.materias.create')
        </div>
    </div>
    <div class="Table-view">
        <table class="tabla-base tabla-rayas tabla-bordes tabla-materias">
            <thead class="encabezado-tabla">
                <tr>
                    <th>Carrera</th>
                    <th>Materia</th>
                    <th>No. Créditos</th>
                    <th>Semestre</th>
                    <th>Modalidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="cuerpo-tabla">
                @foreach ($dataList as $registro)
                    <tr> {{-- ¡NOTA: Agregué la etiqueta <tr> faltante! --}}
                        <td>{{ $registro->career?->name ?? 'Sin datos'}}</td>
                        <td>{{ $registro->nombre ?? 'Sin datos'}}</td>
                        <td>{{ $registro->creditos ?? 'Sin datos'}}</td>
                        <td>{{ $registro->semestre ?? 'Sin datos'}}</td>
                        <td>{{ $registro->type ?? 'Sin datos'}}</td>
                        <td>
                            {{-- Botón VER --}}
                            <button type="button" class="data-action-btn data-btn-view" data-view-materia-id="{{ $registro->id }}"><img src="{{ asset('images/icons/eye-solid-full-gold.svg') }}" alt="" style="width:22px;height:22px" loading="lazy"></button>
                            @include('layouts.ControlAdmin.Listas.materias.show', ['registro' => $registro])
                            {{-- Botón EDITAR --}}
                            <button type="button" class="data-action-btn data-btn-edit" data-materia-id="{{ $registro->id }}"><img src="{{asset('images/icons/pen-to-square-solid-full.svg')}}" alt="" style="width:22px;height:22px" loading="lazy"></button>
                            @include('layouts.ControlAdmin.Listas.materias.edit', ['registro' => $registro, 'carreras' => $carreras])
                            {{-- Botón ELIMINAR --}}
                            <form class="js-materia-delete-form" action="{{ route('control.subjects.destroy', $registro) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="data-action-btn data-btn-delete" aria-label="Eliminar materia"><img src="{{asset('images/icons/Vector.svg')}}" alt="" style="width:22px;height:22px" loading="lazy"></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- 1. LÓGICA DEL MODAL DE CREACIÓN (Singular) ---
            
            // Identificadores únicos para el modal de creación
            const createModal = document.getElementById('createMateriaModal');
            const openCreateBtn = document.getElementById('openCreateMateriaBtn'); 
            
            // Función para abrir el modal de Creación
            if (openCreateBtn) {
                openCreateBtn.addEventListener('click', () => {
                    if (createModal) {
                        createModal.style.display = 'flex';
                    }
                });
            }

            // Detectar errores de Creación (Asumiendo que no hay ID en old input, solo el campo 'name')
            // CAMBIO 1: Se ajusta la verificación de la ruta a 'materias.update'
            const hasCreateErrors = @json($errors->hasAny() && old('nombre') !== null && !request()->routeIs('control.subjects.update')); 

            if (createModal && hasCreateErrors) {
                 createModal.style.display = 'flex'; 
            }
            
            // ----------------------------------------------------
            
            // --- 2. LÓGICA DEL MODAL DE EDICIÓN (Múltiple) ---
            
            // Selector de clase para TODOS los botones de edición
            const openEditButtons = document.querySelectorAll('.btn-edit');

            openEditButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Nota: Se ha cambiado a 'data-materia-id' para coincidir con la variable
                    const materiaId = this.getAttribute('data-materia-id'); 
                    // Abrir el modal específico de esta materia usando el ID único
                    const modal = document.getElementById('editMateriaModal_' + materiaId);
                    if (modal) {
                        modal.style.display = 'flex';
                    }
                });
            });

            // Detectar errores de Edición: reabrir el modal que falló (edit_materia_id en sesión)
            const editMateriaIdConError = @json(session('edit_materia_id'));
            if (editMateriaIdConError) {
                const modalEdit = document.getElementById('editMateriaModal_' + editMateriaIdConError);
                if (modalEdit) modalEdit.style.display = 'flex';
            }


            // ----------------------------------------------------
            
            // --- 3. LÓGICA UNIFICADA DE CIERRE (para todos los modales) ---
            
            // Cierre con el botón 'X' (.close-custom) o 'Cancelar' (.btn-secondary)
            document.querySelectorAll('.close-custom, .btn-secondary').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Buscar el contenedor de modal más cercano y cerrarlo
                    const modal = btn.closest('.modal-overlay');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Cierre al hacer clic fuera del modal (overlay) y con tecla ESC
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                // Cierre por clic en overlay
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Cierre con la tecla ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // Cierra cualquier modal que esté visible
                    document.querySelectorAll('.modal-overlay').forEach(modal => {
                         if (modal.style.display === 'flex') {
                             modal.style.display = 'none';
                         }
                    });
                }
            });
            
        });
    </script>
@endpush
@endsection
