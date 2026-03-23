<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExternalApiService
{
    private $accessKey;
    private $secretKey;
    private $expirationMinutes;

    public function __construct($accessKey, $secretKey, $expirationMinutes = 60)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->expirationMinutes = $expirationMinutes;
    }

    /**
     * Genera un token JWT firmado.
     * 
     * @return string
     */
    public function generateToken(): string
    {
        // 1. Encabezado
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        // 2. Carga útil (Payload)
        $now = time();
        $payload = json_encode([
            'iss' => $this->accessKey,
            'iat' => $now,
            'exp' => $now + ($this->expirationMinutes * 60)
        ]);

        // 3. Helper para codificar en Base64Url
        $base64Url = function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };

        $base64UrlHeader = $base64Url($header);
        $base64UrlPayload = $base64Url($payload);

        // 4. Firmar
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignature = $base64Url($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Realiza una petición a la API externa usando un token firmado.
     * 
     * @param string $url La URL completa de la API
     * @param string $method GET, POST, etc.
     * @param array $data Parámetros del cuerpo
     * @return array
     * @throws \Exception
     */
    public function execute(string $url, string $method = 'GET', array $data = [])
    {
        $token = $this->generateToken();

        $response = Http::withToken($token)->$method($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Error al consultar la API ({$url}): " . $response->status() . " - " . $response->body());
    }
}
