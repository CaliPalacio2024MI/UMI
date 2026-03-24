@extends('layouts.app')

@section('title', 'Administrador de Temas')

@section('content')
<div class="container-fluid">

    <h2 style="margin-bottom:20px;">Administrador de Temas y Subtemas</h2>

    <div class="card" style="padding:20px;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Curso</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td>{{ $course->title }}</td>
                        <td>
                            <a href="{{ route('course.topic.create', $course->id) }}"
                               class="btn btn-primary">
                               Administrar temas
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No hay cursos disponibles</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection