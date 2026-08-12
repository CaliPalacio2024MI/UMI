@extends('layouts.app')
@push('css')
    @vite('resources/css/ControlEsc/becas.css')
@endpush
@php use Illuminate\Support\Facades\Storage; @endphp
@section('content')
<style>
#umi-app-view { overflow-y: auto !important; height: auto !important; min-height: calc(100vh - var(--global-offset-height)); }

/* Toggle */
.fb-toggle {
    width:40px; height:22px; border-radius:999px; background:#d1d9e0;
    position:relative; cursor:pointer; transition:background .2s; flex-shrink:0; display:inline-block;
}
.fb-toggle::after {
    content:''; position:absolute; top:3px; left:3px;
    width:16px; height:16px; border-radius:50%; background:#fff;
    transition:left .2s; box-shadow:0 1px 3px rgba(0,0,0,.2);
}
.fb-toggle--on { background:#223F70; }
.fb-toggle--on::after { left:21px; }
.fb-toggle--loading { opacity:.5; pointer-events:none; }

/* Panel superior */
.fb-panel {
    background:#fff; border:1px solid #e3e8ef; border-radius:12px;
    padding:20px 24px; margin-bottom:20px;
    box-shadow:0 1px 4px rgba(34,63,112,.05);
}
.fb-panel-body {
    display:grid; grid-template-columns:1fr 260px; gap:20px;
}
.fb-panel label { font-size:.82rem; font-weight:600; color:#444; display:block; margin-bottom:5px; }
.fb-panel-right { display:flex; flex-direction:column; gap:14px; }
.fb-toggles-stack { display:flex; flex-direction:column; gap:10px; }
.fb-toggle-row { display:flex; align-items:center; justify-content:space-between; }
.fb-toggle-label { font-size:.85rem; color:#333; }

/* Filas de campos */
.fb-row {
    background:#fff; border:1px solid #e8ecf2; border-radius:8px;
    display:flex; align-items:center; gap:10px;
    padding:12px 16px; margin-bottom:8px;
    transition:box-shadow .15s;
}
.fb-row:hover { box-shadow:0 2px 8px rgba(34,63,112,.08); }
.fb-row-num { font-size:.85rem; font-weight:700; color:#BBC4D2; min-width:20px; }
.fb-row-label { flex:1; font-size:.92rem; font-weight:600; color:#223F70; }
.fb-row-tipo { font-size:.74rem; color:#9aa6b6; margin-top:2px; }
.fb-row-right { display:flex; align-items:center; gap:10px; }
.fb-order-btn {
    background:none; border:1px solid #e0e5ef; border-radius:4px;
    width:22px; height:22px; display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#bbb; font-size:.65rem; transition:background .12s;
}
.fb-order-btn:hover { background:#f0f3fa; color:#223F70; }
.fb-add-btn {
    width:22px; height:22px; border-radius:50%; border:2px solid #BBC4D2;
    background:none; cursor:pointer; color:#BBC4D2; font-size:.9rem; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
    transition:border-color .15s, color .15s;
}
.fb-add-btn:hover { border-color:#223F70; color:#223F70; }
</style>

<div class="container">
<div id="umi-app-view">
    <div class="content-header">
        <div class="content-title"><h3>Formulario de Registro</h3></div>
        <div class="content-actions">
            <button type="button" class="becas-btn becas-btn--primary" onclick="openPreview()" style="display:flex;align-items:center;gap:8px;">
                <img src="{{ asset('images/icons/eye.svg') }}" alt="" width="15" height="15" style="filter:brightness(10);">
                Previsualizar
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="becas-alert becas-alert--ok">{{ session('success') }}</div>
    @endif

    {{-- ── Campos base del formulario público ── --}}
    <style>
    .fbc-section { margin-top:40px; padding-top:32px; border-top:2px solid #f0f3fa; }
    .fbc-header { display:flex; align-items:center; gap:12px; margin-bottom:22px; }
    .fbc-header-icon {
        width:36px; height:36px; border-radius:9px;
        background:linear-gradient(135deg,#223F70 0%,#2d5499 100%);
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .fbc-header-icon svg { color:#fff; }
    .fbc-header-text h4 { margin:0; font-size:.97rem; font-weight:700; color:#223F70; }
    .fbc-header-text p  { margin:2px 0 0; font-size:.78rem; color:#9aa6b6; }

    .fbc-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    @media(max-width:680px){ .fbc-grid { grid-template-columns:1fr; } }

    .fbc-card {
        background:#fff; border:1px solid #e3e8ef; border-radius:12px;
        overflow:hidden; box-shadow:0 1px 4px rgba(34,63,112,.04);
    }
    .fbc-card-head {
        display:flex; align-items:center; gap:10px;
        padding:13px 18px; border-bottom:1px solid #f0f3fa;
        background:#fafbfd;
    }
    .fbc-card-head-dot {
        width:8px; height:8px; border-radius:50%;
        background:linear-gradient(135deg,#223F70,#B08955);
        flex-shrink:0;
    }
    .fbc-card-head span {
        font-size:.82rem; font-weight:700; color:#223F70; letter-spacing:.01em;
    }
    .fbc-card-head .fbc-count {
        margin-left:auto; background:#eef1f8; color:#223F70;
        font-size:.7rem; font-weight:700; padding:2px 8px; border-radius:999px;
    }
    .fbc-field-row {
        display:flex; align-items:center; gap:12px;
        padding:11px 16px; border-bottom:1px solid #f7f8fb;
        transition:background .12s;
    }
    .fbc-field-row:last-child { border-bottom:none; }
    .fbc-field-row:hover { background:#fafbfd; }
    .fbc-field-row.is-locked { opacity:.7; }
    .fbc-field-icon {
        width:28px; height:28px; border-radius:7px;
        background:#eef1f8; display:flex; align-items:center;
        justify-content:center; flex-shrink:0; color:#223F70;
    }
    .fbc-field-info { flex:1; min-width:0; }
    .fbc-field-label {
        font-size:.86rem; font-weight:600; color:#1e3560;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .fbc-field-key {
        font-size:.7rem; color:#b0baca; font-family:monospace;
        margin-top:1px; display:block;
    }
    .fbc-lock-chip {
        display:inline-flex; align-items:center; gap:3px;
        background:#f0f3fa; color:#7a8aaa; border-radius:5px;
        font-size:.67rem; font-weight:700; padding:2px 7px;
        letter-spacing:.03em; flex-shrink:0;
    }
    .fbc-toggles {
        display:flex; align-items:center; gap:8px;
        background:#f7f8fb; border:1px solid #eaecf2;
        border-radius:8px; padding:5px 10px;
    }
    .fbc-toggle-pair { display:flex; align-items:center; gap:5px; }
    .fbc-toggle-pair + .fbc-toggle-pair {
        padding-left:8px;
        border-left:1px solid #e0e5ef;
    }
    .fbc-toggle-label { font-size:.67rem; font-weight:600; color:#9aa6b6; letter-spacing:.04em; text-transform:uppercase; }

    .fbc-edit-btn {
        background:none; border:none; cursor:pointer; padding:4px;
        color:#BBC4D2; border-radius:5px; display:flex; align-items:center;
        transition:color .15s, background .15s; flex-shrink:0;
    }
    .fbc-edit-btn:hover { color:#223F70; background:#eef1f8; }
    .fbc-label-input {
        font-size:.86rem; font-weight:600; color:#1e3560;
        border:1.5px solid #223F70; border-radius:6px;
        padding:2px 8px; outline:none; width:100%; background:#fff;
        box-shadow:0 0 0 3px rgba(34,63,112,.08);
    }
    </style>

    @php
    $fieldIcons = [
        'tutor_curp'     => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="9" x2="17" y2="9"/><line x1="7" y1="13" x2="13" y2="13"/></svg>',
        'alumno_curp'    => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="9" x2="17" y2="9"/><line x1="7" y1="13" x2="13" y2="13"/></svg>',
        'tutor_nombre'   => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
        'tutor_paterno'  => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
        'tutor_materno'  => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
        'alumno_nombre'  => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
        'alumno_paterno' => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
        'alumno_materno' => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
        'telefono1'      => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3.08 4.18 2 2 0 0 1 5.07 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L9.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'telefono2'      => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3.08 4.18 2 2 0 0 1 5.07 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L9.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'tutor_email'    => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>',
        'nivel_educativo'=> '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
        'carrera_id'     => '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
    ];
    $lockIcon = '<svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
    @endphp

    <div class="fbc-section">
        <div class="fbc-header">
            <div class="fbc-header-icon">
                <svg width="17" height="17" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07M8.46 8.46a5 5 0 0 0 0 7.07"/>
                </svg>
            </div>
            <div class="fbc-header-text">
                <h4>Campos base del formulario público</h4>
                <p>Controla la visibilidad y obligatoriedad de cada campo. Los campos con candado no pueden desactivarse.</p>
            </div>
        </div>

        @php
    $customMap = [];
    foreach($campos as $c) {
        if ($c->seccion) {
            $customMap[$c->seccion][$c->despues_de ?? ''][] = $c;
        }
    }
    $tipoIcono = [
        'text'     => '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="14" y2="18"/></svg>',
        'textarea' => '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="13" y2="16"/></svg>',
        'select'   => '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="8,10 12,14 16,10"/></svg>',
        'file'     => '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13,2 13,9 20,9"/></svg>',
        'date'     => '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'checkbox' => '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9,12 11,14 15,10"/></svg>',
        'email'    => '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>',
        'tel'      => '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3.08 4.18 2 2 0 0 1 5.07 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L9.09 9.91a16 16 0 0 0 6 6z"/></svg>',
    ];
    @endphp
    <style>
    .fbc-custom-row {
        display:flex; align-items:center; gap:10px;
        padding:9px 16px; border-bottom:1px solid #f7f8fb;
        background:#fafbfd; border-left:3px solid #B08955;
        transition:background .12s;
    }
    .fbc-custom-row:hover { background:#f5f3ef; }
    .fbc-custom-icon {
        width:24px; height:24px; border-radius:6px;
        background:#f0ebe2; display:flex; align-items:center;
        justify-content:center; flex-shrink:0; color:#B08955;
    }
    .fbc-insert-row {
        display:flex; align-items:center; justify-content:center;
        padding:3px 16px;
        border-bottom:1px solid #f7f8fb;
    }
    .fbc-insert-btn {
        display:inline-flex; align-items:center; gap:4px;
        background:none; border:none; cursor:pointer;
        color:#BBC4D2; font-size:.72rem; font-weight:600;
        padding:3px 8px; border-radius:5px;
        transition:color .15s, background .15s;
    }
    .fbc-insert-btn:hover { color:#B08955; background:#f5f3ef; }
    </style>

    <div class="fbc-grid">
        @foreach(['postulante' => 'Datos del postulante', 'tutor' => 'Tutor o padre interesado'] as $seccionKey => $titulo)
        @if(isset($camposBase[$seccionKey]))
        <div class="fbc-card">
            <div class="fbc-card-head">
                <div class="fbc-card-head-dot"></div>
                <span>{{ $titulo }}</span>
                <span class="fbc-count">{{ $camposBase[$seccionKey]->count() + collect($customMap[$seccionKey] ?? [])->flatten(1)->count() }} campos</span>
            </div>

            @foreach($camposBase[$seccionKey] as $cb)
            {{-- Campo base --}}
            <div class="fbc-field-row {{ $cb->siempre_activo ? 'is-locked' : '' }}">
                <div class="fbc-field-icon">
                    {!! $fieldIcons[$cb->campo] ?? '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>' !!}
                </div>
                <div class="fbc-field-info">
                    <div class="fbc-field-label" id="lbl-{{ $cb->campo }}">{{ $cb->etiqueta }}</div>
                    <code class="fbc-field-key">{{ $cb->campo }}</code>
                </div>
                @if($cb->siempre_activo)
                    <span class="fbc-lock-chip">{!! $lockIcon !!} fijo</span>
                @else
                <button class="fbc-edit-btn" type="button"
                        onclick="editBaseLabel('{{ $cb->campo }}', this)"
                        title="Renombrar etiqueta">
                    <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar" width="13" height="13">
                </button>
                <div class="fbc-toggles">
                    <div class="fbc-toggle-pair">
                        <span class="fbc-toggle-label">Visible</span>
                        <div class="fb-toggle {{ $cb->activo ? 'fb-toggle--on' : '' }}"
                             data-campo="{{ $cb->campo }}" data-field="activo"
                             onclick="toggleBase(this)" title="Visible en formulario"></div>
                    </div>
                    <div class="fbc-toggle-pair">
                        <span class="fbc-toggle-label">Requerido</span>
                        <div class="fb-toggle {{ $cb->obligatorio ? 'fb-toggle--on' : '' }}"
                             data-campo="{{ $cb->campo }}" data-field="obligatorio"
                             onclick="toggleBase(this)" title="Obligatorio"></div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Campos personalizados anclados después de este campo base --}}
            @foreach($customMap[$seccionKey][$cb->campo] ?? [] as $cc)
            <div class="fbc-custom-row">
                <div class="fbc-custom-icon">
                    {!! $tipoIcono[$cc->tipo] ?? $tipoIcono['text'] !!}
                </div>
                <div class="fbc-field-info" style="flex:1;min-width:0;">
                    <div class="fbc-field-label" style="font-size:.84rem;">{{ $cc->etiqueta }}</div>
                    <code class="fbc-field-key">{{ $cc->nombre_campo }} · {{ ['text'=>'Texto','select'=>'Selección','file'=>'Archivo','date'=>'Fecha','textarea'=>'Texto largo','checkbox'=>'Casilla','email'=>'Email','tel'=>'Teléfono'][$cc->tipo] ?? $cc->tipo }}</code>
                </div>
                <div class="fb-toggle {{ $cc->activo ? 'fb-toggle--on' : '' }}"
                     data-id="{{ $cc->id }}" data-field="activo"
                     onclick="toggleCampo(this)" title="Activo" style="flex-shrink:0;"></div>
                <button class="fbc-edit-btn" type="button"
                        onclick="loadEditCampo({{ $cc->id }})" title="Editar">
                    <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar" width="13" height="13">
                </button>
                <button class="fbc-edit-btn" type="button" style="color:#e74c3c;"
                        onclick="openDeleteModal('{{ route('crm.formulario.destroy', $cc->id) }}', '{{ addslashes($cc->etiqueta) }}')" title="Eliminar">
                    <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar" width="13" height="13">
                </button>
            </div>
            @endforeach

            {{-- Botón insertar después de este campo --}}
            <div class="fbc-insert-row">
                <button class="fbc-insert-btn" type="button"
                        onclick="openCampoModal('{{ $seccionKey }}', '{{ $cb->campo }}', '{{ addslashes($cb->etiqueta) }}')">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Agregar campo aquí
                </button>
            </div>
            @endforeach

        </div>
        @endif
        @endforeach
    </div>
    </div>

    {{-- Campos sin sección asignada (legacy) --}}
    @php $sinSeccion = $campos->whereNull('seccion'); @endphp
    @if($sinSeccion->isNotEmpty())
    <div style="margin-top:20px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;">
        <div style="font-size:.8rem;font-weight:700;color:#92400e;margin-bottom:10px;">
            ⚠ Campos sin sección asignada — no se muestran en el formulario hasta que los edites y les asignes una sección.
        </div>
        @foreach($sinSeccion as $cc)
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #fde68a;">
            <span style="flex:1;font-size:.85rem;font-weight:600;color:#78350f;">{{ $cc->etiqueta }}</span>
            <button class="fbc-edit-btn" type="button" onclick="loadEditCampo({{ $cc->id }})" title="Asignar sección">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </button>
            <button class="fbc-edit-btn" type="button" style="color:#e74c3c;"
                    onclick="openDeleteModal('{{ route('crm.formulario.destroy', $cc->id) }}', '{{ addslashes($cc->etiqueta) }}')" title="Eliminar">
                <img src="{{ asset('images/icons/delete.svg') }}" alt="Eliminar" width="13" height="13">
            </button>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Modal agregar / editar campo ── --}}
    <div id="campo-overlay" style="
        display:none; position:fixed; inset:0; z-index:9998;
        background:rgba(10,18,36,.45); backdrop-filter:blur(2px);
        align-items:center; justify-content:center; padding:16px;
    " onclick="if(event.target===this)cancelEdit()">
        <div style="background:#fff;border-radius:14px;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 8px 40px rgba(0,0,0,.18);">
            {{-- Header del modal --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 22px;border-bottom:1px solid #f0f3fa;">
                <span id="campo-modal-title" style="font-size:.97rem;font-weight:700;color:#223F70;">Agregar campo</span>
                <button onclick="cancelEdit()" style="background:rgba(34,63,112,.07);border:none;cursor:pointer;color:#223F70;font-size:1rem;padding:4px 10px;border-radius:6px;line-height:1;" title="Cerrar">✕</button>
            </div>

            {{-- Cuerpo --}}
            <div style="padding:20px 22px;">
                <form id="campoForm" method="POST" action="{{ route('crm.formulario.store') }}">
                    @csrf
                    <input type="hidden" id="campo_method"          name="_method"      value="">
                    <input type="hidden" id="campo_nombre_campo"    name="nombre_campo" value="">
                    <input type="hidden" id="campo_orden"           name="orden"        value="0">
                    <input type="hidden" id="campo_obligatorio_val" name="obligatorio"  value="1">
                    <input type="hidden" id="campo_activo_val"      name="activo"       value="1">

                    {{-- Sección y posición --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #f0f3fa;">
                        <div>
                            <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:5px;">
                                Sección: <span style="color:#c0392b">*</span>
                            </label>
                            <select id="campo_seccion" name="seccion" class="becas-input" onchange="updateDespuesDe()" required>
                                <option value="">— Elige sección —</option>
                                <option value="tutor">Tutor o padre interesado</option>
                                <option value="postulante">Datos del postulante</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:5px;">
                                Insertar después de:
                            </label>
                            <select id="campo_despues_de" name="despues_de" class="becas-input">
                                <option value="">— Primero en la sección —</option>
                            </select>
                        </div>
                    </div>

                    {{-- Pregunta predeterminada --}}
                    <div style="margin-bottom:16px;">
                        <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:5px;">
                            Usar pregunta predeterminada <small style="color:#999;">(opcional)</small>
                        </label>
                        <select id="pregunta_preset" class="becas-input" onchange="applyPreset(this.value)">
                            <option value="">— Selecciona una opción —</option>
                            <option value="nombre_completo|Nombre completo|text">Nombre completo</option>
                            <option value="fecha_nacimiento|Fecha de nacimiento|date">Fecha de nacimiento</option>
                            <option value="genero|Género|select|Masculino\nFemenino\nOtro\nPrefiero no decir">Género</option>
                            <option value="edad|Edad en años|text">Edad en años</option>
                            <option value="estado_civil|Estado civil|select|Soltero/a\nCasado/a\nDivorciado/a\nViudo/a">Estado civil</option>
                            <option value="ocupacion|Ocupación|text">Ocupación</option>
                            <option value="municipio|Municipio de residencia|text">Municipio de residencia</option>
                            <option value="como_se_entero|¿Cómo se enteró de nosotros?|select|Redes sociales\nRecomendación\nPublicidad\nRadio o televisión\nOtro">¿Cómo se enteró?</option>
                        </select>
                    </div>

                    <div class="fb-panel-body">
                        {{-- Izquierda --}}
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <div>
                                <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:5px;">
                                    Pregunta: <span style="color:#c0392b">*</span>
                                </label>
                                <input id="campo_etiqueta" class="becas-input" type="text" name="etiqueta"
                                       placeholder="Escribe la pregunta o etiqueta del campo" required>
                            </div>
                            <div>
                                <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:5px;">
                                    Placeholder <small style="color:#999;">(opcional)</small>
                                </label>
                                <input id="campo_placeholder" class="becas-input" type="text" name="placeholder"
                                       placeholder="Texto de ayuda dentro del campo">
                            </div>
                            <div id="opciones_wrap" style="display:none;">
                                <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:5px;">
                                    Opciones <small style="color:#999;">(una por línea)</small>
                                </label>
                                <textarea id="campo_opciones" class="becas-input" name="opciones" rows="3"
                                          placeholder="Opción 1&#10;Opción 2&#10;Opción 3"></textarea>
                            </div>
                        </div>

                        {{-- Derecha --}}
                        <div class="fb-panel-right">
                            <div>
                                <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:5px;">
                                    Tipo de respuesta: <span style="color:#c0392b">*</span>
                                </label>
                                <select id="campo_tipo" class="becas-input" name="tipo" required onchange="toggleOpciones(true)">
                                    <option value="text">Respuesta breve en texto</option>
                                    <option value="textarea">Respuesta larga</option>
                                    <option value="select">Selección (lista)</option>
                                    <option value="email">Correo electrónico</option>
                                    <option value="tel">Teléfono</option>
                                    <option value="date">Fecha</option>
                                    <option value="file">Archivo</option>
                                    <option value="checkbox">Casilla (sí / no)</option>
                                </select>
                            </div>
                            <div class="fb-toggles-stack">
                                @foreach([
                                    ['obligatorio','Obligatoria', true],
                                    ['activo',     'Activo',      true],
                                ] as [$key, $label, $default])
                                <div class="fb-toggle-row">
                                    <span class="fb-toggle-label">{{ $label }}</span>
                                    <div class="fb-toggle {{ $default ? 'fb-toggle--on' : '' }}"
                                         id="toggle_{{ $key }}_ui"
                                         onclick="panelToggle('{{ $key }}')"></div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid #f0f3fa;">
                        <button type="button" onclick="cancelEdit()" class="becas-btn">Cancelar</button>
                        <button type="submit" class="becas-btn becas-btn--primary" id="submitBtn">Registrar pregunta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</div>
</div>

{{-- ── Modal Eliminar campo ── --}}
<form id="deleteForm" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

<div id="delete-overlay" style="
    display:none; position:fixed; inset:0; z-index:9998;
    background:rgba(10,18,36,.45); backdrop-filter:blur(2px);
    align-items:center; justify-content:center; padding:16px;
" onclick="if(event.target===this)closeDeleteModal()">
    <div style="background:#fff;border-radius:10px;width:100%;max-width:320px;padding:20px 22px;box-shadow:0 8px 32px rgba(0,0,0,.18);">
        <p style="font-size:.9rem;color:#1a202c;margin:0 0 16px;font-weight:600;">¿Eliminar "<span id="delete-campo-nombre"></span>"?</p>
        <div style="display:flex;gap:8px;justify-content:flex-end;">
            <button type="button" onclick="closeDeleteModal()"
                style="padding:7px 16px;border-radius:7px;border:1.5px solid #d1d5db;background:#fff;font-size:.83rem;font-weight:600;color:#374151;cursor:pointer;">
                Cancelar
            </button>
            <button type="button" onclick="confirmDelete()"
                style="padding:7px 16px;border-radius:7px;border:none;background:#dc2626;color:#fff;font-size:.83rem;font-weight:600;cursor:pointer;">
                Eliminar
            </button>
        </div>
    </div>
</div>

{{-- ── Modal Previsualización ── --}}
<style>
/* Estilos del formulario público, aislados dentro del modal */
.pv-wrap *, .pv-wrap *::before, .pv-wrap *::after { box-sizing: border-box; }
.pv-wrap {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(160deg, #f0f4f8 0%, #e8eef5 100%);
    padding: 24px 16px 28px;
}
.pv-wrap .pv-header {
    background-color: #0d1e38;
    padding: 10px 24px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
}
.pv-wrap .pv-header img { height: 40px; }
.pv-wrap .pv-header .pv-header-text {
    color: #fff; font-size: .78rem; font-weight: 700; letter-spacing: .04em;
}
.pv-wrap .enrollment-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px 30px;
    width: 100%;
    max-width: 580px;
    margin: 0 auto;
    box-shadow: 0 8px 32px rgba(13,30,56,.10);
    border-top: 4px solid #B08955;
}
.pv-wrap .form-section { margin-bottom: 20px; }
.pv-wrap .form-section + .form-section {
    padding-top: 18px;
    border-top: 1px solid #e4e9f0;
}
.pv-wrap .section-title {
    font-size: 13px; font-weight: 700; color: #0d1e38;
    margin: 0 0 14px; padding-left: 10px;
    border-left: 3px solid #B08955; line-height: 1.4;
}
.pv-wrap .form-group { display: flex; flex-direction: column; margin-bottom: 10px; }
.pv-wrap .form-label {
    font-size: 10px; font-weight: 600; color: #4a5568;
    margin-bottom: 4px; letter-spacing: .04em; text-transform: uppercase;
}
.pv-wrap .form-control {
    width: 100%; padding: 8px 12px;
    border-radius: 7px; border: 1.5px solid #d1d9e6;
    background: #f8fafc; font-family: 'Poppins', sans-serif;
    font-size: 12px; color: #1a202c; outline: none;
    appearance: none; -webkit-appearance: none;
}
.pv-wrap select.form-control {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%234a5568' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 32px; cursor: pointer;
}
.pv-wrap .form-control::placeholder { color: #a0aec0; }
.pv-wrap .split-inputs {
    display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: end;
}
.pv-wrap .split-inputs > .phone-field { display: flex; flex-direction: column; }
.pv-wrap .split-label {
    font-size: 10px; font-weight: 600; color: #4a5568;
    margin-bottom: 4px; letter-spacing: .04em; text-transform: uppercase; display: block;
}
.pv-wrap .form-actions { display: flex; justify-content: center; margin-top: 20px; }
.pv-wrap .btn-submit {
    background: #B08955; color: #fff; border: none;
    padding: 10px 52px; border-radius: 7px;
    font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600;
    cursor: not-allowed; opacity: .85;
    box-shadow: 0 4px 14px rgba(176,137,85,.35);
}
</style>

<div id="preview-overlay" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(10,18,36,.6); backdrop-filter:blur(4px);
    align-items:center; justify-content:center; padding:20px 16px;
" onclick="if(event.target===this)closePreview()">
    <div style="width:100%;max-width:560px;max-height:88vh;border-radius:14px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.4);display:flex;flex-direction:column;">

        {{-- Barra superior del modal --}}
        <div style="background:#1a2a47;padding:11px 18px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#B08955" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span style="color:#fff;font-weight:600;font-size:.88rem;font-family:'Poppins',sans-serif;">Vista previa — Formulario de Registro</span>
                <span style="background:#B08955;color:#fff;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:999px;letter-spacing:.05em;">SOLO CAMPOS ACTIVOS</span>
            </div>
            <button onclick="closePreview()" style="background:rgba(255,255,255,.12);border:none;cursor:pointer;color:#fff;font-size:1rem;padding:4px 10px;border-radius:6px;line-height:1;" title="Cerrar">✕</button>
        </div>

        {{-- Cuerpo: réplica exacta del formulario público --}}
        <div class="pv-wrap" style="overflow-y:auto;flex:1;">
            {{-- Mini-header con logo --}}
            <div class="pv-header" style="margin:-40px -20px 32px;padding:14px 32px;">
                <img src="{{ asset('images/uhta-logo.png') }}" alt="UMI" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <span class="pv-header-text" style="display:none;">UMI · Universidad Mundo Imperial</span>
            </div>

            <div class="enrollment-card">
                {{-- Secciones base (tutor y postulante) — renderizadas dinámicamente por JS --}}
                <div id="preview-tutor-section"></div>
                <div id="preview-postulante-section"></div>

                {{-- Sección campos dinámicos --}}
                <div id="preview-dynamic-section" class="form-section" style="display:none;">
                    <div id="preview-campos-container"></div>
                </div>

                <div class="form-actions">
                    <button class="btn-submit" disabled>Registrar</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

const PANEL_TOGGLES = {
    obligatorio: true, activo: true
};

function panelToggle(key) {
    PANEL_TOGGLES[key] = !PANEL_TOGGLES[key];
    const ui = document.getElementById(`toggle_${key}_ui`);
    ui.classList.toggle('fb-toggle--on', PANEL_TOGGLES[key]);
    document.getElementById(`campo_${key}_val`).value = PANEL_TOGGLES[key] ? '1' : '0';
}

function setPanelToggle(key, value) {
    PANEL_TOGGLES[key] = !!value;
    document.getElementById(`toggle_${key}_ui`).classList.toggle('fb-toggle--on', !!value);
    document.getElementById(`campo_${key}_val`).value = value ? '1' : '0';
}

function toggleOpciones(fillDefaults = false) {
    const tipo = document.getElementById('campo_tipo').value;
    const wrap = document.getElementById('opciones_wrap');
    wrap.style.display = tipo === 'select' ? 'block' : 'none';
    if (tipo === 'select' && fillDefaults) {
        const f = document.getElementById('campo_opciones');
        if (!f.value.trim()) f.value = 'Opción 1\nOpción 2\nOpción 3';
    }
}

function applyPreset(val) {
    if (!val) return;
    const parts = val.split('|');
    const slug = parts[0], etiq = parts[1], tipo = parts[2], opts = parts[3] || '';
    document.getElementById('campo_etiqueta').value = etiq;
    document.getElementById('campo_nombre_campo').value = slug;
    document.getElementById('campo_tipo').value = tipo;
    document.getElementById('campo_opciones').value = opts.replace(/\\n/g, '\n');
    toggleOpciones(false);
    document.getElementById('pregunta_preset').value = '';
}

document.getElementById('campo_etiqueta').addEventListener('input', function () {
    if (document.getElementById('campo_method').value === 'PUT') return;
    const slug = this.value.toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
    document.getElementById('campo_nombre_campo').value = slug;
});

async function loadEditCampo(id) {
    const res = await fetch(`/crm/formulario/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    if (!res.ok) { alert('No se pudo cargar el campo.'); return; }
    const row = (await res.json()).data;

    document.getElementById('campoForm').action = `/crm/formulario/${id}`;
    document.getElementById('campo_method').value = 'PUT';
    document.getElementById('campo_etiqueta').value = row.etiqueta || '';
    document.getElementById('campo_nombre_campo').value = row.nombre_campo || '';
    document.getElementById('campo_tipo').value = row.tipo || 'text';
    document.getElementById('campo_opciones').value = row.opciones || '';
    document.getElementById('campo_placeholder').value = row.placeholder || '';
    document.getElementById('campo_orden').value = row.orden ?? 0;

    ['obligatorio','activo'].forEach(k => setPanelToggle(k, !!row[k]));

    const secSelect = document.getElementById('campo_seccion');
    secSelect.value = row.seccion || '';
    updateDespuesDe(row.despues_de || '');

    toggleOpciones();
    document.getElementById('submitBtn').textContent = 'Guardar cambios';
    document.getElementById('campo-modal-title').textContent = 'Editar campo';

    const overlay = document.getElementById('campo-overlay');
    if (overlay.parentElement !== document.body) document.body.appendChild(overlay);
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

const BASE_CAMPOS_POR_SECCION = {
    tutor: [
        { key: 'tutor_curp',    label: 'CURP' },
        { key: 'tutor_nombre',  label: 'Nombre(s)' },
        { key: 'tutor_paterno', label: 'Apellido paterno' },
        { key: 'tutor_materno', label: 'Apellido materno' },
        { key: 'telefono1',     label: 'Teléfonos' },
        { key: 'tutor_email',   label: 'Correo electrónico' },
    ],
    postulante: [
        { key: 'alumno_curp',    label: 'CURP' },
        { key: 'alumno_nombre',  label: 'Nombre(s)' },
        { key: 'alumno_paterno', label: 'Apellido paterno' },
        { key: 'alumno_materno', label: 'Apellido materno' },
        { key: 'nivel_educativo',label: 'Nivel educativo' },
        { key: 'carrera_id',     label: 'Plan de estudio / Carrera' },
    ]
};

function updateDespuesDe(preselect = '') {
    const seccion = document.getElementById('campo_seccion').value;
    const sel = document.getElementById('campo_despues_de');
    sel.innerHTML = '<option value="">— Al inicio de la sección —</option>';
    (BASE_CAMPOS_POR_SECCION[seccion] || []).forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.key;
        opt.textContent = 'Después de: ' + c.label;
        if (c.key === preselect) opt.selected = true;
        sel.appendChild(opt);
    });
}

function openCampoModal(seccion = '', despuesDe = '', labelDespuesDe = '') {
    document.getElementById('campoForm').reset();
    document.getElementById('campoForm').action = '{{ route('crm.formulario.store') }}';
    document.getElementById('campo_method').value = '';
    document.getElementById('campo_nombre_campo').value = '';
    document.getElementById('submitBtn').textContent = 'Registrar pregunta';
    document.getElementById('campo-modal-title').textContent = 'Agregar campo';
    ['obligatorio','activo'].forEach(k => setPanelToggle(k, true));
    toggleOpciones();

    // Pre-seleccionar sección y posición
    const secSelect = document.getElementById('campo_seccion');
    secSelect.value = seccion;
    updateDespuesDe(despuesDe);

    const overlay = document.getElementById('campo-overlay');
    if (overlay.parentElement !== document.body) document.body.appendChild(overlay);
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('campo_etiqueta').focus(), 80);
}

function cancelEdit() {
    document.getElementById('campo-overlay').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('campoForm').reset();
    document.getElementById('campoForm').action = '{{ route('crm.formulario.store') }}';
    document.getElementById('campo_method').value = '';
    document.getElementById('campo_nombre_campo').value = '';
    document.getElementById('submitBtn').textContent = 'Registrar pregunta';
    ['obligatorio','activo'].forEach(k => setPanelToggle(k, true));
    toggleOpciones();
}

document.getElementById('campoForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = e.currentTarget;
    const etiq = document.getElementById('campo_etiqueta').value;
    const nc = document.getElementById('campo_nombre_campo');
    if (!nc.value.trim()) {
        nc.value = etiq.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
    }
    const formData = new FormData(form);
    const btn = document.getElementById('submitBtn');
    const prev = btn.textContent;
    btn.disabled = true; btn.textContent = 'Guardando...';
    try {
        const res = await fetch(form.action, {
            method: 'POST', body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) throw new Error(data.message || 'No se pudo guardar.');
        document.getElementById('campo-overlay').style.display = 'none';
        document.body.style.overflow = '';
        window.location.reload();
    } catch (err) {
        alert(err.message || 'Error al guardar.');
        btn.disabled = false; btn.textContent = prev;
    }
});

async function toggleCampo(el) {
    el.classList.add('fb-toggle--loading');
    try {
        const res = await fetch(`/crm/formulario/${el.dataset.id}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ field: el.dataset.field })
        });
        const data = await res.json();
        if (data.ok) {
            el.classList.toggle('fb-toggle--on', data.value);
            // Sincronizar PREVIEW_CAMPOS para que openPreview() refleje el cambio
            if (el.dataset.field === 'activo') {
                const idx = PREVIEW_CAMPOS.findIndex(c => c.id == el.dataset.id);
                if (idx !== -1) PREVIEW_CAMPOS[idx].activo = data.value;
            }
        }
    } catch (e) {}
    el.classList.remove('fb-toggle--loading');
}

async function reorderCampo(id, direction) {
    await fetch(`/crm/formulario/${id}/reorder`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ direction })
    });
    window.location.reload();
}

// ── Previsualización ──
const PREVIEW_CAMPOS = @json($campos->values());
const PREVIEW_BASE_CAMPOS = @json($camposBase->flatten()->values());

function buildBaseSection(seccion, titulo) {
    const campos = PREVIEW_BASE_CAMPOS.filter(c => c.seccion === seccion && c.activo);
    if (campos.length === 0) return '';

    // Campos dinámicos asignados a esta sección (activos)
    const dinamicos = PREVIEW_CAMPOS.filter(c => c.activo && c.seccion === seccion);

    let html = `<div class="form-section"><h2 class="section-title">${titulo}</h2>`;

    // Dinámicos que van al inicio de la sección (sin despues_de)
    dinamicos.filter(c => !c.despues_de).forEach(c => { html += buildPreviewField(c); });

    const tel1 = campos.find(c => c.campo === 'telefono1');
    const tel2 = campos.find(c => c.campo === 'telefono2');

    for (const campo of campos) {
        if (campo.campo === 'telefono2') continue; // se maneja junto con telefono1

        if (campo.campo === 'telefono1') {
            if (tel1 && tel2) {
                html += `<div class="form-group">
                    <div class="form-control split-inputs" style="padding:0;border:none;background:none;">
                        <div class="phone-field">
                            <label class="split-label">${tel1.etiqueta}:</label>
                            <input type="text" class="form-control" placeholder="10 dígitos" disabled>
                        </div>
                        <div class="phone-field">
                            <label class="split-label">${tel2.etiqueta}:</label>
                            <input type="text" class="form-control" placeholder="10 dígitos" disabled>
                        </div>
                    </div>
                </div>`;
            } else {
                html += `<div class="form-group">
                    <label class="form-label">${tel1.etiqueta}:</label>
                    <input type="text" class="form-control" placeholder="10 dígitos" disabled>
                </div>`;
            }
        } else if (campo.campo === 'nivel_educativo') {
            html += `<div class="form-group">
                <label class="form-label">${campo.etiqueta}:</label>
                <select class="form-control" disabled><option>Seleccione un nivel</option></select>
            </div>`;
        } else if (campo.campo === 'carrera_id') {
            html += `<div class="form-group">
                <label class="form-label">${campo.etiqueta}:</label>
                <select class="form-control" disabled><option>Seleccione una carrera</option></select>
            </div>`;
        } else {
            const inputType = campo.campo.includes('email') ? 'email' : 'text';
            const ph = campo.campo.includes('curp')
                ? (seccion === 'tutor' ? 'CURP del tutor' : 'CURP del postulante')
                : campo.etiqueta;
            html += `<div class="form-group">
                <label class="form-label">${campo.etiqueta}:</label>
                <input type="${inputType}" class="form-control" placeholder="${ph}" disabled>
            </div>`;
        }

        // Dinámicos que van después de este campo base
        dinamicos.filter(c => c.despues_de === campo.campo).forEach(c => { html += buildPreviewField(c); });
    }

    html += '</div>';
    return html;
}

function buildPreviewField(campo) {
    const req = campo.obligatorio ? ' <span style="color:#c0392b">*</span>' : '';
    const ph  = campo.placeholder || '';
    let input = '';

    if (campo.tipo === 'select') {
        const opts = (campo.opciones || '').split('\n')
            .map(o => o.trim()).filter(Boolean)
            .map(o => `<option value="${o}">${o}</option>`).join('');
        input = `<select class="form-control">
            <option value="">${ph || 'Seleccione una opción'}</option>
            ${opts}
        </select>`;
    } else if (campo.tipo === 'textarea') {
        input = `<textarea class="form-control" rows="3" placeholder="${ph}"></textarea>`;
    } else if (campo.tipo === 'checkbox') {
        input = `<label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#1a202c;font-family:'Poppins',sans-serif;">
            <input type="checkbox" style="width:16px;height:16px;cursor:pointer;accent-color:#B08955;">
            <span>${ph || campo.etiqueta}</span>
        </label>`;
    } else {
        const t = { email:'email', tel:'tel', date:'date', file:'file', text:'text' };
        input = `<input type="${t[campo.tipo]||'text'}" class="form-control" placeholder="${ph}">`;
    }

    return `<div class="form-group">
        <label class="form-label">${campo.etiqueta}:${req}</label>
        <div class="form-input-container">${input}</div>
    </div>`;
}

function openPreview() {
    // Reconstruir secciones base según estado actual de toggles
    document.getElementById('preview-tutor-section').innerHTML =
        buildBaseSection('tutor', 'Datos del padre o tutor interesado:');
    document.getElementById('preview-postulante-section').innerHTML =
        buildBaseSection('postulante', 'Datos del postulante:');

    const container = document.getElementById('preview-campos-container');
    const section   = document.getElementById('preview-dynamic-section');

    // Solo campos sin sección asignada (los asignados a tutor/postulante ya se intercalan dentro de buildBaseSection)
    const camposActivos = PREVIEW_CAMPOS.filter(c => c.activo && !c.seccion);
    if (camposActivos.length > 0) {
        container.innerHTML = camposActivos.map(buildPreviewField).join('');
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
    }

    const overlay = document.getElementById('preview-overlay');
    // Mover al body para que position:fixed sea relativo al viewport real
    if (overlay.parentElement !== document.body) {
        document.body.appendChild(overlay);
    }
    overlay.style.paddingLeft  = '16px';
    overlay.style.paddingRight = '16px';

    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePreview() {
    document.getElementById('preview-overlay').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closePreview(); closeDeleteModal(); cancelEdit(); }
});

// ── Campos base ──
function editBaseLabel(campo, btn) {
    const lblEl = document.getElementById('lbl-' + campo);
    if (lblEl.querySelector('input')) return; // ya editando

    const current = lblEl.textContent.trim();
    lblEl.innerHTML = '';

    const input = document.createElement('input');
    input.type = 'text';
    input.value = current;
    input.className = 'fbc-label-input';
    lblEl.appendChild(input);
    input.focus();
    input.select();

    const save = async () => {
        const val = input.value.trim();
        if (!val || val === current) { lblEl.textContent = current; return; }
        try {
            const res = await fetch(`/crm/formulario/base/${campo}`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ etiqueta: val })
            });
            const data = await res.json();
            lblEl.textContent = data.ok ? data.etiqueta : current;
            if (data.ok) {
                const idx = PREVIEW_BASE_CAMPOS.findIndex(c => c.campo === campo);
                if (idx !== -1) PREVIEW_BASE_CAMPOS[idx].etiqueta = data.etiqueta;
            }
        } catch { lblEl.textContent = current; }
    };

    input.addEventListener('blur', save);
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
        if (e.key === 'Escape') { input.value = current; input.blur(); }
    });
}

async function toggleBase(el) {
    if (el.classList.contains('fb-toggle--loading')) return;
    el.classList.add('fb-toggle--loading');
    try {
        const res = await fetch(`/crm/formulario/base/${el.dataset.campo}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ field: el.dataset.field })
        });
        const data = await res.json();
        if (data.ok) {
            el.classList.toggle('fb-toggle--on', data.value);
            // Sincronizar el array de preview para que openPreview() refleje el cambio
            const idx = PREVIEW_BASE_CAMPOS.findIndex(c => c.campo === el.dataset.campo);
            if (idx !== -1) PREVIEW_BASE_CAMPOS[idx][el.dataset.field] = data.value;
        } else {
            alert(data.message || 'No se puede modificar este campo.');
        }
    } catch (e) {}
    el.classList.remove('fb-toggle--loading');
}

// ── Modal eliminar ──
function openDeleteModal(action, nombre) {
    document.getElementById('deleteForm').action = action;
    document.getElementById('delete-campo-nombre').textContent = nombre;
    const overlay = document.getElementById('delete-overlay');
    if (overlay.parentElement !== document.body) document.body.appendChild(overlay);
    overlay.style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('delete-overlay').style.display = 'none';
}

function confirmDelete() {
    document.getElementById('deleteForm').submit();
}
</script>
@endsection
