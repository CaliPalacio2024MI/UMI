@extends('layouts.app')

@vite('resources/css/Cursos/courseShow.css')

@section('title', 'Asistencia - ' . $course->title)

@section('content')

<div style="display:flex; gap:28px; align-items:flex-start; padding:20px;">

    {{-- PANEL IZQUIERDO --}}
    <div class="course-menu">
        <div style="background:#ffffff; border-radius:18px; padding:18px; box-shadow:0 10px 25px rgba(0,0,0,.05);">

            <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px; font-size:20px; font-weight:700; color:#1e293b;">
                📚 {{ $course->title }}
            </div>

            @foreach($periods as $period)
                <div onclick="filterPeriod({{ $period->id }})"
                     data-period="{{ $period->id }}"
                     style="
                        border:1px solid #dbe3ef;
                        border-left:4px solid #0ea5e9;
                        border-radius:14px;
                        padding:16px;
                        margin-bottom:14px;
                        background:#f8fafc;
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        cursor:pointer;
                     ">

                    <div>
                        <div style="font-weight:700; color:#1e293b; margin-bottom:8px;">
                            📅 {{ \Carbon\Carbon::parse($period->start_date)->format('d/m/Y') }}
                        </div>

                        <div style="color:#64748b; font-size:14px;">
                            ⏳ Hasta:
                            {{ \Carbon\Carbon::parse($period->end_date)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div>
                        @if($period->attendance_enabled)
                            <span style="background:#dcfce7; color:#15803d; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700;">
                                Activo
                            </span>
                        @else
                            <span style="background:#fee2e2; color:#b91c1c; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700;">
                                Inactivo
                            </span>
                        @endif
                    </div>

                </div>
            @endforeach

        </div>
    </div>

    {{-- PANEL DERECHO --}}
    <main class="course-viewer">

        <div class="course-controls presencial-actions">

            <button onclick="exportFilteredPDF()"
                    class="btn-presencial-action">
                <i class="fa-solid fa-download"></i>
                Descargar PDF
            </button>

            <button class="btn-presencial-action"
                    onclick="window.location.href='{{ route('Cursos.index') }}'">
                Salir
            </button>

        </div>

        <div class="presencial-table-viewer">

            <table class="selected-users-table presencial-users-table"
                   id="attendanceTable">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre Completo</th>
                        <th>Período</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($attendances as $index => $attendance)

                        <tr data-period-id="{{ $attendance['period_id'] ?? '' }}">

                            <td class="row-number"></td>

                            <td>{{ $attendance['nombre'] }}</td>

                            <td>{{ $attendance['period_name'] ?? 'Sin período' }}</td>

                            <td>
                                @if($attendance['started_at'])
                                    {{ \Carbon\Carbon::parse($attendance['started_at'])->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if($attendance['completed_at'])
                                    {{ \Carbon\Carbon::parse($attendance['completed_at'])->format('d/m/Y') }}
                                @else
                                    En progreso
                                @endif
                            </td>

                            <td class="attendance-status">
                                @if($attendance['progress'] >= 100 && $attendance['final_score'] !== null)
                                    <span class="attendance-badge present"
                                          style="background:#dcfce7; color:#15803d; padding:8px 12px; border-radius:999px; font-size:13px; font-weight:700;">
                                        Completado
                                    </span>
                                @else
                                    <span class="attendance-badge pending"
                                          style="background:#fef3c7; color:#92400e; padding:8px 12px; border-radius:999px; font-size:13px; font-weight:700;">
                                        En progreso
                                    </span>
                                @endif
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-session-message">
                                    No hay estudiantes inscritos en este curso
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>

    </main>

</div>

<script>
let selectedPeriodId = {{ $period->id }};

function filterPeriod(periodId) {
    selectedPeriodId = periodId;

    document.querySelectorAll('[data-period]').forEach(card => {
        card.style.background = '#f8fafc';
        card.style.borderLeftColor = '#0ea5e9';
    });

    const activeCard = document.querySelector(`[data-period="${periodId}"]`);

    if (activeCard) {
        activeCard.style.background = '#e0f2fe';
        activeCard.style.borderLeftColor = '#0369a1';
    }

    document.querySelectorAll('#attendanceTable tbody tr').forEach(row => {
        row.style.display =
            row.dataset.periodId == periodId ? '' : 'none';
    });

    renumerarFilasPeriodo(periodId);
}

function renumerarFilasPeriodo(periodId) {
    let contador = 1;

    document.querySelectorAll('#attendanceTable tbody tr').forEach(row => {
        if (row.dataset.periodId == periodId && row.style.display !== 'none') {
            const numberCell = row.querySelector('.row-number');

            if (numberCell) {
                numberCell.textContent = contador++;
            }
        }
    });
}

function exportFilteredPDF() {
    let url = '{{ route("courses.attendance.virtual.pdf", $course) }}';

    if (selectedPeriodId) {
        url += '?period=' + selectedPeriodId;
    }

    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function () {
    filterPeriod(selectedPeriodId);
});
</script>

@endsection
