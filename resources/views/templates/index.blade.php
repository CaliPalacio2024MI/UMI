@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h2 style="margin-bottom:20px; font-weight: bold;">Biblioteca de Temas</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{route('templates.create') }}" class="btn btn-primary mb-3" style="font-weight: bold;">
        Crear Nuevo Tema
    </a>

    <div class="card" style="padding:20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Titulo</th>
                    <th>Descripcion</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td style="vertical-align: middle;">{{ $template->title }}</td>
                        <td style="vertical-align: middle;">{{ Str::limit($template->description, 70) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No hay plantillas de temas creados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
