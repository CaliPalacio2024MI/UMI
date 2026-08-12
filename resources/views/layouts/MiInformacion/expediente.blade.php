@extends('layouts.app')

@section('title', 'Documentación - ' . session('active_institution_name'))

@section('content')
<div class="main-content" style="padding: 24px;">

    {{-- HEADER --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px;">
        <h2 style="margin:0; color:#0d2240; font-weight:700; letter-spacing:.5px;">DOCUMENTACIÓN</h2>
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
            @php
                $hayRechazados = false;
                $nombresRechazados = [];
                foreach ($config->flatten() as $req) {
                    $subsDelReq = $subs[$req->id] ?? collect();
                    if ($subsDelReq->contains('validation_status', 'rechazado')) {
                        $hayRechazados = true;
                        $nombresRechazados[] = $req->nombre;
                    }
                }
            @endphp
            @if($hayRechazados)
                <div style="background:#fdecea; border:1px solid #e74c3c; color:#922b21; padding:14px 16px; border-radius:10px; margin-bottom:20px; font-size:.95rem;">
                    <strong><i class="fa-solid fa-triangle-exclamation"></i> Atención:</strong>
                    Control escolar rechazó {{ count($nombresRechazados) === 1 ? 'el siguiente documento' : 'los siguientes documentos' }}:
                    <strong>{{ implode(', ', $nombresRechazados) }}</strong>.
                    Sube de nuevo únicamente los archivos indicados en rojo.
                </div>
            @endif

            @foreach($procesos as $procesoSlug => $procesoLabel)
                @php $docs = $config[$procesoSlug] ?? null; @endphp
                @if($procesoSlug === 'becas' && isset($becaAsignacion) && $becaAsignacion)
                    <div style="background:#f0f5ff;border:1.5px solid #c7d7f5;border-radius:10px;padding:12px 18px;margin-bottom:14px;display:flex;align-items:center;gap:12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#223F70" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
                        <div>
                            <div style="font-size:.78rem;color:#5a7ab5;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Beca asignada</div>
                            <div style="font-size:.95rem;font-weight:700;color:#223F70;">{{ $becaAsignacion->beca->nombre ?? 'Sin nombre' }}</div>
                            @if($becaAsignacion->beca?->especificaciones)
                                <div style="font-size:.82rem;color:#5a7ab5;margin-top:2px;">{{ $becaAsignacion->beca->especificaciones }}</div>
                            @endif
                            <div style="margin-top:4px;">
                                @if($becaAsignacion->status === 'activa')
                                    <span style="background:#e6f6ec;color:#1e7e45;font-size:.72rem;font-weight:700;padding:2px 10px;border-radius:999px;border:1px solid #27ae60;">✓ Activa</span>
                                @elseif($becaAsignacion->status === 'inactiva')
                                    <span style="background:#fdeaea;color:#c0392b;font-size:.72rem;font-weight:700;padding:2px 10px;border-radius:999px;border:1px solid #e74c3c;">Inactiva</span>
                                @else
                                    <span style="background:#fff8e1;color:#b7770d;font-size:.72rem;font-weight:700;padding:2px 10px;border-radius:999px;border:1px solid #f0c040;">En revisión</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if($docs && $docs->count())
                    <div style="background:#fff; border:1px solid #e3e8ef; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 1px 3px rgba(13,34,64,.05);">
                        <h3 style="margin:0 0 14px; color:#0d2240; font-size:1.05rem; border-bottom:2px solid #f0f2f6; padding-bottom:10px;">
                            <img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" width="18" height="18" style="vertical-align:middle; margin-right:4px;"> {{ $procesoLabel }}
                        </h3>

                        @if($procesoSlug === 'becas')
                            @php
                                $todosEnviados = $docs->every(function($req) use ($subs) {
                                    $subsDelReq = $subs[$req->id] ?? collect();
                                    return $subsDelReq->isNotEmpty() && !$subsDelReq->contains('validation_status', 'rechazado');
                                });
                            @endphp
                            @if($todosEnviados)
                                <div style="background:#f0f7ff;border:1px solid #c7d7f5;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:.85rem;color:#3a5fa0;">
                                    <img src="{{ asset('images/icons/clock-solid-full.svg') }}" width="14" height="14" style="vertical-align:middle; margin-right:6px;">
                                    Todos tus documentos han sido enviados y están en espera de revisión. No puedes subir más hasta que control escolar devuelva alguno.
                                </div>
                            @endif
                        @endif

                        <div style="display:flex; flex-wrap:wrap; gap:18px;">
                            @foreach($docs as $req)
                                @php
                                    $subsDelReq = $subs[$req->id] ?? collect();
                                    $exts       = $req->tiposArchivoArray();
                                    $accept     = collect($exts)->map(fn ($e) => $acceptMap[$e] ?? ('.' . $e))->implode(',');
                                    $rechazado  = $subsDelReq->contains('validation_status', 'rechazado');
                                    $aceptado   = !$rechazado && $subsDelReq->contains('validation_status', 'aceptado');
                                    $enRevision = !$rechazado && !$aceptado && $subsDelReq->isNotEmpty();
                                    $yaSubido   = $subsDelReq->isNotEmpty();
                                    $borderColor = $rechazado ? '#e74c3c' : ($aceptado ? '#27ae60' : '#eef1f6');
                                @endphp
                                <div style="flex:1 1 45%; min-width:260px; border:1.5px solid {{ $borderColor }}; border-radius:10px; padding:14px; background:#fbfcfe;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:6px;">
                                        <label style="font-weight:600; color:#0d2240;">
                                            {{ $req->nombre }}
                                            @if($req->obligatorio)<span style="color:#c0392b;" title="Obligatorio">*</span>@endif
                                        </label>
                                        @if($rechazado)
                                            <span style="background:#fdeaea; color:#c0392b; font-size:.72rem; font-weight:700; padding:2px 10px; border-radius:999px; border:1px solid #e74c3c;">✗ Rechazado</span>
                                        @elseif($aceptado)
                                            <span style="background:#e6f6ec; color:#1e7e45; font-size:.72rem; font-weight:700; padding:2px 10px; border-radius:999px; border:1px solid #27ae60;">✓ Aceptado</span>
                                        @elseif($enRevision)
                                            <span style="background:#fff8e1; color:#b7770d; font-size:.72rem; font-weight:700; padding:2px 10px; border-radius:999px; border:1px solid #f0c040;">En revisión</span>
                                        @elseif($req->obligatorio)
                                            <span style="background:#fdeaea; color:#c0392b; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:999px;">Pendiente</span>
                                        @else
                                            <span style="background:#eef1f6; color:#6b6b6b; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:999px;">Opcional</span>
                                        @endif
                                    </div>

                                    @if($req->descripcion)
                                        <p style="color:#6b7280; font-size:.85rem; margin:0 0 8px;">{{ $req->descripcion }}</p>
                                    @endif

                                    @if($rechazado)
                                        {{-- Documento rechazado: mostrar archivo anterior y permitir re-subida --}}
                                        <p style="color:#c0392b; font-size:.83rem; font-weight:600; margin:0 0 8px;"><i class="fa-solid fa-circle-exclamation"></i> Documento rechazado — adjunta un archivo nuevo.</p>
                                        @foreach($subsDelReq as $sub)
                                            <a href="{{ asset('storage/'.$sub->archivo_path) }}" target="_blank"
                                               style="color:#BC8A55; font-size:.82rem; text-decoration:none; display:block; margin-bottom:4px;">
                                                <img src="{{ asset('images/icons/eye-solid-full-bc8a55.svg') }}" width="14" height="14" style="vertical-align:middle;"> {{ $sub->nombre_original ?? 'Ver archivo rechazado' }}
                                            </a>
                                        @endforeach
                                        <input type="file" name="expediente_docs[{{ $req->id }}][]"
                                               accept="{{ $accept }}" @if($req->cantidad > 1) multiple @endif
                                               style="width:100%; font-size:.9rem; margin-top:8px; border:1px solid #e74c3c; border-radius:4px; padding:2px;">
                                        <small style="display:block; color:#9aa6b6; margin-top:4px;">
                                            Tipos: {{ strtoupper(implode(', ', $exts)) }} · Máx {{ $req->cantidad }} archivo(s)
                                        </small>
                                        @error("expediente_docs.{$req->id}")
                                            <p style="color:#c0392b; font-size:.83rem; margin:6px 0 0;"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                                        @enderror
                                    @elseif($yaSubido)
                                        {{-- Documento en revisión o aceptado: solo mostrar link --}}
                                        <div style="margin-top:6px; display:flex; flex-direction:column; gap:4px;">
                                            @foreach($subsDelReq as $sub)
                                                <a href="{{ asset('storage/'.$sub->archivo_path) }}" target="_blank"
                                                   style="color:#BC8A55; font-size:.85rem; text-decoration:none;">
                                                    <img src="{{ asset('images/icons/eye-solid-full-bc8a55.svg') }}" width="14" height="14" style="vertical-align:middle;"> {{ $sub->nombre_original ?? 'Ver documento' }}
                                                </a>
                                            @endforeach
                                            @if($aceptado)
                                                <small style="color:#1e7e45; margin-top:2px;"><img src="{{ asset('images/icons/check.svg') }}" width="12" height="12" style="vertical-align:middle;"> Documento validado por control escolar.</small>
                                            @else
                                                <small style="color:#9aa6b6; margin-top:2px;"><img src="{{ asset('images/icons/clock-solid-full.svg') }}" width="12" height="12" style="vertical-align:middle;"> En espera de revisión por control escolar.</small>
                                            @endif
                                        </div>
                                    @else
                                        {{-- Sin documento: input normal --}}
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
