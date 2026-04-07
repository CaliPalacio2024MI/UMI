@extends('layouts.app')

@section('title', 'Añadir Temas a ' . $course->title)

@vite(['resources/css/Cursos/topic.css','resources/js/app.js'])

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
            <h1>Añadir Temas y Actividades</h1>
            <h2>Curso: {{ $course->title }}</h2>
        </div>
        <a href="{{ route('Cursos.index') }}" class="btn-secondary">
            Finalizar
        </a>
    </div>

    <div class="topics-layout">
        {{-- Columna del formulario --}}
        <div class="topics-form">
            @if ($errors->any())
                <div class="alert-danger">
                    <strong>¡Ups! Hubo algunos problemas:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div id="form-topic" class="form-mode-container" style="display: block;">
                <div class="header-topic" style="display:flex; justify-content: space-between;">
                <h3>Añadir Nuevo Tema</h3>
                </div>
                <form id="topic-form" action="{{ route('topics.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                    {{-- Título con checkbox inline --}}
                    <div class="form-group">
                        <label for="title">
                            Título del Tema
                            <input type="checkbox" name="show_title" value="1" checked style="margin-left: 10px; width: auto; height: auto;">
                        </label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    {{-- Descripción --}}
                    <div class="form-group">
                        <label for="description">Descripción Detallada del Tema</label>
                        <textarea id="description" name="description" rows="5"></textarea>
                    </div>

                    {{-- Archivo --}}
                    <div class="form-group">
                        <label for="file">Adjuntar Archivo (PDF o Video)</label>
                        <input type="file" id="file" name="file" accept=".pdf,.mp4,.webm,.avi,.mov,.wmv">
                    </div>

                    {{-- OPCIONES DE TORTUGUITA CON SEGMENTOS --}}
<div id="topic-turtle-options" style="display: none; margin-top: 15px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fafafa;">
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="show_turtle" value="1" id="topic_show_turtle_checkbox" style="width: auto; height: auto;">
            <strong>Mostrar tortuguita durante el video</strong>
        </label>
        <small style="color: #666; margin-left: 24px;">
            La tortuguita aparecerá mientras el video se reproduce
        </small>
    </div>

    <div id="topic-turtle-voice-selector" style="display: none; margin-top: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
        
        <!-- TABS -->
        <div style="display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #ddd;">
            <button type="button" class="turtle-mode-btn active" data-mode="simple" data-form="topic" style="padding: 10px 20px; border: none; background: none; cursor: pointer; border-bottom: 3px solid #4f46e5; font-weight: bold;">
                🐢 Simple
            </button>
            <button type="button" class="turtle-mode-btn" data-mode="segments" data-form="topic" style="padding: 10px 20px; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; color: #666;">
                ⏱️ Segmentos
            </button>
        </div>

        <!-- MODO SIMPLE -->
        <div id="topic-turtle-simple-mode" style="display: block;">
            <label for="topic_turtle_voice">Selecciona la tortuguita:</label>
            <select name="turtle_voice" id="topic_turtle_voice" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="0">🐢 Toby</option>
                <option value="1">🐢 Mely</option>
            </select>
            <small style="display: block; margin-top: 5px; color: #666;">
                La tortuguita aparecerá durante todo el video
            </small>
        </div>

        <!-- MODO SEGMENTOS -->
        <div id="topic-turtle-segments-mode" style="display: none;">
            <p style="margin-bottom: 15px; color: #555;">
                <strong>📹 Videos largos:</strong> Define cuándo aparece cada tortuguita
            </p>

            <div id="topic-segments-container"></div>

            <button type="button" id="topic-add-segment-btn" style="margin-top: 10px; padding: 8px 16px; background: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer;">
                ➕ Agregar Segmento
            </button>

            <small style="display: block; margin-top: 10px; color: #666;">
                💡 Tip: Formato de tiempo MM:SS (ej: 01:30)
            </small>
        </div>
    </div>
    
