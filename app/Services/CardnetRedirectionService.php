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
    protected $secretKey; // Clave para generar hash si es necesario

    public function __construct()
    {
        // Cargar credenciales
        $this->merchantId = config('services.cardnet.merchant_id', env('CARDNET_MERCHANT_ID', ''));
        $this->terminalId = config('services.cardnet.terminal_id', env('CARDNET_TERMINAL_ID', ''));
        
        // Asegurar código numérico ISO 4217 para DOP (214)
        $this->currency = '214'; 

        $this->environment = config('services.cardnet.environment', 'sandbox');
        
        // Clave secreta para Hash (KeyEncriptionKey) - Asegúrate de tener esto en tu .env si aplica
        // Si no tienes una, déjala vacía, pero si el terminal es estricto la necesitarás.
        $this->secretKey = config('services.cardnet.secret_key', env('CARDNET_SECRET_KEY', '')); 

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
    // ================= MONTO =================
    $amountClean = number_format($amount, 2, '', '');
    $formattedAmount = str_pad($amountClean, 12, '0', STR_PAD_LEFT);

    $formattedTax = str_pad('0', 12, '0', STR_PAD_LEFT);

    // ================= IDS =================
    // TransactionId EXACTO 6 dígitos
    $transactionId = str_pad((string)random_int(1, 999999), 6, '0', STR_PAD_LEFT);

    // ORDEN ID DEBE SER ÚNICO EN SANDBOX
    $orderId = $orderId . '-' . now()->format('His');

    // ================= URLs =================
    $returnUrl = route('cardnet.response');
    $cancelUrl = route('cardnet.cancel');

    $returnUrl = str_replace('http://', 'https://', $returnUrl);
    $cancelUrl = str_replace('http://', 'https://', $cancelUrl);

    // ================= IP =================
    $cleanIp = in_array($ipAddress, ['127.0.0.1', '::1'])
        ? '200.88.167.233'
        : substr($ipAddress, 0, 15);

    // ================= TRANSACTION TYPE =================
    // SANDBOX SOLO FUNCIONA BIEN CON 0100
    $transactionType = $this->environment === 'production'
        ? '0200'
        : '0100';

    // ================= PAYLOAD =================
    $data = [
        'TransactionType'          => $transactionType,
        'CurrencyCode'             => '214',
        'AcquiringInstitutionCode' => '349',
        'MerchantType'             => '5440',
        'MerchantNumber'           => $this->merchantId,
        'MerchantTerminal'         => $this->terminalId,
        'TerminalId'               => $this->terminalId,

        'OrdenId'                  => (string)$orderId,
        'TransactionId'            => $transactionId,
        'Amount'                   => $formattedAmount,
        'Tax'                      => $formattedTax,

        'ReturnUrl'                => $returnUrl,
        'CancelUrl'                => $cancelUrl,
        'PageLanguaje'             => 'ESP',

        'MerchantName'             => 'CENTU GESTION ACADEMICA DO',
        'Ipclient'                 => $cleanIp,

        // ===== 3DS (mínimo viable para sandbox) =====
        '3DS_email'            => 'sandbox@centu.edu.do',
        '3DS_mobilePhone'      => '8095555555',
        '3DS_billAddr_line1'   => 'Calle Principal',
        '3DS_billAddr_city'    => 'Santo Domingo',
        '3DS_billAddr_country' => 'DOP',
        '3DS_billAddr_postCode'=> '10101',
    ];

    // ================= HASH =================
    // EN SANDBOX SIEMPRE ENVIARLO
    $secret = $this->secretKey ?: 'SANDBOXKEY';

    $hashString = $this->merchantId
        . $this->terminalId
        . $transactionId
        . $formattedAmount
        . $secret;

    $data['KeyEncriptionKey'] = md5($hashString);

    // ================= LOG =================
    Log::info('========== CARDNET SANDBOX FIX ==========');
    Log::info('PAYLOAD:', $data);
    Log::info('=========================================');

    return [
        'url' => $this->url,
        'fields' => $data
    ];
}
}