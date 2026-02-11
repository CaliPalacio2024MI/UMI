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
                    <div class="syllabus-link act" data-target="#content-activity-{{ $activity->id }}">
                        ▶ {{ $activity->title }}
                    </div>
                @endforeach
            @endforeach

            @foreach ($topic->activities as $activity)
                <div class="syllabus-link act" data-target="#content-activity-{{ $activity->id }}">
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
                    <section class="content-panel" id="content-activity-{{ $activity->id }}">
                        <h2>{{ $activity->title }}</h2>
                        <p>{{ $activity->description }}</p>

                        @if ($activity->file_path)
                            <div class="file-viewer">
                                <iframe src="{{ asset('storage/'.$activity->file_path) }}"></iframe>
                            </div>
                        @endif
                    </section>
                @endforeach
            @endforeach

            @foreach ($topic->activities as $activity)
                <section class="content-panel" id="content-activity-{{ $activity->id }}">
                    <h2>{{ $activity->title }}</h2>
                    <p>{{ $activity->description }}</p>

                    @if ($activity->file_path)
                        <div class="file-viewer">
                            <iframe src="{{ asset('storage/'.$activity->file_path) }}"></iframe>
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
</script>

<script>
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
</script>


@endsection
