@extends('layouts.app')

@section('title', ($tituloHorario ?? 'Horario de Docente') . ' - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class="container" style="max-width: 1400px; margin: 0 auto;">
    <div class="horario-page-toolbar" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <h5 style="margin: 0; color: #002366; font-size: 1.5rem;">Horario de {{ trim($user->nombre . ' ' . ($user->apellido_paterno ?? '') . ' ' . ($user->apellido_materno ?? '')) ?: 'Docente' }}</h5>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <button type="button" class="btn btn--secondary horario-export-pdf-btn" aria-label="Exportar a PDF">
                <i class="fa-solid fa-file-export" style="margin-right: 6px;"></i> Exportar
            </button>
            <a href="{{ isset($tituloHorario) && $tituloHorario === 'Horario de Alumno' ? (request()->routeIs('control.*') ? route('control.students.index') : route('escolar.students.index')) : route('control.teachers.index') }}" class="btn btn--secondary horario-close-btn" style="text-decoration: none; font-size: 1.5rem; line-height: 1; padding: 0.25rem 0.5rem; min-width: auto; color: #666;" aria-label="Cerrar">&times;</a>
        </div>
    </div>
    @include('layouts.ControlAdmin.Listas.members.partials.horarios_body', ['user' => $user, 'horarios' => $horarios, 'esAlumno' => isset($tituloHorario) && $tituloHorario === 'Horario de Alumno', 'materiaLabels' => $materiaLabels ?? [], 'horarioResumenPorClase' => $horarioResumenPorClase ?? []])
</div>
@endsection

