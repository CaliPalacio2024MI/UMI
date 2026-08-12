@extends('layouts.app')

@section('title', $course->title)

@section('content')

@if($course->show_welcome)
<div id="welcomeModal" class="welcome-modal" style="display: none;">
    <div class="welcome-modal-content">

        <div class="turtles-container">

            <!-- Toby -->
    <div class="welcome-turtle-box">
        <div class="welcome-turtle-wrapper">
            <img src="{{ asset('videos/welcome/toby.png') }}" id="welcome-toby-static" class="welcome-turtle-static welcome-active">
            <video id="welcome-toby-video" class="welcome-turtle-video" muted playsinline loop>
                <source src="{{ asset('videos/tortuguita-hablando.webm') }}" type="video/webm">
            </video>
        </div>
        <p class="welcome-turtle-name">Toby</p>
    </div>
    <!-- Mely -->
    <div class="welcome-turtle-box">
        <div class="welcome-turtle-wrapper">
            <img src="{{ asset('videos/welcome/mely.png') }}" id="welcome-mely-static" class="welcome-turtle-static welcome-active">
            <video id="welcome-mely-video" class="welcome-turtle-video" muted playsinline loop>
                <source src="{{ asset('videos/tortuguita1-hablando.webm') }}" type="video/webm">
            </video>
        </div>
        <p class="welcome-turtle-name">Mely</p>
    </div>

        </div>

        <button class="welcome-skip-btn" onclick="closeWelcomeModal()">
            Saltar presentación
        </button>
    </div>
</div>
@endif

{{-- Modal para validación de preguntas --}}
<div id="questionModal" class="question-modal" style="display: none;">
    <div class="modal-overlay" onclick="closeQuestionModal()"></div>
    <div class="modal-content">
        <div class="modal-icon">⚠️</div>
        <h3>Responde la pregunta actual</h3>
        <p>Por favor selecciona una respuesta antes de continuar.</p>
        <p class="modal-hint">💡 Después podrás regresar a esta pregunta si no estás seguro.</p>
        <button onclick="closeQuestionModal()" class="modal-btn">Entendido</button>
    </div>
</div>

{{-- Modal para Autoplay en juegos --}}
<div id="gameModal" class="question-modal" style="display: none;">
    <div class="modal-overlay" onclick="closeGameModal()"></div>
    <div class="modal-content">
        <div class="modal-icon">🎮</div>
        <h3>Modo Automático Pausado</h3>
        <p>El modo automático se pausa para que disfrutes el juego.</p>
        <p class="modal-hint">🎯 Puedes continuar cuando finalices la actividad.</p>
        <button onclick="closeGameModal()" class="modal-btn">¡Entendido!</button>
    </div>
</div>
{{-- MODAL QR --}}
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content qr-modal-content">

            <div class="qr-header">
                <h5>Escanear código QR</h5>
            </div>
<div class="qr-body">
    <video id="camera-preview"
           autoplay
           playsinline
           muted
           style="width:100%; max-width:480px; height:360px; background:#000; object-fit:cover;">
    </video>
</div>

            <div class="qr-footer">
                <button type="button" class="btn-cancel-qr" data-bs-dismiss="modal">
                    Cancelar
                </button>
            </div>

        </div>
    </div>
</div>


{{-- ===== SOLO HÍBRIDA ===== --}}
@if(in_array($course->modality, ['hibrida']))

        <h2>📚 {{ $course->title }}</h2>
        <p>Cursos disponibles</p>
        <div class="course-controls" style="justify-content: flex-end;">
            <button class="exit" onclick="window.location.href='{{ route('Cursos.index') }}'">
                Salir
            </button>
        </div>
    @endif
    {{-- ===== CURSOS DENTRO DE HÍBRIDO (FULL FUNCIONAL) ===== --}}
@if($course->modality === 'hibrida')

    @php
        $hybridCourses = $course->hybridCourses()->get();
    @endphp

    @if($hybridCourses->count() > 0)

        <div class="courses-container">

            @foreach ($hybridCourses as $courses)

                <div class="course-card {{ !$courses->active ? 'course-disabled' : '' }}">

                    <a href="{{ route('course.show', $courses) }}" class="course-card-show">

                        {{-- IMAGEN --}}
                        @if($courses->image)
                            <img src="{{ asset('storage/' . $courses->image) }}" alt="Curso">
                        @else
                            <img src="https://via.placeholder.com/400x200?text=Curso" alt="Curso">
                        @endif

                        {{-- INFO --}}
                        <div class="course-info">
                            <h3 class="course-title">{{ $courses->title }}</h3>
                            <p class="course-description">{{ $courses->description }}</p>

                            <div class="course-meta">
                                <span>Horas: {{ $courses->hours }}</span>
                            </div>
                        </div>

                    </a>

                    {{-- ACCIONES (IGUAL QUE INDEX) --}}
                    <div class="btn-display">

                        {{-- HORARIOS --}}
                        @if(in_array($courses->modality, ['presencial', 'hibrida']))
                            <a href="{{ route('courses.sessions.index', $courses) }}" class="btn-action">
                                <i class="fa-regular fa-clock"></i>
                            </a>
                        @endif

                        {{-- EDITAR --}}
                        @can('update', $courses)
                            <a href="{{ route('courses.edit', $courses) }}" class="btn-edit">
                                <img src="{{asset('images/icons/pen-to-square-solid-full.svg')}}"
                                     style="width:27px;height:27px">
                            </a>
                        @endcan

                        {{-- ELIMINAR --}}
                        @can('delete', $courses)
                            <form action="{{ route('courses.destroy', $courses) }}" method="POST">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="btn-delete"
                                    onclick="return confirm('¿Eliminar el curso?')">
                                    <img src="{{asset('images/icons/Vector.svg')}}"
                                         style="width:27px;height:19px">
                                </button>
                            </form>
                        @endcan

                        {{-- ICONO MODALIDAD --}}
                        @php
                            $icon = match($courses->modality) {
                               'virtual' => 'fa-solid fa-computer',
                               'presencial' => 'fa-solid fa-user',
                               'hibrida' => 'fa-solid fa-book-open-reader',
                               default => 'fa-solid fa-book'
                            };
                        @endphp

                        <span class="course-modality">
                            <i class="{{ $icon }}"></i>
                        </span>

                    </div>

                </div>

            @endforeach

        </div>

    @else
        <div class="no-courses-message">
            <p>No hay cursos afiliados.</p>
        </div>
    @endif
@endif




@if($course->modality !== 'hibrida')
<div class="course-layout">

    {{-- ===== MENU ===== --}}

<aside class="course-menu">
    <h3>📚 {{ $course->title }}</h3>


    @php
        // Obtener topics y actividades independientes
        $independentActivities = \App\Models\Cursos\Activities::where('course_id', $course->id)
            ->whereNull('topic_id')
            ->whereNull('subtopic_id')
            ->where('is_final_exam', false)
            ->get();

        // Mezclar todos los items por orden
        $allItems = collect();
        foreach ($course->topics as $topic) {
            $allItems->push(['type' => 'topic', 'order' => $topic->order, 'data' => $topic]);
        }
        foreach ($independentActivities as $activity) {
            $allItems->push(['type' => 'activity', 'order' => $activity->order, 'data' => $activity]);
        }
        $allItems = $allItems->sortBy('order')->values();

        // Contador para numerar SOLO los temas

        $topicNumber = 0;
    @endphp

    @foreach ($allItems as $item)
        @if($item['type'] === 'topic')
            @php
                $topic = $item['data'];
                $topicNumber++; // Incrementar contador solo para temas
            @endphp

            <div class="syllabus-link" data-target="#content-topic-{{ $topic->id }}">
                {{ $topicNumber }}. {{ $topic->title }}
            </div>

            @foreach ($topic->subtopics as $subtopic)
                <div class="syllabus-link sub" data-target="#content-subtopic-{{ $subtopic->id }}">
                    • {{ $subtopic->title }}
                </div>

                @foreach ($subtopic->activities as $activity)
                    <div class="syllabus-link act" data-target="#content-activity-{{ $activity->id }}" data-activity-id="{{ $activity->id }}">
                        ▶ {{ $activity->title }}
                    </div>
                @endforeach
            @endforeach

            @foreach ($topic->activities as $activity)
                <div class="syllabus-link act" data-target="#content-activity-{{ $activity->id }}" data-activity-id="{{ $activity->id }}">
                    ▶ {{ $activity->title }}
                </div>
            @endforeach

        @else
            {{-- ACTIVIDAD INDEPENDIENTE (sin número) --}}
            @php $activity = $item['data']; @endphp
            <div class="syllabus-link" data-target="#content-activity-{{ $activity->id }}" data-activity-id="{{ $activity->id }}">
                🎮 {{ $activity->title }}
            </div>
        @endif
    @endforeach


