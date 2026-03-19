@extends('layouts.app')

@section('title', 'Cursos - ' . session('active_institution_name'))

@vite(['resources/css/ControlEsc/base.css','resources/js/app.js'])

@section('content')
    {{-- Contenedor principal del formulario --}}
    <div class="form-container">
        {{-- Encabezado --}}
        <div class="header-section">
            <h2 class="form-title">Registro de Docente</h2>
        </div>
        
        {{-- Cuerpo del formulario --}}
        <div class="form-body">
            <form method="POST" action="{{ route('control.teachers.store') }}" class="registration-form" id="form-registro-docente" target="_top">
                @csrf
                {{-- Bloque para mostrar mensajes Flash (éxito o error) --}}
                @if ($errors->any())
                    <div class="message-error" style="border: 1px solid #c00; padding: 10px; margin-bottom: 15px; background: #ffe0e0;">
                        <strong>Revisa los datos.</strong>
                        <ul style="margin: 8px 0 0 1em;">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="message-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="message-error" style="border: 1px solid red; padding: 10px; margin-bottom: 15px; background: #ffe0e0;">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Sección: Datos Personales --}}
                <h3><img src="{{ asset('images/icons/circle-user-solid-full.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px" aria-hidden="true"> Datos Personales</h3>
                <hr>
                <div class="form-group-triple">
                    {{-- Nombre: solo selector --}}
                    <div class="form-field">
                        <label for="docente_autocomplete">Nombre</label>
                        <select id="docente_autocomplete" class="form-control" required>
                            <option value="">Seleccione un docente...</option>
                        </select>
                        <input type="hidden" id="nombre" name="nombre" value="{{ old('nombre') }}">
                    </div>

                    {{-- Apellido Paterno --}}
                    <div class="form-field">
                        <label for="apellido_paterno">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" placeholder="Ingresar el apellido" required readonly>
                    </div>

                    {{-- Apellido Materno --}}
                    <div class="form-field">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" placeholder="Ingrese el apellido" required readonly>
                    </div>
                </div>

                <div class="form-group-double">
                    {{-- Email --}}
                    <div class="form-field">
                        <label for="email">Correo Electrónico (Email)</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Ingrese su correo electrónico" required readonly>
                    </div>

                    {{-- Teléfono --}}
                    <div class="form-field">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Introduzca su número celular o telefónico" required readonly>
                    </div>
                </div>

                <div class="form-group-triple">
                    {{-- RFC --}}
                    <div class="form-field">
                        <label for="RFC">RFC</label>
                        <input type="text" id="RFC" name="RFC" value="{{ old('RFC') }}" placeholder="Ingrese su RFC" required maxlength="13" readonly>
                        @error('RFC')
                            <span class="field-error">Falta rellenar el RFC.</span>
                        @enderror
                    </div>

                    {{-- Fecha de Nacimiento --}}
                    <div class="form-field">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required readonly>
                    </div>
                </div>
                
                {{-- Sección: Dirección --}}
                <h3><img src="{{ asset('images/icons/address-svgrepo-com.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;margin-top:-2px" aria-hidden="true"> Dirección</h3>
                <hr>

                {{-- Dirección (se asume que hay 6 campos, agrupados en dos filas de 3) --}}
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="calle_1">Calle</label>
                        <input type="text" id="calle" name="calle" value="{{ old('calle') }}" placeholder="Coloque el nombre de la calle" required readonly>
                    </div>
                    <div class="form-field">
                        <label for="colonia">Colonia</label>
                        <input type="text" id="colonia" name="colonia" value="{{ old('colonia') }}" placeholder="Coloque su colonia" required readonly>
                    </div>
                    <div class="form-field">
                        <label for="ciudad">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad') }}" placeholder="Coloque su ciudad" required readonly>
                    </div>
                </div>
                
                <div class="form-group-triple">
                    
                    <div class="form-field">
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" name="estado" value="{{ old('estado') }}" placeholder="Introduzca su estado" required readonly>
                    </div>
                    <div class="form-field">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal') }}" placeholder="Introduzca su código postal" required readonly>
                    </div>
                </div>

                {{-- Sección: Contraseña y Carrera --}}
                <h3><img src="{{ asset('images/icons/padlock-unlocked-outlined-svgrepo-com.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px" aria-hidden="true"> Carrera</h3>
                <hr>

                <div class="form-group-triple">
                    {{-- Carrera --}}
                    <div class="form-field">
                        <select id="carrera" name="carrera" required>
                            <option value="" class="select-placeholder">Seleccione una Carrera</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}"{{ old('carrera') == $carrera->id ? 'selected' : '' }}>{{ $carrera->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-action-buttons">
                    <button type="submit" class="submit-button">+ Agregar Docente</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        var listUrl = @json(route('control.teachers.list-for-register'));
        var select = document.getElementById('docente_autocomplete');
        var form = document.getElementById('form-registro-docente');
        if (!select || !form) return;

        var docentesList = [];

        function fillFields(doc) {
            var ids = ['nombre','apellido_paterno','apellido_materno','email','telefono','RFC','fecha_nacimiento','edad','calle','colonia','ciudad','estado','codigo_postal'];
            ids.forEach(function(id) {
                var el = form.querySelector('#' + id);
                if (el && doc[id] !== undefined) el.value = doc[id] || '';
            });
        }

        function clearAutocompleteFields() {
            fillFields({
                nombre: '', apellido_paterno: '', apellido_materno: '', email: '', telefono: '', RFC: '',
                fecha_nacimiento: '', edad: '', calle: '', colonia: '', ciudad: '', estado: '', codigo_postal: ''
            });
        }

        select.addEventListener('change', function() {
            var val = this.value;
            if (val === '') {
                clearAutocompleteFields();
                return;
            }
            var id = parseInt(val, 10);
            var doc = docentesList.find(function(d) { return d.id === id; });
            if (doc) fillFields(doc);
        });

        fetch(listUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                docentesList = data;
                var frag = document.createDocumentFragment();
                data.forEach(function(d) {
                    var opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = (d.nombre || '') + ' ' + (d.apellido_paterno || '') + ' ' + (d.apellido_materno || '');
                    frag.appendChild(opt);
                });
                select.appendChild(frag);
            })
            .catch(function() {});
    })();
    </script>
@endsection