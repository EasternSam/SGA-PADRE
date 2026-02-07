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
        $this->currency = config('services.cardnet.currency', '214'); // 214 = DOP
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

        // 3. TRANSACTION ID: Debe ser EXACTAMENTE de 6 dígitos.
        // RESTAURADO: Usamos substr(time) como en tu versión original que funcionaba
        $transactionId = substr((string)time(), -6);

        // 4. URLs de retorno
        $returnUrl = route('cardnet.response');
        $cancelUrl = route('cardnet.cancel');

        if (app()->environment('production') || str_contains(config('app.url'), 'https')) {
            $returnUrl = str_replace('http://', 'https://', $returnUrl);
            $cancelUrl = str_replace('http://', 'https://', $cancelUrl);
        }

        // Datos estrictos según documentación "Integración con Pantalla (POST)"
        // RESTAURADO: AcquirerId en lugar de AcquiringInstitutionCode (como en tu versión original)
        // RESTAURADO: MerchantType 5311 (como en tu versión original)
        $data = [
            'TransactionType' => '0200', 
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349',
            'MerchantType'    => '5311',
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
            'Ipclient'        => substr($ipAddress, 0, 15), 
        ];

        // Mapeo adicional por inconsistencias en documentación
        $data['TerminalId'] = $this->terminalId;

        Log::info("Cardnet Form Data Generado (Versión Restaurada)", [
            'TransactionId' => $transactionId,
            'Amount' => $formattedAmount,
            'OrdenId' => $orderId,
            'Ipclient' => $data['Ipclient']
        ]);

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}