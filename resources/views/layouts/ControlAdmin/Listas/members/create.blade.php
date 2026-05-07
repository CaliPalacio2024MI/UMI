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
            <form method="POST" action="{{ route('control.teachers.store') }}" class="registration-form" id="form-registro-docente" data-index-url="{{ route('control.teachers.index') }}" target="_top">
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
                    <div class="form-field">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ingrese el nombre" required>
                    </div>

                    <div class="form-field">
                        <label for="apellido_paterno">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" placeholder="Ingresar el apellido" required>
                    </div>

                    <div class="form-field">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" placeholder="Ingrese el apellido" required>
                    </div>
                </div>

                <div class="form-group-double">
                    <div class="form-field">
                        <label for="email">Correo Electrónico (Email)</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Ingrese su correo electrónico" required>
                    </div>

                    <div class="form-field">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Introduzca su número celular o telefónico" required>
                    </div>
                </div>

                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="RFC">RFC</label>
                        <input type="text" id="RFC" name="RFC" value="{{ old('RFC') }}" placeholder="Ingrese su RFC" required maxlength="13">
                        @error('RFC')
                            <span class="field-error">Falta rellenar el RFC.</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                    </div>
                    <div class="form-field">
                        <label for="registro_docente_edad">Edad</label>
                        <input type="text" id="registro_docente_edad" readonly tabindex="-1" value="" autocomplete="off" aria-live="polite" inputmode="numeric">
                    </div>
                </div>
                
                {{-- Sección: Dirección --}}
                <h3><img src="{{ asset('images/icons/address-svgrepo-com.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;margin-top:-2px" aria-hidden="true"> Dirección</h3>
                <hr>

                {{-- Dirección (se asume que hay 6 campos, agrupados en dos filas de 3) --}}
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="calle">Calle</label>
                        <input type="text" id="calle" name="calle" value="{{ old('calle') }}" placeholder="Coloque el nombre de la calle" required>
                    </div>
                    <div class="form-field">
                        <label for="colonia">Colonia</label>
                        <input type="text" id="colonia" name="colonia" value="{{ old('colonia') }}" placeholder="Coloque su colonia" required>
                    </div>
                    <div class="form-field">
                        <label for="ciudad">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad') }}" placeholder="Coloque su ciudad" required>
                    </div>
                </div>
                
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" name="estado" value="{{ old('estado') }}" placeholder="Introduzca su estado" required>
                    </div>
                    <div class="form-field">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal') }}" placeholder="Introduzca su código postal" required>
                    </div>
                </div>

                <h3 id="heading-docente-carreras" style="margin-top: 1rem;"><img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;margin-top:-2px" aria-hidden="true"> Carreras</h3>
                <hr>

                @php
                    $oldCarrerasIds = collect(old('carreras', []))->map(fn ($v) => (int) $v)->all();
                @endphp
                <div class="form-group-triple">
                    <fieldset class="form-field docente-carreras-fieldset" style="grid-column: 1 / -1; border: none; padding: 0; margin: 0;" aria-labelledby="heading-docente-carreras">
                        <legend class="docente-carreras-legend">Carreras</legend>
                        <div class="docente-carreras-checkboxes">
                            @foreach ($carreras as $carrera)
                                <label class="docente-carrera-checkbox-label">
                                    <input type="checkbox" name="carreras[]" value="{{ $carrera->id }}" @checked(in_array((int) $carrera->id, $oldCarrerasIds, true))>
                                    <span>{{ $carrera->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                </div>

                <div class="form-action-buttons">
                    <button type="submit" class="submit-button">+ Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection