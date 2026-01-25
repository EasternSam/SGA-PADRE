<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\CustomSystemMail;
use App\Models\CourseSchedule;
use App\Models\Payment;
use App\Models\Student;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class EmailTester extends Component
{
    // Campos del Formulario
    public $audience = 'individual';
    public $emailTo;
    public $sectionId;
    public $subject;
    public $messageBody;

    // Datos de UI
    public $availableSections = [];
    public $recipientCount = 0; 
    public $debugLog = [];

    // --- VARIABLES DE PROCESO ---
    public $isProcessing = false;
    public $batchId = null;
    public $totalToSend = 0;
    public $sentCount = 0;
    public $progress = 0;

    protected function rules()
    {
        $rules = [
            'audience' => 'required|in:individual,section,debt,all',
            'subject' => 'required|string|min:3',
            'messageBody' => 'required|string|min:10',
        ];

        if ($this->audience === 'individual') {
            $rules['emailTo'] = 'required|email';
        }

        if ($this->audience === 'section') {
            $rules['sectionId'] = 'required|exists:course_schedules,id';
        }

        return $rules;
    }

    public function mount()
    {
        // INTENTO DE SOLUCIÓN AL ERROR "DATABASE IS LOCKED"
        // Configuramos SQLite para que espere hasta 30 segundos antes de fallar por bloqueo.
        // Esto ayuda a que la escritura de sesión al finalizar el request tenga tiempo de completarse.
        $this->configureSqliteTimeout();

        // NO cargar nada pesado al inicio
        $this->availableSections = [];
    }

    protected function configureSqliteTimeout()
    {
        try {
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::connection()->statement('PRAGMA busy_timeout = 30000;');
            }
        } catch (\Exception $e) {
            // Continuar silenciosamente si no se puede configurar
        }
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'audience' && $this->audience === 'section') {
            $this->loadSections();
        }

        if (in_array($propertyName, ['audience', 'sectionId', 'emailTo'])) {
            $this->calculateRecipients();
        }
    }

    public function loadSections()
    {
        if (!empty($this->availableSections)) return;

        try {
            // Consulta optimizada para SQLite
            $this->availableSections = DB::table('course_schedules')
                ->join('modules', 'course_schedules.module_id', '=', 'modules.id')
                ->join('courses', 'modules.course_id', '=', 'courses.id')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('enrollments')
                          ->whereColumn('enrollments.course_schedule_id', 'course_schedules.id')
                          ->whereIn('status', ['Cursando', 'Activo']);
                })
                ->select(
                    'course_schedules.id',
                    'courses.name as course_name',
                    'modules.name as module_name',
                    'course_schedules.section_name'
                )
                ->orderBy('course_schedules.created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'name' => "{$row->course_name} - {$row->module_name} ({$row->section_name})"
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            // Log a archivo, no a BD para evitar bloqueos
            Log::channel('daily')->error("EmailTester: Error cargando secciones: " . $e->getMessage());
        }
    }

    public function calculateRecipients()
    {
        // Cálculo ligero
        try {
            switch ($this->audience) {
                case 'individual':
                    $this->recipientCount = !empty($this->emailTo) ? 1 : 0;
                    break;
                case 'section':
                    $this->recipientCount = $this->sectionId
                        ? DB::table('enrollments')
                            ->where('course_schedule_id', $this->sectionId)
                            ->whereIn('status', ['Cursando', 'Activo'])
                            ->count()
                        : 0;
                    break;
                case 'debt':
                    $this->recipientCount = DB::table('payments')
                        ->where('status', 'Pendiente')
                        ->distinct('student_id')
                        ->count('student_id');
                    break;
                case 'all':
                    $this->recipientCount = DB::table('students')
                        ->whereNotNull('email')
                        ->count();
                    break;
                default:
                    $this->recipientCount = 0;
            }
        } catch (\Exception $e) {
            $this->recipientCount = 0;
        }
    }

    public function startSending()
    {
        // 1. Validación básica
        $this->validate();
        $this->debugLog = [];

        // 2. Obtención de destinatarios (Lectura única a BD)
        $recipients = $this->getRecipientsEmails();

        if (empty($recipients)) {
            $this->addDebug("⚠️ No hay destinatarios válidos.");
            return;
        }

        // 3. Configuración del Lote en CACHÉ (Forzando driver 'file')
        // Es CRÍTICO usar 'file' aquí si el cache default es 'database' para evitar bloqueos
        $this->batchId = 'batch_' . uniqid();
        $this->totalToSend = count($recipients);
        $this->sentCount = 0;
        $this->progress = 0;

        // Guardar lista completa en caché de archivo por 1 hora
        Cache::store('file')->put($this->batchId, $recipients, 3600);

        $this->isProcessing = true;
        $this->addDebug("🚀 Iniciando envío a {$this->totalToSend} destinatarios.");
    }

    public function processBatch()
    {
        // --- SOLUCIÓN DEFINITIVA PARA SQLITE LOCKED ---
        // Cerramos la escritura de sesión inmediatamente.
        // Esto permite que otras peticiones lean la sesión, pero evita bloqueos de escritura.
        if (session()->isStarted()) {
            session_write_close();
        }

        if (!$this->isProcessing || !$this->batchId) {
            return;
        }

        // Recuperar lista desde caché (File Driver explícito)
        $allRecipients = Cache::store('file')->get($this->batchId);

        if (!$allRecipients) {
            $this->isProcessing = false;
            $this->addDebug("🛑 Error: Lista de envío no encontrada o expirada.");
            return;
        }

        // Procesar un lote MUY pequeño para ser rápidos y liberar el proceso PHP
        // 5 correos es un buen balance para SMTP
        $batchSize = 5;
        $currentBatch = array_slice($allRecipients, $this->sentCount, $batchSize);

        if (empty($currentBatch)) {
            $this->finishProcessing();
            return;
        }

        $sentInBatch = 0;

        foreach ($currentBatch as $email) {
            try {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Envío real
                    Mail::to($email)->send(new CustomSystemMail($this->subject, $this->messageBody));
                    $sentInBatch++;
                }
            } catch (\Exception $e) {
                // Solo loguear en archivo de texto para no tocar la BD SQLite
                Log::channel('daily')->error("Fallo envío a $email: " . $e->getMessage());
            }
        }

        // Actualizar contadores en memoria del componente Livewire
        $this->sentCount += count($currentBatch); // Contamos procesados, no solo exitosos
        
        // Calcular progreso
        if ($this->totalToSend > 0) {
            $this->progress = round(($this->sentCount / $this->totalToSend) * 100);
        } else {
            $this->progress = 100;
        }

        // Feedback visual mínimo para no saturar el payload de Livewire
        if ($this->sentCount % 10 == 0) {
            $this->addDebug("Procesados: {$this->sentCount}/{$this->totalToSend}");
        }

        // Verificar finalización
        if ($this->sentCount >= $this->totalToSend) {
            $this->finishProcessing();
        }
    }

    public function finishProcessing()
    {
        $this->isProcessing = false;
        $this->progress = 100;
        $this->addDebug("✅ Proceso completado. Total procesados: {$this->sentCount}");
        
        // Limpiar caché (File driver)
        Cache::store('file')->forget($this->batchId);
        
        // Resetear formulario
        $this->reset(['subject', 'messageBody']);
        
        // IMPORTANTE: No intentamos escribir flash messages en sesión aquí 
        // porque la sesión está cerrada (session_write_close).
        // El usuario verá el mensaje de debug "Completado".
    }

    private function getRecipientsEmails()
    {
        // Usar DB::table siempre para evitar hidratar modelos Eloquent y reducir overhead
        try {
            switch ($this->audience) {
                case 'individual':
                    return [$this->emailTo];
                case 'section':
                    return DB::table('enrollments')
                        ->join('students', 'enrollments.student_id', '=', 'students.id')
                        ->where('enrollments.course_schedule_id', $this->sectionId)
                        ->whereIn('enrollments.status', ['Cursando', 'Activo'])
                        ->whereNotNull('students.email')
                        ->pluck('students.email')
                        ->toArray();
                case 'debt':
                    return DB::table('payments')
                        ->join('students', 'payments.student_id', '=', 'students.id')
                        ->where('payments.status', 'Pendiente')
                        ->whereNotNull('students.email')
                        ->distinct()
                        ->pluck('students.email')
                        ->toArray();
                case 'all':
                    // Límite de seguridad aumentado a 1000 pero usando chunking interno si fuera necesario
                    return DB::table('students')
                        ->whereNotNull('email')
                        ->limit(1000)
                        ->pluck('email')
                        ->toArray();
                default:
                    return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    private function addDebug($message)
    {
        array_unshift($this->debugLog, "[" . now()->format('H:i:s') . "] " . $message);
        // Limitar el log visual para no hacer pesado el componente
        if (count($this->debugLog) > 50) {
            array_pop($this->debugLog);
        }
    }

    public function render()
    {
        return view('livewire.admin.email-tester');
    }
}