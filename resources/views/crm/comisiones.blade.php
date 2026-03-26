@extends('layouts.app')
@section('title', 'CRM - Comisiones')
@section('content')
<div class="crm-comisiones">
   <div class="header-top">
      <h1>COMISIONES</h1>
   </div>
   <div class="toolbar">
      <div class="filtros-izquierda">
         <div class="input-group-custom">
            <img src="{{ asset('images/icons/calendario.svg') }}" class="icon-calendar">
            <input type="date" class="input-custom" id="fecha-inicio">
         </div>
         <div class="input-group-custom">
            <img src="{{ asset('images/icons/calendario.svg') }}" class="icon-calendar">
            <input type="date" class="input-custom" id="fecha-fin">
         </div>
         <div class="input-group-custom search-wrapper">
            <img src="{{ asset('images/icons/search.svg') }}" alt="Search" width="16">
            <input type="text" class="input-custom buscador-ctp" placeholder="Buscar por CTP">
         </div>
      </div>
      <div class="toolbar-acciones">
         <button class="btn-comision">
         % Comisión
         </button>
         <button class="btn-exportar">
         <img src="{{ asset('images/icons/export.svg') }}" alt="Exportar" width="16">
         Exportar
         </button>
      </div>
   </div>
   <div class="table-container">
      <div class="table-card">
         <div class="table-row-header">
            <div class="col-ctp">CTP</div>
            <div class="col-conversiones"># Conversiones</div>
            <div class="col-total">Monto de conversion</div>
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
                  <img src="{{ asset('images/icons/download.svg') }}"
                     alt="Descargar"
                     class="icon-accion btn-descargar-pdf-comision"
                     title="Descargar PDF">
               </div>
            </div>
            @empty
            <p class="sin-registros">No hay CTPs registrados.</p>
            @endforelse
         </div>
      </div>
   </div>
</div>

<!-- MODAL % COMISIÓN -->
<div id="modal-comision" class="modal-comision d-none">
   <div class="modal-comision-content">
      <div class="modal-comision-header">
         <h5>% Comisión</h5>
         <button id="cerrar-modal-comision" class="btn-cerrar-modal">&times;</button>
      </div>
      <div class="modal-comision-body">
         <!-- Clasificación -->
         <div class="mc-campo">
            <label class="mc-label">Clasificación:</label>
            <div class="mc-select-wrapper">
               <select id="mc-clasificacion" class="mc-select">
                  <option value="">Seleccione el producto</option>
                  <option value="Licenciatura">Licenciatura</option>
                  <option value="Posgrado">Posgrado</option>
                  <option value="Maestría">Maestría</option>
                  <option value="Doctorado">Doctorado</option>
               </select>
            </div>
         </div>
         <!-- Producto -->
         <div class="mc-campo">
            <label class="mc-label">Producto:</label>
            <div class="mc-select-wrapper">
               <select id="mc-producto" class="mc-select">
                  <option value="">Seleccione el producto</option>
                  @foreach($carreras as $carrera)
                  <option value="{{ $carrera->id }}" data-nombre="{{ $carrera->nombre }}">
                     {{ $carrera->nombre }}
                  </option>
                  @endforeach
               </select>
            </div>
         </div>
         <!-- Precio y % comisión -->
         <div class="mc-fila-2">
            <div class="mc-campo-inline">
               <label class="mc-label">Precio:</label>
               <div class="mc-number-wrapper">
                  <input type="number" id="mc-precio" class="mc-input-number" value="0" min="0">
               </div>
            </div>
            <div class="mc-campo-inline">
               <label class="mc-label">% de comisión:</label>
               <div class="mc-number-wrapper">
                  <input type="number" id="mc-porcentaje" class="mc-input-number" value="0" min="0" max="100">
               </div>
            </div>
         </div>
         <!-- Tabla -->
         <div class="mc-table-container">
            <div class="mc-table-card">
               <div class="mc-table-header">
                  <div>Clasificación</div>
                  <div>Producto</div>
                  <div>Precio (Mes)</div>
                  <div>% de Comisión</div>
                  <div>Total</div>
                  <div>Acciones</div>
               </div>
               <div class="mc-table-body" id="mc-tabla-body">
                  <!-- filas dinámicas -->
               </div>
            </div>
         </div>
      </div>
      <!-- Botón Agregar -->
      <div class="mc-footer">
         <button id="mc-btn-agregar" class="mc-btn-agregar">+ Agregar</button>
      </div>
   </div>
</div>

<!-- MODAL DETALLE COMISIONES -->
<div id="modal-detalle-comision" class="modal-prospecto d-none">
    <div class="modal-prospecto-content">

        <!-- HEADER -->
        <div class="modal-prospecto-header">
            <h5>Detalle del Monto de conversión</h5>
            <button id="cerrar-modal-detalle" class="btn-cerrar-modal">&times;</button>
        </div>

        <!-- BODY -->
        <div class="modal-prospecto-body">

            <!-- TITULO -->
            <div class="modal-seccion-titulo text-center">
                <strong>MONTO DE CONVERSIÓN</strong>
            </div>

            <!-- TABLA -->
            <div class="mc-table-container">
                <div class="mc-table-card">

                    <div class="mc-table-header">
                        <div>Clasificación</div>
                        <div>Alumno</div>
                        <div>Precio de comisión</div>
                    </div>

                    <div class="mc-table-body" id="detalle-comision-body">
                        <!-- FILAS DINÁMICAS -->
                        <div class="mc-table-row">
                            <div class="mc-celda">Licenciatura</div>
                            <div class="mc-celda">1</div>
                            <div class="mc-celda">$2,500</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TOTAL -->
            <div class="mt-3">
                <strong>Total:</strong> <span id="detalle-total">$0.00</span>
            </div>

        </div>

    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
   (function initComisiones() {
       // Evitar doble init
       const tabla = document.getElementById('tabla-comisiones');
       if (!tabla || tabla.dataset.init) return;
       tabla.dataset.init = 'true';
   
       // ===== BUSCADOR =====
       const buscadorCTP = document.querySelector('.buscador-ctp');
       const fechaInicio = document.getElementById('fecha-inicio');
       const fechaFin    = document.getElementById('fecha-fin');
   
       function aplicarFiltros() {
           const texto  = buscadorCTP?.value.toLowerCase().trim() ?? '';
           const inicio = fechaInicio?.value ?? '';
           const fin    = fechaFin?.value ?? '';
           document.querySelectorAll('#tabla-comisiones .table-row').forEach(fila => {
               const ctp       = (fila.getAttribute('data-ctp') || '').toLowerCase();
               const fechaFila = (fila.getAttribute('data-fecha') || '');
               let visible = true;
               if (texto  && !ctp.includes(texto))  visible = false;
               if (inicio && fechaFila < inicio)     visible = false;
               if (fin    && fechaFila > fin)         visible = false;
               fila.style.display = visible ? '' : 'none';
           });
       }
   
       buscadorCTP?.addEventListener('input',  aplicarFiltros);
       fechaInicio?.addEventListener('change', aplicarFiltros);
       fechaFin?.addEventListener('change',    aplicarFiltros);
   
       // Abrir calendario al click en ícono
       document.querySelectorAll('.icon-calendar').forEach(icon => {
           icon.addEventListener('click', function () {
               this.nextElementSibling?.showPicker();
           });
       });
   
       // ===== BOTÓN OJO =====
    const modalDetalle = document.getElementById('modal-detalle-comision');
    const cerrarDetalle = document.getElementById('cerrar-modal-detalle');
    const tablaDetalle = document.getElementById('detalle-comision-body');
    const totalDetalle = document.getElementById('detalle-total');

    document.querySelectorAll('.btn-ver-ctp').forEach(btn => {
        btn.addEventListener('click', function () {

            const ctpId = this.getAttribute('data-id');

            // 🔥 LIMPIAR TABLA
            tablaDetalle.innerHTML = '';

            // 🔥 EJEMPLO FRONT (luego aquí va backend)
            const datosEjemplo = [
                { clasificacion: 'Licenciatura', alumno: '1', precio: 2500 },
            ];

            let total = 0;

            datosEjemplo.forEach(item => {
                total += item.precio;

                const fila = document.createElement('div');
                fila.className = 'mc-table-row';

                fila.innerHTML = `
                    <div class="mc-celda">${item.clasificacion}</div>
                    <div class="mc-celda">${item.alumno}</div>
                    <div class="mc-celda">$${item.precio.toLocaleString('es-MX')}</div>
                `;

                tablaDetalle.appendChild(fila);
            });

            totalDetalle.textContent = `$${total.toLocaleString('es-MX')}`;

            // 🔥 ABRIR MODAL
            modalDetalle.classList.remove('d-none');
        });
    });

    // CERRAR MODAL
    cerrarDetalle.addEventListener('click', () => {
        modalDetalle.classList.add('d-none');
    });

    // CERRAR CLICK FUERA
    modalDetalle.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('d-none');
        }
    });

    document.querySelectorAll('.btn-descargar-pdf-comision').forEach(btn => {
    btn.addEventListener('click', function () {

        if (!window.jspdf) {
            alert('Cargando librería...');
            return;
        }

        const fila = this.closest('.table-row');
        const nombreCTP = fila.querySelector('.col-ctp').innerText;

        // 👇 OBTENER FILAS DEL MODAL (YA GENERADAS)
        const filas = document.querySelectorAll('#detalle-comision-body .mc-table-row');

        if (filas.length === 0) {
            alert('Primero abre el detalle (👁️) para generar el PDF');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const azulOscuro = [13, 27, 42];
        const azulMedio  = [31, 58, 99];
        const grisF      = [245, 247, 250];

        const W = 210;
        const M = 14;

        // HEADER
        doc.setFillColor(...azulOscuro);
        doc.rect(0, 0, W, 30, 'F');

        doc.setTextColor(255,255,255);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(16);
        doc.text('DETALLE DE COMISIONES', W/2, 15, { align: 'center' });

        doc.setFontSize(10);
        doc.text(nombreCTP, W/2, 23, { align: 'center' });

        let y = 40;

        // TITULO
        doc.setFillColor(...azulOscuro);
        doc.roundedRect(M, y - 5, W - M*2, 10, 2, 2, 'F');

        doc.setTextColor(255,255,255);
        doc.setFontSize(9);
        doc.text('MONTO DE CONVERSIÓN', W/2, y+2, { align: 'center' });

        y += 10;

        // HEADER TABLA
        doc.setFillColor(...azulMedio);
        doc.rect(M, y, W - M*2, 8, 'F');

        doc.setTextColor(255,255,255);
        doc.setFontSize(8);

        const col1 = M + 5;
        const col2 = 90;
        const col3 = 150;

        doc.text('Clasificación', col1, y+5);
        doc.text('Alumno', col2, y+5);
        doc.text('Precio', col3, y+5);

        y += 8;

        let total = 0;

        filas.forEach((f, i) => {
            const celdas = f.querySelectorAll('.mc-celda');

            const clasificacion = celdas[0].innerText;
            const alumno        = celdas[1].innerText;
            const precio        = celdas[2].innerText;

            total += parseFloat(precio.replace(/[$,]/g, ''));

            if (i % 2 === 0) {
                doc.setFillColor(...grisF);
                doc.rect(M, y, W - M*2, 8, 'F');
            }

            doc.setTextColor(30,30,30);
            doc.text(clasificacion, col1, y+5);
            doc.text(alumno, col2, y+5);
            doc.text(precio, col3, y+5);

            y += 8;
        });

        // TOTAL
        y += 5;
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(...azulMedio);
        doc.text(`Total: $${total.toLocaleString('es-MX')}`, W - M, y, { align: 'right' });

        // FOOTER
        doc.setFillColor(...azulOscuro);
        doc.rect(0, 280, W, 15, 'F');

        doc.setTextColor(255,255,255);
        doc.setFontSize(8);
        doc.text(`Generado el ${new Date().toLocaleDateString('es-MX')}`, W/2, 290, { align: 'center' });

        doc.save(`comisiones_${nombreCTP}.pdf`);
    });
});

      
       // ===== EXPORTAR EXCEL =====
       document.querySelector('.btn-exportar')?.addEventListener('click', () => {
           const datos = [];
           datos.push([`Reporte de Comisiones`]);
           datos.push([`Generado el: ${new Date().toLocaleDateString('es-MX')}`]);
           datos.push([]);
           datos.push(["CTP", "Número de Conversiones", "Total de Comisiones"]);
           document.querySelectorAll('#tabla-comisiones .table-row').forEach(fila => {
               if (fila.style.display === 'none') return;
               datos.push([
                   fila.querySelector('.col-ctp')?.innerText.trim(),
                   fila.querySelector('.col-conversiones')?.innerText.trim(),
                   fila.querySelector('.col-total')?.innerText.trim(),
               ]);
           });
           const hoja = XLSX.utils.aoa_to_sheet(datos);
           hoja['!cols'] = [{ wch: 30 }, { wch: 25 }, { wch: 22 }];
           const libro = XLSX.utils.book_new();
           XLSX.utils.book_append_sheet(libro, hoja, "Comisiones");
           XLSX.writeFile(libro, `comisiones_${new Date().toISOString().slice(0,10)}.xlsx`);
       });
   
       // ===== MODAL % COMISIÓN =====
       const modalComision  = document.getElementById('modal-comision');
       const btnComision    = document.querySelector('.btn-comision');
       const cerrarComision = document.getElementById('cerrar-modal-comision');
       const mcTablaBody    = document.getElementById('mc-tabla-body');
       let filaEditando     = null;
   
       btnComision?.addEventListener('click', () => modalComision.classList.remove('d-none'));
       cerrarComision?.addEventListener('click', () => modalComision.classList.add('d-none'));
       modalComision?.addEventListener('click', function(e) {
           if (e.target === this) this.classList.add('d-none');
       });
   
       document.getElementById('mc-btn-agregar')?.addEventListener('click', () => {
           const clasificacion = document.getElementById('mc-clasificacion').value;
           const productoSel   = document.getElementById('mc-producto');
           const producto      = productoSel.options[productoSel.selectedIndex]?.text || '';
           const precio        = parseFloat(document.getElementById('mc-precio').value) || 0;
           const porcentaje    = parseFloat(document.getElementById('mc-porcentaje').value) || 0;
   
           if (!clasificacion || !producto || producto === 'Seleccione el producto') {
               alert('Por favor selecciona Clasificación y Producto.');
               return;
           }
   
           const total = ((precio * porcentaje) / 100).toFixed(2);
   
           if (filaEditando) {
               const celdas = filaEditando.querySelectorAll('.mc-celda');
               celdas[0].textContent = clasificacion;
               celdas[1].textContent = producto;
               celdas[2].textContent = `$${precio.toLocaleString('es-MX')}`;
               celdas[3].textContent = `${porcentaje}%`;
               celdas[4].textContent = `$${parseFloat(total).toLocaleString('es-MX')}`;
               filaEditando = null;
               document.getElementById('mc-btn-agregar').textContent = '+ Agregar';
           } else {
               const fila = document.createElement('div');
               fila.className = 'mc-table-row';
               fila.innerHTML = `
                   <div class="mc-celda">${clasificacion}</div>
                   <div class="mc-celda">${producto}</div>
                   <div class="mc-celda">$${precio.toLocaleString('es-MX')}</div>
                   <div class="mc-celda">${porcentaje}%</div>
                   <div class="mc-celda">$${parseFloat(total).toLocaleString('es-MX')}</div>
                   <div class="mc-celda"><span class="icon-editar" title="Editar">&#9998;</span></div>
               `;
               fila.querySelector('.icon-editar').addEventListener('click', () => {
                   const celdas = fila.querySelectorAll('.mc-celda');
                   document.getElementById('mc-clasificacion').value = celdas[0].textContent;
                   document.getElementById('mc-precio').value        = celdas[2].textContent.replace(/[$,]/g, '');
                   document.getElementById('mc-porcentaje').value    = celdas[3].textContent.replace('%', '');
                   Array.from(document.getElementById('mc-producto').options).forEach(opt => {
                       if (opt.text === celdas[1].textContent) document.getElementById('mc-producto').value = opt.value;
                   });
                   filaEditando = fila;
                   document.getElementById('mc-btn-agregar').textContent = '💾 Guardar';
               });
               mcTablaBody.appendChild(fila);
           }
   
           document.getElementById('mc-clasificacion').value = '';
           document.getElementById('mc-producto').value      = '';
           document.getElementById('mc-precio').value        = 0;
           document.getElementById('mc-porcentaje').value    = 0;
       });
   
   })(); // <-- IIFE: se ejecuta inmediatamente
</script>
@endsection