<?php

namespace App\Services;

class CardnetRedirectionService
{
    protected $merchantId;
    protected $terminalId;
    protected $currency;
    protected $url;

    public function __construct()
    {
        $this->merchantId = config('services.cardnet.merchant_id');
        $this->terminalId = config('services.cardnet.terminal_id');
        $this->currency = config('services.cardnet.currency', '214');
        
        $environment = config('services.cardnet.environment', 'sandbox');
        $this->url = $environment === 'production' 
            ? config('services.cardnet.url_production') 
            : config('services.cardnet.url_sandbox');
    }

    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        $formattedAmount = number_format($amount, 2, '.', '');
        $transactionId = time();

        $data = [
            'TransactionType' => '0200',
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349',
            'MerchantType'    => '5311',
            'MerchantNumber'  => $this->merchantId,
            'TerminalId'      => $this->terminalId,
            'ReturnUrl'       => route('cardnet.response'), // Ruta de Éxito/Fallo (POST)
            'CancelUrl'       => route('cardnet.cancel'),   // Ruta de Cancelación (POST) <--- CAMBIO CLAVE
            'PageLanguage'    => 'ES',
            'OrdenId'         => $orderId,
            'TransactionId'   => $transactionId,
            'Amount'          => $formattedAmount,
            'Tax'             => '0.00',
            'Tip'             => '0.00',
            'IpAddress'       => $ipAddress,
        ];

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}