@if($course->modality === 'presencial')

    <div class="course-sessions-box">

        @foreach($course->sessions as $session)

            <div class="course-session-item"
                 onclick="showSessionTable(
                    '{{ $session->id }}',
                    '{{ $session->date }}',
                    '{{ $session->start_time }}',
                    '{{ $session->end_time }}',
                    '{{ $session->attendance_enabled ? 1 : 0 }}'
                    )">

                <div class="course-session-info">

                    <div class="course-session-date">
                        📅 {{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}
                    </div>

                    <div class="course-session-time">
                        🕒 {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}
                        -
                        {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                    </div>

                </div>

                <span class="session-status {{ $session->attendance_enabled ? 'active' : 'inactive' }}">
                    {{ $session->attendance_enabled ? 'Activo' : 'Inactivo' }}
                </span>

            </div>

        @endforeach

    </div>

@endif

{{-- EXAMEN FINAL --}}
@if($finalExamActivity)
    <a href="#content-final-exam"
       class="syllabus-link {{ $progress >= 51 ? '' : 'locked' }}"
       data-target="#content-final-exam"
       @if($progress < 51) onclick="event.preventDefault(); alert('Debes completar el curso para acceder al examen final.');" @endif>

        @if($finalExamData)
            ✅ Examen Final ({{ $finalExamData->score }}%)
        @elseif($progress >= 100)
            🏆 Examen Final
        @else
            🔒 Examen Final ({{ round($progress) }}%)
        @endif

    </a>
@endif
</aside>


{{-- ===== VIEWER ===== --}}
<main class="course-viewer">
    @if($course->modality === 'presencial')
<div class="course-controls presencial-actions">

    <a id="btn-download-session-pdf"
       href="#"
       class="btn-presencial-action"
       style="display: none;">
        <i class="fa-solid fa-download"></i>
        Descargar PDF
    </a>

    <button type="button"
            id="btn-scan-qr"
            class="btn-presencial-action"
            style="display: none;"
            onclick="abrirModalQR()">
        <i class="fa-solid fa-qrcode"></i>
        Escanear QR
    </button>
    <button class="btn-presencial-action"
            onclick="window.location.href='{{ route('Cursos.index') }}'">

        Salir

    </button>

</div>
    <div id="session-table-container" class="presencial-table-viewer">

        <div class="empty-session-message">
            Selecciona un horario para ver el grupo
        </div>

    </div>

    @foreach($course->sessions as $session)

        @php
            $group = $session->groups->first();
            $hosts = collect([]);

            if ($group) {
                if (method_exists($group, 'hosts')) {
                    $hosts = $group->hosts;
                } elseif ($group->hosts_json) {
                    $hostIds = json_decode($group->hosts_json, true) ?? [];

                    $hosts = \App\Models\Users\User::whereIn('id', $hostIds)
                        ->with(['department', 'workstation'])
                        ->get();
                }
            }
        @endphp
<div id="session-table-data-{{ $session->id }}"
     style="display:none;">

    @if($hosts->count())

        <table class="selected-users-table presencial-users-table">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>RFC</th>
                    <th>Departamento</th>
                    <th>Puesto</th>
                    <th>Asistencia</th>
                </tr>
            </thead>

            <tbody>

                @foreach($hosts as $index => $host)

                    @php
                        $hostRfc = strtoupper(trim($host->RFC ?? $host->rfc ?? ''));

                        $sessionAttendances = $attendanceMap[$session->id] ?? collect();

                        $attendance = $sessionAttendances->firstWhere('rfc', $hostRfc);

                        $hasAttendance = $attendance ? true : false;
                    @endphp

                    <tr data-rfc="{{ $hostRfc }}"
                        class="{{ $hasAttendance ? 'attendance-row-present' : '' }}">

                        {{-- NÚMERO DE LISTA --}}
                        <td>
                            {{ $index + 1 }}
                        </td>

                        {{-- NOMBRE --}}
                        <td>
                            {{ $host->nombre }}
                        </td>

                        {{-- APELLIDO PATERNO --}}
                        <td>
                            {{ $host->apellido_paterno }}
                        </td>

                        {{-- APELLIDO MATERNO --}}
                        <td>
                            {{ $host->apellido_materno }}
                        </td>

                        {{-- RFC --}}
                        <td>
                            {{ $host->RFC ?? 'Sin RFC' }}
                        </td>

                        {{-- DEPARTAMENTO --}}
                        <td>
                            {{ optional($host->department)->name ?? 'Sin departamento' }}
                        </td>

                        {{-- PUESTO --}}
                        <td>
                            {{ optional($host->workstation)->name ?? 'Sin puesto' }}
                        </td>

                        {{-- ASISTENCIA --}}
                        <td class="attendance-status">
                            <span class="attendance-badge {{ $hasAttendance ? 'present' : 'pending' }}">
                                {{ $hasAttendance ? 'Asistió' : 'Pendiente' }}
                            </span>
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p class="empty">
            No hay usuarios asignados a este horario.
        </p>

    @endif

</div>

    @endforeach


@endif

    {{-- ===== CONTROLES SOLO VIRTUAL ===== --}}
   @if($course->modality === 'virtual')
    <div class="course-controls">

        <button id="btnPrev">⏮<br>Anterior</button>
        <button id="btnAutoplay">▶️<br>Autoplay</button>
        <button id="btnNext">⏭<br>Siguiente</button>

        <div class="right-controls">

            @if(isset($periods) && $periods->count() > 0)
                <button class="btn-guide"
                    onclick="window.location.href='{{ route('courses.periods.attendance', [$course, $periods->first()]) }}'">
                    📊 Asistencia
                </button>
            @endif

            @if($course->guide_material_path)
                <button class="btn-guide" onclick="openGuide('{{ asset('storage/' . $course->guide_material_path) }}')">
                    📘 Guía del curso
                </button>
            @else
                <button class="btn-guide" onclick="showNoGuideMessage()">
                    📘 Guía del curso
                </button>
            @endif

            <button class="exit" onclick="window.history.back()">Salir</button>

        </div>
    </div>
@endif


        <div class="course-progress">
            <div class="course-progress-bar"></div>
        </div>

        @foreach ($course->topics as $topic)
            <section class="content-panel" id="content-topic-{{ $topic->id }}"
                     data-show-turtle="{{ $topic->show_turtle ? '1' : '0' }}"
                     data-turtle-voice="{{ $topic->turtle_voice ?? '0' }}"
                     @if($topic->video_segments) data-video-segments='@json($topic->video_segments)' @endif>
                @if($topic->show_title)
                    <h2>{{ $topic->title }}</h2>
                @endif
                <p>{{ $topic->description }}</p>

                @if ($topic->file_path)
                    @if (Str::endsWith($topic->file_path, '.pdf'))
                        {{-- VISOR PDF CON NAVEGACIÓN --}}
                        <div class="file-viewer">
                            <div class="exam-navigation" style="margin-bottom: 15px;">
                                <button type="button" class="exam-nav-btn pdf-nav-btn" data-pdf-id="topic-{{ $topic->id }}" data-action="prev" disabled>
                                    ⬅ Anterior
                                </button>
                                <span class="question-counter">
                                    Página <span class="pdf-current-page" data-pdf-id="topic-{{ $topic->id }}">1</span> de <span class="pdf-total-pages" data-pdf-id="topic-{{ $topic->id }}">...</span>
                                </span>
                                <button type="button" class="exam-nav-btn pdf-nav-btn" data-pdf-id="topic-{{ $topic->id }}" data-action="next">
                                    Siguiente ➡
                                </button>
                            </div>

                            <div style="overflow: auto; max-height: 800px; border: 1px solid #ddd; background: #f5f5f5;">
                                <canvas id="pdf-canvas-topic-{{ $topic->id }}"

                                        data-pdf-id="topic-{{ $topic->id }}"
                                        data-pdf-url="{{ asset('storage/'.$topic->file_path) }}"
                                        style="display: block; margin: 0 auto;"></canvas>
                            </div>
                        </div>
                    @else
                        {{-- OTROS ARCHIVOS --}}
                        <div class="file-viewer">
                            <iframe src="{{ asset('storage/'.$topic->file_path) }}" class="pdf-frame"></iframe>
                        </div>
                    @endif
                @endif
            </section>

            @foreach ($topic->subtopics as $subtopic)
                <section class="content-panel" id="content-subtopic-{{ $subtopic->id }}"
                         data-show-turtle="{{ $subtopic->show_turtle ? '1' : '0' }}"
                         data-turtle-voice="{{ $subtopic->turtle_voice ?? '0' }}">
                         @if($subtopic->video_segments)
             data-video-segments='@json($subtopic->video_segments)'
         @endif
                    @if($subtopic->show_title)
                        <h2>{{ $subtopic->title }}</h2>
                    @endif
                    <p>{{ $subtopic->description }}</p>

                    @if ($subtopic->file_path)
                        @if (Str::endsWith($subtopic->file_path, '.pdf'))
                            {{-- VISOR PDF CON NAVEGACIÓN --}}
                            <div class="file-viewer">
                                <div class="exam-navigation" style="margin-bottom: 15px;">
                                    <button type="button" class="exam-nav-btn pdf-nav-btn" data-pdf-id="subtopic-{{ $subtopic->id }}" data-action="prev" disabled>
                                        ⬅ Anterior
                                    </button>
                                    <span class="question-counter">
                                        Página <span class="pdf-current-page" data-pdf-id="subtopic-{{ $subtopic->id }}">1</span> de <span class="pdf-total-pages" data-pdf-id="subtopic-{{ $subtopic->id }}">...</span>
                                    </span>
                                    <button type="button" class="exam-nav-btn pdf-nav-btn" data-pdf-id="subtopic-{{ $subtopic->id }}" data-action="next">
                                        Siguiente ➡
                                    </button>
                                </div>
                                <div style="overflow: auto; max-height: 800px; border: 1px solid #ddd; background: #f5f5f5;">
                                    <canvas id="pdf-canvas-subtopic-{{ $subtopic->id }}"
                                            data-pdf-id="subtopic-{{ $subtopic->id }}"
                                            data-pdf-url="{{ asset('storage/'.$subtopic->file_path) }}"
                                            style="display: block; margin: 0 auto;"></canvas>
                                </div>
                            </div>
                        @else
                            {{-- OTROS ARCHIVOS --}}
                            <div class="file-viewer">
                                <iframe src="{{ asset('storage/'.$subtopic->file_path) }}" class="pdf-frame"></iframe>
                            </div>
                        @endif
                    @endif
                </section>

                @foreach ($subtopic->activities as $activity)
                    <section class="content-panel" id="content-activity-{{ $activity->id }}" data-activity-type="{{ $activity->type }}">
                        @if($activity->show_title)
                            <h2>{{ $activity->title }}</h2>
                        @endif
                        <p>{{ $activity->description }}</p>

                        {{-- CUESTIONARIO --}}
                        @if ($activity->type === 'Cuestionario')
                            <div class="game-container cuestionario-container">
                                @php
                                    // Soportar tanto formato antiguo (una pregunta) como nuevo (múltiples preguntas)
                                    $questions = [];
                                    if (isset($activity->content['question'])) {
                                        // Formato antiguo: una sola pregunta
                                        $questions = [[
                                            'question' => $activity->content['question'],
                                            'options' => $activity->content['options'] ?? []
                                        ]];
                                    } elseif (isset($activity->content['questions'])) {
                                        // Formato nuevo: múltiples preguntas
                                        $questions = $activity->content['questions'];
                                    }
                                    $totalQuestions = count($questions);
                                @endphp

                                @if ($totalQuestions > 1)
                                    {{-- NAVEGACIÓN PARA MÚLTIPLES PREGUNTAS --}}
                                    <div class="exam-navigation">
                                        <button type="button" class="exam-nav-btn" id="prevQuizQuestion-{{ $activity->id }}" disabled>
                                            ⬅ Anterior
                                        </button>
                                        <span class="question-counter" id="quiz-counter-{{ $activity->id }}">
                                            Pregunta <span class="current">1</span> de <span class="total">{{ $totalQuestions }}</span>
                                        </span>
                                        <button type="button" class="exam-nav-btn" id="nextQuizQuestion-{{ $activity->id }}">
                                            Siguiente ➡
                                        </button>
                                    </div>
                                @endif

                                <form class="cuestionario-form" data-activity-id="{{ $activity->id }}" data-total="{{ $totalQuestions }}">
                                    @csrf
                                    @foreach ($questions as $qIndex => $question)
                                        <div class="question-item" data-question="{{ $qIndex }}" style="{{ $qIndex === 0 ? '' : 'display: none;' }}">
                                            <h3>{{ $qIndex + 1 }}. {{ $question['question'] }}</h3>
                                            <div class="options-container">
                                                @foreach ($question['options'] ?? [] as $optIndex => $option)
                                                    <label class="option-label">
                                                        <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    @if ($totalQuestions > 1)
                                        <button type="submit" class="btn-submit" id="submitQuiz-{{ $activity->id }}" style="display: none;">
                                            Enviar Cuestionario
                                        </button>
                                    @else
                                        <button type="submit" class="btn-submit">Enviar Respuesta</button>
                                    @endif
                                </form>
                                <div class="result-message"></div>
                            </div>
                        @endif

                        {{-- EXAMEN --}}
                        @if ($activity->type === 'Examen')
                            <div class="game-container examen-container">
                                <div class="exam-navigation">
                                    <button type="button" class="exam-nav-btn" id="prevQuestion-{{ $activity->id }}" disabled>
                                        ⬅ Anterior
                                    </button>
                                    <span class="question-counter" id="counter-{{ $activity->id }}">
                                        Pregunta <span class="current">1</span> de <span class="total">{{ count($activity->content['questions'] ?? []) }}</span>
                                    </span>
                                    <button type="button" class="exam-nav-btn" id="nextQuestion-{{ $activity->id }}">
                                        Siguiente ➡
                                    </button>
                                </div>

                                <form class="examen-form" data-activity-id="{{ $activity->id }}" data-total="{{ count($activity->content['questions'] ?? []) }}">
                                    @csrf
                                    @foreach ($activity->content['questions'] ?? [] as $qIndex => $question)
                                        <div class="question-item" data-question="{{ $qIndex }}" style="{{ $qIndex === 0 ? '' : 'display: none;' }}">
                                            <h4>{{ $qIndex + 1 }}. {{ $question['question'] }}</h4>
                                            <div class="options-container">
                                                @foreach ($question['options'] ?? [] as $optIndex => $option)
                                                    <label class="option-label">
                                                        <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    <button type="submit" class="btn-submit" id="submitExam-{{ $activity->id }}" style="display: none;">
                                        Finalizar Examen
                                    </button>
                                </form>
                                <div class="result-message"></div>
                            </div>
                        @endif

                        {{-- SOPA DE LETRAS --}}
                        @if ($activity->type === 'SopaDeLetras')
                            <div class="game-container sopa-container">
                                <div class="sopa-instructions">
                                    <h4>📝 Instrucciones:</h4>
                                    <ol>
                                        <li><strong>Primer clic:</strong> Selecciona la primera letra de la palabra</li>
                                        <li><strong>Segundo clic:</strong> Selecciona la última letra de la palabra</li>
                                        <li>Las palabras pueden estar en <strong>cualquier dirección</strong> (horizontal, vertical o diagonal)</li>
                                        <li>Si la palabra es correcta, se marcará en <strong style="color: #4CAF50;">verde</strong></li>
                                    </ol>
                                </div>

                                <div class="sopa-game-area">
                                    <div class="words-to-find">
                                        <h4>🎯 Palabras a encontrar:</h4>
                                        <ul id="word-list-{{ $activity->id }}">
                                            @foreach ($activity->content['words'] ?? [] as $word)
                                                <li data-word="{{ strtoupper(trim($word)) }}">
                                                    <span class="word-text">{{ strtoupper($word) }}</span>
                                                    <span class="word-checkmark">✓</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="grid-wrapper">
                                        <div class="grid-container" id="grid-{{ $activity->id }}"
                                             data-activity-id="{{ $activity->id }}"
                                             data-words='@json(array_map(fn($w) => strtoupper(trim($w)), $activity->content['words'] ?? []))'
                                             data-size="{{ $activity->content['grid_size'] ?? 10 }}">
                                            <!-- La grilla se generará aquí con JavaScript -->
                                        </div>
                                    </div>
                                </div>

                                <div class="result-message"></div>
                            </div>
                        @endif

                        {{-- AHORCADO --}}
                        @if ($activity->type === 'Ahorcado')
                            <div class="game-container ahorcado-container">
                                <div class="ahorcado-game" id="ahorcado-{{ $activity->id }}"
                                     data-activity-id="{{ $activity->id }}"
                                     data-word="{{ strtoupper($activity->content['word'] ?? '') }}"
                                     data-hint="{{ $activity->content['hint'] ?? '' }}"
                                     data-max-attempts="{{ $activity->content['max_attempts'] ?? 6 }}">

                                    <div class="hangman-drawing" id="hangman-drawing-{{ $activity->id }}">
                                        <svg width="200" height="250" class="hangman-svg">
                                            <line x1="10" y1="230" x2="150" y2="230" stroke="#333" stroke-width="4"/> <!-- Base -->
                                            <line x1="50" y1="230" x2="50" y2="20" stroke="#333" stroke-width="4"/> <!-- Poste -->
                                            <line x1="50" y1="20" x2="130" y2="20" stroke="#333" stroke-width="4"/> <!-- Viga -->
                                            <line x1="130" y1="20" x2="130" y2="50" stroke="#333" stroke-width="4"/> <!-- Cuerda -->

                                            <!-- Partes del cuerpo (ocultas inicialmente) -->
                                            <circle cx="130" cy="70" r="20" class="hangman-part" data-part="0" style="display:none"/> <!-- Cabeza -->
                                            <line x1="130" y1="90" x2="130" y2="150" class="hangman-part" data-part="1" style="display:none"/> <!-- Cuerpo -->
                                            <line x1="130" y1="110" x2="100" y2="130" class="hangman-part" data-part="2" style="display:none"/> <!-- Brazo izq -->
                                            <line x1="130" y1="110" x2="160" y2="130" class="hangman-part" data-part="3" style="display:none"/> <!-- Brazo der -->
                                            <line x1="130" y1="150" x2="110" y2="190" class="hangman-part" data-part="4" style="display:none"/> <!-- Pierna izq -->
                                            <line x1="130" y1="150" x2="150" y2="190" class="hangman-part" data-part="5" style="display:none"/> <!-- Pierna der -->
                                        </svg>
                                    </div>

                                    <div class="ahorcado-info">
                                        <p class="hint-text"><strong>Pista:</strong> <span id="hint-{{ $activity->id }}"></span></p>
                                        <p class="attempts-text">Intentos restantes: <span id="attempts-{{ $activity->id }}"></span></p>
                                    </div>

                                    <div class="word-display" id="word-display-{{ $activity->id }}"></div>

                                    <div class="keyboard" id="keyboard-{{ $activity->id }}"></div>

                                    <div class="result-message"></div>
                                </div>
                            </div>
                        @endif

                        {{-- CRUCIGRAMA --}}
                        @if ($activity->type === 'Crucigrama')
                            <div class="game-container crucigrama-container">
                                <div class="crucigrama-game" id="crucigrama-{{ $activity->id }}"
                                     data-activity-id="{{ $activity->id }}"
                                     data-words='@json($activity->content['words'] ?? [])'
                                     data-size="{{ $activity->content['grid_size'] ?? 10 }}">

                                    <div class="crucigrama-layout">
                                        <div class="crucigrama-clues">
                                            <div class="clues-section">
                                                <h4>Horizontales</h4>
                                                <ul id="clues-horizontal-{{ $activity->id }}"></ul>
                                            </div>
                                            <div class="clues-section">
                                                <h4>Verticales</h4>
                                                <ul id="clues-vertical-{{ $activity->id }}"></ul>
                                            </div>
                                        </div>

                                        <div class="crucigrama-grid-wrapper">
                                            <div class="crucigrama-grid" id="crucigrama-grid-{{ $activity->id }}">
                                                <!-- La grilla se generará con JavaScript -->
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn-submit" onclick="checkCrucigramaCompletion({{ $activity->id }})">Verificar Crucigrama</button>
                                    <div class="result-message"></div>
                                </div>
                            </div>
                        @endif

                        {{-- ARCHIVOS MULTIMEDIA --}}
                        @if ($activity->file_path)
                            @if (Str::endsWith($activity->file_path, '.pdf'))
                                {{-- VISOR PDF CON NAVEGACIÓN --}}
                                <div class="file-viewer">
                                    <div class="exam-navigation" style="margin-bottom: 15px;">
                                        <button type="button" class="exam-nav-btn pdf-nav-btn" data-pdf-id="activity-{{ $activity->id }}" data-action="prev" disabled>
                                            ⬅ Anterior
                                        </button>
                                        <span class="question-counter">
                                            Página <span class="pdf-current-page" data-pdf-id="activity-{{ $activity->id }}">1</span> de <span class="pdf-total-pages" data-pdf-id="activity-{{ $activity->id }}">...</span>
                                        </span>
                                        <button type="button" class="exam-nav-btn pdf-nav-btn" data-pdf-id="activity-{{ $activity->id }}" data-action="next">
                                            Siguiente ➡
                                        </button>
                                    </div>
                                    <div style="overflow: auto; max-height: 800px; border: 1px solid #ddd; background: #f5f5f5;">
                                        <canvas id="pdf-canvas-activity-{{ $activity->id }}"
                                                data-pdf-id="activity-{{ $activity->id }}"
                                                data-pdf-url="{{ asset('storage/'.$activity->file_path) }}"
                                                style="display: block; margin: 0 auto;"></canvas>
                                    </div>
                                </div>
                            @else
                                {{-- OTROS ARCHIVOS (videos, imágenes, etc) --}}
                                <div class="file-viewer">
                                    <iframe src="{{ asset('storage/'.$activity->file_path) }}" class="pdf-frame"></iframe>
                                </div>
                            @endif
                        @endif

                        {{-- JUEGOS EXTERNOS --}}
                        @if ($activity->game_url)
                            <div class="file-viewer">
                                <iframe
                                    src="{{ $activity->game_url }}"
                                    frameborder="0"
                                    allowfullscreen
                                    style="width:100%;height:500px;">
                                </iframe>
                            </div>
                        @endif

                    </section>
                @endforeach
            @endforeach

            @foreach ($topic->activities as $activity)
                <section class="content-panel" id="content-activity-{{ $activity->id }}" data-activity-type="{{ $activity->type }}">
                    @if($activity->show_title)
                        <h2>{{ $activity->title }}</h2>
                    @endif
                    <p>{{ $activity->description }}</p>

                    {{-- CUESTIONARIO --}}
                    @if ($activity->type === 'Cuestionario')
                        <div class="game-container cuestionario-container">
                            @php
                                // Soportar tanto formato antiguo (una pregunta) como nuevo (múltiples preguntas)
                                $questions = [];
                                if (isset($activity->content['question'])) {
                                    // Formato antiguo: una sola pregunta
                                    $questions = [[
                                        'question' => $activity->content['question'],
                                        'options' => $activity->content['options'] ?? []
                                    ]];
                                } elseif (isset($activity->content['questions'])) {
                                    // Formato nuevo: múltiples preguntas
                                    $questions = $activity->content['questions'];
                                }
                                $totalQuestions = count($questions);
                            @endphp

                            @if ($totalQuestions > 1)
                                {{-- NAVEGACIÓN PARA MÚLTIPLES PREGUNTAS --}}
                                <div class="exam-navigation">
                                    <button type="button" class="exam-nav-btn" id="prevQuizQuestion2-{{ $activity->id }}" disabled>
                                        ⬅ Anterior
                                    </button>
                                    <span class="question-counter" id="quiz-counter2-{{ $activity->id }}">
                                        Pregunta <span class="current">1</span> de <span class="total">{{ $totalQuestions }}</span>
                                    </span>
                                    <button type="button" class="exam-nav-btn" id="nextQuizQuestion2-{{ $activity->id }}">
                                        Siguiente ➡
                                    </button>
                                </div>
                            @endif

                            <form class="cuestionario-form" data-activity-id="{{ $activity->id }}" data-total="{{ $totalQuestions }}">
                                @csrf
                                @foreach ($questions as $qIndex => $question)
                                    <div class="question-item" data-question="{{ $qIndex }}" style="{{ $qIndex === 0 ? '' : 'display: none;' }}">
                                        <h3>{{ $qIndex + 1 }}. {{ $question['question'] }}</h3>
                                        <div class="options-container">
                                            @foreach ($question['options'] ?? [] as $optIndex => $option)
                                                <label class="option-label">
                                                    <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                @if ($totalQuestions > 1)
                                    <button type="submit" class="btn-submit" id="submitQuiz2-{{ $activity->id }}" style="display: none;">
                                        Enviar Cuestionario
                                    </button>
                                @else
                                    <button type="submit" class="btn-submit">Enviar Respuesta</button>
                                @endif
                            </form>
                            <div class="result-message"></div>
                        </div>
                    @endif

                    {{-- EXAMEN --}}
                    @if ($activity->type === 'Examen')
                        <div class="game-container examen-container">
                            <div class="exam-navigation">
                                <button type="button" class="exam-nav-btn" id="prevQuestion-{{ $activity->id }}" disabled>
                                    ⬅ Anterior
                                </button>
                                <span class="question-counter" id="counter-{{ $activity->id }}">
                                    Pregunta <span class="current">1</span> de <span class="total">{{ count($activity->content['questions'] ?? []) }}</span>
                                </span>
                                <button type="button" class="exam-nav-btn" id="nextQuestion-{{ $activity->id }}">
                                    Siguiente ➡
                                </button>
                            </div>

                            <form class="examen-form" data-activity-id="{{ $activity->id }}" data-total="{{ count($activity->content['questions'] ?? []) }}">
                                @csrf
                                @foreach ($activity->content['questions'] ?? [] as $qIndex => $question)
                                    <div class="question-item" data-question="{{ $qIndex }}" style="{{ $qIndex === 0 ? '' : 'display: none;' }}">
                                        <h4>{{ $qIndex + 1 }}. {{ $question['question'] }}</h4>
                                        <div class="options-container">
                                            @foreach ($question['options'] ?? [] as $optIndex => $option)
                                                <label class="option-label">
                                                    <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                                <button type="submit" class="btn-submit" id="submitExam-{{ $activity->id }}" style="display: none;">
                                    Finalizar Examen
                                </button>
                            </form>
                            <div class="result-message"></div>
                        </div>
                    @endif

                    {{-- SOPA DE LETRAS --}}
                    @if ($activity->type === 'SopaDeLetras')
                        <div class="game-container sopa-container">
                            <div class="sopa-instructions">
                                <h4>📝 Instrucciones:</h4>
                                <ol>
                                    <li><strong>Primer clic:</strong> Selecciona la primera letra de la palabra</li>
                                    <li><strong>Segundo clic:</strong> Selecciona la última letra de la palabra</li>
                                    <li>Las palabras pueden estar en <strong>cualquier dirección</strong> (horizontal, vertical o diagonal)</li>
                                    <li>Si la palabra es correcta, se marcará en <strong style="color: #4CAF50;">verde</strong></li>
                                </ol>
                            </div>

                            <div class="sopa-game-area">
                                <div class="words-to-find">
                                    <h4>🎯 Palabras a encontrar:</h4>
                                    <ul id="word-list-{{ $activity->id }}">
                                        @foreach ($activity->content['words'] ?? [] as $word)
                                            <li data-word="{{ strtoupper(trim($word)) }}">
                                                <span class="word-text">{{ strtoupper($word) }}</span>
                                                <span class="word-checkmark">✓</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="grid-wrapper">
                                    <div class="grid-container" id="grid-{{ $activity->id }}"
                                         data-activity-id="{{ $activity->id }}"
                                         data-words='@json(array_map(fn($w) => strtoupper(trim($w)), $activity->content['words'] ?? []))'
                                         data-size="{{ $activity->content['grid_size'] ?? 10 }}">
                                        <!-- La grilla se generará aquí con JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <div class="result-message"></div>
                        </div>
                    @endif

                    {{-- AHORCADO --}}
                    @if ($activity->type === 'Ahorcado')
                        <div class="game-container ahorcado-container">
                            <div class="ahorcado-game" id="ahorcado-{{ $activity->id }}"
                                 data-activity-id="{{ $activity->id }}"
                                 data-word="{{ strtoupper($activity->content['word'] ?? '') }}"
                                 data-hint="{{ $activity->content['hint'] ?? '' }}"
                                 data-max-attempts="{{ $activity->content['max_attempts'] ?? 6 }}">

                                <div class="hangman-drawing" id="hangman-drawing-{{ $activity->id }}">
                                    <svg width="200" height="250" class="hangman-svg">
                                        <line x1="10" y1="230" x2="150" y2="230" stroke="#333" stroke-width="4"/>
                                        <line x1="50" y1="230" x2="50" y2="20" stroke="#333" stroke-width="4"/>
                                        <line x1="50" y1="20" x2="130" y2="20" stroke="#333" stroke-width="4"/>
                                        <line x1="130" y1="20" x2="130" y2="50" stroke="#333" stroke-width="4"/>

                                        <circle cx="130" cy="70" r="20" class="hangman-part" data-part="0" style="display:none"/>
                                        <line x1="130" y1="90" x2="130" y2="150" class="hangman-part" data-part="1" style="display:none"/>
                                        <line x1="130" y1="110" x2="100" y2="130" class="hangman-part" data-part="2" style="display:none"/>
                                        <line x1="130" y1="110" x2="160" y2="130" class="hangman-part" data-part="3" style="display:none"/>
                                        <line x1="130" y1="150" x2="110" y2="190" class="hangman-part" data-part="4" style="display:none"/>
                                        <line x1="130" y1="150" x2="150" y2="190" class="hangman-part" data-part="5" style="display:none"/>
                                    </svg>
                                </div>

                                <div class="ahorcado-info">
                                    <p class="hint-text"><strong>Pista:</strong> <span id="hint-{{ $activity->id }}"></span></p>
                                    <p class="attempts-text">Intentos restantes: <span id="attempts-{{ $activity->id }}"></span></p>
                                </div>

                                <div class="word-display" id="word-display-{{ $activity->id }}"></div>

                                <div class="keyboard" id="keyboard-{{ $activity->id }}"></div>

                                <div class="result-message"></div>
                            </div>
                        </div>
                    @endif

                    {{-- CRUCIGRAMA --}}
                    @if ($activity->type === 'Crucigrama')
                        <div class="game-container crucigrama-container">
                            <div class="crucigrama-game" id="crucigrama-grid-{{ $activity->id }}"
                                 data-activity-id="{{ $activity->id }}"
                                 data-words='@json($activity->content['words'] ?? [])'
                                 data-size="{{ $activity->content['grid_size'] ?? 10 }}">

                                <div class="crucigrama-layout">
                                    <div class="crucigrama-clues">
                                        <div class="clues-section">
                                            <h4>Horizontales</h4>
                                            <ul id="clues-horizontal-{{ $activity->id }}"></ul>
                                        </div>
                                        <div class="clues-section">
                                            <h4>Verticales</h4>
                                            <ul id="clues-vertical-{{ $activity->id }}"></ul>
                                        </div>
                                    </div>

                                    <div class="crucigrama-grid-wrapper">
                                        <div class="crucigrama-grid" id="crucigrama-grid-{{ $activity->id }}">
                                            <!-- La grilla se generará con JavaScript -->
                                        </div>
                                    </div>
                                </div>

                                <button class="btn-submit" onclick="checkCrucigramaCompletion({{ $activity->id }})">Verificar Crucigrama</button>
                                <div class="result-message"></div>
                            </div>
                        </div>
                    @endif

                    {{-- ARCHIVOS MULTIMEDIA --}}
                    @if ($activity->file_path)
                        @if (Str::endsWith($activity->file_path, '.pdf'))
                            {{-- VISOR PDF CON NAVEGACIÓN --}}
                            <div class="file-viewer">
                                <div class="exam-navigation" style="margin-bottom: 15px;">
                                    <button type="button" class="exam-nav-btn pdf-nav-btn" data-pdf-id="activity2-{{ $activity->id }}" data-action="prev" disabled>
                                        ⬅ Anterior
                                    </button>
                                    <span class="question-counter">
                                        Página <span class="pdf-current-page" data-pdf-id="activity2-{{ $activity->id }}">1</span> de <span class="pdf-total-pages" data-pdf-id="activity2-{{ $activity->id }}">...</span>
                                    </span>
                                    <button type="button" class="exam-nav-btn pdf-nav-btn" data-pdf-id="activity2-{{ $activity->id }}" data-action="next">
                                        Siguiente ➡
                                    </button>
                                </div>
                                <div style="overflow: auto; max-height: 800px; border: 1px solid #ddd; background: #f5f5f5;">
                                    <canvas id="pdf-canvas-activity2-{{ $activity->id }}"
                                            data-pdf-id="activity2-{{ $activity->id }}"
                                            data-pdf-url="{{ asset('storage/'.$activity->file_path) }}"
                                            style="display: block; margin: 0 auto;"></canvas>
                                </div>
                            </div>
                        @else
                            {{-- OTROS ARCHIVOS (videos, imágenes, etc) --}}
                            <div class="file-viewer">
                                <iframe src="{{ asset('storage/'.$activity->file_path) }}" class="pdf-frame"></iframe>
                            </div>
                        @endif
                    @endif

                    {{-- JUEGOS EXTERNOS --}}
                    @if ($activity->game_url)
                        <div class="file-viewer">
                            <iframe
                                src="{{ $activity->game_url }}"
                                frameborder="0"
                                allowfullscreen
                                style="width:100%;height:500px;">
                            </iframe>
                        </div>
                    @endif

                </section>
            @endforeach
        @endforeach

    {{-- ===== ACTIVIDADES INDEPENDIENTES ===== --}}
    @foreach ($independentActivities as $activity)
        <section class="content-panel" id="content-activity-{{ $activity->id }}" data-activity-type="{{ $activity->type }}">
            @if($activity->show_title ?? true)
                <h2>{{ $activity->title }}</h2>
            @endif

            {{-- CUESTIONARIO --}}
                        @if ($activity->type === 'Cuestionario')
                            <div class="game-container cuestionario-container">
                                @php
                                    // Soportar tanto formato antiguo (una pregunta) como nuevo (múltiples preguntas)
                                    $questions = [];
                                    if (isset($activity->content['question'])) {
                                        // Formato antiguo: una sola pregunta
                                        $questions = [[
                                            'question' => $activity->content['question'],
                                            'options' => $activity->content['options'] ?? []
                                        ]];
                                    } elseif (isset($activity->content['questions'])) {
                                        // Formato nuevo: múltiples preguntas
                                        $questions = $activity->content['questions'];
                                    }
                                    $totalQuestions = count($questions);
                                @endphp

                                @if ($totalQuestions > 1)
                                    {{-- NAVEGACIÓN PARA MÚLTIPLES PREGUNTAS --}}
                                    <div class="exam-navigation">
                                        <button type="button" class="exam-nav-btn" id="prevQuizQuestion-{{ $activity->id }}" disabled>
                                            ⬅ Anterior
                                        </button>
                                        <span class="question-counter" id="quiz-counter-{{ $activity->id }}">
                                            Pregunta <span class="current">1</span> de <span class="total">{{ $totalQuestions }}</span>
                                        </span>
                                        <button type="button" class="exam-nav-btn" id="nextQuizQuestion-{{ $activity->id }}">
                                            Siguiente ➡
                                        </button>
                                    </div>
                                @endif

                                <form class="cuestionario-form" data-activity-id="{{ $activity->id }}" data-total="{{ $totalQuestions }}">
                                    @csrf
                                    @foreach ($questions as $qIndex => $question)
                                        <div class="question-item" data-question="{{ $qIndex }}" style="{{ $qIndex === 0 ? '' : 'display: none;' }}">
                                            <h3>{{ $qIndex + 1 }}. {{ $question['question'] }}</h3>
                                            <div class="options-container">
                                                @foreach ($question['options'] ?? [] as $optIndex => $option)
                                                    <label class="option-label">
                                                        <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    @if ($totalQuestions > 1)
                                        <button type="submit" class="btn-submit" id="submitQuiz-{{ $activity->id }}" style="display: none;">
                                            Enviar Cuestionario
                                        </button>
                                    @else
                                        <button type="submit" class="btn-submit">Enviar Respuesta</button>
                                    @endif
                                </form>
                                <div class="result-message"></div>
                            </div>
                        @endif

            @if($activity->type === 'SopaDeLetras')
                <div class="game-container sopa-container">
                    <div class="sopa-instructions">
                        <h4>📝 Instrucciones:</h4>
                        <ol>
                            <li><strong>Primer clic:</strong> Selecciona la primera letra de la palabra</li>
                            <li><strong>Segundo clic:</strong> Selecciona la última letra de la palabra</li>
                            <li>Las palabras pueden estar en <strong>cualquier dirección</strong></li>
                            <li>Si la palabra es correcta, se marcará en <strong style="color: #4CAF50;">verde</strong></li>
                        </ol>
                    </div>

                    <div class="sopa-game-area">
                        <div class="words-to-find">
                            <h4>🎯 Palabras a encontrar:</h4>
                            <ul id="word-list-{{ $activity->id }}">
                                @foreach ($activity->content['words'] ?? [] as $word)
                                    <li data-word="{{ strtoupper(trim($word)) }}">
                                        <span class="word-text">{{ strtoupper($word) }}</span>
                                        <span class="word-checkmark">✓</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="grid-wrapper">
                            <div class="grid-container" id="grid-{{ $activity->id }}"
                                 data-activity-id="{{ $activity->id }}"
                                 data-words='@json(array_map(fn($w) => strtoupper(trim($w)), $activity->content['words'] ?? []))'
                                 data-size="{{ $activity->content['grid_size'] ?? 10 }}">
                            </div>
                        </div>
                    </div>

                    <div class="result-message"></div>
                </div>
            @endif

            @if($activity->type === 'Ahorcado')
                <div class="game-container ahorcado-container">
                    <div class="ahorcado-game" id="ahorcado-{{ $activity->id }}"
                         data-activity-id="{{ $activity->id }}"
                         data-word="{{ strtoupper($activity->content['word'] ?? '') }}"
                         data-hint="{{ $activity->content['hint'] ?? '' }}"
                         data-max-attempts="{{ $activity->content['max_attempts'] ?? 6 }}">

                        <div class="hangman-drawing" id="hangman-drawing-{{ $activity->id }}">
                            <svg width="200" height="250" class="hangman-svg">
                                <line x1="10" y1="230" x2="150" y2="230" stroke="#333" stroke-width="4"/>
                                <line x1="50" y1="230" x2="50" y2="20" stroke="#333" stroke-width="4"/>
                                <line x1="50" y1="20" x2="130" y2="20" stroke="#333" stroke-width="4"/>
                                <line x1="130" y1="20" x2="130" y2="50" stroke="#333" stroke-width="4"/>
                                <circle cx="130" cy="70" r="20" class="hangman-part" data-part="0" style="display:none"/>
                                <line x1="130" y1="90" x2="130" y2="150" class="hangman-part" data-part="1" style="display:none"/>
                                <line x1="130" y1="110" x2="100" y2="130" class="hangman-part" data-part="2" style="display:none"/>
                                <line x1="130" y1="110" x2="160" y2="130" class="hangman-part" data-part="3" style="display:none"/>
                                <line x1="130" y1="150" x2="110" y2="190" class="hangman-part" data-part="4" style="display:none"/>
                                <line x1="130" y1="150" x2="150" y2="190" class="hangman-part" data-part="5" style="display:none"/>
                            </svg>
                        </div>

                        <div class="ahorcado-info">
                            <p class="hint-text"><strong>Pista:</strong> <span id="hint-{{ $activity->id }}"></span></p>
                            <p class="attempts-text">Intentos restantes: <span id="attempts-{{ $activity->id }}"></span></p>
                        </div>

                        <div class="word-display" id="word-display-{{ $activity->id }}"></div>
                        <div class="keyboard" id="keyboard-{{ $activity->id }}"></div>
                        <div class="result-message"></div>
                    </div>
                </div>
            @endif

            {{-- CRUCIGRAMA --}}
                        @if ($activity->type === 'Crucigrama')
                            <div class="game-container crucigrama-container">
                                <div class="crucigrama-game" id="crucigrama-{{ $activity->id }}"
                                     data-activity-id="{{ $activity->id }}"
                                     data-words='@json($activity->content['words'] ?? [])'
                                     data-size="{{ $activity->content['grid_size'] ?? 10 }}">

                                    <div class="crucigrama-layout">
                                        <div class="crucigrama-clues">
                                            <div class="clues-section">
                                                <h4>Horizontales</h4>
                                                <ul id="clues-horizontal-{{ $activity->id }}"></ul>
                                            </div>
                                            <div class="clues-section">
                                                <h4>Verticales</h4>
                                                <ul id="clues-vertical-{{ $activity->id }}"></ul>
                                            </div>
                                        </div>

                                        <div class="crucigrama-grid-wrapper">
                                            <div class="crucigrama-grid" id="crucigrama-grid-{{ $activity->id }}">
                                                <!-- La grilla se generará con JavaScript -->
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn-submit" onclick="checkCrucigramaCompletion({{ $activity->id }})">Verificar Crucigrama</button>
                                    <div class="result-message"></div>
                                </div>
                            </div>
                        @endif

            {{-- EXAMEN --}}
                        @if ($activity->type === 'Examen')
                            <div class="game-container examen-container">
                                <div class="exam-navigation">
                                    <button type="button" class="exam-nav-btn" id="prevQuestion-{{ $activity->id }}" disabled>
                                        ⬅ Anterior
                                    </button>
                                    <span class="question-counter" id="counter-{{ $activity->id }}">
                                        Pregunta <span class="current">1</span> de <span class="total">{{ count($activity->content['questions'] ?? []) }}</span>
                                    </span>
                                    <button type="button" class="exam-nav-btn" id="nextQuestion-{{ $activity->id }}">
                                        Siguiente ➡
                                    </button>
                                </div>

                                <form class="examen-form" data-activity-id="{{ $activity->id }}" data-total="{{ count($activity->content['questions'] ?? []) }}">
                                    @csrf
                                    @foreach ($activity->content['questions'] ?? [] as $qIndex => $question)
                                        <div class="question-item" data-question="{{ $qIndex }}" style="{{ $qIndex === 0 ? '' : 'display: none;' }}">
                                            <h4>{{ $qIndex + 1 }}. {{ $question['question'] }}</h4>
                                            <div class="options-container">
                                                @foreach ($question['options'] ?? [] as $optIndex => $option)
                                                    <label class="option-label">
                                                        <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    <button type="submit" class="btn-submit" id="submitExam-{{ $activity->id }}" style="display: none;">
                                        Finalizar Examen
                                    </button>
                                </form>
                                <div class="result-message"></div>
                            </div>
                        @endif

        </section>
    @endforeach

    {{-- ========== EXAMEN FINAL ========== --}}
@if($finalExamActivity)
<section class="content-panel" id="content-final-exam" style="display: none;">
    @if($progress >= 0)
        <h2>{{ $finalExamActivity->title }}</h2>
        <p>{{ $finalExamActivity->description }}</p>
        @if($finalExamData)
            {{-- Ya completó el examen --}}
            <div style="background: #d4edda; border: 2px solid #28a745; border-radius: 10px; padding: 20px; text-align: center;">
                <h3 style="color: #155724; margin: 0 0 10px 0;">✅ ¡Examen Completado!</h3>
                <p style="margin: 0; font-size: 1.1em;">Calificación: <strong>{{ $finalExamData->score }}%</strong></p>
                <p style="margin: 5px 0 0 0; color: #666;">Completado el {{ $finalExamData->created_at->format('d/m/Y H:i') }}</p>
            </div>
        @else
            {{-- Aún no lo completa --}}
            <div class="game-container">
                {{-- EXAMEN --}}
                    @if($finalExamActivity && strtolower($finalExamActivity->type) === 'examen')
                        <div class="game-container examen-container">
                            <div class="exam-navigation">
                                <button type="button" class="exam-nav-btn" id="prevQuestion-{{ $finalExamActivity->id }}" disabled>
                                    ⬅ Anterior
                                </button>
                                <span class="question-counter" id="counter-{{ $finalExamActivity->id }}">
                                    Pregunta <span class="current">1</span> de <span class="total">{{ count($finalExamActivity->content['questions'] ?? []) }}</span>
                                </span>
                                <button type="button" class="exam-nav-btn" id="nextQuestion-{{ $finalExamActivity->id }}">
                                    Siguiente ➡
                                </button>
                            </div>

                            <form class="examen-form" data-activity-id="{{ $finalExamActivity->id }}" data-total="{{ count($finalExamActivity->content['questions'] ?? []) }}">
                                @csrf
                                @foreach ($finalExamActivity->content['questions'] ?? [] as $qIndex => $question)
                                    <div class="question-item" data-question="{{ $qIndex }}" style="{{ $qIndex === 0 ? '' : 'display: none;' }}">
                                        <h4>{{ $qIndex + 1 }}. {{ $question['question'] }}</h4>
                                        <div class="options-container">
                                            @foreach ($question['options'] ?? [] as $optIndex => $option)
                                                <label class="option-label">
                                                    <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                                <button type="submit" class="btn-submit" id="submitExam-{{ $finalExamActivity->id }}" style="display: none;">
                                    Finalizar Examen
                                </button>
                            </form>
                            <div class="result-message"></div>
                        </div>
                    @endif
            </div>
        @endif
    @else
        {{-- Curso no completado --}}
        <div style="background: #f8d7da; border: 2px solid #dc3545; border-radius: 10px; padding: 40px; text-align: center;">
            <h2 style="color: #721c24; margin: 0 0 15px 0;">🔒 Examen Final Bloqueado</h2>
            <p style="font-size: 1.2em; margin: 0 0 20px 0;">Debes completar el curso para desbloquear el examen final.</p>
            <div style="background: white; border-radius: 10px; padding: 15px; max-width: 400px; margin: 0 auto;">
                <div style="background: #e9ecef; border-radius: 10px; height: 30px; overflow: hidden;">
                    <div style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); height: 100%; width: {{ $progress }}%; transition: width 0.3s;"></div>
                </div>
                <p style="margin: 10px 0 0 0; color: #666; font-weight: bold;">{{ round($progress) }}% completado</p>
            </div>
        </div>
    @endif
</section>
@endif
@endif


    </main>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
let selectedSessionData = {
    id: null,
    date: null,
    start: null,
    end: null,
    active: false
};

let cameraStream = null;
let scanning = false;

function showSessionTable(sessionId, sessionDate, startTime, endTime, isActive) {
    const source = document.getElementById('session-table-data-' + sessionId);
    const container = document.getElementById('session-table-container');

    const btnPdf = document.getElementById('btn-download-session-pdf');
    const btnQr = document.getElementById('btn-scan-qr');

    if (!source || !container) return;

    container.innerHTML = source.innerHTML;

    selectedSessionData = {
        id: sessionId,
        date: sessionDate,
        start: startTime,
        end: endTime,
        active: Number(isActive) === 1
    };

    if (btnPdf) {
        btnPdf.href = `/groups/${sessionId}/export-pdf`;
        btnPdf.style.display = 'inline-flex';
    }

    if (btnQr) {
        btnQr.style.display = 'inline-flex';
    }
}

function abrirModalQR() {
    const qrModal = document.getElementById('qrModal');

    if (!selectedSessionData.id) {
        alert('Selecciona primero un horario.');
        return;
    }

    if (!qrModal) {
        alert('No se encontró el modal QR.');
        return;
    }

    const modal = new bootstrap.Modal(qrModal);
    modal.show();
}

function extraerRFC(valorQR) {
    return String(valorQR || '')
        .trim()
        .toUpperCase()
        .replace('RFC:', '')
        .replace(/[^A-Z0-9Ñ&]/g, '');
}

async function validarAsistenciaPorRFC(valorQR) {
    const rfc = extraerRFC(valorQR);

    if (!rfc) {
        alert('QR inválido. No contiene RFC.');
        return false;
    }

    if (!selectedSessionData.id) {
        alert('Selecciona primero un horario.');
        return false;
    }

    if (!selectedSessionData.active) {
        alert('No cumple: este horario no está activo.');
        return false;
    }

    const ahora = new Date();
    const fechaHoy = ahora.toLocaleDateString('en-CA');

    if (fechaHoy !== selectedSessionData.date) {
        alert('No cumple: este horario no corresponde a la fecha de hoy.');
        return false;
    }

    const inicio = new Date(`${selectedSessionData.date}T${selectedSessionData.start}`);
    const fin = new Date(`${selectedSessionData.date}T${selectedSessionData.end}`);

    if (ahora < inicio) {
        alert('No cumple: aún no inicia el horario.');
        return false;
    }

    if (ahora > fin) {
        alert('No cumple: el horario ya terminó.');
        return false;
    }

    const fila = document.querySelector(
        `#session-table-container tr[data-rfc="${rfc}"]`
    );

    if (!fila) {
        alert('No cumple: el RFC no está en la lista de este horario.');
        return false;
    }

    const badge = fila.querySelector('.attendance-badge');

    if (badge && badge.classList.contains('present')) {
        alert('Este RFC ya tiene asistencia registrada.');
        return false;
    }

    try {
        const response = await fetch(`/sessions/${selectedSessionData.id}/attendance/qr`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                rfc: rfc
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            alert(data.message || 'No se pudo guardar la asistencia.');
            return false;
        }

        if (badge) {
            badge.textContent = 'Asistió';
            badge.classList.remove('pending');
            badge.classList.add('present');
        }

        fila.classList.add('attendance-row-present');

        alert('Asistencia registrada correctamente.');
        return true;

    } catch (error) {
        console.error('Error al guardar asistencia:', error);
        alert('Error al guardar la asistencia.');
        return false;
    }
}

function cerrarCamara() {
    scanning = false;

    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    const video = document.getElementById('camera-preview');

    if (video) {
        video.srcObject = null;
    }
}

function cerrarCamaraYModal() {
    cerrarCamara();

    const qrModal = document.getElementById('qrModal');

    if (qrModal) {
        const modal = bootstrap.Modal.getInstance(qrModal);

        if (modal) {
            modal.hide();
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const qrModal = document.getElementById('qrModal');

    if (!qrModal) return;

    qrModal.addEventListener('shown.bs.modal', async function () {
        const video = document.getElementById('camera-preview');

        if (!video) {
            alert('No se encontró el video de cámara.');
            return;
        }

        if (typeof jsQR === 'undefined') {
            alert('No se cargó la librería jsQR.');
            return;
        }

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: false
            });

            video.srcObject = cameraStream;
            await video.play();

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d', {
                willReadFrequently: true
            });

            scanning = true;

            async function scanLoop() {
                if (!scanning) return;

                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const imageData = context.getImageData(
                        0,
                        0,
                        canvas.width,
                        canvas.height
                    );

                    const code = jsQR(
                        imageData.data,
                        imageData.width,
                        imageData.height
                    );

                    if (code && code.data) {
                        scanning = false;

                        const registrado = await validarAsistenciaPorRFC(code.data);

                        if (registrado) {
                            cerrarCamaraYModal();
                            return;
                        }

                        setTimeout(() => {
                            scanning = true;
                            requestAnimationFrame(scanLoop);
                        }, 1200);

                        return;
                    }
                }

                requestAnimationFrame(scanLoop);
            }

            scanLoop();

        } catch (error) {
            console.error("ERROR CÁMARA:", error);

            alert(
                "Error cámara:\n" +
                error.name + "\n" +
                error.message
            );
        }
    });

    qrModal.addEventListener('hidden.bs.modal', function () {
        cerrarCamara();
    });
});
</script>
{{-- ========== FUNCIONES DE LA TORTUGUITA ========== --}}
<script>
function showTurtle() {
    let turtle = document.getElementById('turtle-mascot');

    // CORREGIDO: 0 = masculina, 1 = femenina
    const turtleVideo = window.currentTurtleIndex === 0
        ? "{{ asset('videos/tortuguita-hablando.webm') }}"      // MASCULINA
        : "{{ asset('videos/tortuguita1-hablando.webm') }}";    // FEMENINA

    console.log(`🐢 Mostrando tortuguita ${window.currentTurtleIndex}`);

    if (!turtle) {
        turtle = document.createElement('div');
        turtle.id = 'turtle-mascot';
        turtle.className = 'turtle-container';
        turtle.innerHTML = `
            <div class="turtle-wrapper">
                <video autoplay loop muted playsinline class="turtle-video">
                    <source src="${turtleVideo}" type="video/webm">
                </video>
            </div>
        `;
        document.body.appendChild(turtle);
    } else {
        const video = turtle.querySelector('.turtle-video source');
        if (video) {
            video.src = turtleVideo;
            turtle.querySelector('.turtle-video').load();
        }
    }

    turtle.classList.add('active');

    const video = turtle.querySelector('.turtle-video');
    if (video) {
        video.play();
    }
}

