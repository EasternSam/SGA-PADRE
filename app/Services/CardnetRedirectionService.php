<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CardnetRedirectionService
{
    protected $merchantId;
    protected $terminalId;
    protected $currency;
    protected $url;

    public function __construct()
    {
        // Usamos valores por defecto seguros si la configuración falla
        $this->merchantId = config('services.cardnet.merchant_id', env('CARDNET_MERCHANT_ID', ''));
        $this->terminalId = config('services.cardnet.terminal_id', env('CARDNET_TERMINAL_ID', ''));
        $this->currency = config('services.cardnet.currency', '214');
        
        $environment = config('services.cardnet.environment', env('CARDNET_ENV', 'sandbox'));
        
        // URLs oficiales por defecto (Respaldo)
        $urlSandbox = 'https://lab.cardnet.com.do/authorize';
        $urlProduction = 'https://payments.cardnet.com.do/authorize';
        
        // Priorizar config, sino usar respaldo
        if ($environment === 'production') {
            $this->url = config('services.cardnet.url_production', $urlProduction);
        } else {
            $this->url = config('services.cardnet.url_sandbox', $urlSandbox);
        }
    }

    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        // Asegurar formato 00.00
        $formattedAmount = number_format($amount, 2, '.', '');
        $transactionId = time();

        $data = [
            'TransactionType' => '0200',
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349',
            'MerchantType'    => '5311',
            'MerchantNumber'  => $this->merchantId,
            'TerminalId'      => $this->terminalId,
            'ReturnUrl'       => route('cardnet.response'), // Ruta de Éxito/Fallo
            'CancelUrl'       => route('cardnet.cancel'),   // Ruta de Cancelación (POST)
            'PageLanguage'    => 'ES',
            'OrdenId'         => $orderId,
            'TransactionId'   => $transactionId,
            'Amount'          => $formattedAmount,
            'Tax'             => '0.00',
            'Tip'             => '0.00',
            'IpAddress'       => $ipAddress,
        ];

        // Validación de seguridad: Log si falta configuración crítica
        if (empty($this->merchantId) || empty($this->terminalId)) {
            Log::error("Cardnet Error: MerchantID o TerminalID están vacíos.");
        }

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}