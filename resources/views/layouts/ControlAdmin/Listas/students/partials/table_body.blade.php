@forelse ($dataList as $user)
    <tr>
        <td style="font-family: monospace; font-size: 0.9rem; font-weight: bold;">
            {{ $user->curp ?? '—' }}
        </td>
        <td style="font-weight: 700;">
            {{ $user->nombre }}
        </td>
        <td style="font-weight: 700;">{{ $user->apellido_paterno }}</td>
        <td style="font-weight: 700;">{{ $user->apellido_materno }}</td>

        <td style="text-align:center">
            @php
                $status = $user->academicProfile->status ?? 'Aspirante';
                $statusColor = match($status) {
                    'Alumno Activo' => '#27ae60',
                    'Alumno Inactivo' => '#e74c3c',
                    'Baja' => '#7f8c8d',
                    'Egresado' => '#3498db',
                    default => '#f39c12',
                };
            @endphp
            <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                <span style="color: #000; font-weight: bold; border: none; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; width: fit-content;">
                    {{ $status }}
                </span>
            </div>
        </td>
        <td style="font-weight: 600; color: #555;">
            <div class="career-cell">
                {{ $user->academicProfile?->career?->name ?? 'Sin Asignar' }}
            </div>
        </td>
        <td style="text-align:center; vertical-align: middle;">
            <div class="actions-row umi-actions-icons" style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: nowrap;">
                <a href="{{ request()->routeIs('control.*') ? route('control.students.horarios', $user->id) : route('escolar.students.horarios', $user->id) }}"
                    class="btn-icon data-btn-clock-student" title="Horario"
                    style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                    <img src="{{ asset('images/icons/clock-solid-full-092034.svg') }}" alt="Horario" width="20" height="20">
                </a>
                <button type="button" class="btn-icon" title="Ver Expediente" style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center;"
                    data-action="open-expediente"
                    data-name="{{ $user->nombre }} {{ $user->apellido_paterno }} {{ $user->apellido_materno }}"
                    data-email="{{ $user->email }}"
                    data-phone="{{ $user->telefono ?? 'N/A' }}"
                    data-career="{{ $user->academicProfile->career->name ?? 'Sin Carrera' }}"
                    data-semester="{{ $user->academicProfile->semestre ?? '1' }}"
                    data-status="{{ $user->academicProfile->status ?? 'Pendiente' }}"
                    data-matricula="{{ $user->academicProfile->matricula ?? 'No Asignada' }}"
                    data-doc-acta="{{ $user->academicProfile->doc_acta_nacimiento ? Storage::url($user->academicProfile->doc_acta_nacimiento) : '' }}"
                    data-doc-cert="{{ $user->academicProfile->doc_certificado_prepa ? Storage::url($user->academicProfile->doc_certificado_prepa) : '' }}"
                    data-doc-curp="{{ $user->academicProfile->doc_curp ? Storage::url($user->academicProfile->doc_curp) : '' }}"
                    data-doc-ine="{{ $user->academicProfile->doc_ine ? Storage::url($user->academicProfile->doc_ine) : '' }}">
                    <img src="{{ asset('images/icons/eye-solid-full-bc8a55.svg') }}" alt="Ver">
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
            No se encontraron alumnos registrados.
        </td>
    </tr>
@endforelse