// NUEVA FUNCIÓN: Mostrar tortuguita SILENCIOSA específica durante videos
function showTurtleWithVoice(voiceIndex) {
    let turtle = document.getElementById('turtle-mascot');

    // CORREGIDO: 0 = masculina, 1 = femenina
    const turtleVideo = voiceIndex === 0
        ? "{{ asset('videos/tortuguita-hablando.webm') }}"      // MASCULINA
        : "{{ asset('videos/tortuguita1-hablando.webm') }}";    // FEMENINA

    console.log(`🐢 Mostrando tortuguita SILENCIOSA: voz ${voiceIndex}`);

    if (!turtle) {
        turtle = document.createElement('div');
        turtle.id = 'turtle-mascot';
        turtle.className = 'turtle-container';
        turtle.innerHTML = `
            <div class="turtle-wrapper">
                <video autoplay loop muted playsinline class="turtle-video">
                    <source src="${turtleVideo}" type="video/webm">
                </video>
            </div>
        `;
        document.body.appendChild(turtle);
    } else {
        const video = turtle.querySelector('.turtle-video source');
        if (video) {
            video.src = turtleVideo;
            turtle.querySelector('.turtle-video').load();
        }
    }

    turtle.classList.add('active');
    turtle.querySelector('.turtle-video')?.play();
}

