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
               <div class="col-total">${{ number_format($ctp->total_comisiones, 2) }}</div>
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

      <!-- HEADER -->
      <div class="modal-comision-header">
         <h5>% Comisión</h5>
         <button id="cerrar-modal-comision" class="btn-cerrar-modal">&times;</button>
      </div>

      <!-- BODY -->
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
                  @foreach($comisiones as $comision)
                  <div class="mc-table-row" data-id="{{ $comision->id }}">
                     <div>{{ $comision->clasificacion }}</div>
                     <div>{{ $comision->producto }}</div>
                     <div>${{ number_format($comision->precio, 2) }}</div>
                     <div>{{ $comision->porcentaje }}%</div>
                     <div>${{ number_format($comision->total, 2) }}</div>
                     <div>
                        <a href="#" title="Editar" class="btn-icon btn-edit" data-id="{{ $comision->id }}">
                           <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
                        </a>
                        <button class="btn btn-icon btn-eliminar" data-id="{{ $comision->id }}">
                           <img src="{{ asset('images/icons/delete.svg') }}" class="icon">
                        </button>
                     </div>
                  </div>
                  @endforeach
               </div><!-- fin mc-table-body -->
            </div><!-- fin mc-table-card -->
         </div><!-- fin mc-table-container -->

      </div><!-- fin modal-comision-body -->

      <!-- FOOTER - Botón Agregar -->
      <div class="mc-footer">
         <button id="mc-btn-agregar" class="mc-btn-agregar">+ Agregar</button>
      </div>

   </div><!-- fin modal-comision-content -->
</div><!-- fin modal-comision -->

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
                        <div>Producto</div>
                        <div>Alumno</div>
                        <div>Comisión</div>
                    </div>

                    <div class="mc-table-body" id="detalle-comision-body">
                        <!-- FILAS DINÁMICAS DESDE EL BACKEND -->
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
const LOGO_BASE64 = "data:image/png;base64,{{ $logoBase64 }}";
</script>

<script>
(function initComisiones() {
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

    document.querySelectorAll('.icon-calendar').forEach(icon => {
        icon.addEventListener('click', function () {
            this.nextElementSibling?.showPicker();
        });
    });

    // ===== MODAL % COMISIÓN — ABRIR/CERRAR =====
    const modalComision  = document.getElementById('modal-comision');
    const btnComision    = document.querySelector('.btn-comision');
    const cerrarComision = document.getElementById('cerrar-modal-comision');

    btnComision?.addEventListener('click', () => modalComision.classList.remove('d-none'));
    cerrarComision?.addEventListener('click', () => modalComision.classList.add('d-none'));
    modalComision?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('d-none');
    });

    // ===== AGREGAR O GUARDAR EDICIÓN (UN SOLO LISTENER) =====
    const btnAgregar = document.getElementById('mc-btn-agregar');

    btnAgregar?.addEventListener('click', async () => {
        const editId         = btnAgregar.dataset.editId;
        const clasificacion  = document.getElementById('mc-clasificacion').value;
        const productoSelect = document.getElementById('mc-producto');
        const producto       = productoSelect.options[productoSelect.selectedIndex]?.dataset.nombre || '';
        const precio         = document.getElementById('mc-precio').value;
        const porcentaje     = document.getElementById('mc-porcentaje').value;

        if (!clasificacion || !producto || !precio || !porcentaje) {
            alert('Por favor completa todos los campos.');
            return;
        }

        const url    = editId ? `/crm/comisiones/${editId}` : '/crm/comisiones';
        const method = editId ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ clasificacion, producto, precio, porcentaje })
        });

        if (!res.ok) { alert('Error al guardar.'); return; }

        const comision = await res.json();

        if (editId) {
            // Actualizar fila existente
            const fila = document.querySelector(`#mc-tabla-body .mc-table-row[data-id="${editId}"]`);
            const divs = fila.querySelectorAll(':scope > div');
            divs[0].textContent = comision.clasificacion;
            divs[1].textContent = comision.producto;
            divs[2].textContent = `$${parseFloat(comision.precio).toFixed(2)}`;
            divs[3].textContent = `${comision.porcentaje}%`;
            divs[4].textContent = `$${parseFloat(comision.total).toFixed(2)}`;

            delete btnAgregar.dataset.editId;
            btnAgregar.textContent = '+ Agregar';
        } else {
            // Agregar nueva fila
            document.getElementById('mc-tabla-body').insertAdjacentHTML('beforeend', `
                <div class="mc-table-row" data-id="${comision.id}">
                    <div>${comision.clasificacion}</div>
                    <div>${comision.producto}</div>
                    <div>$${parseFloat(comision.precio).toFixed(2)}</div>
                    <div>${comision.porcentaje}%</div>
                    <div>$${parseFloat(comision.total).toFixed(2)}</div>
                    <div>
                        <a href="#" class="btn-icon btn-edit" data-id="${comision.id}">
                            <img src="/images/icons/pen-to-square-solid-full.svg" alt="Editar">
                        </a>
                        <button class="btn btn-icon btn-eliminar" data-id="${comision.id}">
                            <img src="/images/icons/delete.svg" class="icon">
                        </button>
                    </div>
                </div>
            `);
        }

        // Limpiar formulario
        document.getElementById('mc-clasificacion').value = '';
        document.getElementById('mc-producto').value      = '';
        document.getElementById('mc-precio').value        = 0;
        document.getElementById('mc-porcentaje').value    = 0;
    });

    // ===== EDITAR (delegación en tabla) =====
    document.getElementById('mc-tabla-body')?.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-edit');
        if (!btnEdit) return;

        const fila = btnEdit.closest('.mc-table-row');
        const divs = fila.querySelectorAll(':scope > div');

        document.getElementById('mc-clasificacion').value = divs[0].textContent.trim();
        document.getElementById('mc-precio').value        = parseFloat(divs[2].textContent.replace(/[$,]/g, ''));
        document.getElementById('mc-porcentaje').value    = parseFloat(divs[3].textContent);

        // Seleccionar el producto correcto
        const nombreProducto = divs[1].textContent.trim();
        Array.from(document.getElementById('mc-producto').options).forEach(opt => {
            if (opt.dataset.nombre === nombreProducto) {
                document.getElementById('mc-producto').value = opt.value;
            }
        });

        btnAgregar.dataset.editId = fila.dataset.id;
        btnAgregar.textContent    = '💾 Guardar cambios';
    });

    // ===== ELIMINAR (delegación en tabla) =====
    document.getElementById('mc-tabla-body')?.addEventListener('click', async function (e) {
        const btnEliminar = e.target.closest('.btn-eliminar');
        if (!btnEliminar) return;

        const fila = btnEliminar.closest('.mc-table-row');
        const id   = fila.dataset.id;

        if (!confirm('¿Eliminar esta comisión?')) return;

        const res = await fetch(`/crm/comisiones/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        if (res.ok) fila.remove();
        else alert('Error al eliminar.');
    });

    // ===== MODAL DETALLE (OJO) =====
    const modalDetalle  = document.getElementById('modal-detalle-comision');
    const cerrarDetalle = document.getElementById('cerrar-modal-detalle');
    const tablaDetalle  = document.getElementById('detalle-comision-body');
    const totalDetalle  = document.getElementById('detalle-total');

    document.querySelectorAll('.btn-ver-ctp').forEach(btn => {
        btn.addEventListener('click', async function () {
            const ctpId = this.getAttribute('data-id');

            tablaDetalle.innerHTML = '<div class="mc-celda">Cargando...</div>';
            modalDetalle.classList.remove('d-none');

            const res = await fetch(`/crm/comisiones/${ctpId}/detalle`, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });

            const json = await res.json();

            tablaDetalle.innerHTML = '';

            if (json.data.length === 0) {
                tablaDetalle.innerHTML = '<div class="mc-celda">Sin conversiones registradas.</div>';
                totalDetalle.textContent = '$0.00';
                return;
            }

            json.data.forEach(item => {
                tablaDetalle.insertAdjacentHTML('beforeend', `
                    <div class="mc-table-row">
                        <div class="mc-celda">${item.clasificacion}</div>
                        <div class="mc-celda">${item.producto}</div>
                        <div class="mc-celda">${item.alumno}</div>
                        <div class="mc-celda">$${parseFloat(item.comision).toLocaleString('es-MX')}</div>
                    </div>
                `);
            });

            totalDetalle.textContent = `$${parseFloat(json.total).toLocaleString('es-MX')}`;
        });
    });

    cerrarDetalle?.addEventListener('click', () => modalDetalle.classList.add('d-none'));
    modalDetalle?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('d-none');
    });

    // ===== PDF =====
    document.querySelectorAll('.btn-descargar-pdf-comision').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!window.jspdf) { alert('Cargando librería...'); return; }

            const fila      = this.closest('.table-row');
            const nombreCTP = fila.querySelector('.col-ctp').innerText;
            const totalCTP  = fila.querySelector('.col-total').innerText; // 👈 tomar el total de la tabla
            const filas     = document.querySelectorAll('#detalle-comision-body .mc-table-row');

            if (filas.length === 0) {
                alert('Primero abre el detalle (👁️) para generar el PDF');
                return;
            }

            const { jsPDF }  = window.jspdf;
            const doc        = new jsPDF();
            const azulOscuro = [13, 27, 42];
            const azulMedio  = [31, 58, 99];
            const grisF      = [245, 247, 250];
            const W = 210, M = 14;

            // HEADER
            doc.setFillColor(...azulOscuro);
            doc.rect(0, 0, W, 30, 'F');
            doc.addImage(LOGO_BASE64, 'PNG', 5, 3, 20, 20);
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(16);
            doc.text('DETALLE DE COMISIONES', W / 2, 15, { align: 'center' });
            doc.setFontSize(10);
            doc.text(nombreCTP, W / 2, 23, { align: 'center' });

            let y = 40;

            // TÍTULO
            doc.setFillColor(...azulOscuro);
            doc.roundedRect(M, y - 5, W - M * 2, 10, 2, 2, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(9);
            doc.text('MONTO DE CONVERSIÓN', W / 2, y + 2, { align: 'center' });
            y += 10;

            // HEADER TABLA — ahora 4 columnas
            doc.setFillColor(...azulMedio);
            doc.rect(M, y, W - M * 2, 8, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(8);

            const col1 = M + 3;
            const col2 = 55;
            const col3 = 130;
            const col4 = 170;

            doc.text('Clasificación', col1, y + 5);
            doc.text('Producto',      col2, y + 5);
            doc.text('Alumno',        col3, y + 5);
            doc.text('Comisión',      col4, y + 5);
            y += 8;

            let total = 0;

            filas.forEach((f, i) => {
                const celdas = f.querySelectorAll('.mc-celda');

                const clasificacion = celdas[0]?.innerText ?? '';
                const producto      = celdas[1]?.innerText ?? '';
                const alumno        = celdas[2]?.innerText ?? '';
                const comision      = celdas[3]?.innerText ?? '$0';

                // Sumar correctamente
                total += parseFloat(comision.replace(/[$,]/g, '')) || 0;

                if (i % 2 === 0) {
                    doc.setFillColor(...grisF);
                    doc.rect(M, y, W - M * 2, 8, 'F');
                }

                doc.setTextColor(30, 30, 30);
                doc.text(clasificacion, col1, y + 5);
                doc.text(producto,      col2, y + 5);
                doc.text(alumno,        col3, y + 5);
                doc.text(comision,      col4, y + 5);
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
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(8);
            doc.text(`Generado el ${new Date().toLocaleDateString('es-MX')}`, W / 2, 290, { align: 'center' });

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
        const hoja  = XLSX.utils.aoa_to_sheet(datos);
        hoja['!cols'] = [{ wch: 30 }, { wch: 25 }, { wch: 22 }];
        const libro = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(libro, hoja, "Comisiones");
        XLSX.writeFile(libro, `comisiones_${new Date().toISOString().slice(0, 10)}.xlsx`);
    });

})();
</script>
@endsection