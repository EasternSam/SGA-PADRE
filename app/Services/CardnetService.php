<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CardnetService
{
    protected $environment;
    protected $privateKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->environment = config('services.cardnet.environment', 'sandbox');
        $this->privateKey = config('services.cardnet.private_key');

        // URLs basadas en la lógica de SGA Wordpress
        if ($this->environment === 'production') {
            $this->apiUrl = 'https://servicios.cardnet.com.do/servicios/tokens/v1/api/Purchase';
        } else {
            $this->apiUrl = 'https://lab.cardnet.com.do/servicios/tokens/v1/api/Purchase';
        }
    }

    /**
     * Realiza un cargo a una tarjeta usando el Token generado por el frontend.
     * Replica la lógica de _process_cardnet_purchase de Wordpress.
     *
     * @param string $token El token devuelto por el JS (TokenId)
     * @param float $amount El monto a cobrar (en decimales, ej: 100.00)
     * @param string $orderNumber Número de orden único
     * @return array Respuesta estandarizada
     */
    public function purchase($token, $amount, $orderNumber)
    {
        // Cardnet espera el monto en CENTAVOS (Integer)
        // Ej: 100.00 DOP -> 10000
        $amountInCents = intval(floatval($amount) * 100);

        $payload = [
            'TrxToken' => $token,
            'Order'    => (string)$orderNumber,
            'Amount'   => $amountInCents,
            'Currency' => 'DOP',
            'Capture'  => true,
            'DataDo'   => [
                'Invoice' => (string)$orderNumber,
            ]
        ];

        Log::info("Cardnet Request: Orden #{$orderNumber}", ['payload' => $payload, 'url' => $this->apiUrl]);

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                // Autenticación Basic con la Private Key como usuario (el password se deja vacío con :)
                'Authorization' => 'Basic ' . base64_encode($this->privateKey . ':')
            ])
            ->timeout(45)
            ->post($this->apiUrl, $payload);

            $body = $response->json();
            
            Log::info("Cardnet Response: Orden #{$orderNumber}", ['status' => $response->status(), 'body' => $body]);

            // Verificar éxito según estructura de Cardnet
            // En WP: $response_body['Transaction']['TransactionStatusId'] === 1
            $isSuccessful = isset($body['Transaction']['TransactionStatusId']) && $body['Transaction']['TransactionStatusId'] === 1;

            if ($isSuccessful) {
                return [
                    'success'            => true,
                    'authorization_code' => $body['Transaction']['ApprovalCode'] ?? 'N/A',
                    'response_code'      => '00',
                    'message'            => $body['Transaction']['Steps'][0]['ResponseMessage'] ?? 'Aprobada',
                    'transaction_id'     => $body['Transaction']['TransactionId'] ?? null, // ID interno de Cardnet
                ];
            } else {
                return [
                    'success'       => false,
                    'response_code' => $body['Transaction']['Steps'][0]['ResponseCode'] ?? '99',
                    'message'       => $body['Transaction']['Steps'][0]['ResponseMessage'] ?? 'Rechazada por el banco',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Cardnet Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de comunicación con la pasarela: ' . $e->getMessage()
            ];
        }
    }
}