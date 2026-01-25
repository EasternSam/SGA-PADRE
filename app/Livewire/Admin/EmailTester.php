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
    public $recipientCount = 0; // Se inicializa en 0 y se actualiza solo bajo demanda
    public $debugLog = [];

    // --- VARIABLES DE PROCESO POR LOTES ---
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
        // 1. Optimización Extrema: NO cargar NADA al inicio.
        // Ni siquiera las secciones. Dejamos que el usuario interactúe primero.
        $this->availableSections = [];
    }

    public function updated($propertyName)
    {
        // Solo cargar secciones si realmente se selecciona la audiencia 'section'
        if ($propertyName === 'audience' && $this->audience === 'section') {
            $this->loadSections();
        }

        // Recalcular destinatarios SOLO si cambian los filtros relevantes
        if (in_array($propertyName, ['audience', 'sectionId', 'emailTo'])) {
            $this->calculateRecipients();
        }
    }

    public function loadSections()
    {
        if (!empty($this->availableSections)) return;

        try {
            // Consulta optimizada usando DB::table para evitar overhead de Eloquent
            $this->availableSections = DB::table('course_schedules')
                ->join('modules', 'course_schedules.module_id', '=', 'modules.id')
                ->join('courses', 'modules.course_id', '=', 'courses.id')
                // Solo secciones que tengan inscripciones activas (optimización clave)
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
                ->limit(50) // Límite duro para evitar bloqueo
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'name' => "{$row->course_name} - {$row->module_name} ({$row->section_name})"
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error("EmailTester: Error cargando secciones: " . $e->getMessage());
        }
    }

    public function calculateRecipients()
    {
        // 4. Optimización de Conteo: Usar consultas directas a DB sin modelos
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
            Log::error("EmailTester: Error contando destinatarios: " . $e->getMessage());
        }
    }

    public function startSending()
    {
        $this->validate();
        $this->debugLog = [];

        Log::info("EmailTester: Iniciando startSending(). Audiencia: " . $this->audience);

        $recipients = $this->getRecipientsEmails();

        if (empty($recipients)) {
            $this->addDebug("⚠️ No hay destinatarios válidos.");
            Log::warning("EmailTester: No se encontraron destinatarios.");
            return;
        }

        $this->batchId = 'email_batch_' . uniqid();
        $this->totalToSend = count($recipients);
        $this->sentCount = 0;
        $this->progress = 0;

        Log::info("EmailTester: Batch ID: {$this->batchId}, Total a enviar: {$this->totalToSend}");

        // Guardamos en caché por 30 mins
        Cache::put($this->batchId, $recipients, 1800);

        $this->isProcessing = true;
        $this->addDebug("🚀 Iniciando envío a {$this->totalToSend} destinatarios.");
        $this->addDebug("⏳ Procesando en segundo plano...");
    }

    public function processBatch()
    {
        // 5. FIX CRÍTICO SQLITE: Liberar sesión inmediatamente.
        session_write_close();

        if (!$this->isProcessing || !$this->batchId) {
            return;
        }

        $allRecipients = Cache::get($this->batchId);

        if (!$allRecipients) {
            $this->stopProcessing("Error: La lista de envío expiró.");
            return;
        }

        // Lote pequeño
        $batchSize = 3;
        $currentBatch = array_slice($allRecipients, $this->sentCount, $batchSize);

        if (empty($currentBatch)) {
            $this->finishProcessing();
            return;
        }

        foreach ($currentBatch as $email) {
            try {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($email)->send(new CustomSystemMail($this->subject, $this->messageBody));
                }
            } catch (\Exception $e) {
                // Logueo solo a archivo, no a la UI para no sobrecargar
                Log::error("EmailTester: Fallo al enviar a {$email}: " . $e->getMessage());
            }
        }

        $this->sentCount += count($currentBatch);
        $this->progress = ($this->totalToSend > 0) ? round(($this->sentCount / $this->totalToSend) * 100) : 100;

        if ($this->sentCount >= $this->totalToSend) {
            $this->finishProcessing();
        }
    }

    public function finishProcessing()
    {
        $this->isProcessing = false;
        $this->progress = 100;
        $this->addDebug("✅ Completado. {$this->sentCount} envíos procesados.");

        Log::info("EmailTester: Proceso finalizado exitosamente.");

        Cache::forget($this->batchId);
        $this->reset(['subject', 'messageBody']);
        
        // No intentamos flashear sesión aquí para evitar bloqueos
    }

    public function stopProcessing($msg = "Proceso detenido.")
    {
        $this->isProcessing = false;
        $this->addDebug("🛑 $msg");
        Log::warning("EmailTester: Proceso detenido: $msg");
    }

    private function getRecipientsEmails()
    {
        // 6. Extracción Optimizada usando Query Builder (DB::table)
        // Esto es mucho más rápido y ligero que Eloquent
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
                    // Límite de seguridad
                    return DB::table('students')
                        ->whereNotNull('email')
                        ->limit(500)
                        ->pluck('email')
                        ->toArray();
                default:
                    return [];
            }
        } catch (\Exception $e) {
            Log::error("EmailTester: Error obteniendo emails: " . $e->getMessage());
            return [];
        }
    }

    private function addDebug($message)
    {
        array_unshift($this->debugLog, "[" . now()->format('H:i:s') . "] " . $message);
    }

    public function render()
    {
        return view('livewire.admin.email-tester');
    }
}