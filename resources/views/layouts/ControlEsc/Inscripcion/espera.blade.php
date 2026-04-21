@extends('layouts.app')

@section('title', 'Inscripción')

@section('content')
<div class="form-container form-container--inscripcion" style="display:flex; align-items:center; justify-content:center; min-height:70vh;">
    <div style="background:#fff; border-radius:16px; max-width:560px; width:100%; box-shadow:0 18px 50px rgba(0,0,0,0.2); padding:24px 20px; text-align:center;">
        <div style="width:72px; height:72px; border-radius:50%; background:#eaf7ea; margin:0 auto 14px; display:flex; align-items:center; justify-content:center; border:3px solid #cfe6c8; color:#2e7d32; font-size:38px; font-weight:800;">
            ✓
        </div>
        <h3 style="margin:0; color:#223F70; font-size:1.25rem;">Información enviada con éxito</h3>
        <p style="margin:10px 0 0; color:#666; font-weight:600; line-height:1.5;">
            Tu registro está en espera de aprobación por Control Escolar.
        </p>
    </div>
</div>
@endsection
