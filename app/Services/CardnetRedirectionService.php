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

        if (app()->environment('production') || str_contains(config('app.url'), 'https')) {
            $returnUrl = str_replace('http://', 'https://', $returnUrl);
            $cancelUrl = str_replace('http://', 'https://', $cancelUrl);
        }
        
        // Limpiar IP del cliente (Max 15 caracteres)
        // CardNet Sandbox a veces rechaza IPs locales. Usamos una IP pública fija genérica de RD para pruebas si es local.
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
            'PageLanguaje'              => 'ESP', // Ojo con la 'j' en Languaje, según doc
            'OrdenId'                   => (string)$orderId,
            'TransactionId'             => $transactionId,
            'Amount'                    => $formattedAmount,
            'Tax'                       => $formattedTax,
            'MerchantName'              => 'CENTU GESTION ACADEMICA DO',
            'Ipclient'                  => $cleanIp,
            
            // Campos opcionales recomendados para sandbox
            'loteid'                    => '001',
            'seqid'                     => '001',

            // --- CAMPOS OBLIGATORIOS 3D SECURE (HARDCODED PARA SANDBOX) ---
            // Estos campos son requeridos para evitar el error TF en terminales 3DS.
            '3DS_email'                 => 'prueba@centu.edu.do',
            '3DS_mobilePhone'           => '8095555555',
            '3DS_workPhone'             => '8095555555',
            '3DS_homePhone'             => '8095555555',
            '3DS_billAddr_line1'        => 'Calle Principal No 1',
            '3DS_billAddr_line2'        => 'Sector Central',
            '3DS_billAddr_line3'        => ' ', // Espacio en blanco si está vacío, a veces ayuda
            '3DS_billAddr_city'         => 'Santo Domingo',
            '3DS_billAddr_state'        => 'DN',
            '3DS_billAddr_country'      => 'DOP', // Según ejemplo doc
            '3DS_billAddr_postCode'     => '10101',
            '3DS_shipAddr_line1'        => 'Calle Principal No 1',
            '3DS_shipAddr_line2'        => 'Sector Central',
            '3DS_shipAddr_line3'        => ' ',
            '3DS_shipAddr_city'         => 'Santo Domingo',
            '3DS_shipAddr_state'        => 'DN',
            '3DS_shipAddr_country'      => 'DOP',
            '3DS_shipAddr_postCode'     => '10101',
        ];

        // Mapeo adicional por inconsistencias en documentación (enviamos ambos por seguridad)
        $data['TerminalId'] = $this->terminalId;

        // Generar KeyEncriptionKey si existe una secret key configurada
        // La fórmula estándar: MD5(MerchantNumber + MerchantTerminal + TransactionId + Amount + SecretKey)
        if (!empty($this->secretKey)) {
             $stringToHash = $this->merchantId . $this->terminalId . $transactionId . $formattedAmount . $this->secretKey;
             $data['KeyEncriptionKey'] = md5($stringToHash);
        }

        // --- DEBUG COMPLETO (LOGS) ---
        Log::channel('single')->info('==================================================');
        Log::channel('single')->info('CARDNET: INICIO PREPARACIÓN DE FORMULARIO (3DS FIXED)');
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