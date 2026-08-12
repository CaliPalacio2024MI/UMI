@extends('layouts.app')

@section('title', 'Editar Curso - ' . $course->title)

@section('content')
@vite(['resources/css/Cursos/editCourses.css'])

<div class="create-course-container">
    <h1 class="page-title">Editar Curso</h1>

    {{-- ERRORES --}}
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

    <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <input type="hidden" name="institution_id" value="{{ $currentInstitution->id }}">

        {{-- TÍTULO --}}
        <div class="form-group">
            <label>Título del Curso</label>
            <input type="text" name="title"
                   value="{{ old('title', $course->title) }}" required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="description" rows="4" required>{{ old('description', $course->description) }}</textarea>
        </div>

        {{-- MODALIDAD --}}
        <div class="form-group">
            <label for="modality">Modalidad</label>
            <select id="modality" name="modality" required>
                <option value="" disabled>Selecciona la modalidad</option>

                <option value="presencial"
                    {{ old('modality', $course->modality) == 'presencial' ? 'selected' : '' }}>
                    Presencial
                </option>

                <option value="virtual"
                    {{ old('modality', $course->modality) == 'virtual' ? 'selected' : '' }}>
                    Virtual
                </option>

                <option value="hibrida"
                    {{ old('modality', $course->modality) == 'hibrida' ? 'selected' : '' }}>
                    Híbrida
                </option>
            </select>
        </div>



        {{-- PONDERACIÓN --}}
        <div id="ponderacionContainer" style="display:none; margin-top:15px;">
            <h3>Ponderación del curso</h3>

            <div class="form-row">
                <div class="form-group flex-1">
                    <label>Virtual (%)</label>
                    <input type="number"
                           name="virtual_percentage"
                           min="0"
                           max="100"
                           value="{{ old('virtual_percentage', $course->virtual_percentage ?? '') }}">
                </div>

                <div class="form-group flex-1">
                    <label>Presencial (%)</label>
                    <input type="number"
                           name="presencial_percentage"
                           min="0"
                           max="100"
                           value="{{ old('presencial_percentage', $course->presencial_percentage ?? '') }}">
                </div>
            </div>
        </div>

        {{-- HÍBRIDO --}}
        <div id="hibrido_section" style="display:none;">
            <div class="form-group">
                <label for="courses_select">Seleccionar cursos afiliados</label>

                <select id="courses_select"
                        name="selected_courses[]"
                        multiple
                        class="form-control"
                        style="min-height:150px;">

                    @foreach($courses as $c)
                        <option value="{{ $c->id }}"
                                data-hours="{{ $c->hours }}"
                                {{ in_array($c->id, old('selected_courses', $selectedCourses ?? [])) ? 'selected' : '' }}>
                            {{ $c->title }} ({{ $c->hours }} hrs) - {{ ucfirst($c->modality) }}
                        </option>
                    @endforeach

                </select>

                <small class="text-muted">
                    Mantén presionada la tecla CTRL para seleccionar múltiples cursos
                </small>
            </div>

            <div class="form-group">
                <label>Total de horas del curso híbrido</label>
                <input type="number"
                       id="total_hours"
                       class="form-control"
                       readonly
                       value="{{ old('hours', $course->hours) }}">

                <small class="text-muted">
                    La suma automática de las horas de los cursos seleccionados
                </small>
            </div>
        </div>

        {{-- UNIVERSIDAD --}}
        @if ($currentInstitution->name === 'Universidad Mundo Imperial')

            <div class="form-row">
                <div class="form-group" id="hours_manual_container">
                    <label id="hours_label">Horas</label>
                    <input type="number"
                           id="hours"
                           name="hours"
                           value="{{ old('hours', $course->hours) }}"
                           required>
                </div>

                <div class="form-group">
                    <label>Créditos</label>
                    <input type="number"
                           name="credits"
                           value="{{ old('credits', $course->credits) }}"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label>Carrera</label>
                <select name="career_id" required>
                    @foreach($currentInstitution->careers as $career)
                        <option value="{{ $career->id }}"
                            {{ old('career_id', $course->career_id) == $career->id ? 'selected' : '' }}>
                            {{ $career->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        @else

            {{-- CORPORATIVO --}}
            <div class="form-group" id="hours_container">
                <label>Horas</label>
                <input type="number"
                       id="hours"
                       name="hours"
                       value="{{ old('hours', $course->hours) }}"
                       required>
            </div>

            <input type="hidden" name="credits" value="0">

        @endif

        {{-- IMAGEN Y MATERIAL --}}
        <div class="form-row">
            <div class="form-group">
                <label class="file-upload-label" for="image">Cambiar Imagen</label>
                <input type="file" id="image" name="image" accept="image/*">

                @if ($course->image_path)
                    <div class="current-file-box">
                        <img src="{{ asset('storage/' . $course->image_path) }}"
                             style="max-width:180px;border-radius:8px;margin-top:10px;">
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="file-upload-label" for="guide_material">Material de Guía</label>

                @if ($course->guide_material_path)
                    <div class="current-file-box">
                        <a href="{{ asset('storage/' . $course->guide_material_path) }}"
                           target="_blank"
                           class="btn-secondary">
                            Ver Guía Actual
                        </a>
                    </div>
                @endif

                <input type="file"
                       id="guide_material"
                       name="guide_material"
                       accept=".pdf,.doc,.docx,.ppt,.pptx">
            </div>
        </div>

        {{-- CERTIFICADO --}}
        <div class="form-group">
            <label class="file-upload-label" for="cert_bg_image">
                Imagen de Fondo del Certificado
            </label>

            <input type="file"
                   id="cert_bg_image"
                   name="cert_bg_image"
                   accept="image/*">

            @if ($course->cert_bg_image_path ?? $course->cert_background_path)
                <div class="current-file-box" style="margin-top:10px;">
                    <small>Imagen actual:</small><br>
                    <img src="{{ asset('storage/' . ($course->cert_bg_image_path ?? $course->cert_background_path)) }}"
                         style="max-width:250px; border-radius:8px; border:1px solid #ddd;">
                </div>
            @endif
        </div>

        {{-- FIRMAS --}}
        <div class="form-row">
            <div class="form-group">
                <label class="file-upload-label" for="cert_sig_1_image">Firma 1</label>
                <input type="file"
                       id="cert_sig_1_image"
                       name="cert_sig_1_image"
                       accept="image/png,image/jpeg">

                <input type="text"
                       name="cert_sig_1_name"
                       value="{{ old('cert_sig_1_name', $course->cert_sig_1_name) }}"
                       placeholder="Nombre / Cargo">
            </div>

            <div class="form-group">
                <label class="file-upload-label" for="cert_sig_2_image">Firma 2</label>
                <input type="file"
                       id="cert_sig_2_image"
                       name="cert_sig_2_image"
                       accept="image/png,image/jpeg">

                <input type="text"
                       name="cert_sig_2_name"
                       value="{{ old('cert_sig_2_name', $course->cert_sig_2_name) }}"
                       placeholder="Nombre / Cargo">
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="form-row">
            <button class="btn-submit"
                    type="submit"
                    name="action"
                    value="save_and_exit">
                Guardar Cambios
            </button>
            @if(strtolower($course->modality) == 'virtual')
                <button class="btn-submit"
                    type="submit"
                    name="action"
                    value="save_and_continue">
                    Guardar y Editar Temas →
                </button>
            @endif
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ============================
    // MOSTRAR NOMBRE DE ARCHIVOS
    // ============================
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', () => {
            if (input.files[0]) {
                let nameTag = input.nextElementSibling;

                if (!nameTag || !nameTag.classList.contains('file-name')) {
                    nameTag = document.createElement('p');
                    nameTag.className = 'file-name';
                    input.after(nameTag);
                }

                nameTag.textContent = input.files[0].name;
            }
        });
    });

    // ============================
    // MODALIDAD HÍBRIDA
    // ============================
    const modalitySelect = document.getElementById('modality');

    const ponderacion = document.getElementById('ponderacionContainer');
    const hibridoSection = document.getElementById('hibrido_section');
    const instructorPresencialContainer =
    document.getElementById('instructor_presencial_container');

    const hoursContainer =
        document.getElementById('hours_container')
        || document.getElementById('hours_manual_container');

    const hoursInput = document.getElementById('hours');
    const coursesSelect = document.getElementById('courses_select');
    const totalHoursInput = document.getElementById('total_hours');

