@extends('layouts.app')

@section('title', 'Horarios - Control administrativo')

@vite(['resources/css/Cursos/courses.css', 'resources/js/app.js'])


@section('content')

@php
    $modoEdicion = $modoEdicion ?? false;
@endphp

<div class ="container">
    @if(!$modoEdicion)
    <div class="content-header" style="text-align: left; margin-bottom: 1rem; margin-left: 1.5rem;">
        <h5 class="content-title" style="font-size: 1.75rem; font-weight: 700; margin: 0;">HORARIOS</h5>
    </div>
    @endif
    <div class = "creator-container {{ $modoEdicion ? 'is-editing' : '' }}" id="creator_container">
        <div class = "schedule-lists">
            <div class="schedule-edit-header" id="schedule_edit_header" style="{{ $modoEdicion ? '' : 'display: none;' }}" aria-hidden="{{ $modoEdicion ? 'false' : 'true' }}">
                <span class="schedule-edit-header__title">Editar horario</span>
                <button type="button" class="schedule-edit-close" id="schedule_edit_close" title="Salir de edición" aria-label="Salir de edición">✕</button>
            </div>
            <form id="schedule_form" method="POST" action="{{ $modoEdicion ? route('control.schedules.update', $horario->id) : route('control.schedules.store') }}" data-store-url="{{ route('control.schedules.store') }}" data-index-url="{{ route('control.schedules.index') }}" data-aulas-url="{{ route('control.schedules.aulasDisponibles') }}" @if($modoEdicion && $horario->aula_id) data-initial-aula-id="{{ $horario->aula_id }}" @endif @if($modoEdicion && $horario->aula_id && $horario->aula) data-initial-aula-label="{{ e(\App\Support\AulaHorarioPresenter::selectOptionSoloSeccion($horario->aula)) }}" @endif @if($modoEdicion && $horario->franjas->isNotEmpty()) data-initial-franjas="{{ $horario->franjas->toJson() }}" @endif>
                @csrf
                @if ($modoEdicion)
                    @method('PUT') 
                @endif
                @error('franjas_json')
                    <div class="alert alert-warning">{{ $message }}</div>
                @enderror
                <div class = "schedule-list-select">
                    <label for="clasificacion_select">Clasificación:</label>
                    <select id="clasificacion_select" name="clasificacion_id" @if ($modoEdicion) disabled @endif>
                        <option value="">Seleccione una Clasificación</option>
                        @foreach ($carreras->filter(fn($c) => $c->classification)->unique('career_classification_id') as $carreraClasificada)
                            <option
                                value="{{ $carreraClasificada->career_classification_id }}"
                                @if ($modoEdicion && isset($horario) && $horario->carrera && $carreraClasificada->career_classification_id == $horario->carrera->career_classification_id) selected @endif
                            >
                                {{ $carreraClasificada->classification->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class = "schedule-list-select">
                    <label for = "career_select">Carrera:</label>
                    <select id="carrera_select" name="carrera_id" required @if ($modoEdicion) disabled @endif>
                        <option value="">Seleccione una Carrera</option>
                        {{-- Aquí va el loop para cargar las carreras desde la BD --}}
                        @foreach ($carreras as $carrera)
                            <option
                                value = "{{$carrera->id}}"
                                data-classification-id="{{ $carrera->career_classification_id ?? '' }}"
                                @if ($modoEdicion && $carrera->id == $horario->career_id) selected @endif
                            >{{$carrera->name}}</option>
                        @endforeach
                    </select>
                    @if ($modoEdicion)
                        <input type="hidden" name="carrera_id" value="{{ $horario->career_id }}">
                    @endif
                </div>
                <div class = "schedule-list-select">
                    <label for="semestre_filter_select">Semestre</label>
                    <select id="semestre_filter_select" name="semestre_filter">
                        <option value="">Todos los semestres</option>
                    </select>
                    <label for = "materia_select" style="margin-top: 0.75rem;">Materia</label>
                    <select id="materia_select" name="materia_id" required>
                        <option value="">Seleccione una Materia</option>
                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id }}" data-career-id="{{ $materia->career_id }}" data-semestre="{{ $materia->semestre ?? '' }}" @if ($modoEdicion && isset($horario) && $materia->id == $horario->materia_id) selected @endif>{{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class = "schedule-list-select">
                    <label for = "docente_select">Docente</label>
                    <select id="docente_select" name="docente_id" required>
                        <option value="">Seleccione un Docente</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id }}" data-career-id="{{ $docente->teachingCareers->pluck('id')->implode(',') }}" @if ($modoEdicion && $docente->id == $horario->user_id) selected @endif>{{ $docente->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="schedule-form-section-label" style="color: #e69a37; margin-bottom: 0.5rem; display: block; font-weight: 600;">Horario</label>
                <h3 class="schedule-select-title">Seleccione los horarios</h3>
                <div class="schedule-settings">
                    {{-- Div para Botones para seleccionar los dias (Lunes a Domingo) --}}
                    <div class="day-selection-buttons">
                        <button type="button" data-day="1">L</button>
                        <button type="button" data-day="2">M</button>
                        <button type="button" data-day="3">M</button>
                        <button type="button" data-day="4">J</button>
                        <button type="button" data-day="5">V</button>
                        <button type="button" data-day="6">S</button>
                        <button type="button" data-day="7">D</button>
                    </div>
                    
                    {{-- Menú de reloj: Hora (12h), Minutos, AM/PM --}}
                    <div class="time-inputs">
                        <div class="time-input-wrap" data-time-input="hora_inicio">
                            <span class="time-input-display" id="hora_inicio_display" aria-hidden="true">00:00</span>
                            <input type="hidden" id="hora_inicio" name="hora_inicio" value="00:00" required>
                            <div class="time-picker-dropdown time-picker-clock" id="hora_inicio_dropdown" aria-hidden="true">
                                <div class="time-picker-selected">
                                    <span class="time-picker-selected-cell" data-col="hour">12</span>
                                    <span class="time-picker-selected-cell" data-col="min">00</span>
                                    <span class="time-picker-selected-cell" data-col="ampm">a. m.</span>
                                </div>
                                <div class="time-picker-columns">
                                    <div class="time-picker-col" data-col="hour"></div>
                                    <div class="time-picker-col" data-col="min"></div>
                                    <div class="time-picker-col" data-col="ampm"></div>
                                </div>
                            </div>
                        </div>
                        <div class="time-input-wrap" data-time-input="hora_fin">
                            <span class="time-input-display" id="hora_fin_display" aria-hidden="true">00:00</span>
                            <input type="hidden" id="hora_fin" name="hora_fin" value="00:00" required>
                            <div class="time-picker-dropdown time-picker-clock" id="hora_fin_dropdown" aria-hidden="true">
                                <div class="time-picker-selected">
                                    <span class="time-picker-selected-cell" data-col="hour">12</span>
                                    <span class="time-picker-selected-cell" data-col="min">00</span>
                                    <span class="time-picker-selected-cell" data-col="ampm">a. m.</span>
                                </div>
                                <div class="time-picker-columns">
                                    <div class="time-picker-col" data-col="hour"></div>
                                    <div class="time-picker-col" data-col="min"></div>
                                    <div class="time-picker-col" data-col="ampm"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Botón circular con check para confirmar la selección de horas/días --}}
                    <button type="button" class="add-time-slot-btn" aria-label="Añadir franja horaria">
                        <svg class="add-time-slot-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                    
                </div>
                <h3 class="schedule-resume__title">Vista Previa</h3>
                <div class="schedule-resume schedule-resume--cards">
                    <div id="time_slots_body" class="schedule-preview-cards">
                        <table class="schedule-preview-empty-table"><tr><th class="schedule-preview-empty" style="color: #ACACAC; font-size: 0.9rem; font-weight: normal; margin: 0; padding: 8px 12px; text-align: left; border: none; background: transparent; text-transform: capitalize; display: flex; align-items: center; justify-content: space-between; gap: 10px;"><span>Lunes – Martes – Miercoles -- 07:00 – 08:00</span><img src="{{ asset('images/icons/Vector.svg') }}" class="schedule-preview-empty__icon" width="18" height="18" alt="Eliminar" style="flex-shrink: 0;" /></th></tr></table>
                    </div>
                </div>
                <div class = "schedule-list-select">
                    <label for = "aula_select">Aula</label>
                    <select id="aula_select" name="aula_id">
                        <option value="" class="select-placeholder">Seleccione Aula</option>
                        @if ($modoEdicion && isset($horario) && $horario->aula_id && $horario->aula)
                            <option value="{{ $horario->aula_id }}" selected>{{ \App\Support\AulaHorarioPresenter::selectOptionSoloSeccion($horario->aula) }}</option>
                        @endif
                    </select>
                </div>
                <div class="schedule-submit">
                    <button type="submit" id="save_schedule_btn" class="submit-button">{{ $modoEdicion ? '+ Actualizar' : '+ Agregar Horario' }}</button>
                </div>
            </form>
        </div>
        <div class = "schedule-table">
            <div class="toolbar__search">
                <img src="{{ asset('images/icons/magnifying-glass-svgrepo-com.svg') }}" alt="" class="toolbar__search-icon" aria-hidden="true">
                <form action="{{ route('control.schedules.index') }}" method="GET" id="search-form" class="d-flex mb-4">
                    <input type="text" 
                        name="search_query" 
                        id="search-input" 
                        class="form-control me-2" 
                        placeholder="Buscar por..."
                        value="{{ request('search_query') }}"
                        autocomplete="off" {{-- Recomendado para búsquedas en tiempo real --}}>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table-custom-sessions">

                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Materia</th>
                        <th>Clasificación</th>
                        <th>Docentes</th>
                        <th class="schedule-actions-col">Acciones</th>
                    </tr>
                </thead>
                    <tbody class="cuerpo-tabla" id="horarios-tbody">
                        @forelse ($horarios as $horario)
                            <tr>
                                {{-- Acceder a las relaciones cargadas con with() --}}
                                @php
                                    $nombreCarrera = $horario->carrera->name ?? '';
                                    $palabras = preg_split('/\s+/', trim($nombreCarrera), -1, PREG_SPLIT_NO_EMPTY);
                                    $num = count($palabras);
                                    if ($num <= 1) {
                                        $celdaCarrera = e($nombreCarrera);
                                    } else {
                                        $mitad = (int) ceil($num / 2);
                                        $linea1 = e(implode(' ', array_slice($palabras, 0, $mitad)));
                                        $linea2 = e(implode(' ', array_slice($palabras, $mitad)));
                                        $celdaCarrera = $linea1 . '<br>' . $linea2;
                                    }
                                @endphp
                                <td>{!! $celdaCarrera !!}</td>
                                <td>{!! str_replace('Orientada a ', 'Orientada a<br>', e($horario->materia?->nombre ?? '')) !!}</td>
                                <td>{{ $horario->carrera?->classification?->name ?? '—' }}</td>
                                <td>{{ $horario->user?->nombre ?? '—' }}</td>
                                <td class="schedule-actions-col">
                                    <div class="carrer-btn-section" style="display: flex; align-items: center; gap: 4px; flex-wrap: nowrap;">
                                        <a href="{{ route('control.schedules.show', $horario->id) }}" class="btn-view" data-show-url="{{ route('control.schedules.show', $horario->id) }}" title="Ver información">
                                            <img src="{{ asset('images/icons/eye-solid-full-gold.svg') }}" alt="Ver" width="20" height="20">
                                        </a>
                                        <a href="{{ route('control.schedules.edit', $horario->id) }}" class="btn-edit" data-edit-url="{{ route('control.schedules.edit', $horario->id) }}" title="Editar">
                                            <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar" width="20" height="20">
                                        </a>
                                        <form id="deleteHorarioForm_{{ $horario->id }}" action="{{ route('control.schedules.destroy', $horario->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn-delete btn-horario-delete" data-form-id="deleteHorarioForm_{{ $horario->id }}" title="Eliminar">
                                                <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar" width="20" height="20">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            {{--  Este bloque se ejecuta cuando $horarios está vacío --}}
                            <tr>
                                <td colspan="5" class="text-center">
                                    No se encontraron horarios
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

    {{-- Modal Confirmar eliminación de horario --}}
    <div id="horarioDeleteModal" class="modal-overlay modal-overlay--center" style="display:none; z-index:10002;" aria-hidden="true">
        <div class="modal-container" style="max-width:300px; width:300px; height:auto; min-height:unset; text-align:center; padding:20px 24px 18px; border-radius:10px;">
            <p style="font-size:0.9rem;font-weight:600;color:#333;margin:0 0 4px;">¿Eliminar este horario?</p>
            <p style="font-size:0.8rem;color:#999;margin:0 0 16px;">Esta acción no se puede deshacer.</p>
            <div style="display:flex;gap:8px;justify-content:center;">
                <button type="button" id="horarioDeleteCancelBtn" style="background:#f0f0f0;color:#555;border:none;padding:7px 20px;border-radius:5px;font-size:0.85rem;cursor:pointer;">Cancelar</button>
                <button type="button" id="horarioDeleteConfirmBtn" style="background:#c0392b;color:#fff;border:none;padding:7px 20px;border-radius:5px;font-size:0.85rem;cursor:pointer;font-weight:600;">Eliminar</button>
            </div>
        </div>
    </div>

    {{-- Modal Editar horario (abre por modal en lugar de vista) --}}
    <div id="horarioEditModal" class="modal-overlay modal-overlay--center" style="display: none; z-index: 10001;" aria-hidden="true">
        <div class="modal-view-career__container modal-view-career__container--wide">
            <div class="modal-view-career__header">
                <h5 class="modal-view-career__title">Editar horario</h5>
                <button type="button" class="close-custom btn-close-view modal-view-career__close horario-edit-modal-close" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-view-career__body" id="horarioEditContent" style="max-height: 80vh; overflow-y: auto;">
            </div>
        </div>
    </div>

    {{-- Modal Ver horario --}}
    <div id="horarioVerModal" class="modal-overlay modal-overlay--center" style="display: none; z-index: 10000;" aria-hidden="true">
        <div class="modal-view-career__container modal-view-career__container--wide">
            <div class="modal-view-career__header">
                <h5 class="modal-view-career__title">Detalle del horario</h5>
                <button type="button" class="close-custom btn-close-view modal-view-career__close horario-modal-close" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-view-career__body" id="horarioVerModalBody" style="max-height: 70vh; overflow-y: auto;">
                <div class="horario-modal-loading" style="padding: 1.5rem; text-align: center; color: #666;">Cargando...</div>
            </div>
        </div>
    </div>

    {{-- Modal Ver horario --}}
