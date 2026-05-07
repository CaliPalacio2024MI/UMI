import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({

            input: [
                // CSS Base
                'resources/css/app.css',
                'resources/css/base.css',
                'resources/css/login.css',
                'resources/css/sidebar.css',
                'resources/css/billing.css',
                'resources/css/components.css',
                'resources/css/layout.css',
                'resources/css/responsive.css',
                'resources/css/variables.css',
                // CSS Ajustes
                'resources/css/Ajustes/modal.css',
                'resources/css/Ajustes/table.css',
                // CSS CRM
                'resources/css/CRM/leads.css',
                'resources/css/CRM/prospectos.css',
                'resources/css/CRM/estadisticas.css',
                'resources/css/CRM/comisiones.css',
                'resources/css/CRM/public.css',
                // CSS Control Admin
                'resources/css/Control Admin/aulas.css',
                'resources/css/Control Admin/base.css',
                'resources/css/Control Admin/content.css',
                'resources/css/Control Admin/users.css',
                'resources/css/Control Admin/horario-edit.css',
                // CSS ControlEsc
                'resources/css/ControlEsc/base.css',
                'resources/css/ControlEsc/boletas.css',
                // CSS Cursos
                'resources/css/Cursos/certificados.css',
                'resources/css/Cursos/courseShow.css',
                'resources/css/Cursos/courses.css',
                'resources/css/Cursos/createCourses.css',
                'resources/css/Cursos/editCourses.css',
                'resources/css/Cursos/topic.css',
                // CSS Mi_Informacion
                'resources/css/Mi_Informacion/clases.css',
                'resources/css/Mi_Informacion/historial_academico.css',
                'resources/css/Mi_Informacion/horario.css',
                'resources/css/Mi_Informacion/perfil.css',
                // JavaScript
                'resources/js/app.js',
                'resources/js/facturacion.js',
                'resources/js/horario-edit.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
