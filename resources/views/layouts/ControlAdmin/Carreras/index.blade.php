@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css','resources/js/app.js'])

@section('content')
<div class ="container">
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1rem; padding: 10px 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24;">{{ session('error') }}</div>
    @endif
    <!-- Header -->
    <div class ="content-header">
        <div class="content-title">
            <h3>Carreras</h3>
        </div>
        <div class="option-carrer">
            @if(Auth::user()->hasAnyRole(['master']))
                <button type="button" id="openCreateClasificacionBtn">+ Agregar clasificación</button>
                <button type="button" id="openCreateCareerBtn">+ Agregar carrera</button>
            @endif
        </div>
        @include('layouts.ControlAdmin.Carreras.create')
        @include('layouts.ControlAdmin.Carreras.classification_modal')
    </div>
    <!-- Grid de Carreras -->
    <div class="carrers-container">
        @forelse ($careers as $carrera)
            <div class="carrer-card" data-reticula-url="{{ route('control.careers.reticula', $carrera->id) }}" role="button" tabindex="0">
                <div class="carrer-name">
                    <a href="{{ route('control.careers.reticula', $carrera->id) }}" class="carrer-card-link"><h3 class="carrer-title">{{ $carrera->name }}</h3></a>
                </div>
                <div class="line-separator"></div>
                <div class="carrer-card-options">
                    <div class="carrer-info">
                        <span>RVOE: Acuerdo número:</span>
                        <span>{{ str_replace('-', '/', $carrera->official_id) }}</span>
                    </div>
                    <div class="carrer-btn-section">
                        {{-- Visualizar carrera --}}
                        <button type="button" class="btn-view" data-view-career-id="{{ $carrera->id }}" title="Ver información">
                            <svg width="20" height="20" viewBox="0 0 29 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M14.4987 0.625C10.4307 0.625 7.17322 2.49375 4.80187 4.71797C2.44562 6.92188 0.869748 9.5625 0.124609 11.3754C-0.0415365 11.7766 -0.0415365 12.2234 0.124609 12.6246C0.869748 14.4375 2.44562 17.0781 4.80187 19.282C7.17322 21.5063 10.4307 23.375 14.4987 23.375C18.5668 23.375 21.8243 21.5063 24.1956 19.282C26.5519 17.073 28.1277 14.4375 28.8779 12.6246C29.0441 12.2234 29.0441 11.7766 28.8779 11.3754C28.1277 9.5625 26.5519 6.92188 24.1956 4.71797C21.8243 2.49375 18.5668 0.625 14.4987 0.625ZM7.24874 12C7.24874 10.0606 8.01258 8.20064 9.37222 6.82928C10.7319 5.45792 12.5759 4.6875 14.4987 4.6875C16.4216 4.6875 18.2656 5.45792 19.6253 6.82928C20.9849 8.20064 21.7487 10.0606 21.7487 12C21.7487 13.9394 20.9849 15.7994 19.6253 17.1707C18.2656 18.5421 16.4216 19.3125 14.4987 19.3125C12.5759 19.3125 10.7319 18.5421 9.37222 17.1707C8.01258 15.7994 7.24874 13.9394 7.24874 12ZM14.4987 8.75C14.4987 10.5426 13.0538 12 11.2765 12C10.9191 12 10.5767 11.9391 10.2545 11.8324C9.97756 11.741 9.65534 11.9137 9.66541 12.2082C9.68051 12.5586 9.73086 12.909 9.82652 13.2594C10.5163 15.8594 13.1696 17.4031 15.7474 16.7074C18.3251 16.0117 19.8557 13.3355 19.1659 10.7355C18.6071 8.62813 16.7593 7.21133 14.7052 7.125C14.4132 7.11484 14.242 7.43477 14.3326 7.71914C14.4383 8.04414 14.4987 8.38945 14.4987 8.75Z"
                                    fill="#BC8A55" />
                            </svg>
                        </button>
                        @include('layouts.ControlAdmin.Carreras.show', ['career' => $carrera])
                        {{-- Editar Carrera --}}
                        @if(Auth::user()->hasAnyRole(['master']))
                            <button type="button" id="openEditModalBtn_{{ $carrera->id }}" class="btn-edit" data-career-id="{{ $carrera->id }}">
                            <svg width="20" height="20" viewBox="0 0 24 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                            d="M22.3364 0.648281C21.2991 -0.216094 19.6225 -0.216094 18.5852 0.648281L17.1596 1.83235L21.7964 5.69638L23.2221 4.50836C24.2593 3.64399 24.2593 2.24678 23.2221 1.38241L22.3364 0.648281ZM8.16538 9.33149C7.87646 9.57225 7.65386 9.86827 7.52598 10.1959L6.12403 13.7007C5.98668 14.0402 6.09561 14.4151 6.39874 14.6717C6.70186 14.9282 7.15181 15.015 7.56387 14.9006L11.7697 13.7323C12.1581 13.6257 12.5133 13.4402 12.8069 13.1995L20.7308 6.59233L16.0892 2.72436L8.16538 9.33149ZM4.54685 2.31783C2.03661 2.31783 0 4.015 0 6.10686V16.211C0 18.3028 2.03661 20 4.54685 20H16.6718C19.182 20 21.2186 18.3028 21.2186 16.211V12.4219C21.2186 11.7233 20.5413 11.1589 19.703 11.1589C18.8647 11.1589 18.1874 11.7233 18.1874 12.4219V16.211C18.1874 16.9096 17.5101 17.474 16.6718 17.474H4.54685C3.70852 17.474 3.03123 16.9096 3.03123 16.211V6.10686C3.03123 5.40826 3.70852 4.84385 4.54685 4.84385H9.09369C9.93202 4.84385 10.6093 4.27944 10.6093 3.58084C10.6093 2.88223 9.93202 2.31783 9.09369 2.31783H4.54685Z"
                            fill="black" />
                        </svg>
                        </button>
                        @include('layouts.ControlAdmin.Carreras.edit', ['career' => $carrera])
                        @endif
                        {{-- Eliminar --}}
                        <form action="{{ route('control.careers.destroy', $carrera->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta carrera?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn-delete">
                                <svg width="20" height="20" viewBox="0 0 27 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                    d="M27 3C27 1.34531 25.6547 0 24 0H9.62344C8.82656 0 8.0625 0.314062 7.5 0.876562L0.440625 7.94062C0.159375 8.22187 0 8.60156 0 9C0 9.39844 0.159375 9.77813 0.440625 10.0594L7.5 17.1234C8.0625 17.6859 8.82656 18 9.62344 18H24C25.6547 18 27 16.6547 27 15V3ZM12.7031 5.20312C13.1438 4.7625 13.8562 4.7625 14.2922 5.20312L16.4953 7.40625L18.6984 5.20312C19.1391 4.7625 19.8516 4.7625 20.2875 5.20312C20.7234 5.64375 20.7281 6.35625 20.2875 6.79219L18.0844 8.99531L20.2875 11.1984C20.7281 11.6391 20.7281 12.3516 20.2875 12.7875C19.8469 13.2234 19.1344 13.2281 18.6984 12.7875L16.4953 10.5844L14.2922 12.7875C13.8516 13.2281 13.1391 13.2281 12.7031 12.7875C12.2672 12.3469 12.2625 11.6344 12.7031 11.1984L14.9062 8.99531L12.7031 6.79219C12.2625 6.35156 12.2625 5.63906 12.7031 5.20312Z"
                                    fill="#D30303" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-carrers">
                <div class="emty-text">
                    <h2>Sin registro de carreras</h2>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal Operación exitosa (después de eliminar/actualizar carrera) --}}
<div id="careerSuccessModal" class="modal-overlay career-success-modal" style="display: none;">
    <div class="modal-container career-success-modal__box">
        <div class="career-success-modal__icon-wrap">
            <svg class="career-success-modal__check" width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#a8d5a2" stroke-width="2"/>
                <path d="M8 12l3 3 5-6" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="career-success-modal__title">Operación exitosa</h3>
        <p class="career-success-modal__message" id="careerSuccessModalMessage">Carrera eliminada exitosamente.</p>
        <button type="button" class="career-success-modal__ok" id="careerSuccessModalOk">OK</button>
    </div>
</div>
<style>
.career-success-modal { align-items: center; justify-content: center; }
.career-success-modal__box { max-width: 420px; max-height: 280px; text-align: center; padding: 16px 24px 20px; border: 1px solid #ddd; }
.career-success-modal__icon-wrap { margin-bottom: 16px; }
.career-success-modal__check { display: inline-block; width: 72px; height: 72px; }
.career-success-modal__title { font-size: 1.15rem; font-weight: 700; color: #333; margin: 0 0 6px 0; }
.career-success-modal__message { font-size: 0.9rem; color: #666; margin: 0 0 14px 0; }
.career-success-modal__ok {
    background: #223F70;
    color: #fff;
    border: none;
    width: 80px;
    padding: 8px 28px;
    border-radius: 6px;
    font-size: 0.95rem;
    cursor: pointer;
    display: block;
    margin: 0 auto;
}
.career-success-modal__ok:hover { background: #15213F; }
</style>

@if(session('success') && request('modal') === 'success')
<script>window.careerSuccessMessage = @json(session('success'));</script>
@endif

{{-- Script JS para abrir/cerrar (el mismo de antes) --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- 0. MODAL "Operación exitosa" (después de eliminar/actualizar carrera) ---
            const successModal = document.getElementById('careerSuccessModal');
            const successModalMessage = document.getElementById('careerSuccessModalMessage');
            const successModalOk = document.getElementById('careerSuccessModalOk');

            // Detectar si venimos por navegación atrás/adelante del navegador
            let isBackOrForward = false;
            if (performance && typeof performance.getEntriesByType === 'function') {
                const navEntries = performance.getEntriesByType('navigation');
                if (navEntries && navEntries[0] && navEntries[0].type === 'back_forward') {
                    isBackOrForward = true;
                }
            }

            // Solo mostrar el modal automáticamente cuando NO sea navegación atrás/adelante
            if (!isBackOrForward && window.careerSuccessMessage && successModal && successModalMessage) {
                successModalMessage.textContent = window.careerSuccessMessage;
                successModal.style.display = 'flex';
            }
            if (successModalOk && successModal) {
                successModalOk.addEventListener('click', function() {
                    successModal.style.display = 'none';
                });
            }
            
            // --- 1. LÓGICA DEL MODAL DE CREACIÓN (Singular) ---
            
            // Identificadores únicos para el modal de creación
            const createModal = document.getElementById('createCareerModal');
            const openCreateBtn = document.getElementById('openCreateCareerBtn'); 
            
            // Función para abrir el modal de Creación
            if (openCreateBtn) {
                openCreateBtn.addEventListener('click', () => {
                    if (createModal) {
                        createModal.style.display = 'flex';
                    }
                });
            }

            // Detectar errores de Creación (Asumiendo que no hay ID de carrera en old input, 
            // solo el campo 'name' u otro campo requerido)
            const hasCreateErrors = @json($errors->hasAny() && old('name') && !request()->routeIs('careers.update')); 

            if (createModal && hasCreateErrors) {
                 createModal.style.display = 'flex'; 
            }

            const classificationModal = document.getElementById('createClassificationModal');
            const hasClassificationErrors = @json($errors->has('classification_name'));
            if (classificationModal && hasClassificationErrors) {
                classificationModal.style.display = 'flex';
            }
            
            // ----------------------------------------------------
            
            // --- 2. LÓGICA MODAL VISUALIZAR (Ver información) ---
            document.querySelectorAll('.btn-view[data-view-career-id]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-view-career-id');
                    const modal = document.getElementById('viewCareerModal_' + id);
                    if (modal) modal.style.display = 'flex';
                });
            });

            // --- Clic en tarjeta: ir a Reticula escolar (no si se hace clic en botones) ---
            document.querySelectorAll('.carrer-card[data-reticula-url]').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('.carrer-btn-section') || e.target.closest('form')) return;
                    const url = this.getAttribute('data-reticula-url');
                    if (url) window.location.href = url;
                });
            });

            // --- 3. LÓGICA DEL MODAL DE EDICIÓN (solo botones con data-career-id) ---
            document.querySelectorAll('.btn-edit[data-career-id]').forEach(button => {
                button.addEventListener('click', function() {
                    const careerId = this.getAttribute('data-career-id');
                    const modal = document.getElementById('editCareerModal_' + careerId);
                    if (modal) modal.style.display = 'flex';
                });
            });

            // Detectar errores de Edición (Asumiendo que un modal con error tiene .alert-danger)
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                const errorElement = modal.querySelector('.alert-danger'); 
                
                // Si encontramos un error en cualquier modal y no es el modal de creación
                if (errorElement && modal.id.startsWith('editCareerModal_')) {
                     modal.style.display = 'flex';
                }
            });


            // ----------------------------------------------------
            
            // --- 4. LÓGICA UNIFICADA DE CIERRE (para todos los modales) ---
            
            // Cierre con el botón 'X' (.close-custom) o 'Cancelar' (.btn-secondary) o 'Cerrar'
            document.querySelectorAll('.close-custom, .btn-secondary, .btn-close-view').forEach(btn => {
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
