@extends('layouts.app')
@section('title', 'CRM - Comisiones')
@push('css')
    @vite('resources/css/CRM/comisiones.css')
@endpush
@section('content')
<div class="crm-comisiones">

    <div class="header-top">
        <h1>COMISIONES</h1>
    </div>

    <div class="toolbar">
        <div class="filtros-izquierda">
            <div class="input-group-custom search-wrapper">
                <img src="{{ asset('images/icons/search.svg') }}" alt="Search" width="16">
                <input type="text" class="input-custom buscador-ctp" placeholder="Buscar por CTP">
            </div>
        </div>
        <button class="btn-exportar">
            <img src="{{ asset('images/icons/export.svg') }}" alt="Exportar" width="16">
            Exportar
        </button>
    </div>

    <div class="table-container">
        <div class="table-card">

            <div class="table-row-header">
                <div class="col-ctp">CTP</div>
                <div class="col-conversiones">Número de Conversiones</div>
                <div class="col-total">Total de Comisiones</div>
                <div class="col-acciones">Acciones</div>
            </div>

            <div class="table-body" id="tabla-comisiones">

            @forelse($ctps as $ctp)
<div class="table-row" data-ctp="{{ strtolower($ctp->nombre . ' ' . $ctp->apellido_paterno) }}">
    <div class="col-ctp">{{ $ctp->nombre }} {{ $ctp->apellido_paterno }}</div>
    <div class="col-conversiones">{{ $ctp->num_conversiones }}</div>
    <div class="col-total">$0.00</div>
    <div class="col-acciones">
        <img src="{{ asset('images/icons/eye.svg') }}"
             alt="Ver"
             class="icon-accion btn-ver-ctp"
             data-id="{{ $ctp->id }}"
             title="Ver detalle">
    </div>
</div>
@empty
<p class="sin-registros">No hay CTPs registrados.</p>
@endforelse

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {

        // ===== BUSCADOR =====
        const buscadorCTP = document.querySelector('.buscador-ctp');

        function filtrarPorCTP() {
            const texto = buscadorCTP.value.toLowerCase().trim();
            const filas = document.querySelectorAll('#tabla-comisiones .table-row');
            filas.forEach(fila => {
                const ctp = (fila.getAttribute('data-ctp') || '').toLowerCase();
                fila.style.display = texto === '' || ctp.includes(texto) ? '' : 'none';
            });
        }

        if (buscadorCTP) {
            buscadorCTP.addEventListener('input', filtrarPorCTP);
        }

        // ===== BOTÓN OJO =====
        document.querySelectorAll('.btn-ver-ctp').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                console.log('Ver CTP id:', id);
            });
        });

        // ===== EXPORTAR EXCEL =====
        const btnExportar = document.querySelector('.btn-exportar');

        btnExportar.addEventListener('click', () => {

            const datos = [];
            const fechaExport = new Date().toLocaleDateString('es-MX');

            datos.push([`Reporte de Comisiones`]);
            datos.push([`Generado el: ${fechaExport}`]);
            datos.push([]);

            datos.push([
                "CTP",
                "Número de Conversiones",
                "Total de Comisiones"
            ]);

            document.querySelectorAll('#tabla-comisiones .table-row').forEach(fila => {

                if (fila.style.display === 'none') return;

                const ctp          = fila.querySelector('.col-ctp')?.innerText.trim();
                const conversiones = fila.querySelector('.col-conversiones')?.innerText.trim();
                const total        = fila.querySelector('.col-total')?.innerText.trim();

                datos.push([ctp, conversiones, total]);
            });

            const hoja = XLSX.utils.aoa_to_sheet(datos);

            hoja['!cols'] = [
                { wch: 30 },
                { wch: 25 },
                { wch: 22 }
            ];

            const libro = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(libro, hoja, "Comisiones");

            const fechaArchivo = new Date().toISOString().slice(0, 10);
            XLSX.writeFile(libro, `comisiones_${fechaArchivo}.xlsx`);
        });

    });
</script>
@endpush