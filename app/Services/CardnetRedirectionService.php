<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CardnetRedirectionService
{
    protected $merchantId;
    protected $terminalId;
    protected $currency;
    protected $sessionUrl;
    protected $authorizeUrl;
    protected $environment;

    public function __construct()
    {
        $this->merchantId = config('services.cardnet.merchant_id');
        $this->terminalId = config('services.cardnet.terminal_id');
        $this->currency = config('services.cardnet.currency', '214'); // 214 = DOP
        
        $this->environment = config('services.cardnet.environment', 'sandbox');
        
        // URLs basadas en la documentación "Integración con Pantalla (POST)"
        if ($this->environment === 'production') {
            $this->sessionUrl = 'https://ecommerce.cardnet.com.do/sessions';
            $this->authorizeUrl = 'https://ecommerce.cardnet.com.do/authorize';
        } else {
            // URL de desarrollo
            $this->sessionUrl = 'https://labservicios.cardnet.com.do/sessions';
            $this->authorizeUrl = 'https://labservicios.cardnet.com.do/authorize';
        }
    }

    /**
     * Prepara los datos para el formulario POST de redirección.
     * Primero crea una sesión vía API y luego retorna los datos para el form.
     */
    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        // 1. Formatear monto: Cardnet espera centavos sin puntos (ej: 100.00 -> 10000)
        // PERO la documentación de "Integración con Pantalla (POST)" dice "Longitud: 12"
        // En los ejemplos de la doc se ve: "Amount": "88100" (sin ceros a la izquierda excesivos en el JSON)
        // pero en el form dice value="000000011799".
        // Para la sesión API REST, usaremos el formato entero simple (centavos).
        $amountInCents = intval(round($amount * 100));
        $formattedAmount = (string)$amountInCents; 

        // 2. Crear Sesión en Cardnet
        $sessionData = [
            'TransactionType' => '0200',
            'CurrencyCode' => $this->currency,
            'AcquiringInstitutionCode' => '349',
            'MerchantType' => '5311', // O el que corresponda a tu comercio (ej: 5440)
            'MerchantNumber' => $this->merchantId,
            'MerchantTerminal' => $this->terminalId,
            'ReturnUrl' => route('cardnet.response'),
            'CancelUrl' => route('cardnet.cancel'),
            'PageLanguaje' => 'ESP',
            'OrdenId' => (string)$orderId,
            'TransactionId' => (string)time(), // Debe ser único
            'Tax' => '000000000000',
            'MerchantName' => 'CENTU GESTION ACADEMICA DO', // Ajustar según doc (max 40 chars)
            // 'AVS' => 'Direccion comercio', // Opcional pero recomendado
            'Amount' => $formattedAmount
        ];

        Log::info('Cardnet Session Request:', ['url' => $this->sessionUrl, 'data' => $sessionData]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                // Algunos ambientes requieren headers extra, verificar si proxy blockea
            ])->post($this->sessionUrl, $sessionData);

            $responseData = $response->json();
            Log::info('Cardnet Session Response:', ['status' => $response->status(), 'body' => $responseData]);

            if ($response->successful() && isset($responseData['SESSION'])) {
                // Éxito: Retornar datos para el formulario de autorización
                return [
                    'url' => $this->authorizeUrl,
                    'fields' => [
                        'SESSION' => $responseData['SESSION']
                    ]
                ];
            } else {
                Log::error('Cardnet Session Failed', ['body' => $response->body()]);
                // Fallback o error - lanzar excepción para que el componente lo maneje
                throw new \Exception('No se pudo iniciar la sesión de pago con Cardnet.');
            }

        } catch (\Exception $e) {
            Log::error('Cardnet Connection Error: ' . $e->getMessage());
            // Retornar null o lanzar excepción
            throw $e;
        }
    }
}