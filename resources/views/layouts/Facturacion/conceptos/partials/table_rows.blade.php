@forelse ($conceptos as $item)
    <tr>
        <td><strong>{{ $item->concept }}</strong></td>
        <td>${{ number_format($item->amount, 2) }}</td>
        <td>{{ $item->porcentaje_cargo_moratorio !== null ? number_format((float) $item->porcentaje_cargo_moratorio, 2) : '-' }}</td>
        <td>{{ $item->cargo_monetario !== null ? '$'.number_format((float) $item->cargo_monetario, 2) : '-' }}</td>
        <td>{{ $item->description ?? '-' }}</td>

        <td class="status-toggle-cell">
            <form action="{{ route('facturacion.conceptos.toggleStatus', $item->id) }}" method="POST" class="inline-form" onsubmit="return confirm('¿Cambiar el estatus?');">
                @csrf @method('POST')
                <label class="switch" title="{{ $item->is_active ? 'Activo' : 'Inactivo' }}">
                    <input type="checkbox" {{ $item->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                    <span class="slider"></span>
                </label>
            </form>
        </td>

        <td class="actions">
            <a href="#" class="btn-icon btn-edit" data-id="{{ $item->id }}" data-item="{{ json_encode($item) }}">
                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
            </a>
            <form action="{{ route('facturacion.conceptos.destroy', $item->id) }}" method="POST" class="inline-form" onsubmit="return confirm('¿Eliminar permanentemente?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-delete"><img src="{{ asset('images/icons/delete-left-solid-full.svg') }}" alt="Eliminar"></button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center">No hay conceptos registrados.</td></tr>
@endforelse
