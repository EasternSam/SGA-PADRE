<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\BillsApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPaymentToBillsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * El número de veces que se puede intentar el trabajo.
     */
    public $tries = 3;

    /**
     * El número de segundos que se debe esperar antes de reintentar el trabajo.
     */
    public $backoff = 60;

    protected $payment;

    /**
     * Create a new job instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function handle(BillsApiService $apiService): void
    {
        // Caso 2: Si ya tiene bills_invoice_id, actualizamos su estado a PAGO si el pago local está completado/paid
        if (!empty($this->payment->bills_invoice_id)) {
            if ($this->payment->status === 'paid' || $this->payment->status === 'Completado') {
                try {
                    $this->payment->updateQuietly([
                        'bills_sync_status' => 'processing',
                        'bills_sync_error' => null
                    ]);

                    $apiService->updateInvoiceStatus($this->payment->bills_invoice_id, 'paid');

                    // Recuperar detalles para traer el NCF generado tras el pago
                    $ncf = null;
                    $securityCode = null;
                    $dgiiStatus = 'pending';
                    $dgiiTrackId = null;

                    try {
                        $details = $apiService->getInvoiceDetails($this->payment->bills_invoice_id);
                        if (!empty($details['encf'])) {
                            $ncf = $details['encf'];
                        }
                        if (!empty($details['security_code'])) {
                            $securityCode = $details['security_code'];
                        }
                        if (!empty($details['dgii_status'])) {
                            $dgiiStatus = $details['dgii_status'];
                        }
                        if (!empty($details['dgii_track_id'])) {
                            $dgiiTrackId = $details['dgii_track_id'];
                        }
                    } catch (\Exception $detailsEx) {
                        Log::warning("BILLS_JOB: No se pudieron obtener detalles de e-CF tras marcar como paga: " . $detailsEx->getMessage());
                    }

                    $updateData = [
                        'bills_sync_status' => 'synced',
                        'bills_sync_error' => null
                    ];

                    if (!empty($ncf)) {
                        $updateData['ncf'] = $ncf;
                        $updateData['security_code'] = $securityCode;
                        $updateData['dgii_status'] = $dgiiStatus;
                        $updateData['dgii_track_id'] = $dgiiTrackId;
                    }

                    $this->payment->updateQuietly($updateData);
                    Log::info("BILLS_JOB: Factura #{$this->payment->bills_invoice_number} (ID: {$this->payment->bills_invoice_id}) marcada como PAGA en Bills.");
                } catch (\Exception $e) {
                    $this->payment->updateQuietly([
                        'bills_sync_status' => 'failed',
                        'bills_sync_error' => 'Fallo al marcar como paga: ' . substr($e->getMessage(), 0, 450)
                    ]);
                    throw $e;
                }
            }
            return;
        }

        // Caso 1: Crear factura por primera vez
        // Verificar si ya está sincronizado
        if ($this->payment->bills_sync_status === 'synced') {
            Log::info("BILLS_JOB: El pago #{$this->payment->id} ya está sincronizado.");
            return;
        }

        try {
            // Actualizar estado a procesando
            $this->payment->updateQuietly([
                'bills_sync_status' => 'processing',
                'bills_sync_error' => null
            ]);

            // Sincronizar factura
            $result = $apiService->createInvoiceFromPayment($this->payment);

            if ($result['success']) {
                $invoiceId = $result['invoice_id'];
                $invoiceNumber = $result['invoice_number'];

                // Intentar recuperar los detalles completos por si Bills procesó e-CF (DGII) de inmediato
                $ncf = null;
                $securityCode = null;
                $dgiiStatus = 'pending';
                $dgiiTrackId = null;

                try {
                    $details = $apiService->getInvoiceDetails($invoiceId);
                    
                    // Bills usa 'encf' para el NCF electrónico, 'security_code', 'dgii_status', 'dgii_track_id'
                    if (!empty($details['encf'])) {
                        $ncf = $details['encf'];
                    }
                    if (!empty($details['security_code'])) {
                        $securityCode = $details['security_code'];
                    }
                    if (!empty($details['dgii_status'])) {
                        $dgiiStatus = $details['dgii_status'];
                    }
                    if (!empty($details['dgii_track_id'])) {
                        $dgiiTrackId = $details['dgii_track_id'];
                    }
                } catch (\Exception $detailsEx) {
                    // No abortar el éxito de la sincronización si falla el fetch de e-CF
                    Log::warning("BILLS_JOB: No se pudieron obtener detalles adicionales de la factura #{$invoiceId}: " . $detailsEx->getMessage());
                }

                // Preparar campos a actualizar
                $updateData = [
                    'bills_invoice_id' => $invoiceId,
                    'bills_invoice_number' => $invoiceNumber,
                    'bills_sync_status' => 'synced',
                    'bills_sync_error' => null
                ];

                // Si Bills devolvió información fiscal, la sincronizamos con el pago local
                if (!empty($ncf)) {
                    $updateData['ncf'] = $ncf;
                    $updateData['security_code'] = $securityCode;
                    $updateData['dgii_status'] = $dgiiStatus;
                    $updateData['dgii_track_id'] = $dgiiTrackId;
                }

                // Guardamos usando updateQuietly para no disparar eventos recursivos de Payment
                $this->payment->updateQuietly($updateData);

                Log::info("BILLS_JOB: Pago #{$this->payment->id} sincronizado exitosamente con Bills. Factura: {$invoiceNumber}");
            } else {
                throw new \Exception("La API respondió con fallo inesperado.");
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error("BILLS_JOB: Fallo al sincronizar pago #{$this->payment->id}: " . $errorMessage);

            // Registrar el fallo en el pago
            $this->payment->updateQuietly([
                'bills_sync_status' => 'failed',
                'bills_sync_error' => substr($errorMessage, 0, 500)
            ]);

            // Re-lanzar la excepción para que Laravel gestione el reintento de la cola
            throw $e;
        }
    }
}
