<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

/**
 * Servicio para integrar el SGA con cualquier distribución de Gridbase Bills.
 */
class BillsApiService
{
    protected $apiUrl;
    protected $apiToken;
    protected $defaultTaxRate;
    protected $enabled;

    public function __construct()
    {
        try {
            $this->enabled = Setting::get('enable_bills_invoicing', 'false') === 'true';
            $this->apiUrl = Setting::get('bills_api_url', '');
            $this->apiToken = Setting::get('bills_api_token', '');
            $this->defaultTaxRate = (float) Setting::get('bills_default_tax_rate', '0');
        } catch (QueryException $e) {
            // Prevenir fallos en migraciones o consola sin base de datos activa
            $this->enabled = false;
            $this->apiUrl = '';
            $this->apiToken = '';
            $this->defaultTaxRate = 0.0;
        }
    }

    /**
     * Verifica si la integración está habilitada y configurada correctamente.
     */
    public function isConfigured(): bool
    {
        return $this->enabled && !empty($this->apiUrl) && !empty($this->apiToken);
    }

    /**
     * Envía un pago del SGA como factura a Gridbase Bills.
     *
     * @param Payment $payment
     * @return array Resumen del resultado del envío.
     * @throws \Exception
     */
    public function createInvoiceFromPayment(Payment $payment): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception("La integración con Gridbase Bills no está activa o le faltan configuraciones (URL/Token).");
        }

        $student = $payment->student;
        if (!$student) {
            throw new \Exception("El pago #{$payment->id} no tiene un estudiante asociado.");
        }

        // 1. Resolver los datos del cliente para Bills
        $clientTaxId = !empty($payment->rnc_client) ? $payment->rnc_client : (!empty($student->rnc) ? $student->rnc : '');
        $clientName = !empty($payment->company_name) ? $payment->company_name : $student->post_title;
        
        $clientPayload = [
            'tax_id' => $clientTaxId,
            'company_name' => $clientName,
            'contact_name' => $student->post_title,
            'email' => $student->email,
            'phone' => $student->telefono,
            'whatsapp' => $student->telefono,
            'address_line1' => $student->direccion ?: 'República Dominicana',
        ];

        // 2. Resolver concepto e items
        $conceptName = $payment->paymentConcept ? $payment->paymentConcept->name : 'Pago de Matrícula / Servicios';
        $items = [
            [
                'description' => $conceptName . " - Ref: SGA-{$payment->id}",
                'quantity' => 1,
                'unit_price' => (float) $payment->amount,
            ]
        ];

        // 3. Determinar facturación electrónica
        // Si el SGA tiene activada la facturación electrónica general
        $isElectronic = Setting::get('enable_electronic_billing', 'true') === 'true';
        $ecfType = 32; // Por defecto: Consumo (32)
        
        if (!empty($payment->rnc_client)) {
            $ecfType = 31; // Crédito Fiscal
        } elseif ($payment->ncf_type_requested === '01') {
            $ecfType = 31;
        } elseif ($student->rnc && (strlen($student->rnc) == 9 || strlen($student->rnc) == 11)) {
            $ecfType = 31;
        }

        // 4. Armar el payload completo de factura
        $payload = [
            'client' => $clientPayload,
            'items' => $items,
            'currency' => $payment->currency ?: 'DOP',
            'tax_rate' => $this->defaultTaxRate,
            'discount_type' => 'fixed',
            'discount_value' => (float) ($payment->discount_amount ?: 0),
            'notes' => $payment->notes ?: 'Generado automáticamente desde SGA',
            'issue_date' => $payment->created_at ? $payment->created_at->toDateString() : now()->toDateString(),
            'due_date' => $payment->due_date ? $payment->due_date->toDateString() : now()->toDateString(),
            'is_ecf' => $isElectronic,
            'ecf_type' => $ecfType,
            'status' => ($payment->status === 'paid' || $payment->status === 'Completado') ? 'paid' : 'sent',
        ];

        // 5. Enviar la petición HTTP
        $fullUrl = rtrim($this->apiUrl, '/') . '/invoices';

        Log::info("BILLS_API: Enviando factura para pago #{$payment->id}", [
            'url' => $fullUrl,
            'is_ecf' => $isElectronic,
            'ecf_type' => $ecfType
        ]);

        try {
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-SGA-Centu-Client/1.0',
                ])
                ->post($fullUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("BILLS_API: Factura creada exitosamente en Bills.", [
                    'bills_invoice_id' => $data['data']['id'] ?? null,
                    'bills_invoice_number' => $data['data']['invoice_number'] ?? null,
                ]);

                return [
                    'success' => true,
                    'invoice_id' => $data['data']['id'] ?? null,
                    'invoice_number' => $data['data']['invoice_number'] ?? null,
                    'data' => $data['data'] ?? [],
                ];
            }

            $errorBody = $response->body();
            Log::error("BILLS_API: Error de respuesta HTTP {$response->status()}", [
                'response' => substr($errorBody, 0, 1000)
            ]);

            throw new \Exception("Error al crear factura en Bills (Status: {$response->status()}): " . ($response->json('error') ?: 'Respuesta inválida del servidor.'));

        } catch (\Exception $e) {
            Log::error("BILLS_API: Excepción de conexión", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene los detalles completos de una factura en Bills.
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getInvoiceDetails(int $id): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception("La integración con Gridbase Bills no está activa o le faltan configuraciones.");
        }

        $fullUrl = rtrim($this->apiUrl, '/') . "/invoices/{$id}";

        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-SGA-Centu-Client/1.0',
                ])
                ->get($fullUrl);

            if ($response->successful()) {
                return $response->json('data') ?: [];
            }

            throw new \Exception("Error al consultar factura #{$id} en Bills (Status: {$response->status()}): " . $response->body());

        } catch (\Exception $e) {
            Log::error("BILLS_API: Excepción al consultar factura #{$id}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Prueba la conexión contra la API de Bills
     */
    public function testConnection(): array
    {
        if (empty($this->apiUrl) || empty($this->apiToken)) {
            return [
                'success' => false,
                'message' => 'Faltan parámetros de conexión (URL/Token).'
            ];
        }

        $fullUrl = rtrim($this->apiUrl, '/') . '/clients'; // Endpoint simple para listar clientes

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-SGA-Centu-Client/1.0',
                ])
                ->get($fullUrl);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexión establecida correctamente con Bills API (Código: ' . $response->status() . ').'
                ];
            }

            return [
                'success' => false,
                'message' => 'El servidor respondió con error (Código: ' . $response->status() . '): ' . $response->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza el estado de una factura en Bills.
     *
     * @param int $id
     * @param string $status
     * @return array
     * @throws \Exception
     */
    public function updateInvoiceStatus(int $id, string $status): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception("La integración con Gridbase Bills no está activa o le faltan configuraciones.");
        }

        $fullUrl = rtrim($this->apiUrl, '/') . "/invoices/{$id}";

        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-SGA-Centu-Client/1.0',
                ])
                ->put($fullUrl, [
                    'status' => $status
                ]);

            if ($response->successful()) {
                return $response->json() ?: [];
            }

            $errorBody = $response->body();
            Log::error("BILLS_API: Error al actualizar factura #{$id} en Bills (Status: {$response->status()})", [
                'response' => substr($errorBody, 0, 1000)
            ]);

            throw new \Exception("Error al actualizar factura #{$id} en Bills (Status: {$response->status()}): " . ($response->json('error') ?: 'Respuesta inválida.'));

        } catch (\Exception $e) {
            Log::error("BILLS_API: Excepción al actualizar factura #{$id}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Descarga el PDF de una factura desde Bills como binario.
     *
     * @param int $id
     * @param string $template
     * @return string Contenido binario del PDF
     * @throws \Exception
     */
    public function getInvoicePdfStream(int $id, string $template = 'thermal'): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception("La integración con Gridbase Bills no está activa.");
        }

        $fullUrl = rtrim($this->apiUrl, '/') . "/invoices/{$id}/pdf";

        try {
            $response = Http::withoutVerifying()
                ->timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/pdf',
                    'User-Agent' => 'Laravel-SGA-Centu-Client/1.0',
                ])
                ->get($fullUrl, [
                    'template' => $template
                ]);

            if ($response->successful()) {
                return $response->body();
            }

            throw new \Exception("Fallo al descargar PDF de Bills (Status: {$response->status()}): " . $response->body());

        } catch (\Exception $e) {
            Log::error("BILLS_API: Excepción al descargar PDF para factura #{$id}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