function hideTurtle() {
    const turtle = document.getElementById('turtle-mascot');
    if (turtle) {
        turtle.classList.remove('active');

        const video = turtle.querySelector('.turtle-video');
        if (video) {
            video.pause();
        }
    }
}

async function readPdf(url) {
    const speakBtn = document.getElementById('btnSpeak');

    try {
        window.isReading = true;
        if (speakBtn) {
            speakBtn.textContent = '⏸ Detener';
            speakBtn.style.background = '#ff5252';
        }

        const loadingTask = pdfjsLib.getDocument(url);
        const pdf = await loadingTask.promise;

        let fullText = '';

        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const content = await page.getTextContent();
            const strings = content.items.map(item => item.str).join(' ');
            fullText += strings + ' ';
        }

        fullText = fullText.replace(/\s+/g, ' ').trim();

        speakWithElevenLabs(
            fullText.substring(0, 5000),
            () => showTurtle(),
            () => {
                hideTurtle();
                window.isReading = false;
                if (speakBtn) {
                    speakBtn.textContent = '🔊 Leer';
                    speakBtn.style.background = '';
                }
            }
        );

    } catch (error) {
        console.error(error);
        hideTurtle();
        window.isReading = false;
        if (speakBtn) {
            speakBtn.textContent = '🔊 Leer';
            speakBtn.style.background = '';
        }
        speak('No se pudo leer el PDF.');
    }
}

</script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const btn = document.getElementById('btnSpeak');
    if(!btn) return;

    btn.addEventListener('click', async () => {
        if (window.isReading) {
            window.speechSynthesis.cancel();
            if (window.currentAudio) {
                window.currentAudio.pause();
                window.currentAudio = null;
            }
            hideTurtle();
            window.isReading = false;
            btn.textContent = '🔊 Leer';
            btn.style.background = '';
            return;
        }

        const panel = document.querySelector('.content-panel:not([style*="display: none"])');
        if (!panel) {
            alert('No hay contenido visible');
            return;
        }

        const iframes = panel.querySelectorAll('iframe');
        console.log('🔍 Iframes encontrados:', iframes.length);

        for (let iframe of iframes) {
            console.log('📄 Revisando iframe:', iframe.src);
            if (iframe.src && iframe.src.toLowerCase().includes('.pdf')) {
                console.log('✅ PDF encontrado, leyendo...');
                readPdf(iframe.src);
                return;
            }
        }

        let text = panel.innerText.replace(/\s+/g, ' ').trim();

        if (!text || text.length < 10) {
            alert('No hay suficiente texto para leer');
            return;
        }

        speak(text);
    });

});

function speak(text) {
    const speakBtn = document.getElementById('btnSpeak');

    window.isReading = true;
    if (speakBtn) {
        speakBtn.textContent = '⏸ Detener';
        speakBtn.style.background = '#ff5252';
    }

    speakWithElevenLabs(
        text,
        () => showTurtle(),
        () => {
            hideTurtle();
            window.isReading = false;
            if (speakBtn) {
                speakBtn.textContent = '🔊 Leer';
                speakBtn.style.background = '';
            }
        }
    );
}
</script>

<script>
// ====== CONFIGURACIÓN ELEVENLABS ======
const ELEVENLABS_API_KEY = 'sk_abdb547b1e48007a9557c9ee79f33b4c226d583598ada4db';

// VOCES ALTERNADAS - Una para cada tortuguita
const ELEVENLABS_VOICES = [
    'iHfvoHbLYn9aHUcsmJuC',  // Voz 0 (MASCULINA - tortuguita-hablando.webm)
    'VSim0Box4DjqUmOLLAd8'   // Voz 1 (FEMENINA - tortuguita1-hablando.webm)
];

// Variable global para controlar el estado de lectura
window.isReading = false;
window.currentTurtleIndex = 0;
window.currentAudio = null;

