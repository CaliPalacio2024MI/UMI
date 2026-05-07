@extends('layouts.app')

@section('title', 'Ver Docente - ' . session('active_institution_name'))

@vite(['resources/css/Control Admin/base.css', 'resources/js/app.js'])

@section('content')
<div class="container" style="max-width: 900px; margin: 0 auto;">
    <div class="modal-view-career__container" style="max-width: 100%;">
        <div class="modal-view-career__header" style="display: flex; align-items: center; justify-content: center; position: relative; padding-right: 2.5rem;">
            <h5 class="modal-view-career__title" style="margin: 0; text-align: center;">Información del docente</h5>
            <a href="{{ route('control.teachers.index') }}" class="modal-view-career__close" aria-label="Cerrar" style="position: absolute; right: 0.75rem; text-decoration: none; color: rgba(255,255,255,0.9); font-size: 1.5rem; line-height: 1; padding: 0.25rem; border-radius: 6px;">&times;</a>
        </div>
        <div class="modal-view-career__body">
            {{-- Misma estructura que la vista de editar, en solo lectura --}}
            <div class="form-body--view-only">
                {{-- Sección: Datos Personales --}}
                <h3><img src="{{ asset('images/icons/circle-user-solid-full.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px" aria-hidden="true"> Datos Personales</h3>
                <hr>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="nombre">Nombre(s)</label>
                        <input type="text" id="nombre" value="{{ $user->nombre ?? '—' }}" readonly>
                    </div>
                    <div class="form-field">
                        <label for="apellido_paterno">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" value="{{ $user->apellido_paterno ?? '—' }}" readonly>
                    </div>
                    <div class="form-field">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" id="apellido_materno" value="{{ $user->apellido_materno ?? '—' }}" readonly>
                    </div>
                </div>
                <div class="form-group-double">
                    <div class="form-field">
                        <label for="email">Correo Electrónico (Email)</label>
                        <input type="email" id="email" value="{{ $user->email ?? '—' }}" readonly>
                    </div>
                    <div class="form-field">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" value="{{ $user->telefono ?? '—' }}" readonly>
                    </div>
                </div>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="RFC">RFC</label>
                        <input type="text" id="RFC" value="{{ $user->RFC ?? '—' }}" readonly>
                    </div>
                    <div class="form-field">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="text" id="fecha_nacimiento" value="{{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') : '—' }}" readonly>
                    </div>
                    <div class="form-field">
                        <label for="edad">Edad</label>
                        @php
                            $edadVer = null;
                            if ($user->fecha_nacimiento) {
                                $edadVer = \Carbon\Carbon::parse($user->fecha_nacimiento)->age;
                            } elseif ($user->edad !== null && $user->edad !== '') {
                                $edadVer = (int) $user->edad;
                            }
                        @endphp
                        <input type="text" id="edad" value="{{ $edadVer !== null ? $edadVer : '—' }}" readonly>
                    </div>
                </div>

                {{-- Sección: Dirección --}}
                <h3><img src="{{ asset('images/icons/address-svgrepo-com.svg') }}" alt="" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;margin-top:-2px" aria-hidden="true"> Dirección</h3>
                <hr>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="calle">Calle</label>
                        <input type="text" id="calle" value="{{ $user->address?->calle ?? '—' }}" readonly>
                    </div>
                    <div class="form-field">
                        <label for="colonia">Colonia</label>
                        <input type="text" id="colonia" value="{{ $user->address?->colonia ?? '—' }}" readonly>
                    </div>
                    <div class="form-field"></div>
                </div>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="ciudad">Ciudad</label>
                        <input type="text" id="ciudad" value="{{ $user->address?->ciudad ?? '—' }}" readonly>
                    </div>
                    <div class="form-field">
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" value="{{ $user->address?->estado ?? '—' }}" readonly>
                    </div>
                    <div class="form-field">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" id="codigo_postal" value="{{ $user->address?->codigo_postal ?? '—' }}" readonly>
                    </div>
                </div>

                @php
                    $carrerasNombres = $user->teachingCareers->isNotEmpty()
                        ? $user->teachingCareers->pluck('name')
                        : collect(array_filter([$user->academicProfile?->career?->name]));
                    if ($carrerasNombres->isEmpty()) {
                        $carrerasTextoVista = '—';
                        $carrerasFilas = 2;
                    } else {
                        $carrerasTextoVista = $carrerasNombres->implode("\n");
                        $carrerasFilas = max(2, min(10, $carrerasNombres->count()));
                    }
                @endphp
                <div class="form-group-triple">
                    <div class="form-field docente-carreras-view-field">
                        <label for="carrera" class="docente-carreras-view-label"><img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="" width="18" height="18" aria-hidden="true"> Carreras</label>
                        <textarea id="carrera" class="docente-carreras-view-text" rows="{{ $carrerasFilas }}" readonly>{{ $carrerasTextoVista }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
