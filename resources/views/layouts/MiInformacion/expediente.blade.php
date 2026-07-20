@extends('layouts.app')

@section('title', 'Expediente - ' . session('active_institution_name'))

@section('content')
<div class="main-content" style="padding: 24px;">

    {{-- HEADER --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px;">
        <h2 style="margin:0; color:#0d2240; font-weight:700; letter-spacing:.5px;">EXPEDIENTE</h2>
        <span style="color:#6b6b6b;">¡Hola, {{ $user->nombre }}! Aquí subes la documentación que te solicita la institución.</span>
    </div>

    {{-- MENSAJES --}}
    @if(session('success'))
        <div style="background:#e6f6ec; border:1px solid #b6e2c6; color:#1e7e45; padding:12px 14px; border-radius:8px; margin-bottom:16px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fdecea; border:1px solid #e74c3c; color:#922b21; padding:12px 14px; border-radius:8px; margin-bottom:16px;">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    @php
        $totalDocs = collect($config)->flatten()->count();
    @endphp

    @if($totalDocs === 0)
        <div style="background:#fff; border:1px dashed #cfd6e0; border-radius:10px; padding:40px; text-align:center; color:#6b6b6b;">
            <i class="fa-regular fa-folder-open" style="font-size:2rem; display:block; margin-bottom:10px; color:#9aa6b6;"></i>
            Aún no hay documentos requeridos configurados para tu unidad.
        </div>
    @else
        <form method="POST" action="{{ route('MiInformacion.expediente.store') }}" enctype="multipart/form-data">
            @csrf

            @php
                $acceptMap = ['pdf' => '.pdf', 'jpg' => '.jpg,.jpeg', 'png' => '.png', 'doc' => '.doc,.docx', 'xls' => '.xls,.xlsx'];
            @endphp

            {{-- Una sección por proceso, en el orden definido --}}
            @foreach($procesos as $procesoSlug => $procesoLabel)
                @php $docs = $config[$procesoSlug] ?? null; @endphp
                @if($docs && $docs->count())
                    <div style="background:#fff; border:1px solid #e3e8ef; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 1px 3px rgba(13,34,64,.05);">
                        <h3 style="margin:0 0 14px; color:#0d2240; font-size:1.05rem; border-bottom:2px solid #f0f2f6; padding-bottom:10px;">
                            <i class="fa-solid fa-folder-open" style="color:#BC8A55;"></i> {{ $procesoLabel }}
                        </h3>

                        <div style="display:flex; flex-wrap:wrap; gap:18px;">
                            @foreach($docs as $req)
                                @php
                                    $subsDelReq = $subs[$req->id] ?? collect();
                                    $exts = $req->tiposArchivoArray();
                                    $accept = collect($exts)->map(fn ($e) => $acceptMap[$e] ?? ('.' . $e))->implode(',');
                                    $yaSubido = $subsDelReq->isNotEmpty();
                                @endphp
                                <div style="flex:1 1 45%; min-width:260px; border:1px solid #eef1f6; border-radius:10px; padding:14px; background:#fbfcfe;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:6px;">
                                        <label style="font-weight:600; color:#0d2240;">
                                            {{ $req->nombre }}
                                            @if($req->obligatorio)
                                                <span style="color:#c0392b;" title="Obligatorio">*</span>
                                            @endif
                                        </label>
                                        @if($yaSubido)
                                            <span style="background:#e6f6ec; color:#1e7e45; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:999px;">Subido</span>
                                        @elseif($req->obligatorio)
                                            <span style="background:#fdeaea; color:#c0392b; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:999px;">Pendiente</span>
                                        @else
                                            <span style="background:#eef1f6; color:#6b6b6b; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:999px;">Opcional</span>
                                        @endif
                                    </div>

                                    @if($req->descripcion)
                                        <p style="color:#6b7280; font-size:.85rem; margin:0 0 8px;">{{ $req->descripcion }}</p>
                                    @endif

                                    @if($yaSubido)
                                        <div style="margin-top:6px; display:flex; flex-direction:column; gap:4px;">
                                            @foreach($subsDelReq as $sub)
                                                <a href="{{ asset('storage/'.$sub->archivo_path) }}" target="_blank"
                                                   style="color:#BC8A55; font-size:.85rem; text-decoration:none;">
                                                    <i class="fa-regular fa-eye"></i> {{ $sub->nombre_original ?? 'Ver documento' }}
                                                </a>
                                            @endforeach
                                            <small style="color:#9aa6b6; margin-top:2px;">
                                                <i class="fa-solid fa-lock" style="font-size:.75rem;"></i> Documento ya entregado. No se puede reemplazar.
                                            </small>
                                        </div>
                                    @else
                                        <input type="file" name="expediente_docs[{{ $req->id }}][]"
                                               accept="{{ $accept }}" @if($req->cantidad > 1) multiple @endif
                                               style="width:100%; font-size:.9rem;">
                                        <small style="display:block; color:#9aa6b6; margin-top:5px;">
                                            Tipos: {{ strtoupper(implode(', ', $exts)) }} · Máx {{ $req->cantidad }} archivo(s)
                                        </small>
                                        @error("expediente_docs.{$req->id}")
                                            <p style="color:#c0392b; font-size:.83rem; margin:6px 0 0;"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                <button type="submit"
                        style="background:#0d2240; color:#fff; border:none; padding:12px 28px; border-radius:8px; font-weight:600; cursor:pointer; font-size:.95rem;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Guardar documentos
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
