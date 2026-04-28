@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
@php
    // Define la variable de control para toda la plantilla
    // Si la variable $horario existe (porque estamos en la ruta de edición), es TRUE.
    $modoEdicion = isset($horario); 
@endphp

<div class ="container">
    @if(!$modoEdicion)
    <div class="content-header" style="text-align: left; margin-bottom: 1rem; margin-left: 1.5rem;">
        <h5 class="content-title" style="font-size: 1.75rem; font-weight: 700; margin: 0;">HORARIOS</h5>
    </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class = "creator-container {{ $modoEdicion ? 'is-editing' : '' }}" id="creator_container">
        <div class = "schedule-lists">
            <div class="schedule-edit-header" id="schedule_edit_header" style="{{ $modoEdicion ? '' : 'display: none;' }}" aria-hidden="{{ $modoEdicion ? 'false' : 'true' }}">
                <span class="schedule-edit-header__title">Editar horario</span>
                <button type="button" class="schedule-edit-close" id="schedule_edit_close" title="Salir de edición" aria-label="Salir de edición">✕</button>
            </div>
            <form id="schedule_form" method="POST" action="{{ $modoEdicion ? route('control.schedules.update', $horario->id) : route('control.schedules.store') }}" data-store-url="{{ route('control.schedules.store') }}" @if($modoEdicion && $horario->franjas->isNotEmpty()) data-initial-franjas="{{ $horario->franjas->toJson() }}" @endif>
                @csrf
                @if ($modoEdicion)
                    @method('PUT') 
                @endif
                @error('franjas_json')
                    <div class="alert alert-warning">{{ $message }}</div>
                @enderror
                <div class = "schedule-list-select">
                    <label for = "career_select">Carrera:</label>
                    <select id="carrera_select" name="carrera_id" required>
                        @if ($modoEdicion) disabled @endif
                        <option value="">Seleccione una Carrera</option>
                        {{-- Aquí va el loop para cargar las carreras desde la BD --}}
                        @foreach ($carreras as $carrera)
                            <option value = "{{$carrera->id}}" @if ($modoEdicion && $carrera->id == $horario->career_id) selected @endif>{{$carrera->name}}</option>
                        @endforeach
                    </select>
                    @if ($modoEdicion)
                        <input type="hidden" name="carrera_id" value="{{ $horario->career_id }}">
                    @endif
                </div>
                <div class = "schedule-list-select">
                    <label for = "materia_select">Materia</label>
                    <select id="materia_select" name="materia_id" required>
                        <option value="">Seleccione una Materia</option>
                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id }}" data-career-id="{{ $materia->career_id }}" @if ($modoEdicion && isset($horario) && $materia->id == $horario->materia_id) selected @endif>{{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class = "schedule-list-select">
                    <label for = "docente_select">Docente</label>
                    <select id="docente_select" name="docente_id" required>
                        <option value="">Seleccione un Docente</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id }}" data-career-id="{{ $docente->academicProfile->career_id ?? '' }}" @if ($modoEdicion && $docente->id == $horario->user_id) selected @endif>{{ $docente->nombre }}</option>
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
                        <table class="schedule-preview-empty-table"><tr><th class="schedule-preview-empty" style="color: #ACACAC; font-size: 0.9rem; font-weight: normal; margin: 0; padding: 8px 12px; text-align: left; border: none; background: transparent; text-transform: capitalize; display: flex; align-items: center; justify-content: space-between; gap: 10px;"><span>Lunes – Martes – Miercoles -- 07:00 – 08:00</span><img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" class="schedule-preview-empty__icon" width="18" height="18" alt="Editar" style="flex-shrink: 0;" /></th></tr></table>
                    </div>
                </div>
                <div class = "schedule-list-select">
                    <label for = "aula_select">Aula</label>
                    <select id="aula_select" name="aula_id">
                        <option value="" class="select-placeholder">Seleccione Aula</option>
                    {{-- Aquí va el loop para cargar las carreras desde la BD --}}
                    @foreach ($aulas as $aula)
                        <option value = "{{$aula->id}}" @if ($modoEdicion && $aula->id == $horario->aula_id) selected @endif>{{$aula->numero_aula}}</option>
                    @endforeach
                    
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
            <div class="Table-view">
                <table class="tabla-base tabla-rayas tabla-bordes">
                    <theader class="encabezado-tabla">
                        <tr>
                                <th>Carrera</th>
                                <th>Materia</th>
                                <th>Docentes</th>
                                <th>Acciones</th>
                        </tr>
                    </theader>
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
                                <td>{{ $horario->user?->nombre ?? '—' }}</td>
                                <td>
                                    <div class="carrer-btn-section" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                        <a href="{{ route('control.schedules.show', $horario->id) }}" class="btn-view" data-show-url="{{ route('control.schedules.show', $horario->id) }}" title="Ver información">
                                            <svg width="20" height="20" viewBox="0 0 29 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M14.4987 0.625C10.4307 0.625 7.17322 2.49375 4.80187 4.71797C2.44562 6.92188 0.869748 9.5625 0.124609 11.3754C-0.0415365 11.7766 -0.0415365 12.2234 0.124609 12.6246C0.869748 14.4375 2.44562 17.0781 4.80187 19.282C7.17322 21.5063 10.4307 23.375 14.4987 23.375C18.5668 23.375 21.8243 21.5063 24.1956 19.282C26.5519 17.073 28.1277 14.4375 28.8779 12.6246C29.0441 12.2234 29.0441 11.7766 28.8779 11.3754C28.1277 9.5625 26.5519 6.92188 24.1956 4.71797C21.8243 2.49375 18.5668 0.625 14.4987 0.625ZM7.24874 12C7.24874 10.0606 8.01258 8.20064 9.37222 6.82928C10.7319 5.45792 12.5759 4.6875 14.4987 4.6875C16.4216 4.6875 18.2656 5.45792 19.6253 6.82928C20.9849 8.20064 21.7487 10.0606 21.7487 12C21.7487 13.9394 20.9849 15.7994 19.6253 17.1707C18.2656 18.5421 16.4216 19.3125 14.4987 19.3125C12.5759 19.3125 10.7319 18.5421 9.37222 17.1707C8.01258 15.7994 7.24874 13.9394 7.24874 12ZM14.4987 8.75C14.4987 10.5426 13.0538 12 11.2765 12C10.9191 12 10.5767 11.9391 10.2545 11.8324C9.97756 11.741 9.65534 11.9137 9.66541 12.2082C9.68051 12.5586 9.73086 12.909 9.82652 13.2594C10.5163 15.8594 13.1696 17.4031 15.7474 16.7074C18.3251 16.0117 19.8557 13.3355 19.1659 10.7355C18.6071 8.62813 16.7593 7.21133 14.7052 7.125C14.4132 7.11484 14.242 7.43477 14.3326 7.71914C14.4383 8.04414 14.4987 8.38945 14.4987 8.75Z" fill="#BC8A55"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('control.schedules.edit', $horario->id) }}" class="btn-edit" data-edit-url="{{ route('control.schedules.edit', $horario->id) }}" title="Editar">
                                            <svg width="20" height="20" viewBox="0 0 24 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M22.3364 0.648281C21.2991 -0.216094 19.6225 -0.216094 18.5852 0.648281L17.1596 1.83235L21.7964 5.69638L23.2221 4.50836C24.2593 3.64399 24.2593 2.24678 23.2221 1.38241L22.3364 0.648281ZM8.16538 9.33149C7.87646 9.57225 7.65386 9.86827 7.52598 10.1959L6.12403 13.7007C5.98668 14.0402 6.09561 14.4151 6.39874 14.6717C6.70186 14.9282 7.15181 15.015 7.56387 14.9006L11.7697 13.7323C12.1581 13.6257 12.5133 13.4402 12.8069 13.1995L20.7308 6.59233L16.0892 2.72436L8.16538 9.33149ZM4.54685 2.31783C2.03661 2.31783 0 4.015 0 6.10686V16.211C0 18.3028 2.03661 20 4.54685 20H16.6718C19.182 20 21.2186 18.3028 21.2186 16.211V12.4219C21.2186 11.7233 20.5413 11.1589 19.703 11.1589C18.8647 11.1589 18.1874 11.7233 18.1874 12.4219V16.211C18.1874 16.9096 17.5101 17.474 16.6718 17.474H4.54685C3.70852 17.474 3.03123 16.9096 3.03123 16.211V6.10686C3.03123 5.40826 3.70852 4.84385 4.54685 4.84385H9.09369C9.93202 4.84385 10.6093 4.27944 10.6093 3.58084C10.6093 2.88223 9.93202 2.31783 9.09369 2.31783H4.54685Z" fill="black"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('control.schedules.destroy', $horario->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este horario? Esta acción es irreversible.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete" title="Eliminar">
                                                <svg width="20" height="20" viewBox="0 0 27 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M27 3C27 1.34531 25.6547 0 24 0H9.62344C8.82656 0 8.0625 0.314062 7.5 0.876562L0.440625 7.94062C0.159375 8.22187 0 8.60156 0 9C0 9.39844 0.159375 9.77813 0.440625 10.0594L7.5 17.1234C8.0625 17.6859 8.82656 18 9.62344 18H24C25.6547 18 27 16.6547 27 15V3ZM12.7031 5.20312C13.1438 4.7625 13.8562 4.7625 14.2922 5.20312L16.4953 7.40625L18.6984 5.20312C19.1391 4.7625 19.8516 4.7625 20.2875 5.20312C20.7234 5.64375 20.7281 6.35625 20.2875 6.79219L18.0844 8.99531L20.2875 11.1984C20.7281 11.6391 20.7281 12.3516 20.2875 12.7875C19.8469 13.2234 19.1344 13.2281 18.6984 12.7875L16.4953 10.5844L14.2922 12.7875C13.8516 13.2281 13.1391 13.2281 12.7031 12.7875C12.2672 12.3469 12.2625 11.6344 12.7031 11.1984L14.9062 8.99531L12.7031 6.79219C12.2625 6.35156 12.2625 5.63906 12.7031 5.20312Z" fill="#D30303"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            {{--  Este bloque se ejecuta cuando $horarios está vacío --}}
                            <tr>
                                <td colspan="6" class="text-center">
                                    No se encontraron horarios
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
        const materiaSelect = document.getElementById('materia_select');
        const docenteSelect = document.getElementById('docente_select');

        const materiaOptions = Array.from(materiaSelect.querySelectorAll('option')).filter(o => o.value !== '').map(o => ({
            value: o.value,
            careerId: String(o.getAttribute('data-career-id') || ''),
            text: o.textContent.trim()
        }));
        const docenteOptions = Array.from(docenteSelect.querySelectorAll('option')).filter(o => o.value !== '').map(o => ({
            value: o.value,
            careerId: String(o.getAttribute('data-career-id') || ''),
            text: o.textContent.trim()
        }));

        function actualizarMateriaYDocente(resetValues) {
            const careerId = (carreraSelect && carreraSelect.value) ? String(carreraSelect.value) : '';
            const materiaVal = materiaSelect.value;
            const docenteVal = docenteSelect.value;

            // Materia: si hay carrera elegida, solo opciones de esa carrera; si no hay ninguna, el menú no muestra nada (solo una opción vacía)
            const materiasFiltradas = careerId === '' ? materiaOptions : materiaOptions.filter(o => o.careerId === careerId);
            materiaSelect.innerHTML = '';
            const optM0 = document.createElement('option');
            optM0.value = '';
            optM0.textContent = 'Seleccione una Materia';
            materiaSelect.appendChild(optM0);
            materiasFiltradas.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.setAttribute('data-career-id', o.careerId);
                opt.textContent = o.text;
                if (!resetValues && materiaVal === o.value) opt.selected = true;
                materiaSelect.appendChild(opt);
            });

            // Docente: igual; si la carrera no tiene docentes, el menú no muestra nada
            const docentesFiltrados = careerId === '' ? docenteOptions : docenteOptions.filter(o => o.careerId === careerId);
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

        if (carreraSelect) {
            carreraSelect.addEventListener('change', function() {
                actualizarMateriaYDocente(true);
            });
            actualizarMateriaYDocente(false);
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
            var materiaSelect = document.getElementById('materia_select');
            if (carreraSelect) { carreraSelect.disabled = false; carreraSelect.value = ''; }
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
</script>
@endpush
@endsection
