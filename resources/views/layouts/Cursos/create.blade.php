@extends('layouts.app')

@section('title', 'Crear Nuevo Curso - ' . session('active_institution_name'))


@section('content')
@vite(['resources/css/Cursos/createCourses.css'])

<div class="create-course-container">
    <h1 class="page-title">Crear Nuevo Curso</h1>

    @if ($errors->any())
        <div class="alert-error">
            <strong>¡Ups! Hubo algunos problemas con tu entrada.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="institution_id" value="{{ $currentInstitution->id }}">

        {{-- Título --}}
        <div class="form-group">
            <label for="title">Título del Curso</label>
            <input type="text" id="title" name="title" required value="{{ old('title') }}">
        </div>

        {{-- Descripción --}}
        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
        </div>

{{-- Modalidad--}}
<div class="form-group">
    <label for="modality">Modalidad</label>
    <select id="modality" name="modality" required>
        <option value="" disabled selected>Selecciona la modalidad</option>
        <option value="presencial" {{ old('modality') == 'presencial' ? 'selected' : ''}}>Presencial</option>
        <option value="virtual" {{ old('modality') == 'virtual' ? 'selected' : ''}}>Virtual</option>
        <option value="hibrida" {{ old('modality') == 'hibrida' ? 'selected' : ''}}>Híbrida</option>
    </select>
</div>

{{-- PONDERACIÓN --}}
<div id="ponderacionContainer" style="display:none; margin-top:15px;">
    <h3>Ponderación del curso</h3>

    <div class="form-row">
        <div class="form-group flex-1">
            <label>Virtual (%)</label>
            <input type="number" name="virtual_percentage" min="0" max="100">
        </div>

        <div class="form-group flex-1">
            <label>Presencial (%)</label>
            <input type="number" name="presencial_percentage" min="0" max="100">
        </div>
    </div>
