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
        $this->currency = '214'; // 214 = DOP (ISO 4217)
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
        // Ejemplo: 100.00 -> 000000010000
        $amountClean = number_format($amount, 2, '', ''); 
        $formattedAmount = str_pad($amountClean, 12, '0', STR_PAD_LEFT);

        // 2. IMPUESTOS: 12 dígitos (0.00)
        $formattedTax = str_pad('000', 12, '0', STR_PAD_LEFT);

        // 3. TRANSACTION ID: Debe ser EXACTAMENTE de 6 dígitos numéricos.
        // Usamos mt_rand para garantizar que sea numérico (time() puede dar problemas de unicidad o longitud)
        $transactionId = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        // 4. URLs de retorno
        $returnUrl = route('cardnet.response');
        $cancelUrl = route('cardnet.cancel');

        // Forzar HTTPS en producción o si la config lo dicta
        if (app()->environment('production') || str_contains(config('app.url'), 'https')) {
            $returnUrl = str_replace('http://', 'https://', $returnUrl);
            $cancelUrl = str_replace('http://', 'https://', $cancelUrl);
        }
        
        // 5. Limpiar IP del cliente (Max 15 caracteres)
        $cleanIp = (in_array($ipAddress, ['::1', '127.0.0.1', 'localhost'])) ? '172.16.0.1' : substr($ipAddress, 0, 15);

        // --- CONSTRUCCIÓN DEL PAYLOAD SEGÚN DOCUMENTACIÓN OFICIAL ---
        $data = [
            'TransactionType'           => '0200', 
            'CurrencyCode'              => $this->currency,
            'AcquiringInstitutionCode'  => '349', // CORREGIDO: Según doc (era AcquirerId)
            'MerchantType'              => '5440', // CORREGIDO: Código estándar pruebas (era 5311)
            'MerchantNumber'            => $this->merchantId,
            'MerchantTerminal'          => $this->terminalId,
            'ReturnUrl'                 => $returnUrl, 
            'CancelUrl'                 => $cancelUrl,
            'PageLanguaje'              => 'ESP', // Ojo con la 'j' en Languaje
            'OrdenId'                   => (string)$orderId,
            'TransactionId'             => $transactionId,
            'Amount'                    => $formattedAmount,
            'Tax'                       => $formattedTax,
            'MerchantName'              => 'CENTU GESTION ACADEMICA DO',
            'Ipclient'                  => $cleanIp,
            // Campos opcionales recomendados para sandbox
            'loteid'                    => '001',
            'seqid'                     => '001',
        ];

        // Campo de seguridad redundante (a veces requerido por versiones legacy del gateway)
        $data['TerminalId'] = $this->terminalId;

        // --- DEBUG COMPLETO (LOGS) ---
        // Esto escribirá en storage/logs/laravel.log
        Log::channel('single')->info('==================================================');
        Log::channel('single')->info('CARDNET: INICIO PREPARACIÓN DE FORMULARIO');
        Log::channel('single')->info('URL ENDPOINT: ' . $this->url);
        Log::channel('single')->info('DATOS ENVIADOS (PAYLOAD):', $data);
        Log::channel('single')->info('MONTO ORIGINAL: ' . $amount);
        Log::channel('single')->info('MONTO FORMATEADO: ' . $formattedAmount);
        Log::channel('single')->info('IP CLIENTE: ' . $cleanIp);
        Log::channel('single')->info('==================================================');

        return [
            'url' => $this->url,
            'fields' => $data
        ];
    }
}