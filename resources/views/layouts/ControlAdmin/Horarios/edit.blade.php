@extends('layouts.minimal')

@section('title', 'Editar horario')

@section('vite')
    @vite(['resources/css/app.css', 'resources/css/Control Admin/horario-edit.css', 'resources/js/horario-edit.js'])
@endsection

@section('content')
<div class="horario-edit-wrap">
    <div class="creator-container is-editing" id="creator_container">
        <div class="schedule-lists">
            <form id="schedule_form" method="POST" action="{{ route('control.schedules.update', $horario->id) }}" data-store-url="{{ route('control.schedules.store') }}" @if($horario->franjas->isNotEmpty()) data-initial-franjas="{{ $horario->franjas->toJson() }}" @endif>
                @csrf
                @method('PUT')
                @error('franjas_json')
                    <div class="alert alert-warning">{{ $message }}</div>
                @enderror

                <div class="schedule-list-select">
                    <label for="career_select">Carrera:</label>
                    <select id="carrera_select" name="carrera_id" required>
                        <option value="">Seleccione una Carrera</option>
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id }}" @if($carrera->id == $horario->career_id) selected @endif>{{ $carrera->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="schedule-list-select">
                    <label for="materia_select">Materia</label>
                    <select id="materia_select" name="materia_id" required>
                        <option value="">Seleccione una Materia</option>
                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id }}" data-career-id="{{ $materia->career_id }}" @if($materia->id == $horario->materia_id) selected @endif>{{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="schedule-list-select">
                    <label for="docente_select">Docente</label>
                    <select id="docente_select" name="docente_id" required>
                        <option value="">Seleccione un Docente</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id }}" data-career-id="{{ $docente->academicProfile->career_id ?? '' }}" @if($docente->id == $horario->user_id) selected @endif>{{ $docente->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="schedule-form-section-label" style="color: #e69a37; margin-bottom: 0.5rem; display: block; font-weight: 600;">Horario</label>
                <h3 class="schedule-select-title">Seleccione los horarios</h3>
                <div class="schedule-settings">
                    <div class="day-selection-buttons">
                        <button type="button" data-day="1">L</button>
                        <button type="button" data-day="2">M</button>
                        <button type="button" data-day="3">M</button>
                        <button type="button" data-day="4">J</button>
                        <button type="button" data-day="5">V</button>
                        <button type="button" data-day="6">S</button>
                        <button type="button" data-day="7">D</button>
                    </div>
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

                <div class="schedule-list-select">
                    <label for="aula_select">Aula</label>
                    <select id="aula_select" name="aula_id">
                        <option value="" class="select-placeholder">Seleccione Aula</option>
                        @foreach ($aulas as $aula)
                            <option value="{{ $aula->id }}" @if($aula->id == $horario->aula_id) selected @endif>{{ $aula->numero_aula }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="schedule-submit">
                    <button type="submit" id="save_schedule_btn" class="submit-button">+ Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
