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
        // Formato estricto para Cardnet: 100.00
        $formattedAmount = number_format($amount, 2, '.', '');

        $data = [
            'TransactionType' => '0200',
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349',
            'MerchantType'    => '5311',
            'MerchantNumber'  => $this->merchantId,
            'TerminalId'      => $this->terminalId,
            'ReturnUrl'       => route('cardnet.response'), // Ruta de retorno
            'CancelUrl'       => route('dashboard'),
            'PageLanguage'    => 'ES',
            'OrdenId'         => $orderId,
            'TransactionId'   => time(),
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