@extends('layouts.app')

@section('title', 'Editar Tema - Biblioteca')

{{-- Cargamos el mismo CSS para mantener la estructura de 2 columnas --}}
@vite(['resources/css/Cursos/topic.css', 'resources/js/app.js'])

@section('content')
<div class="topics-container">

    <div class="topics-header">
        <div>
            <h1>Biblioteca de Temas</h1>
            <h2>Modificando: {{ $template->title }}</h2>
        </div>
        <a href="{{ route('templates.index') }}" class="btn-secondary">
            Volver al listado
        </a>
    </div>

    <div class="topics-layout">
        {{-- Columna del formulario (IZQUIERDA) --}}
        <div class="topics-form">

            <div class="form-mode-container" style="display: block; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">
                <div class="header-topic" style="background-color: #1a3a63;">
                    <h3 style="color: white; margin: 0;">Editar Tema</h3>
                </div>

                <form method="POST" action="{{ route('templates.update', $template->id) }}" enctype="multipart/form-data" style="padding: 20px; background: white;">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="title" style="font-weight: bold;">Título del Tema</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ $template->title }}" required>
                    </div>

                    <div class="form-group">
                        <label for="description" style="font-weight: bold;">Descripción Detallada</label>
                        <textarea name="description" id="description" class="form-control" rows="6">{{ $template->description }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="file" style="font-weight: bold;">Cambiar Archivo (Opcional)</label>
                        <input type="file" name="file" id="file" class="form-control">
                        @if($template->file_path)
                            <small class="text-muted">Archivo actual: {{ basename($template->file_path) }}</small>
                        @endif
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        {{-- Botón guardar cambios --}}
                        <button type="submit" class="btn-primary" style="background-color: #1a3a63; border: none; padding: 10px 20px;">
                            Guardar Cambios
                        </button>
                        <a href="{{ route('templates.index') }}" style="text-decoration: none; color: #666; margin-left: 15px; font-weight: bold;">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Columna del listado (DERECHA) --}}
        <div class="topics-list">
            <div class="list-header">
                <h3>Otros Temas en la Biblioteca</h3>
            </div>

            <div class="list-container">
                @foreach($templates as $item)
                    {{-- El borde del seleccionado --}}
                    <div class="topic-item shadow-sm" 
                         style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border: {{ $item->id == $template->id ? '2px solid #1a3a63' : '1px solid #eee' }};">
                        
                        <div class="topic-info">
                            {{-- Título --}}
                            <h4 style="margin: 0; color: {{ $item->id == $template->id ? '#1a3a63' : '#333' }}; font-weight: bold;">
                                {{ $item->title }}
                            </h4>
                            <p style="margin: 5px 0 0; color: #666; font-size: 0.85rem;">
                                {{ Str::limit($item->description, 80) }}
                            </p>
                        </div>

                        <div class="topic-actions">
                            @if($item->id != $template->id)
                            <a href="{{ route('templates.edit', $template->id) }}" title="Editar Tema" style="color: #010a16; font-size: 1.2rem; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif 
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection