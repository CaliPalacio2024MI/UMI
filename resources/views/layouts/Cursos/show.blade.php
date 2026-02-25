@extends('layouts.app')

@section('title', $course->title)

@section('content')

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
            <button id="btnSpeak">🔊 Leer</button>
            <button id="btnDone">✔ Completar</button>
            <button id="btnNext">⏭ Siguiente</button>
            <button id="btnFull">⛶ Pantalla</button>
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
                        <iframe src="{{ asset('storage/'.$topic->file_path) }}"></iframe>
                    </div>
                @endif
            </section>

            @foreach ($topic->subtopics as $subtopic)
                <section class="content-panel" id="content-subtopic-{{ $subtopic->id }}">
                    <h2>{{ $subtopic->title }}</h2>
                    <p>{{ $subtopic->description }}</p>

                    @if ($subtopic->file_path)
                        <div class="file-viewer">
                            <iframe src="{{ asset('storage/'.$subtopic->file_path) }}"></iframe>
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
                                <form class="examen-form" data-activity-id="{{ $activity->id }}">
                                    @csrf
                                    @foreach ($activity->content['questions'] ?? [] as $qIndex => $question)
                                        <div class="question-item">
                                            <h4>{{ $qIndex + 1 }}. {{ $question['question'] }}</h4>
                                            @foreach ($question['options'] ?? [] as $optIndex => $option)
                                                <label class="option-label">
                                                    <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                    <button type="submit" class="btn-submit">Enviar Examen</button>
                                </form>
                                <div class="result-message"></div>
                            </div>
                        @endif

                        {{-- SOPA DE LETRAS --}}
                        @if ($activity->type === 'SopaDeLetras')
                            <div class="game-container sopa-container">
                                <div class="words-to-find">
                                    <h4>Palabras a encontrar:</h4>
                                    <ul id="word-list-{{ $activity->id }}">
                                        @foreach ($activity->content['words'] ?? [] as $word)
                                            <li data-word="{{ strtoupper(trim($word)) }}">{{ $word }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="grid-container" id="grid-{{ $activity->id }}" 
                                     data-activity-id="{{ $activity->id }}"
                                     data-words='@json(array_map(fn($w) => strtoupper(trim($w)), $activity->content['words'] ?? []))'
                                     data-size="{{ $activity->content['grid_size'] ?? 10 }}">
                                    <!-- La grilla se generará aquí con JavaScript -->
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
                                <iframe src="{{ asset('storage/'.$activity->file_path) }}"></iframe>
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
                            <form class="examen-form" data-activity-id="{{ $activity->id }}">
                                @csrf
                                @foreach ($activity->content['questions'] ?? [] as $qIndex => $question)
                                    <div class="question-item">
                                        <h4>{{ $qIndex + 1 }}. {{ $question['question'] }}</h4>
                                        @foreach ($question['options'] ?? [] as $optIndex => $option)
                                            <label class="option-label">
                                                <input type="radio" name="question_{{ $qIndex }}" value="{{ $optIndex }}" required>
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endforeach
                                <button type="submit" class="btn-submit">Enviar Examen</button>
                            </form>
                            <div class="result-message"></div>
                        </div>
                    @endif

                    {{-- SOPA DE LETRAS --}}
                    @if ($activity->type === 'SopaDeLetras')
                        <div class="game-container sopa-container">
                            <div class="words-to-find">
                                <h4>Palabras a encontrar:</h4>
                                <ul id="word-list-{{ $activity->id }}">
                                    @foreach ($activity->content['words'] ?? [] as $word)
                                        <li data-word="{{ strtoupper(trim($word)) }}">{{ $word }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="grid-container" id="grid-{{ $activity->id }}" 
                                 data-activity-id="{{ $activity->id }}"
                                 data-words='@json(array_map(fn($w) => strtoupper(trim($w)), $activity->content['words'] ?? []))'
                                 data-size="{{ $activity->content['grid_size'] ?? 10 }}">
                                <!-- La grilla se generará aquí con JavaScript -->
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
                            <iframe src="{{ asset('storage/'.$activity->file_path) }}"></iframe>
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
document.addEventListener('DOMContentLoaded', function () {

    const links = [...document.querySelectorAll('.syllabus-link')];
    const panels = document.querySelectorAll('.content-panel');
    const bar = document.querySelector('.course-progress-bar');

    let index = 0;

    panels.forEach(p => p.style.display = 'none');
    showIndex(0);
    
    // Inicializar todas las sopas de letras al cargar
    setTimeout(() => {
        document.querySelectorAll('.grid-container').forEach(grid => {
            if(!grid.innerHTML) {
                initSopaDeLetras(grid);
            }
        });
        
        // Inicializar Ahorcado
        document.querySelectorAll('.ahorcado-game').forEach(game => {
            if(!game.querySelector('.letter-btn')) {
                initAhorcado(game);
            }
        });
        
        // Inicializar Crucigrama
        document.querySelectorAll('.crucigrama-game').forEach(game => {
            const grid = game.querySelector('.crucigrama-grid');
            if(grid && !grid.innerHTML) {
                initCrucigrama(game);
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
                if(sopaGrid && !sopaGrid.innerHTML) {
                    initSopaDeLetras(sopaGrid);
                }
                
                // Inicializar Ahorcado si existe
                const ahorcadoGame = panel.querySelector('.ahorcado-game');
                if(ahorcadoGame && !ahorcadoGame.querySelector('.letter-btn')) {
                    initAhorcado(ahorcadoGame);
                }
                
                // Inicializar Crucigrama si existe
                const crucigramaGame = panel.querySelector('.crucigrama-game');
                if(crucigramaGame) {
                    const grid = crucigramaGame.querySelector('.crucigrama-grid');
                    if(grid && !grid.innerHTML) {
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

    document.getElementById('btnDone').onclick = ()=>{
        links[index].classList.add('done');
        showIndex(index+1);
    };

    document.getElementById('btnFull').onclick = ()=>{
        document.querySelector('.course-viewer').requestFullscreen();
    };

    // ====== VOICE ======
    document.getElementById('btnSpeak').onclick = ()=>{
        window.speechSynthesis.cancel();

        const panel = document.querySelector('.content-panel:not([style*="display: none"])');
        if(!panel) return;

        const text = panel.innerText.replace(/\s+/g,' ').trim();
        if(!text) return;

        const msg = new SpeechSynthesisUtterance(text);
        msg.lang = 'es-MX';
        msg.rate = 1;
        msg.pitch = 1;

        window.speechSynthesis.speak(msg);
    };

});

document.addEventListener('DOMContentLoaded', () => {

    const btn = document.getElementById('btnSpeak');
    if(!btn) return;

    btn.addEventListener('click', async () => {
        window.speechSynthesis.cancel();

        const panel = document.querySelector('.content-panel:not([style*="display: none"])');
        if (!panel) {
            alert('No hay contenido visible');
            return;
        }

        const iframe = panel.querySelector('iframe');

        if (iframe && iframe.src.toLowerCase().includes('.pdf')) {
            readPdf(iframe.src);
            return;
        }

        let text = panel.innerText.replace(/\s+/g, ' ').trim();

        if (!text) {
            alert('No hay texto para leer');
            return;
        }

        speak(text);
    });

});

function speak(text) {
    const msg = new SpeechSynthesisUtterance(text);
    msg.lang = 'es-MX';
    msg.rate = 1;
    msg.pitch = 1;
    window.speechSynthesis.speak(msg);
}

async function readPdf(url) {
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

        speak(fullText.substring(0, 12000));

    } catch (error) {
        console.error(error);
        speak('No se pudo leer el PDF.');
    }
}

{{-- ========== CUESTIONARIO HANDLER ========== --}}
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

{{-- ========== EXAMEN HANDLER ========== --}}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.examen-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const activityId = this.dataset.activityId;
            const resultDiv = this.parentElement.querySelector('.result-message');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Recopilar respuestas
            const answers = [];
            let questionIndex = 0;
            
            while (true) {
                const radio = this.querySelector(`input[name="question_${questionIndex}"]:checked`);
                if (!radio) break;
                
                answers.push({
                    q: questionIndex,
                    a: radio.value
                });
                questionIndex++;
            }
            
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
                        <div class="alert alert-success" style="font-size: 18px; padding: 20px; margin: 20px 0;">
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
                    
                    // NO recargamos la página para que el usuario vea su calificación
                    // Si necesitas actualizar la barra de progreso, hazlo manualmente:
                    if (data.created) {
                        // Actualizar solo la barra de progreso sin recargar
                        updateProgressBar();
                    }
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar Examen';
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger">❌ Error al enviar el examen</div>`;
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Examen';
                console.error(error);
            }
        });
    });
});

// Función para actualizar la barra de progreso sin recargar
function updateProgressBar() {
    // Puedes implementar una llamada AJAX aquí para obtener el nuevo progreso
    // Por ahora solo mostramos un mensaje
    console.log('✅ Progreso actualizado');
}

{{-- ========== SOPA DE LETRAS - DEFINIR FUNCIONES PRIMERO ========== --}}
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

    let firstCell = null;

    container.querySelectorAll('.sopa-cell').forEach(cell => {
        cell.addEventListener('click', () => {
            const row = parseInt(cell.dataset.row);
            const col = parseInt(cell.dataset.col);

            if (!firstCell) {
                // PRIMER CLIC: Marcar inicio
                firstCell = { row, col, element: cell };
                cell.classList.add('selected');
            } else {
                // SEGUNDO CLIC: Intentar formar la palabra
                const secondCell = { row, col, element: cell };
                
                // Obtenemos todas las celdas en la línea entre el primer y segundo clic
                const path = getPath(firstCell, secondCell);
                
                if (path.length > 0) {
                    const selectedElements = path.map(p => 
                        container.querySelector(`[data-row="${p.row}"][data-col="${p.col}"]`)
                    );
                    
                    // Verificamos si la palabra es correcta
                    checkWord(selectedElements, activityId);
                }

                // Limpiar selección visual del primer clic
                firstCell.element.classList.remove('selected');
                firstCell = null;
            }
        });
    });
}

