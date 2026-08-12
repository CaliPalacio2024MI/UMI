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
        <div class="content-title" style="display:flex; align-items:center; gap:10px;">
           <a href="{{ route('control.careers.index') }}" title="Volver a clasificaciones" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; background:#f1f5f9; transition: background 0.2s;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 18L9 12L15 6" stroke="#2f4b7c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <h5>{{ $careerClassification->name }}</h5>
        </div>
        <div class="option-carrer">
            @if(Auth::user()->hasAnyRole(['master']))
                <button type="button" id="openCreateCareerBtn">+ Agregar carrera</button>
            @endif
           {{-- Botón ver estado Landing --}}
            <label class="landing-switch-control" for="landingViewToggle">
                <span id="landingToggleText">Ver estado Landing</span>
                <span class="switch">
                    <input id="landingViewToggle" type="checkbox" onchange="toggleVistaLanding(this)">
                    <span class="slider"></span>
                </span>
            </label>
        </div>

        @include('layouts.ControlAdmin.Carreras.create', ['lockedClassification' => $careerClassification])
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

    <!-- Grid de Carreras -->
    <div class="carrers-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        @forelse ($careers as $carrera)
            <div class="carrer-card" data-reticula-url="{{ route('control.careers.reticula', $carrera->id) }}" role="button" tabindex="0">
                <!-- Header -->
                <div class="card-header">
                    <h4 class="card-title">{{ $carrera->name }}</h4>
                    @if($carrera->official_id)
                        <span class="header-badge">{{ str_replace('-', '/', $carrera->official_id) }}</span>
                    @endif
                </div>

                <!-- Cuerpo -->
                <div class="card-body">
                    <!-- Contadores -->
                    <div class="info-grid">
                        <div class="info-card info-card--mat">
                            <div class="info-number">{{ $carrera->materias_count ?? $carrera->materias()->count() }}</div>
                            <div class="info-label">Materias</div>
                        </div>
                        <div class="info-card info-card--sem">
                            <div class="info-number">{{ $carrera->semestres ?? '—' }}</div>
                            <div class="info-label">Semestres</div>
                        </div>
                    </div>

                    </div>

                <!-- Acciones -->
                @if(Auth::user()->hasAnyRole(['master']))
                    <div class="carrer-btn-section" style="display:flex; align-items:center; gap:10px;">
                        <button type="button" class="btn-view" data-view-career-id="{{ $carrera->id }}" title="Ver información">
                            <img src="{{ asset('images/icons/eye-solid-full-gold.svg') }}" alt="Ver" width="20" height="20">
                        </button>
                        <button type="button" id="openEditModalBtn_{{ $carrera->id }}" class="btn-edit" data-career-id="{{ $carrera->id }}" title="Editar carrera">
                            <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar" width="20" height="20">
                        </button>
                        <form action="{{ route('control.careers.destroy', $carrera->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta carrera?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn-delete" title="Eliminar carrera">
                                <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar" width="20" height="20">
                            </button>
                        </form>
                        <div class="landing-badge" style="display: none; align-items: center; gap: 8px;">
                            <form method="post" action="{{ route('control.careers.toggle', $carrera->id) }}" style="display:inline;">
                                @csrf
                                <label class="switch" title="{{ $carrera->visible_landing ? 'Visible en landing' : 'Oculto en landing' }}">
                                    <input type="checkbox" {{ $carrera->visible_landing ? 'checked' : '' }} onchange="this.closest('form').submit()">
                                    <span class="slider"></span>
                                </label>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <p class="carrers-empty" role="status">No hay carreras en esta clasificación.</p>
        @endforelse
    </div>

    {{-- Modales fuera del grid para evitar conflictos de stacking context --}}
    @foreach ($careers as $carrera)
        @include('layouts.ControlAdmin.Carreras.show', ['career' => $carrera])
        @include('layouts.ControlAdmin.Carreras.edit', ['career' => $carrera])
    @endforeach
    </div>

{{-- Script JS --}}
@push('scripts')
    <script>
        function careerRenderMonthlyPriceInputs(form) {
            if (!form) return;
            var sem = form.querySelector('.js-career-semestres');
            var mode = form.querySelector('.js-career-pricing-mode');
            var container = form.querySelector('.js-career-months-fields');
            if (!sem || !mode || !container) return;

            var totalMonths = parseInt(sem.value, 10) || 1;
            totalMonths = Math.max(1, totalMonths);
            var oldData = {};
            try {
                oldData = JSON.parse(container.getAttribute('data-old-monthly') || '{}') || {};
            } catch (e) {
                oldData = {};
            }
            var previousValues = {};
            container.querySelectorAll('input.js-career-month-price').forEach(function(input) {
                var month = input.getAttribute('data-month');
                if (month) previousValues[month] = input.value;
            });
            container.innerHTML = '';
            for (var month = 1; month <= totalMonths; month++) {
                var row = document.createElement('div');
                row.style.display = 'flex';
                row.style.alignItems = 'center';
                row.style.gap = '8px';
                row.style.marginBottom = '6px';

                var label = document.createElement('label');
                label.textContent = 'Mes ' + month + ':';
                label.style.minWidth = '60px';

                var input = document.createElement('input');
                input.type = 'number';
                input.step = '0.01';
                input.min = '0';
                input.placeholder = '0.00';
                input.name = 'monthly_prices[' + month + ']';
                input.setAttribute('data-month', String(month));
                input.className = 'js-career-month-price';
                input.style.width = '100%';
                input.value = previousValues[String(month)] || oldData[String(month)] || '';

                row.appendChild(label);
                row.appendChild(input);
                container.appendChild(row);
            }
            container.setAttribute('data-old-monthly', '{}');
        }

        function careerTogglePricingSections(form) {
            if (!form) return;
            var mode = form.querySelector('.js-career-pricing-mode');
            var uniformWrap = form.querySelector('.js-career-uniform-wrap');
            var monthsWrap = form.querySelector('.js-career-months-wrap');
            if (!mode || !uniformWrap || !monthsWrap) return;
            var perMonth = mode.value === 'per_month';
            uniformWrap.style.display = perMonth ? 'none' : '';
            monthsWrap.style.display = perMonth ? '' : 'none';
            if (perMonth) careerRenderMonthlyPriceInputs(form);
        }

        function careerUpdateCargoMonetario(form) {
            if (!form) return;
            var monto = form.querySelector('.js-career-monto');
            var sem = form.querySelector('.js-career-semestres');
            var mode = form.querySelector('.js-career-pricing-mode');
            var pct = form.querySelector('.js-career-porcentaje');
            var out = form.querySelector('.js-career-cargo-out');
            if (!sem || !out) return;
            var s = parseInt(sem.value, 10) || 0;
            var p = pct ? parseFloat(pct.value) : 0;
            p = isNaN(p) || p < 0 ? 0 : p;
            if (mode && mode.value === 'per_month') {
                var total = 0;
                var hasValue = false;
                form.querySelectorAll('.js-career-month-price').forEach(function(input) {
                    var n = parseFloat(input.value);
                    if (!isNaN(n) && n >= 0) {
                        total += n;
                        hasValue = true;
                    }
                });
                out.value = (hasValue && s > 0) ? (total * (p / 100)).toFixed(2) : '';
                return;
            }
            if (!monto) return;
            var m = parseFloat(monto.value);
            out.value = (isNaN(m) || s < 1) ? '' : ((m * s) * (p / 100)).toFixed(2);
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList && e.target.classList.contains('js-career-monto')) {
                careerUpdateCargoMonetario(e.target.closest('form'));
            }
            if (e.target.classList && e.target.classList.contains('js-career-porcentaje')) {
                careerUpdateCargoMonetario(e.target.closest('form'));
            }
        });
        document.addEventListener('change', function(e) {
            if (e.target.classList && e.target.classList.contains('js-career-semestres')) {
                careerRenderMonthlyPriceInputs(e.target.closest('form'));
                careerUpdateCargoMonetario(e.target.closest('form'));
            }
            if (e.target.classList && e.target.classList.contains('js-career-pricing-mode')) {
                var form = e.target.closest('form');
                careerTogglePricingSections(form);
                careerUpdateCargoMonetario(form);
            }
        });
        document.addEventListener('input', function(e) {
            if (e.target.classList && e.target.classList.contains('js-career-month-price')) {
                careerUpdateCargoMonetario(e.target.closest('form'));
            }
        });

        function careerInitPricingForms() {
            document.querySelectorAll('form .js-career-monto').forEach(function(el) {
                var form = el.closest('form');
                careerTogglePricingSections(form);
                careerUpdateCargoMonetario(form);
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', careerInitPricingForms);
        } else {
            careerInitPricingForms();
        }

        document.addEventListener('DOMContentLoaded', function() {

            // --- 1. LÓGICA DEL MODAL DE CREACIÓN (Singular) ---
            const createModal = document.getElementById('createCareerModal');
            const openCreateBtn = document.getElementById('openCreateCareerBtn');

            if (openCreateBtn) {
                openCreateBtn.addEventListener('click', () => {
                    if (createModal) {
                        createModal.style.display = 'flex';
                    }
                });
            }

            const hasCreateErrors = @json($errors->hasAny() && old('name') && !request()->routeIs('careers.update'));

            if (createModal && hasCreateErrors) {
                 createModal.style.display = 'flex';
            }

           // --- 2. LÓGICA MODAL VISUALIZAR (Ver información) ---
            document.querySelectorAll('.btn-view[data-view-career-id]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const id = this.getAttribute('data-view-career-id');
                    const modal = document.getElementById('viewCareerModal_' + id);
                    if (modal) modal.style.display = 'flex';
                });
            });

            // --- Clic en tarjeta: ir a Reticula escolar (no si se hace clic en botones/acciones) ---
            document.querySelectorAll('.carrer-card[data-reticula-url]').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('form') || e.target.closest('button') || e.target.closest('svg') || e.target.closest('.carrer-actions')) return;
                    const url = this.getAttribute('data-reticula-url');
                    if (url) window.location.href = url;
                });
            });

           // --- 3. LÓGICA DEL MODAL DE EDICIÓN (solo botones con data-career-id) ---
            document.querySelectorAll('.btn-edit[data-career-id]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const careerId = this.getAttribute('data-career-id');
                    const modal = document.getElementById('editCareerModal_' + careerId);
                    if (modal) modal.style.display = 'flex';
                });
            });

            document.querySelectorAll('.modal-overlay').forEach(modal => {
                const errorElement = modal.querySelector('.alert-danger');
                if (errorElement && modal.id.startsWith('editCareerModal_')) {
                     modal.style.display = 'flex';
                }
            });

            // --- 4. LÓGICA UNIFICADA DE CIERRE (para todos los modales) ---
            document.querySelectorAll('.close-custom, .btn-secondary, .btn-close-view').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const modal = btn.closest('.modal-overlay');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay').forEach(modal => {
                         if (modal.style.display === 'flex') {
                             modal.style.display = 'none';
                         }
                    });
                }
            });

        });
    </script>
@endpush
@endsection