</div>
{{-- HÍBRIDO --}}
<div id="hibrido_section" style="display:none;">
    <div class="form-group">
        <label for="courses_select">Seleccionar cursos afiliados</label>
        <select id="courses_select" name="selected_courses[]" multiple class="form-control" style="min-height: 150px;">
            @foreach($courses as $c)
                <option value="{{ $c->id }}" data-hours="{{ $c->hours }}">
                    {{ $c->title }} ({{ $c->hours }} hrs) - {{ ucfirst($c->modality) }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Mantén presionada la tecla CTRL para seleccionar múltiples cursos</small>
    </div>

</div>
      {{-- Campos especiales para Universidad Mundo Imperial --}}
    @if ($currentInstitution->name == 'Universidad Mundo Imperial')

<div class="form-row">
    <div class="form-group flex-1" id="hours_container">
        <label for="hours">Horas</label>
        <input type="number"
               name="hours"
               id="hours"
               class="form-control"
               value="{{ old('hours') }}">
    </div>
        <div class="form-group flex-1">
            <label for="credits">Créditos</label>
            <input type="number" name="credits" id="credits" required value="{{ old('credits') }}">
        </div>
    </div>

    <div class="form-group">
        <label for="career_id">Carrera</label>
        <select name="career_id" id="career_id" required>
            <option value="" disabled selected>Selecciona la Carrera</option>
            @foreach($currentInstitution->careers as $career)
                <option value="{{ $career->id }}" {{ old('career_id') == $career->id ? 'selected' : '' }}>
                    {{ $career->name }}
                </option>
            @endforeach
        </select>
    </div>

    @else

        {{-- SOLO HORAS --}}
        <div class="form-group flex-1" id="hours_container">
            <label for="hours">Horas</label>
            <input type="number" name="hours" id="hours" class="form-control" value="{{ old('hours') }}">
        </div>

    @endif

        <div class="form-row">
            <div class="form-group" >
                <label class="file-upload-label" for="image">Imagen del Curso</label>
                <input type="file" id="image" name="image" accept="image/*">
                <p id="image-name-image" class="file-name"></p>
            </div>

            <div class="form-group" >
                <label class="file-upload-label" for="guide_material">Material de Guía (PDF, Word, PPT)</label>
                <input type="file" id="guide_material" name="guide_material" accept=".pdf,.doc,.docx,.ppt,.pptx">
                <p id="image-name-guide" class="file-name"></p>
            </div>
        </div>

        {{-- Certificado --}}
        <h2 class="section-title">Configuración del Certificado</h2>
        <div class="form-group">
            <label class="file-upload-label" for="cert_bg_image">Imagen de Fondo </label>
            <input type="file" id="cert_bg_image" name="cert_bg_image" accept="image/*">
            <p id="image-name-bg" class="file-name"></p>
        </div>
        <div class="form-row" >
            <div class="form-group">
                <label class="file-upload-label"for="cert_sig_1_image">Firma 1 (Subir imagen de la Firma 1)</label>
                <input type="file" id="cert_sig_1_image" name="cert_sig_1_image" accept="image/png" >
                <input type="text" name="cert_sig_1_name" placeholder="Nombre/Cargo de la Firma 1"  value="{{ old('cert_sig_1_name') }}">
                <p id="image-name-sig1" class="file-name"></p>
            </div>

            <div class="form-group">
                <label class="file-upload-label"for="cert_sig_2_image">Firma 2 (Subir imagen de la Firma 2)</label>
                <input type="file" id="cert_sig_2_image" name="cert_sig_2_image" accept="image/png" >
                <input type="text" name="cert_sig_2_name" placeholder="Nombre/Cargo de la Firma 2"  value="{{ old('cert_sig_2_name') }}">
                <p id="image-name-sig2" class="file-name"></p>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            Guardar Curso
        </button>
    </form>
</div>


<script>
(function() {

    /* ============================
       1. Datos desde PHP
    ============================ */
    const departmentWorkstations = @json($departmentWorkstationsMap);

    /* ============================
       2. Elementos del DOM
    ============================ */
    const departmentSelect  = document.getElementById('department_id');
    const workstationSelect = document.getElementById('workstation_id');

    const modalitySelect    = document.getElementById('modality');
    const ponderacion       = document.getElementById('ponderacionContainer');
    const hibridoSection    = document.getElementById('hibrido_section');

    const hoursContainer    = document.getElementById('hours_container')
                          || document.getElementById('hours_manual_container');

    const hoursInput        = document.getElementById('hours'); //  EL QUE SE GUARDA
    const coursesSelect     = document.getElementById('courses_select');

    /* ============================
       3. Cargar puestos
    ============================ */
    function populateWorkstations(departmentId) {

        if (!workstationSelect) return;

        workstationSelect.innerHTML = '';
        const defaultOption = new Option('', '', true, true);

        if (departmentId && departmentWorkstations[departmentId]) {

            workstationSelect.disabled = false;
            defaultOption.textContent = 'Selecciona el Puesto (Opcional)';
            workstationSelect.appendChild(defaultOption);

            workstationSelect.appendChild(
                new Option("Todos los Puestos del Departamento", "")
            );


            departmentWorkstations[departmentId].forEach(w => {
                workstationSelect.appendChild(new Option(w.name, w.id));
            });

        } else {

            workstationSelect.disabled = true;
            defaultOption.textContent = 'Primero selecciona un departamento';
            workstationSelect.appendChild(defaultOption);
        }
    }


    if (departmentSelect) {
        departmentSelect.addEventListener('change', e => {
            populateWorkstations(e.target.value);
        });

        if (departmentSelect.value) {
            populateWorkstations(departmentSelect.value);


            const oldWorkstation = "{{ old('workstation_id') }}";
            if (oldWorkstation) workstationSelect.value = oldWorkstation;
        }
    }

    /* ============================
       4. Mostrar nombre de archivos

       ============================ */
    const fileInputs = [
        { input: 'image',             label: 'image-name-image' },
        { input: 'guide_material',    label: 'image-name-guide' },
        { input: 'cert_bg_image',     label: 'image-name-bg' },
        { input: 'cert_sig_1_image',  label: 'image-name-sig1' },
        { input: 'cert_sig_2_image',  label: 'image-name-sig2' }
    ];

    fileInputs.forEach(f => {
        const input = document.getElementById(f.input);
        const label = document.getElementById(f.label);

        if (input && label) {
            input.addEventListener('change', () => {
                label.textContent = input.files[0]?.name || "Ningún archivo seleccionado";
            });
        }
    });

    /* ============================
       5. MODALIDAD (HÍBRIDO)
    ============================ */
    function toggleHibrido() {

        if (!modalitySelect) return;
if (modalitySelect.value === 'hibrida') {
    if (ponderacion) ponderacion.style.display = 'block';
    if (hibridoSection) hibridoSection.style.display = 'block';

    if (hoursContainer) hoursContainer.style.display = 'block';
    if (hoursInput) hoursInput.readOnly = true;

} else {
    if (ponderacion) ponderacion.style.display = 'none';
    if (hibridoSection) hibridoSection.style.display = 'none';

    if (hoursContainer) hoursContainer.style.display = 'block';
    if (hoursInput) {
        hoursInput.readOnly = false;
        hoursInput.value = '';
    }

    if (coursesSelect) coursesSelect.selectedIndex = -1;
}
    }

    if (modalitySelect) {
        modalitySelect.addEventListener('change', toggleHibrido);
        toggleHibrido(); // ejecutar al cargar
    }

    /* ============================
       6. SUMA AUTOMÁTICA DE HORAS
    ============================ */
if (coursesSelect) {
    coursesSelect.addEventListener('change', function () {
        let total = 0;

        Array.from(this.selectedOptions).forEach(option => {
            total += Number(option.dataset.hours || 0);
        });

        if (hoursInput) {
            hoursInput.value = total;
        }
    });
}
})();
</script>

@endsection