// Función para convertir texto a audio con ElevenLabs
async function speakWithElevenLabs(text, onStart, onEnd) {
    // Obtener la voz según la tortuguita actual
    const currentVoiceId = ELEVENLABS_VOICES[window.currentTurtleIndex];

    console.log('🎤 INICIANDO ElevenLabs...');
    console.log('📝 API Key:', ELEVENLABS_API_KEY ? 'Presente ✓' : 'FALTA ✗');
    console.log(`🐢 Tortuguita ${window.currentTurtleIndex} - Voice ID:`, currentVoiceId);
    console.log('📝 Texto length:', text.length);

    try {
        const textToSpeak = text.substring(0, 5000);

        console.log('📡 Haciendo fetch a ElevenLabs...');

        const response = await fetch(`https://api.elevenlabs.io/v1/text-to-speech/${currentVoiceId}`, {
            method: 'POST',
            headers: {
                'Accept': 'audio/mpeg',
                'Content-Type': 'application/json',
                'xi-api-key': ELEVENLABS_API_KEY
            },
            body: JSON.stringify({
                text: textToSpeak,
                model_id: 'eleven_multilingual_v2',
                voice_settings: {
                    stability: 0.5,
                    similarity_boost: 0.75
                }
            })
        });

        console.log('📡 Response status:', response.status);
        console.log('📡 Response OK:', response.ok);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error HTTP:', response.status, response.statusText);
            console.error('❌ Error body:', errorText);
            throw new Error(`Error ${response.status}: ${errorText}`);
        }

        console.log('✅ Respuesta OK de ElevenLabs');

        const audioBlob = await response.blob();
        console.log('✅ Audio blob recibido, tamaño:', audioBlob.size, 'bytes');

        const audioUrl = URL.createObjectURL(audioBlob);
        const audio = new Audio(audioUrl);

        window.currentAudio = audio;

        audio.onloadeddata = () => {
            console.log('✅ Audio cargado, duración:', audio.duration, 'segundos');
        };

        audio.onplay = () => {
            console.log(`▶️ REPRODUCIENDO ELEVENLABS - Voz ${window.currentTurtleIndex}`);
            if (onStart) onStart();
        };

        audio.onended = () => {
            console.log('✅ Audio ElevenLabs completado');
            URL.revokeObjectURL(audioUrl);
            window.currentAudio = null;
            if (onEnd) onEnd();
        };

        audio.onerror = (e) => {
            console.error('❌ Error reproduciendo audio:', e);
            console.error('❌ Audio error details:', audio.error);
            URL.revokeObjectURL(audioUrl);
            window.currentAudio = null;

            alert('Error reproduciendo audio de ElevenLabs. Ver consola.');
            if (onEnd) onEnd();
        };

        console.log('▶️ Intentando reproducir audio...');

        const playPromise = audio.play();

        if (playPromise !== undefined) {
            playPromise
                .then(() => {
                    console.log('✅ Reproducción iniciada exitosamente');
                })
                .catch(error => {
                    console.error('❌ Error al iniciar reproducción:', error);
                    alert('Error: No se pudo iniciar el audio. Ver consola.');
                });
        }

    } catch (error) {
        console.error('❌❌❌ ERROR CRÍTICO CON ELEVENLABS ❌❌❌');
        console.error('Error:', error);
        console.error('Error stack:', error.stack);

        alert(`Error ElevenLabs: ${error.message}\n\nRevisa la consola (F12) para más detalles.`);

        console.log('⚠️ Usando voz genérica como FALLBACK TEMPORAL');
        const msg = new SpeechSynthesisUtterance(text);
        msg.lang = 'es-MX';
        msg.rate = 1;
        msg.pitch = 1;
        if (onStart) msg.onstart = onStart;
        if (onEnd) msg.onend = onEnd;
        window.speechSynthesis.speak(msg);
    }
}

document.addEventListener('DOMContentLoaded', function () {

    const links = [...document.querySelectorAll('.syllabus-link')];
    const panels = document.querySelectorAll('.content-panel');
    const bar = document.querySelector('.course-progress-bar');

    let index = 0;
    let autoplayActive = false;

    panels.forEach(p => p.style.display = 'none');

    // ✅ PRIMERO: Cargar progreso desde BD
    window.maxProgress = parseFloat({{ $progress }}) || 0;
    bar.style.width = window.maxProgress + '%';
    console.log(`🎬 Progreso cargado desde BD: ${Math.round(window.maxProgress)}%`);

    // ✅ Si es primera vez (0%), contar automáticamente el primer tema
    if (window.maxProgress === 0) {
        const firstProgress = (1 / links.length) * 100;
        window.maxProgress = firstProgress;
        bar.style.width = firstProgress + '%';
        saveProgress(firstProgress);
        console.log(`🎬 Primera visita - contando tema 1: ${Math.round(firstProgress)}%`);
    }

    // ✅ DESPUÉS: Mostrar el primer panel
    showIndex(0);

    // Pausar videos
    function pauseAllVideos() {
        document.querySelectorAll('video:not(.turtle-video)').forEach(video => {
            video.pause();
            video.currentTime = 0;
            video.removeAttribute('autoplay');
        });

        document.querySelectorAll('iframe[data-autoplay-blocked]').forEach(iframe => {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
                if (iframeDoc) {
                    iframeDoc.querySelectorAll('video').forEach(video => {
                        video.pause();
                        video.currentTime = 0;
                        video.removeAttribute('autoplay');
                    });
                }
            } catch (e) {}
        });
    }

    pauseAllVideos();
    setTimeout(() => pauseAllVideos(), 100);
    setTimeout(() => pauseAllVideos(), 500);
    setTimeout(() => pauseAllVideos(), 1000);
    setTimeout(() => pauseAllVideos(), 2000);

    const observer = new MutationObserver(() => pauseAllVideos());
    observer.observe(document.body, { childList: true, subtree: true });

    setTimeout(() => {
        console.log('🎬 Videos pausados');
        document.querySelectorAll('iframe').forEach(iframe => {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                if (iframeDoc) {
                    iframeDoc.querySelectorAll('video').forEach(video => {
                        video.pause();
                        video.currentTime = 0;
                    });
                }
            } catch (e) {}
        });
        console.log('✅ Videos pausados');
    }, 100);

    // Inicializar juegos
    setTimeout(() => {
        console.log('🔍 Buscando juegos para inicializar...');

        const sopaGrids = document.querySelectorAll('.grid-container');
        console.log(`Encontrados ${sopaGrids.length} juegos de Sopa de Letras`);
        sopaGrids.forEach((grid) => {
            if(!grid.innerHTML.trim() || grid.innerHTML.trim().length < 100) {
                console.log('✅ Inicializando Sopa de Letras...');
                initSopaDeLetras(grid);
            }
        });

        const ahorcadoGames = document.querySelectorAll('.ahorcado-game');
        console.log(`Encontrados ${ahorcadoGames.length} juegos de Ahorcado`);
        ahorcadoGames.forEach(game => {
            if(!game.querySelector('.letter-btn')) {
                console.log('✅ Inicializando Ahorcado...');
                initAhorcado(game);
            }
        });

        const crucigramas = document.querySelectorAll('.crucigrama-game');
        crucigramas.forEach(game => {
            console.log('✅ Inicializando Crucigrama...');
            initCrucigrama(game);
        });

    }, 200);

    // ========== FUNCIÓN showIndex ==========
    function showIndex(i){
        if(i < 0 || i >= links.length) return;

        panels.forEach(p => p.style.display = 'none');
        links.forEach(l => l.classList.remove('active'));

        const target = links[i].dataset.target;
        const panel = document.querySelector(target);

        if(panel){
            panel.style.display = 'block';
            links[i].classList.add('active');
            index = i;

            // ✅ Calcular progreso basado en posición
            const newProgress = ((i + 1) / links.length) * 100;

            // ✅ Calcular último tema visitado
            const lastVisitedIndex = Math.floor((window.maxProgress / 100) * links.length) - 1;

            if (i <= lastVisitedIndex + 2) {
                // ✅ Avanzó secuencialmente
                window.maxProgress = newProgress;
                bar.style.width = newProgress + '%';
                saveProgress(newProgress);
                console.log(`➡️ Avanzando: ${Math.round(newProgress)}%`);
            } else if (i > lastVisitedIndex + 1) {
                // ⏭️ Saltó temas
                bar.style.width = window.maxProgress + '%';
                console.log(`⏭️ Saltó temas - manteniendo: ${Math.round(window.maxProgress)}%`);
            } else {
                // ⬅️ Retrocedió
                bar.style.width = window.maxProgress + '%';
                console.log(`⬅️ Retrocediendo - manteniendo: ${Math.round(window.maxProgress)}%`);
            }

            pauseAllVideos();
        }
    }

    // ========== NAVEGACIÓN ==========
    links.forEach((l, i) => {
        l.addEventListener('click', () => {
            showIndex(i);
        });
    });

    document.getElementById('btnNext').onclick = () => showIndex(index + 1);
    document.getElementById('btnPrev').onclick = () => showIndex(index - 1);

    // ========== GUARDAR PROGRESO ==========
    function saveProgress(progressPercentage) {
        const courseId = {{ $course->id }};

        console.log(`💾 Guardando progreso: ${Math.round(progressPercentage)}%`);

        fetch(`/cursos/${courseId}/save-progress`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                progress: progressPercentage
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log(`✅ Progreso guardado: ${Math.round(data.progress)}%`);
            }
        })
        .catch(err => console.error('❌ Error guardando progreso:', err));
    }

    // ====== AUTOPLAY ======
    const btnAutoplay = document.getElementById('btnAutoplay');

    btnAutoplay.onclick = ()=>{
        autoplayActive = !autoplayActive;

        if (autoplayActive) {
            btnAutoplay.textContent = '⏸ Detener';
            btnAutoplay.style.background = '#ff5252';
            console.log('🎬 Autoplay ACTIVADO');
            startAutoplay();
        } else {
            btnAutoplay.textContent = '▶️ Autoplay';
            btnAutoplay.style.background = '';
            console.log('⏸ Autoplay DESACTIVADO');

            window.speechSynthesis.cancel();
            if (window.currentAudio) {
                window.currentAudio.pause();
                window.currentAudio = null;
            }

            hideTurtle();
        }
    };

    function startAutoplay() {
        if (!autoplayActive) return;

        const panel = document.querySelector('.content-panel:not([style*="display: none"])');
        if (!panel) return;

        if (panel.querySelector('.game-container')) {
            console.log('🎮 Juego detectado - PAUSANDO autoplay');
            showGameModal();
            autoplayActive = false;
            btnAutoplay.textContent = '▶️ Autoplay';
            btnAutoplay.style.background = '';
            return;
        }

        readCurrentPanel();
    }

    // ✅ FUNCIÓN MEJORADA: Manejar video con segmentos
    function handleVideoWithSegments(video, panel) {
        console.log('🎥 Manejando video con posibles segmentos...');

        // Obtener segmentos del panel
        let segments = [];
        const segmentsData = panel.dataset.videoSegments;

        if (segmentsData) {
            try {
                const parsedSegments = JSON.parse(segmentsData);
                // ✅ Convertir objeto con índices numéricos a array
                segments = Object.values(parsedSegments);
                console.log('📊 Segmentos encontrados:', segments);
            } catch (e) {
                console.log('⚠️ No hay segmentos definidos, usando tortuguita por defecto');
            }
        }

        // Función para convertir MM:SS a segundos
        function timeToSeconds(timeStr) {
            const [minutes, seconds] = timeStr.split(':').map(Number);
            return minutes * 60 + seconds;
        }

        let currentSegmentIndex = -1;
        let turtleUpdateInterval = null;

        // Función para actualizar tortuguita según el tiempo actual
        function updateTurtleByTime() {
            const currentTime = video.currentTime;

            if (segments.length === 0) {
                // Sin segmentos, usar tortuguita por defecto del panel
                const showTurtle = panel.dataset.showTurtle === '1';
                const turtleVoice = parseInt(panel.dataset.turtleVoice) || 0;

                if (showTurtle && !document.getElementById('turtle-mascot')?.classList.contains('active')) {
                    showTurtleWithVoice(turtleVoice);
                }
                return;
            }

            // Buscar segmento activo
            let activeSegmentIndex = -1;

            for (let i = 0; i < segments.length; i++) {
                const segment = segments[i];
                const startTime = timeToSeconds(segment.start);
                const endTime = segment.end ? timeToSeconds(segment.end) : video.duration;

                if (currentTime >= startTime && currentTime < endTime) {
                    activeSegmentIndex = i;
                    break;
                }
            }

            // Si cambió el segmento activo
            if (activeSegmentIndex !== currentSegmentIndex) {
                currentSegmentIndex = activeSegmentIndex;

                if (activeSegmentIndex >= 0) {
                    const segment = segments[activeSegmentIndex];
                    const turtleVoice = parseInt(segment.turtle);

                    console.log(`🐢 Cambiando a tortuguita ${turtleVoice} (${turtleVoice === 0 ? 'Toby' : 'Mely'})`);

                    showTurtleWithVoice(turtleVoice);
                } else {
                    // Fuera de todos los segmentos, ocultar tortuguita
                    console.log('👋 Ocultando tortuguita (fuera de segmentos)');
                    hideTurtle();
                }
            }
        }

        // Iniciar monitoreo de tiempo
        updateTurtleByTime(); // Actualizar inmediatamente

        turtleUpdateInterval = setInterval(updateTurtleByTime, 500); // Revisar cada 0.5 segundos

        // Limpiar interval cuando termina el video
video.addEventListener('ended', function videoEnded() {
    console.log('✅ Video completado');

    if (turtleUpdateInterval) {
        clearInterval(turtleUpdateInterval);
    }

    hideTurtle();

    if (autoplayActive) {
        // ✅ VERIFICAR SI EL SIGUIENTE ES UN JUEGO ANTES DE AVANZAR
        setTimeout(() => {
            // Simular avance para verificar
            const links = [...document.querySelectorAll('.syllabus-link')];
            const currentIndex = links.findIndex(l => l.classList.contains('active'));
            const nextIndex = currentIndex + 1;

            if (nextIndex < links.length) {
                const nextTarget = links[nextIndex].dataset.target;
                const nextPanel = document.querySelector(nextTarget);

                if (nextPanel && nextPanel.querySelector('.game-container')) {
    console.log('🎮 Siguiente es un juego - avanzando y mostrando modal');

    // ✅ PRIMERO avanzar al juego
    window.currentTurtleIndex = (window.currentTurtleIndex + 1) % 2;
    const links = [...document.querySelectorAll('.syllabus-link')];
    const currentIndex = links.findIndex(l => l.classList.contains('active'));
    showIndex(currentIndex + 1);

    // ✅ LUEGO pausar autoplay y mostrar modal
    setTimeout(() => {
        autoplayActive = false;
        const btnAutoplay = document.getElementById('btnAutoplay');
        if (btnAutoplay) {
            btnAutoplay.textContent = '▶️ Autoplay';
            btnAutoplay.style.background = '';
        }
        showGameModal();
    }, 300);
} else {
    advanceToNext();
}
            }
        }, 1000);
    }

    video.removeEventListener('ended', videoEnded);
});

        // Limpiar interval si se pausa (y no está en autoplay)
        video.addEventListener('pause', function videoPaused() {
            if (turtleUpdateInterval && !autoplayActive) {
                clearInterval(turtleUpdateInterval);
                hideTurtle();
            }
        });
    }

    function readCurrentPanel() {
        const panel = document.querySelector('.content-panel:not([style*="display: none"])');
        if (!panel) return;

        // 1. BUSCAR VIDEOS (tag video directo)
        let video = panel.querySelector('video:not(.turtle-video)');
        if (video) {
            console.log('🎥 Video detectado');

            // ✅ USAR LA FUNCIÓN QUE MANEJA SEGMENTOS
            handleVideoWithSegments(video, panel);

            if (autoplayActive) {
                const title = panel.querySelector('h2');
                const description = panel.querySelector('p');

                let textToRead = '';
                if (title) textToRead += title.textContent + '. ';
                if (description) textToRead += description.textContent;
                textToRead = textToRead.trim();

                if (textToRead.length > 10) {
    speakWithElevenLabs(
        textToRead,
        () => showTurtle(), // ✅ MOSTRAR tortuguita al leer
        () => {
            hideTurtle(); // ✅ OCULTAR al terminar de leer
            if (autoplayActive) {
                setTimeout(() => {
                    video.currentTime = 0;
                    video.play().catch(() => {
                        if (autoplayActive) advanceToNext();
                    });
                }, 1000);
            }
        }
    );
}else {
                    video.currentTime = 0;
                    video.play();
                }
            }
            return;
        }

        // 2. BUSCAR VIDEOS en iframes
const allIframes = panel.querySelectorAll('iframe');
for (let iframe of allIframes) {
    if (iframe.src &&
        (iframe.src.toLowerCase().includes('.mp4') ||
         iframe.src.toLowerCase().includes('.webm') ||
         iframe.src.toLowerCase().includes('.ogg') ||
         iframe.src.toLowerCase().includes('video'))) {

        console.log('🎥 Video en iframe detectado:', iframe.src);

        // ✅ Intentar acceder al video dentro del iframe
        try {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
            if (iframeDoc) {
                const video = iframeDoc.querySelector('video');

                if (video) {
                    console.log('✅ Video tag encontrado dentro del iframe');

                    // ✅ USAR handleVideoWithSegments para el video del iframe
                    handleVideoWithSegments(video, panel);

                    if (autoplayActive) {
                        const title = panel.querySelector('h2');
                        const description = panel.querySelector('p');

                        let textToRead = '';
                        if (title) textToRead += title.textContent + '. ';
                        if (description) textToRead += description.textContent;
                        textToRead = textToRead.trim();

                        if (textToRead.length > 10) {
    speakWithElevenLabs(
        textToRead,
        () => showTurtle(), // ✅ MOSTRAR tortuguita al leer
        () => {
            hideTurtle(); // ✅ OCULTAR al terminar de leer
            if (autoplayActive) {
                setTimeout(() => {
                    video.currentTime = 0;
                    video.play().catch(() => {
                        if (autoplayActive) advanceToNext();
                    });
                }, 1000);
            }
        }
    );
} else {
                            video.currentTime = 0;
                            video.play();
                        }
                    }
                    return;
                }
            }
        } catch (err) {
            console.log('⚠️ No se pudo acceder al video dentro del iframe, usando método legacy');
        }

        // FALLBACK: Si no se pudo acceder al iframe, usar método antiguo
        const showTurtle = panel.dataset.showTurtle === '1';
        const turtleVoice = parseInt(panel.dataset.turtleVoice) || 0;

        if (showTurtle) {
            showTurtleWithVoice(turtleVoice);
        }

        if (autoplayActive) {
            tryDetectIframeVideoDuration(iframe, showTurtle, turtleVoice);
        }
        return;
    }
}

        // 3. BUSCAR PDFs CON CANVAS
        const pdfCanvas = panel.querySelector('canvas[data-pdf-id]');
        if (pdfCanvas) {
            const pdfId = pdfCanvas.dataset.pdfId;
            console.log('📄 PDF con canvas detectado:', pdfId);
            readPdfCanvasAutoplay(pdfId, panel);
            return;
        }

        // 4. BUSCAR PDFs en iframes (legacy)
        for (let iframe of allIframes) {
            if (iframe.src && iframe.src.toLowerCase().includes('.pdf')) {
                console.log('📄 PDF en iframe detectado');
                readPdfAutoplay(iframe.src);
                return;
            }
        }

        // 5. Leer texto (excluyendo navegación)
        const panelClone = panel.cloneNode(true);
        panelClone.querySelectorAll('.exam-navigation').forEach(nav => nav.remove());
        panelClone.querySelectorAll('.pdf-nav-btn').forEach(btn => btn.remove());
        panelClone.querySelectorAll('.course-controls').forEach(ctrl => ctrl.remove());

        const text = panelClone.innerText.replace(/\s+/g,' ').trim();
        if (text && text.length > 10) {
            console.log('📝 Leyendo texto...');
            speakText(text);
        } else {
            advanceToNext();
        }
    }

    function speakText(text) {
        speakWithElevenLabs(
            text,
            () => showTurtle(),
            () => {
                hideTurtle();
                console.log('✅ Lectura completada');
                if (autoplayActive) {
                    setTimeout(() => advanceToNext(), 1000);
                }
            }
        );
    }

    async function readPdfAutoplay(url) {
        try {
            const loadingTask = pdfjsLib.getDocument(url);
            const pdf = await loadingTask.promise;

            let fullText = '';
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const content = await page.getTextContent();
                const strings = content.items.map(item => item.str).join(' ');
                fullText += strings + ' ';
            }

            fullText = fullText.replace(/\s+/g, ' ').trim();

            speakWithElevenLabs(
                fullText.substring(0, 5000),
                () => showTurtle(),
                () => {
                    hideTurtle();
                    if (autoplayActive) {
                        setTimeout(() => advanceToNext(), 1000);
                    }
                }
            );
        } catch (error) {
            console.error('Error leyendo PDF:', error);
            advanceToNext();
        }
    }

    async function readPdfCanvasAutoplay(pdfId, panel) {
        console.log('📖 Iniciando lectura de PDF canvas:', pdfId);

        const title = panel.querySelector('h2');
        const description = panel.querySelector('p');

        let introText = '';
        if (title) introText += title.textContent + '. ';
        if (description) introText += description.textContent;
        introText = introText.trim();

        const pdfInfo = window.getPdfInfo(pdfId);
        if (!pdfInfo) {
            console.error('❌ No se pudo obtener info del PDF');
            advanceToNext();
            return;
        }

        console.log(`📄 PDF tiene ${pdfInfo.totalPages} páginas`);

        let currentPdfPage = 1;

        async function readNextPage() {
            if (!autoplayActive) return;

            if (currentPdfPage > pdfInfo.totalPages) {
                console.log('✅ PDF completado');
                hideTurtle();
                setTimeout(() => advanceToNext(), 1000);
                return;
            }

            if (currentPdfPage > 1) {
                window.navigatePdfPage(pdfId, 'next');
            }

            await new Promise(resolve => setTimeout(resolve, 500));

            const canvas = document.querySelector(`canvas[data-pdf-id="${pdfId}"]`);
            const pdfUrl = canvas.dataset.pdfUrl;

            try {
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                const pdf = await loadingTask.promise;
                const page = await pdf.getPage(currentPdfPage);
                const content = await page.getTextContent();
                const pageText = content.items.map(item => item.str).join(' ');

                let textToRead = currentPdfPage === 1 && introText.length > 10
                    ? introText + '. ' + pageText
                    : pageText;

                textToRead = textToRead.replace(/\s+/g, ' ').trim().substring(0, 5000);

                if (textToRead.length > 10) {
                    speakWithElevenLabs(
                        textToRead,
                        () => showTurtle(),
                        () => {
                            hideTurtle();
                            currentPdfPage++;
                            if (autoplayActive) {
                                setTimeout(() => readNextPage(), 1000);
                            }
                        }
                    );
                } else {
                    currentPdfPage++;
                    readNextPage();
                }
            } catch (error) {
                console.error('Error leyendo página del PDF:', error);
                currentPdfPage++;
                readNextPage();
            }
        }

        readNextPage();
    }

    function advanceToNext() {
        if (!autoplayActive) return;

        console.log('➡️ Avanzando al siguiente...');
        window.currentTurtleIndex = (window.currentTurtleIndex + 1) % 2;

        showIndex(index + 1);

        setTimeout(() => {
            if (autoplayActive) startAutoplay();
        }, 500);
    }

    function tryDetectIframeVideoDuration(iframe, showTurtle, turtleVoice) {
        console.log('🔍 Intentando detectar duración del video en iframe...');

        try {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
            if (iframeDoc) {
                const video = iframeDoc.querySelector('video');

                if (video) {
    video.currentTime = 0;
    video.play().then(() => {
        video.addEventListener('ended', () => {
            if (showTurtle) hideTurtle();

            if (autoplayActive) {
                setTimeout(() => {
                    // ✅ VERIFICAR SI EL SIGUIENTE ES UN JUEGO
                    const links = [...document.querySelectorAll('.syllabus-link')];
                    const currentIndex = links.findIndex(l => l.classList.contains('active'));
                    const nextIndex = currentIndex + 1;

                    if (nextIndex < links.length) {
                        const nextTarget = links[nextIndex].dataset.target;
                        const nextPanel = document.querySelector(nextTarget);

                        if (nextPanel && nextPanel.querySelector('.game-container')) {
    console.log('🎮 Siguiente es un juego - avanzando y mostrando modal');

    // ✅ PRIMERO avanzar al juego
    window.currentTurtleIndex = (window.currentTurtleIndex + 1) % 2;
    const links = [...document.querySelectorAll('.syllabus-link')];
    const currentIndex = links.findIndex(l => l.classList.contains('active'));
    showIndex(currentIndex + 1);

    // ✅ LUEGO pausar autoplay y mostrar modal
    setTimeout(() => {
        autoplayActive = false;
        const btnAutoplay = document.getElementById('btnAutoplay');
        if (btnAutoplay) {
            btnAutoplay.textContent = '▶️ Autoplay';
            btnAutoplay.style.background = '';
        }
        showGameModal();
    }, 300);
} else {
    advanceToNext();
}
                    }
                }, 1000);
            }
        });
    }).catch(() => {
        useEstimatedTime(showTurtle);
    });
                } else {
                    useEstimatedTime(showTurtle);
                }
            } else {
                useEstimatedTime(showTurtle);
            }
        } catch (err) {
            console.log('⚠️ Error accediendo al iframe:', err);
            useEstimatedTime(showTurtle);
        }
    }

    function useEstimatedTime(showTurtle) {
        console.log('⏱️ Usando tiempo estimado de 30 segundos...');
        setTimeout(() => {
            if (showTurtle) hideTurtle();
            if (autoplayActive) advanceToNext();
        }, 30000);
    }

    function showGameModal() {
        const modal = document.getElementById('gameModal');
        if (modal) modal.style.display = 'flex';
    }

});
</script>


