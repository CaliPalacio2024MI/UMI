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

        {{-- Modalidad --}}
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
            Híbrido
        </option>
    </select>
</div>

        {{-- UNIVERSIDAD --}}
        @if ($currentInstitution->name === 'Universidad Mundo Imperial')

            <div class="form-row">
                <div class="form-group">
                    <label>Horas</label>
                    <input type="number" name="hours"
                           value="{{ old('hours', $course->hours) }}" required>
                </div>

                <div class="form-group">
                    <label>Créditos</label>
                    <input type="number" name="credits"
                           value="{{ old('credits', $course->credits) }}" required>
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
            <div class="form-group">
                <label>Horas</label>
                <input type="number" name="hours"
                       value="{{ old('hours', $course->hours) }}" required>
            </div>

            <input type="hidden" name="credits" value="0">

           
        @endif

        {{-- IMAGEN --}}
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
                           target="_blank" class="btn-secondary">
                            Ver Guía Actual
                        </a>
                    </div>
                @endif

                <input type="file" id="guide_material" name="guide_material"
                       accept=".pdf,.doc,.docx,.ppt,.pptx">
            </div>
        </div>

        {{-- CERTIFICADO - IMAGEN DE FONDO --}}
<div class="form-group">
    <label class="file-upload-label" for="cert_bg_image">
        Imagen de Fondo del Certificado
    </label>
    <input 
        type="file" 
        id="cert_bg_image" 
        name="cert_bg_image" 
        accept="image/*">
    
    @if ($course->cert_bg_image_path ?? $course->cert_background_path)
        <div class="current-file-box" style="margin-top: 10px;">
            <small>Imagen actual:</small><br>
            <img src="{{ asset('storage/' . ($course->cert_bg_image_path ?? $course->cert_background_path)) }}"
                 style="max-width: 250px; border-radius: 8px; border: 1px solid #ddd;">
        </div>
    @endif
</div>

        <div class="form-row">
    <div class="form-group">
        <label class="file-upload-label" for="cert_sig_1_image">Firma 1</label>
        <input type="file" id="cert_sig_1_image" name="cert_sig_1_image" accept="image/png,image/jpeg">
        <input type="text" name="cert_sig_1_name" 
               value="{{ old('cert_sig_1_name', $course->cert_sig_1_name) }}"
               placeholder="Nombre / Cargo">
    </div>

    <div class="form-group">
        <label class="file-upload-label" for="cert_sig_2_image">Firma 2</label>
        <input type="file" id="cert_sig_2_image" name="cert_sig_2_image" accept="image/png,image/jpeg">
        <input type="text" name="cert_sig_2_name" 
               value="{{ old('cert_sig_2_name', $course->cert_sig_2_name) }}"
               placeholder="Nombre / Cargo">
    </div>
</div>

        {{-- BOTONES --}}
        <div class="form-row">
            <button class="btn-submit" type="submit" name="action" value="save_and_exit">
                Guardar Cambios
            </button>

            <button class="btn-submit" type="submit" name="action" value="save_and_continue">
                Guardar y Editar Temas →
            </button>
        </div>

    </form>
</div>
<script>
document.querySelectorAll('input[type="file"]').forEach(input => {
    const label = document.querySelector(`label[for="${input.id}"]`);
    
    input.addEventListener('change', () => {
        if (input.files[0]) {
            // Opcional: mostrar nombre debajo
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
</script>
@endsection
