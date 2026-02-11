@extends('layouts.app')

@section('title', 'CRM - Prospectos')

@vite(['resources/css/CRM/prospectos.css'])

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
        <div class="table-header">
            <div>RFC</div>
            <div>Nombre</div>
            <div>Apellido Paterno</div>
            <div>Apellido Materno</div>
            <div>CTP</div>
            <div>Tipo de prospecto</div>
            <div>Fecha</div>
        </div>

        <!-- BODY -->
        <div class="table-body">
            @forelse($leads as $lead)
                <div class="table-row">
                    <div>{{ $lead->rfc }}</div>
                    <div>{{ $lead->alumno_nombre }}</div>
                    <div>{{ $lead->alumno_paterno }}</div>
                    <div>{{ $lead->alumno_materno }}</div>
                    <div>{{ $lead->curp }}</div>
                    <div>{{ $lead->clasificacion }}</div>
                    <div>{{ $lead->created_at->format('Y-m-d') }}</div>
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
                // Si coincide o si el buscador esta vacio, mostrar
                if (contenido.includes(texto) || texto === '') {
                    fila.style.display = ''; // Restaurar display original (grid/flex)
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
});
</script>