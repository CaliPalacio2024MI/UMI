@forelse ($dataList as $item)
    @if (($item->kind ?? null) === 'lead' && isset($item->lead))
        @php
            $lead = $item->lead;
            $nombreCarrera = $lead->carrera->name ?? 'Sin carrera';
            $correoDesdeInscripcion = $item->lead_student_email ?? '';
        @endphp
        <tr class="table-row-lead-crm">
            <td style="font-family: monospace; font-size: 0.9rem; font-weight: bold;">
                {{ $lead->alumno_curp ?? '—' }}
            </td>
            <td style="font-weight: 700;">
                {{ $lead->alumno_nombre ?? '—' }}
            </td>
            <td style="font-weight: 700;">{{ $lead->alumno_paterno ?? '—' }}</td>
            <td style="font-weight: 700;">{{ $lead->alumno_materno ?? '—' }}</td>

            <td style="text-align:center">
                <span style="color: #000; font-weight: bold; border: none; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; width: fit-content; display: inline-block;">
                    Aspirante
                </span>
            </td>
            <td style="font-weight: 600; color: #555;">
                <div class="career-cell">
                    {{ $nombreCarrera }}
                </div>
            </td>
            <td style="text-align:center; vertical-align: middle;">
                <div class="actions-row umi-actions-icons" style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: nowrap;">
                    @if (request()->routeIs('control.*'))
                        <span class="btn-icon data-btn-clock-student" title="Horario disponible tras inscribir al alumno"
                            style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; cursor: default;"
                            role="img" aria-label="Horario no disponible hasta inscribir al aspirante">
                            <img src="{{ asset('images/icons/clock-solid-full-092034.svg') }}" alt="Horario" width="20" height="20">
                        </span>
                    @endif
                    @if (!request()->routeIs('control.*'))
                    <button type="button" class="add-time-slot-btn" aria-label="Aceptar aspirante"
                        data-action="accept-aspirante"
                        data-accept-url="{{ route('escolar.students.acceptAspirante', $lead->id) }}"
                        data-lead-name="{{ trim(($lead->alumno_nombre ?? '') . ' ' . ($lead->alumno_paterno ?? '') . ' ' . ($lead->alumno_materno ?? '')) }}"
                        style="margin-top: 0; width: 20px; height: 20px; align-self: center;">
                        <svg class="add-time-slot-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                    </button>
                    <button type="button" class="btn-icon" title="Datos del aspirante (CRM)" style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center;"
                        data-action="open-expediente"
                        data-name="{{ trim(($lead->alumno_nombre ?? '') . ' ' . ($lead->alumno_paterno ?? '') . ' ' . ($lead->alumno_materno ?? '')) }}"
                        data-alumno-nombre="{{ $lead->alumno_nombre ?? '' }}"
                        data-alumno-paterno="{{ $lead->alumno_paterno ?? '' }}"
                        data-alumno-materno="{{ $lead->alumno_materno ?? '' }}"
                        data-alumno-curp="{{ $lead->alumno_curp ?? '' }}"
                        data-email="{{ $correoDesdeInscripcion ?: 'N/A' }}"
                        data-phone="{{ $lead->telefono1 ?? ($lead->telefono2 ?? 'N/A') }}"
                        data-career="{{ $nombreCarrera }}"
                        data-semester="{{ $lead->semestre ?? 1 }}"
                        data-status="Aspirante"
                        data-matricula="Lead CRM #{{ $lead->id }}"
                        data-doc-acta="{{ $lead->doc_acta_nacimiento ? Storage::url($lead->doc_acta_nacimiento) : '' }}"
                        data-doc-cert="{{ $lead->doc_certificado_prepa ? Storage::url($lead->doc_certificado_prepa) : '' }}"
                        data-doc-curp="{{ $lead->doc_curp ? Storage::url($lead->doc_curp) : '' }}"
                        data-doc-ine="{{ $lead->doc_ine ? Storage::url($lead->doc_ine) : '' }}"
                        data-doc-ficha="{{ $lead->doc_ficha_pago ? Storage::url($lead->doc_ficha_pago) : '' }}"
                        data-doc-xml="{{ $lead->doc_factura_xml ? Storage::url($lead->doc_factura_xml) : '' }}">
                        <img src="{{ asset('images/icons/eye-solid-full-bc8a55.svg') }}" alt="Ver">
                    </button>
                    <button type="button" class="btn-icon" title="Editar aspirante"
                        data-action="open-expediente-edit"
                        data-lead-id="{{ $lead->id }}"
                        data-status="Aspirante"
                        data-alumno-nombre="{{ $lead->alumno_nombre ?? '' }}"
                        data-alumno-paterno="{{ $lead->alumno_paterno ?? '' }}"
                        data-alumno-materno="{{ $lead->alumno_materno ?? '' }}"
                        data-alumno-curp="{{ $lead->alumno_curp ?? '' }}"
                        data-telefono1="{{ $lead->telefono1 ?? '' }}"
                        data-alumno-email="{{ $correoDesdeInscripcion }}"
                        data-carrera-id="{{ $lead->carrera_id ?? '' }}"
                        data-semestre="{{ $lead->semestre ?? 1 }}"
                        data-doc-acta="{{ $lead->doc_acta_nacimiento ? Storage::url($lead->doc_acta_nacimiento) : '' }}"
                        data-doc-cert="{{ $lead->doc_certificado_prepa ? Storage::url($lead->doc_certificado_prepa) : '' }}"
                        data-doc-curp="{{ $lead->doc_curp ? Storage::url($lead->doc_curp) : '' }}"
                        data-doc-ine="{{ $lead->doc_ine ? Storage::url($lead->doc_ine) : '' }}"
                        data-doc-rech-acta="{{ !empty($lead->doc_acta_rechazado) ? '1' : '0' }}"
                        data-doc-rech-cert="{{ !empty($lead->doc_certificado_rechazado) ? '1' : '0' }}"
                        data-doc-rech-curp="{{ !empty($lead->doc_curp_rechazado) ? '1' : '0' }}"
                        data-doc-rech-ine="{{ !empty($lead->doc_ine_rechazado) ? '1' : '0' }}"
                        data-doc-ficha="{{ $lead->doc_ficha_pago ? Storage::url($lead->doc_ficha_pago) : '' }}"
                        data-doc-xml="{{ $lead->doc_factura_xml ? Storage::url($lead->doc_factura_xml) : '' }}"
                        data-doc-rech-ficha="{{ !empty($lead->doc_ficha_pago_rechazado) ? '1' : '0' }}"
                        data-doc-rech-xml="{{ !empty($lead->doc_factura_xml_rechazado) ? '1' : '0' }}"
                        style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar" width="20" height="20">
                    </button>
                    @endif
                    <form action="{{ route('crm.leads.destroy', $lead->id) }}" method="POST"
                        style="display: inline-flex; margin: 0; align-items: center;"
                        onsubmit="return confirm('¿Eliminar este aspirante? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon" title="Eliminar aspirante"
                            style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; padding: 0;">
                            <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar" width="20" height="20">
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @else
        @php
            $user = $item->user ?? $item;
        @endphp
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
                @endphp
                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                    <span style="color: #000; font-weight: bold; border: none; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; width: fit-content;">
                        {{ $status }}
                    </span>
                </div>
            </td>
            <td style="font-weight: 600; color: #555;">
                <div class="career-cell">
                    {{ $user->academicProfile?->career?->name ?? ($user->fallback_career_name ?? 'Sin Asignar') }}
                </div>
            </td>
            <td style="text-align:center; vertical-align: middle;">
                <div class="actions-row umi-actions-icons" style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: nowrap;">
                    @if (request()->routeIs('control.*'))
                        <a href="{{ route('control.students.horarios', $user->id) }}"
                            class="btn-icon data-btn-clock-student" title="Horario"
                            style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <img src="{{ asset('images/icons/clock-solid-full-092034.svg') }}" alt="Horario" width="20" height="20">
                        </a>
                    @endif
                @if (!request()->routeIs('control.*'))
                <button type="button" class="add-time-slot-btn" aria-label="Aceptar aspirante"
                    data-action="accept-aspirante"
                    data-accept-url="{{ !empty($user->fallback_lead_id) ? route('escolar.students.acceptAspirante', $user->fallback_lead_id) : '' }}"
                    data-lead-name="{{ $user->nombre }} {{ $user->apellido_paterno }} {{ $user->apellido_materno }}"
                    style="margin-top: 0; width: 20px; height: 20px; align-self: center;">
                    <svg class="add-time-slot-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6L9 17l-5-5"></path>
                    </svg>
                </button>
                    <button type="button" class="btn-icon" title="Ver Expediente" style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center;"
                        data-action="open-expediente"
                        data-name="{{ $user->nombre }} {{ $user->apellido_paterno }} {{ $user->apellido_materno }}"
                        data-alumno-nombre="{{ $user->nombre ?? '' }}"
                        data-alumno-paterno="{{ $user->apellido_paterno ?? '' }}"
                        data-alumno-materno="{{ $user->apellido_materno ?? '' }}"
                        data-alumno-curp="{{ $user->curp ?? '' }}"
                        data-email="{{ $user->email }}"
                        data-phone="{{ $user->telefono ?? 'N/A' }}"
                        data-career="{{ $user->academicProfile?->career?->name ?? ($user->fallback_career_name ?? 'Sin Carrera') }}"
                        data-semester="{{ $user->academicProfile?->semestre ?? ($user->fallback_lead_semestre ?? 1) }}"
                        data-status="{{ $user->academicProfile->status ?? 'Pendiente' }}"
                        data-matricula="{{ $user->academicProfile->matricula ?? 'No Asignada' }}"
                        data-doc-acta="{{ $user->academicProfile?->doc_acta_nacimiento ? Storage::url($user->academicProfile->doc_acta_nacimiento) : (($user->fallback_lead_doc_acta ?? null) ? Storage::url($user->fallback_lead_doc_acta) : '') }}"
                        data-doc-cert="{{ $user->academicProfile?->doc_certificado_prepa ? Storage::url($user->academicProfile->doc_certificado_prepa) : (($user->fallback_lead_doc_cert ?? null) ? Storage::url($user->fallback_lead_doc_cert) : '') }}"
                        data-doc-curp="{{ $user->academicProfile?->doc_curp ? Storage::url($user->academicProfile->doc_curp) : (($user->fallback_lead_doc_curp ?? null) ? Storage::url($user->fallback_lead_doc_curp) : '') }}"
                        data-doc-ine="{{ $user->academicProfile?->doc_ine ? Storage::url($user->academicProfile->doc_ine) : (($user->fallback_lead_doc_ine ?? null) ? Storage::url($user->fallback_lead_doc_ine) : '') }}"
                        data-doc-ficha="{{ $user->academicProfile?->doc_ficha_pago ? Storage::url($user->academicProfile->doc_ficha_pago) : (($user->fallback_lead_doc_ficha ?? null) ? Storage::url($user->fallback_lead_doc_ficha) : '') }}"
                        data-doc-xml="{{ $user->academicProfile?->doc_factura_xml ? Storage::url($user->academicProfile->doc_factura_xml) : (($user->fallback_lead_doc_xml ?? null) ? Storage::url($user->fallback_lead_doc_xml) : '') }}">
                        <img src="{{ asset('images/icons/eye-solid-full-bc8a55.svg') }}" alt="Ver">
                    </button>
                    <button type="button" class="btn-icon" title="Editar"
                        data-action="open-expediente-edit"
                        data-lead-id="{{ $user->fallback_lead_id ?? '' }}"
                        data-status="{{ $user->academicProfile->status ?? 'Pendiente' }}"
                        data-alumno-nombre="{{ $user->nombre ?? '' }}"
                        data-alumno-paterno="{{ $user->apellido_paterno ?? '' }}"
                        data-alumno-materno="{{ $user->apellido_materno ?? '' }}"
                        data-alumno-curp="{{ $user->curp ?? '' }}"
                        data-telefono1="{{ $user->telefono ?? '' }}"
                        data-alumno-email="{{ $user->email ?? '' }}"
                        data-carrera-id="{{ $user->academicProfile->career_id ?? ($user->fallback_lead_carrera_id ?? '') }}"
                        data-semestre="{{ $user->academicProfile->semestre ?? ($user->fallback_lead_semestre ?? 1) }}"
                        data-doc-acta="{{ $user->academicProfile?->doc_acta_nacimiento ? Storage::url($user->academicProfile->doc_acta_nacimiento) : (($user->fallback_lead_doc_acta ?? null) ? Storage::url($user->fallback_lead_doc_acta) : '') }}"
                        data-doc-cert="{{ $user->academicProfile?->doc_certificado_prepa ? Storage::url($user->academicProfile->doc_certificado_prepa) : (($user->fallback_lead_doc_cert ?? null) ? Storage::url($user->fallback_lead_doc_cert) : '') }}"
                        data-doc-curp="{{ $user->academicProfile?->doc_curp ? Storage::url($user->academicProfile->doc_curp) : (($user->fallback_lead_doc_curp ?? null) ? Storage::url($user->fallback_lead_doc_curp) : '') }}"
                        data-doc-ine="{{ $user->academicProfile?->doc_ine ? Storage::url($user->academicProfile->doc_ine) : (($user->fallback_lead_doc_ine ?? null) ? Storage::url($user->fallback_lead_doc_ine) : '') }}"
                        data-doc-rech-acta="{{ (!empty($user->academicProfile?->doc_acta_rechazado) || !empty($user->fallback_lead_doc_rech_acta)) ? '1' : '0' }}"
                        data-doc-rech-cert="{{ (!empty($user->academicProfile?->doc_certificado_rechazado) || !empty($user->fallback_lead_doc_rech_cert)) ? '1' : '0' }}"
                        data-doc-rech-curp="{{ (!empty($user->academicProfile?->doc_curp_rechazado) || !empty($user->fallback_lead_doc_rech_curp)) ? '1' : '0' }}"
                        data-doc-rech-ine="{{ (!empty($user->academicProfile?->doc_ine_rechazado) || !empty($user->fallback_lead_doc_rech_ine)) ? '1' : '0' }}"
                        data-doc-ficha="{{ $user->academicProfile?->doc_ficha_pago ? Storage::url($user->academicProfile->doc_ficha_pago) : (($user->fallback_lead_doc_ficha ?? null) ? Storage::url($user->fallback_lead_doc_ficha) : '') }}"
                        data-doc-xml="{{ $user->academicProfile?->doc_factura_xml ? Storage::url($user->academicProfile->doc_factura_xml) : (($user->fallback_lead_doc_xml ?? null) ? Storage::url($user->fallback_lead_doc_xml) : '') }}"
                        data-doc-rech-ficha="{{ (!empty($user->academicProfile?->doc_ficha_pago_rechazado) || !empty($user->fallback_lead_doc_rech_ficha)) ? '1' : '0' }}"
                        data-doc-rech-xml="{{ (!empty($user->academicProfile?->doc_factura_xml_rechazado) || !empty($user->fallback_lead_doc_rech_xml)) ? '1' : '0' }}"
                        style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar" width="20" height="20">
                    </button>
                @endif
                    <form action="{{ request()->routeIs('control.*') ? route('control.students.destroy', $user->id) : route('escolar.students.destroy', $user->id) }}" method="POST"
                        style="display: inline-flex; margin: 0; align-items: center;"
                        onsubmit="return confirm('¿Eliminar este alumno? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon" title="Eliminar alumno"
                            style="border:none; background:none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; padding: 0;">
                            <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar" width="20" height="20">
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @endif
@empty
    <tr>
        <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
            No se encontraron alumnos registrados ni aspirantes (CRM) con el criterio indicado.
        </td>
    </tr>
@endforelse
