@forelse ($data as $item)
<tr>
    @php
        $displayInstitutionId = (int) ($item->institution_id ?? optional($item->institutions->first())->id ?? 0);
    @endphp
    <td>{{ $item->RFC }}</td>
    <td>{{ $item->institutions->first()->name ?? 'N/A' }}</td>
    <td>{{ $item->nombre }}</td>
    <td>{{ $item->apellido_paterno }}</td>
    <td>{{ $item->apellido_materno }}</td>
    <td>{{ $item->roleDisplayNameForAjustes($displayInstitutionId > 0 ? $displayInstitutionId : null) }}</td>
    <td>
        <div class="actions">
            <a href="#" title="Editar" class="btn-icon btn-edit" data-id="{{ $item->id }}">
                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
            </a>
            <form action="{{ route('ajustes.destroy', ['seccion' => 'users', 'id' => $item->id]) }}"
                method="POST" class="inline-form"
                onsubmit="return confirm('ADVERTENCIA: ¿Estás seguro de ELIMINAR PERMANENTEMENTE este registro? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" title="Eliminar Permanente" class="btn-delete">
                    <img src="{{ asset('images/icons/delete-left-solid-full.svg') }}" alt="Eliminar">
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center">
        No hay datos disponibles en la sección de {{ strtolower($page_title) }}.
    </td>
</tr>
@endforelse
