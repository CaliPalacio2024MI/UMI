@extends('layouts.app')

@section('title', $course->title)

@section('content')

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

<div class="course-layout">

    {{-- ===== MENU ===== --}}
    <aside class="course-menu">
        <h3>📚 {{ $course->title }}</h3>

        @foreach ($course->topics as $topic)
            <div class="syllabus-link" data-target="#content-topic-{{ $topic->id }}">
                {{ $loop->iteration }}. {{ $topic->title }}
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
        @endforeach
    </aside>

    {{-- ===== VIEWER ===== --}}
    <main class="course-viewer">

        {{-- CONTROLS --}}
        <div class="course-controls">
            <button id="btnPrev">⏮ Anterior</button>
            <button id="btnAutoplay">▶️ Autoplay</button>
            <button id="btnNext">⏭ Siguiente</button>
            <button class="exit" onclick="window.history.back()">Salir</button>
        </div>

        <div class="course-progress">
            <div class="course-progress-bar"></div>
        </div>

        @foreach ($course->topics as $topic)
            <section class="content-panel" id="content-topic-{{ $topic->id }}">
                <h2>{{ $topic->title }}</h2>
                <p>{{ $topic->description }}</p>

                @if ($topic->file_path)
                    <div class="file-viewer">
                        <iframe src="{{ asset('storage/'.$topic->file_path) }}#toolbar=0&navpanes=0&scrollbar=0" class="pdf-frame"></iframe>
                    </div>
                @endif
            </section>

            @foreach ($topic->subtopics as $subtopic)
                <section class="content-panel" id="content-subtopic-{{ $subtopic->id }}">
                    <h2>{{ $subtopic->title }}</h2>
                    <p>{{ $subtopic->description }}</p>

                    @if ($subtopic->file_path)
                        <div class="file-viewer">
                            <iframe src="{{ asset('storage/'.$subtopic->file_path) }}#toolbar=0&navpanes=0&scrollbar=0" class="pdf-frame"></iframe>
                        </div>
                    @endif
                </section>

                @foreach ($subtopic->activities as $activity)
                    <section class="content-panel" id="content-activity-{{ $activity->id }}" data-activity-type="{{ $activity->type }}">
                        <h2>{{ $activity->title }}</h2>
                        <p>{{ $activity->description }}</p>

                        {{-- CUESTIONARIO --}}
                        @if ($activity->type === 'Cuestionario')
                            <div class="game-container cuestionario-container">
                                <div class="question-box">
                                    <h3>{{ $activity->content['question'] ?? '' }}</h3>
                                    <form class="cuestionario-form" data-activity-id="{{ $activity->id }}">
                                        @csrf
                                        @foreach ($activity->content['options'] ?? [] as $index => $option)
                                            <label class="option-label">
                                                <input type="radio" name="answer" value="{{ $index }}" required>
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                        <button type="submit" class="btn-submit">Enviar Respuesta</button>
                                    </form>
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
                                
                                <button class="btn-submit" onclick="checkSopaCompletion({{ $activity->id }})">Verificar Completado</button>
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
                            <div class="file-viewer">
                                <iframe src="{{ asset('storage/'.$activity->file_path) }}#toolbar=0&navpanes=0&scrollbar=0" class="pdf-frame"></iframe>
                            </div>
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
                    <h2>{{ $activity->title }}</h2>
                    <p>{{ $activity->description }}</p>

                    {{-- CUESTIONARIO --}}
                    @if ($activity->type === 'Cuestionario')
                        <div class="game-container cuestionario-container">
                            <div class="question-box">
                                <h3>{{ $activity->content['question'] ?? '' }}</h3>
                                <form class="cuestionario-form" data-activity-id="{{ $activity->id }}">
                                    @csrf
                                    @foreach ($activity->content['options'] ?? [] as $index => $option)
                                        <label class="option-label">
                                            <input type="radio" name="answer" value="{{ $index }}" required>
                                            <span>{{ $option }}</span>
                                        </label>
                                    @endforeach
                                    <button type="submit" class="btn-submit">Enviar Respuesta</button>
                                </form>
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
                            
                            <button class="btn-submit" onclick="checkSopaCompletion({{ $activity->id }})">Verificar Completado</button>
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
                        <div class="file-viewer">
                            <iframe src="{{ asset('storage/'.$activity->file_path) }}#toolbar=0&navpanes=0&scrollbar=0" class="pdf-frame"></iframe>
                        </div>
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

    </main>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
