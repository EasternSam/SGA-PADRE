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
        $this->merchantId = config('services.cardnet.merchant_id');
        $this->terminalId = config('services.cardnet.terminal_id');
        $this->currency = config('services.cardnet.currency', '214'); // 214 = DOP
        $this->environment = config('services.cardnet.environment', 'sandbox');
        
        // URLs CORREGIDAS SEGÚN DOCUMENTACIÓN "INTEGRACIÓN CON PANTALLA (POST)"
        $urlSandbox = 'https://labservicios.cardnet.com.do/authorize'; 
        $urlProduction = 'https://ecommerce.cardnet.com.do/authorize';
        
        $this->url = $this->environment === 'production' 
            ? config('services.cardnet.url_production', $urlProduction) 
            : config('services.cardnet.url_sandbox', $urlSandbox);
    }

    /**
     * Prepara los datos para el formulario POST de redirección.
     */
    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        // Cardnet requiere el monto en formato estándar (ej: 100.00)
        $formattedAmount = number_format($amount, 2, '.', '');

        // Generar TransactionId único (timestamp)
        $transactionId = time();

        $data = [
            'TransactionType' => '0200', // Autorización Financiera
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349', // Fijo para Cardnet
            'MerchantType'    => '5311', // Código de categoría (Educación)
            'MerchantNumber'  => $this->merchantId,
            'TerminalId'      => $this->terminalId,
            'ReturnUrl'       => route('cardnet.response'), // Ruta de retorno
            'CancelUrl'       => route('cardnet.cancel'), // Ruta de cancelación
            'PageLanguage'    => 'ES',
            'OrdenId'         => $orderId,
            'TransactionId'   => $transactionId,
            'Amount'          => $formattedAmount,
            'Tax'             => '0.00',
            'Tip'             => '0.00',
            'IpAddress'       => $ipAddress,
        ];

        Log::info("Generando redirección Cardnet: URL {$this->url} | Orden {$orderId}");

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}