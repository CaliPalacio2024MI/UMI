@extends('layouts.app')

@section('title', 'Estadísticas CRM')

@push('css')
    @vite('resources/css/CRM/estadisticas.css')
@endpush

@section('content')
    <div class="crm-estadisticas">

        {{-- Header --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <div class="header-top">
            <h1>ESTADÍSTICOS</h1>
        </div>

        {{-- Filters Bar --}}
        <form method="GET" action="{{ route('crm.estadisticas') }}">
            <div class="toolbar mb-8">

                <div class="filtros-izquierda">

                    <!-- Fecha Inicio -->
                    <div class="input-group-custom">
                        <img src="{{ asset('images/icons/calendario.svg') }}" alt="Calendario" width="16">
                        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                            class="input-custom">
                    </div>

                    <!-- Fecha Fin -->
                    <div class="input-group-custom">
                        <img src="{{ asset('images/icons/calendario.svg') }}" alt="Calendario" width="16">
                        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="input-custom">
                    </div>

                    <!-- Buscador -->
                    @if(session('active_role_name') == 'master' || session('active_role_name') == 'coordinador_ctp')

                    <div class="input-group-custom search-wrapper">
                        <img src="{{ asset('images/icons/search.svg') }}" width="16">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="input-custom" placeholder="Buscar por CTP">
                    </div>

                    @endif

                   <!-- Filtro de Estatus -->
                    <div class="input-group-custom">
                        <select id="filtroEstatus" name="estatus" class="input-custom" onchange="this.form.submit()">

                            <option value="">Todos los estatus</option>

                            <option value="Prospecto frío"
                                {{ request('estatus') == 'Prospecto frío' ? 'selected' : '' }}>
                                Prospecto Frío
                            </option>

                            <option value="Prospecto caliente"
                                {{ request('estatus') == 'Prospecto caliente' ? 'selected' : '' }}>
                                Prospecto Caliente
                            </option>

                            <option value="Aspirante"
                                {{ request('estatus') == 'Aspirante' ? 'selected' : '' }}>
                                Aspirante
                            </option>

                            <option value="Alumno"
                                {{ request('estatus') == 'Alumno' ? 'selected' : '' }}>
                                Alumno
                            </option>

                        </select>
                    </div>

                </div>

                <button type="submit" class="btn-exportar">
                    <i class="fa fa-file-excel-o"></i>
                    Exportar
                </button>

            </div>
        </form>

        <!-- ================= TARJETAS RESUMEN ================= -->

        <div class="cards-resumen">

            <div class="card-resumen leads">
                <div class="card-icon">
                    <i class="fa-solid fa-user-group"></i>
                </div>

                <div class="card-info">
                    <div class="card-titulo">Total de Leads</div>
                    <div class="card-numero contador" data-target="{{ $totalLeads }}"></div>
                </div>
            </div>


            <div class="card-resumen alumnos">
                <div class="card-icon">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>

                <div class="card-info">
                    <div class="card-titulo">Total de Alumnos</div>
                    <div class="card-numero contador" data-target="{{ $totalAlumno }}"></div>
                </div>
            </div>


            <div class="card-resumen tiempo">
                <div class="card-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div class="card-info">
                    <div class="card-titulo">Tiempo Promedio</div>
                    <div class="card-numero contador" data-target="{{ $tiempoPromedio }}"></div>
                </div>
            </div>

        </div>

       {{-- Main Content Section (Charts) --}}

       <!-- ================= CARD GRÁFICAS ================= -->

    <div class="charts-card">

        <div class="charts-wrapper">

            <!-- COLUMNA 1 -->
            <div class="chart-column">

                <div class="chart-item">
                    <h4>Distribución de Prospectos</h4>

                    <div class="leyenda-estatus">

                    <div class="item-leyenda">
                        <span class="color-box frio"></span>
                        Prospecto Frío
                    </div>

                    <div class="item-leyenda">
                        <span class="color-box caliente"></span>
                        Prospecto Caliente
                    </div>

                    <div class="item-leyenda">
                        <span class="color-box aspirante"></span>
                        Aspirante
                    </div>

                    <div class="item-leyenda">
                        <span class="color-box alumno"></span>
                        Alumno
                    </div>

                </div>
                    <canvas id="chartNumero"></canvas>
                </div>

            </div>


            <!-- COLUMNA 2 -->
            <div class="chart-column">

                <div class="chart-item">
                    <h4>Leads VS Alumnos</h4>
                    <canvas id="chartCierre"></canvas>
                </div>

            </div>

        </div>

    </div>

    <!-- ================= CARD TABLAS ================= -->

    <div class="tables-card">

        <div class="charts-wrapper">

            <!-- TABLA 1 -->
            <div class="chart-column">

                <div class="chart-table">

                    <div class="chart-title">
                        Distribución de Prospectos
                    </div>

                    <div class="table-header">
                        <div>Estado</div>
                        <div>Total</div>
                    </div>

                    @php
                    $totalGeneral = $totalFrio + $totalCaliente + $totalAspirante + $totalAlumno;

                    $porcentajeFrio = $totalGeneral > 0 
                        ? round(($totalFrio / $totalGeneral) * 100, 1) 
                        : 0;

                    $porcentajeCaliente = $totalGeneral > 0 
                        ? round(($totalCaliente / $totalGeneral) * 100, 1) 
                        : 0;

                    $porcentajeAspirante = $totalGeneral > 0 
                        ? round(($totalAspirante / $totalGeneral) * 100, 1) 
                        : 0;

                    $porcentajeAlumno = $totalGeneral > 0 
                        ? round(($totalAlumno / $totalGeneral) * 100, 1) 
                        : 0; 
                    @endphp

                    <div class="table-body-mini">

                        <div class="table-row-mini">
                            <div>Prospecto Frío</div>
                            <div>{{ $totalFrio }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Prospecto Caliente</div>
                            <div>{{ $totalCaliente }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Aspirante</div>
                            <div>{{ $totalAspirante }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Alumno</div>
                            <div>{{$totalAlumno}}</div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- TABLA 2 -->
            <div class="chart-column">

                <div class="chart-table">

                    <div class="chart-title">
                        Tasa de Conversión
                    </div>

                    <div class="table-header">
                        <div>Estado</div>
                        <div>Conversión</div>
                        <div>Tiempo Promedio</div>
                    </div>

                    <div class="table-body-mini">

                        <div class="table-row-mini">
                            <div>Prospecto Frío</div>
                            <div>{{ $porcentajeFrio }}%</div>
                            <div>{{ $promedioFrio }} días</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Prospecto Caliente</div>
                            <div>{{ $porcentajeCaliente }}%</div>
                            <div>{{ $promedioCaliente }} días</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Aspirante</div>
                            <div>{{ $porcentajeAspirante }}%</div>
                            <div>{{ $promedioAspirante }} días</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Alumno</div>
                            <div>{{ $porcentajeAlumno }}%</div>
                            <div>{{ $promedioAlumno }} días</div>
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
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

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


           /* ================= GRÁFICA 1: NÚMERO POR MES ================= */

            const ctxNumero = document.getElementById('chartNumero').getContext('2d');


            /* DATOS */

            const meses = [
                'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
            ];


            const datosEstados = {

                "Prospecto frío": [
                    {{ $frioPorMes[1] ?? 0 }},
                    {{ $frioPorMes[2] ?? 0 }},
                    {{ $frioPorMes[3] ?? 0 }},
                    {{ $frioPorMes[4] ?? 0 }},
                    {{ $frioPorMes[5] ?? 0 }},
                    {{ $frioPorMes[6] ?? 0 }},
                    {{ $frioPorMes[7] ?? 0 }},
                    {{ $frioPorMes[8] ?? 0 }},
                    {{ $frioPorMes[9] ?? 0 }},
                    {{ $frioPorMes[10] ?? 0 }},
                    {{ $frioPorMes[11] ?? 0 }},
                    {{ $frioPorMes[12] ?? 0 }}
                ],

                "Prospecto caliente": [
                    {{ $calientePorMes[1] ?? 0 }},
                    {{ $calientePorMes[2] ?? 0 }},
                    {{ $calientePorMes[3] ?? 0 }},
                    {{ $calientePorMes[4] ?? 0 }},
                    {{ $calientePorMes[5] ?? 0 }},
                    {{ $calientePorMes[6] ?? 0 }},
                    {{ $calientePorMes[7] ?? 0 }},
                    {{ $calientePorMes[8] ?? 0 }},
                    {{ $calientePorMes[9] ?? 0 }},
                    {{ $calientePorMes[10] ?? 0 }},
                    {{ $calientePorMes[11] ?? 0 }},
                    {{ $calientePorMes[12] ?? 0 }}
                ],

                "Aspirante": [
                    {{ $aspirantePorMes[1] ?? 0 }},
                    {{ $aspirantePorMes[2] ?? 0 }},
                    {{ $aspirantePorMes[3] ?? 0 }},
                    {{ $aspirantePorMes[4] ?? 0 }},
                    {{ $aspirantePorMes[5] ?? 0 }},
                    {{ $aspirantePorMes[6] ?? 0 }},
                    {{ $aspirantePorMes[7] ?? 0 }},
                    {{ $aspirantePorMes[8] ?? 0 }},
                    {{ $aspirantePorMes[9] ?? 0 }},
                    {{ $aspirantePorMes[10] ?? 0 }},
                    {{ $aspirantePorMes[11] ?? 0 }},
                    {{ $aspirantePorMes[12] ?? 0 }}
                ],

                "Alumno": [
                    {{ $alumnoPorMes[1] ?? 0 }},
                    {{ $alumnoPorMes[2] ?? 0 }},
                    {{ $alumnoPorMes[3] ?? 0 }},
                    {{ $alumnoPorMes[4] ?? 0 }},
                    {{ $alumnoPorMes[5] ?? 0 }},
                    {{ $alumnoPorMes[6] ?? 0 }},
                    {{ $alumnoPorMes[7] ?? 0 }},
                    {{ $alumnoPorMes[8] ?? 0 }},
                    {{ $alumnoPorMes[9] ?? 0 }},
                    {{ $alumnoPorMes[10] ?? 0 }},
                    {{ $alumnoPorMes[11] ?? 0 }},
                    {{ $alumnoPorMes[12] ?? 0 }}
                ]

            };


            /* CREAR GRÁFICA */

            const graficaMeses = new Chart(ctxNumero, {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: []
                },
                options: {

                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            display: false
                        },

                        tooltip: {
                            enabled: true
                        }
                    },

                    scales: {

                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            }
                        },

                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }

                    }
                }
                
            });



            /* FUNCIÓN CAMBIAR ESTATUS */

            function actualizarGrafica(estado){

                if(estado === "" || estado === "Todos"){

                    graficaMeses.data.datasets = [

                        {
                            label: 'Frío',
                            data: datosEstados["Prospecto frío"],
                            backgroundColor: '#17a2b8'
                        },

                        {
                            label: 'Caliente',
                            data: datosEstados["Prospecto caliente"],
                            backgroundColor: '#ffc107'
                        },

                        {
                            label: 'Aspirante',
                            data: datosEstados["Aspirante"],
                            backgroundColor: '#28a745'
                        },

                        {
                            label: 'Alumno',
                            data: datosEstados["Alumno"],
                            backgroundColor: '#6f42c1'
                        }

                    ];

                }else{

                    let color = {

                        "Prospecto frío": '#17a2b8',
                        "Prospecto caliente": '#ffc107',
                        "Aspirante": '#28a745',
                        "Alumno": '#6f42c1'
                    };

                    graficaMeses.data.datasets = [

                        {
                            data: datosEstados[estado],
                            backgroundColor: color[estado],
                            borderRadius: 6
                        }

                    ];

                }

                graficaMeses.update();
            }



            /* ESTATUS INICIAL */

            actualizarGrafica("{{ request('estatus') ?? 'Todos' }}");



            /* FILTRO */

            document.getElementById('filtroEstatus')
            .addEventListener('change', function() {

                actualizarGrafica(this.value);

            });


            /* ================= GRÁFICA 2: CONVERSIÓN POR ESTADO ================= */

            const ctxCierre = document.getElementById('chartCierre').getContext('2d');

            new Chart(ctxCierre, {
                type: 'doughnut',
                data: {
                    labels: [
                        'Prospecto Frío',
                        'Prospecto Caliente',
                        'Aspirante',
                        'Alumno'
                    ],
                    datasets: [{
                        data: [
                            {{ $porcentajeFrio }},
                            {{ $porcentajeCaliente }},
                            {{ $porcentajeAspirante }},
                            {{ $porcentajeAlumno }}
                        ],
                        backgroundColor: [
                            '#17a2b8',
                            '#ffc107',
                            '#28a745',
                            '#6f42c1'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    }
                }
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

        document.addEventListener("DOMContentLoaded", () => {

            const counters = document.querySelectorAll(".contador");

            counters.forEach(counter => {

                const updateCount = () => {

                    const target = +counter.getAttribute("data-target");
                    const count = +counter.innerText;

                    const increment = target / 60;

                    if(count < target){

                        counter.innerText = Math.ceil(count + increment);
                        setTimeout(updateCount, 20);

                    }else{

                        counter.innerText = target;

                    }

                };

            updateCount();

        });

});
        
    </script>
@endpush
