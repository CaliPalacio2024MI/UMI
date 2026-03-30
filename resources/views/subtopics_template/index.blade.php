@extends('layouts.app')

@section('title', 'Biblioteca de Subtemas')

{{-- Importante: Cargamos el CSS que hace que las columnas funcionen --}}
@vite(['resources/css/Cursos/topic.css', 'resources/js/app.js'])

@section('content')
<div class="topics-container">

    {{-- Mensaje de éxito --}}
    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="topics-header">
        <div>
            <h1>Biblioteca de Subtemas</h1>
            <h2>Gestiona tus plantillas de subtemas</h2>
        </div>
        {{-- Quitamos el botón de finalizar porque aquí es gestión libre --}}
    </div>

    <div class="topics-layout">
        {{-- Columna del formulario (IZQUIERDA) --}}
        <div class="topics-form">
            <div id="form-topic" class="form-mode-container" style="display: block;">
                <div class="header-topic">
                    <h3>Añadir Nuevo Subtema</h3>
                </div>

                <form method="POST" action="{{ route('subtopics_template.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="title">Título del Subtema</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Descripción Detallada del Subtema</label>
                        <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="file">Adjuntar Archivo (PDF o Video)</label>
                        <input type="file" name="file" id="file" class="form-control">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">+ Añadir Subtema</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Columna del listado (DERECHA) --}}
        <div class="topics-list">
            <div class="list-header">
                <h3>Subtemas en la Biblioteca ({{ $templates->count() }})</h3>
            </div>

            <div class="list-container">
                @forelse($templates as $template)
                    <div class="topic-item shadow-sm" style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #eee;">
                        
                        <div class="topic-info">
                            <h4 style="margin: 0; color: #333; font-weight: bold;">{{ $template->title }}</h4>
                            <p style="margin: 5px 0 0; color: #666; font-size: 0.9rem;">
                                {{ Str::limit($template->description, 100) }}
                            </p>
                        </div>

                        <div class="topic-actions" style="display: flex; gap: 10px; align-items: center;">
                            {{-- Icono de editar --}}
                            <a href="{{ route('subtopics_template.edit', $template->id) }}" title="Editar Subtema" style="color: #010a16; font-size: 1.2rem; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- Icono de borrar --}}
                            <form action="{{ route('subtopics_template.destroy', $template->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este subtema de la biblioteca?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar Subtema" style="background: none; border: none; color: #df3c36; font-size: 1.2rem; cursor: pointer; padding: 0; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-backspace"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="empty-message">Aún no has añadido ningún subtema a esta biblioteca.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection