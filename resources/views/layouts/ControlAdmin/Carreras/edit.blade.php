<div id="editCareerModal_{{ $career->id }}" class="modal-overlay"> 
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="editCareerModalLabel">Editar Carrera</h5>
            <button type="button" id="closeEditModalBtn_{{ $career->id }}" class="close-custom">&times;</button>
        </div>
        
        <div class="modal-body-custom" id="modalBodyContentEdit">
            
            <form method="post" action="{{ route('control.careers.update', $career->id) }}">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="error-message">Debe rellenar todo el formulario.</div>
                @endif

                {{-- 1. Nombre --}}
                <div class="form-field">
                    <label for="name_{{ $career->id }}">Nombre:</label>
                    <input type="text" id="name_{{ $career->id }}" name="name" 
                           class="@error('name') validation-error @enderror"
                           value="{{ old('name', $career->name) }}">
                </div>

                {{-- 2. RVOE --}}
                <div class="form-field">
                    <label for="official_id_{{ $career->id }}">RVOE: Acuerdo Número:</label>
                    <input type="text" id="official_id_{{ $career->id }}" name="official_id" 
                           class="@error('official_id') validation-error @enderror"
                           value="{{ old('official_id', $career->official_id) }}">
                </div>

                {{-- 3. Desc1 --}}
                <div class="form-field">
                    <label for="description1_{{ $career->id }}">Profesionalización y empleabilidad:</label>
                    <input type="text" id="description1_{{ $career->id }}" name="description1" 
                           class="@error('description1') validation-error @enderror"
                           value="{{ old('description1', $career->description1) }}">
                </div>

                {{-- 4. Desc2 --}}
                <div class="form-field">
                    <label for="description2_{{ $career->id }}">Objetivo General:</label>
                    <input type="text" id="description2_{{ $career->id }}" name="description2" 
                           class="@error('description2') validation-error @enderror"
                           value="{{ old('description2', $career->description2) }}">
                </div>

                {{-- 5. Desc3 --}}
                <div class="form-field">
                    <label for="description3_{{ $career->id }}">Elige Ser:</label>
                    <input type="text" id="description3_{{ $career->id }}" name="description3" 
                           class="@error('description3') validation-error @enderror"
                           value="{{ old('description3', $career->description3) }}">
                </div>

                <div class="options">
                    {{-- 6. Modalidad (Radios) --}}
                    <div class="Checkboxes form-field">
                        <label>Modalidad:</label>
                        @php $currentType = old('type', $career->type); @endphp
                        <div>
                            <input type="radio" id="type_presencial_{{ $career->id }}" name="type" value="Presencial" 
                                   {{ $currentType == 'Presencial' ? 'checked' : '' }}>
                            <label for="type_presencial_{{ $career->id }}">Presencial:</label>
                        </div>
                        <div>
                            <input type="radio" id="type_enlinea_{{ $career->id }}" name="type" value="En linea" 
                                   {{ $currentType == 'En linea' ? 'checked' : '' }}>
                            <label for="type_enlinea_{{ $career->id }}">En linea:</label>
                        </div>
                    </div>

                    {{-- 7. Semestres (select) --}}
                    <div class="lists form-field">
                        <label for="semesters_{{ $career->id }}">No. de semestres:</label>
                        <select id="semesters_{{ $career->id }}" name="semesters" class="@error('semesters') validation-error @enderror">
                            @php $currentSemesters = old('semesters', $career->semesters); @endphp
                            @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ $currentSemesters == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">+ Actualizar</button>
                </div>
            </form>

        </div>
    </div>
</div>