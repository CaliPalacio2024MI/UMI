@extends('layouts.app')

@section('title', 'Cursos - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/js/app.js'])



@section('content')
<div class="courses-wrapper">
    <!-- Header -->
    <div class="courses-header">
        <div>
            <h1 class="courses-title">Cursos Disponibles</h1>
            <p class="courses-subtitle">
                @if(Auth::user()->hasAnyRole(['master', 'docente']))
                    Gestiona y crea cursos para los estudiantes
                @else
                    Explora y inscríbete a los cursos disponibles
                @endif
            </p>
        </div>
        
        @if(Auth::user()->hasAnyRole(['master', 'docente', 'gerente_capacitacion']))
            <button onclick="window.navigateTo('{{ route('courses.create') }}')" class="btn-create">
                + Crear Curso
            </button>
        @endif
    </div>

    <!-- Grid de cursos -->
    <div class="courses-container">
        @forelse ($course as $courses)
            <div class="course-card">

    <a href="{{ route('course.show', $courses) }}" class="course-card-show">
        <img src="{{ asset('storage/' . $courses->image) }}" alt="Imagen del curso">

        <div class="course-overlay">
            <span>Ver curso</span>
        </div>

        <div class="course-info">
            <h3 class="course-title">{{ $courses->title }}</h3>
            <p class="course-description">{{ $courses->description }}</p>

            <div class="course-meta">
                @if (session('active_institution_name') == 'Universidad Mundo Imperial')
                    <span>Créditos: {{ $courses->credits }}</span>
                    <span>Horas: {{ $courses->hours }}</span>
                @else
                    <span>Horas: {{ $courses->hours }}</span>
                @endif
            </div>
        </div>
    </a>

    <div class="btn-display">
        @can('update', $courses)
            <a href="{{ route('courses.edit', $courses) }}" class="btn-edit">
                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}"
                     style="width:27px;height:27px">
            </a>
        @endcan

        @can('delete', $courses)
            <form action="{{ route('courses.destroy', $courses) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete">
                    <img src="{{ asset('images/icons/Vector.svg') }}"
                         style="width:38px;height:25px">
                </button>
            </form>
        @endcan
    </div>

</div>

        @empty
            <div class="no-courses-message">
                <p>Aún no hay cursos disponibles. ¡Vuelve pronto!</p>
            </div>
        @endforelse
    </div>
</div>


<script>
// Inscripción a cursos
function enrollInCourse(courseId) {
    if (!confirm('¿Estás seguro de que quieres inscribirte a este curso?')) {
        return;
    }
    
    fetch(`/courses/${courseId}/enroll`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al inscribirse al curso');
    });
}

// Desinscripción de cursos
function unenrollFromCourse(courseId) {
    if (!confirm('¿Estás seguro de que quieres desinscribirte de este curso?')) {
        return;
    }
    
    fetch(`/courses/${courseId}/unenroll`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al desinscribirse del curso');
    });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.course-card');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: .2 });

    cards.forEach(card => observer.observe(card));
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.course-card').forEach((card, i) => {
        card.style.opacity = 0;
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all .4s ease';
            card.style.opacity = 1;
            card.style.transform = 'translateY(0)';
        }, i * 80);
    });
});
</script>


@endsection