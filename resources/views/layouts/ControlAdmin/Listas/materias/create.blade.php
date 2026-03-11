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
                        <label for="creditos">No. de Creditos:</label>
                        <select id="creditos" name="creditos" class="@if($errors->any()) validation-error @endif">
                            @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('creditos') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
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
                        <select id="semestre" name="semestre" class="@if($errors->any()) validation-error @endif">
                            @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ old('semestre') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">+ Agregar</button>
                </div>
            </form>

        </div>
    </div>
</div>