{{-- ========== CUESTIONARIO HANDLER ========== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cuestionario-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const activityId = this.dataset.activityId;
            const totalQuestions = parseInt(this.dataset.total);
            const resultDiv = this.parentElement.querySelector('.result-message');
            const submitBtn = this.querySelector('button[type="submit"]');

            let requestData;

            if (totalQuestions > 1) {
                // Múltiples preguntas - validar que todas estén respondidas
                const answers = [];
                let allAnswered = true;

                for (let i = 0; i < totalQuestions; i++) {
                    const radio = this.querySelector(`input[name="question_${i}"]:checked`);
                    if (!radio) {
                        showQuestionModal();
                        allAnswered = false;
                        break;
                    }
                    answers.push({ q: i, a: radio.value });
                }

                if (!allAnswered) return;

                requestData = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ answers })
                };
            } else {
                // Una sola pregunta - enviar con nombre "answer"
                const radio = this.querySelector(`input[name="question_0"]:checked`);

                if (!radio) {
                    showQuestionModal();
                    return;
                }

                // Crear FormData manualmente con el nombre correcto
                const formData = new FormData();
                formData.append('answer', radio.value);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                requestData = {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                };
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            try {
                const response = await fetch(`/activities/${activityId}/submit`, requestData);
                const data = await response.json();

                if (data.success) {
                    let message = `✅ ${data.message}`;
                    if (data.score !== undefined) {
                        message = `
                            <div class="alert alert-success" style="font-size: 18px; padding: 20px; margin: 20px 0;">
                                <h3>✅ ${data.message}</h3>
                                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">
                                    Calificación: ${data.score}%
                                </p>
                                <small style="display: block; margin-top: 10px; opacity: 0.8;">
                                    El cuestionario ha sido guardado.
                                </small>
                            </div>
                        `;
                    } else {
                        message = `
                            <div class="alert alert-success" style="font-size: 18px; padding: 20px; margin: 20px 0;">
                                <h3>✅ ${data.message}</h3>
                                <small style="display: block; margin-top: 10px; opacity: 0.8;">
                                    La respuesta ha sido guardada correctamente.
                                </small>
                            </div>
                        `;
                    }

                    resultDiv.innerHTML = message;
                    this.querySelectorAll('input').forEach(input => input.disabled = true);
                    submitBtn.textContent = 'Respuesta Enviada';

                    // Ocultar navegación si existe
                    const nav = this.previousElementSibling;
                    if (nav && nav.classList.contains('exam-navigation')) {
                        nav.style.display = 'none';
                    }

                    // Mostrar todas las preguntas si es multi-pregunta
                    if (totalQuestions > 1) {
                        this.querySelectorAll('.question-item').forEach(q => {
                            q.style.display = 'block';
                        });
                    }

                    if (data.created) {
                        updateProgressBar();
                    }
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                    submitBtn.disabled = false;
                    submitBtn.textContent = totalQuestions > 1 ? 'Enviar Cuestionario' : 'Enviar Respuesta';
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger">❌ Error al enviar la respuesta</div>`;
                submitBtn.disabled = false;
                submitBtn.textContent = totalQuestions > 1 ? 'Enviar Cuestionario' : 'Enviar Respuesta';
                console.error(error);
            }
        });
    });

    // ===== NAVEGACIÓN PARA CUESTIONARIOS CON MÚLTIPLES PREGUNTAS =====
    document.querySelectorAll('.cuestionario-form').forEach(form => {
        const activityId = form.dataset.activityId;
        const totalQuestions = parseInt(form.dataset.total);

        // Solo agregar navegación si hay más de una pregunta
        if (totalQuestions > 1) {
            let currentQuestion = 0;

            const prevBtn = document.getElementById(`prevQuizQuestion-${activityId}`) ||
                           document.getElementById(`prevQuizQuestion2-${activityId}`);
            const nextBtn = document.getElementById(`nextQuizQuestion-${activityId}`) ||
                           document.getElementById(`nextQuizQuestion2-${activityId}`);
            const submitBtn = document.getElementById(`submitQuiz-${activityId}`) ||
                             document.getElementById(`submitQuiz2-${activityId}`);
            const counter = document.getElementById(`quiz-counter-${activityId}`) ||
                           document.getElementById(`quiz-counter2-${activityId}`);
            const questions = form.querySelectorAll('.question-item');

            if (!prevBtn || !nextBtn || !submitBtn || !counter) return;

            function updateQuizNavigation() {
                counter.querySelector('.current').textContent = currentQuestion + 1;

                questions.forEach((q, idx) => {
                    q.style.display = idx === currentQuestion ? 'block' : 'none';
                });

                prevBtn.disabled = currentQuestion === 0;

                if (currentQuestion === totalQuestions - 1) {
                    nextBtn.style.display = 'none';
                    submitBtn.style.display = 'inline-block';
                } else {
                    nextBtn.style.display = 'inline-block';
                    submitBtn.style.display = 'none';
                }
            }

            prevBtn.onclick = () => {
                if (currentQuestion > 0) {
                    currentQuestion--;
                    updateQuizNavigation();
                }
            };

            nextBtn.onclick = () => {
                const currentQ = questions[currentQuestion];
                const answered = currentQ.querySelector('input[type="radio"]:checked');

                if (!answered) {
                    showQuestionModal();
                    return;
                }

                if (currentQuestion < totalQuestions - 1) {
                    currentQuestion++;
                    updateQuizNavigation();
                }
            };

            updateQuizNavigation();
        }
    });
});
</script>

{{-- ========== PDF VIEWER CON NAVEGACIÓN (CANVAS) ========== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Inicializando visores PDF con canvas...');

    // Objeto para almacenar PDFs cargados
    const pdfViewers = {};

    // Inicializar todos los canvas PDF
    document.querySelectorAll('canvas[data-pdf-id]').forEach(canvas => {
        const pdfId = canvas.dataset.pdfId;
        const pdfUrl = canvas.dataset.pdfUrl;

        console.log(`📄 Cargando PDF: ${pdfId}`);

        // Cargar PDF
        const loadingTask = pdfjsLib.getDocument(pdfUrl);
        loadingTask.promise.then(pdf => {
            console.log(`✅ PDF ${pdfId} cargado: ${pdf.numPages} páginas`);

            // Guardar referencia
            pdfViewers[pdfId] = {
                pdf: pdf,
                currentPage: 1,
                totalPages: pdf.numPages,
                canvas: canvas,
                rendering: false
            };

            // Actualizar total de páginas
            const totalPagesSpan = document.querySelector(`.pdf-total-pages[data-pdf-id="${pdfId}"]`);
            if (totalPagesSpan) {
                totalPagesSpan.textContent = pdf.numPages;
            }

            // Renderizar primera página con múltiples intentos
            console.log(`🎨 Iniciando render del PDF ${pdfId}`);

            // Intento 1: Inmediato
            setTimeout(() => {
                console.log(`📄 Intento 1 de render para ${pdfId}`);
                renderPage(pdfId, 1);
            }, 100);

            // Intento 2: Si el canvas sigue vacío
            setTimeout(() => {
                const viewer = pdfViewers[pdfId];
                if (viewer && viewer.canvas.width === 0) {
                    console.log(`📄 Intento 2 de render para ${pdfId} (canvas estaba vacío)`);
                    viewer.rendering = false;
                    renderPage(pdfId, 1);
                }
            }, 500);

            // Intento 3: Forzado agresivo
            setTimeout(() => {
                const viewer = pdfViewers[pdfId];
                if (viewer && viewer.canvas.width === 0) {
                    console.log(`📄 Intento 3 FORZADO para ${pdfId}`);
                    viewer.rendering = false;

                    // Forzar render con detección de orientación
                    viewer.pdf.getPage(1).then(page => {
                        const viewport = page.getViewport({ scale: 1 });
                        const width = viewport.width;
                        const height = viewport.height;

                        // Detectar orientación: horizontal = 0.75, vertical = 1.3
                        const scale = width > height ? 0.75 : 1.3;

                        const scaledViewport = page.getViewport({ scale: scale });
                        viewer.canvas.height = scaledViewport.height;
                        viewer.canvas.width = scaledViewport.width;

                        const renderContext = {
                            canvasContext: viewer.canvas.getContext('2d'),
                            viewport: scaledViewport
                        };

                        page.render(renderContext).promise.then(() => {
                            viewer.rendering = false;
                            viewer.currentPage = 1;
                            console.log(`✅ PDF ${pdfId} renderizado en intento 3`);
                            updateButtons(pdfId);
                        });
                    });
                }
            }, 1000);

        }).catch(error => {
            console.error(`❌ Error cargando PDF ${pdfId}:`, error);
        });
    });

    // Función para renderizar una página
    function renderPage(pdfId, pageNum) {
        const viewer = pdfViewers[pdfId];
        if (!viewer || viewer.rendering) return;

        viewer.rendering = true;

        viewer.pdf.getPage(pageNum).then(page => {
            // Obtener dimensiones de la página
            const viewport = page.getViewport({ scale: 1 });
            const width = viewport.width;
            const height = viewport.height;

            // Detectar orientación y aplicar escala apropiada
            let scale;
            if (width > height) {
                // PDF HORIZONTAL (landscape) - perfecto como está
                scale = 0.75;
                console.log(`📄 PDF ${pdfId} página ${pageNum} - HORIZONTAL - Escala: 0.75`);
            } else {
                // PDF VERTICAL (portrait) - escala mayor para mejor legibilidad
                scale = 1.3;
                console.log(`📄 PDF ${pdfId} página ${pageNum} - VERTICAL - Escala: 1.3`);
            }

            const scaledViewport = page.getViewport({ scale: scale });

            viewer.canvas.height = scaledViewport.height;
            viewer.canvas.width = scaledViewport.width;

            const renderContext = {
                canvasContext: viewer.canvas.getContext('2d'),
                viewport: scaledViewport
            };

            page.render(renderContext).promise.then(() => {
                viewer.rendering = false;
                viewer.currentPage = pageNum;

                console.log(`📄 PDF ${pdfId}: Renderizada página ${pageNum}`);

                // Actualizar contador
                const currentPageSpan = document.querySelector(`.pdf-current-page[data-pdf-id="${pdfId}"]`);
                if (currentPageSpan) {
                    currentPageSpan.textContent = pageNum;
                }

                // Actualizar botones
                updateButtons(pdfId);
            });
        });
    }

    // Función para actualizar estado de botones
    function updateButtons(pdfId) {
        const viewer = pdfViewers[pdfId];
        if (!viewer) return;

        const prevBtn = document.querySelector(`.pdf-nav-btn[data-pdf-id="${pdfId}"][data-action="prev"]`);
        const nextBtn = document.querySelector(`.pdf-nav-btn[data-pdf-id="${pdfId}"][data-action="next"]`);

        if (prevBtn) prevBtn.disabled = viewer.currentPage <= 1;
        if (nextBtn) nextBtn.disabled = viewer.currentPage >= viewer.totalPages;
    }

    // Manejar clics en botones
    document.querySelectorAll('.pdf-nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const pdfId = this.dataset.pdfId;
            const action = this.dataset.action;
            const viewer = pdfViewers[pdfId];

            if (!viewer) return;

            let newPage = viewer.currentPage;

            if (action === 'next' && viewer.currentPage < viewer.totalPages) {
                newPage = viewer.currentPage + 1;
            } else if (action === 'prev' && viewer.currentPage > 1) {
                newPage = viewer.currentPage - 1;
            }

            if (newPage !== viewer.currentPage) {
                renderPage(pdfId, newPage);
            }
        });
    });

    // Exponer función global para autoplay
    window.navigatePdfPage = function(pdfId, direction) {
        const viewer = pdfViewers[pdfId];
        if (!viewer) return false;

        let newPage = viewer.currentPage;

        if (direction === 'next' && viewer.currentPage < viewer.totalPages) {
            newPage = viewer.currentPage + 1;
        } else if (direction === 'prev' && viewer.currentPage > 1) {
            newPage = viewer.currentPage - 1;
        } else {
            return false; // No se puede avanzar más
        }

        renderPage(pdfId, newPage);
        return true;
    };

    // Función para obtener info del PDF
    window.getPdfInfo = function(pdfId) {
        const viewer = pdfViewers[pdfId];
        if (!viewer) return null;

        return {
            currentPage: viewer.currentPage,
            totalPages: viewer.totalPages,
            hasNextPage: viewer.currentPage < viewer.totalPages,
            hasPrevPage: viewer.currentPage > 1
        };
    };

    console.log('✅ Visores PDF inicializados');
});
</script>

