@forelse($dataList as $item)
    <tr>
        <td>{{ $item->alumno?->nombre }} {{ $item->alumno?->apellido_paterno }}</td>
        <td>{{ $item->nombre_documento }}</td>
        <td>{{ $item->descripcion ?: '-' }}</td>
        <td><a class="becas-download-link" href="{{ route('escolar.becas.download', $item->id) }}" target="_blank">Descargar Documento</a></td>
        <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
        <td>
            <button class="becas-btn becas-btn--icon" type="button" onclick="editBeca({{ $item->id }})" aria-label="Editar" title="Editar">
                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
            </button>
            <form method="POST" action="{{ route('escolar.becas.destroy', $item->id) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar documento?')">
                @csrf @method('DELETE')
                <button class="becas-btn becas-btn--icon becas-btn--danger" type="submit" aria-label="Eliminar" title="Eliminar">
                    <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar">
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="6" style="text-align:center;color:#777;">Sin documentos de becas.</td></tr>
@endforelse
