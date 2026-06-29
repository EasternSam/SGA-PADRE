<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Jobs\SyncPaymentToBillsJob;

class SyncExistingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:sync-existing 
                            {--only-paid : Sincronizar solo los cobros que estén completados o pagados}
                            {--limit= : Limitar la cantidad de cobros a procesar (evitar sobrecargas)}
                            {--dry-run : Ejecutar una simulación sin encolar los trabajos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encola la sincronización de cobros/facturas existentes en el SGA que no se han enviado a Bills';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Buscando cobros sin sincronizar en Bills...");

        $query = Payment::whereNull('bills_invoice_id');

        if ($this->option('only-paid')) {
            $query->whereIn('status', ['paid', 'Completado']);
            $this->comment("Filtrando: Solo cobros completados/pagados.");
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info("Todos los cobros ya se encuentran sincronizados con Bills.");
            return Command::SUCCESS;
        }

        $this->warn("Se encontraron {$count} cobros pendientes de sincronización.");

        $limit = $this->option('limit');
        if ($limit) {
            $query->limit((int) $limit);
            $this->comment("Aplicando límite de: {$limit} registros.");
            $countToProcess = min($count, (int) $limit);
        } else {
            $countToProcess = $count;
        }

        if ($this->option('dry-run')) {
            $this->info("[Simulación] Se habrían encolado {$countToProcess} cobros para sincronización.");
            return Command::SUCCESS;
        }

        if (!$this->confirm("¿Deseas encolar estos {$countToProcess} cobros en segundo plano?", true)) {
            $this->comment("Operación cancelada.");
            return Command::SUCCESS;
        }

        $payments = $query->get();
        $bar = $this->output->createProgressBar(count($payments));
        $bar->start();

        foreach ($payments as $payment) {
            // Encolar el job asíncrono
            SyncPaymentToBillsJob::dispatch($payment);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("¡Éxito! Se han encolado {$countToProcess} trabajos de sincronización.");
        $this->comment("Asegúrate de que 'php artisan queue:work' o 'php artisan queue:listen' esté ejecutándose para procesarlos.");

        return Command::SUCCESS;
    }
}
