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
        
        $this->url = $this->environment === 'production' 
            ? config('services.cardnet.url_production') 
            : config('services.cardnet.url_sandbox');
    }

    /**
     * Prepara los datos para el formulario POST de redirección.
     *
     * @param float $amount Monto a cobrar.
     * @param string $orderId ID único de la orden (referencia de pago).
     * @param string $ipAddress Dirección IP del cliente.
     * @return array Arreglo con 'url' y 'fields'.
     */
    public function prepareFormData($amount, $orderId, $ipAddress = '127.0.0.1')
    {
        // Cardnet requiere el monto en formato estándar con 2 decimales (ej: 100.00)
        $formattedAmount = number_format($amount, 2, '.', '');

        // Datos estándar para la integración POST con pantalla
        // Basado en la documentación de Cardnet "Integración con Pantalla (POST)"
        $data = [
            'TransactionType' => '0200', // Autorización Financiera
            'CurrencyCode'    => $this->currency,
            'AcquirerId'      => '349', // Fijo para Cardnet
            'MerchantType'    => '5311', // Categoría del comercio
            'MerchantNumber'  => $this->merchantId,
            'TerminalId'      => $this->terminalId,
            'ReturnUrl'       => route('cardnet.response'), // Ruta de retorno
            'CancelUrl'       => route('student.payments'), // Ruta si el usuario cancela (volver a pagos estudiante)
            'PageLanguage'    => 'ES',
            'OrdenId'         => $orderId,
            'TransactionId'   => time(), // ID único de transacción para evitar duplicados
            'Amount'          => $formattedAmount,
            'Tax'             => '0.00',
            'Tip'             => '0.00',
            'IpAddress'       => $ipAddress,
        ];

        Log::info("Generando formulario Cardnet para Orden: {$orderId}, Monto: {$formattedAmount}");

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}