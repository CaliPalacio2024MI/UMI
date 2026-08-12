@forelse($dataList as $item)
    <tr>
        <td>{{ $item->alumno?->nombre }} {{ $item->alumno?->apellido_paterno }}</td>
        <td>{{ $item->nombre_documento }}</td>
        <td><a class="becas-download-link" href="{{ route('escolar.becas.download', $item->id) }}" target="_blank">Ver archivo</a></td>
        <td><span style="background:#e8ecf2;color:#666;padding:3px 10px;border-radius:50px;font-size:0.78rem;">—</span></td>
        <td>{{ optional($item->created_at)->format('d/m/Y') }}</td>
        <td>
            <button class="becas-btn becas-btn--icon" type="button" onclick="editBeca({{ $item->id }})" title="Editar">
                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
            </button>
            <form method="POST" action="{{ route('escolar.becas.destroy', $item->id) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar documento?')">
                @csrf @method('DELETE')
                <button class="becas-btn becas-btn--icon becas-btn--danger" type="submit" title="Eliminar">
                    <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar">
                </button>
            </form>
        </td>
    </tr>
@empty
    @if(($submittedDocs ?? collect())->isEmpty())
        <tr><td colspan="6" style="text-align:center;color:#777;padding:20px;">Sin documentos de becas.</td></tr>
    @endif
@endforelse
