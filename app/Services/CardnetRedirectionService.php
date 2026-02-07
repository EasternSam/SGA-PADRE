<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CardnetRedirectionService
{
    protected $merchantId;
    protected $terminalId;
    protected $currency;
    protected $url;
    protected $environment;

    public function __construct()
    {
        // Cargar credenciales
        $this->merchantId = config('services.cardnet.merchant_id', env('CARDNET_MERCHANT_ID', ''));
        $this->terminalId = config('services.cardnet.terminal_id', env('CARDNET_TERMINAL_ID', ''));
        
        // Asegurar código numérico ISO 4217 para DOP (214)
        $this->currency = '214'; 

        $this->environment = config('services.cardnet.environment', 'sandbox');
        
        // URLs OFICIALES
        $urlSandbox = 'https://labservicios.cardnet.com.do/authorize'; 
        $urlProduction = 'https://ecommerce.cardnet.com.do/authorize';
        
        if ($this->environment === 'production') {
            $this->url = config('services.cardnet.url_production', $urlProduction);
        } else {
            $this->url = config('services.cardnet.url_sandbox', $urlSandbox);
        }
    }

    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        // 1. MONTO: 12 dígitos, ceros a la izquierda, sin puntos.
        $amountClean = number_format($amount, 2, '', ''); 
        $formattedAmount = str_pad($amountClean, 12, '0', STR_PAD_LEFT);

        // 2. IMPUESTOS: 12 dígitos (0.00)
        $formattedTax = str_pad('000', 12, '0', STR_PAD_LEFT);

        // 3. TRANSACTION ID: 6 dígitos numéricos.
        // Usamos mt_rand para evitar problemas de formato.
        $transactionId = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        // 4. URLs de retorno
        $returnUrl = route('cardnet.response');
        $cancelUrl = route('cardnet.cancel');

        if (app()->environment('production') || str_contains(config('app.url'), 'https')) {
            $returnUrl = str_replace('http://', 'https://', $returnUrl);
            $cancelUrl = str_replace('http://', 'https://', $cancelUrl);
        }
        
        // Limpiar IP
        $cleanIp = (in_array($ipAddress, ['::1', '127.0.0.1'])) ? '172.16.0.1' : substr($ipAddress, 0, 15);

        // Datos estrictos según documentación "Integración con Pantalla (POST)"
        $data = [
            'TransactionType' => '0200', 
            'CurrencyCode'    => $this->currency,
            'AcquiringInstitutionCode' => '349',
            'MerchantType'    => '5440', // Según documentación
            'MerchantNumber'  => $this->merchantId,
            'MerchantTerminal' => $this->terminalId, 
            'ReturnUrl'       => $returnUrl, 
            'CancelUrl'       => $cancelUrl,
            'PageLanguaje'    => 'ESP', 
            'OrdenId'         => (string)$orderId,
            'TransactionId'   => $transactionId,
            'Amount'          => $formattedAmount,
            'Tax'             => $formattedTax,
            'MerchantName'    => 'CENTU GESTION ACADEMICA DO',
            'Ipclient'        => $cleanIp,
            'loteid'          => '001',
            'seqid'           => '001',
        ];

        // Mapeo adicional de seguridad (algunas versiones piden TerminalId)
        $data['TerminalId'] = $this->terminalId;

        // --- DEBUG LOGGING ---
        Log::channel('single')->info('--------------------------------------------------');
        Log::channel('single')->info('CARDNET DEBUG: Iniciando preparación de formulario');
        Log::channel('single')->info('URL Destino: ' . $this->url);
        Log::channel('single')->info('Datos Enviados:', $data);
        Log::channel('single')->info('--------------------------------------------------');

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}