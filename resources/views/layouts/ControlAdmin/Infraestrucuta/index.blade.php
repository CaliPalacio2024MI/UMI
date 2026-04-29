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
        @include('layouts.ControlAdmin.Infraestrucuta.components.create', ['carreras' => $carreras ?? collect()])
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
        <div class="Table-view Table-view--aulas" style="margin-top: 1rem;">
            <table class="tabla-base tabla-rayas tabla-bordes tabla-aulas" style="width: 100%;">
                <thead class="encabezado-tabla">
                    <tr>
                        <th>Nombre del aula</th>
                        <th>Carrera</th>
                        <th>Materia</th>
                        <th>Clasificación</th>
                        @if(Auth::user()->hasAnyRole(['master']))
                            <th class="aulas-tabla-acciones">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="cuerpo-tabla">
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $item->nombre_aula ?? '—' }}</td>
                            <td>{{ $item->career->name ?? '—' }}</td>
                            <td>{{ $item->tipo_materia ? $item->tipo_materia : '—' }}</td>
                            <td>{{ $item->career?->classification?->name ?? '—' }}</td>
                            @if(Auth::user()->hasAnyRole(['master']))
                                <td class="aulas-tabla-acciones">
                                    <div class="aulas-acciones">
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
                    @include('layouts.ControlAdmin.Infraestrucuta.components.edit_form', ['facility' => $item, 'carreras' => $carreras ?? collect()])
                </template>
            @endforeach
        @endif
    @endif
</div>
@push('scripts')
@include('layouts.ControlAdmin.Infraestrucuta.components._aulas_materias_select_script')
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
            } else if (typeof window.umiAulasFillMateriaSelect === 'function') {
                const carSel = modalBodyContent.querySelector('#createFacilityForm #career_id');
                const matSel = modalBodyContent.querySelector('#createFacilityForm #tipo_materia');
                if (carSel && matSel) {
                    window.umiAulasFillMateriaSelect(carSel.value || '', matSel, null);
                }
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
            } else if (typeof window.umiAulasFillMateriaSelect === 'function') {
                const ef = modalBodyContent.querySelector('#editFacilityForm');
                if (ef) {
                    const cs = ef.querySelector('#edit_career_id');
                    const ms = ef.querySelector('#edit_tipo_materia');
                    if (cs && ms) {
                        window.umiAulasFillMateriaSelect(cs.value || '', ms, ms.getAttribute('data-preselected') || '');
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
            modalPanel.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                hideModal();
            }
        });

        const aulasSearchInput = document.getElementById('aulasSearchNombre');
        const tbody = document.querySelector('.tabla-base.tabla-aulas .cuerpo-tabla');
        if (aulasSearchInput && tbody) {
            const applyAulasSearch = () => {
                const search = (aulasSearchInput.value || '').trim().toLowerCase();
                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.forEach((tr) => {
                    const nombreAula = (tr.querySelector('td:nth-child(1)')?.textContent || '').trim().toLowerCase();
                    const carrera = (tr.querySelector('td:nth-child(2)')?.textContent || '').trim().toLowerCase();
                    const materia = (tr.querySelector('td:nth-child(3)')?.textContent || '').trim().toLowerCase();
                    const clasificacion = (tr.querySelector('td:nth-child(4)')?.textContent || '').trim().toLowerCase();
                    const textoBusqueda = `${nombreAula} ${carrera} ${materia} ${clasificacion}`;
                    tr.style.display = (!search || textoBusqueda.includes(search)) ? '' : 'none';
                });
            };
            aulasSearchInput.addEventListener('input', applyAulasSearch);
        }
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
