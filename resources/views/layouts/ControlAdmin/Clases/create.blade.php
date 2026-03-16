@extends('layouts.app')

@section('title', 'Agregar alumnos a la clase - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class="container" style="max-width: 700px;">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="content-header">
        <div class="content-title">
            <h3>Agregar alumnos a la clase</h3>
        </div>
        <a href="{{ route('control.classes.index') }}" class="btn btn-sm btn-outline-secondary">Volver a Clases</a>
    </div>
    <p><strong>{{ $clase->carrera->name }}</strong> — {{ $clase->materia->nombre }} (semestre {{ $clase->materia->semestre ?? 'N/A' }})</p>
    <p class="text-muted small">Solo se muestran alumnos con status <strong>Alumno Activo</strong> de la misma carrera y semestre.</p>

    <form action="{{ route('control.classes.store') }}" method="POST">
        @csrf
        <input type="hidden" name="horario_clase_id" value="{{ $clase->id }}">
        <div class="form-group">
            <label>Seleccione alumnos</label>
            @if($alumnosDisponibles->isEmpty())
                <p class="text-muted">No hay alumnos activos elegibles para esta carrera/semestre.</p>
            @else
                <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 12px;">
                    @foreach($alumnosDisponibles as $alumno)
                        <label style="display: flex; align-items: center; gap: 8px; padding: 6px 0;">
                            <input type="checkbox" name="alumnos[]" value="{{ $alumno->id }}" {{ in_array($alumno->id, $alumnosInscritos) ? 'checked' : '' }}>
                            <span>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} — {{ $alumno->academicProfile->matricula ?? 'Sin matrícula' }} (Sem. {{ $alumno->academicProfile->semestre ?? '?' }})</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
        <div style="margin-top: 1rem; display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary" {{ $alumnosDisponibles->isEmpty() ? 'disabled' : '' }}>Guardar alumnos</button>
            <a href="{{ route('control.classes.show', $clase->id) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