function toggleHibrido() {

    if (!modalitySelect) return;

    // =========================
    // HÍBRIDA
    // =========================
    if (modalitySelect.value === 'hibrida') {

        if (ponderacion) {
            ponderacion.style.display = 'block';
        }

        if (hibridoSection) {
            hibridoSection.style.display = 'block';
        }

        if (hoursContainer) {
            hoursContainer.style.display = 'none';
        }

    } else {

        if (ponderacion) {
            ponderacion.style.display = 'none';
        }

        if (hibridoSection) {
            hibridoSection.style.display = 'none';
        }

        if (hoursContainer) {
            hoursContainer.style.display = 'block';
        }
    }

    // =========================
    // INSTRUCTOR PRESENCIAL
    // =========================
    if (instructorPresencialContainer) {

        if (modalitySelect.value === 'presencial') {

            instructorPresencialContainer.style.display = 'block';

        } else {

            instructorPresencialContainer.style.display = 'none';

        }
    }
}

    function calcularHorasHibridas() {
        if (!coursesSelect) return;

        let total = 0;

        Array.from(coursesSelect.selectedOptions).forEach(option => {
            total += parseInt(option.dataset.hours || 0);
        });

        if (totalHoursInput) totalHoursInput.value = total;

        if (hoursInput && modalitySelect.value === 'hibrida') {
            hoursInput.value = total;
        }
    }

    if (modalitySelect) {
        modalitySelect.addEventListener('change', () => {
            toggleHibrido();
            calcularHorasHibridas();
        });

        toggleHibrido();
    }

    if (coursesSelect) {
        coursesSelect.addEventListener('change', calcularHorasHibridas);
        calcularHorasHibridas();
    }

});
</script>

@endsection
