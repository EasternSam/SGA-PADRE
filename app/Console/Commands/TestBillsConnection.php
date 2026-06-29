<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillsApiService;

class TestBillsConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica la conexión con el servidor de Gridbase Bills utilizando el API Token';

    /**
     * Execute the console command.
     */
    public function handle(BillsApiService $apiService)
    {
        $this->info("Probando conexión con Gridbase Bills...");

        if (!$apiService->isConfigured()) {
            $this->error("La integración no está configurada o está deshabilitada en la tabla settings.");
            $this->comment("Verifica enable_bills_invoicing, bills_api_url y bills_api_token.");
            return Command::FAILURE;
        }

        $result = $apiService->testConnection();

        if ($result['success']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        } else {
            $this->error("Fallo de conexión: " . $result['message']);
            return Command::FAILURE;
        }
    }
}
