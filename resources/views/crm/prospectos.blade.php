@extends('layouts.app')
@section('title', 'CRM - Prospectos')
@push('css')
    @vite('resources/css/CRM/prospectos.css')
@endpush
@section('content')
<div class="crm-prospectos">
    
    <!-- Encabezado SUPERIOR -->
    <div class="header-top">
        <h1>PROSPECTOS</h1>
    </div>
    <!-- Barra de Herramientas (Fechas, Buscador, Exportar) -->
    <div class="toolbar">
        
        <div class="filtros-izquierda">
            
            <!-- Fecha Inicio -->
            <div class="input-group-custom">
                <img src="{{ asset('images/icons/calendario.svg') }}" alt="Calendario" width="16">
                <input type="date" class="input-custom" placeholder="Fecha inicio">
            </div>
            <!-- Fecha Fin -->
            <div class="input-group-custom">
                 <img src="{{ asset('images/icons/calendario.svg') }}" alt="Calendario" width="16">
                <input type="date" class="input-custom" placeholder="Fecha fin">
            </div>
            <!-- Buscador (Live Search) -->
            <div class="input-group-custom search-wrapper">
                <img src="{{ asset('images/icons/search.svg') }}" alt="Search" width="16">
                <input 
                    type="text" 
                    class="input-custom buscador" 
                    placeholder="Buscar"
                >
            </div>
        </div>
        <button class="btn-exportar">
             <i class="fa fa-file-excel-o"></i> 
             Exportar
        </button>
    </div>
    <!-- TABLA DE DATOS -->
    <div class="table-container">
    <div class="table-card">
        <!-- HEADER -->
        <div class="table-row-header">
            <div class="col-curp">CURP</div>
            <div class="col-nombre">Nombre</div>
            <div class="col-paterno">Apellido Paterno</div>
            <div class="col-materno">Apellido Materno</div>
            <div class="col-ctp">CTP</div>
            <div class="col-tipo">Tipo de prospecto</div>
            <div class="col-fecha">Fecha</div>
        </div>
        <!-- BODY -->
        <div class="table-body">
            @forelse($leads as $lead)
                <div class="table-row">
                    <div class="col-curp">{{ $lead->alumno_curp }}</div>
                    <div class="col-nombre">{{ $lead->alumno_nombre }}</div>
                    <div class="col-paterno">{{ $lead->alumno_paterno }}</div>
                    <div class="col-materno">{{ $lead->alumno_materno }}</div>
                    <div class="col-ctp">{{ $lead->ctp?->name ?? 'Sin asignar' }}</div>
                    <div class="col-tipo">
                    {{ $lead->seguimientos()->orderBy('id', 'desc')->first()?->estado ?? 'Sin seguimiento' }}
</div>
                    <div class="col-fecha">{{ $lead->created_at->format('Y-m-d') }}</div>
                </div>
            @empty
                <p>No hay prospectos.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.querySelector('.buscador');
    
    if (buscador) {
        buscador.addEventListener('input', function() {
            const texto = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('.table-row');
            filas.forEach(fila => {
                const contenido = fila.innerText.toLowerCase();
                if (contenido.includes(texto) || texto === '') {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
});
</script>