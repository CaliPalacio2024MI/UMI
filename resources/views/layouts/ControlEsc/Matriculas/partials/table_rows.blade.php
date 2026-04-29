@forelse ($dataList as $student)
    <tr style="border-bottom: 1px solid #eee;">
        <td style="padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; text-align: center;">
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <strong style="color: #333; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                    {{ $student->nombre }} {{ $student->apellido_paterno }} {{ $student->apellido_materno }}
                </strong>
                <small style="color: #777; margin-top: 4px;">
                    <i class="fa-regular fa-envelope"></i> {{ $student->email }}
                </small>
            </div>
        </td>

        <td style="padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; text-align: center;">
            <span style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.35; font-size: 0.9rem; white-space: normal; word-break: normal; overflow-wrap: break-word; max-width: 220px; margin: 0 auto;">
                {{ $student->academicProfile?->career?->name ?? 'Sin Carrera Asignada' }}
            </span>
        </td>

        <td style="padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; text-align: center;">
            <span style="display: block; line-height: 1.4; font-size: 0.9rem;">
                {{ $student->academicProfile?->career?->classification?->name ?? 'Sin clasificación' }}
            </span>
        </td>

        <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
            @php
                $pagoStatus = $student->billing_status ?? 'Pendiente';
                $colorPago = $pagoStatus === 'Pagado' ? '#27ae60' : '#e74c3c';
                $bgPago = $pagoStatus === 'Pagado' ? '#eafaf1' : '#fdedec';
            @endphp
            <span style="color: {{ $colorPago }}; background-color: {{ $bgPago }}; font-weight: bold; border: 1px solid {{ $colorPago }}; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block;">
                {{ $pagoStatus }}
            </span>
        </td>

        <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
            <div style="background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px dashed #ccc; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <div style="display: flex; justify-content: center; gap: 10px; width: 100%;">
                    <i class="fa-solid fa-file-certificate" style="font-size: 1.1rem; {{ $student->doc_certificado ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="Certificado"></i>
                    <i class="fa-solid fa-id-card" style="font-size: 1.1rem; {{ $student->doc_acta ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="Acta"></i>
                    <i class="fa-solid fa-passport" style="font-size: 1.1rem; {{ $student->doc_curp ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="CURP"></i>
                </div>
                <hr style="width: 80%; border: 0; border-top: 1px solid #eee; margin: 2px 0;">

                @if($pagoStatus === 'Pagado')
                    @if($student->academicProfile && $student->academicProfile->documentoSEP_path)
                        <div style="width: 100%;">
                            <a href="javascript:void(0)"
                                onclick="openDocViewer('{{ asset('storage/' . $student->academicProfile->documentoSEP_path) }}', '{{ $student->nombre }} {{ $student->apellido_paterno }}')"
                                class="umi-btn"
                                style="background-color: #223F70; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; display: block; width: 100%; box-sizing: border-box; text-align: center; margin-bottom: 5px; font-weight: bold;">
                                <i class="fa-solid fa-eye"></i> VER DOCUMENTO
                            </a>
                            <form id="form-doc-{{ $student->id }}" action="{{ route('escolar.documentacion.upload', $student->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file"
                                    id="file-upload-{{ $student->id }}"
                                    name="documento_pdf"
                                    class="pdf-uploader"
                                    data-form-id="form-doc-{{ $student->id }}"
                                    accept="application/pdf"
                                    style="display: none;">
                                <label for="file-upload-{{ $student->id }}" style="cursor: pointer; color: #777; font-size: 0.75rem; text-decoration: underline; display: block; margin-top: 5px;">
                                    <i class="fa-solid fa-rotate"></i> Cambiar archivo
                                </label>
                            </form>
                        </div>
                    @else
                        <form id="form-doc-{{ $student->id }}" action="{{ route('escolar.documentacion.upload', $student->id) }}" method="POST" enctype="multipart/form-data" style="width: 100%;">
                            @csrf
                            <input type="file"
                                id="file-upload-{{ $student->id }}"
                                name="documento_pdf"
                                class="pdf-uploader"
                                data-form-id="form-doc-{{ $student->id }}"
                                accept="application/pdf"
                                style="display: none;">
                            <label for="file-upload-{{ $student->id }}"
                                style="cursor: pointer; background: #e0e0e0; color: #333; padding: 8px 10px; border-radius: 4px; font-size: 0.8rem; border: 1px solid #ccc; font-weight: 600; display: block; width: 100%; box-sizing: border-box; text-align: center;">
                                <i class="fa-solid fa-cloud-arrow-up"></i> SUBIR PDF
                            </label>
                            <small style="display: block; color: #999; font-size: 0.7rem; margin-top: 3px;">(Max 10MB)</small>
                        </form>
                    @endif
                @else
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #eee; font-size: 0.75rem; color: #95a5a6; text-align: center;">
                        <i class="fa-solid fa-lock" style="font-size: 1.2rem; margin-bottom: 5px; display: block;"></i>
                        Pago Requerido
                    </div>
                @endif
            </div>
        </td>

        <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
            <form id="form-matricula-{{ $student->id }}"
                action="{{ $student->academicProfile?->matricula ? route('escolar.matriculas.update', $student->id) : route('escolar.matriculas.store', $student->id) }}"
                method="POST">
                @csrf
                @if($student->academicProfile?->matricula)
                    @method('PUT')
                @endif

                @if($pagoStatus === 'Pagado')
                    <input type="text" name="matricula"
                        value="{{ $student->academicProfile?->matricula }}"
                        placeholder="Ej. 2025-001"
                        style="padding: 8px; border: 1px solid #223F70; border-radius: 4px; width: 100%; box-sizing: border-box; text-align: center; font-weight: bold; color: #223F70;">
                @else
                    <div style="background: #f8f9fa; padding: 8px; border-radius: 4px; border: 1px solid #eee; font-size: 0.8rem; color: #95a5a6;">
                        <i class="fa-solid fa-lock"></i> Pago Pendiente
                    </div>
                @endif
            </form>
        </td>

        <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
            <div style="display: flex; flex-direction: row; gap: 10px; align-items: center; justify-content: center;">
                <button type="button"
                    onclick="openMatriculaDetail({{ $student->id }})"
                    title="Ver detalle"
                    style="border: none; background: transparent; padding: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('images/icons/eye.svg') }}" alt="Ver" style="width: 22px; height: 22px;">
                </button>

                <button type="submit"
                    form="form-matricula-{{ $student->id }}"
                    title="{{ $student->academicProfile?->matricula ? 'Editar matrícula' : 'Alta matrícula' }}"
                    style="border: none; background: transparent; padding: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar" style="width: 21px; height: 21px;">
                </button>

                <form id="form-delete-matricula-{{ $student->id }}"
                    action="{{ route('escolar.matriculas.destroy', $student->id) }}"
                    method="POST"
                    style="margin: 0; display: inline-flex; align-items: center; justify-content: center;">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                        onclick="confirmDeleteMatricula({{ $student->id }}, '{{ $student->nombre }} {{ $student->apellido_paterno }}')"
                        title="Eliminar matrícula"
                        style="border: none; background: transparent; padding: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar" style="width: 22px; height: 22px;">
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" style="text-align: center; padding: 50px; color: #666; background-color: #fafafa;">
            <i class="fa-solid fa-users-slash" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
            <p style="font-size: 1.1rem; margin: 0;">No se encontraron alumnos que coincidan con la búsqueda.</p>
        </td>
    </tr>
@endforelse
