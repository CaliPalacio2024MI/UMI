import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({

            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/facturacion.js',
                'resources/css/CRM/leads.css',
                'resources/css/CRM/prospectos.css',
                'resources/css/CRM/estadisticas.css',
                'resources/css/CRM/comisiones.css',
                'resources/css/Control Admin/horario-edit.css',
                'resources/js/horario-edit.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
