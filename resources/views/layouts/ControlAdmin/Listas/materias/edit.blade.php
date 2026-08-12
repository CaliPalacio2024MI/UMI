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

                {{-- Campo: Clasificación --}}
                <div class="form-field lists">
                    <label for="career_classification_id_{{ $registro->id }}">Clasificación:</label>
                    <select id="career_classification_id_{{ $registro->id }}" name="career_classification_id"
                        class="js-clasif-select @if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif"
                        data-modal-id="{{ $registro->id }}">
                        <option value="">Seleccione una Clasificación</option>
                        @foreach ($clasificaciones as $clasificacion)
                            <option value="{{ $clasificacion->id }}" {{ old('career_classification_id', $registro->career_classification_id) == $clasificacion->id ? 'selected' : '' }}>
                                {{ $clasificacion->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Campo: Carrera (filtrada por clasificación) --}}
                <div class="form-field lists">
                    <label for="carrera_id_{{ $registro->id }}">Carrera:</label>
                    <select id="carrera_id_{{ $registro->id }}" name="carrera_id"
                        class="js-carrera-select @if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif"
                        data-modal-id="{{ $registro->id }}">
                        <option value="">Seleccione una Carrera</option>
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id }}"
                                data-clasificacion="{{ $carrera->career_classification_id }}"
                                {{ old('carrera_id', $registro->career_id) == $carrera->id ? 'selected' : '' }}>
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
                    {{-- Campo: Modalidad --}}
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

                {{-- Campo: Descripción --}}
                <div class="form-field">
                    <label for="descripcion_{{ $registro->id }}">Descripción general:</label>
                    <textarea id="descripcion_{{ $registro->id }}" name="descripcion" rows="1" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">{{ old('descripcion', $registro->descripcion) }}</textarea>
                </div>

                {{-- Campo: Objetivo --}}
                <div class="form-field">
                    <label for="objetivo_{{ $registro->id }}">Objetivo:</label>
                    <textarea id="objetivo_{{ $registro->id }}" name="objetivo" rows="1" class="@if(session('edit_materia_id') == $registro->id && $errors->any()) validation-error @endif">{{ old('objetivo', $registro->objetivo) }}</textarea>
                </div>

                {{-- Campo: Temario (dinámico) --}}
                <div class="form-field">
                    <label>Temario:</label>
                    <div id="temario-container-{{ $registro->id }}">
                        @php
                            $temas = old('temario', $registro->temario ?? []);
                            if (!is_array($temas)) $temas = [];
                            if (empty($temas)) $temas = [''];
                        @endphp
                        @foreach($temas as $i => $tema)
                            <div class="temario-item" style="display:flex; gap:8px; margin-bottom:6px;">
                                <input type="text" name="temario[]" value="{{ $tema }}" placeholder="Tema {{ $i + 1 }}" style="flex:1;">
                                <button type="button" class="remove-tema" style="background:#c0392b;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="add-tema-btn" onclick="addMateriaTemaEdit(event, 'temario-container-{{ $registro->id }}')" style="margin-top:6px;background:#2c3e50;color:#fff;border:none;padding:6px 14px;border-radius:4px;cursor:pointer;">+ Agregar tema</button>
                </div>

                {{-- Campo: Infografía --}}
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
    <script>
    function addMateriaTemaEdit(event, containerId) {
        event.preventDefault();
        const container = document.getElementById(containerId);
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
        if (e.target.classList.contains('remove-tema')) {
            const item = e.target.closest('.temario-item');
            const container = item ? item.closest('[id^="temario-container-"]') : null;
            if (container && container.querySelectorAll('.temario-item').length > 1) {
                item.remove();
            }
        }
    });

    (function() {
        const mid = '{{ $registro->id }}';
        const selClasif = document.getElementById('career_classification_id_' + mid);
        const selCarrera = document.getElementById('carrera_id_' + mid);
        if (!selClasif || !selCarrera) return;

        const allOpts = Array.from(selCarrera.options).slice(1);

        function filterCarreras(clasificId) {
            const current = selCarrera.value;
            while (selCarrera.options.length > 1) selCarrera.remove(1);
            allOpts.forEach(opt => {
                if (!clasificId || opt.dataset.clasificacion == clasificId) {
                    selCarrera.appendChild(opt.cloneNode(true));
                }
            });
            if (current && Array.from(selCarrera.options).some(o => o.value == current)) {
                selCarrera.value = current;
            } else {
                selCarrera.value = '';
            }
        }

        selClasif.addEventListener('change', function() { filterCarreras(this.value); });

        selCarrera.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.clasificacion) {
                selClasif.value = opt.dataset.clasificacion;
                filterCarreras(opt.dataset.clasificacion);
            }
        });

        if (selClasif.value) filterCarreras(selClasif.value);
    })();
    </script>
</div>