@push('scripts')
<script>
    // Franjas horarias (añadir, eliminar, guardar, vista previa e inicial en edición) se gestionan en app.js (delegación + data-initial-franjas).
    document.addEventListener('DOMContentLoaded', function() {

        // Filtrar Materia y Docente por carrera: solo mostrar opciones de esa carrera; si no hay, el menú no muestra nada
        const carreraSelect = document.getElementById('carrera_select');
        const clasificacionSelect = document.getElementById('clasificacion_select');
        const materiaSelect = document.getElementById('materia_select');
        const docenteSelect = document.getElementById('docente_select');
        const carreraOptions = Array.from(carreraSelect.querySelectorAll('option')).filter(o => o.value !== '').map(o => ({
            value: o.value,
            classificationId: String(o.getAttribute('data-classification-id') || ''),
            text: o.textContent.trim()
        }));

        const materiaOptions = Array.from(materiaSelect.querySelectorAll('option')).filter(o => o.value !== '').map(o => ({
            value: o.value,
            careerId: String(o.getAttribute('data-career-id') || ''),
            text: o.textContent.trim(),
            semestre: String(o.getAttribute('data-semestre') || ''),
        }));
        const docenteOptions = Array.from(docenteSelect.querySelectorAll('option')).filter(o => o.value !== '').map(o => ({
            value: o.value,
            careerId: String(o.getAttribute('data-career-id') || ''),
            text: o.textContent.trim()
        }));

        function actualizarCarreras(resetValues) {
            if (!carreraSelect || !clasificacionSelect) return;
            const classificationId = clasificacionSelect.value ? String(clasificacionSelect.value) : '';
            const carreraVal = carreraSelect.value;
            const carrerasFiltradas = classificationId === ''
                ? carreraOptions
                : carreraOptions.filter(o => o.classificationId === classificationId);

            carreraSelect.innerHTML = '';
            const optC0 = document.createElement('option');
            optC0.value = '';
            optC0.textContent = 'Seleccione una Carrera';
            carreraSelect.appendChild(optC0);

            carrerasFiltradas.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.setAttribute('data-classification-id', o.classificationId);
                opt.textContent = o.text;
                if (!resetValues && carreraVal === o.value) opt.selected = true;
                carreraSelect.appendChild(opt);
            });

            if (resetValues) carreraSelect.value = '';
        }

        function actualizarMateriaYDocente(resetValues) {
            const careerId = (carreraSelect && carreraSelect.value) ? String(carreraSelect.value) : '';
            const semestreFilterSelect = document.getElementById('semestre_filter_select');
            const materiaVal = materiaSelect.value;
            const docenteVal = docenteSelect.value;

            // Materias de la carrera seleccionada
            const materiasPorCarrera = careerId === '' ? materiaOptions : materiaOptions.filter(o => o.careerId === careerId);

            // Poblar semestres disponibles según la carrera
            if (semestreFilterSelect) {
                const semestreValActual = semestreFilterSelect.value;
                const semestres = [...new Set(materiasPorCarrera.map(o => o.semestre).filter(s => s !== ''))].sort((a, b) => Number(a) - Number(b));
                semestreFilterSelect.innerHTML = '';
                const optS0 = document.createElement('option');
                optS0.value = '';
                optS0.textContent = 'Todos los semestres';
                semestreFilterSelect.appendChild(optS0);
                semestres.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = 'Semestre ' + s;
                    semestreFilterSelect.appendChild(opt);
                });
                if (resetValues) {
                    semestreFilterSelect.value = '';
                } else if (semestres.includes(semestreValActual)) {
                    semestreFilterSelect.value = semestreValActual;
                }
            }

            // Filtrar materias también por semestre si hay uno seleccionado
            const semestreActual = semestreFilterSelect ? semestreFilterSelect.value : '';
            const materiasFiltradas = semestreActual === '' ? materiasPorCarrera : materiasPorCarrera.filter(o => o.semestre === semestreActual);

            materiaSelect.innerHTML = '';
            const optM0 = document.createElement('option');
            optM0.value = '';
            optM0.textContent = 'Seleccione una Materia';
            materiaSelect.appendChild(optM0);
            materiasFiltradas.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.setAttribute('data-career-id', o.careerId);
                opt.setAttribute('data-semestre', o.semestre);
                opt.textContent = o.text;
                if (!resetValues && materiaVal === o.value) opt.selected = true;
                materiaSelect.appendChild(opt);
            });

            // Docente: igual; si la carrera no tiene docentes, el menú no muestra nada
            const docentesFiltrados = careerId === '' ? docenteOptions : docenteOptions.filter(o => o.careerId.split(',').map(s => s.trim()).filter(Boolean).includes(careerId));
            docenteSelect.innerHTML = '';
            const optD0 = document.createElement('option');
            optD0.value = '';
            optD0.textContent = 'Seleccione un Docente';
            docenteSelect.appendChild(optD0);
            docentesFiltrados.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.setAttribute('data-career-id', o.careerId);
                opt.textContent = o.text;
                if (!resetValues && docenteVal === o.value) opt.selected = true;
                docenteSelect.appendChild(opt);
            });

            if (resetValues) {
                materiaSelect.value = '';
                docenteSelect.value = '';
            }
        }

        if (clasificacionSelect) {
            clasificacionSelect.addEventListener('change', function() {
                actualizarCarreras(true);
                actualizarMateriaYDocente(true);
                updateClasificacionPlaceholderStyle();
            });
        }

        if (carreraSelect) {
            carreraSelect.addEventListener('change', function() {
                actualizarMateriaYDocente(true);
            });
            actualizarCarreras(false);
            actualizarMateriaYDocente(false);
        }

        const semestreFilterSelectEl = document.getElementById('semestre_filter_select');
        if (semestreFilterSelectEl) {
            semestreFilterSelectEl.addEventListener('change', function() {
                actualizarMateriaYDocente(false);
            });
        }

        function updateClasificacionPlaceholderStyle() {
            if (clasificacionSelect) {
                if (clasificacionSelect.value === '') clasificacionSelect.classList.add('select-placeholder');
                else clasificacionSelect.classList.remove('select-placeholder');
            }
        }
        if (clasificacionSelect) {
            updateClasificacionPlaceholderStyle();
        }

        // Placeholder gris para "Seleccione Aula" (aula es opcional, sin required)
        const aulaSelect = document.getElementById('aula_select');
        function updateAulaPlaceholderStyle() {
            if (aulaSelect) {
                if (aulaSelect.value === '') aulaSelect.classList.add('select-placeholder');
                else aulaSelect.classList.remove('select-placeholder');
            }
        }
        if (aulaSelect) {
            aulaSelect.addEventListener('change', updateAulaPlaceholderStyle);
            updateAulaPlaceholderStyle();
        }

        // Modal Ver horario: delegación en tbody (los botones se inyectan por búsqueda)
        document.getElementById('horarios-tbody') && document.getElementById('horarios-tbody').addEventListener('click', function(e) {
            var btnVer = e.target.closest('a.btn-view[data-show-url]');
            if (btnVer) {
                e.preventDefault();
                var url = btnVer.getAttribute('data-show-url');
                var modal = document.getElementById('horarioVerModal');
                var body = document.getElementById('horarioVerModalBody');
                if (!modal || !body || !url) return;
                body.innerHTML = '<div class="horario-modal-loading" style="padding: 1.5rem; text-align: center; color: #666;">Cargando...</div>';
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.ok ? r.text() : Promise.reject(new Error('Error al cargar')); })
                    .then(function(html) {
                        body.innerHTML = html;
                    })
                    .catch(function() {
                        body.innerHTML = '<p style="color: #c00; padding: 1rem;">No se pudo cargar el detalle.</p>';
                    });
                return;
            }
        });

        // Cerrar modal Ver horario (botón y clic en overlay)
        function closeHorarioVerModal() {
            var verModal = document.getElementById('horarioVerModal');
            if (verModal) { verModal.style.display = 'none'; verModal.setAttribute('aria-hidden', 'true'); }
        }
        document.querySelectorAll('.horario-modal-close').forEach(function(btn) {
            btn.addEventListener('click', closeHorarioVerModal);
        });
        document.getElementById('horarioVerModal') && document.getElementById('horarioVerModal').addEventListener('click', function(e) {
            if (e.target === this) closeHorarioVerModal();
        });

        // Salir de edición: tacha (X) — ocultar barra de búsqueda y tabla ya se hace con .is-editing
        function exitScheduleEditMode() {
            var creator = document.querySelector('.creator-container');
            if (creator) creator.classList.remove('is-editing');
            var editHeader = document.getElementById('schedule_edit_header');
            if (editHeader) { editHeader.style.display = 'none'; editHeader.setAttribute('aria-hidden', 'true'); }
            var form = document.getElementById('schedule_form');
            if (!form) return;
            var storeUrl = form.getAttribute('data-store-url');
            if (storeUrl) form.action = storeUrl;
            var methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.value = 'POST';
            var carreraSelect = document.getElementById('carrera_select');
            var clasificacionSelect = document.getElementById('clasificacion_select');
            var materiaSelect = document.getElementById('materia_select');
            if (carreraSelect) { carreraSelect.disabled = false; carreraSelect.value = ''; }
            if (clasificacionSelect) {
                clasificacionSelect.disabled = false;
                clasificacionSelect.value = '';
                clasificacionSelect.classList.add('select-placeholder');
            }
            if (materiaSelect) { materiaSelect.disabled = false; materiaSelect.value = ''; }
            var hCarrera = form.querySelector('input[name="carrera_id"]');
            var hMateria = form.querySelector('input[name="materia_id"]');
            if (hCarrera) hCarrera.remove();
            if (hMateria) hMateria.remove();
            form.removeAttribute('data-initial-franjas');
            delete form._scheduleFranjas;
            var docenteSelect = document.getElementById('docente_select');
            var aulaSelectEl = document.getElementById('aula_select');
            if (docenteSelect) docenteSelect.value = '';
            if (aulaSelectEl) aulaSelectEl.value = '';
            if (typeof updateAulaPlaceholderStyle === 'function') updateAulaPlaceholderStyle();
            if (window.initScheduleFormIfNeeded) window.initScheduleFormIfNeeded();
            var submitBtn = form.querySelector('#save_schedule_btn');
            if (submitBtn) submitBtn.textContent = '+ Agregar Horario';
        }
        document.getElementById('schedule_edit_close') && document.getElementById('schedule_edit_close').addEventListener('click', exitScheduleEditMode);

        // Menú de reloj: Hora (12h), Minutos, AM/PM — columnas desplazables
        function pad2(n) { return (n < 10 ? '0' : '') + n; }
        function to24h(h12, ampm) {
            var h = parseInt(h12, 10);
            if (ampm === 'p. m.') return h === 12 ? 12 : h + 12;
            return h === 12 ? 0 : h;
        }
        function from24h(h24) {
            var h = parseInt(h24, 10);
            if (h === 0) return { h12: '12', ampm: 'a. m.' };
            if (h < 12) return { h12: pad2(h), ampm: 'a. m.' };
            if (h === 12) return { h12: '12', ampm: 'p. m.' };
            return { h12: pad2(h - 12), ampm: 'p. m.' };
        }
        function toDisplay12h(val24) {
            var v = (val24 || '00:00').trim().split(':');
            var h24 = Math.min(23, Math.max(0, parseInt(v[0], 10) || 0));
            var m = pad2(Math.min(59, Math.max(0, parseInt(v[1], 10) || 0)));
            var s = from24h(h24);
            return (s.h12 === '12' ? '12' : String(parseInt(s.h12, 10))) + ':' + m + ' ' + s.ampm;
        }

        function initClockPicker(inputId, displayId, dropdownId) {
            var wrap = document.querySelector('.time-input-wrap[data-time-input="' + inputId + '"]');
            var input = document.getElementById(inputId);
            var display = document.getElementById(displayId);
            var dropdown = document.getElementById(dropdownId);
            if (!wrap || !input || !display || !dropdown) return;

            var selectedRow = dropdown.querySelector('.time-picker-selected');
            var cols = dropdown.querySelectorAll('.time-picker-col');
            var colHour = cols[0], colMin = cols[1], colAmpm = cols[2];
            var cells = selectedRow.querySelectorAll('.time-picker-selected-cell');
            var cellHour = cells[0], cellMin = cells[1], cellAmpm = cells[2];

            if (colHour.children.length === 0) {
                ['12','01','02','03','04','05','06','07','08','09','10','11'].forEach(function(v) {
                    var o = document.createElement('div'); o.className = 'time-picker-option'; o.dataset.value = v; o.textContent = v; colHour.appendChild(o);
                });
                for (var m = 0; m < 60; m++) {
                    var o = document.createElement('div'); o.className = 'time-picker-option'; o.dataset.value = pad2(m); o.textContent = pad2(m); colMin.appendChild(o);
                }
                ['a. m.', 'p. m.'].forEach(function(v) {
                    var o = document.createElement('div'); o.className = 'time-picker-option'; o.dataset.value = v; o.textContent = v; colAmpm.appendChild(o);
                });
            }

            function getState() {
                return { hour: cellHour.textContent, min: cellMin.textContent, ampm: cellAmpm.textContent };
            }
            function setState(hour, min, ampm) {
                cellHour.textContent = hour;
                cellMin.textContent = min;
                cellAmpm.textContent = ampm;
                colHour.querySelectorAll('.time-picker-option').forEach(function(o) { o.classList.toggle('selected', o.dataset.value === hour); });
                colMin.querySelectorAll('.time-picker-option').forEach(function(o) { o.classList.toggle('selected', o.dataset.value === min); });
                colAmpm.querySelectorAll('.time-picker-option').forEach(function(o) { o.classList.toggle('selected', o.dataset.value === ampm); });
                var h24 = to24h(hour, ampm);
                var val = pad2(h24) + ':' + min;
                input.value = val;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                display.textContent = val;
            }
            function syncFromInput() {
                var v = (input.value || '00:00').trim().split(':');
                var h24 = Math.min(23, Math.max(0, parseInt(v[0], 10) || 0));
                var m = Math.min(59, Math.max(0, parseInt(v[1], 10) || 0));
                var s = from24h(h24);
                setState(s.h12, pad2(m), s.ampm);
                [colHour, colMin, colAmpm].forEach(function(col) {
                    var sel = col.querySelector('.time-picker-option.selected');
                    if (sel) sel.scrollIntoView({ block: 'nearest', behavior: 'auto' });
                });
            }

            [colHour, colMin, colAmpm].forEach(function(col) {
                col.addEventListener('click', function(e) {
                    var opt = e.target.closest('.time-picker-option');
                    if (!opt) return;
                    var state = getState();
                    var colName = col.dataset.col;
                    if (colName === 'hour') state.hour = opt.dataset.value;
                    if (colName === 'min') state.min = opt.dataset.value;
                    if (colName === 'ampm') state.ampm = opt.dataset.value;
                    setState(state.hour, state.min, state.ampm);
                });
            });

            wrap.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = dropdown.classList.contains('is-open');
                document.querySelectorAll('.time-picker-dropdown.is-open').forEach(function(el) { el.classList.remove('is-open'); });
                if (!isOpen) {
                    syncFromInput();
                    dropdown.classList.add('is-open');
                }
            });
            dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
        }
        initClockPicker('hora_inicio', 'hora_inicio_display', 'hora_inicio_dropdown');
        initClockPicker('hora_fin', 'hora_fin_display', 'hora_fin_dropdown');

        document.addEventListener('click', function() {
            document.querySelectorAll('.time-picker-dropdown.is-open').forEach(function(el) { el.classList.remove('is-open'); });
        });
        document.querySelectorAll('.time-input-wrap').forEach(function(w) {
            w.addEventListener('click', function(e) { e.stopPropagation(); });
        });

        function syncTimeDisplay(inputId, displayId) {
            var input = document.getElementById(inputId);
            var display = document.getElementById(displayId);
            if (!input || !display) return;
            function update() { display.textContent = (input.value || '00:00').substring(0, 5); }
            input.addEventListener('input', update);
            update();
        }
        syncTimeDisplay('hora_inicio', 'hora_inicio_display');
        syncTimeDisplay('hora_fin', 'hora_fin_display');

        // Los botones de día, "Añadir franja", "Eliminar" y "Guardar" se manejan por delegación en app.js (funcionan con SPA y carga normal).
    });
    // =========================================================
    // 2. BÚSQUEDA SIN REFRESCAR (AJAX)
    // =========================================================
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const tbody = document.getElementById('horarios-tbody');
        let searchTimeout;

        if (searchInput && searchForm && tbody) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                doSearch();
            });

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = searchInput.value.trim();
                if (query.length === 0) {
                    searchTimeout = setTimeout(doSearch, 150);
                    return;
                }
                if (query.length >= 1) {
                    searchTimeout = setTimeout(doSearch, 300);
                }
            });

            function doSearch() {
                const query = searchInput.value.trim();
                const url = searchForm.action + (searchForm.action.indexOf('?') >= 0 ? '&' : '?') + 'search_query=' + encodeURIComponent(query);
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.text(); })
                    .then(function(html) {
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');
                        var newTbody = doc.getElementById('horarios-tbody');
                        if (newTbody) tbody.innerHTML = newTbody.innerHTML;
                    })
                    .catch(function() {});
            }
        }
    });

    // =========================================================
    // 3. MODAL PERSONALIZADO DE CONFIRMACIÓN DE ELIMINACIÓN
    // =========================================================
    (function() {
        var pendingFormId = null;

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-horario-delete');
            if (!btn) return;
            e.preventDefault();
            pendingFormId = btn.getAttribute('data-form-id');
            var modal = document.getElementById('horarioDeleteModal');
            if (modal) { modal.style.display = 'flex'; modal.setAttribute('aria-hidden', 'false'); }
        });

        document.getElementById('horarioDeleteConfirmBtn') && document.getElementById('horarioDeleteConfirmBtn').addEventListener('click', function() {
            if (pendingFormId) {
                var form = document.getElementById(pendingFormId);
                if (form) form.submit();
            }
            closeDeleteModal();
        });

        document.getElementById('horarioDeleteCancelBtn') && document.getElementById('horarioDeleteCancelBtn').addEventListener('click', closeDeleteModal);

        var modal = document.getElementById('horarioDeleteModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeDeleteModal();
            });
        }

        function closeDeleteModal() {
            var modal = document.getElementById('horarioDeleteModal');
            if (modal) { modal.style.display = 'none'; modal.setAttribute('aria-hidden', 'true'); }
            pendingFormId = null;
        }
    })();
</script>
@endpush
@endsection
