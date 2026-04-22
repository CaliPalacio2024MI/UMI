@forelse($dataList as $item)
    <tr>
        <td>{{ $item->alumno?->nombre }} {{ $item->alumno?->apellido_paterno }}</td>
        <td>{{ $item->nombre_documento }}</td>
        <td>{{ $item->descripcion ?: '-' }}</td>
        <td><a class="titulacion-download-link" href="{{ route('escolar.titulacion.download', $item->id) }}" target="_blank">Ver Documento / Descargar Documento</a></td>
        <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
        <td>
            <button class="titulacion-btn titulacion-btn--icon" type="button" onclick="editTitulacion({{ $item->id }})" aria-label="Editar" title="Editar">
                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
            </button>
            <form method="POST" action="{{ route('escolar.titulacion.destroy', $item->id) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar documento?')">
                @csrf @method('DELETE')
                <button class="titulacion-btn titulacion-btn--icon titulacion-btn--danger" type="submit" aria-label="Eliminar" title="Eliminar">
                    <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar">
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="6" style="text-align:center;color:#777;">Sin documentos de titulación.</td></tr>
@endforelse
