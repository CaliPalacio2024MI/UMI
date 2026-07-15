<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\Request;

class ExternalDataController extends Controller
{
    /**
     * Consume la API externa y devuelve los datos como JSON.
     */
    public function index(Request $request)
    {
        // --- MODO DEMO (temporal) ---
        // Si EXTERNAL_API_DEMO=true, devolvemos datos de ejemplo para poder
        // probar los formularios sin las credenciales reales de la API.
        if (config('services.external_api.demo')) {
            $endpoint = $request->query('endpoint', '/api/external/propiedades');
            return response()->json($this->demoData($endpoint));
        }

        $accessKey = config('services.external_api.access_key');
        $secretKey = config('services.external_api.secret_key');
        $baseUrl = config('services.external_api.base_url');

        if (empty($accessKey) || empty($secretKey) || empty($baseUrl)) {
            $missing = [];
            if (empty($accessKey)) $missing[] = 'access_key';
            if (empty($secretKey)) $missing[] = 'secret_key';
            if (empty($baseUrl)) $missing[] = 'base_url';

            return response()->json([
                'error' => 'Configuracion incompleta: verifica services.external_api.* en config/services.php y .env.',
                'missing' => $missing,
            ], 500);
        }

        // Permite sobreescribir el endpoint desde querystring si lo necesitas:
        // /external-data?endpoint=/api/external/propiedades
        $endpoint = $request->query('endpoint', '/api/external/propiedades');

        $service = new ExternalApiService($accessKey, $secretKey, 10); // expiracion en minutos

        try {
            $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
            $data = $service->execute($url, 'GET');

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Datos de ejemplo para el MODO DEMO (temporal).
     * Simula las respuestas de la API real según el endpoint solicitado.
     * Eliminar este método (y la rama demo en index) cuando la API real esté configurada.
     */
    private function demoData(string $endpoint): array
    {
        $propiedades = [
            ['id' => 1, 'nombre' => 'Palacio Mundo Imperial'],
            ['id' => 2, 'nombre' => 'Princess Mundo Imperial'],
            ['id' => 3, 'nombre' => 'Pierre Mundo Imperial'],
        ];

        $departamentosPorPropiedad = [
            1 => ['Recursos Humanos', 'Alimentos y Bebidas', 'Ama de Llaves', 'Recepción', 'Mantenimiento'],
            2 => ['Recursos Humanos', 'Ventas', 'Reservaciones', 'Spa', 'Seguridad'],
            3 => ['Recursos Humanos', 'Cocina', 'Banquetes', 'Concierge', 'Contabilidad'],
        ];

        // /api/external/propiedades/{id}/anfitriones (o /usuarios /empleados)
        // Lista de personas de la propiedad (para elegir el nombre del usuario).
        if (preg_match('#/(anfitriones|usuarios|empleados|trabajadores)#', $endpoint)) {
            return ['anfitriones' => [
                ['id' => 1, 'RFC' => 'GOMA850101AB1', 'Nombre' => 'Ana',   'apellido_paterno' => 'Gómez',     'apellido_materno' => 'Martínez', 'departamento_nombre' => 'Recursos Humanos',     'puesto_nombre' => 'Gerente'],
                ['id' => 2, 'RFC' => 'LOPL900202CD2', 'Nombre' => 'Luis',  'apellido_paterno' => 'López',     'apellido_materno' => 'Pérez',    'departamento_nombre' => 'Alimentos y Bebidas', 'puesto_nombre' => 'Supervisor'],
                ['id' => 3, 'RFC' => 'HERM880303EF3', 'Nombre' => 'María',  'apellido_paterno' => 'Hernández', 'apellido_materno' => 'Ruiz',     'departamento_nombre' => 'Recepción',           'puesto_nombre' => 'Coordinador'],
                ['id' => 4, 'RFC' => 'RARJ920404GH4', 'Nombre' => 'Jorge',  'apellido_paterno' => 'Ramírez',   'apellido_materno' => 'Cruz',     'departamento_nombre' => 'Mantenimiento',       'puesto_nombre' => 'Auxiliar'],
            ]];
        }

        // /api/external/propiedades/{id}/departamentos/{deptId}/posiciones
        // (se revisa ANTES que departamentos porque la URL también contiene "departamentos")
        if (preg_match('#/departamentos/[^/]+/posiciones#', $endpoint)) {
            $puestos = ['Gerente', 'Subgerente', 'Supervisor', 'Coordinador', 'Jefe de Área', 'Auxiliar', 'Analista'];
            $posiciones = array_map(
                fn ($nombre, $i) => ['id' => $i + 1, 'posicion_nombre' => $nombre],
                $puestos,
                array_keys($puestos)
            );
            return ['posiciones' => $posiciones];
        }

        // /api/external/propiedades/{id}/departamentos
        if (preg_match('#/propiedades/(\d+)/departamentos#', $endpoint, $m)) {
            $propId = (int) $m[1];
            $lista = $departamentosPorPropiedad[$propId] ?? [];
            $departamentos = array_map(
                fn ($nombre, $i) => ['id' => $i + 1, 'departamento_nombre' => $nombre],
                $lista,
                array_keys($lista)
            );
            return ['departamentos' => $departamentos];
        }

        // /api/external/propiedades
        return ['propiedades' => $propiedades];
    }
}

