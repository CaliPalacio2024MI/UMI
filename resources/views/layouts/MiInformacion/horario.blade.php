@extends('layouts.app')

@section('title', 'Mi Horario - ' . session('active_institution_name'))

@section('content')
<style>
@media print {
    @page { size: landscape !important; }
    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .sidebar, .header, .horario-page-toolbar { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
}
</style>
<div class="main-content">
    <div class="horario-page-toolbar" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h5 style="margin: 0; color: #002366; font-size: 1.5rem;">Horarios</h5>
            <div style="color:#ACACAC; font-family:'Poppins',sans-serif; font-weight:500;">Agosto 2025 – Febrero 2026</div>
        </div>
        <button type="button" class="btn btn--secondary" onclick="window.print()" aria-label="Exportar a PDF">
            <i class="fa-solid fa-file-export" style="margin-right: 6px;"></i> Exportar
        </button>
    </div>

    @include('layouts.ControlAdmin.Listas.members.partials.horarios_grilla_semanal', [
        'horarios' => $horarios ?? collect(),
        'esAlumno' => $esAlumno ?? true,
        'materiaLabels' => $materiaLabels ?? [],
        'horarioResumenPorClase' => $horarioResumenPorClase ?? [],
    ])
</div>
@endsection
