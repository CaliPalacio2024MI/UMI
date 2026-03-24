@extends('layouts.app')

@section('title', 'Editar Curso - ' .$course->title) {{-- Titulo Dinamico --}}

@section('content')
@vite(['resources/css/Cursos/editCourses.css'])

<div class="create-course-container">
    <h1 class="page-title">Editar Curso</h1>

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

        {{-- TÍTULO --}}
        <div class="form-group">
            <label for="title">Título del Curso</label>
            <input type="text" id="title" name="title"
                   value="{{ old('title', $course->title) }}" required>
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" rows="4" required>{{ old('description', $course->description) }}</textarea>
        </div>

        <input type="hidden" name="institution_id" value="{{ $currentInstitution->id }}">

        {{-- MODALIDAD --}}
        <div class="form-group">
            <label for="modality">Modalidad</label>
            <select id="modality" name="modality" required>
                <option value="" disabled>Selecciona la modalidad</option>

                <option value="presencial" {{ old('modality', $course->modality) == 'presencial' ? 'selected' : ''}}>Presencial</option>
                <option value="virtual" {{ old('modality', $course->modality) == 'virtual' ? 'selected' : ''}}>Virtual</option>
                <option value="hibrido" {{ old('modality', $course->modality) == 'hibrido' ? 'selected' : ''}}>Hibrido</option>
            </select>
        </div>
        

        {{-- UNIVERSIDAD --}}
        @if ($currentInstitution->name == 'Universidad Mundo Imperial')

            <div class="form-row">
                <div class="form-group">
                    <label for="hours">Horas</label>
                    <input type="number" name="hours" id="hours"
                           value="{{ old('hours', $course->hours) }}" required>
                </div>

                <div class="form-group">
                    <label for="credits">Créditos</label>
                    <input type="number" name="credits" id="credits"
                           value="{{ old('credits', $course->credits) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="career_id">Carrera</label>
                <select name="career_id" id="career_id" required>
                    <option value="" disabled>Selecciona la Carrera</option>
                    @foreach($currentInstitution->careers as $career)
                        <option value="{{ $career->id }}"
                            @if(old('career_id', $selectedFilters['career_id']) == $career->id) selected @endif>
                            {{ $career->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        @else
            {{-- CORPORATIVO --}}
            <div class="form-group">
                <label for="hours">Horas</label>
                <input type="number" name="hours" id="hours"
                       value="{{ old('hours', $course->hours) }}" required>
            </div>

            <input type="hidden" name="credits" value="0">

            <div class="form-row">
                <div class="form-group">
                    <label for="department_id">Departamento</label>
                    <select name="departments[]" id="department_id" multiple required>
                        @foreach($currentInstitution->departments as $department)
                            <option value="{{ $department->id }}"
                                @if(collect(old('departments', $course->departments->pluck('id')->toArray()))->contains($department->id)) selected @endif>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="workstation_id">Puesto</label>
                    <select name="workstations[]" id="workstation_id" multiple
                        @if(empty(old('departments', $course->departments->pluck('id')->toArray()))) disabled @endif>
                        <option value="">Todos los Puestos</option>
                    </select>
                </div>
            </div>
        @endif

        {{-- IMAGEN --}}
        

        <div class="form-row">
            <div class="form-group">
                <label class="file-upload-label" for="image">Cambiar Imagen</label>
                <input type="file" id="image" name="image" accept="image/*">
                <p id="image-name-image" class="file-name"></p>
            </div>

            <div class="form-group">
                <label class="file-upload-label" for="guide_material">Material de Guía (PDF, Word, PPT)</label>

                @if ($course->guide_material_path)
                    <div class="current-file-box">
                        <a href="{{ asset('storage/' . $course->guide_material_path) }}" 
                        target="_blank" 
                        class="btn-secondary">
                             Ver Guía Actual
                        </a>
                    </div>
                @endif

                <input type="file" id="guide_material" name="guide_material" accept=".pdf,.doc,.docx,.ppt,.pptx">
                <p id="image-name-guide" class="file-name"></p>
            </div>

        </div>

        {{-- CERTIFICADO --}}
        <h2 class="section-title">Configuración del Certificado</h2>

        <div class="form-group">
            <label class="file-upload-label" for="cert_bg_image">Imagen Fondo</label>
            <input type="file" id="cert_bg_image" name="cert_bg_image">
            <p id="image-name-bg" class="file-name"></p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="file-upload-label" for="cert_sig_1_image">Firma 1</label>
                <input type="file" id="cert_sig_1_image" name="cert_sig_1_image">
                <input type="text" name="cert_sig_1_name" placeholder="Nombre Firma 1">
            </div>

            <div class="form-group">
                <label class="file-upload-label" for="cert_sig_2_image">Firma 2</label>
                <input type="file" id="cert_sig_2_image" name="cert_sig_2_image">
                <input type="text" name="cert_sig_2_name" placeholder="Nombre Firma 2">
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="form-row">
            <button type="submit" class="btn-submit" name="action" value="save_and_exit">
                Guardar Cambios
            </button>

            <button type="submit" class="btn-submit" name="action" value="save_and_continue">
                Guardar y Editar Temas →
            </button>
        </div>

    </form>
</div>

@endsection


@push('scripts')
<script>

    // 1. Datos pasados desde el controlador
    const departmentWorkstations = @json($departmentWorkstationsMap);
    
    // 2. Referencias a los <select>
    const departmentSelect = document.getElementById('department_id');
    const workstationSelect = document.getElementById('workstation_id');
    
    // 3. ID del puesto que ya estaba guardado (si existe)
    let selectedWorkstationIds = @json(old('workstations', $course->workstations->pluck('id')->toArray()));

    // 4. Función para poblar los puestos
    function populateWorkstations(selectedDepartmentIds) {
        if (!workstationSelect) return; 

        // Limpiamos opciones anteriores (dejamos la opción "Todos los puestos")
        while (workstationSelect.options.length > 1) {
            workstationSelect.remove(1);
        }

        if (selectedDepartmentIds && selectedDepartmentIds.length > 0) {
            workstationSelect.disabled = false;
            
            let allWorkstations = [];

            selectedDepartmentIds.forEach(function(deptId){
                if (departmentWorkstations[deptId]) {
                    allWorkstations = allWorkstations.concat(departmentWorkstations[deptId]);
                }
            });
            
            allWorkstations.forEach(function (workstation) {
                const option = new Option(workstation.name, workstation.id);
                // Si este puesto es el que estaba guardado, lo seleccionamos
                if (selectedWorkstationIds.includes(workstation.id)) {
                    option.selected = true;
                }
                workstationSelect.add(option);
            });
        } else {
            workstationSelect.disabled = true;
        }
    }

    // 5. Escuchar cambios en el <select> de departamento
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function () {
            // Importante: Si el usuario cambia el depto, ya no queremos
            // forzar la selección del puesto guardado anteriormente.
            //selectedWorkstationIds = null;
            const selectedDepartments = Array.from(this.selectedOptions).map(opt => opt.value);

            populateWorkstations(selectedDepartments);
        });
    }

    // 6. Ejecutar la función una vez al cargar la página
    //    para rellenar los puestos del departamento que ya estaba seleccionado.
    if (departmentSelect) {
        const initialDepartments = Array.from(departmentSelect.selectedOptions).map(opt => opt.value);
        populateWorkstations(initialDepartments);
    }
</script>
@endpush