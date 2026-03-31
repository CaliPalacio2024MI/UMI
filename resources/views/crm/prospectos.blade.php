@extends('layouts.app')
@section('title', 'CRM - Prospectos')
@section('content')
<link rel="stylesheet" href="{{ Vite::asset('resources/css/CRM/prospectos.css') }}">
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
            <img src="{{ asset('images/icons/calendario.svg') }}" class="icon-calendar">
            <input type="date" class="input-custom" id="fecha-inicio">
         </div>
         <!-- Fecha Fin -->
         <div class="input-group-custom">
            <img src="{{ asset('images/icons/calendario.svg') }}" class="icon-calendar">
            <input type="date" class="input-custom" id="fecha-fin">
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
      <img src="{{ asset('images/icons/export.svg') }}" alt="Search" width="16">
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
            <div class="col-paterno">Apellido<br>Paterno</div>
            <div class="col-materno">Carrera</div>
            <div class="col-ctp">CTP</div>
            <div class="col-tipo">Estatus</div>
            <div class="col-fecha">Fecha</div>
            <div class="col-acciones">Acciones</div>
         </div>
         <!-- BODY -->
         <div class="table-body">
            @forelse($leads as $lead)
            <div class="table-row"
               data-id="{{ $lead->id }}"
               data-tutor-nombre="{{ $lead->tutor_nombre }}"
               data-tutor-paterno="{{ $lead->tutor_paterno }}"
               data-tutor-materno="{{ $lead->tutor_materno }}"
               data-tutor-curp="{{ $lead->tutor_curp }}"
               data-tutor-email="{{ $lead->tutor_email }}"
               data-telefono1="{{ $lead->telefono1 }}"
               data-telefono2="{{ $lead->telefono2 }}"
               data-alumno-nombre="{{ $lead->alumno_nombre }}"
               data-alumno-paterno="{{ $lead->alumno_paterno }}"
               data-alumno-materno="{{ $lead->alumno_materno }}"
               data-alumno-curp="{{ $lead->alumno_curp }}"
               data-ctp="{{ $lead->ctp ? $lead->ctp->nombre.' '.$lead->ctp->apellido_paterno : 'Sin asignar' }}"
               data-carrera="{{ $lead->carrera->nombre ?? 'Sin carrera' }}"
               data-seguimientos='@json($lead->seguimientos)'
               >
               <div class="col-curp">{{ $lead->alumno_curp }}</div>
               <div class="col-nombre">{{ $lead->alumno_nombre }}</div>
               <div class="col-paterno">{{ $lead->alumno_paterno }}</div>
               <div class="col-materno">{{ $lead->carrera->nombre ?? 'Sin carrera'}}</div>
               <div class="col-ctp">{{ $lead->ctp ? $lead->ctp->nombre.' '.$lead->ctp->apellido_paterno : 'Sin asignar' }}</div>
               <div class="col-tipo">
                  @if($lead->ctp_id)
                  {{ $lead->seguimientos()->orderBy('id', 'desc')->first()?->estado ?? 'Prospecto frío' }}
                  @else
                  Sin asignar
                  @endif
               </div>
               <div class="col-fecha">{{ $lead->created_at->format('Y-m-d') }}</div>
               <div class="col-acciones">
                  <img src="{{ asset('images/icons/eye.svg') }}" class="icon-accion btn-ver-prospecto" title="Ver">
                  <img src="{{ asset('images/icons/download.svg') }}" class="icon-accion btn-descargar-pdf" title="Descargar PDF">
               </div>
            </div>
            @empty
            <p>No hay prospectos.</p>
            @endforelse
         </div>
      </div>
   </div>
   <!-- MODAL VER PROSPECTO -->
   <div id="modal-prospecto" class="modal-prospecto d-none">
      <div class="modal-prospecto-content">
         <div class="modal-prospecto-header">
            <h5>Detalle del Prospecto</h5>
            <button id="cerrar-modal-prospecto" class="btn-cerrar-modal">&times;</button>
         </div>
         <div class="modal-prospecto-body">
            <!-- SEGUIMIENTO -->
            <div class="modal-seccion-titulo">SEGUIMIENTO</div>
            <div class="modal-seguimiento-header">
               <span>Estado</span>
               <span>Fecha</span>
               <span>Hora</span>
            </div>
            <div id="modal-seguimiento-body"></div>
            <!-- DATOS GENERALES -->
            <div class="modal-seccion-titulo mt-3">DATOS GENERALES</div>
            <div class="modal-datos-card">
               <div class="modal-titulo-card">Datos del Tutor</div>
               <div class="modal-fila-3">
                  <div class="modal-campo">
                     <label>Nombre</label>
                     <p id="m-tutor-nombre"></p>
                  </div>
                  <div class="modal-campo">
                     <label>Apellido Paterno</label>
                     <p id="m-tutor-paterno"></p>
                  </div>
                  <div class="modal-campo">
                     <label>Apellido Materno</label>
                     <p id="m-tutor-materno"></p>
                  </div>
               </div>
               <div class="modal-fila-3">
                  <div class="modal-campo">
                     <label>Teléfono 1</label>
                     <p id="m-tel1"></p>
                  </div>
                  <div class="modal-campo">
                     <label>Teléfono 2</label>
                     <p id="m-tel2"></p>
                  </div>
                  <div class="modal-campo">
                     <label>Correo</label>
                     <p id="m-tutor-email"></p>
                  </div>
               </div>
               <div class="modal-fila-centro">
                  <div class="modal-campo">
                     <label>CURP</label>
                     <p id="m-tutor-curp"></p>
                  </div>
               </div>
            </div>
            <div class="modal-datos-card">
               <div class="modal-titulo-card">Datos del Aspirante</div>
               <div class="modal-fila-3">
                  <div class="modal-campo">
                     <label>Nombre</label>
                     <p id="m-alumno-nombre"></p>
                  </div>
                  <div class="modal-campo">
                     <label>Apellido Paterno</label>
                     <p id="m-alumno-paterno"></p>
                  </div>
                  <div class="modal-campo">
                     <label>Apellido Materno</label>
                     <p id="m-alumno-materno"></p>
                  </div>
               </div>
               <div class="modal-fila-centro">
                  <div class="modal-campo">
                     <label>CURP</label>
                     <p id="m-alumno-curp"></p>
                  </div>
               </div>
               <div class="carrera-panel mt-2">
                  <label>Plan de estudios / Carrera:</label>
                  <p id="m-carrera"></p>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