{{-- ========== EXAMEN HANDLER ========== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== NAVEGACIÓN DE PREGUNTAS =====
    document.querySelectorAll('.examen-form').forEach(form => {
        const activityId = form.dataset.activityId;
        const totalQuestions = parseInt(form.dataset.total);
        let currentQuestion = 0;

        const prevBtn = document.getElementById(`prevQuestion-${activityId}`);
        const nextBtn = document.getElementById(`nextQuestion-${activityId}`);
        const submitBtn = document.getElementById(`submitExam-${activityId}`);
        const counter = document.getElementById(`counter-${activityId}`);
        const questions = form.querySelectorAll('.question-item');

        function updateNavigation() {
            counter.querySelector('.current').textContent = currentQuestion + 1;

            questions.forEach((q, idx) => {
                q.style.display = idx === currentQuestion ? 'block' : 'none';
            });

            prevBtn.disabled = currentQuestion === 0;

            if (currentQuestion === totalQuestions - 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'inline-block';
            } else {
                nextBtn.style.display = 'inline-block';
                submitBtn.style.display = 'none';
            }
        }

        prevBtn.onclick = () => {
            if (currentQuestion > 0) {
                currentQuestion--;
                updateNavigation();
            }
        };

        nextBtn.onclick = () => {
            const currentQ = questions[currentQuestion];
            const answered = currentQ.querySelector('input[type="radio"]:checked');

            if (!answered) {
                showQuestionModal();
                return;
            }

            if (currentQuestion < totalQuestions - 1) {
                currentQuestion++;
                updateNavigation();
            }
        };

        updateNavigation();
    });

    // ===== ENVÍO DEL EXAMEN =====
    document.querySelectorAll('.examen-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const activityId = this.dataset.activityId;
            const totalQuestions = parseInt(this.dataset.total);
            const resultDiv = this.parentElement.querySelector('.result-message');
            const submitBtn = this.querySelector('#submitExam-' + activityId);

            const answers = [];
            let allAnswered = true;

            for (let i = 0; i < totalQuestions; i++) {
                const radio = this.querySelector(`input[name="question_${i}"]:checked`);
                if (!radio) {
                    showQuestionModal();
                    allAnswered = false;
                    break;
                }
                answers.push({ q: i, a: radio.value });
            }

            if (!allAnswered) return;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            try {
                const response = await fetch(`/activities/${activityId}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ answers })
                });

                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success" style="font-size: 18px; padding: 20px; margin: 20px 0; background: #d4edda; color: #155724; border-radius: 8px;">
                            <h3 style="margin-bottom: 10px;">✅ ${data.message}</h3>
                            <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">
                                Calificación: ${data.score}%
                            </p>
                            <small style="display: block; margin-top: 10px; opacity: 0.8;">
                                El examen ha sido guardado. Puedes continuar navegando el curso.
                            </small>
                        </div>
                    `;

                    this.querySelectorAll('input').forEach(input => input.disabled = true);
                    submitBtn.textContent = 'Examen Enviado';

                    const nav = this.previousElementSibling;
                    if (nav && nav.classList.contains('exam-navigation')) {
                        nav.style.display = 'none';
                    }

                    this.querySelectorAll('.question-item').forEach(q => {
                        q.style.display = 'block';
                    });

                    if (data.created) {
                        updateProgressBar();
                    }
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px;">❌ ${data.message}</div>`;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Finalizar Examen';
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px;">❌ Error al enviar el examen</div>`;
                submitBtn.disabled = false;
                submitBtn.textContent = 'Finalizar Examen';
                console.error(error);
            }
        });
    });
});

function updateProgressBar() {
    console.log('✅ Progreso actualizado');
}

function showQuestionModal() {
    document.getElementById('questionModal').style.display = 'flex';
}

function closeQuestionModal() {
    document.getElementById('questionModal').style.display = 'none';
}

function closeGameModal() {
    document.getElementById('gameModal').style.display = 'none';
}
</script>

<script>
// ========== FUNCIONES DE SOPA DE LETRAS ==========
function initSopaDeLetras(gridContainer) {
    console.log('🎮 Inicializando Sopa de Letras...');
    console.log('Grid Container:', gridContainer);

    const activityId = gridContainer.dataset.activityId;
    const wordsData = gridContainer.dataset.words;
    const size = parseInt(gridContainer.dataset.size);

    console.log('Activity ID:', activityId);
    console.log('Words Data (raw):', wordsData);
    console.log('Size:', size);

    if (!wordsData) {
        console.error('❌ No hay palabras definidas');
        return;
    }

    let words;
    try {
        words = JSON.parse(wordsData);
        console.log('✅ Palabras parseadas:', words);
    } catch(e) {
        console.error('❌ Error al parsear palabras:', e);
        return;
    }

    const grid = createEmptyGrid(size);
    placeWords(grid, words, size);
    fillEmptySpaces(grid, size);

    renderGrid(gridContainer, grid, size, activityId);
    console.log('✅ Sopa de Letras renderizada exitosamente');
}

function createEmptyGrid(size) {
    return Array(size).fill(null).map(() => Array(size).fill(''));
}

function placeWords(grid, words, size) {
    const directions = [
        [0, 1],
        [1, 0],
        [1, 1],
        [1, -1]
    ];

    words.forEach(word => {
        let placed = false;
        let attempts = 0;

        while (!placed && attempts < 100) {
            const dir = directions[Math.floor(Math.random() * directions.length)];
            const row = Math.floor(Math.random() * size);
            const col = Math.floor(Math.random() * size);

            if (canPlaceWord(grid, word, row, col, dir, size)) {
                placeWord(grid, word, row, col, dir);
                placed = true;
            }
            attempts++;
        }
    });
}

function canPlaceWord(grid, word, row, col, dir, size) {
    const len = word.length;
    for (let i = 0; i < len; i++) {
        const newRow = row + (dir[0] * i);
        const newCol = col + (dir[1] * i);

        if (newRow < 0 || newRow >= size || newCol < 0 || newCol >= size) {
            return false;
        }

        if (grid[newRow][newCol] !== '' && grid[newRow][newCol] !== word[i].toUpperCase()) {
            return false;
        }
    }
    return true;
}

function placeWord(grid, word, row, col, dir) {
    for (let i = 0; i < word.length; i++) {
        const newRow = row + (dir[0] * i);
        const newCol = col + (dir[1] * i);
        grid[newRow][newCol] = word[i].toUpperCase();
    }
}

function fillEmptySpaces(grid, size) {
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for (let i = 0; i < size; i++) {
        for (let j = 0; j < size; j++) {
            if (grid[i][j] === '') {
                grid[i][j] = letters[Math.floor(Math.random() * letters.length)];
            }
        }
    }
}

function renderGrid(container, grid, size, activityId) {
    let html = '<table class="sopa-grid">';
    for (let i = 0; i < size; i++) {
        html += '<tr>';
        for (let j = 0; j < size; j++) {
            html += `<td class="sopa-cell" data-row="${i}" data-col="${j}">${grid[i][j]}</td>`;
        }
        html += '</tr>';
    }
    html += '</table>';
    container.innerHTML = html;

    let firstClick = null;
    let selectedCells = [];

    container.querySelectorAll('.sopa-cell').forEach(cell => {
        cell.addEventListener('click', () => {
            // Permitir seleccionar cualquier celda, incluso las encontradas

            if (!firstClick) {
                container.querySelectorAll('.sopa-cell').forEach(c => c.classList.remove('selected', 'selecting'));

                firstClick = cell;
                cell.classList.add('selecting');
                selectedCells = [cell];
                console.log('🎯 Primera letra seleccionada:', cell.textContent);
            }
            else {
                const row1 = parseInt(firstClick.dataset.row);
                const col1 = parseInt(firstClick.dataset.col);
                const row2 = parseInt(cell.dataset.row);
                const col2 = parseInt(cell.dataset.col);

                const path = getCellsBetween(container, row1, col1, row2, col2);

                if (path.length > 0) {
                    selectedCells = path;
                    selectedCells.forEach(c => c.classList.add('selected'));

                    setTimeout(() => {
                        checkWord(selectedCells, activityId);
                        firstClick = null;
                        selectedCells = [];
                    }, 300);
                } else {
                    firstClick.classList.remove('selecting');
                    firstClick = null;
                    selectedCells = [];
                }
            }
        });

        cell.addEventListener('mouseenter', () => {
            if (firstClick) {
                container.querySelectorAll('.preview').forEach(c => c.classList.remove('preview'));

                const row1 = parseInt(firstClick.dataset.row);
                const col1 = parseInt(firstClick.dataset.col);
                const row2 = parseInt(cell.dataset.row);
                const col2 = parseInt(cell.dataset.col);

                const path = getCellsBetween(container, row1, col1, row2, col2);
                if (path.length > 1) {
                    path.forEach(c => c.classList.add('preview'));
                }
            }
        });
    });
}

function getCellsBetween(container, row1, col1, row2, col2) {
    const cells = [];

    const rowDiff = row2 - row1;
    const colDiff = col2 - col1;

    const isHorizontal = rowDiff === 0;
    const isVertical = colDiff === 0;
    const isDiagonal = Math.abs(rowDiff) === Math.abs(colDiff);

    if (!isHorizontal && !isVertical && !isDiagonal) {
        return [];
    }

    const steps = Math.max(Math.abs(rowDiff), Math.abs(colDiff));
    const rowStep = steps === 0 ? 0 : rowDiff / steps;
    const colStep = steps === 0 ? 0 : colDiff / steps;

    for (let i = 0; i <= steps; i++) {
        const row = row1 + Math.round(rowStep * i);
        const col = col1 + Math.round(colStep * i);
        const cell = container.querySelector(`.sopa-cell[data-row="${row}"][data-col="${col}"]`);
        if (cell) {
            cells.push(cell);
        }
    }

    return cells;
}

function checkWord(cells, activityId) {
    const word = cells.map(c => c.textContent).join('');
    const wordReverse = word.split('').reverse().join('');
    const wordList = document.querySelector(`#word-list-${activityId}`);

    let found = false;

    wordList.querySelectorAll('li').forEach(li => {
        const targetWord = li.dataset.word;
        if (word === targetWord || wordReverse === targetWord) {
            cells.forEach(c => {
                c.classList.add('found');
                c.classList.remove('selected', 'selecting', 'preview');
            });
            li.classList.add('found');
            found = true;
            console.log('✅ Palabra encontrada:', targetWord);

            cells.forEach((c, i) => {
                setTimeout(() => {
                    c.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        c.style.transform = '';
                    }, 200);
                }, i * 50);
            });
        }
    });

    if (!found) {
        console.log('❌ Palabra incorrecta:', word);
        cells.forEach(c => {
            c.classList.add('wrong');
            setTimeout(() => {
                c.classList.remove('wrong');
            }, 500);
        });
    }

    cells.forEach(c => c.classList.remove('selected', 'selecting', 'preview'));
}

function checkSopaCompletion(activityId) {
    const wordList = document.querySelector(`#word-list-${activityId}`);
    const totalWords = wordList.querySelectorAll('li').length;
    const foundWords = wordList.querySelectorAll('li.found').length;

    const resultDiv = document.querySelector(`#grid-${activityId}`).parentElement.querySelector('.result-message');
    const submitBtn = document.querySelector(`#grid-${activityId}`).parentElement.querySelector('.btn-submit');

    if (foundWords === totalWords) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';

        fetch(`/activities/${activityId}/submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ completed: true })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success" style="font-size: 18px; padding: 20px; margin: 20px 0;">
                        <h3>✅ ¡Felicidades! Completaste la sopa de letras</h3>
                        <p style="font-size: 16px; margin-top: 10px;">
                            Encontraste todas las ${totalWords} palabras correctamente.
                        </p>
                        <small style="display: block; margin-top: 10px; opacity: 0.8;">
                            Tu progreso ha sido guardado.
                        </small>
                    </div>
                `;
                submitBtn.textContent = 'Completado ✓';

                if (data.created) {
                    updateProgressBar();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = `<div class="alert alert-danger">❌ Error al guardar el progreso</div>`;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Verificar Completado';
        });
    } else {
        resultDiv.innerHTML = `
            <div class="alert alert-warning" style="padding: 15px;">
                ⚠️ Has encontrado <strong>${foundWords}</strong> de <strong>${totalWords}</strong> palabras.
                <br>¡Sigue buscando!
            </div>
        `;
    }
}

