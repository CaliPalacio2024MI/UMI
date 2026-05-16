<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\Request;
use App\Models\Users\Department;
use App\Models\Users\Institution;
use App\Models\Users\Workstation;

class ExternalDataController extends Controller
{
    /**
     * Consume la API externa y muestra los datos en una vista Blade.
     */
    public function syncProperties()
{
    $service = new ExternalApiService(
        config('services.external_api.access_key'),
        config('services.external_api.secret_key'),
        10
    );

    $url = config('services.external_api.base_url')
        . '/api/external/propiedades';

    $response = $service->execute($url, 'GET');

    $propiedades = $response['data'] ?? [];

    foreach ($propiedades as $propiedad) {

        $nombre = $propiedad['name']
            ?? $propiedad['nombre']
            ?? null;

        if (!$nombre) {
            continue;
        }

        Institution::updateOrCreate(
            [
                'name' => $nombre
            ],
            [
                'name' => $nombre
            ]
        );
    }

    return 'Propiedades sincronizadas';
}
public function propiedades()
{
    $service = new ExternalApiService(
        config('services.external_api.access_key'),
        config('services.external_api.secret_key'),
        10
    );

    $url = config('services.external_api.base_url')
        . '/api/external/propiedades';

    $response = $service->execute($url, 'GET');

    $propiedades = $response['data'] ?? [];

    return response()->json($propiedades);
}
public function syncDepartments($propertyId)
{
    $service = new ExternalApiService(
        config('services.external_api.access_key'),
        config('services.external_api.secret_key'),
        10
    );

    $url = config('services.external_api.base_url')
        . "/api/external/propiedades/$propertyId/departamentos";

    try {

        $response = $service->execute($url, 'GET');

        $departamentos = $response['data'] ?? [];

        foreach ($departamentos as $departamento) {

            $nombre = $departamento['name']
                ?? $departamento['nombre']
                ?? null;

            if (!$nombre) {
                continue;
            }

            Department::updateOrCreate(
                [
                    'name' => $nombre,
                    'institution_id' => session('active_institution_id'),
                ],
                [
                    'name' => $nombre,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Departamentos sincronizados',
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function syncPositions($propertyId, $departmentId)
{
    $service = new ExternalApiService(
        config('services.external_api.access_key'),
        config('services.external_api.secret_key'),
        10
    );

    $url = config('services.external_api.base_url')
        . "/api/external/propiedades/$propertyId/departamentos/$departmentId/posiciones";

    $response = $service->execute($url, 'GET');

    $positions = $response['data'] ?? [];

    foreach ($positions as $position) {

        $nombre = $position['name']
            ?? $position['nombre']
            ?? null;

        if (!$nombre) {
            continue;
        }

        Workstation::updateOrCreate(
            [
                'name' => $nombre
            ],
            [
                'name' => $nombre
            ]
        );
    }

    return 'Posiciones sincronizadas';
}
public function index(Request $request)
{
    $accessKey = config('services.external_api.access_key');
    $secretKey = config('services.external_api.secret_key');
    $baseUrl = config('services.external_api.base_url');

    if (empty($accessKey) || empty($secretKey) || empty($baseUrl)) {

        return response()->json([
            'error' => 'Configuracion incompleta'
        ], 500);
    }

    $endpoint = $request->query(
        'endpoint',
        '/api/external/propiedades'
    );

    $service = new ExternalApiService(
        $accessKey,
        $secretKey,
        10
    );

    try {

        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $response = $service->execute($url, 'GET');

        return response()->json($response);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}
}