// Función auxiliar para calcular la línea entre dos puntos (soporta diagonales)
function getPath(start, end) {
    const rowDiff = end.row - start.row;
    const colDiff = end.col - start.col;
    
    // Calcular dirección (-1, 0, o 1)
    const rowStep = rowDiff === 0 ? 0 : rowDiff / Math.abs(rowDiff);
    const colStep = colDiff === 0 ? 0 : colDiff / Math.abs(colDiff);
    
    // Validar que sea una línea recta o diagonal perfecta (45 grados)
    if (rowDiff !== 0 && colDiff !== 0 && Math.abs(rowDiff) !== Math.abs(colDiff)) {
        return []; // No es una línea válida para sopa de letras
    }

    const path = [];
    let currentRow = start.row;
    let currentCol = start.col;
    const steps = Math.max(Math.abs(rowDiff), Math.abs(colDiff));

    for (let i = 0; i <= steps; i++) {
        path.push({ row: currentRow, col: currentCol });
        currentRow += rowStep;
        currentCol += colStep;
    }
    
    return path;
}

function checkWord(cells, activityId) {
    const word = cells.map(c => c.textContent).join('');
    const wordList = document.querySelector(`#word-list-${activityId}`);
    
    wordList.querySelectorAll('li').forEach(li => {
        const targetWord = li.dataset.word;
        if (word === targetWord || word.split('').reverse().join('') === targetWord) {
            cells.forEach(c => c.classList.add('found'));
            li.classList.add('found');
        }
    });
    
    cells.forEach(c => c.classList.remove('selected'));
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
// Esperar a que el navegador cargue todo el HTML
document.addEventListener('DOMContentLoaded', () => {
    // Buscar todos los contenedores de sopa de letras en la página
    const containers = document.querySelectorAll('.grid-container');
    
    containers.forEach(container => {
        // Ejecutar la función inicializadora para cada uno
        initSopaDeLetras(container);
    });
});

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

/**
 * INICIALIZACIÓN AUTOMÁTICA
 */
window.onload = function() {
    console.log('🚀 Iniciando sistema de crucigramas...');
    const games = document.querySelectorAll('.crucigrama-game');
    games.forEach(game => initCrucigrama(game));
};

function initCrucigrama(gameContainer) {
    const activityId = gameContainer.dataset.activityId;
    const wordsData = gameContainer.dataset.words;
    const size = parseInt(gameContainer.dataset.size);
    
    if (!wordsData) {
        console.error('❌ No hay palabras definidas');
        return;
    }
    
    let words;
    try {
        const parsedData = JSON.parse(wordsData);
        // SOLUCIÓN AL ERROR: Convertir objeto a array si es necesario
        words = Array.isArray(parsedData) ? parsedData : Object.values(parsedData);
        console.log('✅ Palabras cargadas para actividad ' + activityId, words);
    } catch(e) {
        console.error('❌ Error al parsear palabras:', e);
        return;
    }
    
    if (!words || words.length === 0) return;
    
    // Crear grilla y colocar palabras
    const { grid, placements } = createCrucigramaGrid(size, words);
    renderCrucigramaGrid(activityId, grid, size, placements);
    renderCrucigramaClues(activityId, words, placements);
}

function createCrucigramaGrid(size, words) {
    // Inicializar grilla vacía
    const grid = Array(size).fill(null).map(() => 
        Array(size).fill(null).map(() => ({ letter: '', editable: false, wordIndex: -1 }))
    );
    
    const placements = [];
    
    words.forEach((wordData, index) => {
        if (!wordData.word) return; // Saltar si no hay palabra

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
                }
            } else { // vertical
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
                }
            }
            attempts++;
        }
    });
    
    return { grid, placements };
}

function renderCrucigramaGrid(activityId, grid, size, placements) {
    const gridContainer = document.getElementById(`crucigrama-grid-${activityId}`);
    if (!gridContainer) return;
    
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
                    </td>`;
            } else {
                html += '<td class="crossword-cell blocked"></td>';
            }
        }
        html += '</tr>';
    }
    html += '</table>';
    
    gridContainer.innerHTML = html;
    
    // Eventos de movimiento de foco
    gridContainer.querySelectorAll('.cell-input').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            if (this.value.length === 1) {
                const r = parseInt(this.dataset.row);
                const c = parseInt(this.dataset.col);
                // Intenta buscar siguiente a la derecha, si no, abajo
                const next = gridContainer.querySelector(`.cell-input[data-row="${r}"][data-col="${c + 1}"]`) || 
                             gridContainer.querySelector(`.cell-input[data-row="${r + 1}"][data-col="${c}"]`);
                if (next) next.focus();
            }
        });
    });
}

function renderCrucigramaClues(activityId, words, placements) {
    const hList = document.getElementById(`clues-horizontal-${activityId}`);
    const vList = document.getElementById(`clues-vertical-${activityId}`);
    if (!hList || !vList) return;
    
    hList.innerHTML = '';
    vList.innerHTML = '';
    
    placements.forEach((p) => {
        const li = document.createElement('li');
        li.innerHTML = `<strong>${p.index + 1}.</strong> ${p.clue} <small>(${p.word.length} letras)</small>`;
        if (p.direction === 'horizontal') hList.appendChild(li);
        else vList.appendChild(li);
    });
}

function checkCrucigramaCompletion(activityId) {
    const gridElem = document.getElementById(`crucigrama-grid-${activityId}`);
    const cells = gridElem.querySelectorAll('.crossword-cell.editable');
    const resultDiv = gridElem.closest('.crucigrama-game').querySelector('.result-message');
    
    let correctCount = 0;
    cells.forEach(cell => {
        const input = cell.querySelector('.cell-input');
        const answer = cell.dataset.answer;
        cell.classList.remove('correct', 'incorrect');
        
        if (input.value.toUpperCase() === answer) {
            correctCount++;
            cell.classList.add('correct');
        } else if (input.value !== '') {
            cell.classList.add('incorrect');
        }
    });

    if (correctCount === cells.length) {
        resultDiv.innerHTML = '<div style="color:green; font-weight:bold; margin-top:10px;">¡Excelente! Todo correcto.</div>';
    } else {
        resultDiv.innerHTML = `<div style="color:orange; margin-top:10px;">Llevas ${correctCount} de ${cells.length} correctas.</div>`;
    }
}

</script>


@endsection