@extends('layouts.app')

@section('title', 'Períodos - ' . $course->title)

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 30px;">
    
    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 30px; color: white; margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0 0 10px 0; font-size: 2em;">📅 Gestión de Períodos</h1>
                <p style="margin: 0; opacity: 0.9;">{{ $course->title }}</p>
            </div>
            <a href="{{ route('courses.attendance', $course) }}" style="background: rgba(255,255,255,0.2); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                ← Volver a Asistencia
            </a>
        </div>
    </div>

    {{-- Formulario crear período --}}
    <div style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0;">➕ Crear Nuevo Período</h3>
        <form action="{{ route('courses.periods.store', $course) }}" method="POST" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; align-items: end;">
            @csrf
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Nombre del Período</label>
                <input type="text" name="name" placeholder="Ej: Período Abril 2026" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Fecha Inicio</label>
                <input type="date" name="start_date" min="{{ now()->format('Y-m-d') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Fecha Fin</label>
                <input type="date" name="end_date" min="{{ now()->format('Y-m-d') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                ✓ Crear
            </button>
        </form>
    </div>

    {{-- Lista de períodos --}}
    <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th style="padding: 15px; text-align: left; font-weight: 600; color: #333;">Nombre</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333;">Fecha Inicio</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333;">Fecha Fin</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333;">Estado</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #333;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $period)
                @php
                    $today = \Carbon\Carbon::today();
                    $estado = 'No iniciado';
                    $color = '#6c757d';
                    
                    if ($today->lessThan($period->start_date)) {
                        $estado = '⏳ No iniciado';
                        $color = '#6c757d';
                    } elseif ($today->greaterThan($period->end_date)) {
                        $estado = '✓ Finalizado';
                        $color = '#dc3545';
                    } else {
                        $estado = '🟢 Activo';
                        $color = '#28a745';
                    }
                @endphp
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 15px; font-weight: 500;">{{ $period->name }}</td>
                    <td style="padding: 15px; text-align: center;">{{ $period->start_date->format('d/m/Y') }}</td>
                    <td style="padding: 15px; text-align: center;">{{ $period->end_date->format('d/m/Y') }}</td>
                    <td style="padding: 15px; text-align: center;">
                        <span style="background: {{ $color }}20; color: {{ $color }}; padding: 4px 12px; border-radius: 5px; font-size: 0.9em; font-weight: 600;">
                            {{ $estado }}
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <form action="{{ route('courses.periods.destroy', [$course, $period]) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este período?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer;">
                                🗑️ Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 40px; text-align: center; color: #999;">
                        No hay períodos creados. Crea el primero arriba.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection