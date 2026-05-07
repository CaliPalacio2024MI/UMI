@forelse($rows as $r)
    <tr>
        <td>
            {{ $r->alumno_nombre }}
            @if(!empty($r->materia) && $r->materia !== '—')
                <div style="font-size: 0.78rem; font-weight: 500; color: #666;">{{ $r->materia }}</div>
            @endif
        </td>
        <td class="boletas-col-divider">{{ $r->parciales }}</td>
        <td class="boletas-col-divider">{{ $r->final }}</td>
        <td class="boletas-col-divider">{{ $r->evaluacion }}</td>
        <td class="boletas-col-divider">{{ $r->observaciones }}</td>
    </tr>
@empty
    <tr>
        <td colspan="5" style="text-align: center; padding: 2.5rem; color: #666;">
            No hay registros con los filtros actuales.
        </td>
    </tr>
@endforelse
