@extends('layouts.app')

@section('title', 'Ver Docente - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class="container" style="max-width: 520px; margin: 0 auto;">
    {{-- Mismo diseño que "Información de la carrera": contenedor con header azul, cuerpo y footer --}}
    <div class="modal-view-career__container" style="max-width: 100%;">
        <div class="modal-view-career__header" style="display: flex; align-items: center; justify-content: center; position: relative; padding-right: 2.5rem;">
            <h5 class="modal-view-career__title" style="margin: 0; text-align: center;">Información del docente</h5>
            <a href="{{ route('control.teachers.index') }}" class="modal-view-career__close" aria-label="Cerrar" style="position: absolute; right: 0.75rem; text-decoration: none; color: rgba(255,255,255,0.9); font-size: 1.5rem; line-height: 1; padding: 0.25rem; border-radius: 6px;">&times;</a>
        </div>
        <div class="modal-view-career__body">
            <dl class="career-view-dl career-view-dl--styled">
                <div class="career-view-row">
                    <dt>Nombre</dt>
                    <dd>{{ $user->nombre ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Apellido paterno</dt>
                    <dd>{{ $user->apellido_paterno ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Apellido materno</dt>
                    <dd>{{ $user->apellido_materno ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Correo</dt>
                    <dd>{{ $user->email ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Teléfono</dt>
                    <dd>{{ $user->telefono ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>RFC</dt>
                    <dd>{{ $user->RFC ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Fecha de nacimiento</dt>
                    <dd>{{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') : '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Edad</dt>
                    <dd>{{ $user->edad ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Calle</dt>
                    <dd>{{ $user->address?->calle ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Colonia</dt>
                    <dd>{{ $user->address?->colonia ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Ciudad</dt>
                    <dd>{{ $user->address?->ciudad ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Estado</dt>
                    <dd>{{ $user->address?->estado ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Código postal</dt>
                    <dd>{{ $user->address?->codigo_postal ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Carrera:</dt>
                    <dd>{{ $user->academicProfile?->career?->name ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Estado</dt>
                    <dd>Inactivo</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
