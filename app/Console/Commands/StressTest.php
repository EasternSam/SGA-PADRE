<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;

class StressTest extends Command
{
    /**
     * El nombre y firma del comando.
     *
     * @var string
     */
    protected $signature = 'system:stress-test 
                            {users=50 : Cantidad de usuarios simultáneos por lote} 
                            {batches=5 : Cantidad de oleadas}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Simula tráfico masivo en el sistema para medir rendimiento.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $concurrency = $this->argument('users');
        $batches = $this->argument('batches');
        $baseUrl = config('app.url'); // Asegúrate que APP_URL en .env sea correcto (ej: http://localhost:8000)

        $this->info("🚀 INICIANDO PRUEBA DE ESTRÉS");
        $this->info("Objetivo: $baseUrl");
        $this->info("Simulación: $batches oleadas de $concurrency usuarios simultáneos (" . ($concurrency * $batches) . " peticiones totales)");
        $this->newLine();

        // 1. Preparar rutas a probar (Rutas pesadas)
        // Nota: Para simular login real necesitaríamos manejar cookies, 
        // aquí probaremos la carga del servidor en rutas públicas y API si tuvieras.
        // Para rutas protegidas, simularemos usando un usuario de prueba si se puede.
        
        $urls = [
            '/' => 'Login Page (Static)',
            // Si tienes rutas públicas pesadas, agrégalas aquí.
            // '/api/public/courses' => 'API Cursos', 
        ];

        // Intentamos obtener un token o simular un usuario logueado es complejo en CLI puro 
        // sin headless browser, así que mediremos el TTFB (Time to First Byte) del servidor.

        $totalTime = 0;
        $totalFailures = 0;
        $maxTime = 0;
        $minTime = 9999;

        $bar = $this->output->createProgressBar($batches);
        $bar->start();

        for ($i = 0; $i < $batches; $i++) {
            
            $startBatch = microtime(true);

            // Disparar peticiones asíncronas paralelas
            $responses = Http::pool(function (Pool $pool) use ($concurrency, $baseUrl) {
                $requests = [];
                for ($j = 0; $j < $concurrency; $j++) {
                    // Simulamos tráfico a la raíz
                    $requests[] = $pool->get($baseUrl);
                }
                return $requests;
            });

            $endBatch = microtime(true);
            $batchDuration = ($endBatch - $startBatch) * 1000; // ms

            // Analizar resultados del lote
            foreach ($responses as $response) {
                if ($response->ok()) {
                    // Éxito
                } elseif ($response->serverError() || $response->failed()) {
                    $totalFailures++;
                }
            }

            // Estadísticas
            $avgBatch = $batchDuration / $concurrency;
            $maxTime = max($maxTime, $avgBatch);
            $minTime = min($minTime, $avgBatch);
            $totalTime += $batchDuration;

            $bar->advance();
            // Pequeña pausa para no ahogar tu propia máquina CLI
            usleep(100000); 
        }

        $bar->finish();
        $this->newLine(2);

        // --- RESULTADOS ---
        $avgResponseTime = $totalTime / ($batches * $concurrency); // Aproximado

        $this->table(
            ['Métrica', 'Resultado', 'Evaluación'],
            [
                ['Peticiones Totales', $batches * $concurrency, 'N/A'],
                ['Fallos (Errores 500/Timeout)', $totalFailures, $totalFailures == 0 ? '<info>Excelente</info>' : '<error>Crítico</error>'],
                ['Tiempo Promedio Respuesta', number_format($avgResponseTime, 2) . ' ms', $this->rateSpeed($avgResponseTime)],
                ['Tiempo Mínimo', number_format($minTime, 2) . ' ms', ''],
                ['Tiempo Máximo (Pico)', number_format($maxTime, 2) . ' ms', ''],
            ]
        );

        $this->diagnose($avgResponseTime, $totalFailures);
    }

    private function rateSpeed($ms)
    {
        if ($ms < 200) return '<info>Rapidísimo 🚀</info>';
        if ($ms < 500) return '<comment>Aceptable ⚠️</comment>';
        return '<error>Lento 🐢</error>';
    }

    private function diagnose($avgTime, $failures)
    {
        $this->info("--- DIAGNÓSTICO DE IA (50,000 Estudiantes) ---");
        
        if ($failures > 0) {
            $this->error("EL SISTEMA FALLÓ BAJO PRESIÓN.");
            $this->line("Si con esta prueba pequeña hubo fallos, con 50,000 estudiantes el sistema colapsará inmediatamente (Error 500/504 Gateway Time-out).");
            return;
        }

        if ($avgTime > 500) {
            $this->comment("EL SISTEMA RESPONDE, PERO LENTO.");
            $this->line("Para 50,000 estudiantes, necesitas optimizar la base de datos y usar caché obligatoriamente. Los tiempos de respuesta se multiplicarán exponencialmente con tráfico real.");
        } else {
            $this->info("EL SISTEMA RESPONDE BIEN (En esta escala).");
            $this->line("Tu configuración actual maneja bien la concurrencia básica. Sin embargo, 50,000 estudiantes es otro nivel.");
        }
    }
}