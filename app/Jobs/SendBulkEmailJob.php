<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CustomSystemMail;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $recipients;
    public $subject;
    public $messageBody;

    /**
     * Create a new job instance.
     * 
     * @param array $recipients Array of associative arrays, e.g. [['email' => 'a@b.com', 'name' => 'Juan', 'course' => 'Inglés']]
     */
    public function __construct(array $recipients, string $subject, string $messageBody)
    {
        $this->recipients = $recipients;
        $this->subject = $subject;
        $this->messageBody = $messageBody;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("SendBulkEmailJob: Iniciando envío masivo a " . count($this->recipients) . " destinatarios.");
        $successCount = 0;
        $errorCount = 0;

        foreach ($this->recipients as $recipient) {
            try {
                // Validación básica
                if (empty($recipient['email'])) {
                    continue; // Saltar si no hay correo
                }

                // Inyección de variables mágicas
                $personalizedBody = $this->messageBody;
                
                if (isset($recipient['name'])) {
                    $personalizedBody = str_replace('[NOMBRE_ESTUDIANTE]', $recipient['name'], $personalizedBody);
                }
                
                if (isset($recipient['course'])) {
                    $personalizedBody = str_replace('[NOMBRE_CURSO]', $recipient['course'], $personalizedBody);
                }
                
                // Otras variables pueden ser agregadas aquí en el futuro (ej. balance pendiente)
                if (isset($recipient['balance'])) {
                    $personalizedBody = str_replace('[BALANCE_PENDIENTE]', number_format($recipient['balance'], 2), $personalizedBody);
                }

                // Envío sincrónico dentro de este Job encolado
                Mail::to($recipient['email'])->send(new CustomSystemMail($this->subject, $personalizedBody));
                
                $successCount++;
                
                // Pequeña pausa opcional (sleep) para no saturar servidores SMTP gratuitos/baratos
                // usleep(200000); // 0.2 segundos
                
            } catch (\Exception $e) {
                Log::error("SendBulkEmailJob - Error al enviar a {$recipient['email']}: " . $e->getMessage());
                $errorCount++;
            }
        }

        Log::info("SendBulkEmailJob: Finalizado. Éxitos: $successCount, Errores: $errorCount.");
    }
}