// ========== FUNCIONES DE AHORCADO ==========
function initAhorcado(gameContainer) {
    console.log('🎮 Inicializando Ahorcado...');

    const activityId = gameContainer.dataset.activityId;
    const word = gameContainer.dataset.word;
    const hint = gameContainer.dataset.hint;
    const maxAttempts = parseInt(gameContainer.dataset.maxAttempts);

    if (!word) {
        console.error('❌ No hay palabra definida');
        return;
    }

    let attempts = 0;
    let guessedLetters = [];
    let wordArray = word.split('');

    document.getElementById(`hint-${activityId}`).textContent = hint || 'Sin pista';
    document.getElementById(`attempts-${activityId}`).textContent = maxAttempts;

    updateWordDisplay();

    const keyboard = document.getElementById(`keyboard-${activityId}`);
    const letters = 'ABCDEFGHIJKLMNÑOPQRSTUVWXYZ';

    letters.split('').forEach(letter => {
        const btn = document.createElement('button');
        btn.textContent = letter;
        btn.className = 'letter-btn';
        btn.onclick = () => guessLetter(letter, btn);
        keyboard.appendChild(btn);
    });

    function guessLetter(letter, btn) {
        if (guessedLetters.includes(letter)) return;

        guessedLetters.push(letter);
        btn.disabled = true;
        btn.classList.add('used');

        if (wordArray.includes(letter)) {
            btn.classList.add('correct');
            updateWordDisplay();
            checkWin();
        } else {
            btn.classList.add('incorrect');
            attempts++;
            updateAttemptsDisplay();
            showHangmanPart(attempts - 1);
            checkLoss();
        }
    }

    function updateWordDisplay() {
        const display = document.getElementById(`word-display-${activityId}`);
        display.innerHTML = wordArray.map(letter => {
            return guessedLetters.includes(letter)
                ? `<span class="letter revealed">${letter}</span>`
                : `<span class="letter hidden">_</span>`;
        }).join('');
    }

    function updateAttemptsDisplay() {
        document.getElementById(`attempts-${activityId}`).textContent = maxAttempts - attempts;
    }

    function showHangmanPart(partIndex) {
        const part = gameContainer.querySelector(`.hangman-part[data-part="${partIndex}"]`);
        if (part) part.style.display = 'block';
    }

    function checkWin() {
        if (wordArray.every(letter => guessedLetters.includes(letter))) {
            const resultDiv = gameContainer.querySelector('.result-message');
            resultDiv.innerHTML = `
                <div class="alert alert-success" style="font-size: 18px; padding: 20px; margin: 20px 0;">
                    <h3>🎉 ¡Felicidades! Adivinaste la palabra</h3>
                    <p style="font-size: 20px; font-weight: bold;">${word}</p>
                </div>
            `;

            keyboard.querySelectorAll('.letter-btn').forEach(btn => btn.disabled = true);

            submitAhorcado(activityId, true);
        }
    }

    function checkLoss() {
        if (attempts >= maxAttempts) {
            const resultDiv = gameContainer.querySelector('.result-message');
            resultDiv.innerHTML = `
                <div class="alert alert-danger" style="font-size: 18px; padding: 20px; margin: 20px 0;">
                    <h3>😞 Game Over</h3>
                    <p>La palabra era: <strong>${word}</strong></p>
                </div>
            `;

            keyboard.querySelectorAll('.letter-btn').forEach(btn => btn.disabled = true);

            const display = document.getElementById(`word-display-${activityId}`);
            display.innerHTML = wordArray.map(letter =>
                `<span class="letter revealed">${letter}</span>`
            ).join('');
        }
    }
}

function submitAhorcado(activityId, success) {
    fetch(`/activities/${activityId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ success })
    })
    .then(res => res.json())
    .then(data => {
        console.log('✅ Ahorcado guardado:', data);
        if (data.created) {
            updateProgressBar();
        }
    })
    .catch(error => console.error('Error:', error));
}
// ========== FUNCIONES DE CRUCIGRAMA ==========
function initCrucigrama(gameContainer) {
    console.log('🎮 Inicializando Crucigrama...');
    console.log('Game container:', gameContainer);

    if (!gameContainer) {
        console.error('❌ Game container es null');
        return;
    }

    const activityId = gameContainer.dataset.activityId;
    const wordsData = gameContainer.dataset.words;
    const size = parseInt(gameContainer.dataset.size);

    console.log('Activity ID:', activityId);
    console.log('Size:', size);
    console.log('Words data (raw):', wordsData);
    console.log('Type of words data:', typeof wordsData);

    if (!wordsData) {
        console.error('❌ No hay palabras definidas en data-words');
        console.log('Atributos del gameContainer:', gameContainer.attributes);
        return;
    }

    let words;
    try {
        words = JSON.parse(wordsData);
        console.log('✅ Palabras parseadas exitosamente:', words);
        console.log('Número de palabras:', words.length);
    } catch(e) {
        console.error('❌ Error al parsear palabras:', e);
        console.error('Contenido que intentó parsear:', wordsData);
        return;
    }

    if (!words || words.length === 0) {
        console.error('❌ Array de palabras vacío o undefined');
        return;
    }

    console.log('🔨 Creando grilla del crucigrama...');

    const { grid, placements } = createCrucigramaGrid(size, words);

    console.log('📊 Grilla creada. Placements:', placements.length);

    console.log('🎨 Renderizando grilla...');
    renderCrucigramaGrid(activityId, grid, size, placements);

    console.log('📝 Renderizando pistas...');
    renderCrucigramaClues(activityId, words, placements);

    console.log('✅ Crucigrama inicializado completamente');
}

function createCrucigramaGrid(size, words) {
    const grid = Array(size).fill(null).map(() =>
        Array(size).fill(null).map(() => ({ letter: '', editable: false, wordIndex: -1 }))
    );

    const placements = [];

    words.forEach((wordData, index) => {
        const word = wordData.word.toUpperCase();
        const direction = wordData.direction;
        const wordLength = word.length;

        let placed = false;
        let attempts = 0;
        const maxAttempts = 100;

        while (!placed && attempts < maxAttempts) {
            let row, col;

            if (direction === 'horizontal') {
                row = Math.floor(Math.random() * size);
                col = Math.floor(Math.random() * (size - wordLength + 1));

                let canPlace = true;
                for (let i = 0; i < wordLength; i++) {
                    if (grid[row][col + i].editable && grid[row][col + i].letter !== word[i]) {
                        canPlace = false;
                        break;
                    }
                }

                if (canPlace) {
                    for (let i = 0; i < wordLength; i++) {
                        grid[row][col + i] = {
                            letter: word[i],
                            editable: true,
                            wordIndex: index,
                            direction: 'horizontal'
                        };
                    }
                    placements.push({ index, row, col, direction, word, clue: wordData.clue });
                    placed = true;
                    console.log(`✅ Palabra "${word}" colocada en (${row}, ${col}) horizontal`);
                }
            } else {
                row = Math.floor(Math.random() * (size - wordLength + 1));
                col = Math.floor(Math.random() * size);

                let canPlace = true;
                for (let i = 0; i < wordLength; i++) {
                    if (grid[row + i][col].editable && grid[row + i][col].letter !== word[i]) {
                        canPlace = false;
                        break;
                    }
                }

                if (canPlace) {
                    for (let i = 0; i < wordLength; i++) {
                        grid[row + i][col] = {
                            letter: word[i],
                            editable: true,
                            wordIndex: index,
                            direction: 'vertical'
                        };
                    }
                    placements.push({ index, row, col, direction, word, clue: wordData.clue });
                    placed = true;
                    console.log(`✅ Palabra "${word}" colocada en (${row}, ${col}) vertical`);
                }
            }

            attempts++;
        }

        if (!placed) {
            console.warn(`⚠️ No se pudo colocar la palabra "${word}" después de ${maxAttempts} intentos`);
        }
    });

    return { grid, placements };
}

function renderCrucigramaGrid(activityId, grid, size, placements) {
    const gridContainer = document.getElementById(`crucigrama-grid-${activityId}`);

    if (!gridContainer) {
        console.error('❌ No se encontró el contenedor de la grilla');
        return;
    }

    let html = '<table class="crossword-table">';

    for (let i = 0; i < size; i++) {
        html += '<tr>';
        for (let j = 0; j < size; j++) {
            const cell = grid[i][j];
            if (cell.editable) {
                let wordNumber = '';
                const placement = placements.find(p => p.row === i && p.col === j);
                if (placement) {
                    wordNumber = `<span class="cell-number">${placement.index + 1}</span>`;
                }

                html += `
                    <td class="crossword-cell editable" data-row="${i}" data-col="${j}" data-answer="${cell.letter}">
                        ${wordNumber}
                        <input type="text" maxlength="1" class="cell-input" data-row="${i}" data-col="${j}">
                    </td>
                `;
            } else {
                html += '<td class="crossword-cell blocked"></td>';
            }
        }
        html += '</tr>';
    }
    html += '</table>';

    gridContainer.innerHTML = html;
    console.log('✅ Grilla renderizada');

    gridContainer.querySelectorAll('.cell-input').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();

            if (this.value.length === 1) {
                const row = parseInt(this.dataset.row);
                const col = parseInt(this.dataset.col);

                const nextInput = gridContainer.querySelector(
                    `.cell-input[data-row="${row}"][data-col="${col + 1}"]`
                ) || gridContainer.querySelector(
                    `.cell-input[data-row="${row + 1}"][data-col="${col}"]`
                );

                if (nextInput) nextInput.focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '') {
                const row = parseInt(this.dataset.row);
                const col = parseInt(this.dataset.col);

                const prevInput = gridContainer.querySelector(
                    `.cell-input[data-row="${row}"][data-col="${col - 1}"]`
                ) || gridContainer.querySelector(
                    `.cell-input[data-row="${row - 1}"][data-col="${col}"]`
                );

                if (prevInput) {
                    prevInput.focus();
                    prevInput.value = '';
                }
            }
        });
    });
}

function renderCrucigramaClues(activityId, words, placements) {
    const horizontalList = document.getElementById(`clues-horizontal-${activityId}`);
    const verticalList = document.getElementById(`clues-vertical-${activityId}`);

    if (!horizontalList || !verticalList) {
        console.error('❌ No se encontraron las listas de pistas');
        return;
    }

    horizontalList.innerHTML = '';
    verticalList.innerHTML = '';

    placements.forEach((placement) => {
        const li = document.createElement('li');
        li.textContent = `${placement.index + 1}. ${placement.clue} (${placement.word.length} letras)`;

        if (placement.direction === 'horizontal') {
            horizontalList.appendChild(li);
        } else {
            verticalList.appendChild(li);
        }
    });

    console.log('✅ Pistas renderizadas');
}

function checkCrucigramaCompletion(activityId) {
    const grid = document.getElementById(`crucigrama-grid-${activityId}`);
    const cells = grid.querySelectorAll('.crossword-cell.editable');
    const resultDiv = grid.closest('.crucigrama-game').querySelector('.result-message');
    const submitBtn = grid.closest('.crucigrama-game').querySelector('.btn-submit');

    let correct = 0;
    let total = cells.length;

    cells.forEach(cell => {
        cell.classList.remove('correct', 'incorrect');
    });

    cells.forEach(cell => {
        const input = cell.querySelector('.cell-input');
        const answer = cell.dataset.answer;

        if (input && input.value.toUpperCase() === answer) {
            correct++;
            cell.classList.add('correct');
        } else if (input && input.value !== '') {
            cell.classList.add('incorrect');
        }
    });

    if (correct === total) {
        resultDiv.innerHTML = `
            <div class="alert alert-success" style="font-size: 18px; padding: 20px; margin: 20px 0;">
                <h3>🎉 ¡Crucigrama completado correctamente!</h3>
                <p>Todas las respuestas son correctas</p>
                <small style="display: block; margin-top: 10px; opacity: 0.8;">
                    Tu progreso ha sido guardado.
                </small>
            </div>
        `;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Completado ✓';

        fetch(`/activities/${activityId}/submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ completed: true })
        })
        .then(res => res.json())
        .then(data => {
            console.log('✅ Crucigrama guardado:', data);
            if (data.created) {
                updateProgressBar();
            }
        })
        .catch(error => {
            console.error('Error al guardar:', error);
        });
    } else {
        resultDiv.innerHTML = `
            <div class="alert alert-warning" style="padding: 15px;">
                ⚠️ Tienes <strong>${correct}</strong> de <strong>${total}</strong> respuestas correctas.
                <br>Revisa las celdas marcadas en rojo.
            </div>
        `;
    }
}
</script>

<script>
// ========== SECUENCIA DE BIENVENIDA ==========
const welcomeSequence = [
    {
        turtle: 'toby',
        audio: "{{ asset('videos/welcome/audio1.mp3') }}"  // "Hola soy Toby"
    },
    {
        turtle: 'mely',
        audio: "{{ asset('videos/welcome/audio2.mp3') }}"  // "Y yo soy Mely..."
    },
    {
        turtle: 'toby',
        audio: "{{ asset('videos/welcome/audio3.mp3') }}"  // "Para esto solo..."
    },
    {
        turtle: 'mely',
        audio: "{{ asset('videos/welcome/audio4.mp3') }}"  // "Disfruta el curso"
    }
];

let currentStepIndex = 0;
let currentAudio = null;
let isWelcomeActive = true;

function resetTurtles() {
    ['toby', 'mely'].forEach(turtle => {
        const staticEl = document.getElementById(`welcome-${turtle}-static`);
        const videoEl = document.getElementById(`welcome-${turtle}-video`);

        if (!staticEl || !videoEl) return;

        videoEl.classList.remove('welcome-active'); // Corregido
        videoEl.pause();

        staticEl.classList.remove('welcome-hidden'); // Corregido
        staticEl.classList.add('welcome-active');    // Aseguramos que se vea
    });
}

function showWelcomeModal() {
    const modal = document.getElementById('welcomeModal');
    modal.style.display = 'flex';

    resetTurtles();

    // 👇 ESPERA interacción
    modal.addEventListener('click', startSequence, { once: true });
}

function startSequence() {
    playWelcomeStep(0);
}

function playWelcomeStep(index) {
    if (!isWelcomeActive) return; // 🔴 DETENER TODO

    if (index >= welcomeSequence.length) {
        setTimeout(() => { closeWelcomeModal(); }, 1000);
        return;
    }

    currentStepIndex = index;
    const step = welcomeSequence[index];
    const activeTurtle = step.turtle;
    const inactiveTurtle = activeTurtle === 'toby' ? 'mely' : 'toby';

    // Activa la tortuga que habla
    const activeStatic = document.getElementById(`welcome-${activeTurtle}-static`);
    const activeVideo = document.getElementById(`welcome-${activeTurtle}-video`);

    activeStatic.classList.remove('welcome-active');
    activeStatic.classList.add('welcome-hidden');
    activeVideo.classList.add('welcome-active');
    activeVideo.play();

    // Desactiva la otra
    const inactiveStatic = document.getElementById(`welcome-${inactiveTurtle}-static`);
    const inactiveVideo = document.getElementById(`welcome-${inactiveTurtle}-video`);

    inactiveVideo.classList.remove('welcome-active');
    inactiveVideo.pause();
    inactiveStatic.classList.add('welcome-active');
    inactiveStatic.classList.remove('welcome-hidden');

    if (currentAudio) { currentAudio.pause(); }

    currentAudio = new Audio(step.audio);
    currentAudio.onended = () => {
    if (!isWelcomeActive) return; // 🔴 DETENER

    activeVideo.classList.remove('welcome-active');
    activeVideo.pause();
    activeStatic.classList.add('welcome-active');
    activeStatic.classList.remove('welcome-hidden');

    setTimeout(() => {
        if (isWelcomeActive) {
            playWelcomeStep(index + 1);
        }
    }, 500);
};

    currentAudio.play().catch(e => console.error("Error al reproducir audio:", e));
}

function closeWelcomeModal() {
    isWelcomeActive = false; // 🔴 CORTA TODO

    if (currentAudio) {
        currentAudio.pause();
        currentAudio = null;
    }

    document.getElementById('welcome-toby-video').pause();
    document.getElementById('welcome-mely-video').pause();

    const modal = document.getElementById('welcomeModal');
    modal.style.display = 'none';

    sessionStorage.setItem('welcomeSeen', 'true');
}

// Auto-mostrar modal al cargar la página (solo una vez por sesión)
document.addEventListener('DOMContentLoaded', function() {
    const alreadySeen = sessionStorage.getItem('welcomeSeen');

    if (!alreadySeen) {
        showWelcomeModal();
    }
});

</script>

<script> //JS listas
document.addEventListener("DOMContentLoaded", function () {

    const departments = @json($departments);

    // CLICK EN DEPARTAMENTO
    document.querySelectorAll('.dept-card-modal').forEach(card => {
        card.addEventListener('click', function () {

            let deptId = this.dataset.id;

            // Activar selección
            document.querySelectorAll('.dept-card-modal')
                .forEach(c => c.classList.remove('active-card'));

            this.classList.add('active-card');

            let dept = departments.find(d => d.id == deptId);

            let html = '';

            if (!dept || !dept.workstations || dept.workstations.length === 0) {
                html = `<div class="empty">No hay puestos</div>`;
            } else {
                dept.workstations.forEach(w => {
                    html += `
                        <div class="card work-card-modal" data-id="${w.id}">
                            ${w.name}
                        </div>
                    `;
                });
            }

            document.getElementById('workstations-modal').innerHTML = html;

            // Reset participantes
            document.getElementById('participants-modal').innerHTML =
                `<div class="empty">Selecciona un puesto</div>`;

            attachWorkEvents(dept);
        });
    });

    // EVENTOS DE PUESTOS
    function attachWorkEvents(dept) {

        document.querySelectorAll('.work-card-modal').forEach(card => {
            card.addEventListener('click', function () {

                let workId = this.dataset.id;

                document.querySelectorAll('.work-card-modal')
                    .forEach(c => c.classList.remove('active-card'));

                this.classList.add('active-card');

                let workstation = dept.workstations.find(w => w.id == workId);

                let html = '';

                if (!workstation || !workstation.participants || workstation.participants.length === 0) {
                    html = `<div class="empty">No hay participantes</div>`;
                } else {

                    html += `<strong>${dept.name} - ${workstation.name}</strong>`;

                    workstation.participants.forEach(p => {
                        html += `
                            <div class="card">
                                ${p.name}
                            </div>
                        `;
                    });
                }

                document.getElementById('participants-modal').innerHTML = html;
            });
        });
    }

});
</script>

<script>
function openGuide(url) {
    window.open(url, '_blank');
}

function showNoGuideMessage() {
    alert("Este curso no cuenta con una guía disponible 📭");
}
</script>
@endsection
