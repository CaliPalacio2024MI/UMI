@extends('layouts.app')

@section('title', 'Estadísticas CRM')

@push('css')
    @vite('resources/css/CRM/estadisticas.css')
@endpush

@section('content')
    <div class="crm-estadisticas">
       <form method="GET" action="{{ route('crm.estadisticas') }}">
        {{-- Header --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <div class="header-top">

            <h1>ESTADÍSTICOS</h1>

            <div class="header-actions">
                    <!-- Fecha Inicio -->
                    <div class="input-group-custom input-fecha-header">
                        <img src="{{ asset('images/icons/calendario.svg') }}" alt="Calendario" width="16">
                        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                            class="input-custom" onchange="this.form.submit()">
                    </div>

                    <!-- Fecha Fin -->
                    <div class="input-group-custom input-fecha-header">
                        <img src="{{ asset('images/icons/calendario.svg') }}" alt="Calendario" width="16">
                        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="input-custom"
                        onchange="this.form.submit()">
                    </div>

                    <!-- Exportar -->
                   <button type="button" class="btn-exportar"
                    onclick="exportarExcel()">
                    
                    <img src="{{ asset('images/icons/export.svg') }}" width="16">
                    Exportar
                </button>
            </div>

        </div>

        {{-- Filters Bar --}}
 
            <div class="toolbar mb-8">

                <div class="filtros-izquierda">
                    
                    <!-- Filtro CTP -->
                   @if(session('active_role_name') == 'master' || session('active_role_name') == 'coordinador_ctp')

                    <div class="input-group-custom select-wrapper">

                        <select name="ctp_id" class="input-custom" onchange="this.form.submit()">

                            <option value="">Todos los CTP</option>

                            @foreach($ctps as $ctp)
                                <option value="{{ $ctp->id }}"
                                    {{ request('ctp_id') == $ctp->id ? 'selected' : '' }}>
                                    {{ $ctp->nombre }}
                                </option>
                            @endforeach

                        </select>

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


                    <!-- Filtro Nivel Educativo -->
                    <div class="input-group-custom select-wrapper filtro-carrera">

                        <select name="nivel_educativo" class="input-custom" onchange="this.form.submit()">
                            <option value="">Todos clasificación</option>
                            @foreach($clasificaciones as $clasificacion)
                                <option value="{{ $clasificacion->id }}"
                                    {{ request('nivel_educativo') == $clasificacion->id ? 'selected' : '' }}>
                                    {{ $clasificacion->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <!-- Filtro Carrera -->
                    <div class="input-group-custom select-wrapper filtro-carrera">

                        <select name="carrera_id" class="input-custom" onchange="this.form.submit()">
                            <option value="">Todas las carreras</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera->id }}"
                                        data-clasificacion="{{ $carrera->career_classification_id }}"
                                        {{ request('carrera_id') == $carrera->id ? 'selected' : '' }}>
                                    {{ \Illuminate\Support\Str::limit($carrera->name, 30) }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                </div>

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
                    <i class="fa-solid fa-percent"></i>
                </div>
                <div class="card-info">
                    <div class="card-titulo">Conversión General</div>
                    <div class="card-numero contador" data-target="{{ $porcentajeConversion }}">%</div>
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
                    <h4 id="tituloDona">Leads VS Alumnos</h4>
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
                            <div>{{ $promedioFrio }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Prospecto Caliente</div>
                            <div>{{ $porcentajeCaliente }}%</div>
                            <div>{{ $promedioCaliente }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Aspirante</div>
                            <div>{{ $porcentajeAspirante }}%</div>
                            <div>{{ $promedioAspirante }}</div>
                        </div>

                        <div class="table-row-mini">
                            <div>Alumno</div>
                            <div>{{ $porcentajeAlumno }}%</div>
                            <div>{{ $promedioAlumno }}</div>
                        </div>

                    </div>

                </div>

            </div>
            

        </div>

    </div>
       
</div>
@endsection

@push('scripts')
<script>
(function() {

    // ── GRÁFICA BARRAS ──
    const ctxNumero = document.getElementById('chartNumero')?.getContext('2d');
    const ctxCierre = document.getElementById('chartCierre')?.getContext('2d');
    if (!ctxNumero || !ctxCierre) return;

    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    const datosEstados = {
        "Prospecto frío":     [{{ $frioPorMes[1] ?? 0 }},{{ $frioPorMes[2] ?? 0 }},{{ $frioPorMes[3] ?? 0 }},{{ $frioPorMes[4] ?? 0 }},{{ $frioPorMes[5] ?? 0 }},{{ $frioPorMes[6] ?? 0 }},{{ $frioPorMes[7] ?? 0 }},{{ $frioPorMes[8] ?? 0 }},{{ $frioPorMes[9] ?? 0 }},{{ $frioPorMes[10] ?? 0 }},{{ $frioPorMes[11] ?? 0 }},{{ $frioPorMes[12] ?? 0 }}],
        "Prospecto caliente": [{{ $calientePorMes[1] ?? 0 }},{{ $calientePorMes[2] ?? 0 }},{{ $calientePorMes[3] ?? 0 }},{{ $calientePorMes[4] ?? 0 }},{{ $calientePorMes[5] ?? 0 }},{{ $calientePorMes[6] ?? 0 }},{{ $calientePorMes[7] ?? 0 }},{{ $calientePorMes[8] ?? 0 }},{{ $calientePorMes[9] ?? 0 }},{{ $calientePorMes[10] ?? 0 }},{{ $calientePorMes[11] ?? 0 }},{{ $calientePorMes[12] ?? 0 }}],
        "Aspirante":          [{{ $aspirantePorMes[1] ?? 0 }},{{ $aspirantePorMes[2] ?? 0 }},{{ $aspirantePorMes[3] ?? 0 }},{{ $aspirantePorMes[4] ?? 0 }},{{ $aspirantePorMes[5] ?? 0 }},{{ $aspirantePorMes[6] ?? 0 }},{{ $aspirantePorMes[7] ?? 0 }},{{ $aspirantePorMes[8] ?? 0 }},{{ $aspirantePorMes[9] ?? 0 }},{{ $aspirantePorMes[10] ?? 0 }},{{ $aspirantePorMes[11] ?? 0 }},{{ $aspirantePorMes[12] ?? 0 }}],
        "Alumno":             [{{ $alumnoPorMes[1] ?? 0 }},{{ $alumnoPorMes[2] ?? 0 }},{{ $alumnoPorMes[3] ?? 0 }},{{ $alumnoPorMes[4] ?? 0 }},{{ $alumnoPorMes[5] ?? 0 }},{{ $alumnoPorMes[6] ?? 0 }},{{ $alumnoPorMes[7] ?? 0 }},{{ $alumnoPorMes[8] ?? 0 }},{{ $alumnoPorMes[9] ?? 0 }},{{ $alumnoPorMes[10] ?? 0 }},{{ $alumnoPorMes[11] ?? 0 }},{{ $alumnoPorMes[12] ?? 0 }}]
    };

    const textoCentroDona = {
        id: 'textoCentroDona',
        beforeDraw(chart) {
            const { width, height, ctx } = chart;
            ctx.restore();
            const fontSize = (height / 110).toFixed(2);
            ctx.font = fontSize + "em sans-serif";
            ctx.textBaseline = "middle";
            ctx.textAlign = "center";
            ctx.fillStyle = "#333";
            ctx.fillText(chart.config.data.centerText || "", width / 2, height / 2);
            ctx.save();
        }
    };

    const graficaMeses = new Chart(ctxNumero, {
        type: 'bar',
        data: { labels: meses, datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: true } },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    function actualizarGrafica(estado) {
        const colores = { "Prospecto frío": '#17a2b8', "Prospecto caliente": '#ffc107', "Aspirante": '#28a745', "Alumno": '#6f42c1' };
        if (estado === "" || estado === "Todos") {
            graficaMeses.data.datasets = [
                { label: 'Frío',      data: datosEstados["Prospecto frío"],     backgroundColor: colores["Prospecto frío"] },
                { label: 'Caliente',  data: datosEstados["Prospecto caliente"], backgroundColor: colores["Prospecto caliente"] },
                { label: 'Aspirante', data: datosEstados["Aspirante"],          backgroundColor: colores["Aspirante"] },
                { label: 'Alumno',    data: datosEstados["Alumno"],             backgroundColor: colores["Alumno"] }
            ];
        } else {
            graficaMeses.data.datasets = [{ data: datosEstados[estado], backgroundColor: colores[estado], borderRadius: 6 }];
        }
        graficaMeses.update();
    }

    // ── GRÁFICA DONA ──
    let chartCierre = new Chart(ctxCierre, {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [], backgroundColor: [], borderWidth: 0 }] },
        options: {
            cutout: '70%', responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: (ctx) => ctx.label + ': ' + ctx.raw } }
            }
        }
    });

    function actualizarGraficaDona(estado) {
        const titulo = document.getElementById("tituloDona");
        const colores = { "Prospecto frío": "#17a2b8", "Prospecto caliente": "#ffc107", "Aspirante": "#28a745", "Alumno": "#6f42c1" };
        const totalSinFiltro = {{ $totalSinFiltroEstatus }};
        const totalesPorEstado = {
            "Prospecto frío":     {{ $totalFrio }},
            "Prospecto caliente": {{ $totalCaliente }},
            "Aspirante":          {{ $totalAspirante }},
            "Alumno":             {{ $totalAlumno }}
        };
                if (!estado || estado === "Todos") {
            titulo.innerText = "Tasa de conversión";
            chartCierre.data.labels = ["Prospecto frío","Prospecto caliente","Aspirante","Alumno"];
            chartCierre.data.datasets[0].data            = [{{ $porcentajeFrio }},{{ $porcentajeCaliente }},{{ $porcentajeAspirante }},{{ $porcentajeAlumno }}];
            chartCierre.data.datasets[0].backgroundColor = [colores["Prospecto frío"],colores["Prospecto caliente"],colores["Aspirante"],colores["Alumno"]];
            chartCierre.config.data.centerText = null;
        } else {
        const totalEstado = totalesPorEstado[estado] ?? 0;
        const porcentaje = totalSinFiltro > 0
            ? Math.round((totalEstado / totalSinFiltro) * 100 * 10) / 10
            : 0;
        const resto = totalSinFiltro - totalEstado;

        titulo.innerText = "Leads vs " + estado;
        chartCierre.data.labels                      = ["Otros leads", estado];
        chartCierre.data.datasets[0].data            = [resto, totalEstado];
        chartCierre.data.datasets[0].backgroundColor = ["#dee2e6", colores[estado]];
        chartCierre.config.data.centerText           = porcentaje + "%";
    }
        chartCierre.update();
    }

    // ── INICIALIZAR ──
    actualizarGrafica("{{ request('estatus') ?? 'Todos' }}");
    actualizarGraficaDona("{{ request('estatus') }}");

    // ── FILTRO ESTATUS → actualiza gráficas ──
    document.getElementById('filtroEstatus')?.addEventListener('change', function() {
        actualizarGrafica(this.value);
        actualizarGraficaDona(this.value);
    });

    // ── FILTROS COMBINADOS (buscador + estatus en tabla) ──
    const buscador     = document.querySelector('.buscador');
    const filtroEstatus = document.querySelector('.filtro-estatus');

    function aplicarFiltros() {
        const textoCTP           = buscador ? buscador.value.toLowerCase().trim() : '';
        const estatusSeleccionado = filtroEstatus ? filtroEstatus.value.toLowerCase() : '';
        document.querySelectorAll('.table-row-mini').forEach(fila => {
            const ctp    = fila.getAttribute('data-ctp')    || '';
            const estatus = fila.getAttribute('data-estatus') || '';
            const cumpleCTP    = textoCTP === ''           || ctp.toLowerCase().includes(textoCTP);
            const cumpleEstatus = estatusSeleccionado === '' || estatus === estatusSeleccionado;
            fila.style.display = (cumpleCTP && cumpleEstatus) ? '' : 'none';
        });
    }

    if (buscador)      buscador.addEventListener('input', aplicarFiltros);
    if (filtroEstatus) filtroEstatus.addEventListener('change', aplicarFiltros);

    // ── CONTADOR ANIMADO ──
    document.querySelectorAll('.contador').forEach(counter => {
        const updateCount = () => {
            const target    = +counter.getAttribute('data-target');
            const count     = +counter.innerText;
            const increment = target / 60;
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCount, 20);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });

    // ── FILTRO CLASIFICACIÓN → CARRERA ──
    const selectClasificacion = document.querySelector('select[name="nivel_educativo"]');
    const selectCarrera       = document.querySelector('select[name="carrera_id"]');
    if (selectClasificacion && selectCarrera) {
        const opcionesCarrera = Array.from(selectCarrera.querySelectorAll('option'));
        selectClasificacion.addEventListener('change', function () {
            const clasificacionId = this.value;
            selectCarrera.value   = '';
            opcionesCarrera.forEach(option => {
                option.style.display = (!option.value || !clasificacionId || option.dataset.clasificacion === clasificacionId) ? '' : 'none';
            });
            selectClasificacion.closest('form').submit();
        });
    }

})();

function exportarExcel() {
    const form   = document.querySelector('form');
    const params = new URLSearchParams(new FormData(form)).toString();
    window.location = "{{ route('crm.estadisticas.exportar') }}?" + params;
}
</script>
@endpush
