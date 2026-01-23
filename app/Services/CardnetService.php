<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CardnetService
{
    protected $baseUrl;
    protected $privateKey;

    public function __construct()
    {
        $this->baseUrl = config('services.cardnet.base_uri');
        $this->privateKey = config('services.cardnet.private_key');
    }

    /**
     * Realiza un cargo a una tarjeta tokenizada.
     * * @param string $token El token devuelto por el Lightbox (PaymentProfileID)
     * @param float $amount El monto a cobrar
     * @param string $invoiceId ID de referencia de la factura/pago interno
     * @return array Resupuesta de la API
     */
    public function purchase($token, $amount, $invoiceId)
    {
        $url = "{$this->baseUrl}/api/Purchase";
        
        // El monto debe enviarse sin separadores de miles y con 2 decimales si aplica, 
        // pero la documentación de tokens suele pedirlo limpio. 
        // En algunos endpoints de Cardnet es string, en otros number. Probaremos standard.
        
        $payload = [
            "PaymentProfileID" => $token,
            "Amount" => $amount,
            "DataDo" => [
                "Invoice" => (string)$invoiceId,
                // "Tax" => "0" // Opcional
            ],
            "Description" => "Pago Matricula/Curso #{$invoiceId}"
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                // Autenticación Basic con la Private Key como usuario (sin password o password vacio)
                'Authorization' => 'Basic ' . base64_encode($this->privateKey . ':') 
            ])->post($url, $payload);

            Log::info('Cardnet Purchase Response', ['status' => $response->status(), 'body' => $response->json()]);

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Cardnet Purchase Error: ' . $e->getMessage());
            return ['response-code' => 'ERROR', 'response-message' => $e->getMessage()];
        }
    }
}