// Variable global para controlar el estado de lectura
window.isReading = false;

document.addEventListener('DOMContentLoaded', function () {

    const links = [...document.querySelectorAll('.syllabus-link')];
    const panels = document.querySelectorAll('.content-panel');
    const bar = document.querySelector('.course-progress-bar');

    let index = 0;

    panels.forEach(p => p.style.display = 'none');
    showIndex(0);
    
    // Inicializar todas las sopas de letras al cargar
    setTimeout(() => {
        console.log('🔍 Buscando juegos para inicializar...');
        
        const sopaGrids = document.querySelectorAll('.grid-container');
        console.log(`Encontrados ${sopaGrids.length} juegos de Sopa de Letras`);
        sopaGrids.forEach((grid, index) => {
            console.log(`Procesando sopa ${index + 1}, innerHTML length:`, grid.innerHTML.trim().length);
            // Inicializar si está vacío o solo tiene comentario HTML
            if(!grid.innerHTML.trim() || grid.innerHTML.trim().length < 100) {
                console.log('✅ Inicializando Sopa de Letras...');
                initSopaDeLetras(grid);
            } else {
                console.log('⚠️ Sopa ya inicializada');
            }
        });
        
        // Inicializar Ahorcado
        const ahorcadoGames = document.querySelectorAll('.ahorcado-game');
        console.log(`Encontrados ${ahorcadoGames.length} juegos de Ahorcado`);
        ahorcadoGames.forEach(game => {
            if(!game.querySelector('.letter-btn')) {
                console.log('✅ Inicializando Ahorcado...');
                initAhorcado(game);
            }
        });
        
        // Inicializar Crucigrama
        const crucigramaGames = document.querySelectorAll('.crucigrama-game');
        console.log(`Encontrados ${crucigramaGames.length} juegos de Crucigrama`);
        crucigramaGames.forEach((game, index) => {
            console.log(`Procesando crucigrama ${index + 1}:`, game);
            const grid = game.querySelector('.crucigrama-grid');
            console.log('Grid encontrado:', grid);
            console.log('Grid innerHTML:', grid ? grid.innerHTML : 'null');
            console.log('Grid innerHTML length:', grid ? grid.innerHTML.length : 0);
            
            // Siempre inicializar si la grilla existe y está prácticamente vacía
            if(grid && grid.innerHTML.trim().length < 50) {
                console.log('✅ Iniciando inicialización de crucigrama...');
                initCrucigrama(game);
            } else if (!grid) {
                console.error('❌ No se encontró .crucigrama-grid dentro del juego');
            } else {
                console.warn('⚠️ La grilla ya tiene contenido, saltando inicialización');
            }
        });
    }, 200);

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
            bar.style.width = ((i+1)/links.length)*100 + '%';
            
            // Inicializar sopa de letras si existe en el panel actual
            setTimeout(() => {
                const sopaGrid = panel.querySelector('.grid-container');
                if(sopaGrid && (sopaGrid.innerHTML.trim().length < 100)) {
                    console.log('🎯 Inicializando sopa desde showIndex');
                    initSopaDeLetras(sopaGrid);
                }
                
                // Inicializar Ahorcado si existe
                const ahorcadoGame = panel.querySelector('.ahorcado-game');
                if(ahorcadoGame && !ahorcadoGame.querySelector('.letter-btn')) {
                    console.log('🎯 Inicializando ahorcado desde showIndex');
                    initAhorcado(ahorcadoGame);
                }
                
                // Inicializar Crucigrama si existe
                const crucigramaGame = panel.querySelector('.crucigrama-game');
                if(crucigramaGame) {
                    const grid = crucigramaGame.querySelector('.crucigrama-grid');
                    if(grid && grid.innerHTML.trim().length < 50) {
                        console.log('🎯 Inicializando crucigrama desde showIndex');
                        initCrucigrama(crucigramaGame);
                    }
                }
            }, 100);
        }
    }

    links.forEach((l,i)=>{
        l.addEventListener('click', ()=>{
            showIndex(i);
        });
    });

    document.getElementById('btnNext').onclick = ()=> showIndex(index+1);
    document.getElementById('btnPrev').onclick = ()=> showIndex(index-1);

    // ====== AUTOPLAY ======
    let autoplayActive = false;
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
            hideTurtle();
        }
    };
    
    function startAutoplay() {
        if (!autoplayActive) return;
        
        const panel = document.querySelector('.content-panel:not([style*="display: none"])');
        if (!panel) return;
        
        // Detectar si es un juego
        if (panel.querySelector('.game-container')) {
            console.log('🎮 Juego detectado - PAUSANDO autoplay');
            showGameModal();
            autoplayActive = false;
            btnAutoplay.textContent = '▶️ Autoplay';
            btnAutoplay.style.background = '';
            return;
        }
        
        // Leer contenido del panel actual
        readCurrentPanel();
    }
    
    function readCurrentPanel() {
        const panel = document.querySelector('.content-panel:not([style*="display: none"])');
        if (!panel) return;
        
        // 1. BUSCAR VIDEOS (tag video directo)
        let video = panel.querySelector('video');
        if (video) {
            console.log('🎥 Video <video> detectado');
            handleVideoAutoplay(video, panel);
            return;
        }
        
        // 2. BUSCAR VIDEOS en iframes (archivos .mp4, .webm, .ogg)
        const allIframes = panel.querySelectorAll('iframe');
        for (let iframe of allIframes) {
            if (iframe.src && 
                (iframe.src.toLowerCase().includes('.mp4') || 
                 iframe.src.toLowerCase().includes('.webm') ||
                 iframe.src.toLowerCase().includes('.ogg') ||
                 iframe.src.toLowerCase().includes('video'))) {
                
                console.log('🎥 Video en iframe detectado:', iframe.src);
                handleIframeVideoAutoplay(iframe, panel);
                return;
            }
        }
        
        // 3. Buscar PDFs
        for (let iframe of allIframes) {
            if (iframe.src && iframe.src.toLowerCase().includes('.pdf')) {
                console.log('📄 PDF detectado');
                readPdfAutoplay(iframe.src);
                return;
            }
        }
        
        // 4. Si no hay video ni PDF, leer texto
        const text = panel.innerText.replace(/\s+/g,' ').trim();
        if (text && text.length > 10) {
            console.log('📝 Leyendo texto...');
            speakText(text);
        } else {
            // No hay contenido, avanzar
            advanceToNext();
        }
    }
    
    function handleIframeVideoAutoplay(iframe, panel) {
        // Para videos en iframes, leer descripción y luego mostrar/activar el iframe
        const title = panel.querySelector('h2');
        const description = panel.querySelector('p');
        
        let textToRead = '';
        if (title) textToRead += title.textContent + '. ';
        if (description) textToRead += description.textContent;
        
        textToRead = textToRead.trim();
        
        if (textToRead.length > 10) {
            console.log('📝 Leyendo descripción del video...');
            const msg = new SpeechSynthesisUtterance(textToRead);
            msg.lang = 'es-MX';
            msg.rate = 1;
            msg.pitch = 1;
            
            msg.onstart = () => {
                showTurtle();
            };
            
            msg.onend = () => {
                hideTurtle();
                console.log('✅ Descripción completada');
                
                // Después de leer, intentar acceder al video dentro del iframe
                if (autoplayActive) {
                    setTimeout(() => {
                        tryPlayIframeVideo(iframe);
                    }, 1000);
                }
            };
            
            window.speechSynthesis.speak(msg);
        } else {
            tryPlayIframeVideo(iframe);
        }
    }
    
    function tryPlayIframeVideo(iframe) {
        console.log('🎥 Intentando reproducir video en iframe...');
        
        try {
            // Intentar acceder al contenido del iframe
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            const video = iframeDoc.querySelector('video');
            
            if (video) {
                console.log('✅ Video encontrado dentro del iframe');
                video.currentTime = 0;
                video.play().then(() => {
                    console.log('▶️ Video reproduciéndose');
                    
                    video.onended = () => {
                        console.log('✅ Video completado');
                        if (autoplayActive) {
                            setTimeout(() => {
                                advanceToNext();
                            }, 1000);
                        }
                    };
                }).catch(err => {
                    console.log('❌ Error al reproducir:', err);
                    // Si no se puede reproducir, avanzar
                    if (autoplayActive) {
                        setTimeout(() => {
                            advanceToNext();
                        }, 2000);
                    }
                });
            } else {
                console.log('⚠️ No se encontró tag <video> dentro del iframe');
                // Si es un archivo de video directo en el iframe, esperar un tiempo estimado
                console.log('⏱️ Esperando 10 segundos (tiempo estimado)...');
                setTimeout(() => {
                    if (autoplayActive) {
                        advanceToNext();
                    }
                }, 10000);
            }
        } catch (err) {
            console.log('⚠️ No se puede acceder al iframe (CORS):', err);
            // Si hay error de CORS, esperar un tiempo estimado
            console.log('⏱️ Esperando 10 segundos (tiempo estimado)...');
            setTimeout(() => {
                if (autoplayActive) {
                    advanceToNext();
                }
            }, 10000);
        }
    }
    
    function handleVideoAutoplay(video, panel) {
        // Primero leer el título y descripción del tema/subtema
        const title = panel.querySelector('h2');
        const description = panel.querySelector('p');
        
        let textToRead = '';
        if (title) textToRead += title.textContent + '. ';
        if (description) textToRead += description.textContent;
        
        textToRead = textToRead.trim();
        
        if (textToRead.length > 10) {
            // Leer el texto primero
            console.log('📝 Leyendo descripción del video...');
            const msg = new SpeechSynthesisUtterance(textToRead);
            msg.lang = 'es-MX';
            msg.rate = 1;
            msg.pitch = 1;
            
            msg.onstart = () => {
                showTurtle();
            };
            
            msg.onend = () => {
                hideTurtle();
                console.log('✅ Descripción completada, reproduciendo video...');
                
                // Después de leer, reproducir el video
                if (autoplayActive) {
                    setTimeout(() => {
                        playVideoAndWait(video);
                    }, 1000);
                }
            };
            
            window.speechSynthesis.speak(msg);
        } else {
            // Si no hay texto, reproducir video directamente
            playVideoAndWait(video);
        }
    }
    
    function playVideoAndWait(video) {
        console.log('🎥 Reproduciendo video...');
        
        // IMPORTANTE: Reiniciar el video al inicio
        video.currentTime = 0;
        console.log('⏪ Video reiniciado al inicio');
        
        // Reproducir video
        video.play().catch(err => {
            console.log('Error al reproducir video:', err);
            // Si no se puede reproducir, avanzar
            if (autoplayActive) {
                advanceToNext();
            }
        });
        
        // Esperar a que termine el video
        video.onended = () => {
            console.log('✅ Video completado');
            
            if (autoplayActive) {
                setTimeout(() => {
                    advanceToNext();
                }, 1000);
            }
        };
    }
    
    function speakText(text) {
        const msg = new SpeechSynthesisUtterance(text);
        msg.lang = 'es-MX';
        msg.rate = 1;
        msg.pitch = 1;
        
        msg.onstart = () => {
            showTurtle();
        };
        
        msg.onend = () => {
            hideTurtle();
            console.log('✅ Lectura completada');
            
            if (autoplayActive) {
                setTimeout(() => {
                    advanceToNext();
                }, 1000);
            }
        };
        
        window.speechSynthesis.speak(msg);
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
            const textToRead = fullText.substring(0, 12000);
            
            const msg = new SpeechSynthesisUtterance(textToRead);
            msg.lang = 'es-MX';
            msg.rate = 1;
            msg.pitch = 1;
            
            msg.onstart = () => {
                showTurtle();
            };
            
            msg.onend = () => {
                hideTurtle();
                console.log('✅ PDF completado');
                
                if (autoplayActive) {
                    setTimeout(() => {
                        advanceToNext();
                    }, 1000);
                }
            };
            
            window.speechSynthesis.speak(msg);
            
        } catch (error) {
            console.error('Error leyendo PDF:', error);
            advanceToNext();
        }
    }
    
    function advanceToNext() {
        if (!autoplayActive) return;
        
        console.log('➡️ Avanzando al siguiente...');
        showIndex(index + 1);
        
        // Esperar un momento y leer el nuevo contenido
        setTimeout(() => {
            if (autoplayActive) {
                startAutoplay();
            }
        }, 500);
    }
    
    function showGameModal() {
        const modal = document.getElementById('gameModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

});
</script>

{{-- ========== FUNCIONES DE LA TORTUGUITA ========== --}}
<script>
// Funciones para mostrar/ocultar la tortuguita
function showTurtle() {
    let turtle = document.getElementById('turtle-mascot');
    
    // Si no existe, crearla
    if (!turtle) {
        turtle = document.createElement('div');
        turtle.id = 'turtle-mascot';
        turtle.className = 'turtle-container';
        turtle.innerHTML = `
            <div class="turtle-wrapper">
                <video autoplay loop muted playsinline class="turtle-video">
                    <source src="{{ asset('videos/tortuguita-hablando.mp4') }}" type="video/mp4">
                </video>
                <button class="turtle-close" onclick="hideTurtle()">✕</button>
            </div>
        `;
        document.body.appendChild(turtle);
        
        // Hacer la tortuguita arrastrable
        makeTurtleDraggable(turtle);
    }
    
    // Mostrar con animación
    turtle.classList.add('active');
    
    // Reproducir el video
    const video = turtle.querySelector('.turtle-video');
    if (video) {
        video.play();
    }
}

function hideTurtle() {
    const turtle = document.getElementById('turtle-mascot');
    if (turtle) {
        turtle.classList.remove('active');
        turtle.classList.remove('dragging');
        
        // IMPORTANTE: Resetear la posición cuando se oculta
        turtle.style.transform = 'translate3d(0px, 0px, 0)';
        
        // Pausar el video
        const video = turtle.querySelector('.turtle-video');
        if (video) {
            video.pause();
        }
    }
}

