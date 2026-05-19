<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\Request;
use App\Models\Users\Department;
use App\Models\Users\Institution;
use App\Models\Users\Workstation;

class ExternalDataController extends Controller
{

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

            $nombre =
                $propiedad['nombre']
                ?? $propiedad['name']
                ?? null;

            $propertyId =
                $propiedad['id_propiedad']
                ?? $propiedad['id']
                ?? null;

            if (!$nombre) {
                continue;
            }

            Institution::updateOrCreate(

                [
                    'name' => $nombre
                ],

                [
                    'name' => $nombre,
                    'external_property_id' => $propertyId,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Propiedades sincronizadas'
        ]);
    }


    public function propiedades(Request $request)
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


        $institutionId =
            $request->query('institution_id');

        if ($institutionId) {

            $institution = Institution::find(
                $institutionId
            );

            if ($institution) {

                $externalPropertyId =
                    $institution->external_property_id;

                $propiedades = collect($propiedades)
                    ->filter(function ($propiedad) use ($externalPropertyId) {

                        return
                            (
                                $propiedad['id_propiedad']
                                ?? $propiedad['id']
                                ?? null
                            ) == $externalPropertyId;
                    })
                    ->values();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $propiedades
        ]);
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

            $institution = Institution::where(
                'external_property_id',
                $propertyId
            )->first();

            if (!$institution) {

                return response()->json([
                    'error' => 'Institución no encontrada'
                ], 404);
            }

            foreach ($departamentos as $departamento) {

                $nombre =
                    $departamento['nombre']
                    ?? $departamento['name']
                    ?? null;

                if (!$nombre) {
                    continue;
                }

                Department::updateOrCreate(

                    [
                        'name' => $nombre,
                        'institution_id' => $institution->id,
                    ],

                    [
                        'name' => $nombre,
                        'institution_id' => $institution->id,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Departamentos sincronizados'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
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

        try {

            $response = $service->execute($url, 'GET');

            $positions = $response['data'] ?? [];

            foreach ($positions as $position) {

                $nombre =
                    $position['nombre']
                    ?? $position['name']
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

            return response()->json([
                'success' => true,
                'message' => 'Posiciones sincronizadas'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function index(Request $request)
{

    $accessKey = config('services.external_api.access_key');

    $secretKey = config('services.external_api.secret_key');

    $baseUrl = config('services.external_api.base_url');

    if (
        empty($accessKey) ||
        empty($secretKey) ||
        empty($baseUrl)
    ) {

        return response()->json([
            'error' => 'Configuración incompleta'
        ], 500);

    }

    $endpoint = $request->query(
        'endpoint',
        '/api/external/propiedades'
    );

    $allowedPrefixes = [

        '/api/external/propiedades',

    ];

    $endpointIsAllowed = collect($allowedPrefixes)
        ->contains(function ($prefix) use ($endpoint) {

            return str_starts_with(
                $endpoint,
                $prefix
            );

        });

    if (! $endpointIsAllowed) {

        return response()->json([
            'error' => 'Endpoint no permitido'
        ], 403);

    }

    $service = new ExternalApiService(
        $accessKey,
        $secretKey,
        10
    );

    try {

        $url = rtrim($baseUrl, '/')
            . '/'
            . ltrim($endpoint, '/');

        $response = $service->execute(
            $url,
            'GET'
        );

        return response()->json($response);

    } catch (\Exception $e) {

        return response()->json([

            'error' => $e->getMessage(),

        ], 500);

    }
}
}
