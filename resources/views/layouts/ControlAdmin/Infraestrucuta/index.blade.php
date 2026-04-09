@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@section('content')
<div class ="container">
    <!-- Header -->
    <div class ="content-header">
        <div class="content-title">
            <h3>Aulas</h3>
        </div>
        <div class="header-option">
            @if(Auth::user()->hasAnyRole(['master']))
                <button type="button" id="aulasOpenFacilityModalBtn" class="mi-boton">+Agregar Aula</button>
            @endif
        </div>
    </div>

    {{-- Modal fuera del .content-header (flex) para que fixed/overlay no interfiera con clics al cerrar --}}
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
        @include('layouts.ControlAdmin.Infraestrucuta.components.create')
    </template>
    @endif
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem; padding: 10px 16px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">{{ session('success') }}</div>
    @endif
    <!-- Aulas -->
    @if($data->isEmpty())
        <div class="aulas-empty-state" role="status">
            <p>No hay aulas</p>
        </div>
    @else
        <div class="Table-view Table-view--aulas" style="margin-top: 1rem;">
            <table class="tabla-base tabla-rayas tabla-bordes tabla-aulas" style="width: 100%;">
                <thead class="encabezado-tabla">
                    <tr>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th>Sección</th>
                        <th>Capacidad</th>
                        @if(Auth::user()->hasAnyRole(['master']))
                            <th class="aulas-tabla-acciones">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="cuerpo-tabla">
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $item->numero_aula }}</td>
                            <td>{{ $item->tipo }}</td>
                            <td>{{ $item->seccion }}</td>
                            <td>{{ $item->capacidad ?? '—' }}</td>
                            @if(Auth::user()->hasAnyRole(['master']))
                                <td class="aulas-tabla-acciones">
                                    <div class="aulas-acciones">
                                        <button type="button" class="aulas-accion-icon aulas-open-show-modal" title="Ver" aria-label="Ver detalle de aula" data-aulas-show-template="aulas-show-detail-template-{{ $item->id }}" data-modal-title="Aula {{ $item->numero_aula }}">
                                            <img src="{{ asset('images/icons/eye-solid-full-gold.svg') }}" alt="" width="22" height="22" loading="lazy">
                                        </button>
                                        <button type="button" class="aulas-accion-icon aulas-open-edit-modal" title="Editar" aria-label="Editar aula" data-aulas-edit-template="aulas-edit-form-template-{{ $item->id }}">
                                            <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="" width="22" height="22" loading="lazy">
                                        </button>
                                        <form action="{{ route('control.facilities.destroy', $item) }}" method="POST" class="js-aula-delete-form" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete" title="Eliminar" aria-label="Eliminar aula" style="vertical-align: middle;">
                                                <img src="{{ asset('images/icons/Vector.svg') }}" alt="" width="20" height="16" loading="lazy">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(Auth::user()->hasAnyRole(['master']))
            @foreach ($data as $item)
                <template id="aulas-edit-form-template-{{ $item->id }}">
                    @include('layouts.ControlAdmin.Infraestrucuta.components.edit_form', ['facility' => $item])
                </template>
                <template id="aulas-show-detail-template-{{ $item->id }}">
                    @include('layouts.ControlAdmin.Infraestrucuta.components.show_detail', ['facility' => $item])
                </template>
            @endforeach
        @endif
    @endif
