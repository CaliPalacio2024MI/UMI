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

<!-- HEADER AZUL (FLOTADO) -->
<div class="leads-header">
    <div class="encabezado-fila">
        <span>Nombre</span>
        <span>Apellido Paterno</span>
        <span>Apellido Materno</span>
        <span>Teléfono 1</span>
        <span>Teléfono 2</span>
        <span>Acciones</span>
    </div>
</div>

<!-- CUERPO BLANCO -->
<div class="leads-body">
    <div class="cuerpo-tabla">
        @forelse($leads ?? [] as $lead)
                <div class="fila-lead"
                    data-id="{{ $lead->id }}"
                    data-clasificacion="{{ $lead->clasificacion }}"
                    data-tutor-nombre="{{ $lead->tutor_nombre }}"
                    data-tutor-paterno="{{ $lead->tutor_paterno }}"
                    data-tutor-materno="{{ $lead->tutor_materno }}"
                    data-telefono1="{{ $lead->telefono1 }}"
                    data-telefono2="{{ $lead->telefono2 }}"
                    data-alumno-nombre="{{ $lead->alumno_nombre }}"
                    data-alumno-paterno="{{ $lead->alumno_paterno }}"
                    data-alumno-materno="{{ $lead->alumno_materno }}"
                    data-rfc="{{ $lead->rfc }}"
                    data-curp="{{ $lead->curp }}"
                    data-seguimientos='@json($lead->seguimientos)'

                >
                    <div>{{ $lead->alumno_nombre ?? 'N/A' }}</div>
                    <div>{{ $lead->alumno_paterno ?? 'N/A' }}</div>
                    <div>{{ $lead->alumno_materno ?? 'N/A' }}</div>
                    <div>{{ $lead->telefono1 ?? 'N/A' }}</div>
                    <div>{{ $lead->telefono2 ?? 'N/A' }}</div>

                <div class="acciones">
                    <button class="btn btn-icon btn-documento">
                        <img src="{{ asset('images/icons/document.svg') }}" class="icon">
                    </button>

                    <button class="btn btn-icon btn-ver">
                        <img src="{{ asset('images/icons/view.svg') }}" class="icon">
                    </button>

                    <button class="btn btn-icon btn-eliminar" data-id="{{ $lead->id }}">
                        <img src="{{ asset('images/icons/delete.svg') }}" class="icon">
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

    <!-- SEGUIMIENTO -->
    <div class="leads-header">
        <span class="text-center text-white fw-bold d-block py-2">
            SEGUIMIENTO
        </span>
    </div>

    <div class="rfc-panel" id="seguimiento-panel">
        <div class="seguimiento-header">
            <span>Estado</span>
            <span>Fecha</span>
            <span>Hora</span>
            <span>Acciones</span>
        </div>

        <div class="seguimiento-body">
            <!-- JS inyecta filas -->
        </div>
    </div>

    <!-- DATOS GENERALES -->
    <div class="leads-header mt-3">
        <span class="text-center text-white fw-bold d-block py-2">
            DATOS GENERALES
        </span>
    </div>

    <div class="rfc-panel" id="datos-panel">
        <!-- JS inyecta datos -->
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

<script>
const ESTADOS = [
    'Prospecto',
    'Prospecto frío',
    'Prospecto caliente',
    'Alumno'
];

document.querySelectorAll('.fila-lead').forEach(fila => {

    fila.addEventListener('click', () => {

        // activar fila
        document.querySelectorAll('.fila-lead')
            .forEach(f => f.classList.remove('activo'));
        fila.classList.add('activo');

        const d = fila.dataset;

        /* =======================
           SEGUIMIENTO (DESDE BD)
        ======================= */
        const seguimientoBody = document.querySelector('.seguimiento-body');
        seguimientoBody.innerHTML = '';

        const seguimientos = JSON.parse(fila.dataset.seguimientos || '[]');

        let estadoActual = null;
        if (seguimientos.length > 0) {
            estadoActual = seguimientos[seguimientos.length - 1].estado;
        }

        ESTADOS.forEach(estado => {

            const registro = seguimientos.find(s => s.estado === estado);

            const fecha = registro?.fecha ?? '----------';
            const hora = registro?.hora ?? '----------';
            const accion = registro ? '✔' : '○';

            seguimientoBody.innerHTML += `
                <div class="seguimiento-row ${registro ? '' : 'muted'}"
                     data-estado="${estado}">
                    <span>${estado}</span>
                    <span>${fecha}</span>
                    <span>${hora}</span>
                    <span class="accion">${accion}</span>
                </div>
            `;
        });

        /* =======================
           DATOS GENERALES
        ======================= */
        document.getElementById('datos-panel').innerHTML = `
            <div class="datos-card">
                <h6>Datos del tutor</h6>
                <div class="datos-grid">
                    <p><strong>Nombre:</strong><br>${d.tutorNombre}</p>
                    <p><strong>Apellido Paterno:</strong><br>${d.tutorPaterno}</p>
                    <p><strong>Apellido Materno:</strong><br>${d.tutorMaterno}</p>
                    <p><strong>Teléfono 1:</strong><br>${d.telefono1}</p>
                    <p><strong>Teléfono 2:</strong><br>${d.telefono2 ?? 'N/A'}</p>
                </div>
            </div>

            <div class="datos-card">
                <h6>Datos del Aspirante a Alumno</h6>
                <div class="datos-grid">
                    <p><strong>Nombre:</strong><br>${d.alumnoNombre}</p>
                    <p><strong>Apellido Paterno:</strong><br>${d.alumnoPaterno}</p>
                    <p><strong>Apellido Materno:</strong><br>${d.alumnoMaterno}</p>
                    <p><strong>RFC:</strong><br>${d.rfc ?? 'N/A'}</p>
                    <p><strong>CURP:</strong><br>${d.curp ?? 'N/A'}</p>
                </div>
            </div>
        `;
    });
});

/* =======================
   GUARDAR NUEVO ESTADO
======================= */
document.addEventListener('click', function (e) {

    const row = e.target.closest('.seguimiento-row');
    if (!row) return;

    if (row.classList.contains('muted')) return;

    const nuevoEstado = row.dataset.estado;
    const leadId = document.querySelector('.fila-lead.activo')?.dataset.id;

    if (!leadId) return;

    fetch(`/crm/leads/${leadId}/seguimiento`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(() => location.reload());
});
</script>



@endsection