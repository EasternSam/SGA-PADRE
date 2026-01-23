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

        // Definir URL base según entorno
        if ($this->environment === 'production') {
            $this->apiUrl = 'https://servicios.cardnet.com.do/servicios/tokens/v1/api/Purchase';
        } else {
            $this->apiUrl = 'https://labservicios.cardnet.com.do/servicios/tokens/v1/api/Purchase';
        }
    }

    /**
     * Realiza el cobro usando el token generado en el frontend.
     */
    public function purchase($token, $amount, $orderNumber)
    {
        // Validación básica de configuración
        if (empty($this->privateKey)) {
            Log::error('Cardnet: Falta la llave privada en la configuración.');
            return [
                'success' => false,
                'message' => 'Error de configuración del sistema de pagos (Private Key).'
            ];
        }

        // Cardnet espera el monto en CENTAVOS (Integer)
        // Usamos round para evitar errores de punto flotante antes de intval
        $amountInCents = intval(round(floatval($amount) * 100));

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

        Log::info("Cardnet Request [{$orderNumber}]", ['url' => $this->apiUrl, 'amount' => $amountInCents]);

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->privateKey . ':')
            ])
            ->timeout(45)
            ->post($this->apiUrl, $payload);

            $body = $response->json();
            
            // Verificar éxito (TransactionStatusId = 1 es aprobado)
            $isSuccessful = isset($body['Transaction']['TransactionStatusId']) && $body['Transaction']['TransactionStatusId'] === 1;

            if ($isSuccessful) {
                return [
                    'success'            => true,
                    'authorization_code' => $body['Transaction']['ApprovalCode'] ?? 'N/A',
                    'response_code'      => '00',
                    'message'            => $body['Transaction']['Steps'][0]['ResponseMessage'] ?? 'Aprobada',
                    'transaction_id'     => $body['Transaction']['TransactionId'] ?? null,
                ];
            } else {
                $msg = $body['Transaction']['Steps'][0]['ResponseMessage'] ?? 'Rechazada por el banco';
                Log::warning("Cardnet Rechazo [{$orderNumber}]: {$msg}");
                return [
                    'success'       => false,
                    'response_code' => $body['Transaction']['Steps'][0]['ResponseCode'] ?? '99',
                    'message'       => $msg,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Cardnet Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de comunicación con la pasarela.'
            ];
        }
    }
}