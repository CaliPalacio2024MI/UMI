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
            <label>Modalidad</label>
            <div style="display:flex; gap:20px">
                <label>
                    <input type="radio" name="modality" value="virtual"
                        {{ old('modality', $course->modality) === 'virtual' ? 'checked' : '' }} required>
                    Virtual
                </label>

                <label>
                    <input type="radio" name="modality" value="presencial"
                        {{ old('modality', $course->modality) === 'presencial' ? 'checked' : '' }} required>
                    Presencial
                </label>

                <label>
                    <input type="radio" name="modality" value="hibrida"
                        {{ old('modality', $course->modality) === 'hibrida' ? 'checked' : '' }} required>
                    Hibrida
                </label>
            </div>
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

            {{-- MULTI DEPARTAMENTOS --}}
            <div class="form-group">
                <label>Dirigido a Departamentos</label>

                <select name="department_ids[]" multiple required>
                    @foreach($currentInstitution->departments as $department)
                        <option value="{{ $department->id }}"
                            {{ collect(old('department_ids', $course->departments->pluck('id')))
                                ->contains($department->id) ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>

                <small style="color:#666">Puedes seleccionar uno o varios departamentos</small>
            </div>
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

        {{-- CERTIFICADO --}}
        <h2 class="section-title">Configuración del Certificado</h2>

        <div class="form-group">
            <label class="file-upload-label">Imagen de Fondo</label>
            <input type="file" name="cert_bg_image">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="file-upload-label">Firma 1</label>
                <input type="file" name="cert_sig_1_image">
                <input type="text" name="cert_sig_1_name"
                       value="{{ old('cert_sig_1_name', $course->cert_sig_1_name) }}"
                       placeholder="Nombre / Cargo">
            </div>

            <div class="form-group">
                <label class="file-upload-label">Firma 2</label>
                <input type="file" name="cert_sig_2_image">
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
@endsection
