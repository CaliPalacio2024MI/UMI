@php $numP = $numParciales ?? 3; @endphp
@forelse($rows as $r)
    <tr>
        <td>
            {{ $r->alumno_nombre }}
            @if(!empty($r->materia) && $r->materia !== '—')
                <div style="font-size: 0.78rem; font-weight: 500; color: #666;">{{ $r->materia }}</div>
            @endif
        </td>
        @for($i = 0; $i < $numP; $i++)
            <td class="boletas-col-divider">{{ $r->parciales_arr[$i] ?? '----' }}</td>
        @endfor
        <td class="boletas-col-divider">{{ $r->final }}</td>
        <td class="boletas-col-divider">{{ $r->evaluacion }}</td>
        <td class="boletas-col-divider">{{ $r->observaciones }}</td>
    </tr>
@empty
    <tr>
        <td colspan="{{ $numP + 3 }}" style="text-align: center; padding: 2.5rem; color: #666;">
            No hay registros con los filtros actuales.
        </td>
    </tr>
@endforelse
