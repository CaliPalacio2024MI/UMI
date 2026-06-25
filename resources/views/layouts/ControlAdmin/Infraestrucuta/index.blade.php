@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@section('content')
<div class="container">
    <!-- Header -->
    <div class="content-header">
        <div class="content-title">
            <h5>Aulas</h5>
        </div>
        <div class="header-option">
            @if(Auth::user()->hasAnyRole(['master']))
                <button type="button" id="aulasOpenFacilityModalBtn" class="mi-boton">+Agregar Aula</button>
            @endif
        </div>
    </div>

    @if(Auth::user()->hasAnyRole(['master']))
    <div id="createFacilityModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-container" role="dialog" aria-labelledby="createFacilityModalLabel">
            <div class="modal-header-custom">
                <h5 id="createFacilityModalLabel">Agregar Nueva Aula</h5>
                <button type="button" id="aulasCloseFacilityModalBtn" class="close-custom" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body-custom" id="modalBodyContent"></div>
        </div>
    </div>
    <template id="aulasCreateFormTemplate">
        @include('layouts.ControlAdmin.Infraestrucuta.components.create', ['carreras' => $carreras ?? collect(), 'clasificaciones' => $clasificaciones ?? collect()])
    </template>
    @endif

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem; padding: 10px 16px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">{{ session('success') }}</div>
    @endif

    @if(!$data->isEmpty())
        <div class="list-header-toolbar">
            <div class="toolbar__section toolbar__section--left">
                <div class="toolbar__search toolbar__search--aulas">
                    <img src="{{ asset('images/icons/magnifying-glass-svgrepo-com.svg') }}" alt="" class="toolbar__search-icon" aria-hidden="true">
                    <input type="text" id="aulasSearchNombre" placeholder="Buscar por..." autocomplete="off">
                </div>
            </div>
        </div>
    @endif

   

    <!-- Aulas -->
    @if($data->isEmpty())
        <div class="aulas-empty-state" role="status">
            <p>No hay aulas</p>
        </div>
    @else
        <div class="aulas-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            @foreach ($data as $item)
                <div class="aula-card" data-search-text="{{ strtolower($item->nombre_aula . ' ' . $item->careers->pluck('name')->join(' ') . ' ' . $item->materias->pluck('nombre')->join(' ') . ' ' . ($item->classification->name ?? '')) }}" style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; transition: box-shadow 0.2s;">
                    <!-- Header de la tarjeta -->
                    <div style="background: #2f4b7c; color: white; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600;">{{ $item->nombre_aula ?? '—' }}</h4>
                        @if($item->classification)
                            <span style="background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 500;">{{ $item->classification->name }}</span>
                        @endif
                    </div>

                    <!-- Cuerpo de la tarjeta -->
                    <div style="padding: 16px 20px;">
                        <!-- Contadores -->
                        <div style="display: flex; gap: 12px; margin-bottom: 14px;">
                            <div style="background: #f0f4ff; padding: 8px 14px; border-radius: 8px; flex: 1; text-align: center;">
                                <div style="font-size: 1.25rem; font-weight: 700; color: #2f4b7c;">{{ $item->careers->count() }}</div>
                                <div style="font-size: 0.7rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Carreras</div>
                            </div>
                            <div style="background: #fdf6ee; padding: 8px 14px; border-radius: 8px; flex: 1; text-align: center;">
                                <div style="font-size: 1.25rem; font-weight: 700; color: #b87a2b;">{{ $item->materias->count() }}</div>
                                <div style="font-size: 0.7rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Materias</div>
                            </div>
                        </div>

                        <!-- Carreras -->
                        @if($item->careers->isNotEmpty())
                            <div style="margin-bottom: 10px;">
                                <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Carreras</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($item->careers as $career)
                                        <span style="background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 4px; font-size: 0.78rem;">{{ $career->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Materias -->
                        @if($item->materias->isNotEmpty())
                            <div style="margin-bottom: 10px;">
                                <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Materias</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($item->materias as $materia)
                                        <span style="background: #fef9f3; color: #92610e; padding: 3px 8px; border-radius: 4px; font-size: 0.78rem;">{{ $materia->nombre }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Acciones -->
                    @if(Auth::user()->hasAnyRole(['master']))
                        <div style="border-top: 1px solid #f1f5f9; padding: 10px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                            <button type="button" class="aulas-accion-icon aulas-open-edit-modal" title="Editar" aria-label="Editar aula" data-aulas-edit-template="aulas-edit-form-template-{{ $item->id }}" style="background: none; border: none; cursor: pointer; padding: 4px;">
                                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="" width="22" height="22" loading="lazy">
                            </button>
                            <form action="{{ route('control.facilities.destroy', $item) }}" method="POST" class="js-aula-delete-form" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete" title="Eliminar" aria-label="Eliminar aula" style="background: none; border: none; cursor: pointer; padding: 4px; vertical-align: middle;">
                                    <img src="{{ asset('images/icons/Vector.svg') }}" alt="" width="20" height="16" loading="lazy">
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if(Auth::user()->hasAnyRole(['master']))
            @foreach ($data as $item)
                <template id="aulas-edit-form-template-{{ $item->id }}">
                    @include('layouts.ControlAdmin.Infraestrucuta.components.edit_form', ['facility' => $item, 'carreras' => $carreras ?? collect(), 'clasificaciones' => $clasificaciones ?? collect()])
                </template>
            @endforeach
        @endif
    @endif
</div>

@push('scripts')
@include('layouts.ControlAdmin.Infraestrucuta.components._aulas_materias_select_script')
<script>
(function () {
    function initAulasFacilitiesPage() {
        const modal = document.getElementById('createFacilityModal');
        const openBtn = document.getElementById('aulasOpenFacilityModalBtn');
        const closeModalBtn = document.getElementById('aulasCloseFacilityModalBtn');
        const modalBodyContent = document.getElementById('modalBodyContent');
        const modalPanel = modal ? modal.querySelector('.modal-content-container') : null;
        const modalTitleEl = document.getElementById('createFacilityModalLabel');
        if (!modal || !modalBodyContent) return;

        const MODAL_TITLE_CREATE = 'Agregar Nueva Aula';
        const MODAL_TITLE_EDIT = 'Editar aula';

        const hideModal = () => {
            if (modal) { modal.classList.remove('is-visible'); modal.setAttribute('aria-hidden', 'true'); }
            if (modalBodyContent) modalBodyContent.innerHTML = '';
            if (modalTitleEl) modalTitleEl.textContent = MODAL_TITLE_CREATE;
        };

        window.aulasHideFacilityModal = hideModal;
        window.hideModal = hideModal;

        const injectCreateFormFromTemplate = () => {
            if (!modalBodyContent) return false;
            const tpl = document.getElementById('aulasCreateFormTemplate');
            if (!tpl || !tpl.content) return false;
            modalBodyContent.innerHTML = '';
            modalBodyContent.appendChild(tpl.content.cloneNode(true));
            return true;
        };

        const injectBodyFromTemplateId = (templateId) => {
            if (!modalBodyContent || !templateId) return false;
            const tpl = document.getElementById(templateId);
            if (!tpl || !tpl.content) return false;
            modalBodyContent.innerHTML = '';
            modalBodyContent.appendChild(tpl.content.cloneNode(true));
            return true;
        };

        const showModal = () => {
            if (!modal || !modalBodyContent) return;
            if (modalTitleEl) modalTitleEl.textContent = MODAL_TITLE_CREATE;
            if (!injectCreateFormFromTemplate()) {
                modalBodyContent.innerHTML = '<p style="color:red;">No se pudo cargar el formulario.</p>';
            }
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        };

        const showEditModal = (templateId) => {
            if (!modal || !modalBodyContent || !templateId) return;
            if (modalTitleEl) modalTitleEl.textContent = MODAL_TITLE_EDIT;
            if (!injectBodyFromTemplateId(templateId)) {
                modalBodyContent.innerHTML = '<p style="color:red;">No se pudo abrir el formulario de edición.</p>';
            } else {
                modalBodyContent.querySelectorAll('script').forEach(function (oldScript) {
                    var newScript = document.createElement('script');
                    newScript.textContent = oldScript.textContent;
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
                var editForm = modalBodyContent.querySelector('#editFacilityForm');
                if (editForm && window.umiAulasFilterCarreras) {
                    var clasSel = editForm.querySelector('.aula-clasificacion-select');
                    if (clasSel && clasSel.value) {
                        window.umiAulasFilterCarreras(editForm, clasSel.value);
                    }
                }
            }
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        };

        document.querySelectorAll('.aulas-open-edit-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const tid = btn.getAttribute('data-aulas-edit-template');
                showEditModal(tid);
            });
        });

        if (openBtn) {
            openBtn.addEventListener('click', function (event) {
                event.preventDefault();
                showModal();
            });
        }

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', function (e) {
                e.preventDefault();
                hideModal();
            });
        }

        if (modalPanel) {
            modalPanel.addEventListener('click', function (e) { e.stopPropagation(); });
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) hideModal();
        });

        // Búsqueda en tarjetas
        const aulasSearchInput = document.getElementById('aulasSearchNombre');
        if (aulasSearchInput) {
            aulasSearchInput.addEventListener('input', function () {
                const search = (aulasSearchInput.value || '').trim().toLowerCase();
                document.querySelectorAll('.aula-card').forEach(function (card) {
                    const text = card.getAttribute('data-search-text') || '';
                    card.style.display = (!search || text.includes(search)) ? '' : 'none';
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAulasFacilitiesPage);
    } else {
        initAulasFacilitiesPage();
    }
})();

(function () {
    if (window.__umiAulasFacilitiesSubmitBound) return;
    window.__umiAulasFacilitiesSubmitBound = true;

    function aulasShowCareerSuccessModalThenReload(message) {
        var successModal = document.getElementById('careerSuccessModal');
        var successModalMessage = document.getElementById('careerSuccessModalMessage');
        if (successModal && successModalMessage) {
            successModalMessage.textContent = message || 'Operación completada.';
            window.afterCareerSuccessModalOk = function () { window.location.reload(); };
            successModal.style.display = 'flex';
        } else {
            window.location.reload();
        }
    }

    document.addEventListener('submit', function (event) {
        if (event.target && event.target.id === 'createFacilityForm') {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const messagesDiv = document.getElementById('formMessages');
            if (messagesDiv) messagesDiv.innerHTML = '';

            axios.post("{{ route('control.facilities.store') }}", formData, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function (response) {
                if (typeof window.aulasHideFacilityModal === 'function') window.aulasHideFacilityModal();
                var msg = (response.data && response.data.message) ? response.data.message : 'Aula creada exitosamente.';
                aulasShowCareerSuccessModalThenReload(msg);
            })
            .catch(function (error) {
                if (messagesDiv) messagesDiv.innerHTML = '<p style="color: red;">Error al guardar. Verifica los campos.</p>';
                console.error(error.response);
                if (messagesDiv && error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    let errorsHtml = '<ul>';
                    Object.values(error.response.data.errors).forEach(function (messages) {
                        messages.forEach(function (message) { errorsHtml += '<li style="color: red;">' + message + '</li>'; });
                    });
                    errorsHtml += '</ul>';
                    messagesDiv.innerHTML = errorsHtml;
                }
            });
        }

        else if (event.target && event.target.id === 'editFacilityForm') {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const messagesDiv = document.getElementById('formMessages');
            if (messagesDiv) messagesDiv.innerHTML = '';

            axios.post(form.action, formData, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function (response) {
                if (typeof window.aulasHideFacilityModal === 'function') window.aulasHideFacilityModal();
                var msg = (response.data && response.data.message) ? response.data.message : 'Aula actualizada correctamente.';
                aulasShowCareerSuccessModalThenReload(msg);
            })
            .catch(function (error) {
                if (messagesDiv) messagesDiv.innerHTML = '<p style="color: red;">Error al guardar. Verifica los campos.</p>';
                console.error(error.response);
                if (messagesDiv && error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    let errorsHtml = '<ul>';
                    Object.values(error.response.data.errors).forEach(function (messages) {
                        messages.forEach(function (message) { errorsHtml += '<li style="color: red;">' + message + '</li>'; });
                    });
                    errorsHtml += '</ul>';
                    messagesDiv.innerHTML = errorsHtml;
                }
            });
        }
    });
})();
</script>
@endpush
@endsection