const LOGO_BASE64 = "{{ $logoBase64 }}";
(function() {
    const ESTADOS = ['Prospecto frío','Prospecto caliente','Aspirante','Alumno'];
    // Descargar PDF
    document.querySelectorAll('.btn-descargar-pdf').forEach(btn => {
        btn.addEventListener('click', function () {
    if (!window.jspdf) {
        alert('Cargando librería, intenta de nuevo en un momento.');
        return;
    }
    const fila = this.closest('.table-row');
    const d    = fila.dataset;
    const seguimientos = JSON.parse(d.seguimientos || '[]');
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // ── PALETA DE COLORES ──
    const azulOscuro  = [13, 27, 42];    // casi negro azulado (como el formato empresa)
    const azulMedio   = [31, 58, 99];    // azul medio para acentos
    const azulClaro   = [220, 230, 242]; // fondo de headers internos
    const grisF       = [245, 247, 250]; // fondo de cards
    const grisBorde   = [210, 215, 225]; // bordes de tabla
    const W           = 210;
    const M           = 14;

    const drawField = (label, value, x, fy) => {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(7.5);
        doc.setTextColor(100, 100, 100);
        doc.text(label, x, fy);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8.5);
        doc.setTextColor(30, 30, 30);
        doc.text(value || '---', x, fy + 5);
    };

    // ── HEADER ──────────────────────────────────
      // Fondo blanco
      doc.setFillColor(255, 255, 255);
      doc.rect(0, 0, W, 38, 'F');

      // Logo azul — esquina izquierda
      doc.addImage(LOGO_BASE64, 'PNG', 8, 3, 28, 28);


      // Título a la derecha del logo
      doc.setTextColor(...azulOscuro);
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(18);
      doc.text('FICHA DEL PROSPECTO', W / 2, 18, { align: 'center' });

      // Nombre del prospecto
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(10);
      doc.setTextColor(80, 80, 80);
      const nombreCompleto = `${d.alumnoNombre || ''} ${d.alumnoPaterno || ''} ${d.alumnoMaterno || ''}`.trim();
      doc.text(nombreCompleto, W / 2, 28, { align: 'center' });

      // Línea divisoria azul
      doc.setDrawColor(...azulMedio);
      doc.setLineWidth(1.5);
      doc.line(0, 38, W, 38);

      // ── SECCIÓN SEGUIMIENTO ── (ajusta y a 48 en lugar de 40)
      let y = 48;

    // Título sección
    doc.setFillColor(...azulOscuro);
    doc.roundedRect(M, y - 5, W - M * 2, 10, 1, 1, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(255, 255, 255);
    doc.text('SEGUIMIENTO', W / 2, y + 1.5, { align: 'center' });

    y += 10;

    // Header tabla seguimiento
    doc.setFillColor(...azulMedio);
    doc.rect(M, y, W - M * 2, 8, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(8);
    doc.setFont('helvetica', 'bold');
    const col1 = M + 5, col2 = 90, col3 = 148;
    doc.text('Estado', col1, y + 5.5);
    doc.text('Fecha',  col2, y + 5.5);
    doc.text('Hora',   col3, y + 5.5);

    // Borde exterior de la tabla
    doc.setDrawColor(...grisBorde);
    doc.setLineWidth(0.4);
    doc.rect(M, y, W - M * 2, 8 + (4 * 8), 'S');

    y += 8;
    const ESTADOS_LIST = ['Prospecto frío', 'Prospecto caliente', 'Aspirante', 'Alumno'];
    ESTADOS_LIST.forEach((estado, i) => {
        const reg = seguimientos.find(s => s.estado === estado);

        // Fondo alterno
        if (i % 2 === 1) {
            doc.setFillColor(...grisF);
            doc.rect(M, y, W - M * 2, 8, 'F');
        }

        doc.setFontSize(8.5);
        if (reg) {
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(...azulMedio);
            // Punto indicador azul en lugar de dorado
            doc.setFillColor(...azulMedio);
            doc.circle(col1 - 2, y + 4, 1.2, 'F');
        } else {
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(180, 180, 180);
        }
        doc.text(estado,          col1 + 2, y + 5.5);
        doc.text(reg?.fecha ?? '---', col2, y + 5.5);
        doc.text(reg?.hora  ?? '---', col3, y + 5.5);

        // Línea separadora horizontal
        doc.setDrawColor(...grisBorde);
        doc.setLineWidth(0.2);
        doc.line(M, y + 8, W - M, y + 8);
        y += 8;
    });

    // ── SECCIÓN DATOS GENERALES ──────────────────
    y += 8;

    // Título sección (mismo estilo que SEGUIMIENTO)
    doc.setFillColor(...azulOscuro);
    doc.roundedRect(M, y - 5, W - M * 2, 10, 1, 1, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(255, 255, 255);
    doc.text('DATOS GENERALES', W / 2, y + 1.5, { align: 'center' });

    // ── CARD TUTOR ───────────────────────────────
    y += 12;
    const cardTutorH = 52;
    doc.setFillColor(...grisF);
    doc.roundedRect(M, y, W - M * 2, cardTutorH, 2, 2, 'F');
    doc.setDrawColor(...grisBorde);
    doc.setLineWidth(0.4);
    doc.roundedRect(M, y, W - M * 2, cardTutorH, 2, 2, 'S');

    // Subtítulo "Datos del Tutor" — azul en lugar de dorado
    doc.setTextColor(...azulMedio);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.text('Datos del Tutor', W / 2, y + 7, { align: 'center' });
    // Línea bajo el título — azul
    doc.setDrawColor(...azulMedio);
    doc.setLineWidth(0.4);
    doc.line(W / 2 - 20, y + 8.5, W / 2 + 20, y + 8.5);

    const colW = (W - M * 2) / 3;
    const x1 = M + 4, x2 = M + colW + 4, x3 = M + colW * 2 + 4;

    const f1y = y + 16;
    drawField('Nombre',           d.tutorNombre,  x1, f1y);
    drawField('Apellido Paterno', d.tutorPaterno, x2, f1y);
    drawField('Apellido Materno', d.tutorMaterno, x3, f1y);

    doc.setDrawColor(...grisBorde);
    doc.setLineWidth(0.2);
    doc.line(M + 4, f1y + 8, W - M - 4, f1y + 8);

    const f2y = f1y + 14;
    drawField('Teléfono 1', d.telefono1,  x1, f2y);
    drawField('Teléfono 2', d.telefono2,  x2, f2y);
    drawField('Correo',     d.tutorEmail, x3, f2y);

    doc.setDrawColor(...grisBorde);
    doc.setLineWidth(0.2);
    doc.line(M + 4, f2y + 8, W - M - 4, f2y + 8);

    const f3y = f2y + 14;
    drawField('CURP', d.tutorCurp, W / 2 - 15, f3y);

    // ── CARD ASPIRANTE ───────────────────────────
    y += 60;
    const cardAspH = 55;
    doc.setFillColor(...grisF);
    doc.roundedRect(M, y, W - M * 2, cardAspH, 2, 2, 'F');
    doc.setDrawColor(...grisBorde);
    doc.setLineWidth(0.4);
    doc.roundedRect(M, y, W - M * 2, cardAspH, 2, 2, 'S');

    // Subtítulo "Datos del Aspirante" — azul
    doc.setTextColor(...azulMedio);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.text('Datos del Aspirante', W / 2, y + 7, { align: 'center' });
    doc.setDrawColor(...azulMedio);
    doc.setLineWidth(0.4);
    doc.line(W / 2 - 22, y + 8.5, W / 2 + 22, y + 8.5);

    const a1y = y + 16;
    drawField('Nombre',           d.alumnoNombre,  x1, a1y);
    drawField('Apellido Paterno', d.alumnoPaterno, x2, a1y);
    drawField('Apellido Materno', d.alumnoMaterno, x3, a1y);

    doc.setDrawColor(...grisBorde);
    doc.setLineWidth(0.2);
    doc.line(M + 4, a1y + 8, W - M - 4, a1y + 8);

    const a2y = a1y + 14;
    drawField('CURP', d.alumnoCurp, W / 2 - 15, a2y);

    // Panel de carrera — azul oscuro
    const a3y = a2y + 14;
    doc.setFillColor(...azulClaro);
    doc.roundedRect(M, a3y - 4, W - M * 2, 14, 2, 2, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(7.5);
    doc.setTextColor(80, 80, 80);
    doc.text('Plan de estudios / Carrera:', W / 2, a3y + 1, { align: 'center' });
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(...azulMedio);
    doc.text(d.carrera || '---', W / 2, a3y + 7, { align: 'center' });


    // ── FOOTER / LEYENDA LEGAL ───────────────────────────
   doc.setFont('helvetica', 'normal');
   doc.setFontSize(7);
   doc.setTextColor(120, 120, 120);
   doc.text('Propiedad de Mundo Imperial.', W / 2, 281, { align: 'center' });
   doc.text('Prohibida su reproducción total o parcial sin previa autorización por escrito de Mundo Imperial.', W / 2, 286, { align: 'center' });

    const nombreArchivo = `prospecto_${(d.alumnoPaterno || 'sin_nombre').toLowerCase()}_${(d.alumnoNombre || '').toLowerCase()}.pdf`;
    doc.save(nombreArchivo);
});
});

    // Buscador
    const buscador = document.querySelector('.buscador');
    if (buscador) {
        buscador.addEventListener('input', function() {
            const texto = this.value.toLowerCase().trim();
            document.querySelectorAll('.table-row').forEach(fila => {
                fila.style.display = fila.innerText.toLowerCase().includes(texto) ? '' : 'none';
            });
        });
    }
    // Filtro por fechas
const fechaInicio = document.getElementById('fecha-inicio');
const fechaFin    = document.getElementById('fecha-fin');

function filtrarPorFecha() {
    const inicio = fechaInicio.value; // "2026-03-04"
    const fin    = fechaFin.value;

    document.querySelectorAll('.table-row').forEach(fila => {
        const fechaFila = fila.querySelector('.col-fecha')?.textContent.trim(); // "2026-03-04"

        let visible = true;

        if (inicio && fechaFila < inicio) visible = false;
        if (fin    && fechaFila > fin)    visible = false;

        fila.style.display = visible ? '' : 'none';
    });
}

fechaInicio.addEventListener('change', filtrarPorFecha);
fechaFin.addEventListener('change',    filtrarPorFecha);

// Abrir calendario al hacer click en el icono
document.querySelectorAll('.icon-calendar').forEach(icon => {
    icon.addEventListener('click', function () {
        this.nextElementSibling.showPicker();
    });
});
    // Abrir modal al click en ojo
    document.querySelectorAll('.btn-ver-prospecto').forEach(btn => {
        btn.addEventListener('click', function () {
            const fila = this.closest('.table-row');
            const d    = fila.dataset;

            // Datos generales
            document.getElementById('m-tutor-nombre').textContent   = d.tutorNombre   || '---';
            document.getElementById('m-tutor-paterno').textContent  = d.tutorPaterno  || '---';
            document.getElementById('m-tutor-materno').textContent  = d.tutorMaterno  || '---';
            document.getElementById('m-tutor-curp').textContent     = d.tutorCurp     || '---';
            document.getElementById('m-tutor-email').textContent    = d.tutorEmail    || '---';
            document.getElementById('m-tel1').textContent           = d.telefono1     || '---';
            document.getElementById('m-tel2').textContent           = d.telefono2     || '---';
            document.getElementById('m-alumno-nombre').textContent  = d.alumnoNombre  || '---';
            document.getElementById('m-alumno-paterno').textContent = d.alumnoPaterno || '---';
            document.getElementById('m-alumno-materno').textContent = d.alumnoMaterno || '---';
            document.getElementById('m-alumno-curp').textContent    = d.alumnoCurp    || '---';
            document.getElementById('m-carrera').textContent = d.carrera || '---';

            // Seguimiento
            const seguimientos = JSON.parse(d.seguimientos || '[]');
            const body = document.getElementById('modal-seguimiento-body');
            body.innerHTML = '';

            ESTADOS.forEach(estado => {
                const reg = seguimientos.find(s => s.estado === estado);
                body.innerHTML += `
                    <div class="modal-seg-row ${reg ? 'registrado' : 'pendiente'}">
                        <span>${estado}</span>
                        <span>${reg?.fecha ?? '---'}</span>
                        <span>${reg?.hora  ?? '---'}</span>
                    </div>
                `;
            });

            document.getElementById('modal-prospecto').classList.remove('d-none');
        });
    });

    // Cerrar modal
    document.getElementById('cerrar-modal-prospecto').addEventListener('click', () => {
        document.getElementById('modal-prospecto').classList.add('d-none');
    });

    // Cerrar al click fuera
    document.getElementById('modal-prospecto').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('d-none');
    });
    // EXPORTAR A EXCEL PROFESIONAL
const btnExportar = document.querySelector('.btn-exportar');

btnExportar.addEventListener('click', () => {
    if (!window.XLSX) {
        alert('La librería de exportación aún no está lista, intenta de nuevo.');
        return;
    }

    const datos = [];

    // fecha de generación
    const fechaExport = new Date().toLocaleDateString('es-MX');

    datos.push([`Reporte de Prospectos`]);
    datos.push([`Generado el: ${fechaExport}`]);
    datos.push([]);

    // encabezados
    datos.push([
        "CURP",
        "Nombre",
        "Apellido Paterno",
        "Carrera",
        "CTP",
        "Estatus",
        "Fecha"
    ]);

    document.querySelectorAll('.table-row').forEach(fila => {

        if (fila.style.display === 'none') return;

        const curp = fila.querySelector('.col-curp')?.innerText.trim();
        const nombre = fila.querySelector('.col-nombre')?.innerText.trim();
        const paterno = fila.querySelector('.col-paterno')?.innerText.trim();
        const materno = fila.querySelector('.col-materno')?.innerText.trim();
        const ctp = fila.querySelector('.col-ctp')?.innerText.trim();
        const estatus = fila.querySelector('.col-tipo')?.innerText.trim();
        const fecha = fila.querySelector('.col-fecha')?.innerText.trim();

        datos.push([
            curp,
            nombre,
            paterno,
            materno,
            ctp,
            estatus,
            fecha
        ]);
    });

    const hoja = XLSX.utils.aoa_to_sheet(datos);

    // ancho de columnas
    hoja['!cols'] = [
        { wch: 22 },
        { wch: 18 },
        { wch: 18 },
        { wch: 18 },
        { wch: 18 },
        { wch: 20 },
        { wch: 12 }
    ];

    // crear libro
    const libro = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(libro, hoja, "Prospectos");

    // nombre con fecha
    const fechaArchivo = new Date().toISOString().slice(0,10);

    XLSX.writeFile(libro, `prospectos_${fechaArchivo}.xlsx`);

});

})();
</script>
@endsection