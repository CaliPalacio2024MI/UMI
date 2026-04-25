@extends('layouts.app')

@section('title', 'Asistencia - ' . $course->title)

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 30px;">
    
    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #32459a 0%, #0c35d9 100%); border-radius: 15px; padding: 30px; color: white; margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0 0 10px 0; font-size: 2em;">📊 Lista de Asistencia</h1>
                <p style="margin: 0; opacity: 0.9;">{{ $course->title }}</p>
            </div>
            <a href="{{ route('Cursos.index') }}" style="background: rgba(255,255,255,0.2); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                ← Volver a Cursos
            </a>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="color: #667eea; font-size: 0.9em; font-weight: 600; margin-bottom: 5px;">Total Inscritos</div>
            <div style="font-size: 2em; font-weight: bold; color: #333;" id="stat-total">{{ $attendances->count() }}</div>
        </div>
        
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="color: #28a745; font-size: 0.9em; font-weight: 600; margin-bottom: 5px;">Completados</div>
            <div style="font-size: 2em; font-weight: bold; color: #333;" id="stat-completados">{{ $attendances->where('status', 'Completado')->count() }}</div>
        </div>
        
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="color: #ffc107; font-size: 0.9em; font-weight: 600; margin-bottom: 5px;">En Progreso</div>
            <div style="font-size: 2em; font-weight: bold; color: #333;" id="stat-progreso">{{ $attendances->where('status', 'En progreso')->count() }}</div>
        </div>
        
        @if($attendances->where('final_score', '!=', null)->count() > 0)
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="color: #764ba2; font-size: 0.9em; font-weight: 600; margin-bottom: 5px;">Promedio Examen Final</div>
            <div style="font-size: 2em; font-weight: bold; color: #333;" id="stat-promedio">
                {{ round($attendances->whereNotNull('final_score')->avg('final_score'), 1) }}%
            </div>
        </div>
        @endif
    </div>

    {{-- Botón Gestionar Períodos --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('courses.periods.index', $course) }}" style="background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">
            📅 Gestionar Períodos
        </a>
    </div>

    {{-- Filtros y exportación --}}
    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 15px; margin-bottom: 20px;">
        {{-- Buscador por nombre --}}
        <input 
            type="text" 
            id="searchName" 
            placeholder="🔍 Buscar por nombre..." 
            style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 250px; font-size: 14px;"
            onkeyup="filterTable()">
        
        {{-- Filtro por fecha --}}
        <input 
            type="date" 
            id="filterDate" 
            style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;"
            onchange="filterTable()">
        
        {{-- Filtro por período --}}
        <select 
            id="filterPeriod" 
            style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; min-width: 200px;"
            onchange="filterTable()">
            <option value="">📅 Todos los períodos</option>
            @foreach($periods as $period)
                <option value="{{ $period->id }}">{{ $period->name }}</option>
            @endforeach
        </select>
        
        {{-- Botón limpiar filtros --}}
        <button 
            onclick="clearFilters()" 
            style="background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">
            🔄 Limpiar
        </button>
        
        {{-- Botón exportar PDF --}}
        <button 
            onclick="exportFilteredPDF()"
            style="background: #dc3545; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-block;">
            📄 Exportar PDF
        </button>
    </div>

    {{-- Tabla --}}
    <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <table id="attendanceTable" style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th style="padding: 15px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">#</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">Nombre Completo</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">Email</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">Período</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">Fecha Inicio</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">Fecha Fin</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">Progreso</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">Calificación Final</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #dee2e6;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $index => $attendance)
                <tr data-period-id="{{ $attendance['period_id'] ?? '' }}" style="border-bottom: 1px solid #dee2e6; {{ $loop->even ? 'background: #f8f9fa;' : '' }}">
                    <td style="padding: 15px;">{{ $index + 1 }}</td>
                    <td style="padding: 15px; font-weight: 500;">{{ $attendance['nombre'] }}</td>
                    <td style="padding: 15px; color: #666;">{{ $attendance['email'] }}</td>
                    <td style="padding: 15px; text-align: center; font-size: 0.85em; color: #666;">
                        {{ $attendance['period_name'] ?? 'Sin período' }}
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @if($attendance['started_at'])
                            <div style="font-weight: 500;">{{ \Carbon\Carbon::parse($attendance['started_at'])->format('d/m/Y') }}</div>
                            <div style="font-size: 0.85em; color: #999;">{{ \Carbon\Carbon::parse($attendance['started_at'])->format('H:i') }}</div>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @if($attendance['completed_at'])
                            <div style="font-weight: 500;">{{ \Carbon\Carbon::parse($attendance['completed_at'])->format('d/m/Y') }}</div>
                            <div style="font-size: 0.85em; color: #999;">{{ \Carbon\Carbon::parse($attendance['completed_at'])->format('H:i') }}</div>
                        @else
                            <span style="color: #999;">En progreso</span>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <div style="background: #e9ecef; border-radius: 10px; height: 8px; overflow: hidden; max-width: 100px; margin: 0 auto;">
                            <div style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); height: 100%; width: {{ $attendance['progress'] }}%;"></div>
                        </div>
                        <div style="margin-top: 5px; font-size: 0.9em; font-weight: 600; color: #667eea;">{{ round($attendance['progress']) }}%</div>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @if($attendance['final_score'] !== null)
                            <div style="display: inline-block; background: {{ $attendance['final_score'] >= 60 ? '#d4edda' : '#f8d7da' }}; color: {{ $attendance['final_score'] >= 60 ? '#155724' : '#721c24' }}; padding: 6px 12px; border-radius: 6px; font-weight: 600;">
                                {{ $attendance['final_score'] }}%
                            </div>
                        @else
                            <span style="color: #999;">Sin calificación</span>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @if($attendance['progress'] >= 100 && $attendance['final_score'] !== null)
                            <span style="background: #d4edda; color: #155724; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.9em;">✓ Completado</span>
                        @else
                            <span style="background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.9em;">⏳ En progreso</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="padding: 40px; text-align: center; color: #999;">
                        <div style="font-size: 3em; margin-bottom: 10px;">📭</div>
                        <div style="font-size: 1.2em;">Aún no hay estudiantes inscritos en este curso</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable() {
    const searchName = document.getElementById('searchName').value.toLowerCase();
    const filterDate = document.getElementById('filterDate').value;
    const filterPeriod = document.getElementById('filterPeriod').value;
    const rows = document.querySelectorAll('#attendanceTable tbody tr');
    
    let visibleCount = 0;
    let completadosCount = 0;
    let progresoCount = 0;
    let scores = [];
    
    rows.forEach(row => {
        const nombre = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const fechaInicio = row.querySelector('td:nth-child(5) div:first-child')?.textContent || '';
        const periodId = row.dataset.periodId || '';
        const estado = row.querySelector('td:nth-child(9) span')?.textContent.includes('Completado');
        const scoreText = row.querySelector('td:nth-child(8) div')?.textContent || '';
        const score = parseFloat(scoreText.replace('%', ''));
        
        // Convertir fecha de d/m/Y a Y-m-d para comparar
        let fechaInicioComparable = '';
        if (fechaInicio && fechaInicio !== '-') {
            const parts = fechaInicio.split('/');
            if (parts.length === 3) {
                fechaInicioComparable = `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
        }
        
        const matchName = nombre.includes(searchName);
        const matchDate = !filterDate || fechaInicioComparable === filterDate;
        const matchPeriod = !filterPeriod || periodId === filterPeriod;
        
        if (matchName && matchDate && matchPeriod) {
            row.style.display = '';
            visibleCount++;
            if (estado) completadosCount++;
            else progresoCount++;
            if (!isNaN(score)) scores.push(score);
        } else {
            row.style.display = 'none';
        }
    });
    
    // Actualizar estadísticas
    document.getElementById('stat-total').textContent = visibleCount;
    document.getElementById('stat-completados').textContent = completadosCount;
    document.getElementById('stat-progreso').textContent = progresoCount;
    
    if (scores.length > 0) {
        const avg = scores.reduce((a, b) => a + b, 0) / scores.length;
        const statPromedio = document.getElementById('stat-promedio');
        if (statPromedio) statPromedio.textContent = avg.toFixed(1) + '%';
    }
}

function clearFilters() {
    document.getElementById('searchName').value = '';
    document.getElementById('filterDate').value = '';
    document.getElementById('filterPeriod').value = '';
    filterTable();
}

function exportFilteredPDF() {
    const searchName = document.getElementById('searchName').value;
    const filterDate = document.getElementById('filterDate').value;
    const filterPeriod = document.getElementById('filterPeriod').value;
    
    // Construir URL con parámetros de filtro
    let url = '{{ route("courses.attendance.pdf", $course) }}';
    const params = new URLSearchParams();
    
    if (searchName) params.append('search', searchName);
    if (filterDate) params.append('date', filterDate);
    if (filterPeriod) params.append('period', filterPeriod);
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    window.location.href = url;
}
</script>
@endsection