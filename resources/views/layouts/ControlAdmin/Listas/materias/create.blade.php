<div id="createMateriaModal" class="modal-overlay"> 
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="createMateriaModalLabel">Agregar Materia</h5>
            <button type="button" class="close-custom" aria-label="Cerrar">&times;</button>
        </div>
        
        <div class="modal-body-custom" id="modalBodyContent">
            
            <form method="post" action="{{ route('control.subjects.store') }}">
                @csrf

                @if($errors->any())
                    <div class="error-message" style="margin-bottom: 1rem;">
                        @if($errors->has('nombre') && $errors->first('nombre') === 'Ya existe una materia con ese nombre. Elija otro.')
                            Ya existe una materia con ese nombre. Elija otro.
                        @else
                            Te falta un campo por rellenar.
                        @endif
                    </div>
                @endif

                <div class="form-field lists">
                    <label for="carrera_id">Carrera:</label> 
                    
                    <select id="carrera_id" name="carrera_id" class="select-carrera @if($errors->any()) validation-error @endif">
                        <option value="" class="placeholder-option">Seleccione una Carrera</option> 
                        
                        @foreach ($carreras as $carrera)
                            <option 
                                value="{{ $carrera->id }}" {{ old('carrera_id') == $carrera->id ? 'selected' : '' }}>{{ $carrera->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="@if($errors->any()) validation-error @endif" value="{{ old('nombre') }}" placeholder="Ingresa la Materia">
                </div>
                
                
                <div class="form-field lists">
                        <label for="creditos">No. de créditos:</label>
                        <input type="number" id="creditos" name="creditos" min="1" step="1"
                            class="js-materia-creditos @if($errors->any()) validation-error @endif"
                            value="{{ old('creditos', 1) }}">
                </div>
                
                <div class="options">
                    
                    <div class="form-field Checkboxes">
                        <label>Modalidad:</label>
                        <div>
                            <input type="radio" id="type_presencial" name="type" value="Presencial" {{ old('type') == 'Presencial' ? 'checked' : '' }}>
                            <label for="type_presencial">Presencial:</label>
                        </div>
                        <div>
                            <input type="radio" id="type_enlinea" name="type" value="En linea" {{ old('type') == 'En linea' ? 'checked' : '' }}>
                            <label for="type_enlinea">En linea:</label>
                        </div>
                    </div>
                    
                    <div class="form-field lists">
                        <label for="semestre">Semestre:</label>
                        <input type="number" id="semestre" name="semestre" min="1" step="1"
                            class="js-materia-semestre @if($errors->any()) validation-error @endif"
                            value="{{ old('semestre', 1) }}">
                    </div>
                </div>

                <div class="form-field">
                    <label for="descripcion">Descripción general:</label>
                    <textarea id="descripcion" name="descripcion" rows="1" class="@if($errors->any()) validation-error @endif" placeholder="Información general de la materia">{{ old('descripcion') }}</textarea>
                </div>
                <div class="form-field">
                    <label for="objetivo">Objetivo:</label>
                    <textarea id="objetivo" name="objetivo" rows="1" class="@if($errors->any()) validation-error @endif" placeholder="Objetivo de aprendizaje">{{ old('objetivo') }}</textarea>
                </div>
                <div class="form-field">
                    <label for="temario">Temario:</label>
                    <textarea id="temario" name="temario" rows="1" class="@if($errors->any()) validation-error @endif" placeholder="Temas y subtemas">{{ old('temario') }}</textarea>
                </div>
                <div class="form-field">
                    <label for="infografia">Infografía de la materia:</label>
                    <textarea id="infografia" name="infografia" rows="1" class="@if($errors->any()) validation-error @endif" placeholder="Resumen visual o texto de apoyo">{{ old('infografia') }}</textarea>
                </div>
                
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">+ Guardar</button>
                </div>
            </form>

        </div>
    </div>
</div>