</div>
@push('scripts')
<script>
(function () {
    /* Navegación SPA: el HTML de Aulas se inyecta en #main-content y este script se ejecuta entonces;
       DOMContentLoaded ya ocurrió, por eso inicializamos de inmediato si el modal existe. */
    function initAulasFacilitiesPage() {
        const modal = document.getElementById('createFacilityModal');
        const openBtn = document.getElementById('aulasOpenFacilityModalBtn');
        const closeModalBtn = document.getElementById('aulasCloseFacilityModalBtn');
        const modalBodyContent = document.getElementById('modalBodyContent');
        const modalPanel = modal ? modal.querySelector('.modal-content-container') : null;
        const modalTitleEl = document.getElementById('createFacilityModalLabel');
        if (!modal || !modalBodyContent) {
            return;
        }

        const MODAL_TITLE_CREATE = 'Agregar Nueva Aula';
        const MODAL_TITLE_EDIT = 'Editar aula';
        const MODAL_TITLE_SHOW_FALLBACK = 'Detalle de la aula';

        const hideModal = () => {
            if (modal) {
                modal.classList.remove('is-visible');
                modal.setAttribute('aria-hidden', 'true');
            }
            if (modalBodyContent) {
                modalBodyContent.innerHTML = '';
            }
            if (modalTitleEl) {
                modalTitleEl.textContent = MODAL_TITLE_CREATE;
            }
        };

        window.aulasHideFacilityModal = hideModal;
        window.hideModal = hideModal;

        const injectCreateFormFromTemplate = () => {
            if (!modalBodyContent) {
                return false;
            }
            const tpl = document.getElementById('aulasCreateFormTemplate');
            if (!tpl || !tpl.content) {
                return false;
            }
            modalBodyContent.innerHTML = '';
            modalBodyContent.appendChild(tpl.content.cloneNode(true));
            return true;
        };

        const injectBodyFromTemplateId = (templateId) => {
            if (!modalBodyContent || !templateId) {
                return false;
            }
            const tpl = document.getElementById(templateId);
            if (!tpl || !tpl.content) {
                return false;
            }
            modalBodyContent.innerHTML = '';
            modalBodyContent.appendChild(tpl.content.cloneNode(true));
            return true;
        };

        const showModal = () => {
            if (!modal || !modalBodyContent) {
                return;
            }
            if (modalTitleEl) {
                modalTitleEl.textContent = MODAL_TITLE_CREATE;
            }
            if (!injectCreateFormFromTemplate()) {
                modalBodyContent.innerHTML = '<p style="color:red;">No se pudo cargar el formulario.</p>';
            }
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        };

        const showEditModal = (templateId) => {
            if (!modal || !modalBodyContent || !templateId) {
                return;
            }
            if (modalTitleEl) {
                modalTitleEl.textContent = MODAL_TITLE_EDIT;
            }
            if (!injectBodyFromTemplateId(templateId)) {
                modalBodyContent.innerHTML = '<p style="color:red;">No se pudo abrir el formulario de edición.</p>';
            }
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        };

        const showDetailModal = (templateId, titleText) => {
            if (!modal || !modalBodyContent || !templateId) {
                return;
            }
            if (modalTitleEl) {
                modalTitleEl.textContent = titleText && titleText.trim() ? titleText.trim() : MODAL_TITLE_SHOW_FALLBACK;
            }
            if (!injectBodyFromTemplateId(templateId)) {
                modalBodyContent.innerHTML = '<p style="color:red;">No se pudo mostrar el detalle.</p>';
            }
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        };

        document.querySelectorAll('.aulas-open-show-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const tid = btn.getAttribute('data-aulas-show-template');
                const title = btn.getAttribute('data-modal-title');
                showDetailModal(tid, title);
            });
        });

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
            modalPanel.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                hideModal();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAulasFacilitiesPage);
    } else {
        initAulasFacilitiesPage();
    }
})();

(function () {
    if (window.__umiAulasFacilitiesSubmitBound) {
        return;
    }
    window.__umiAulasFacilitiesSubmitBound = true;

    function aulasShowCareerSuccessModalThenReload(message) {
        var successModal = document.getElementById('careerSuccessModal');
        var successModalMessage = document.getElementById('careerSuccessModalMessage');
        if (successModal && successModalMessage) {
            successModalMessage.textContent = message || 'Operación completada.';
            window.afterCareerSuccessModalOk = function () {
                window.location.reload();
            };
            successModal.style.display = 'flex';
        } else {
            window.location.reload();
        }
    }

    document.addEventListener('submit', function (event) {
        
        // 1. Verificamos si el formulario que se está enviando es el nuestro
        if (event.target && event.target.id === 'createFacilityForm') {
            
            event.preventDefault(); // ¡Detenemos el envío normal que recarga la página!
            
            const form = event.target;
            const formData = new FormData(form);
            const messagesDiv = document.getElementById('formMessages');

            if (messagesDiv) {
                messagesDiv.innerHTML = '';
            }

            axios.post("{{ route('control.facilities.store') }}", formData, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (response) {
                    if (typeof window.aulasHideFacilityModal === 'function') {
                        window.aulasHideFacilityModal();
                    }
                    var msg = (response.data && response.data.message) ? response.data.message : 'Aula creada exitosamente.';
                    aulasShowCareerSuccessModalThenReload(msg);
                })
                .catch(function (error) {
                    if (messagesDiv) {
                        messagesDiv.innerHTML = '<p style="color: red;">Error al guardar. Verifica los campos.</p>';
                    }
                    console.error(error.response);

                    if (messagesDiv && error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                        let errorsHtml = '<ul>';
                        Object.values(error.response.data.errors).forEach(function (messages) {
                            messages.forEach(function (message) {
                                errorsHtml += '<li style="color: red;">' + message + '</li>';
                            });
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

            if (messagesDiv) {
                messagesDiv.innerHTML = '';
            }

            axios.post(form.action, formData, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (response) {
                    if (typeof window.aulasHideFacilityModal === 'function') {
                        window.aulasHideFacilityModal();
                    }
                    var msg = (response.data && response.data.message) ? response.data.message : 'Aula actualizada correctamente.';
                    aulasShowCareerSuccessModalThenReload(msg);
                })
                .catch(function (error) {
                    if (messagesDiv) {
                        messagesDiv.innerHTML = '<p style="color: red;">Error al guardar. Verifica los campos.</p>';
                    }
                    console.error(error.response);

                    if (messagesDiv && error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                        let errorsHtml = '<ul>';
                        Object.values(error.response.data.errors).forEach(function (messages) {
                            messages.forEach(function (message) {
                                errorsHtml += '<li style="color: red;">' + message + '</li>';
                            });
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