// Función para hacer la tortuguita arrastrable
function makeTurtleDraggable(element) {
    let isDragging = false;
    let currentX;
    let currentY;
    let initialX;
    let initialY;
    let xOffset = 0;
    let yOffset = 0;

    element.addEventListener('mousedown', dragStart);
    element.addEventListener('touchstart', dragStart);
    
    document.addEventListener('mousemove', drag);
    document.addEventListener('touchmove', drag);
    
    document.addEventListener('mouseup', dragEnd);
    document.addEventListener('touchend', dragEnd);

    function dragStart(e) {
        // No arrastrar si se hace clic en el botón de cerrar
        if (e.target.classList.contains('turtle-close')) {
            return;
        }
        
        if (e.type === 'touchstart') {
            initialX = e.touches[0].clientX - xOffset;
            initialY = e.touches[0].clientY - yOffset;
        } else {
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
        }

        isDragging = true;
        element.classList.add('dragging');
    }

    function drag(e) {
        if (isDragging) {
            e.preventDefault();
            
            if (e.type === 'touchmove') {
                currentX = e.touches[0].clientX - initialX;
                currentY = e.touches[0].clientY - initialY;
            } else {
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
            }

            xOffset = currentX;
            yOffset = currentY;

            setTranslate(currentX, currentY, element);
        }
    }

    function dragEnd(e) {
        initialX = currentX;
        initialY = currentY;

        isDragging = false;
        element.classList.remove('dragging');
    }

    function setTranslate(xPos, yPos, el) {
        el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
    }
}

