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
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

                {{-- Campo: Carrera --}}
                <div class="form-field lists">
                    <label for="carrera_id">Carrera:</label> 
                    <select id="carrera_id" name="carrera_id" class="select-carrera @if($errors->any()) validation-error @endif">
                        <option value="" class="placeholder-option">Seleccione una Carrera</option> 
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id }}" {{ old('carrera_id') == $carrera->id ? 'selected' : '' }}>{{ $carrera->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Campo: Clasificación --}}
                <div class="form-field lists">
                    <label for="career_classification_id">Clasificación:</label>
                    <select id="career_classification_id" name="career_classification_id" class="@if($errors->any()) validation-error @endif">
                        <option value="">Seleccione una Clasificación</option>
                        @foreach ($clasificaciones as $clasificacion)
                            <option value="{{ $clasificacion->id }}" {{ old('career_classification_id') == $clasificacion->id ? 'selected' : '' }}>
                                {{ $clasificacion->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Campo: Nombre --}}
                <div class="form-field">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="@if($errors->any()) validation-error @endif" value="{{ old('nombre') }}" placeholder="Ingresa la Materia">
                </div>
                
                {{-- Campo: No. Créditos --}}
                <div class="form-field lists">
                    <label for="creditos">No. de créditos:</label>
                    <input type="number" id="creditos" name="creditos" min="1" step="1"
                        class="js-materia-creditos @if($errors->any()) validation-error @endif"
                        value="{{ old('creditos', 1) }}">
                </div>
                
                <div class="options">
                    {{-- Campo: Modalidad --}}
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
                    
                    {{-- Campo: Semestre --}}
                    <div class="form-field lists">
                        <label for="semestre">Semestre:</label>
                        <input type="number" id="semestre" name="semestre" min="1" step="1"
                            class="js-materia-semestre @if($errors->any()) validation-error @endif"
                            value="{{ old('semestre', 1) }}">
                    </div>
                </div>

                {{-- Campo: Descripción --}}
                <div class="form-field">
                    <label for="descripcion">Descripción general:</label>
                    <textarea id="descripcion" name="descripcion" rows="1" class="@if($errors->any()) validation-error @endif" placeholder="Información general de la materia">{{ old('descripcion') }}</textarea>
                </div>

                {{-- Campo: Objetivo --}}
                <div class="form-field">
                    <label for="objetivo">Objetivo:</label>
                    <textarea id="objetivo" name="objetivo" rows="1" class="@if($errors->any()) validation-error @endif" placeholder="Objetivo de aprendizaje">{{ old('objetivo') }}</textarea>
                </div>

                {{-- Campo: Temario (dinámico) --}}
                <div class="form-field">
                    <label>Temario:</label>
                    <div id="temario-container">
                        @php
                            $temarioValues = old('temario', ['']);
                            if (!is_array($temarioValues)) {
                                $temarioValues = [$temarioValues];
                            }
                            if (empty($temarioValues)) {
                                $temarioValues = [''];
                            }
                        @endphp
                        @foreach($temarioValues as $index => $tema)
                            <div class="temario-item" style="display:flex; gap:8px; margin-bottom:6px;">
                                <input type="text" name="temario[]" value="{{ $tema }}" placeholder="Tema {{ $index + 1 }}" style="flex:1;">
                                <button type="button" class="remove-tema" style="background:#c0392b;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-tema-btn" style="margin-top:6px;background:#2c3e50;color:#fff;border:none;padding:6px 14px;border-radius:4px;cursor:pointer;">+ Agregar tema</button>
                </div>

                {{-- Campo: Infografía --}}
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const createMateriaModal = document.getElementById('createMateriaModal');
        const hasCreateErrors = @json($errors->any());
        if (createMateriaModal && hasCreateErrors) {
            createMateriaModal.style.display = 'flex';
        }
    });
    function addMateriaTema(event) {
        event.preventDefault();
        const container = document.getElementById('temario-container');
        if (!container) return;

        const count = container.querySelectorAll('.temario-item').length + 1;
        const div = document.createElement('div');
        div.className = 'temario-item';
        div.style.cssText = 'display:flex; gap:8px; margin-bottom:6px;';
        div.innerHTML = '<input type="text" name="temario[]" placeholder="Tema ' + count + '" style="flex:1;">' +
            '<button type="button" class="remove-tema" style="background:#c0392b;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">&times;</button>';
        container.appendChild(div);
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('#add-tema-btn')) {
            addMateriaTema(e);
            return;
        }

        if (e.target.classList.contains('remove-tema')) {
            const container = e.target.closest('#temario-container');
            if (container && container.querySelectorAll('.temario-item').length > 1) {
                e.target.closest('.temario-item').remove();
            }
        }
    });
    </script>
</div>