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
        $this->merchantId = config('services.cardnet.merchant_id');
        $this->terminalId = config('services.cardnet.terminal_id');
        $this->currency = config('services.cardnet.currency', '214'); // 214 = DOP
        
        $environment = config('services.cardnet.environment', 'sandbox');
        
        $urlSandbox = 'https://labservicios.cardnet.com.do/authorize'; 
        $urlProduction = 'https://ecommerce.cardnet.com.do/authorize';
        
        $this->url = $environment === 'production' 
            ? config('services.cardnet.url_production', $urlProduction) 
            : config('services.cardnet.url_sandbox', $urlSandbox);
    }

    /**
     * Prepara los datos para el formulario POST de redirección.
     */
    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        // CORRECCIÓN: Cardnet espera el monto en CENTAVOS y sin puntos decimales.
        // Además, requiere una longitud de 12 dígitos, rellenando con ceros a la izquierda.
        // Ejemplo: 500.00 -> 50000 -> "000000050000"
        
        // 1. Formatear a 2 decimales sin separador de miles ni punto decimal (multiplica por 100 visualmente)
        $amountClean = number_format($amount, 2, '', ''); 
        
        // 2. Rellenar con ceros a la izquierda hasta 12 dígitos
        $formattedAmount = str_pad($amountClean, 12, '0', STR_PAD_LEFT);

        $transactionId = time();

        $data = [
            'TransactionType' => '0200',
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349',
            'MerchantType'    => '5311',
            'MerchantNumber'  => $this->merchantId,
            'TerminalId'      => $this->terminalId,
            'ReturnUrl'       => route('cardnet.response'), 
            'CancelUrl'       => route('cardnet.cancel'),
            'PageLanguage'    => 'ES',
            'OrdenId'         => $orderId,
            'TransactionId'   => $transactionId,
            'Amount'          => $formattedAmount,
            'Tax'             => '000000000000', // Tax también debe tener formato de 12 dígitos si se envía
            'Tip'             => '000000000000',
            'IpAddress'       => $ipAddress,
        ];

        Log::info("Cardnet Form Data: Monto Original: {$amount} -> Enviado: {$formattedAmount} | Orden: {$orderId}");

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}