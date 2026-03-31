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
}

