<div id="createCareerModal" class="modal-overlay">
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="createCareerModalLabel">Agregar Carrera</h5>
            <button type="button" id="closeModalBtn" class="close-custom">&times;</button>
        </div>

        <div class="modal-body-custom" id="modalBodyContent">

            <form method="post" action="{{ route('control.careers.store') }}">
                @csrf

                @if($errors->any())
                    <div class="error-message">Debe rellenar todo el formulario.</div>
                @endif

                <div class="form-field">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" placeholder="Ingrese el nombre de la carrera"
                        class="@error('name') validation-error @enderror" value="{{ old('name') }}">
                </div>

                <div class="form-field">
                    <label for="official_id">RVOE: Acuerdo Número:</label>
                    <input type="text" id="official_id" name="official_id" placeholder="Ingrese el RVOE"
                        class="@error('official_id') validation-error @enderror" value="{{ old('official_id') }}">
                </div>

                <div class="form-field">
                    <label for="career_classification_id" class="career-classification-label--opens-modal" title="Clic para agregar o administrar clasificaciones">Clasificación:</label>
                    <select id="career_classification_id" name="career_classification_id"
                        class="@error('career_classification_id') validation-error @enderror">
                        <option value="">Seleccione una clasificación</option>
                        @foreach ($careerClassifications ?? [] as $clasificacion)
                            <option value="{{ $clasificacion->id }}" @selected(old('career_classification_id') == $clasificacion->id)>
                                {{ $clasificacion->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="description1">Profesionalización y empleabilidad:</label>
                    <input type="text" id="description1" name="description1" placeholder="Ingrese la descripción"
                        class="@error('description1') validation-error @enderror" value="{{ old('description1') }}">
                </div>

                <div class="form-field">
                    <label for="description2">Objetivo General:</label>
                    <input type="text" id="description2" name="description2" placeholder="Ingrese la descripción"
                        class="@error('description2') validation-error @enderror" value="{{ old('description2') }}">
                </div>

                <div class="form-field">
                    <label for="description3">Elige Ser:</label>
                    <input type="text" id="description3" name="description3" placeholder="Ingrese la descripción"
                        class="@error('description3') validation-error @enderror" value="{{ old('description3') }}">
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
                        <label for="semesters">No. de semestres:</label>
                        <select id="semesters" name="semesters" class="@error('semesters') validation-error @enderror">
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ old('semesters') == $i ? 'selected' : '' }}>{{ $i }}</option>
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