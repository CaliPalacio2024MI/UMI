@extends('layouts.app')

@section('title', 'Crear Nuevo Curso - ' . session('active_institution_name'))


@section('content')
@vite(['resources/css/Cursos/createCourses.css'])

<div class="create-course-container">
    <div class="course-form-head">
        <h1 class="page-title">Crear Nuevo Curso</h1>
        {{-- Salir: regresa a Cursos sin guardar --}}
        <a href="{{ route('Cursos.index') }}" class="btn-salir-curso">
            <i class="fas fa-arrow-left"></i>
            Salir
        </a>
    </div>

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

        {{-- PROPIEDAD(ES): según las que el usuario tiene asignadas (su propiedad + propiedades adicionales) --}}
        <div class="form-group">
            <label>{{ $unitOptions->count() > 1 ? 'Propiedad(es) del curso' : 'Propiedad del curso' }}</label>
            @if($unitOptions->count() > 1)
                {{-- Desplegable: solo las propiedades que esta persona tiene asignadas --}}
                <div class="unit-dd" id="unitDd">
                    <button type="button" class="unit-dd-toggle" onclick="document.getElementById('unitDd').classList.toggle('is-open')">
                        <span id="unitDdLabel">Selecciona la propiedad</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="unit-dd-menu">
                        @foreach($unitOptions as $unit)
                            <label class="unit-dd-item">
                                <input type="checkbox" name="institution_ids[]" value="{{ $unit->id }}" data-name="{{ $unit->name }}"
                                    @checked(in_array($unit->id, old('institution_ids', $selectedUnits)))>
                                <span>{{ $unit->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                @php $unicaUnidad = $unitOptions->first(); @endphp
                <input type="text" value="{{ $unicaUnidad->name ?? session('active_institution_name') }}" disabled>
                <input type="hidden" name="institution_ids[]" value="{{ $unicaUnidad->id ?? session('active_institution_id') }}">
            @endif
        </div>


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

    {{-- Carrera: se eligió quitar este campo; la carrera se define en la lista de participantes --}}

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


<style>
    .course-form-head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .course-form-head .page-title { margin: 0; }
    .btn-salir-curso { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:8px; background:#2f4b7c; color:#fff !important; font-weight:700; text-decoration:none; }
    .btn-salir-curso:hover { background:#0d2240; }
    .unit-dd { position: relative; }
    .unit-dd-toggle { width:100%; display:flex; justify-content:space-between; align-items:center; gap:10px; padding:12px 16px; border:1px solid #cbd5e0; border-radius:8px; background:#fff; font-size:1rem; color:#1e293b; cursor:pointer; text-align:left; }
    .unit-dd.is-open .unit-dd-toggle { border-color:#2f4b7c; }
    .unit-dd-menu { display:none; position:absolute; z-index:50; left:0; right:0; top:calc(100% + 4px); background:#fff; border:1px solid #cbd5e0; border-radius:8px; box-shadow:0 10px 25px rgba(13,34,64,.15); padding:6px; max-height:260px; overflow-y:auto; }
    .unit-dd.is-open .unit-dd-menu { display:block; }
    .unit-dd-item { display:flex !important; align-items:center; gap:10px; padding:10px 12px; border-radius:6px; cursor:pointer; font-weight:500 !important; margin:0 !important; }
    .unit-dd-item:hover { background:#eef2f8; }
    .unit-dd-item input { width:auto !important; margin:0; }
</style>

<script>
// Desplegable de propiedades: muestra las elegidas y se cierra al dar clic fuera
(function () {
    const dd = document.getElementById('unitDd');
    if (!dd) return;
    const label = document.getElementById('unitDdLabel');
    const checks = [...dd.querySelectorAll('input[type="checkbox"]')];
    function actualizar() {
        const nombres = checks.filter((c) => c.checked).map((c) => c.dataset.name);
        label.textContent = nombres.length ? nombres.join(', ') : 'Selecciona la propiedad';
    }
    checks.forEach((c) => c.addEventListener('change', actualizar));
    document.addEventListener('click', (e) => { if (!dd.contains(e.target)) dd.classList.remove('is-open'); });
    actualizar();
})();
</script>
@endsection