// También actualizar la función de lectura de PDFs
async function readPdf(url) {
    const speakBtn = document.getElementById('btnSpeak');
    
    try {
        // Cambiar estado del botón inmediatamente
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

        const msg = new SpeechSynthesisUtterance(fullText.substring(0, 12000));
        msg.lang = 'es-MX';
        msg.rate = 1;
        msg.pitch = 1;
        
        // Mostrar tortuga solo cuando REALMENTE empiece
        msg.onstart = () => {
            showTurtle();
        };
        
        msg.onend = () => {
            hideTurtle();
            window.isReading = false;
            if (speakBtn) {
                speakBtn.textContent = '🔊 Leer';
                speakBtn.style.background = '';
            }
        };
        
        window.speechSynthesis.speak(msg);

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
        // Si está leyendo, detener
        if (window.isReading) {
            window.speechSynthesis.cancel();
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

        // Buscar todos los iframes en el panel actual
        const iframes = panel.querySelectorAll('iframe');
        console.log('🔍 Iframes encontrados:', iframes.length);
        
        // Buscar si algún iframe contiene un PDF
        for (let iframe of iframes) {
            console.log('📄 Revisando iframe:', iframe.src);
            if (iframe.src && iframe.src.toLowerCase().includes('.pdf')) {
                console.log('✅ PDF encontrado, leyendo...');
                readPdf(iframe.src);
                return;
            }
        }

        // Si no hay PDF, leer el texto del panel
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
    const msg = new SpeechSynthesisUtterance(text);
    msg.lang = 'es-MX';
    msg.rate = 1;
    msg.pitch = 1;
    
    // Cambiar estado del botón inmediatamente
    window.isReading = true;
    if (speakBtn) {
        speakBtn.textContent = '⏸ Detener';
        speakBtn.style.background = '#ff5252';
    }
    
    // Mostrar tortuga solo cuando REALMENTE empiece
    msg.onstart = () => {
        showTurtle();
    };
    
    msg.onend = () => {
        hideTurtle();
        window.isReading = false;
        if (speakBtn) {
            speakBtn.textContent = '🔊 Leer';
            speakBtn.style.background = '';
        }
    };
    
    window.speechSynthesis.speak(msg);
}

}
</script>

{{-- ========== CUESTIONARIO HANDLER ========== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cuestionario-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const activityId = this.dataset.activityId;
            const formData = new FormData(this);
            const resultDiv = this.parentElement.querySelector('.result-message');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Deshabilitar botón mientras se envía
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';
            
            try {
                const response = await fetch(`/activities/${activityId}/submit`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success" style="font-size: 18px; padding: 20px; margin: 20px 0;">
                            <h3>✅ ${data.message}</h3>
                            <small style="display: block; margin-top: 10px; opacity: 0.8;">
                                La respuesta ha sido guardada correctamente.
                            </small>
                        </div>
                    `;
                    this.querySelectorAll('input').forEach(input => input.disabled = true);
                    submitBtn.textContent = 'Respuesta Enviada';
                    
                    // NO recargamos la página
                    if (data.created) {
                        updateProgressBar();
                    }
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar Respuesta';
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger">❌ Error al enviar la respuesta</div>`;
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Respuesta';
                console.error(error);
            }
        });
    });
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
            // Actualizar contador
            counter.querySelector('.current').textContent = currentQuestion + 1;
            
            // Mostrar/ocultar preguntas
            questions.forEach((q, idx) => {
                q.style.display = idx === currentQuestion ? 'block' : 'none';
            });
            
            // Habilitar/deshabilitar botones
            prevBtn.disabled = currentQuestion === 0;
            
            // Si es la última pregunta, mostrar botón finalizar
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
            // Verificar si la pregunta actual fue respondida
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
            
            // Verificar que todas las preguntas estén respondidas
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
            
            // Deshabilitar botón mientras se envía
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
                    
                    // Deshabilitar todas las opciones del examen
                    this.querySelectorAll('input').forEach(input => input.disabled = true);
                    submitBtn.textContent = 'Examen Enviado';
                    
                    // Ocultar navegación
                    const nav = this.previousElementSibling;
                    if (nav && nav.classList.contains('exam-navigation')) {
                        nav.style.display = 'none';
                    }
                    
                    // Mostrar todas las preguntas con sus respuestas
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

