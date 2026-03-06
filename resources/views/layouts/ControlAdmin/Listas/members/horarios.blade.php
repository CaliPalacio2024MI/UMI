@extends('layouts.app')

@section('title', 'Horario de Docente - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class="container" style="max-width: 980px; margin: 0 auto;">
    <div class="modal-view-career__container" style="max-width: 100%;">
        <div class="modal-view-career__header" style="display: flex; align-items: center; justify-content: center; position: relative; padding-right: 2.5rem;">
            <h5 class="modal-view-career__title" style="margin: 0; text-align: center;">
                Horario de Docente
            </h5>
            <a href="{{ route('control.teachers.index') }}" class="modal-view-career__close" aria-label="Cerrar"
               style="position: absolute; right: 0.75rem; text-decoration: none; color: rgba(255,255,255,0.9); font-size: 1.5rem; line-height: 1; padding: 0.25rem; border-radius: 6px;">
                &times;
            </a>
        </div>

        <div class="modal-view-career__body">
            @include('layouts.ControlAdmin.Listas.members.partials.horarios_body', ['user' => $user, 'horarios' => $horarios])
        </div>
    </div>
</div>
@endsection

