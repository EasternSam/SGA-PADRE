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
        
        // --- URLs CORREGIDAS SEGÚN DOCUMENTACIÓN OFICIAL ---
        // Desarrollo: labservicios.cardnet.com.do
        // Producción: ecommerce.cardnet.com.do
        
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
        // Formato estándar (ej: 100.00)
        $formattedAmount = number_format($amount, 2, '.', '');
        
        // ID único para esta transacción
        $transactionId = time(); 

        $data = [
            'TransactionType' => '0200',
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349',
            'MerchantType'    => '5311',
            'MerchantNumber'  => $this->merchantId,
            'TerminalId'      => $this->terminalId,
            'ReturnUrl'       => route('cardnet.response'), // Asegúrate que esta ruta exista en web.php
            'CancelUrl'       => route('cardnet.cancel'),   // Asegúrate que esta ruta exista en web.php
            'PageLanguage'    => 'ES',
            'OrdenId'         => $orderId,
            'TransactionId'   => $transactionId,
            'Amount'          => $formattedAmount,
            'Tax'             => '0.00',
            'Tip'             => '0.00',
            'IpAddress'       => $ipAddress,
        ];
        
        Log::info("Cardnet: Generando redirección a {$this->url} para Orden #{$orderId}");

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}