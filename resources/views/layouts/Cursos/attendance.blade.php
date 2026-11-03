@extends('layouts.app')

@section('title', 'Asistencia - ' . $course->title)

@section('content')
<div class="course-viewer" style="display: flex; flex-direction: row; gap: 20px; max-width: 100%; margin: 0 auto; padding: 20px; box-sizing: border-box; align-items: flex-start; background-color: #f9fafb; min-height: calc(100vh - 60px);">
    
    {{-- ==========================================
         COLUMNA IZQUIERDA: LISTA DE PERÍODOS 
    ========================================== --}}
    <div style="width: 320px; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
            <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1f2937;">{{ $course->title }}</h2>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            
            {{-- Ciclo de Períodos --}}
            @foreach($periods as $period)
            <div class="period-card" onclick="filterByPeriod('{{ $period->id }}', this)" style="padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; display: flex; flex-direction: column; gap: 8px; transition: all 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    {{-- Fechas del Periodo --}}
                    <div style="font-weight: 600; color: #374151; font-size: 0.95rem;" class="period-name">
                        {{ \Carbon\Carbon::parse($period->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($period->end_date)->format('d/m/Y') }}
                    </div>
                    
                    {{-- Estado del Periodo --}}
                    @php
                        $now = \Carbon\Carbon::now();
                        $start = \Carbon\Carbon::parse($period->start_date);
                        $end = \Carbon\Carbon::parse($period->end_date);
                        $isActive = $now->between($start, $end);
                    @endphp
                    
                    @if($isActive)
                        <span style="background: #d1fae5; color: #065f46; font-size: 0.75rem; padding: 4px 8px; border-radius: 9999px; font-weight: 600;">Activo</span>
                    @else
                        <span style="background: #fee2e2; color: #991b1b; font-size: 0.75rem; padding: 4px 8px; border-radius: 9999px; font-weight: 600;">Finalizado</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ==========================================
         COLUMNA DERECHA: BOTONES Y TABLA 
    ========================================== --}}
    <div style="flex-grow: 1; min-width: 0; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        
        {{-- BOTONES SUPERIORES (Usando clases originales e iconos reducidos) --}}
        <div class="course-controls presencial-actions" style="width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
            
            <a id="btn-download-session-pdf"
               href="#"
               onclick="exportFilteredPDF(); return false;"
               class="btn-presencial-action">
                <i class="fa-solid fa-download" style="font-size: 0.85rem; margin-right: 4px;"></i>
                Descargar PDF
            </a>
            
            <button type="button" 
                    id="btn-scan-qr"
                    class="btn-presencial-action" 
                    onclick="alert('Esta es una vista virtual. El escaneo por QR opera en cursos presenciales.')">
                <i class="fa-solid fa-qrcode" style="font-size: 0.85rem; margin-right: 4px;"></i>
                Escanear QR
            </button>

            <button type="button" 
                    class="btn-presencial-action btn-presencial-exit" 
                    style="margin-left: auto;"
                    onclick="window.location.href='{{ route('Cursos.index') }}'">
                <i class="fa-solid fa-right-from-bracket" style="font-size: 0.85rem; margin-right: 4px;"></i>
                Salir
            </button>
        </div>

        {{-- MENSAJE INICIAL (Visible por defecto) --}}
        <div id="empty-state-message" style="display: flex; justify-content: center; align-items: center; height: 300px; color: #9ca3af; font-size: 1.1rem;">
            Selecciona un período para ver el grupo
        </div>

        {{-- CONTENEDOR DE LA TABLA (Oculto por defecto) --}}
        <div id="table-data-container" class="presencial-table-viewer" style="display: none; overflow-x: auto;">
            <table class="selected-users-table presencial-users-table" id="attendanceTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                <thead style="background-color: #f3f4f6;">
                    <tr>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">#</th>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Nombre Completo</th>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Email</th>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Período</th>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Fecha Asist.</th>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Hora</th>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Progreso</th>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Calificación</th>
                        <th style="padding: 12px 16px; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Estado</th>
                    </tr>
                </thead>
                <tbody style="background-color: #ffffff;">
                    @forelse($attendances as $index => $attendance)
                    <tr class="attendance-row" data-period-id="{{ $attendance['period_id'] ?? '' }}" style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px 16px; color: #6b7280;">{{ $index + 1 }}</td>
                        <td style="padding: 12px 16px; font-weight: 500; color: #111827;">{{ $attendance['nombre'] }}</td>
                        <td style="padding: 12px 16px; color: #6b7280;">{{ $attendance['email'] }}</td>
                        <td style="padding: 12px 16px; color: #6b7280;">{{ $attendance['period_name'] ?? 'Sin período' }}</td>
                        
                        <td style="padding: 12px 16px; color: #374151;">
                            @if($attendance['started_at'])
                                {{ \Carbon\Carbon::parse($attendance['started_at'])->format('d/m/Y') }}
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>

                        <td style="padding: 12px 16px; color: #374151; font-weight: 600;">
                            @if($attendance['started_at'])
                                {{ \Carbon\Carbon::parse($attendance['started_at'])->format('H:i') }}
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>

                        <td style="padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex-grow: 1; background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden; width: 60px;">
                                    <div style="background: #2563eb; height: 100%; width: {{ $attendance['progress'] }}%;"></div>
                                </div>
                                <span style="font-size: 0.85rem; font-weight: 600; color: #374151;">{{ round($attendance['progress']) }}%</span>
                            </div>
                        </td>

                        <td style="padding: 12px 16px;">
                            @if($attendance['final_score'] !== null)
                                <span style="background: {{ $attendance['final_score'] >= 60 ? '#d1fae5' : '#fee2e2' }}; color: {{ $attendance['final_score'] >= 60 ? '#065f46' : '#991b1b' }}; padding: 4px 8px; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">
                                    {{ $attendance['final_score'] }}%
                                </span>
                            @else
                                <span style="color: #9ca3af; font-size: 0.85rem;">-</span>
                            @endif
                        </td>

                        <td style="padding: 12px 16px;">
                            @if($attendance['progress'] >= 100 && $attendance['final_score'] !== null)
                                <span style="color: #059669; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 4px;"><i class="fa-solid fa-check"></i> Completado</span>
                            @else
                                <span style="color: #d97706; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 4px;"><i class="fa-solid fa-clock"></i> En progreso</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="no-users-row" style="display: none;">
                        <td colspan="9" style="padding: 48px 16px; text-align: center; color: #6b7280;">
                            <div style="font-size: 2rem; margin-bottom: 8px;">📭</div>
                            <div style="font-size: 1rem; font-weight: 500;">No hay usuarios asignados a este período.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            {{-- Mensaje alternativo si el filtro no encuentra a nadie --}}
            <div id="no-filtered-users-message" style="display: none; padding: 48px 16px; text-align: center; color: #6b7280;">
                <div style="font-size: 2rem; margin-bottom: 8px;">📭</div>
                <div style="font-size: 1rem; font-weight: 500;">No hay usuarios asignados a este período.</div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPeriodFilter = '';

    function filterByPeriod(periodId, cardElement) {
        currentPeriodFilter = periodId;
        
        // Ocultar mensaje inicial y mostrar contenedor de tabla
        document.getElementById('empty-state-message').style.display = 'none';
        document.getElementById('table-data-container').style.display = 'block';

        const rows = document.querySelectorAll('.attendance-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const rowPeriod = row.dataset.periodId;
            if (!periodId || rowPeriod === periodId) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Mostrar mensaje si no hay resultados en el filtro
        const noUsersMsg = document.getElementById('no-filtered-users-message');
        const tableElement = document.getElementById('attendanceTable');
        
        if(visibleCount === 0 && rows.length > 0) {
            tableElement.style.display = 'none';
            noUsersMsg.style.display = 'block';
        } else {
            tableElement.style.display = 'table';
            noUsersMsg.style.display = 'none';
        }

        // Actualizar UI de tarjetas
        document.querySelectorAll('.period-card').forEach(card => {
            card.style.background = '#fff';
            card.classList.remove('active');
        });
        
        cardElement.style.background = '#f3f4f6';
        cardElement.classList.add('active');
    }

    function exportFilteredPDF() {
        if(!currentPeriodFilter) {
            alert('Por favor selecciona un período antes de descargar el PDF.');
            return;
        }
        let url = '{{ route("courses.attendance.pdf", $course) }}';
        url += '?period=' + currentPeriodFilter;
        window.location.href = url;
    }
</script>
@endsection