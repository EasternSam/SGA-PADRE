<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Jobs\SyncPaymentToBillsJob;

class SyncPaymentToBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:sync-payment {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza un pago del SGA con Gridbase Bills de forma manual (despachando el Job)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        
        $payment = Payment::find($id);

        if (!$payment) {
            $this->error("No se encontró el pago con ID #{$id}.");
            return Command::FAILURE;
        }

        $this->info("Despachando Job de sincronización para el pago #{$id}...");
        
        // Ejecutar de forma síncrona en la consola para ver errores de inmediato
        $this->info("Ejecutando de forma síncrona para depuración...");
        
        try {
            $job = new SyncPaymentToBillsJob($payment);
            app()->call([$job, 'handle']);
            
            $payment->refresh();
            
            if ($payment->bills_sync_status === 'synced') {
                $this->info("¡Éxito! Pago sincronizado.");
                $this->line("Factura Bills ID: " . $payment->bills_invoice_id);
                $this->line("Factura Bills No: " . $payment->bills_invoice_number);
                if ($payment->ncf) {
                    $this->line("NCF devuelto por Bills: " . $payment->ncf);
                }
                return Command::SUCCESS;
            } else {
                $this->error("La sincronización no quedó marcada como exitosa.");
                $this->comment("Estado: " . $payment->bills_sync_status);
                $this->comment("Error registrado: " . $payment->bills_sync_error);
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Excepción detectada: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
