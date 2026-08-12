@forelse($dataList as $item)
    <tr>
        <td>{{ $item->alumno?->nombre }} {{ $item->alumno?->apellido_paterno }}</td>
        <td>{{ $item->nombre_documento }}</td>
        <td>
            <a class="titulacion-download-link" href="{{ route('escolar.titulacion.download', $item->id) }}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;">
                <img src="{{ asset('images/icons/eye.svg') }}" width="14" height="14" style="vertical-align:middle;flex-shrink:0;">
                Ver archivo
            </a>
        </td>
        <td><span style="background:#e8ecf2;color:#666;padding:3px 10px;border-radius:50px;font-size:0.78rem;">—</span></td>
        <td>{{ optional($item->created_at)->format('d/m/Y') }}</td>
        <td>
            <button class="titulacion-btn titulacion-btn--icon" type="button" onclick="editTitulacion({{ $item->id }})" title="Editar">
                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
            </button>
            <form method="POST" action="{{ route('escolar.titulacion.destroy', $item->id) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar documento?')">
                @csrf @method('DELETE')
                <button class="titulacion-btn titulacion-btn--icon titulacion-btn--danger" type="submit" title="Eliminar">
                    <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar">
                </button>
            </form>
        </td>
    </tr>
@empty
    @if(($submittedDocs ?? collect())->isEmpty())
        <tr><td colspan="6" style="text-align:center;color:#777;padding:20px;">Sin documentos de titulación.</td></tr>
    @endif
@endforelse
