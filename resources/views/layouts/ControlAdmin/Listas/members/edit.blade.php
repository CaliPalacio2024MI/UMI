@extends('layouts.app')

@section('title', 'Cursos - ' . session('active_institution_name'))

@vite(['resources/css/ControlEsc/base.css','resources/js/app.js'])

@section('content')
    {{-- Contenedor principal del formulario --}}
    <div class="form-container">
        {{-- Encabezado --}}
        <div class="header-section">
            <h2 class="form-title">Edición de Docente</h2>
        </div>
        
        {{-- Cuerpo del formulario --}}
        <div class="form-body">
            <form method="POST" action="{{ route('control.teachers.update', $user->id) }}" class="registration-form" id="form-edicion-docente" data-index-url="{{ route('control.teachers.index') }}">
                @csrf
                @method('PUT')
                {{-- Manejo de Errores de Validación --}}
                @if ($errors->any())
                    <div class="error-message">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Sección: Datos Personales --}}
                <h3><img src="{{ asset('images/icons/circle-user-solid-full.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px" aria-hidden="true"> Datos Personales</h3>
                <hr>
                <div class="form-group-triple">
                    {{-- Nombre(s) --}}
                    <div class="form-field">
                        <label for="nombre">Nombre(s)</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $user->nombre) }}" required>
                    </div>

                    {{-- Apellido Paterno --}}
                    <div class="form-field">
                        <label for="apellido_paterno">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $user->apellido_paterno) }}" required>
                    </div>

                    {{-- Apellido Materno --}}
                    <div class="form-field">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno',$user->apellido_materno) }}" required>
                    </div>
                </div>

                <div class="form-group-double">
                    {{-- Email --}}
                    <div class="form-field">
                        <label for="email">Correo Electrónico (Email)</label>
                        <input type="email" id="email" name="email" value="{{ old('email',$user->email) }}" required>
                    </div>

                    {{-- Teléfono --}}
                    <div class="form-field">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $user->telefono) }}" required>
                    </div>
                </div>

                <div class="form-group-triple">
                    {{-- RFC --}}
                    <div class="form-field">
                        <label for="RFC">RFC</label>
                        <input type="text" id="RFC" name="RFC" value="{{ old('RFC',$user->RFC) }}" required maxlength="13">
                        @error('RFC')
                            <span class="field-error">Falta rellenar el RFC.</span>
                        @enderror
                    </div>

                    {{-- Fecha de Nacimiento --}}
                    <div class="form-field">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento',$user->fecha_nacimiento) }}" required>
                    </div>

                    {{-- Edad (calculada desde fecha de nacimiento; no se envía, el servidor la recalcula al guardar) --}}
                    <div class="form-field docente-edit-edad-field">
                        <label for="docente_edit_edad">Edad</label>
                        <input type="text" id="docente_edit_edad" readonly tabindex="-1" value="" autocomplete="off" aria-live="polite" inputmode="numeric">
                    </div>
                </div>
                
                {{-- Sección: Dirección --}}
                <h3><img src="{{ asset('images/icons/address-svgrepo-com.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;margin-top:-2px" aria-hidden="true"> Dirección</h3>
                <hr>

                {{-- Dirección (se asume que hay 6 campos, agrupados en dos filas de 3) --}}
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="calle_1">Calle</label>
                        <input type="text" id="calle" name="calle" value="{{ old('calle', $user->address?->calle) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="colonia">Colonia</label>
                        <input type="text" id="colonia" name="colonia" value="{{ old('colonia', $user->address?->colonia) }}" required>
                    </div>
                </div>
                
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="ciudad">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', $user->address?->ciudad) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" name="estado" value="{{ old('estado', $user->address?->estado) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal', $user->address?->codigo_postal) }}" required>
                    </div>
                </div>

                @php
                    $carrerasSeleccionadas = old('carreras') !== null
                        ? collect(old('carreras', []))->map(fn ($v) => (int) $v)->all()
                        : $user->teachingCareers->pluck('id')->map(fn ($v) => (int) $v)->all();
                    if ($carrerasSeleccionadas === [] && $user->academicProfile?->career_id) {
                        $carrerasSeleccionadas = [(int) $user->academicProfile->career_id];
                    }
                @endphp
                <div class="form-group-triple">
                    <fieldset class="form-field docente-carreras-fieldset" style="grid-column: 1 / -1; border: none; padding: 0; margin: 0;">
                        <legend class="docente-carreras-legend"><img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="" width="18" height="18" style="vertical-align:middle;margin-right:5px;margin-top:-2px" aria-hidden="true"> Carreras</legend>
                        <div class="docente-carreras-checkboxes">
                            @foreach ($carreras as $carrera)
                                <label class="docente-carrera-checkbox-label">
                                    <input type="checkbox" name="carreras[]" value="{{ $carrera->id }}" @checked(in_array((int) $carrera->id, $carrerasSeleccionadas, true))>
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