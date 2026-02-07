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
        $this->merchantId = config('services.cardnet.merchant_id', env('CARDNET_MERCHANT_ID', ''));
        $this->terminalId = config('services.cardnet.terminal_id', env('CARDNET_TERMINAL_ID', ''));
        $this->currency = '214'; 
        $this->environment = config('services.cardnet.environment', 'sandbox');
        
        $urlSandbox = 'https://labservicios.cardnet.com.do/authorize'; 
        $urlProduction = 'https://ecommerce.cardnet.com.do/authorize';
        
        $this->url = ($this->environment === 'production') ? $urlProduction : $urlSandbox;
    }

    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        // MONTO: 12 dígitos
        $amountClean = number_format($amount, 2, '', ''); 
        $formattedAmount = str_pad($amountClean, 12, '0', STR_PAD_LEFT);

        // IMPUESTOS: 12 dígitos (0.00)
        $formattedTax = str_pad('000', 12, '0', STR_PAD_LEFT);

        // TRANSACTION ID: 6 dígitos numéricos aleatorios
        $transactionId = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        // URLs
        $returnUrl = route('cardnet.response');
        $cancelUrl = route('cardnet.cancel');

        if (app()->environment('production') || str_contains(config('app.url'), 'https')) {
            $returnUrl = str_replace('http://', 'https://', $returnUrl);
            $cancelUrl = str_replace('http://', 'https://', $cancelUrl);
        }
        
        // IP
        $cleanIp = (in_array($ipAddress, ['::1', '127.0.0.1'])) ? '172.16.0.1' : substr($ipAddress, 0, 15);

        // --- DATOS MÍNIMOS OBLIGATORIOS (Según Doc "Integración con Pantalla") ---
        // Eliminamos campos opcionales que podrían causar ruido (loteid, seqid, TerminalId duplicado)
        $data = [
            'TransactionType' => '0200', 
            'CurrencyCode'    => $this->currency,
            'AcquiringInstitutionCode' => '349',
            'MerchantType'    => '5440', // Código de comercio de pruebas
            'MerchantNumber'  => $this->merchantId,
            'MerchantTerminal' => $this->terminalId, 
            'ReturnUrl'       => $returnUrl, 
            'CancelUrl'       => $cancelUrl,
            'PageLanguaje'    => 'ENG', // Probemos con ENG por si ESP no está soportado en este sandbox específico
            'OrdenId'         => (string)$orderId,
            'TransactionId'   => $transactionId,
            'Amount'          => $formattedAmount,
            'Tax'             => $formattedTax,
            'MerchantName'    => 'CENTU GESTION ACADEMICA DO',
            'Ipclient'        => $cleanIp,
            // 'KeyEncriptionKey' => '', // Si tienes la clave MD5, ponla aquí. Si no, déjalo comentado.
        ];

        Log::channel('single')->info('--------------------------------------------------');
        Log::channel('single')->info('CARDNET DEBUG V3: Datos Limpios');
        Log::channel('single')->info('URL Destino: ' . $this->url);
        Log::channel('single')->info('Datos Enviados:', $data); 
        Log::channel('single')->info('--------------------------------------------------');

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}