// Función para actualizar la barra de progreso sin recargar
function updateProgressBar() {
    console.log('✅ Progreso actualizado');
}

// Funciones del modal de validación
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

{{-- ========== SOPA DE LETRAS - DEFINIR FUNCIONES PRIMERO ========== --}}
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
    
    // Crear la grilla
    const grid = createEmptyGrid(size);
    placeWords(grid, words, size);
    fillEmptySpaces(grid, size);
    
    // Renderizar
    renderGrid(gridContainer, grid, size, activityId);
    console.log('✅ Sopa de Letras renderizada exitosamente');
}

function createEmptyGrid(size) {
    return Array(size).fill(null).map(() => Array(size).fill(''));
}

function placeWords(grid, words, size) {
    const directions = [
        [0, 1],   // horizontal
        [1, 0],   // vertical
        [1, 1],   // diagonal derecha
        [1, -1]   // diagonal izquierda
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
    
    // NUEVA INTERACTIVIDAD - Selección por clics
    let firstClick = null;
    let selectedCells = [];
    
    container.querySelectorAll('.sopa-cell').forEach(cell => {
        cell.addEventListener('click', () => {
            // Si ya está encontrada, ignorar
            if (cell.classList.contains('found')) {
                return;
            }
            
            // Primer clic - seleccionar inicio
            if (!firstClick) {
                // Limpiar selección anterior
                container.querySelectorAll('.sopa-cell').forEach(c => c.classList.remove('selected', 'selecting'));
                
                firstClick = cell;
                cell.classList.add('selecting');
                selectedCells = [cell];
                console.log('🎯 Primera letra seleccionada:', cell.textContent);
            } 
            // Segundo clic - seleccionar final y verificar
            else {
                const row1 = parseInt(firstClick.dataset.row);
                const col1 = parseInt(firstClick.dataset.col);
                const row2 = parseInt(cell.dataset.row);
                const col2 = parseInt(cell.dataset.col);
                
                // Obtener todas las celdas entre los dos clics
                const path = getCellsBetween(container, row1, col1, row2, col2);
                
                if (path.length > 0) {
                    selectedCells = path;
                    selectedCells.forEach(c => c.classList.add('selected'));
                    
                    // Verificar la palabra
                    setTimeout(() => {
                        checkWord(selectedCells, activityId);
                        firstClick = null;
                        selectedCells = [];
                    }, 300);
                } else {
                    // No es una línea válida, resetear
                    firstClick.classList.remove('selecting');
                    firstClick = null;
                    selectedCells = [];
                }
            }
        });
        
        // Hover para preview
        cell.addEventListener('mouseenter', () => {
            if (firstClick && !cell.classList.contains('found')) {
                // Limpiar preview anterior
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

// Función para obtener todas las celdas entre dos puntos (línea recta)
function getCellsBetween(container, row1, col1, row2, col2) {
    const cells = [];
    
    // Calcular dirección
    const rowDiff = row2 - row1;
    const colDiff = col2 - col1;
    
    // Verificar si es una línea válida (horizontal, vertical o diagonal)
    const isHorizontal = rowDiff === 0;
    const isVertical = colDiff === 0;
    const isDiagonal = Math.abs(rowDiff) === Math.abs(colDiff);
    
    if (!isHorizontal && !isVertical && !isDiagonal) {
        return []; // No es una línea válida
    }
    
    // Calcular pasos
    const steps = Math.max(Math.abs(rowDiff), Math.abs(colDiff));
    const rowStep = steps === 0 ? 0 : rowDiff / steps;
    const colStep = steps === 0 ? 0 : colDiff / steps;
    
    // Recopilar celdas
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
            
            // Efecto de celebración
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
        // Efecto de error
        cells.forEach(c => {
            c.classList.add('wrong');
            setTimeout(() => {
                c.classList.remove('wrong');
            }, 500);
        });
    }
    
    // Limpiar selección
    cells.forEach(c => c.classList.remove('selected', 'selecting', 'preview'));
}

function checkSopaCompletion(activityId) {
    const wordList = document.querySelector(`#word-list-${activityId}`);
    const totalWords = wordList.querySelectorAll('li').length;
    const foundWords = wordList.querySelectorAll('li.found').length;
    
    const resultDiv = document.querySelector(`#grid-${activityId}`).parentElement.querySelector('.result-message');
    const submitBtn = document.querySelector(`#grid-${activityId}`).parentElement.querySelector('.btn-submit');
    
    if (foundWords === totalWords) {
        // Deshabilitar botón mientras se envía
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';
        
        // Enviar completado al servidor
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
                
                // NO recargamos la página
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
    
    // Mostrar pista
    document.getElementById(`hint-${activityId}`).textContent = hint || 'Sin pista';
    document.getElementById(`attempts-${activityId}`).textContent = maxAttempts;
    
    // Crear display de palabra
    updateWordDisplay();
    
    // Crear teclado
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
            
            // Deshabilitar teclado
            keyboard.querySelectorAll('.letter-btn').forEach(btn => btn.disabled = true);
            
            // Enviar al servidor
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
            
            // Deshabilitar teclado
            keyboard.querySelectorAll('.letter-btn').forEach(btn => btn.disabled = true);
            
            // Mostrar palabra completa
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
    
    // Crear grilla y colocar palabras
    const { grid, placements } = createCrucigramaGrid(size, words);
    
    console.log('📊 Grilla creada. Placements:', placements.length);
    
    console.log('🎨 Renderizando grilla...');
    renderCrucigramaGrid(activityId, grid, size, placements);
    
    console.log('📝 Renderizando pistas...');
    renderCrucigramaClues(activityId, words, placements);
    
    console.log('✅ Crucigrama inicializado completamente');
}

function createCrucigramaGrid(size, words) {
    // Inicializar grilla vacía
    const grid = Array(size).fill(null).map(() => 
        Array(size).fill(null).map(() => ({ letter: '', editable: false, wordIndex: -1 }))
    );
    
    const placements = [];
    
    // Colocar palabras de forma más inteligente
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
                
                // Verificar si hay espacio
                let canPlace = true;
                for (let i = 0; i < wordLength; i++) {
                    if (grid[row][col + i].editable && grid[row][col + i].letter !== word[i]) {
                        canPlace = false;
                        break;
                    }
                }
                
                if (canPlace) {
                    // Colocar la palabra
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
            } else { // vertical
                row = Math.floor(Math.random() * (size - wordLength + 1));
                col = Math.floor(Math.random() * size);
                
                // Verificar si hay espacio
                let canPlace = true;
                for (let i = 0; i < wordLength; i++) {
                    if (grid[row + i][col].editable && grid[row + i][col].letter !== word[i]) {
                        canPlace = false;
                        break;
                    }
                }
                
                if (canPlace) {
                    // Colocar la palabra
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
                // Verificar si es el inicio de una palabra para poner número
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
    
    // Agregar eventos a los inputs
    gridContainer.querySelectorAll('.cell-input').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            
            // Auto-avanzar al siguiente input
            if (this.value.length === 1) {
                const row = parseInt(this.dataset.row);
                const col = parseInt(this.dataset.col);
                
                // Buscar el siguiente input
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
                
                // Buscar el input anterior
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
    
    // Limpiar listas
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
    
    // Limpiar clases previas
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
        
        // Enviar al servidor
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

@endsection