@extends('layouts.app')

@section('title', 'CRM - Leads')
@vite(['resources/css/CRM/leads.css'])
@section('content')

<div class="crm-leads">

    <!-- ENCABEZADO -->
    <div class="header-section">
        <div class="header-top">
            <h1 class="fw-bold mb-0">LEADS</h1>
        </div>
        
        <div class="search-wrapper">
        <img 
        src="{{ asset('images/icons/search.svg') }}"
        class="search-icon"
        alt="Buscar"
    >
            <input type="text" class="form-control buscador" placeholder="Buscar">
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL EN DOS COLUMNAS -->
    <div class="contenido-principal">

        <!-- COLUMNA IZQUIERDA: LISTA DE LEADS -->
        <div class="card-container">
            <div class="card card-leads">
                <!-- HEADER -->
                <div class="card-header encabezado-tabla">
                    <div class="encabezado-fila">
                        <span>Nombre</span>
                        <span>Apellido Paterno</span>
                        <span>Apellido Materno</span>
                        <span>Teléfono 1</span>
                        <span>Teléfono 2</span>
                        <span>Acciones</span>
                    </div>
                </div>

                <!-- BODY CON SCROLL -->
                <div class="card-body cuerpo-tabla">
                    @forelse($leads ?? [] as $lead)
                        <div class="fila-lead">
                        <div>{{ $lead->alumno_nombre ?? 'N/A' }}</div>
                        <div>{{ $lead->alumno_paterno ?? 'N/A' }}</div>
                        <div>{{ $lead->alumno_materno ?? 'N/A' }}</div>

                            <div>{{ $lead->telefono1 ?? 'N/A' }}</div>
                            <div>{{ $lead->telefono2 ?? 'N/A' }}</div>
                            <div class="acciones">
                                <button class="btn btn-icon btn-documento">
                                <img 
                                    src="{{ asset('images/icons/document.svg') }}"
                                    class="icon icon-documento"
                                    alt="Documento"
                                >
                                </button>
                                <button class="btn btn-icon btn-ver">
                                <img 
                                    src="{{ asset('images/icons/view.svg') }}"
                                    class="icon"
                                    alt="Ver"
                                > 
                                </button>
                                <button 
                                    class="btn btn-icon btn-eliminar"
                                    data-id="{{ $lead->id }}"
                                >
                                <img 
                                    src="{{ asset('images/icons/delete.svg') }}"
                                    class="icon"
                                    alt="Eliminar"
                                >
                                </button>

                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted py-5">No hay leads registrados</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: RFC Y CLASIFICACIÓN -->
        <div class="card-container">
            <div class="card card-detalle">
                <div class="card-header encabezado-detalle">
                    <div class="encabezado-fila-detalle">
                        <span>RFC</span>
                        <span>CLASIFICACIÓN</span>
                    </div>
                </div>

                <div class="card-body cuerpo-detalle">
                    {{-- Aquí irá el detalle del lead seleccionado --}}
                </div>
            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.btn-eliminar').forEach(btn => {

        btn.addEventListener('click', function () {

            const leadId = this.dataset.id;

            if (!confirm('¿Eliminar este lead?')) return;

            fetch(`/crm/leads/${leadId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.closest('.fila-lead').remove();
                }
            })
            .catch(() => alert('Error al eliminar el lead'));

        });

    });

});
</script>

<script>
(function () {

    const buscador = document.querySelector('.buscador');
    if (!buscador) return; // 👈 evita errores en otras vistas

    buscador.addEventListener('input', function () {

        const texto = this.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-lead');

        filas.forEach(fila => {
            const contenidoFila = fila.innerText.toLowerCase();
            fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
        });

    });

})();
</script>




@endsection