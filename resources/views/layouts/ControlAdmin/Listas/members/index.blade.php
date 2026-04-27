@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    @if(session('success'))
        <div class="message-success" style="margin-bottom: 1rem; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">{{ session('success') }}</div>
    @endif
    <!-- Header -->
    <div class ="content-header">
        <div class="content-title">
            <h3>Docente</h3>
        </div>
    </div>
    <div class="list-header-toolbar">
        <div class="toolbar__section toolbar__section--left">
            <div class="toolbar__search">
                <img src="{{ asset('images/icons/magnifying-glass-svgrepo-com.svg') }}" alt="" class="toolbar__search-icon" aria-hidden="true">
                <input type="text" id="docentesSearchNombre" placeholder="Buscar por..." autocomplete="off">
            </div>

            <div class="toolbar__actions">
                    <form action="{{ route('control.teachers.export') }}" method="GET" class="toolbar__export-form">
                        <input type="hidden" name="search_query" id="docentesExportSearchQuery" value="">
                        <button type="submit" class="btn btn--secondary btn-exportar"><svg class="btn-exportar__icon" width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M0 1.875C0 0.84082 0.996528 0 2.22222 0H7.77778V3.75C7.77778 4.26855 8.27431 4.6875 8.88889 4.6875H13.3333V8.4375H7.5C7.03819 8.4375 6.66667 8.75098 6.66667 9.14062C6.66667 9.53027 7.03819 9.84375 7.5 9.84375H13.3333V13.125C13.3333 14.1592 12.3368 15 11.1111 15H2.22222C0.996528 15 0 14.1592 0 13.125V1.875ZM13.3333 9.84375V8.4375H17.1563L15.8021 7.29492C15.4757 7.01953 15.4757 6.57422 15.8021 6.30176C16.1285 6.0293 16.6562 6.02637 16.9792 6.30176L19.7569 8.64551C20.0833 8.9209 20.0833 9.36621 19.7569 9.63867L16.9792 11.9824C16.6528 12.2578 16.125 12.2578 15.8021 11.9824C15.4792 11.707 15.4757 11.2617 15.8021 10.9893L17.1563 9.84668L13.3333 9.84375ZM13.3333 3.75H8.88889V0L13.3333 3.75Z" fill="currentColor"/></svg> Exportar</button>
                    </form>
                </div>
            </div>

            <div class="toolbar__section toolbar__section--right">
                <div class="toolbar__actions">
                    @if(Auth::user()->hasAnyRole(['master']))
                        <button type="button" class="btn btn--primary" id="btnAgregarDocente" data-modal="modalRegistroDocente" data-registro-docente-form-url="{{ route('control.teachers.form') }}" aria-label="Abrir formulario de registro de docente">
                            + Agregar Docente
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Tablas-->
    <div class="Table-view">
        <table class="tabla-base tabla-rayas tabla-bordes tabla-docentes">
            <thead class="encabezado-tabla">
                <tr>
                    <th>RFC</th>
                    <th>Carrera</th>
                    <th>Clasificación</th>
                    <th>Nombre</th>
                    <th>Apellido<br>Paterno</th>
                    <th>Apellido<br>Materno</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="cuerpo-tabla">
                @foreach ($dataList as $user)
                    <tr>
                        <td data-label="RFC">{{ $user->RFC ?? '—' }}</td>
                        @php
                            $teachingCareers = $user->teachingCareers->pluck('name')->filter()->values();
                            $careerFullText = $teachingCareers->isNotEmpty()
                                ? $teachingCareers->implode(', ')
                                : ($user->academicProfile?->career?->name ?? 'Sin datos');
                            $careerShortText = $teachingCareers->count() > 1
                                ? ($teachingCareers->first() . '...')
                                : $careerFullText;
                            $teachingClassifications = $user->teachingCareers
                                ->pluck('classification.name')
                                ->filter()
                                ->unique()
                                ->values();
                            $classificationFullText = $teachingClassifications->isNotEmpty()
                                ? $teachingClassifications->implode(', ')
                                : ($user->academicProfile?->career?->classification?->name ?? 'Sin datos');
                            $classificationShortText = $teachingClassifications->count() > 1
                                ? ($teachingClassifications->first() . '...')
                                : $classificationFullText;
                        @endphp
                        <td data-label="Carrera" title="{{ $careerFullText }}">{{ $careerShortText }}</td>
                        <td data-label="Clasificación" title="{{ $classificationFullText }}">{{ $classificationShortText }}</td>
                        <td data-label="Nombre">{{ $user->nombre }}</td>
                        <td data-label="Paterno">{{ $user->apellido_paterno }}</td>
                        <td data-label="Materno">{{ $user->apellido_materno }}</td>
                        <td data-label="Estado">{{ isset($user->is_active) && $user->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td data-label="Acciones" class="data-actions-cell">
                            <a
                                href="{{ route('control.teachers.horarios', $user->id) }}"
                                class="data-action-btn data-btn-view data-btn-clock"
                                title="Horarios"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false" style="width:27px;height:27px;fill:#092034">
                                    <path d="M320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64zM296 184L296 320C296 328 300 335.5 306.7 340L402.7 404C413.7 411.4 428.6 408.4 436 397.3C443.4 386.2 440.4 371.4 429.3 364L344 307.2L344 184C344 170.7 333.3 160 320 160C306.7 160 296 170.7 296 184z"/>
                                </svg>
                            </a>
                            <button type="button" class="data-action-btn data-btn-view" title="Ver" data-teacher-view-url="{{ route('control.teachers.show', $user->id) }}" data-teacher-name="{{ trim($user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno) }}"><img src="{{ asset('images/icons/eye-solid-full-gold.svg') }}" alt="" style="width:27px;height:27px" loading="lazy"></button>
                            <button type="button" class="data-action-btn data-btn-edit" title="Editar" data-teacher-edit-url="{{ route('control.teachers.edit', $user->id) }}" data-teacher-name="{{ trim($user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno) }}"><img src="{{asset('images/icons/pen-to-square-solid-full.svg')}}" alt="" style="width:27;height:27px" loading="lazy"></button>
                            <form action="{{ route('control.teachers.destroy', $user->id) }}" method="POST" class="data-action-form js-docente-delete-form" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="data-action-btn data-btn-delete" aria-label="Eliminar docente"><img src="{{asset('images/icons/Vector.svg')}}" alt="" style="width:38;height:25px" loading="lazy"></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
(function () {
    const searchInput = document.getElementById('docentesSearchNombre');
    const exportForm = document.querySelector('.toolbar__export-form');
    const exportSearch = document.getElementById('docentesExportSearchQuery');
    if (!searchInput || !exportForm || !exportSearch) return;

    exportForm.addEventListener('submit', function () {
        exportSearch.value = (searchInput.value || '').trim();
    });
})();
</script>
@endsection