</div>

                     <button type="submit" class="btn-successs">+ Añadir Tema </button>

                </form>
            </div>

            {{-- 2.2 FORMULARIO DE SUBTEMA (Inicialmente oculto) --}}
            <div id="form-subtopic" class="form-mode-container" style="display: none;">
                {{-- Encabezado con el contexto del padre --}}
                <div class="header-topic">
                    <h3 id="subtopic-form-title">Añadir Nuevo Subtema</h3>
                    <p id="subtopic-context" style="color: #007bff; font-weight: bold;"></p>
                </div>
                <form id="subtopic-form" action="{{$formActions}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Necesitaremos JS para establecer esta ruta y el topic_id --}}
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <input type="hidden" name="topic_id" id="subtopic-topic-id"> 

                    {{-- Campos Subtema (simples) --}}
                    <div class="form-group">
                        <label for="subtopic-title">
                            Título del Subtema
                            <input type="checkbox" name="show_title" value="1" checked style="margin-left: 10px; width: auto; height: auto;">
                        </label>
                        <input type="text" id="subtopic-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="subtopic-description">Descripción Detallada</label>
                        <textarea id="subtopic-description" name="description" rows="5"></textarea>
                    </div>
                    {{-- Archivo --}}
                    <div class="form-group">
                        <label for="subtopic-file">Adjuntar Archivo (PDF o Video)</label>
                        <input type="file" id="subtopic-file" name="file" accept=".pdf,.mp4,.webm,.avi,.mov,.wmv">
                    </div>

                    {{-- OPCIONES DE TORTUGUITA (solo para videos en SUBTEMAS) --}}
                    <div id="subtopic-turtle-options" class="form-group" style="display: none; border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #f9f9f9; margin-top: 10px;">
                        <div style="margin-bottom: 10px;">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="show_turtle" value="1" id="subtopic_show_turtle_checkbox" style="width: auto; height: auto;">
                                <strong>Mostrar tortuguita durante el video</strong>
                            </label>
                            <small style="color: #666; margin-left: 24px;">
                                La tortuguita permanecerá visible mientras el video se reproduce
                            </small>
                        </div>
                        
                        <div id="subtopic-turtle-voice-selector" style="display: none; margin-left: 24px;">
                            <label for="subtopic_turtle_voice">¿Cuál tortuguita?</label>
                            <select name="turtle_voice" id="subtopic_turtle_voice">
                                <option value="0">🐢 Toby</option>
                                <option value="1">🐢 Mely</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-successs"> Añadir Subtema </button>
                </form>
            </div>

            {{-- 2.3 FORMULARIO DE ACTIVIDAD (Inicialmente oculto) --}}
            <div id="form-activity" class="form-mode-container" style="display: none;">
                <h3>Añadir Nueva Actividad</h3>
                <form id="activity-form" action="{{route('activities.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                    <div class="header-activity" style="display:flex; justify-content: space-between; margin-bottom: 10px;">
                        <h5>Nueva Actividad</h5>
                        <button type="submit" class="btn-primary">+ Añadir Actividad</button>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            Título de la actividad
                            <input type="checkbox" name="show_title" value="1" checked style="margin-left: 10px; width: auto; height: auto;">
                        </label>
                        <input type="text" name="title" placeholder="Título de la actividad" required>
                    </div>

                    <div class="form-group-exam">
                        <details>
                            <summary class="exam-label">
                                <input type="checkbox" name="is_final_exam" value="1" id="is_final_exam_checkbox">
                                <strong>Marcar como Examen Final</strong>
                            </summary>
                            <small class="exam-note">
                                Si se marca, esta actividad se ocultará hasta que se complete el 100% del curso.
                            </small>
                        </details>
                    </div>

                    <div class="form-group">
                        <label for="activity_type">Tipo de Actividad</label>
                        <select name="type" id="activity_type" required>
                            <option value="" disabled selected>Selecciona un tipo</option>
                            <option value="Cuestionario">Cuestionario (Quiz)</option>
                            <option value="SopaDeLetras">Sopa de Letras</option>
                            <option value="Examen">Examen (Múltiples preguntas)</option>
                            <option value="Ahorcado">Ahorcado</option>
                            <option value="Crucigrama">Crucigrama</option>
                        </select>
                    </div>

                    <div id="activity-type-container">

                        <div id="template-Cuestionario" class="activity-template" style="display: none;">
                            <div class="activity-fields-container">
                                 <div class="form-group">
                                        <label>Pregunta del cuestionario:</label>
                                        <input type="text" name="content[question]" class="form-field-cuestionario" placeholder="Escribe la pregunta aquí" disabled>
                                    
                                </div>
                                <label>Opciones de respuesta (marca la correcta):</label>
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="quiz-option">
                                        <input type="radio" name="content[correct_answer]" value="{{ $i }}" disabled>
                                        <input type="text" name="content[options][]" class="form-field-cuestionario" placeholder="Opción {{ $i + 1 }}" disabled>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div id="template-SopaDeLetras" class="activity-template" style="display: none;">
                            
                            <div class="form-group">
                                <label for="content_grid_size">Tamaño de Cuadrícula (Ej: 10 para 10x10)</label>
                                <input type="number" name="content[grid_size]" id="content_grid_size" 
                                    value="10" min="5" max="20" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label for="ws_word_input">Palabras a encontrar</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" id="ws_word_input" 
                                        placeholder="Escribe una palabra y presiona 'Añadir'" 
                                        style="flex: 1;" disabled>
                                    <button type="button" id="ws_add_word_btn" class="btn-secondary" disabled>Añadir</button>
                                </div>
                                <small>Se recomiendan palabras sin espacios ni acentos, todo en mayúsculas.</small>
                            </div>

                            <label>Palabras añadidas:</label>
                            <ul id="ws_word_list" style="list-style: disc; margin-left: 20px; min-height: 50px; background: #f4f4f4; border-radius: 4px; padding: 10px;"></ul>
                            
                            <div id="ws_hidden_inputs"></div>

                        </div>

                        <div id="template-Examen" class="activity-template" style="display: none;">
                            <div class="activity-fields-container" id="examen-questions-container">
                                {{-- Las preguntas se añadirán aquí con JS --}}
                            </div>
                            <button type="button" id="add-examen-question-btn" class="btn-secondary-exam" disabled>
                                + Añadir Pregunta al Examen
                            </button>
                        </div>

                        {{-- NUEVO: TEMPLATE AHORCADO --}}
                        <div id="template-Ahorcado" class="activity-template" style="display: none;">
                            <div class="activity-fields-container">
                                <div class="form-group">
                                    <label for="ahorcado_word">Palabra a adivinar:</label>
                                    <input type="text" name="content[word]" id="ahorcado_word" 
                                           class="form-field-ahorcado" 
                                           placeholder="Ej: PROGRAMACION" 
                                           pattern="[A-ZÑ]+" 
                                           disabled>
                                    <small>Solo letras mayúsculas sin espacios ni acentos</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="ahorcado_hint">Pista (opcional):</label>
                                    <input type="text" name="content[hint]" id="ahorcado_hint" 
                                           class="form-field-ahorcado" 
                                           placeholder="Ej: Proceso de escribir código" 
                                           disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="ahorcado_attempts">Intentos máximos:</label>
                                    <input type="number" name="content[max_attempts]" id="ahorcado_attempts" 
                                           value="6" min="3" max="10" 
                                           class="form-field-ahorcado" 
                                           disabled>
                                </div>
                            </div>
                        </div>

                        {{-- NUEVO: TEMPLATE CRUCIGRAMA --}}
                        <div id="template-Crucigrama" class="activity-template" style="display: none;">
                            <div class="activity-fields-container">
                                <div class="form-group">
                                    <label for="cw_grid_size">Tamaño de Cuadrícula:</label>
                                    <input type="number" name="content[grid_size]" id="cw_grid_size" 
                                           value="10" min="5" max="15" 
                                           class="form-field-crucigrama" 
                                           disabled>
                                </div>
                                
                                <div class="form-group">
                                    <h4>Palabras y Pistas del Crucigrama</h4>
                                    <div id="cw_words_container" style="margin-bottom: 10px;">
                                        {{-- Aquí se agregarán las palabras dinámicamente --}}
                                    </div>
                                    <button type="button" id="cw_add_word_btn" class="btn-secondary" disabled>
                                        + Añadir Palabra
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> 
                </form>
            </div>
            {{-- 2.4 FORMULARIO DE EDICIÓN DE TEMA (Inicialmente oculto) --}}
            <div id="form-edit-topic" class="form-mode-container" style="display: none;">
                <div class="header-topic" style="display:flex; justify-content: space-between;">
                    <h3>Editando Tema</h3>
                    <p id="edit-topic-context" style="color: #007bff; font-weight: bold;"></p>
                </div>
                <form id="edit-topic-form" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    {{-- Campo oculto para el ID del tema --}}
                    <input type="hidden" name="topic_id" id="edit-topic-id" value="PUT">
                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                    {{-- Título --}}
                    <div class="form-group">
                        <label for="edit-title">
                            Título del Tema
                            <input type="checkbox" name="show_title" value="1" id="edit-show-title" style="margin-left: 10px; width: auto; height: auto;">
                        </label>
                        <input type="text" id="edit-title" name="title" required>
                    </div>

                    {{-- Descripción --}}
                    <div class="form-group">
                        <label for="edit-description">Descripción Detallada del Tema</label>
                        <textarea id="edit-description" name="description" rows="5"></textarea>
                    </div>

                    {{-- Archivo --}}
                    <div class="form-group">
                        <label for="edit-file">Reemplazar Archivo (Opcional)</label>
                        <input type="file" id="edit-file" name="file" accept=".pdf,.mp4,.webm,.avi,.mov,.wmv">
                        <div id="current-file-info" style="margin-top: 5px;">
                            <small id="current-file-text"></small>
                            {{-- Campo oculto para mantener el file_path actual si no se sube nuevo archivo --}}
                            <input type="hidden" name="current_file_path" id="current-file-path">
                        </div>
                    </div>

                    {{-- OPCIONES DE TORTUGUITA CON SEGMENTOS (EDICIÓN) --}}
<div id="edit-topic-turtle-options" style="display: none; margin-top: 15px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fafafa;">
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="show_turtle" value="1" id="edit_topic_show_turtle_checkbox" style="width: auto; height: auto;">
            <strong>Mostrar tortuguita durante el video</strong>
        </label>
        <small style="color: #666; margin-left: 24px;">
            La tortuguita aparecerá mientras el video se reproduce
        </small>
    </div>

    <div id="edit-topic-turtle-voice-selector" style="display: none; margin-top: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
        
        <!-- TABS -->
        <div style="display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #ddd;">
            <button type="button" class="turtle-mode-btn active" data-mode="simple" data-form="edit" style="padding: 10px 20px; border: none; background: none; cursor: pointer; border-bottom: 3px solid #4f46e5; font-weight: bold;">
                🐢 Simple
            </button>
            <button type="button" class="turtle-mode-btn" data-mode="segments" data-form="edit" style="padding: 10px 20px; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; color: #666;">
                ⏱️ Segmentos
            </button>
        </div>

        <!-- MODO SIMPLE -->
        <div id="edit-turtle-simple-mode" style="display: block;">
            <label for="edit_topic_turtle_voice">Selecciona la tortuguita:</label>
            <select name="turtle_voice" id="edit_topic_turtle_voice" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="0">🐢 Toby</option>
                <option value="1">🐢 Mely</option>
            </select>
            <small style="display: block; margin-top: 5px; color: #666;">
                La tortuguita aparecerá durante todo el video
            </small>
        </div>

        <!-- MODO SEGMENTOS -->
        <div id="edit-turtle-segments-mode" style="display: none;">
            <p style="margin-bottom: 15px; color: #555;">
                <strong>📹 Videos largos:</strong> Define cuándo aparece cada tortuguita
            </p>

            <div id="edit-segments-container"></div>

            <button type="button" id="edit-add-segment-btn" style="margin-top: 10px; padding: 8px 16px; background: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer;">
                ➕ Agregar Segmento
            </button>

            <small style="display: block; margin-top: 10px; color: #666;">
                💡 Tip: Formato de tiempo MM:SS (ej: 01:30)
            </small>
        </div>
    </div>
    
</div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn-successs" onclick="console.log('🔍 Datos del formulario de edición:', new FormData(document.getElementById('edit-topic-form')))">Guardar Cambios</button>
                        <button type="button" id="cancel-edit-btn" class="btn-secondary">Cancelar</button>
                    </div>
                </form>
            </div>
            
        </div>   

       {{-- Columna lista de temas --}}                
<div class="topics-list">
    <div class="topics-list-header">
        <div style="margin-bottom: 5px;">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 10px;">
                <h3>Temas del Curso ({{ $course->topics->count() }})</h3>
                
                {{-- ✅ AGREGAR ESTO --}}
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input 
                        type="checkbox" 
                        name="show_welcome" 
                        id="show_welcome"
                        {{ $course->show_welcome ? 'checked' : '' }}
                        style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="font-size: 14px;">🐢 Mostrar bienvenida</span>
                </label>
            </div>
            
            <p id="selection-context" style="font-size: 0.9em; color: #555; min-height: 1.2em;"></p>
        </div>
        <div class="content-btn" style="display: flex; gap: 8px; margin-bottom: 10px;">
            <button id="mode-topic" class="btn-topic" data-mode="topic">+ Añadir Tema </button>
            <button id="mode-subtopic" class="btn-subtopic" data-mode="subtopic" disabled>+ Añadir Subtema </button>
            <button id="mode-activity" class="btn-activities" data-mode="activity" disabled>+ Añadir Actividad </button>
        </div>
    </div>
                
            {{-- Lista de temas y subtemas --}}
            <div class="topics-list-content" id="sortable-topics">
                @if ($course->finalExam)
                    @php $activity = $course->finalExam; @endphp
                    
                    <div class="topic-card final-exam-card" data-activity-id="{{ $activity->id }}" style="margin-bottom: 20px;">
                        <div class="card-body" style="border-left: 5px solid #BC8A55; padding: 15px; border-radius: 4px; background: #fffbe6;">
                            <div class="topic-header" style="align-items: center; justify-content: space-between;">
                                
                                <div>
                                    <h5 style="color: #BC8A55; font-weight: 700; margin-bottom: 5px;">
                                         EXAMEN FINAL DEL CURSO
                                    </h5>
                                    <p class="topic-title" style="font-weight: 600; font-size: 15px;">{{ $activity->title }}</p>
                                    <p style="font-size: 0.9em; color: #555;">Tipo: {{ $activity->type }} ({{ count($activity->content['questions'] ?? []) }} preguntas)</p>
                                </div>
                                
                                <div class="topic-actions">
                                    {{-- Botón eliminar actividad/examen usando la ruta existente --}}
                                    <form action="{{ route('activities.destroy', $activity) }}" method="POST" 
                                        onsubmit="return confirm('¿Eliminar el Examen Final? Esto no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" title="Eliminar Examen">
                                            <img src="{{ asset('images/icons/Vector.svg') }}" alt="Eliminar" 
                                                style="width:24px;height:24px" loading="lazy">
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                {{-- ===== MEZCLAR TOPICS Y ACTIVITIES POR ORDEN ===== --}}
                @php
                    // Obtener actividades independientes
                    $independentActivities = \App\Models\Cursos\Activities::where('course_id', $course->id)
                        ->whereNull('topic_id')
                        ->whereNull('subtopic_id')
                        ->where('is_final_exam', false)
                        ->orderBy('order')
                        ->get();
                    
                    // Mezclar topics y activities ordenados por 'order'
                    $allItems = collect();
                    
                    foreach ($course->topics as $topic) {
                        $allItems->push(['type' => 'topic', 'order' => $topic->order, 'data' => $topic]);
                    }
                    
                    foreach ($independentActivities as $activity) {
                        $allItems->push(['type' => 'activity', 'order' => $activity->order, 'data' => $activity]);
                    }
                    
                    // Ordenar TODO por 'order'
                    $allItems = $allItems->sortBy('order')->values();
                @endphp
                
                @forelse ($allItems as $item)
                    @if($item['type'] === 'topic')
                        @php $topic = $item['data']; @endphp
                        
                <div class="topic-card" data-topic-id="{{ $topic->id }}" data-topic-title="{{ $topic->title }}">
                    <div class="card-body">

                        {{-- Cabecera del tema --}}
                        <div class="topic-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="drag-handle" style="cursor: grab; font-size: 20px; color: #999;">⋮⋮</span>
                                <div>
                                    <h5 class="topic-title" style="font-weight: 600; font-size: 15px;">{{ $topic->title }}</h5>
                                    <p class="topic-description">{{ $topic->description }}</p>
                                </div>
                            </div>

                            <div class="topic-actions"> 
                                {{-- BOTÓN DE EDITAR --}}
                                <button type="button" class="btn-edit-topic"
                                        data-id="{{ $topic->id }}"
                                        data-title="{{ $topic->title }}"
                                        data-description="{{ $topic->description }}"
                                        data-file-path="{{ $topic->file_path }}"
                                        data-show-title="{{ $topic->show_title ? '1' : '0' }}"
                                        data-show-turtle="{{ $topic->show_turtle ? '1' : '0' }}"
                                        data-turtle-voice="{{ $topic->turtle_voice ?? '0' }}"
                                        data-video-segments='@json($topic->video_segments)'
                                        data-update-url="{{ route('topics.update', $topic->id) }}"
                                        title="Editar Tema">
                                    <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" 
                                        alt="Editar" style="width:24px;height:24px" loading="lazy">
                                </button>


                                {{-- Botón eliminar tema --}}
                                <form action="{{ route('topics.destroy', $topic) }}" method="POST" 
                                    onsubmit="return confirm('¿Eliminar este tema y todas sus actividades?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" title="Eliminar">
                                        <img src="{{ asset('images/icons/Vector.svg') }}" alt="Eliminar" 
                                            style="width:24px;height:24px" loading="lazy">
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Archivo adjunto --}}
                        @if ($topic->file_path)
                            <div class="topic-file">
                                <a href="{{ asset('storage/' . $topic->file_path) }}" target="_blank" class="text-decoration-none">
                                     Ver Archivo Adjunto
                                </a>
                                @if($topic->show_turtle)
                                    <span style="font-size: 14px; color: #28a745; margin-left: 10px;">
                                        🐢 Con {{ $topic->turtle_voice == 0 ? 'Toby' : 'Mely' }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Actividades del tema --}}
                        <div class="activities-list" style="margin-bottom: 5px;">
                            @if($topic->activities->count() > 0)
                                <p class="activities-label" style="margin: 0 0 0 10px;">Actividades del tema:</p>
                                @foreach($topic->activities as $activity)
                                    <div class="activity-item">
                                        <span class="activity-type">{{ ucfirst($activity->type) }}</span>
                                        <span class="activity-title">{{ $activity->title }}</span>
                                        @if($activity->show_turtle)
                                            <span style="font-size: 12px; color: #28a745;">🐢 Voz {{ $activity->turtle_voice + 1 }}</span>
                                        @endif
                                        <form action="{{ route('activities.destroy', $activity) }}" method="POST" 
                                            onsubmit="return confirm('¿Eliminar esta actividad?');" class="ms-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="delete-activity">&times;</button>
                                        </form>
                                    </div>
                                @endforeach
                            @else
                                <p class="no-activities">No hay actividades para este tema.</p>
                            @endif
                        </div>

                    </div>

                    {{-- Subtemas --}}
                    @if ($topic->subtopics->count() > 0)
                        <div class="subtopics-container">
                            <p class="subtopics-label"></p>
                            @foreach ($topic->subtopics as $subtopic)
                                <div class="subtopic-item"
                                    data-subtopic-id="{{ $subtopic->id }}" 
                                    data-subtopic-title="{{ $subtopic->title }}" 
                                    data-topic-id="{{ $topic->id }}">
                                    
                                    <div class="subtopic-header">    
                                        {{-- Título y descripción --}}
                                        <div>
                                            <h6 class="subtopic-title" style="font-size: 13px">• {{ $subtopic->title }}</h6>
                                            <p class="subtopic-description" style="margin-left: 10px">{{ $subtopic->description }}</p>
                                        </div>
                                        {{-- Botón eliminar Subtema --}}
                                        <form action="{{ route('subtopics.destroy', $subtopic) }}" method="POST" 
                                            onsubmit="return confirm('¿Eliminar este subtema?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger" title="Eliminar">
                                                <img src="{{ asset('images/icons/Vector.svg') }}" alt="Eliminar" 
                                                    style="width:24px;height:24px" loading="lazy">
                                            </button>
                                        </form>
                                    </div>
                                    {{-- Archivo adjunto --}}
                                    @if ($subtopic->file_path)
                                        <div class="topic-file">
                                            <a href="{{ asset('storage/' . $subtopic->file_path) }}" target="_blank" class="text-decoration-none">
                                                 Ver Archivo Adjunto
                                            </a>
                                            @if($subtopic->show_turtle)
                                                <span style="font-size: 14px; color: #28a745; margin-left: 10px;">
                                                    🐢 Con {{ $subtopic->turtle_voice == 0 ? 'Toby' : 'Mely' }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Actividades del subtema --}}
                                    @if($subtopic->activities->count() > 0)
                                        <p class="activities-label" style="margin: 0 0 0 10px;">Actividades del subtema:</p>
                                        @foreach($subtopic->activities as $activity)
                                            <div class="activity-item" style="margin-left: 10px;">
                                                <span class="activity-type">{{ ucfirst($activity->type) }}</span>
                                                <span class="activity-title">{{ $activity->title }}</span>
                                                @if($activity->show_turtle)
                                                    <span style="font-size: 12px; color: #28a745;">🐢 Voz {{ $activity->turtle_voice + 1 }}</span>
                                                @endif
                                                <form action="{{ route('activities.destroy', $activity) }}" method="POST" 
                                                    onsubmit="return confirm('¿Eliminar esta actividad?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="delete-activity">&times;</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                    @else
                        {{-- ===== ACTIVIDAD INDEPENDIENTE ===== --}}
                        @php $activity = $item['data']; @endphp
                        
                        <div class="topic-card" data-topic-id="activity-{{ $activity->id }}" style="border-left: 4px solid #4f46e5;">
                            <div class="topic-header">
                                <div class="drag-handle" style="cursor: grab; margin-right: 10px; color: #999;">⋮⋮</div>
                                
                                <div style="flex: 1;">
                                    <h5 class="topic-title" style="color: #4f46e5; margin: 0;">
                                        🎮 {{ $activity->title }}
                                    </h5>
                                    <span style="font-size: 12px; background: #4f46e5; color: white; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-top: 5px;">
                                        {{ ucfirst($activity->type) }}
                                    </span>
                                </div>

                                <div style="display: flex; gap: 8px;">
                                    <form action="{{ route('activities.destroy', $activity) }}" method="POST" 
                                        onsubmit="return confirm('¿Eliminar esta actividad?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" title="Eliminar">
                                            <img src="{{ asset('images/icons/Vector.svg') }}" alt="Eliminar" 
                                                style="width:24px;height:24px" loading="lazy">
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                @empty
                    <div class="no-topics">
                        <p>Aún no has añadido ningún tema a este curso.</p>
                    </div>
                @endforelse
            </div>
        </div>
    
</div>
@endsection

@once
@push('scripts')
{{-- SortableJS CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===================================================
    // 1. VARIABLES DE ESTADO Y REFERENCIAS AL DOM
    // ===================================================
    let selectedTopicId = null;
    let selectedSubtopicId = null;
    let currentMode = 'topic';

    const modeButtons = document.querySelectorAll('.content-btn button');
    const formContainers = document.querySelectorAll('.form-mode-container');
    const topicCards = document.querySelectorAll('.topic-card');
    const subtopicTopicIdField = document.getElementById('subtopic-topic-id');
    const selectionContextP = document.getElementById('selection-context');
    const editTopicForm = document.getElementById('edit-topic-form');

    // ===================================================
    // 2. DRAG & DROP CON SORTABLE.JS
    // ===================================================
    const sortableList = document.getElementById('sortable-topics');
    if (sortableList) {
        Sortable.create(sortableList, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function (evt) {
                console.log('🔄 Reordenando items...');
                
                // Recopilar nuevo orden de TODOS los items (topics y activities)
                const items = [];
                document.querySelectorAll('.topic-card[data-topic-id]').forEach((card, index) => {
                    const topicId = card.dataset.topicId;
                    
                    // Detectar si es actividad (empieza con "activity-")
                    if (topicId.startsWith('activity-')) {
                        const activityId = topicId.replace('activity-', '');
                        items.push({ 
                            type: 'activity',
                            id: parseInt(activityId), 
                            order: index 
                        });
                    } else {
                        items.push({ 
                            type: 'topic',
                            id: parseInt(topicId), 
                            order: index 
                        });
                    }
                });

                console.log('📦 Items a guardar:', items);

                // Separar topics y activities
                const topics = items.filter(item => item.type === 'topic').map(item => ({
                    id: item.id,
                    order: item.order
                }));
                
                const activities = items.filter(item => item.type === 'activity').map(item => ({
                    id: item.id,
                    order: item.order
                }));

                console.log('📦 Topics:', topics);
                console.log('📦 Activities:', activities);

                // Enviar AJAX para actualizar orden de TOPICS
                if (topics.length > 0) {
                    console.log('📤 Enviando topics:', topics);
                    
                    fetch('/topics/update-order', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ topics: topics })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                console.error('❌ Error del servidor:', text);
                                throw new Error('Error ' + response.status);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            console.log('✅ Orden de topics actualizado');
                        }
                    })
                    .catch(error => console.error('❌ Error actualizando topics:', error));
                }

                // Enviar AJAX para actualizar orden de ACTIVITIES
                if (activities.length > 0) {
                    console.log('📤 Enviando activities:', activities);
                    
                    fetch('/activities/update-order', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ activities: activities })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                console.error('❌ Error del servidor:', text);
                                throw new Error('Error ' + response.status);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            console.log('✅ Orden de activities actualizado');
                        }
                    })
                    .catch(error => console.error('❌ Error actualizando activities:', error));
                }
            }
        });
    }

    // ===================================================
    // 3. FUNCIÓN setFormMode
    // ===================================================
    function setFormMode(mode) {
        console.log("→ setFormMode activado con modo:", mode);

        currentMode = mode;

        // Oculta todos los formularios
        formContainers.forEach(container => container.style.display = 'none');

        // Quita clases y controla botones
        modeButtons.forEach(btn => {
            btn.classList.remove('active', 'btn-primary');
            btn.disabled = (mode === 'edit-topic');
        });

        // Mostrar formulario correcto
        const formToShow = document.getElementById(`form-${mode}`);
        if (formToShow) {
            console.log(`→ Mostrando formulario: form-${mode}`);
            formToShow.style.display = 'block';
        } else {
            console.warn(`⚠ No se encontró form-${mode}, mostrando form-topic por defecto`);
            document.getElementById('form-topic').style.display = 'block';
        }

        // Solo marcar botón activo si no estamos editando
        if (mode !== 'edit-topic') {
            const activeBtn = document.getElementById(`mode-${mode}`);
            if (activeBtn) {
                activeBtn.classList.add('active', 'btn-primary');
            }
        }

        // Asignar IDs de tema/subtema para actividades
        if (mode === 'activity') {
            const topicIdField = document.getElementById('activity-topic-id');
            const subtopicIdField = document.getElementById('activity-subtopic-id');
            if (selectedSubtopicId) {
                subtopicIdField.value = selectedSubtopicId;
                topicIdField.value = '';
            } else if (selectedTopicId) {
                topicIdField.value = selectedTopicId;
                subtopicIdField.value = '';
            }
        }

        console.log("✔ Formulario mostrado correctamente:", formToShow ? formToShow.id : 'ninguno');
    }

    // ===================================================
    // 4. FUNCIÓN updateSelectionState
    // ===================================================
    function updateSelectionState(topicId, subtopicId, topicTitle, subtopicTitle = null) {
        selectedTopicId = topicId;
        selectedSubtopicId = subtopicId;

        topicCards.forEach(card => card.classList.remove('selected'));
        document.querySelectorAll('.subtopic-item').forEach(card => card.classList.remove('selected'));

        let context = '';
        if (selectedSubtopicId) {
            context = `${topicTitle} > ${subtopicTitle}`;
            document.getElementById('mode-subtopic').disabled = true;
        } else if (selectedTopicId) {
            context = topicTitle;
            document.getElementById('mode-subtopic').disabled = false;
        } else {
            document.getElementById('mode-subtopic').disabled = true;
        }
        selectionContextP.textContent = context ? `Selección actual: ${context}` : '';
        
        const canAddActivity = selectedTopicId || selectedSubtopicId;
        document.getElementById('mode-activity').disabled = !canAddActivity;
    }

    // ===================================================
    // 5. EVENTOS DE MODO
    // ===================================================
    modeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const mode = this.dataset.mode;
            setFormMode(mode);

            if (mode === 'subtopic' && selectedTopicId) {
                document.getElementById('subtopic-form').action = `/topics/${selectedTopicId}/subtopics`;
                subtopicTopicIdField.value = selectedTopicId;
            }
        });
    });

    // ===================================================
    // 6. SELECCIÓN DE TEMAS Y SUBTEMAS
    // ===================================================
    topicCards.forEach(card => {
        const topicId = card.dataset.topicId;
        const topicTitle = card.dataset.topicTitle;
        
        card.addEventListener('click', function(e) {
            if (e.target.closest('.topic-actions') || e.target.closest('.drag-handle')) return;
            updateSelectionState(topicId, null, topicTitle);
            this.classList.add('selected');
            setFormMode('subtopic');
            document.getElementById('subtopic-form').action = `/topics/${selectedTopicId}/subtopics`;
            subtopicTopicIdField.value = selectedTopicId;
        });

        card.querySelectorAll('.subtopic-item').forEach(subcard => {
            const subtopicId = subcard.dataset.subtopicId;
            const subtopicTitle = subcard.dataset.subtopicTitle;
            
            subcard.addEventListener('click', function(e) {
                e.stopPropagation();
                updateSelectionState(topicId, subtopicId, topicTitle, subtopicTitle);
                this.classList.add('selected');
                setFormMode('activity');
            });
        });
    });

    // ===================================================
    // 7. SELECTOR DE TIPO DE ACTIVIDAD (SIN VIDEOS)
    // ===================================================
    const activityTypeSelect = document.getElementById('activity_type');

    if (activityTypeSelect) {
        activityTypeSelect.addEventListener('change', function () {
            const selectedType = this.value;
            const form = this.closest('form');
            
            // Ocultar TODAS las plantillas
            const allTemplates = form.querySelectorAll('.activity-template');
            allTemplates.forEach(template => {
                template.style.display = 'none';
                template.querySelectorAll('input, button, select, textarea').forEach(input => {
                    input.disabled = true;
                });
            });

            // Mostrar la plantilla seleccionada
            const activeTemplate = form.querySelector('#template-' + selectedType);
            if (activeTemplate) {
                activeTemplate.style.display = 'block';
                activeTemplate.querySelectorAll('input, button, select, textarea').forEach(input => {
                    input.disabled = false;
                });
                
                if (selectedType === 'Crucigrama') {
                    initCrucigramaForm();
                }
            }
        });
    }

    // ===================================================
    // 7.5 DETECTAR VIDEO EN TEMAS Y SUBTEMAS (TORTUGUITAS)
    // ===================================================
    
    // PARA TEMAS
    const topicFileInput = document.getElementById('file');
    const topicTurtleOptions = document.getElementById('topic-turtle-options');
    const topicShowTurtleCheckbox = document.getElementById('topic_show_turtle_checkbox');
    const topicTurtleVoiceSelector = document.getElementById('topic-turtle-voice-selector');

    if (topicFileInput && topicTurtleOptions) {
        topicFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileName = file.name.toLowerCase();
                const isVideo = fileName.endsWith('.mp4') || 
                               fileName.endsWith('.webm') || 
                               fileName.endsWith('.avi') || 
                               fileName.endsWith('.mov') ||
                               fileName.endsWith('.wmv');
                
                if (isVideo) {
                    topicTurtleOptions.style.display = 'block';
                } else {
                    topicTurtleOptions.style.display = 'none';
                    topicShowTurtleCheckbox.checked = false;
                    topicTurtleVoiceSelector.style.display = 'none';
                }
            } else {
                topicTurtleOptions.style.display = 'none';
            }
        });

        if (topicShowTurtleCheckbox) {
            topicShowTurtleCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    topicTurtleVoiceSelector.style.display = 'block';
                } else {
                    topicTurtleVoiceSelector.style.display = 'none';
                }
            });
        }
    }

    // PARA SUBTEMAS
    const subtopicFileInput = document.getElementById('subtopic-file');
    const subtopicTurtleOptions = document.getElementById('subtopic-turtle-options');
    const subtopicShowTurtleCheckbox = document.getElementById('subtopic_show_turtle_checkbox');
    const subtopicTurtleVoiceSelector = document.getElementById('subtopic-turtle-voice-selector');

    if (subtopicFileInput && subtopicTurtleOptions) {
        subtopicFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileName = file.name.toLowerCase();
                const isVideo = fileName.endsWith('.mp4') || 
                               fileName.endsWith('.webm') || 
                               fileName.endsWith('.avi') || 
                               fileName.endsWith('.mov') ||
                               fileName.endsWith('.wmv');
                
                if (isVideo) {
                    subtopicTurtleOptions.style.display = 'block';
                } else {
                    subtopicTurtleOptions.style.display = 'none';
                    subtopicShowTurtleCheckbox.checked = false;
                    subtopicTurtleVoiceSelector.style.display = 'none';
                }
            } else {
                subtopicTurtleOptions.style.display = 'none';
            }
        });

        if (subtopicShowTurtleCheckbox) {
            subtopicShowTurtleCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    subtopicTurtleVoiceSelector.style.display = 'block';
                } else {
                    subtopicTurtleVoiceSelector.style.display = 'none';
                }
            });
        }
    }

    // ===================================================
    // 8. LÓGICA SOPA DE LETRAS
    // ===================================================
    const addWordBtn = document.getElementById('ws_add_word_btn');
    const wordInput = document.getElementById('ws_word_input');
    const wordList = document.getElementById('ws_word_list');
    const hiddenInputsContainer = document.getElementById('ws_hidden_inputs');

    if (addWordBtn) {
        const addWord = () => {
            let word = wordInput.value.trim().toUpperCase();
            
            if (word === '' || word.includes(' ')) {
                alert('Por favor, escribe una sola palabra sin espacios.');
                return;
            }

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'content[words][]';
            hiddenInput.value = word;
            hiddenInputsContainer.appendChild(hiddenInput);

            const li = document.createElement('li');
            li.textContent = word;

            const removeBtn = document.createElement('span');
            removeBtn.textContent = ' [X]';
            removeBtn.style.color = 'red';
            removeBtn.style.cursor = 'pointer';
            removeBtn.onclick = () => {
                hiddenInputsContainer.removeChild(hiddenInput);
                wordList.removeChild(li);
            };
            li.appendChild(removeBtn);
            
            wordList.appendChild(li);
            wordInput.value = '';
            wordInput.focus();
        };

        addWordBtn.addEventListener('click', addWord);
        wordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addWord();
            }
        });
    }

    // ===================================================
    // 9. LÓGICA EXAMEN
    // ===================================================
    const addExamenBtn = document.getElementById('add-examen-question-btn');
    const examenContainer = document.getElementById('examen-questions-container');
    let examenQuestionCounter = 0;

    if (addExamenBtn) {
        document.getElementById('activity_type').addEventListener('change', function() {
            if (this.value === 'Examen') {
                addExamenBtn.disabled = false;
                if (examenContainer.childElementCount === 0) {
                    addExamenQuestionBlock();
                }
            } else {
                addExamenBtn.disabled = true;
            }
        });
        addExamenBtn.addEventListener('click', addExamenQuestionBlock);
    }

    function addExamenQuestionBlock() {
        const index = examenQuestionCounter++;
        const questionBlock = document.createElement('div');
        questionBlock.classList.add('quiz-question-block');
        questionBlock.style.border = '1px solid #ccc';
        questionBlock.style.padding = '10px';
        questionBlock.style.marginBottom = '10px';
        questionBlock.style.borderRadius = '8px';

        questionBlock.innerHTML = `
            <h5>Pregunta ${index + 1}</h5>
            <div class="form-group">
                <label>Texto de la Pregunta:</label>
                <input type="text" name="content[questions][${index}][question]" class="form-field-examen" required>
            </div>
            <label>Opciones (marca la correcta):</label>
            ${[0, 1, 2, 3].map(optIndex => `
                <div class="quiz-option">
                    <input type="radio" name="content[questions][${index}][correct_answer]" value="${optIndex}" required>
                    <input type="text" name="content[questions][${index}][options][]" class="form-field-examen" placeholder="Opción ${optIndex + 1}" required>
                </div>
            `).join('')}
            <button type="button" class="btn-danger-small btn-remove-question" style="margin-top: 5px;">Eliminar Pregunta</button>
        `;
        questionBlock.querySelectorAll('.form-field-examen, input[type="radio"]').forEach(el => el.disabled = false);
        questionBlock.querySelector('.btn-remove-question').addEventListener('click', function() {
            questionBlock.remove();
        });
        examenContainer.appendChild(questionBlock);
    }

    // ===================================================
    // 10. LÓGICA CRUCIGRAMA
    // ===================================================
    let crucigramaWordCounter = 0;
    
    function initCrucigramaForm() {
        const addWordBtn = document.getElementById('cw_add_word_btn');
        const wordsContainer = document.getElementById('cw_words_container');
        
        if (addWordBtn && !addWordBtn.dataset.initialized) {
            addWordBtn.dataset.initialized = 'true';
            addWordBtn.addEventListener('click', function() {
                addCrucigramaWord(wordsContainer);
            });
            
            addCrucigramaWord(wordsContainer);
        }
    }
    
    function addCrucigramaWord(container) {
        const index = crucigramaWordCounter++;
        const wordBlock = document.createElement('div');
        wordBlock.classList.add('crucigrama-word-block');
        wordBlock.style.border = '1px solid #ddd';
        wordBlock.style.padding = '10px';
        wordBlock.style.marginBottom = '10px';
        wordBlock.style.borderRadius = '6px';
        wordBlock.style.backgroundColor = '#f9f9f9';
        
        wordBlock.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h5 style="margin: 0;">Palabra ${index + 1}</h5>
                <button type="button" class="btn-danger-small btn-remove-cw-word">Eliminar</button>
            </div>
            
            <div class="form-group" style="margin-bottom: 10px;">
                <label>Palabra:</label>
                <input type="text" 
                       name="content[words][${index}][word]" 
                       placeholder="Ej: MATEMATICA" 
                       pattern="[A-ZÑ]+" 
                       required 
                       style="text-transform: uppercase;">
                <small>Solo letras mayúsculas, sin espacios ni acentos</small>
            </div>
            
            <div class="form-group" style="margin-bottom: 10px;">
                <label>Pista:</label>
                <input type="text" 
                       name="content[words][${index}][clue]" 
                       placeholder="Ej: Ciencia de los números" 
                       required>
            </div>
            
            <div class="form-group" style="margin-bottom: 10px;">
                <label>Dirección:</label>
                <select name="content[words][${index}][direction]" required>
                    <option value="horizontal">Horizontal</option>
                    <option value="vertical">Vertical</option>
                </select>
            </div>
        `;
        
        wordBlock.querySelector('.btn-remove-cw-word').addEventListener('click', function() {
            wordBlock.remove();
        });
        
        container.appendChild(wordBlock);
    }

    // ===================================================
    // 11. DELEGACIÓN DE EVENTO: EDITAR TEMA
    // ===================================================
    document.addEventListener('click', function (event) {
        if (event.target.closest('.btn-edit-topic')) {
            const btn = event.target.closest('.btn-edit-topic');
            const topicId = btn.dataset.id;
            const title = btn.dataset.title;
            const description = btn.dataset.description;
            const filePath = btn.dataset.filePath;
            const showTitle = btn.dataset.showTitle;
            const showTurtle = btn.dataset.showTurtle;
            const videoSegments = btn.dataset.videoSegments; // ✅ AGREGAR ESTA LÍNEA
            const turtleVoice = btn.dataset.turtleVoice;
            const updateUrl = btn.dataset.updateUrl;

            const editForm = document.getElementById('form-edit-topic');
            if (!editForm) {
                console.error("❌ No se encontró el formulario de edición");
                return;
            }

            editForm.querySelector('form').action = updateUrl;
            document.getElementById('edit-topic-id').value = topicId || '';
            document.getElementById('edit-title').value = title || '';
            document.getElementById('edit-description').value = description || '';
            document.getElementById('current-file-path').value = filePath || '';
            document.getElementById('edit-show-title').checked = (showTitle === '1');

            const currentFileText = document.getElementById('current-file-text');
            const editTurtleOptions = document.getElementById('edit-topic-turtle-options');
            const editShowTurtleCheckbox = document.getElementById('edit_topic_show_turtle_checkbox');
            const editTurtleVoiceSelector = document.getElementById('edit-topic-turtle-voice-selector');
            const editTurtleVoiceSelect = document.getElementById('edit_topic_turtle_voice');

            if (currentFileText) {
                if (filePath) {
                    const fileName = filePath.split('/').pop();
                    currentFileText.textContent = `Archivo actual: ${fileName}`;
                    
                    // Detectar si es video
                    const isVideo = fileName.toLowerCase().endsWith('.mp4') ||
                                   fileName.toLowerCase().endsWith('.webm') ||
                                   fileName.toLowerCase().endsWith('.avi') ||
                                   fileName.toLowerCase().endsWith('.mov') ||
                                   fileName.toLowerCase().endsWith('.wmv');
                    
                    if (isVideo && editTurtleOptions) {
    editTurtleOptions.style.display = 'block';
    
    // Cargar valores existentes
    if (showTurtle === '1') {
        editShowTurtleCheckbox.checked = true;
        editTurtleVoiceSelector.style.display = 'block';
        
        // ✅ CARGAR SEGMENTOS SI EXISTEN
        console.log('📂 Cargando datos de tortuguita...');
        console.log('videoSegments raw:', videoSegments);
        
        if (videoSegments && videoSegments !== 'null' && videoSegments !== '' && videoSegments !== '[]') {
            try {
                let segments;
                
                // Si es string, parsearlo
                if (typeof videoSegments === 'string') {
                    segments = JSON.parse(videoSegments);
                } else {
                    segments = videoSegments;
                }
                
                console.log('✅ Segmentos parseados:', segments);
                
                // Si hay segmentos, cambiar a tab de segmentos
                if (segments && segments.length > 0) {
                    console.log(`📊 Encontrados ${segments.length} segmentos, cambiando a modo segmentos...`);
                    
                    // Click en tab de segmentos
                    const editTabSegments = document.querySelector('.turtle-mode-btn[data-form="edit"][data-mode="segments"]');
                    if (editTabSegments) {
                        editTabSegments.click();
                        
                        // Esperar un poco para que el DOM se actualice
                        setTimeout(() => {
                            const editContainer = document.getElementById('edit-segments-container');
                            if (editContainer) {
                                // Limpiar container
                                editContainer.innerHTML = '';
                                
                                // Cargar cada segmento
                                segments.forEach((seg, index) => {
                                    console.log(`  ➕ Cargando segmento ${index + 1}:`, seg);
                                    addSegment('edit', seg.start, seg.end, seg.turtle.toString());
                                });
                                
                                console.log('✅ Todos los segmentos cargados!');
                            }
                        }, 100);
                    }
                } else {
                    console.log('ℹ️ No hay segmentos, usando modo simple');
                    editTurtleVoiceSelect.value = turtleVoice || '0';
                }
            } catch (e) {
                console.error('❌ Error parseando segmentos:', e);
                console.log('Usando modo simple por defecto');
                editTurtleVoiceSelect.value = turtleVoice || '0';
            }
        } else {
            console.log('ℹ️ Sin video_segments, modo simple');
            editTurtleVoiceSelect.value = turtleVoice || '0';
        }
    }
}
                } else {
                    currentFileText.textContent = 'No hay archivo adjunto.';
                }
            }

            setFormMode('edit-topic');
        }
    });

    // Detectar cambio de archivo en formulario de edición
    const editFileInput = document.getElementById('edit-file');
    const editTurtleOptions = document.getElementById('edit-topic-turtle-options');
    const editShowTurtleCheckbox = document.getElementById('edit_topic_show_turtle_checkbox');
    const editTurtleVoiceSelector = document.getElementById('edit-topic-turtle-voice-selector');

    if (editFileInput && editTurtleOptions) {
        editFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileName = file.name.toLowerCase();
                const isVideo = fileName.endsWith('.mp4') || 
                               fileName.endsWith('.webm') || 
                               fileName.endsWith('.avi') || 
                               fileName.endsWith('.mov') ||
                               fileName.endsWith('.wmv');
                
                if (isVideo) {
                    editTurtleOptions.style.display = 'block';
                } else {
                    editTurtleOptions.style.display = 'none';
                    editShowTurtleCheckbox.checked = false;
                    editTurtleVoiceSelector.style.display = 'none';
                }
            }
        });

        if (editShowTurtleCheckbox) {
            editShowTurtleCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    editTurtleVoiceSelector.style.display = 'block';
                } else {
                    editTurtleVoiceSelector.style.display = 'none';
                }
            });
        }
    }

    // ===================================================
    // 12. BOTÓN CANCELAR EDICIÓN
    // ===================================================
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            setFormMode('topic');
        });
    }

    // ===================================================
    // 13. ESTADO INICIAL
    // ===================================================
    setFormMode(currentMode);
});

// ===================================================
// MANEJO DE SEGMENTOS DE VIDEO
// ===================================================

let topicSegmentCount = 0;
let subtopicSegmentCount = 0;
let editSegmentCount = 0; // ✅ AGREGAR

// TABS SIMPLE/SEGMENTOS
document.querySelectorAll('.turtle-mode-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const mode = this.dataset.mode;
        const form = this.dataset.form;
        
        const tabs = this.parentElement.querySelectorAll('.turtle-mode-btn');
        tabs.forEach(t => {
            t.classList.remove('active');
            t.style.borderBottom = '3px solid transparent';
            t.style.fontWeight = 'normal';
            t.style.color = '#666';
        });
        
        this.classList.add('active');
        this.style.borderBottom = '3px solid #4f46e5';
        this.style.fontWeight = 'bold';
        this.style.color = '#000';
        
        const simpleMode = document.getElementById(`${form}-turtle-simple-mode`);
        const segmentsMode = document.getElementById(`${form}-turtle-segments-mode`);
        const container = document.getElementById(`${form}-segments-container`);
        
        if (mode === 'simple') {
            simpleMode.style.display = 'block';
            segmentsMode.style.display = 'none';
        } else {
            simpleMode.style.display = 'none';
            segmentsMode.style.display = 'block';
            if (container.children.length === 0) {
                addSegment(form, '00:00', '', '0');
            }
        }
    });
});

// BOTÓN AGREGAR SEGMENTO - TEMA
const topicAddBtn = document.getElementById('topic-add-segment-btn');
if (topicAddBtn) {
    topicAddBtn.addEventListener('click', () => addSegment('topic', '', '', '0'));
}

// BOTÓN AGREGAR SEGMENTO - SUBTEMA
const subtopicAddBtn = document.getElementById('subtopic-add-segment-btn');
if (subtopicAddBtn) {
    subtopicAddBtn.addEventListener('click', () => addSegment('subtopic', '', '', '0'));
}

function addSegment(form, start = '', end = '', turtle = '0') {
    const count = form === 'topic' ? ++topicSegmentCount : (form === 'subtopic' ? ++subtopicSegmentCount : ++editSegmentCount);
    const container = document.getElementById(`${form}-segments-container`);
    
    console.log(`➕ Agregando segmento #${count} a formulario: ${form}`, {start, end, turtle}); // ✅ AGREGAR ESTE LOG
    
    const div = document.createElement('div');
    // ... resto del código
    div.style.cssText = 'display:flex;gap:10px;align-items:center;margin-bottom:10px;padding:10px;border:1px solid #ddd;border-radius:4px;background:white';
    div.innerHTML = `
        <div style="flex:1">
            <label style="font-size:12px;color:#666">Inicio</label>
            <input type="text" name="video_segments[${count}][start]" placeholder="00:00" value="${start}" 
                   class="segment-time" style="width:100%;padding:5px;border:1px solid #ddd;border-radius:4px" pattern="[0-9]{2}:[0-9]{2}">
        </div>
        <div style="flex:1">
            <label style="font-size:12px;color:#666">Fin</label>
            <input type="text" name="video_segments[${count}][end]" placeholder="01:30" value="${end}"
                   class="segment-time" style="width:100%;padding:5px;border:1px solid #ddd;border-radius:4px" pattern="[0-9]{2}:[0-9]{2}">
        </div>
        <div style="flex:1.5">
            <label style="font-size:12px;color:#666">Tortuguita</label>
            <select name="video_segments[${count}][turtle]" style="width:100%;padding:5px;border:1px solid #ddd;border-radius:4px">
                <option value="0" ${turtle==='0'?'selected':''}>🐢 Toby</option>
                <option value="1" ${turtle==='1'?'selected':''}>🐢 Mely</option>
            </select>
        </div>
        <button type="button" class="remove-segment" style="padding:8px 12px;background:#ff5252;color:white;border:none;border-radius:4px;cursor:pointer;align-self:flex-end">🗑️</button>
    `;
    
    div.querySelector('.remove-segment').addEventListener('click', () => {
        if (container.children.length > 1) div.remove();
        else alert('Debe haber al menos un segmento');
    });
    
    container.appendChild(div);
}

// VALIDAR FORMATO TIEMPO
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const inputs = form.querySelectorAll('.segment-time');
        for (let input of inputs) {
            if (input.value && !/^[0-9]{2}:[0-9]{2}$/.test(input.value)) {
                e.preventDefault();
                alert(`Formato incorrecto: "${input.value}"\nUsa MM:SS (ej: 01:30)`);
                input.focus();
                return false;
            }
        }
    });
});

// BOTÓN AGREGAR SEGMENTO - EDICIÓN
const editAddBtn = document.getElementById('edit-add-segment-btn');
if (editAddBtn) {
    editAddBtn.addEventListener('click', () => addSegment('edit', '', '', '0'));
}


// 🔍 DEBUG TEMPORAL - Ver qué se envía en edición
const editFormDebug = document.getElementById('edit-topic-form');
if (editFormDebug) {
    editFormDebug.addEventListener('submit', function(e) {
        const formData = new FormData(this);
        
        console.log('📤 ENVIANDO FORMULARIO DE EDICIÓN:');
        console.log('='.repeat(50));
        
        for (let [key, value] of formData.entries()) {
            if (key.includes('video_segments')) {
                console.log(`✅ ${key} = ${value}`);
            }
        }
        
        console.log('='.repeat(50));
        
        // Verificar si hay inputs de segmentos en el DOM
        const segmentInputs = this.querySelectorAll('[name^="video_segments"]');
        console.log(`📊 Total de inputs de segmentos encontrados: ${segmentInputs.length}`);
        
        segmentInputs.forEach(input => {
            console.log(`  - ${input.name} = ${input.value}`);
        });
    });
}

