@extends('layouts.app')

@section('title', 'Reticula escolar')

@vite(['resources/css/control_admin/base.css','resources/js/app.js'])

@section('content')
<div class="container">
    <div class="content-header">
        <div class="content-title">
            <h3>Reticula escolar</h3>
        </div>
    </div>
    <div class="reticula-content">
        <p class="reticula-carrera-name"><strong>Carrera:</strong> {{ $carrera->name }}</p>
        <p><strong>RVOE:</strong> {{ $carrera->official_id ?? '—' }}</p>
        {{-- Aquí puedes agregar más contenido de la retícula (materias por semestre, etc.) --}}
    </div>
</div>
@endsection
