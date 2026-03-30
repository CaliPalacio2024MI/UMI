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
            <button onclick="window.location.href='{{ route('courses.create') }}'" class="btn-create">
               + Crear Curso
            </button>
        @endif
    </div>

    <!-- Grid de cursos -->
    <div class="courses-container">
        @forelse ($course as $courses)
            <div class="course-card {{ !$courses->active ? 'course-disabled' : '' }}">
                <a href="{{ route('course.show', $courses) }}" class="course-card-show">
                    <img src="{{ asset('storage/' . $courses->image) }}" alt="Imagen del curso">
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
                </a>

                    <div class="btn-display">

                       {{-- Gestionar Horarios --}}
                        @if(in_array($courses->modality, ['presencial', 'hibrida']))
                            <a href="{{ route('courses.sessions.index', $courses) }}" class="btn-action">
                                <i class="fa-regular fa-clock"></i>
                            </a>
                        @endif
                        {{-- EDITAR --}}
                        @can('update', $courses)
                            <a href="{{ route('courses.edit', $courses) }}" class="btn-edit">
                                <img src="{{asset('images/icons/pen-to-square-solid-full.svg')}}"
                                alt=""
                                style="width:27px;height:27px"
                                loading="lazy">
                            </a>
                        @endcan
                        {{--ELIMINAR --}}
                        @can('delete', $courses)
                            <form action="{{ route('courses.destroy', $courses) }}" method="POST">
                               @csrf
                               @method('DELETE')

                                <button type="submit"
                                    class="btn-delete"
                                    onclick="return confirm('¿Eliminar el Curso? Esto no se puede deshacer.')">
                                    <img src="{{asset('images/icons/Vector.svg')}}"
                                    alt=""
                                    style="width:27px;height:19px"
                                    loading="lazy">
                                </button>
                            </form>
                        @endcan

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
@endsection