</script>

<script>
// ========== AUTO-GUARDAR CHECKBOX DE BIENVENIDA ==========
document.addEventListener('DOMContentLoaded', function() {
    const showWelcomeCheckbox = document.getElementById('show_welcome');
    
    if (showWelcomeCheckbox) {
        showWelcomeCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            const courseId = {{ $course->id }};
            
            console.log('🐢 Guardando show_welcome:', isChecked);
            
            // Crear FormData
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');
            formData.append('title', '{{ $course->title }}');
            formData.append('description', `{!! addslashes($course->description ?? '') !!}`);
            formData.append('show_welcome', isChecked ? '1' : '0');
            
            // Hacer petición AJAX
            fetch(`/cursos/${courseId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la petición');
                }
                return response.text();
            })
            .then(data => {
                console.log('✅ Show welcome guardado correctamente');
                
                // Mostrar feedback visual (borde verde)
                showWelcomeCheckbox.style.outline = '2px solid #4CAF50';
                setTimeout(() => {
                    showWelcomeCheckbox.style.outline = 'none';
                }, 1000);
            })
            .catch(error => {
                console.error('❌ Error guardando show_welcome:', error);
                alert('Error al guardar la configuración de bienvenida');
                
                // Revertir checkbox
                showWelcomeCheckbox.checked = !isChecked;
            });
        });
    }
});
</script>

@endpush
@endonce