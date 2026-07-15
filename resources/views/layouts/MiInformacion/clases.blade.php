@extends('layouts.app')

@section('title', 'Mis Clases - ' . session('active_institution_name'))

{{-- Inyectamos el CSS específico --}}
@push('styles')
    @vite(['resources/css/Mi_Informacion/clases.css', 'resources/css/Mi_Informacion/contenido_materia.css'])
@endpush

@section('content')
@php
    $esDocente = $user->hasActiveRole('docente');
@endphp
<div class="main-content">

    <div class="clases-header">
        <div class="clases-header-top">
            <div class="clases-titles-container">
                <div class="clases-page-title">CLASES</div>
                {{-- (Aquí podrías hacer dinámico el periodo si lo tuvieras en la sesión) --}}
                <div class="clases-period-subtitle">Agosto 2025 – Febrero 2026</div>
            </div>
            
            <div class="clases-welcome-container">
                <div class="clases-welcome-message">¡Bienvenido(a) {{ $user->nombre }}!</div>
                
                {{-- Botón de Tareas --}}
                <a href="{{ route('MiInformacion.tareas') }}" class="clases-tasks-section">
                    <div class="clases-tasks-icon">
                        <i class="fa-solid fa-list-check" style="color: #fff;"></i>
                    </div>
                    <div class="clases-tasks-text">Tareas</div>
                </a>
            </div>
        </div>
    </div>

    <div class="clases-container">
        <div class="clases-grid">
            
            {{-- BUCLE REAL: Muestra las clases de la base de datos --}}
            {{-- (Si $clases está vacío, mostrará el bloque @empty) --}}
            @forelse($clases as $clase)
                @php
                    $diasLargo = [1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab', 7 => 'Dom'];
                    $franjasResumen = [];
                    foreach (($clase->franjas ?? collect()) as $f) {
                        $dias = $f->dias_semana;
                        if (is_string($dias)) { $dias = json_decode($dias, true); }
                        if (!is_array($dias)) { $dias = $dias !== null && $dias !== '' ? [(int)$dias] : []; }
                        $diasTxt = collect($dias)->map(fn($d) => $diasLargo[(int)$d] ?? ('Dia ' . $d))->implode(', ');
                        $inicio = \Carbon\Carbon::parse($f->hora_inicio)->format('H:i');
                        $fin = \Carbon\Carbon::parse($f->hora_fin)->format('H:i');
                        $franjasResumen[] = trim($diasTxt . ' ' . $inicio . '-' . $fin);
                    }
                @endphp
                <div class="class-card">
                    @if($esDocente)
                        <a href="{{ route('MiInformacion.clases.asistencia', $clase) }}" class="class-icon-container" title="Ver lista de alumnos y asistencia">
                            <i class="fa-solid {{ $clase->carrera?->iconClass() ?? 'fa-graduation-cap' }}" style="font-size: 2em; color: #223F70;"></i>
                        </a>
                    @else
                        <div class="class-icon-container">
                            <i class="fa-solid {{ $clase->carrera?->iconClass() ?? 'fa-graduation-cap' }}" style="font-size: 2em; color: #223F70;"></i>
                        </div>
                    @endif
                    <div class="class-content">
                        <div class="class-title">{{ $clase->materia->nombre ?? 'Materia' }}</div>
                        @if($clase->carrera)
                            <div class="class-subtitle">{{ $clase->carrera->name }}</div>
                        @endif
                        @unless($esDocente)
                            <div class="class-meta"><strong>Docente:</strong> {{ $clase->user->nombre ?? 'Sin asignar' }}</div>
                        @endunless
                        <div class="class-meta"><strong>Aula:</strong> {{ $clase->aula?->nombre_aula ?? 'Sin asignar' }}</div>
                        <div class="class-meta class-meta--horario"><strong>Horario:</strong> {{ !empty($franjasResumen) ? implode(' | ', array_unique($franjasResumen)) : 'Sin horario' }}</div>
                        <div class="class-orange-line"></div>
                        <div class="class-footer">
                            <button type="button" class="class-icon-placeholder" style="border:none; background:none; cursor:pointer;" title="Contenido de la materia" onclick="document.getElementById('modal-contenido-{{ $clase->id }}').classList.add('is-open')">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            @if($esDocente)
                                <a href="{{ route('MiInformacion.clases.tareas', $clase) }}" class="class-icon-placeholder" title="Tareas">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                </a>
                                <a href="{{ route('MiInformacion.clases.evaluaciones', $clase) }}" class="class-icon-placeholder" title="Evaluaciones">
                                    <i class="fa-solid fa-list-check"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                {{-- Si no hay clases reales, mostramos el mensaje --}}
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    <p>No tienes clases asignadas en este periodo.</p>
                </div>
            @endforelse

        </div>

        <a href="#" class="previous-classes-btn">Clases anteriores</a>
    </div>
   

    @foreach($clases as $clase)
        @php
            $diasLargo = [1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab', 7 => 'Dom'];
            $franjasResumenModal = [];
            foreach (($clase->franjas ?? collect()) as $f) {
                $dias = $f->dias_semana;
                if (is_string($dias)) { $dias = json_decode($dias, true); }
                if (!is_array($dias)) { $dias = $dias !== null && $dias !== '' ? [(int)$dias] : []; }
                $diasTxt = collect($dias)->map(fn($d) => $diasLargo[(int)$d] ?? ('Dia ' . $d))->implode(', ');
                $inicio = \Carbon\Carbon::parse($f->hora_inicio)->format('H:i');
                $fin = \Carbon\Carbon::parse($f->hora_fin)->format('H:i');
                $franjasResumenModal[] = trim($diasTxt . ' ' . $inicio . '-' . $fin);
            }
            $horarioResumenModal = !empty($franjasResumenModal) ? implode(' | ', array_unique($franjasResumenModal)) : 'Sin horario';
        @endphp
        <div class="modal" id="modal-contenido-{{ $clase->id }}">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('modal-contenido-{{ $clase->id }}').classList.remove('is-open')">&times;</span>
                <h2 class="modal-title">Contenido de la materia</h2>

                <div class="contenido-campo">
                    <strong>Nombre:</strong> <span>{{ $clase->materia->nombre ?? 'Materia' }}</span>
                    <div class="contenido-linea"></div>
                </div>
                <div class="contenido-campo">
                    <strong>Horario:</strong> <span>{{ $horarioResumenModal }}</span>
                    <div class="contenido-linea"></div>
                </div>
                <div class="contenido-campo">
                    <strong>Maestro:</strong> <span>{{ $clase->user->nombre ?? 'Sin asignar' }}</span>
                    <div class="contenido-linea"></div>
                </div>
                <div class="contenido-campo">
                    <strong>Aula:</strong> <span>{{ $clase->aula?->nombre_aula ?? 'Sin asignar' }}</span>
                    <div class="contenido-linea"></div>
                </div>
                <div class="contenido-campo">
                    <strong>Créditos:</strong> <span>{{ $clase->materia->creditos ?? '—' }}</span>
                    <div class="contenido-linea"></div>
                </div>

                @if($esDocente)
                    <form action="{{ route('MiInformacion.clases.contenido.update', $clase) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="contenido-rasgos-titulo">Rasgos a evaluar:</div>
                        <div class="contenido-rasgo-fila">
                            <span class="contenido-rasgo-pill">Asistencias</span>
                            <input type="number" name="peso_asistencias" min="0" max="100" value="{{ $clase->materia->peso_asistencias ?? 20 }}" required> %
                        </div>
                        <div class="contenido-rasgo-fila">
                            <span class="contenido-rasgo-pill">Tareas</span>
                            <input type="number" name="peso_tareas" min="0" max="100" value="{{ $clase->materia->peso_tareas ?? 20 }}" required> %
                        </div>
                        <div class="contenido-rasgo-fila">
                            <span class="contenido-rasgo-pill">Evaluaciones</span>
                            <input type="number" name="peso_evaluaciones" min="0" max="100" value="{{ $clase->materia->peso_evaluaciones ?? 60 }}" required> %
                        </div>

                        <div class="contenido-rasgos-titulo" style="margin-top:20px;">Temario:</div>
                        <div class="contenido-rasgo-fila">
                            <label for="temario_archivo_{{ $clase->id }}" class="btn-pill-primary" style="cursor:pointer;">Seleccionar Archivo</label>
                            <input type="file" name="temario_archivo" id="temario_archivo_{{ $clase->id }}" accept=".pdf,.doc,.docx" style="display:none;"
                                onchange="document.getElementById('temario-filename-{{ $clase->id }}').textContent = this.files.length ? this.files[0].name : (@json($clase->materia->temario_archivo ? basename($clase->materia->temario_archivo) : 'Ningún archivo seleccionado'));">
                            <span id="temario-filename-{{ $clase->id }}">{{ $clase->materia->temario_archivo ? basename($clase->materia->temario_archivo) : 'Ningún archivo seleccionado' }}</span>
                        </div>

                        <button type="submit" class="guardar">Guardar</button>
                    </form>
                @else
                    <div class="contenido-rasgos-titulo">Rasgos a evaluar:</div>
                    <ul class="contenido-rasgos-lista">
                        <li>Tareas: {{ $clase->materia->peso_tareas ?? 20 }}%</li>
                        <li>Evaluaciones: {{ $clase->materia->peso_evaluaciones ?? 60 }}%</li>
                        <li>Asistencias: {{ $clase->materia->peso_asistencias ?? 20 }}%</li>
                    </ul>

                    <a href="{{ route('MiInformacion.clases.temario', $clase) }}" class="btn-pill-primary" style="text-decoration:none; display:inline-flex;">
                        <i class="fa-solid fa-download"></i> Descargar Temario
                    </a>
                @endif
            </div>
        </div>
    @endforeach

</div>

<script>
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal') && e.target.classList.contains('is-open')) {
        e.target.classList.remove('is-open');
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.is-open').forEach(function (m) { m.classList.remove('is-open'); });
    }
});
</script>
@endsection

