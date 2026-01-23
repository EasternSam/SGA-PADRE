<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CardnetRedirectionService
{
    protected $merchantId;
    protected $terminalId;
    protected $currency;
    protected $url;
    protected $environment;

    public function __construct()
    {
        // 1. Cargar credenciales (con fallbacks vacíos para evitar errores)
        $this->merchantId = config('services.cardnet.merchant_id', env('CARDNET_MERCHANT_ID', ''));
        $this->terminalId = config('services.cardnet.terminal_id', env('CARDNET_TERMINAL_ID', ''));
        $this->currency = config('services.cardnet.currency', '214'); // 214 = DOP
        $this->environment = config('services.cardnet.environment', 'sandbox');
        
        // 2. FORZAR URLs CORRECTAS (Hardcoded para evitar problemas de caché/config)
        // Según documentación "Integración con Pantalla (POST)":
        // QA/Sandbox: https://labservicios.cardnet.com.do/authorize
        // Prod: https://ecommerce.cardnet.com.do/authorize
        
        if ($this->environment === 'production') {
            $this->url = 'https://ecommerce.cardnet.com.do/authorize';
        } else {
            $this->url = 'https://labservicios.cardnet.com.do/authorize';
        }
    }

    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        // 3. CORRECCIÓN MONTO: 12 dígitos, últimos 2 son decimales, relleno con ceros a la izquierda.
        // Ejemplo: 500.00 -> 50000 -> "000000050000"
        $amountClean = number_format($amount, 2, '', ''); 
        $formattedAmount = str_pad($amountClean, 12, '0', STR_PAD_LEFT);

        $transactionId = time();

        // URLs de retorno asegurando HTTPS
        $returnUrl = route('cardnet.response');
        $cancelUrl = route('cardnet.cancel');

        // Forzar HTTPS en URLs de retorno si estamos en un entorno seguro (evita errores de sesión)
        if (app()->environment('production') || str_contains(config('app.url'), 'https')) {
            $returnUrl = str_replace('http://', 'https://', $returnUrl);
            $cancelUrl = str_replace('http://', 'https://', $cancelUrl);
        }

        $data = [
            'TransactionType' => '0200', 
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349',
            'MerchantType'    => '5311',
            'MerchantNumber'  => $this->merchantId,
            'TerminalId'      => $this->terminalId,
            'ReturnUrl'       => $returnUrl, 
            'CancelUrl'       => $cancelUrl,
            'PageLanguage'    => 'ES',
            'OrdenId'         => (string)$orderId,
            'TransactionId'   => (string)$transactionId,
            'Amount'          => $formattedAmount,
            'Tax'             => '000000000000',
            'Tip'             => '000000000000',
            'IpAddress'       => $ipAddress,
            // Campos adicionales recomendados para evitar rechazos en sandbox
            'MerchantName'    => 'CENTU GESTION ACADEMICA DO',
        ];

        Log::info("Cardnet Form Data Generado", [
            'url_destino' => $this->url,
            'orden' => $orderId,
            'monto_formateado' => $formattedAmount,
            'return_url' => $returnUrl
        ]);

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}