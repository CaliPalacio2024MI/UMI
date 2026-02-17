@extends('layouts.app')

@section('title', 'Estadísticas CRM')

@push('css')
    @vite('resources/css/CRM/estadisticas.css')
@endpush

@section('content')
<div class="crm-estadisticas">
    
    {{-- Header --}}
    <div class="header-top">
        <h1>ESTADÍSTICOS</h1>
    </div>

    {{-- Filters Bar (User Structure) --}}
    <div class="toolbar mb-8">
        
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
            <!-- Filtro de Estatus -->
            <div class="input-group-custom">
                <select class="input-custom filtro-estatus">
                    <option value="">Todos los estatus</option>
                    <option value="prospecto">Prospecto</option>
                    <option value="prospecto frío">Prospecto Frío</option>
                    <option value="prospecto caliente">Prospecto Caliente</option>
                    <option value="aspirante">Aspirante</option>
                </select>
            </div>
            <!-- Buscador (Live Search) -->
            <div class="input-group-custom search-wrapper">
                <img src="{{ asset('images/icons/search.svg') }}" alt="Search" width="16">
                <input 
                    type="text" 
                    class="input-custom buscador" 
                    placeholder="Buscar por CTP"
                >
            </div>
        </div>
        <button class="btn-exportar">
             <i class="fa fa-file-excel-o"></i> 
             Exportar
        </button>
    </div>

    {{-- Main Content Section (Charts) --}}
<!-- ================= GRÁFICAS ================= -->
<div class="charts-container">

    <div class="charts-inner">

        <div class="charts-wrapper">

            <!-- COLUMNA 1 -->
            <div class="chart-column">

                <div class="chart-item">
                    <h4># Número</h4>
                    <canvas id="chartNumero"></canvas>
                </div>

                <div class="chart-table">

                    <div class="chart-title">#Número</div>

                    
                    <div class="table-header">
                        <div>Nombre</div>
                        <div>CTP</div>
                        <div>Fecha</div>
                    </div>

                    
                    <div class="table-body-mini">
                        @forelse($leads as $lead)
                        <div class="table-row-mini" data-ctp="{{ $lead->curp }}" data-estatus="{{ strtolower($lead->clasificacion) }}">
                            <div>{{ $lead->alumno_nombre }} {{ $lead->alumno_paterno }}</div>
                            <div>{{ $lead->curp }}</div>
                            <div>{{ $lead->created_at->format('Y-m-d') }}</div>
                        </div>
                        @empty
                        <div class="table-row-mini">
                            <div colspan="3">No hay datos disponibles</div>
                        </div>
                        @endforelse
                    </div>

                </div>

            </div>


            <!-- COLUMNA 2 -->
            <div class="chart-column">

                <div class="chart-item">
                    <h4>% Cierre</h4>
                    <canvas id="chartCierre"></canvas>
                </div>

                <div class="chart-table">

                    <div class="chart-title">% Cierre</div>

                  
                    <div class="table-header">
                        <div>Nombre</div>
                        <div>CTP</div>
                        <div>Clasificación</div>
                    </div>

                   
                    <div class="table-body-mini">
                        @forelse($leads as $lead)
                        <div class="table-row-mini" data-ctp="{{ $lead->curp }}" data-estatus="{{ strtolower($lead->clasificacion) }}">
                            <div>{{ $lead->alumno_nombre }} {{ $lead->alumno_paterno }}</div>
                            <div>{{ $lead->curp }}</div>
                            <div>{{ $lead->clasificacion }}</div>
                        </div>
                        @empty
                        <div class="table-row-mini">
                            <div colspan="3">No hay datos disponibles</div>
                        </div>
                        @endforelse
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>


</div>
@endsection

@push('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ================= CONFIGURACIÓN MINI DASHBOARD ================= */
    const opcionesGrafica = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        elements: {
            line: {
                borderWidth: 2,
                tension: 0
            },
            point: {
                radius: 0
            }
        },
        scales: {
            x: {
                offset: true, 
                grid: {
                    display: false
                },
                border: {
                    display: true,
                    color: '#000',
                    width: 2   
                },
                ticks: {
                    autoSkip: false,
                    color: '#000',
                    maxRotation: 25,
                    minRotation: 25,
                    font: {
                        size: 10, 
                        family: "'Poppins', sans-serif"
                    }
                }
            },
            y: {
                grid: {
                    display: false
                },
                border: {
                    display: true,
                    color: '#000',
                    width: 2
                },
                ticks: {
                    display: false
                }
            }
        }
    };


    /* ================= GRÁFICA 1: # NÚMERO ================= */
    const ctxNumero = document.getElementById('chartNumero').getContext('2d');

    new Chart(ctxNumero, {
        type: 'line',
        data: {
            labels: [
                'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
            ],
            datasets: [{
                data: [12,19,3,5,2,3,15,10,8,12,5,25],
                borderColor: '#D4AF37',
                backgroundColor: 'rgba(212, 175, 55, 0.08)',
                fill: true
            }]
        },
        options: opcionesGrafica
    });


    /* ================= GRÁFICA 2: % CIERRE ================= */
    const ctxCierre = document.getElementById('chartCierre').getContext('2d');

    new Chart(ctxCierre, {
        type: 'line',
        data: {
            labels: [
                'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
            ],
            datasets: [{
                data: [5,15,25,10,30,15,40,5,20,10,45,30],
                borderColor: '#C5A065',
                backgroundColor: 'rgba(197, 160, 101, 0.08)',
                fill: true
            }]
        },
        options: opcionesGrafica
    });

});

// ================= FILTROS COMBINADOS (CTP + ESTATUS) =================
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.querySelector('.buscador');
    const filtroEstatus = document.querySelector('.filtro-estatus');
    
    // Función para aplicar todos los filtros
    function aplicarFiltros() {
        const textoCTP = buscador ? buscador.value.toLowerCase().trim() : '';
        const estatusSeleccionado = filtroEstatus ? filtroEstatus.value.toLowerCase() : '';
        const filas = document.querySelectorAll('.table-row-mini');
        
        filas.forEach(fila => {
            const ctp = fila.getAttribute('data-ctp') || '';
            const estatus = fila.getAttribute('data-estatus') || '';
            
            // Verificar si cumple con el filtro de CTP
            const cumpleCTP = textoCTP === '' || ctp.toLowerCase().includes(textoCTP);
            
            // Verificar si cumple con el filtro de estatus
            const cumpleEstatus = estatusSeleccionado === '' || estatus === estatusSeleccionado;
            
            // Mostrar solo si cumple con ambos filtros
            if (cumpleCTP && cumpleEstatus) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }
    
    // Agregar event listeners
    if (buscador) {
        buscador.addEventListener('input', aplicarFiltros);
    }
    
    if (filtroEstatus) {
        filtroEstatus.addEventListener('change', aplicarFiltros);
    }
});


</script>

@endpush
