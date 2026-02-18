@extends('layouts.app')

@section('title', 'CRM - Leads')

@push('css')
    @vite('resources/css/CRM/leads.css')
@endpush

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
                        data-tiene-ctp="{{ $lead->ctp_id ? '1' : '0' }}"
                        data-ctp="{{ $lead->ctp_id }}"
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
                @if(in_array(session('active_role_name'), ['master', 'coordinador_ctp']))
                <button 
                    class="btn btn-icon btn-asignar-ctp"
                     data-lead="{{ $lead->id }}"
                    title="Asignar CTP"
                    >
                        <img src="{{ asset('images/icons/usuario_tag.svg') }}" class="icon">
                    </button>
                @endif

                    <button class="btn btn-icon btn-flecha">
                        <img src="{{ asset('images/icons/flecha.svg') }}" class="icon">
                    </button>
                    @if(in_array(session('active_role_name'), ['master', 'coordinador_ctp']))
        <button class="btn btn-icon btn-eliminar" data-id="{{ $lead->id }}">
            <img src="{{ asset('images/icons/delete.svg') }}" class="icon">
        </button>
    @endif
                </div>
            </div>
        @empty
            <p class="text-center text-muted py-5">No hay leads registrados</p>
        @endforelse
    </div>
</div>

</div>


        <!-- COLUMNA DERECHA: RFC Y CLASIFICACIÓN -->
        <div class="card-container" id="right-column">

    <!-- SEGUIMIENTO -->
    <div class="leads-header">
        <span class="titulo-lateral">
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
        <span class="titulo-lateral">
            DATOS GENERALES
        </span>
    </div>

    <div class="rfc-panel" id="datos-panel">
        <!-- JS inyecta datos -->
    </div>

    </div>



    </div>


</div>

<div id="modal-ctp" class="modal-ctp hidden">
    <div class="modal-content">
        <h5>Asignar CTP</h5>

        <select id="ctp-select" class="form-control">
            <option value="">Selecciona un CTP</option>

            @foreach($ctps as $ctp)
                <option value="{{ $ctp->id }}">
                    {{ $ctp->name }}
                </option>
            @endforeach
        </select>


        <div class="modal-actions">
            <button id="guardar-ctp" class="btn btn-primary">Asignar</button>
            <button id="cerrar-ctp" class="btn btn-secondary">Cancelar</button>
        </div>
    </div>
</div>


<script>
    window.ROLE_ACTIVO = "{{ session('active_role_name') }}";
</script>

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

    const btnFlecha = fila.querySelector('.btn-flecha');
    if (!btnFlecha) return;

    btnFlecha.addEventListener('click', () => {

        document.querySelectorAll('.fila-lead')
            .forEach(f => f.classList.remove('activo'));
        fila.classList.add('activo');

        /* =======================
           SEGUIMIENTO
        ======================= */
        const seguimientoBody = document.querySelector('.seguimiento-body');
        seguimientoBody.innerHTML = '';

        const seguimientos = JSON.parse(fila.dataset.seguimientos || '[]');
        const tieneCTP = fila.dataset.tieneCtp === '1';

        let estadoActual = seguimientos.length
            ? seguimientos[seguimientos.length - 1].estado
            : null;

        ESTADOS.forEach((estado, index) => {

            const registro = seguimientos.find(s => s.estado === estado);

            let habilitado = false;
            if (tieneCTP) {
                if (!estadoActual && index === 0) habilitado = true;
                if (estadoActual && ESTADOS[index - 1] === estadoActual) habilitado = true;
            }

            const accion = registro
                ? `<img src="/images/icons/check.svg" class="icon-check activo">`
                : habilitado
                    ? `<img src="/images/icons/check.svg" class="icon-check clickeable" data-estado="${estado}">`
                    : `<span class="icon-disabled">○</span>`;

            seguimientoBody.innerHTML += `
                <div class="seguimiento-row ${habilitado || registro ? '' : 'muted'}">
                    <span>${estado}</span>
                    <span>${registro?.fecha ?? '---'}</span>
                    <span>${registro?.hora ?? '---'}</span>
                    <span class="accion">${accion}</span>
                </div>
            `;
        });

        /* =======================
           DATOS GENERALES  ✅
        ======================= */
        const d = fila.dataset;

        document.getElementById('datos-panel').innerHTML = `
            <div class="datos-card">

                <h6 class="titulo-seccion">Datos del Tutor</h6>

                <div class="datos-grid-3">
                    <div class="dato-item"><label>Nombre:</label><p>${d.tutorNombre ?? '---'}</p></div>
                    <div class="dato-item"><label>Apellido Paterno:</label><p>${d.tutorPaterno ?? '---'}</p></div>
                    <div class="dato-item"><label>Apellido Materno:</label><p>${d.tutorMaterno ?? '---'}</p></div>
                </div>

                <div class="datos-flex-center mt-3">
                    <div class="dato-item"><label>Teléfono 1:</label><p>${d.telefono1 ?? '---'}</p></div>
                    <div class="dato-item"><label>Teléfono 2:</label><p>${d.telefono2 ?? '---'}</p></div>
                </div>

                <hr class="separador-datos">

                <h6 class="titulo-seccion">Datos del Aspirante</h6>

                <div class="datos-grid-3">
                    <div class="dato-item"><label>Nombre:</label><p>${d.alumnoNombre ?? '---'}</p></div>
                    <div class="dato-item"><label>Apellido Paterno:</label><p>${d.alumnoPaterno ?? '---'}</p></div>
                    <div class="dato-item"><label>Apellido Materno:</label><p>${d.alumnoMaterno ?? '---'}</p></div>
                </div>

                <div class="datos-flex-center mt-3">
                    <div class="dato-item"><label>RFC:</label><p>${d.rfc ?? '---'}</p></div>
                    <div class="dato-item"><label>CURP:</label><p>${d.curp ?? '---'}</p></div>
                </div>

            </div>
        `;
    });
});
</script>

<script>
document.addEventListener('click', function (e) {

    const check = e.target.closest('.icon-check.clickeable');
    if (!check) return;

    if (!['ctp', 'master'].includes(window.ROLE_ACTIVO)) {
        alert('No tienes permisos');
        return;
    }

    const nuevoEstado = check.dataset.estado;
    const leadId = document.querySelector('.fila-lead.activo')?.dataset.id;
    if (!leadId) return;

    fetch(`/crm/leads/${leadId}/seguimiento`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ estado: nuevoEstado })
    }).then(() => location.reload());
});
</script>

<script>
let LEAD_SELECCIONADO = null;

document.querySelectorAll('.btn-asignar-ctp').forEach(btn => {
    btn.addEventListener('click', () => {
        LEAD_SELECCIONADO = btn.dataset.lead;
        document.getElementById('modal-ctp').classList.remove('hidden');
    });
});

document.getElementById('cerrar-ctp').addEventListener('click', () => {
    document.getElementById('modal-ctp').classList.add('hidden');
});

document.getElementById('guardar-ctp').addEventListener('click', () => {
    const ctpId = document.getElementById('ctp-select').value;

    if (!ctpId || !LEAD_SELECCIONADO) {
        alert('Selecciona un CTP');
        return;
    }

    fetch(`/crm/leads/${LEAD_SELECCIONADO}/asignar-ctp`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ctp_id: ctpId })
    })
    .then(() => location.reload());
 });

</script>



@endsection