<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportCustomCsv extends Command
{
    protected $signature = 'app:import-custom {type} {file}';
    protected $description = 'Importa CSV masivos usando los scripts ultra-rápidos (type: students_csv o financial_csv)';

    public function handle()
    {
        $type = $this->argument('type');
        $file = $this->argument('file');

        if (!in_array($type, ['students_csv', 'financial_csv'])) {
            $this->error("Tipo inválido. Use 'students_csv' o 'financial_csv'.");
            return;
        }

        if (!file_exists($file)) {
            $this->error("El archivo no existe: $file");
            return;
        }

        $this->info("Iniciando importación masiva en formato RAW para $type...");
        
        // Redirigimos a los comandos ultrarrápidos que ya están configurados para parsear el CSV original con punto y coma (;)
        if ($type === 'students_csv') {
            $this->call('app:import-students-fast', [
                'file' => $file
            ]);
        } else {
            $this->call('app:import-financials-fast', [
                'file' => $file
            ]);
        }

        $this->newLine();
        $this->info("¡Proceso de delegado a importadores rápidos finalizado!");
    }
}

