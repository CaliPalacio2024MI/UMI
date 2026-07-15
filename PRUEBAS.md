# Guía de pruebas — Unidades de Negocio

## ✅ Qué se realizó (cambios en el código)

Se modificaron **2 archivos**:

### 1. `resources/views/layouts/Ajustes/forms/_institutions.blade.php`
Formulario de "Agregar / Editar Unidad de Negocio". Se reescribió la lógica (antes confusa con checkboxes):
- Se **quitó** el campo genérico de "nombre de unidad" y los checkboxes de tipo.
- Se **agregó** un selector **"Universidad o Propiedad"** como primer campo.
- **Universidad** → muestra un **campo de texto abierto** para el nombre.
- **Propiedad** → muestra un **desplegable** que se llena desde la **API externa** (`/external-data?endpoint=/api/external/propiedades`).
- El selector sincroniza automáticamente las banderas `is_universidad` / `is_administrativo`.
- En modo **editar**, preselecciona el tipo correcto y precarga el nombre.

### 2. `resources/views/layouts/Ajustes/index.blade.php`
Tabla de Ajustes:
- Se **eliminó el ícono de "ver"** (y su lógica muerta `.btn-view` en el JS). En Acciones solo quedan **Editar** y **Eliminar**.
- (Requerimiento previo) Se quitó la columna **ID** de las secciones instituciones, departamentos y puestos.

> No se modificó ningún controlador ni la base de datos. El backend sigue leyendo `name`, `is_universidad` e `is_administrativo` igual que antes.

---

## 🧪 Cómo probarlo en la página

### 1. Levantar el sistema
```
php artisan serve
```
(si usas Vite en desarrollo, en otra terminal: `npm run dev`)
Abre: http://localhost:8000

### 2. Iniciar sesión
- **Usuario:** `XAXX010101000`
- **Contraseña:** `140735abc`

(Al entrar con RFC, el sistema te pone en contexto de propiedad, donde aparece "Unidades de Negocio".)

### 3. Ir a Unidades de Negocio
- Menú lateral: **Ajustes → Unidades de Negocio** (o `/ajustes/institutions`).

### 4. Verificar la tabla
- Encabezados: **sin** columna "ID".
- Columna **Acciones**: solo **Editar** y **Eliminar** (sin ícono de "ver").

### 5. Probar "Agregar Unidad de Negocio"
Clic en **+ Agregar Unidad de Negocio**. En el modal:
- Primero aparece **"Universidad o Propiedad"**.
- **Universidad** → aparece campo de texto → escribe el nombre → **Guardar**.
- **Propiedad** → aparece desplegable con datos de la API → selecciona → **Guardar**.

### 6. Probar "Editar"
- Clic en **Editar** de una fila → el modal debe preseleccionar el tipo y precargar el nombre.

---

## ⚠️ Nota sobre la API (desplegable de Propiedad)
El desplegable de **Propiedad** solo mostrará datos si están cargadas las credenciales de la API externa en `.env`:
```
EXTERNAL_API_ACCESS_KEY=...
EXTERNAL_API_SECRET_KEY=...
EXTERNAL_API_BASE_URL=...
```
Después de ponerlas, corre: `php artisan config:clear`

Si están vacías, el desplegable sale vacío (la API responde error 500). **Esto es configuración del entorno, no del formulario.**

---

## 🔎 Ver errores (opcional)
Presiona **F12** en el navegador → pestañas **Console** y **Network** para ver si alguna llamada falla.
