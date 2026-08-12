@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/Control Admin/base.css','resources/js/app.js'])

@section('content')
<div class ="container">
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1rem; padding: 10px 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24;">{{ session('error') }}</div>
    @endif
    <!-- Header -->
    <div class ="content-header">
        <div class="content-title">
            <h5>Clasificación</h5>
        </div>
        <div class="option-carrer">
            @if(Auth::user()->hasAnyRole(['master']))
                <button type="button" id="openCreateClasificacionBtn" class="btn-primary">+ Agregar clasificación</button>
            @endif
            {{-- Botón ver estado Landing --}}
            <label class="landing-switch-control" for="landingViewToggle">
                <span id="landingToggleText">Ver estado Landing</span>
                <span class="switch">
                    <input id="landingViewToggle" type="checkbox" aria-label="Ver estado Landing" onchange="toggleVistaLanding(this)">
                    <span class="slider"></span>
                </span>
            </label>
        </div>

        @include('layouts.ControlAdmin.Carreras.classification_modal')
    </div>

    <script>
        function toggleVistaLanding(checkbox) {
            const badges = document.querySelectorAll('.landing-badge');
            const label = document.getElementById('landingToggleText');
            const show = checkbox ? checkbox.checked : false;

            badges.forEach(badge => {
                badge.style.display = show ? 'flex' : 'none';
            });

            if (label) {
                label.textContent = show ? 'Ocultar estado Landing' : 'Ver estado Landing';
            }
        }
    </script>

    <!-- Grid de Clasificaciones -->
    <div class="carrers-container">
       @forelse ($careerClassifications as $clasificacion)
            <div class="carrer-card" data-careers-url="{{ route('control.careers.byClassification', $clasificacion->id) }}" role="button" tabindex="0">
                <!-- Header -->
                <div class="card-header">
                    <h4>{{ $clasificacion->name }}</h4>
                </div>

                <!-- Cuerpo -->
                <div class="card-body">
                    <!-- Contador -->
                    <div class="stat-row">
                        <div class="stat">
                            <div class="stat-number">{{ $clasificacion->careers_count }}</div>
                            <div class="stat-label">Carreras</div>
                        </div>
                    </div>

                    <!-- Carreras -->
                    @if($clasificacion->careers->isNotEmpty())
                        <div class="career-list">
                            <div class="career-list-title">Carreras</div>
                            <div class="career-items">
                                @foreach($clasificacion->careers as $career)
                                    <div class="career-item">
                                        <span class="career-name">{{ $career->name }}</span>
                                        <div class="career-pills">
                                            <span class="career-pill career-pill--mat">{{ $career->materias_count }} mat</span>
                                            <span class="career-pill career-pill--sem">{{ $career->semesters ?? '—' }} sem</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Landing -->
                    <div class="landing-badge" style="display: none; align-items: center; gap: 8px;">
                        @if(Auth::user()->hasAnyRole(['master']))
                            <form method="post" action="{{ route('control.careers.classifications.toggle', $clasificacion->id) }}" style="display:inline;">
                                @csrf
                                <label class="switch" title="{{ $clasificacion->visible_landing ? 'Visible en landing' : 'Oculto en landing' }}">
                                    <input type="checkbox" {{ $clasificacion->visible_landing ? 'checked' : '' }} onchange="this.closest('form').submit()">
                                    <span class="slider"></span>
                                </label>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        @empty
            <p class="carrers-empty" role="status">Aún no hay clasificaciones. Crea una con el botón "+ Agregar clasificación".</p>
        @endforelse
    </div>
</div>

{{-- Script JS --}}
@push('scripts')
    <script>
        // Reabrir modal de clasificación si hubo error de validación
        (function () {
            const classificationModal = document.getElementById('createClassificationModal');
            const hasClassificationErrors = @json($errors->has('classification_name'));
            if (classificationModal && hasClassificationErrors) {
                classificationModal.style.display = 'flex';
            }
        })();

        // Clic en tarjeta de clasificación: ver sus carreras (no si es el toggle/form)
        document.querySelectorAll('.carrer-card[data-careers-url]').forEach(function (card) {
            card.addEventListener('click', function (e) {
                if (e.target.closest('form')) return;
                const url = card.getAttribute('data-careers-url');
                if (url) window.location.href = url;
            });
        });
    </script>
@endpush
@endsection