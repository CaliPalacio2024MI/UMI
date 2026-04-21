<div id="editMateriaModal_{{ $registro->id }}" class="modal-overlay"> 
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="editMateriaModalLabel">Editar Materia</h5>
            <button type="button" class="close-custom">&times;</button>
        </div>
        
        <div class="modal-body-custom" id="modalBodyContent">
            
            <form class="js-materia-update-form" method="post" action="{{ route('control.subjects.update', $registro) }}">
                @csrf 
                @method('PUT')

                @if(session('edit_materia_id') == $registro->id && $errors->any())
                    <div class="error-message" style="margin-bottom: 1rem;">
                        @if($errors->has('nombre') && $errors->first('nombre') === 'Ya existe una materia con ese nombre. Elija otro.')
                            Ya existe una materia con ese nombre. Elija otro.
                        @else
                            Te falta un campo por rellenar.
                        @endif
                    </div>
                @endif

                {{-- Campo: Carrera (carrera_id) --}}
                <div class="form-field lists">
                    <label for="carrera_id">Carrera:</label> 
                    <select id="carrera_id" name="carrera_id" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">
                        <option value="">Seleccione una Carrera</option> 
                        
                        {{-- La clave foránea en la DB es 'career_id' --}}
                        @foreach ($carreras as $carrera) 
                            <option 
                                value="{{ $carrera->id }}" 
                                {{-- Usamos el valor actual de la materia si no hay old input --}}
                                {{ old('carrera_id', $registro->career_id) == $carrera->id ? 'selected' : '' }}
                            >
                                {{ $carrera->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Campo: Nombre --}}
                <div class="form-field">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif" value="{{ old('nombre', $registro->nombre) }}">
                </div>

                {{-- Campo: No. Créditos --}}
                <div class="form-field lists">
                    <label for="creditos_{{ $registro->id }}">No. de créditos:</label>
                    <input type="number" id="creditos_{{ $registro->id }}" name="creditos" min="1" step="1"
                        class="js-materia-creditos @if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif"
                        value="{{ old('creditos', $registro->creditos) }}">
                </div>

                <div class="options">
                    {{-- Campo: Modalidad (type) --}}
                    <div class="form-field Checkboxes">
                        <label>Modalidad:</label>
                        <div>
                            <input type="radio" id="type_presencial_{{ $registro->id }}" name="type" value="Presencial" {{ old('type', $registro->type) == 'Presencial' ? 'checked' : '' }}>
                            <label for="type_presencial_{{ $registro->id }}">Presencial:</label>
                        </div>
                        <div>
                            <input type="radio" id="type_enlinea_{{ $registro->id }}" name="type" value="En linea" {{ old('type', $registro->type) == 'En linea' ? 'checked' : '' }}>
                            <label for="type_enlinea_{{ $registro->id }}">En linea:</label>
                        </div>
                    </div>

                    {{-- Campo: Semestre --}}
                    <div class="form-field lists">
                        <label for="semestre_{{ $registro->id }}">Semestre:</label>
                        <input type="number" id="semestre_{{ $registro->id }}" name="semestre" min="1" step="1"
                            class="js-materia-semestre @if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif"
                            value="{{ old('semestre', $registro->semestre) }}">
                        <input type="hidden" name="clave" value="{{ old('clave', $registro->clave) }}">
                    </div>
                </div>

                <div class="form-field">
                    <label for="descripcion_{{ $registro->id }}">Descripción general:</label>
                    <textarea id="descripcion_{{ $registro->id }}" name="descripcion" rows="1" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">{{ old('descripcion', $registro->descripcion) }}</textarea>
                </div>
                <div class="form-field">
                    <label for="objetivo_{{ $registro->id }}">Objetivo:</label>
                    <textarea id="objetivo_{{ $registro->id }}" name="objetivo" rows="1" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">{{ old('objetivo', $registro->objetivo) }}</textarea>
                </div>
                <div class="form-field">
                    <label for="temario_{{ $registro->id }}">Temario:</label>
                    <textarea id="temario_{{ $registro->id }}" name="temario" rows="1" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">{{ old('temario', $registro->temario) }}</textarea>
                </div>
                <div class="form-field">
                    <label for="infografia_{{ $registro->id }}">Infografía de la materia:</label>
                    <textarea id="infografia_{{ $registro->id }}" name="infografia" rows="1" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">{{ old('infografia', $registro->infografia) }}</textarea>
                </div>
                              
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">+ Guardar</button>
                </div>
            </form>

        </div>
    </div>
</div>