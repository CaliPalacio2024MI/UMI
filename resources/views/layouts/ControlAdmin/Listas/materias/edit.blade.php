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
                    <label for="creditos">No. de Creditos:</label>
                    <select id="creditos" name="creditos" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">
                        @for ($i = 1; $i <= 10; $i++)
                            <option 
                                value="{{ $i }}" 
                                {{ old('creditos', $registro->creditos) == $i ? 'selected' : '' }}
                            >{{ $i }}</option>
                        @endfor
                    </select>
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
                        <label for="semestre">Semestre:</label>
                        <select id="semestre" name="semestre" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">
                            @for ($i = 1; $i <= 8; $i++)
                                <option 
                                    value="{{ $i }}" 
                                    {{ old('semestre', $registro->semestre) == $i ? 'selected' : '' }}
                                >{{ $i }}</option>
                            @endfor
                        </select>
                        <input type="hidden" name="clave" value="{{ old('clave', $registro->clave) }}">
                        <input type="hidden" name="descripcion" value="{{ old('descripcion', $registro->descripcion) }}">
                    </div>
                </div>              
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">+ Guardar</button>
                </div>
            </form>

        </div>
    </div>
</div>