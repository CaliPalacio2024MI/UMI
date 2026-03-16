<form method="POST" action="{{ route('control.teachers.store') }}" class="registration-form" id="form-registro-docente">
    @csrf
    @if ($errors->any())
        <div class="message-error" style="border: 1px solid #c00; padding: 10px; margin-bottom: 15px; background: #ffe0e0; border-radius: 8px;">
            <strong>Revisa los datos.</strong>
            <ul style="margin: 8px 0 0 1em;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('error'))
        <div class="message-error" style="border: 1px solid red; padding: 10px; margin-bottom: 15px; background: #ffe0e0; border-radius: 8px;">
            {{ session('error') }}
        </div>
    @endif

    <h3 style="margin-top:0;"><img src="{{ asset('images/icons/circle-user-solid-full.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px" aria-hidden="true"> Datos Personales</h3>
    <hr>
    <div class="form-group-triple">
        <div class="form-field">
            <label for="modal_nombre">Nombre(s)</label>
            <input type="text" id="modal_nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ingresar el Nombre" required>
        </div>
        <div class="form-field">
            <label for="modal_apellido_paterno">Apellido Paterno</label>
            <input type="text" id="modal_apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" placeholder="Ingresar el apellido" required>
        </div>
        <div class="form-field">
            <label for="modal_apellido_materno">Apellido Materno</label>
            <input type="text" id="modal_apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" placeholder="Ingrese el apellido" required>
        </div>
    </div>

    <div class="form-group-double">
        <div class="form-field">
            <label for="modal_email">Correo Electrónico (Email)</label>
            <input type="email" id="modal_email" name="email" value="{{ old('email') }}" placeholder="Ingrese su correo electrónico" required>
        </div>
        <div class="form-field">
            <label for="modal_telefono">Teléfono</label>
            <input type="text" id="modal_telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Número celular o telefónico" required>
        </div>
    </div>

    <div class="form-group-triple">
        <div class="form-field">
            <label for="modal_RFC">RFC</label>
            <input type="text" id="modal_RFC" name="RFC" value="{{ old('RFC') }}" placeholder="Ingrese su RFC" required maxlength="13">
            @error('RFC')
                <span class="field-error">Falta rellenar el RFC.</span>
            @enderror
        </div>
        <div class="form-field">
            <label for="modal_fecha_nacimiento">Fecha de Nacimiento</label>
            <input type="date" id="modal_fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
        </div>
        <div class="form-field"></div>
    </div>

    <h3><img src="{{ asset('images/icons/address-svgrepo-com.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;margin-top:-2px" aria-hidden="true"> Dirección</h3>
    <hr>
    <div class="form-group-triple">
        <div class="form-field">
            <label for="modal_calle">Calle</label>
            <input type="text" id="modal_calle" name="calle" value="{{ old('calle') }}" placeholder="Nombre de la calle" required>
        </div>
        <div class="form-field">
            <label for="modal_colonia">Colonia</label>
            <input type="text" id="modal_colonia" name="colonia" value="{{ old('colonia') }}" placeholder="Colonia" required>
        </div>
        <div class="form-field">
            <label for="modal_ciudad">Ciudad</label>
            <input type="text" id="modal_ciudad" name="ciudad" value="{{ old('ciudad') }}" placeholder="Ciudad" required>
        </div>
    </div>
    <div class="form-group-triple">
        <div class="form-field">
            <label for="modal_estado">Estado</label>
            <input type="text" id="modal_estado" name="estado" value="{{ old('estado') }}" placeholder="Estado" required>
        </div>
        <div class="form-field">
            <label for="modal_codigo_postal">Código Postal</label>
            <input type="text" id="modal_codigo_postal" name="codigo_postal" value="{{ old('codigo_postal') }}" placeholder="Código postal" required>
        </div>
        <div class="form-field"></div>
    </div>

    <h3><img src="{{ asset('images/icons/padlock-unlocked-outlined-svgrepo-com.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px" aria-hidden="true"> Carrera</h3>
    <hr>
    <div class="form-group-triple">
        <div class="form-field">
            <select id="modal_carrera" name="carrera" required>
                <option value="">Seleccione una Carrera</option>
                @foreach ($carreras as $carrera)
                    <option value="{{ $carrera->id }}"{{ old('carrera') == $carrera->id ? 'selected' : '' }}>{{ $carrera->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-action-buttons" style="margin-top: 1rem;">
        <button type="submit" class="submit-button">+ Agregar Docente</button>
    </div>
</form>
