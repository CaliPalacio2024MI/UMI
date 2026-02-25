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
                    <div class="input-group-custom search-wrapper">
                        <img src="{{ asset('images/icons/search.svg') }}" alt="Search" width="16">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="input-custom"
                            placeholder="Buscar por CTP">
                    </div>

                    <!-- Filtro de Estatus -->
                    <div class="input-group-custom">
                        <select name="estatus" class="input-custom" onchange="this.form.submit()">
                            <option value="">Todos los estatus</option>

                            <option value="Prospecto" {{ request('estatus') == 'Prospecto' ? 'selected' : '' }}>
                                Prospecto
                            </option>

                            <option value="Prospecto Frío" {{ request('estatus') == 'Prospecto Frío' ? 'selected' : '' }}>
                                Prospecto Frío
                            </option>

                            <option value="Prospecto Caliente"
                                {{ request('estatus') == 'Prospecto Caliente' ? 'selected' : '' }}>
                                Prospecto Caliente
                            </option>

                            <option value="Aspirante" {{ request('estatus') == 'Aspirante' ? 'selected' : '' }}>
                                Aspirante
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

        <div class="card-resumen">
            <div class="card-titulo">Prospectos</div>
            <div class="card-numero">{{ $totalProspecto }}</div>
        </div>

        <div class="card-resumen">
            <div class="card-titulo">Prospectos Fríos</div>
            <div class="card-numero">{{ $totalFrio }}</div>
        </div>

        <div class="card-resumen">
            <div class="card-titulo">Prospectos Calientes</div>
            <div class="card-numero">{{ $totalCaliente }}</div>
        </div>

        <div class="card-resumen">
            <div class="card-titulo">Aspirantes</div>
            <div class="card-numero">{{ $totalAspirante }}</div>
        </div>

    </div>

       {{-- Main Content Section (Charts) --}}

       <!-- ================= CARD GRÁFICAS ================= -->

    <div class="charts-card">

        <div class="charts-wrapper">

            <!-- COLUMNA 1 -->
            <div class="chart-column">

                <div class="chart-item">
                    <h4>Distribución de Prospectos por Mes</h4>

                    <div class="leyenda-estatus">

                    <div class="item-leyenda">
                        <span class="color-box prospecto"></span>
                        Prospecto
                    </div>

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

                </div>
                    <canvas id="chartNumero"></canvas>
                </div>

            </div>


            <!-- COLUMNA 2 -->
            <div class="chart-column">

                <div class="chart-item">
                    <h4>Tasa de Conversión a Aspirantes</h4>
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
                        Distribución de Prospectos por mes
                    </div>

                    <div class="table-header">
                        <div>Estado</div>
                        <div>Total</div>
                        <div>% del Total</div>
                    </div>

                    @php
                    $totalGeneral = $totalProspecto + $totalFrio + $totalCaliente + $totalAspirante;

                    $porcentajeProspecto = $totalGeneral > 0 
                        ? round(($totalProspecto / $totalGeneral) * 100, 1) 
                        : 0;

                    $porcentajeFrio = $totalGeneral > 0 
                        ? round(($totalFrio / $totalGeneral) * 100, 1) 
                        : 0;

                    $porcentajeCaliente = $totalGeneral > 0 
                        ? round(($totalCaliente / $totalGeneral) * 100, 1) 
                        : 0;

                    $porcentajeAspirante = $totalGeneral > 0 
                        ? round(($totalAspirante / $totalGeneral) * 100, 1) 
                        : 0;
                    @endphp

                    <div class="table-body-mini">

                        <div class="table-row-mini">
                            <div>Prospecto</div>
                            <div>{{ $totalProspecto }}</div>
                            <div>{{ $porcentajeProspecto }}%</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Prospecto Frío</div>
                            <div>{{ $totalFrio }}</div>
                            <div>{{ $porcentajeFrio }}%</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Prospecto Caliente</div>
                            <div>{{ $totalCaliente }}</div>
                            <div>{{ $porcentajeCaliente }}%</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Aspirante</div>
                            <div>{{ $totalAspirante }}</div>
                            <div>{{ $porcentajeAspirante }}%</div>
                        </div>

                    </div>

                </div>

            </div>


            <!-- TABLA 2 -->
            <div class="chart-column">

                <div class="chart-table">

                    <div class="chart-title">
                        Tasa de Conversión a Aspirantes
                    </div>

                    <div class="table-header">
                        <div>Indicador</div>
                        <div>Valor</div>
                    </div>

                    <div class="table-body-mini">

                        <div class="table-row-mini">
                            <div>Total Interesados</div>
                            <div>{{ $totalInteresados }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Total Aspirantes</div>
                            <div>{{ $totalAspirantes }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div>No Convertidos</div>
                            <div>{{ $totalInteresados - $totalAspirantes }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div><strong>Tasa Conversión</strong></div>
                            <div><strong>{{ $porcentajeConversion }}%</strong></div>
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

            new Chart(ctxNumero, {
                type: 'bar',
                data: {
                    labels: [
                        'Enero',
                        'Febrero',
                        'Marzo',
                        'Abril',
                        'Mayo',
                        'Junio',
                        'Julio',
                        'Agosto',
                        'Septiembre',
                        'Octubre',
                        'Noviembre',
                        'Diciembre'
                    ],
                    datasets: [{
                        data: [
                            {{ $enero ?? 0 }},
                            {{ $febrero ?? 0 }},
                            {{ $marzo ?? 0 }},
                            {{ $abril ?? 0 }},
                            {{ $mayo ?? 0 }},
                            {{ $junio ?? 0 }},
                            {{ $julio ?? 0 }},
                            {{ $agosto ?? 0 }},
                            {{ $septiembre ?? 0 }},
                            {{ $octubre ?? 0 }},
                            {{ $noviembre ?? 0 }},
                            {{ $diciembre ?? 0 }}
                        ],
                        backgroundColor: '#c27c3a',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },

                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#000',
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            formatter: function(value) {
                                return value;
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });

            /* ================= GRÁFICA 2: % CONVERSIÓN ================= */
            const ctxCierre = document.getElementById('chartCierre').getContext('2d');

            new Chart(ctxCierre, {
                type: 'doughnut',
                data: {
                    labels: ['Convertidos (Aspirantes)', 'No Convertidos'],
                    datasets: [{
                        data: [
                            {{ $totalAspirantes }},
                            {{ $totalInteresados - $totalAspirantes }}
                        ],
                        backgroundColor: [
                            '#28a745', // Verde = Convertidos
                            '#dc3545' // Rojo = No convertidos
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
                                    return context.label + ': ' + context.raw;
                                }
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'centerText',
                    beforeDraw: function(chart) {
                        const {
                            width
                        } = chart;
                        const {
                            height
                        } = chart;
                        const ctx = chart.ctx;
                        ctx.restore();

                        const fontSize = (height / 5).toFixed(2);
                        ctx.font = `bold ${fontSize}px sans-serif`;
                        ctx.textBaseline = "middle";
                        ctx.fillStyle = "#000";

                        const text = "{{ $porcentajeConversion }}%";
                        const textX = Math.round((width - ctx.measureText(text).width) / 2);
                        const textY = height / 2;

                        ctx.fillText(text, textX, textY);
                        ctx.save();
                    }
                }]
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
