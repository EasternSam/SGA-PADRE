<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

    // --- VARIABLES DE PROCESO POR LOTES ---
    public $isProcessing = false;     // ¿Está enviando?
    public $batchId = null;           // ID único del proceso actual
    public $totalToSend = 0;          // Total a enviar
    public $sentCount = 0;            // Enviados hasta ahora
    public $progress = 0;             // Porcentaje (0-100)

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
        $this->availableSections = [];
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
            $this->availableSections = CourseSchedule::with(['module.course'])
                ->whereHas('enrollments') 
                ->latest()
                ->take(100) 
                ->get()
                ->map(function($schedule) {
                    return [
                        'id' => $schedule->id,
                        'name' => ($schedule->module->course->name ?? 'Curso') . ' - ' . ($schedule->module->name ?? 'Módulo') . ' (' . $schedule->section_name . ')'
                    ];
                })
                ->toArray();
        } catch (\Exception $e) { }
    }

    public function calculateRecipients()
    {
        switch ($this->audience) {
            case 'individual':
                $this->recipientCount = !empty($this->emailTo) ? 1 : 0;
                break;
            case 'section':
                $this->recipientCount = $this->sectionId ? \App\Models\Enrollment::where('course_schedule_id', $this->sectionId)->whereIn('status', ['Cursando', 'Activo'])->count() : 0;
                break;
            case 'debt':
                $this->recipientCount = Payment::where('status', 'Pendiente')->distinct('student_id')->count('student_id');
                break;
            case 'all':
                $this->recipientCount = Student::whereNotNull('email')->count();
                break;
        }
    }

    /**
     * Paso 1: Iniciar el proceso.
     * Calcula los destinatarios y guarda la lista en Caché para procesarla poco a poco.
     */
    public function startSending()
    {
        $this->validate();
        $this->debugLog = [];
        
        // 1. Obtener correos
        $recipients = $this->getRecipientsEmails();

        if (empty($recipients)) {
            $this->addDebug("⚠️ No hay destinatarios válidos.");
            return;
        }

        // 2. Configurar lote
        $this->batchId = 'email_batch_' . uniqid();
        $this->totalToSend = count($recipients);
        $this->sentCount = 0;
        $this->progress = 0;
        
        // Guardamos la lista en caché por 30 mins para no sobrecargar la memoria del componente
        Cache::put($this->batchId, $recipients, 1800);

        $this->isProcessing = true;
        $this->addDebug("🚀 Iniciando envío a {$this->totalToSend} destinatarios.");
        $this->addDebug("⏳ Procesando en segundo plano...");
    }

    /**
     * Paso 2: Procesar un pequeño lote (Ej: 5 correos).
     * Esto se llama repetidamente desde la vista con wire:poll mientras isProcessing es true.
     */
    public function processBatch()
    {
        if (!$this->isProcessing || !$this->batchId) return;

        // Recuperar lista completa
        $allRecipients = Cache::get($this->batchId);

        if (!$allRecipients) {
            $this->stopProcessing("Error: La lista de envío expiró.");
            return;
        }

        // Tomar el siguiente lote (slicing)
        $batchSize = 3; // Enviamos de 3 en 3 para ser muy suaves con el servidor
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
                // Fallo silencioso en el log visual para no saturar, o agregar si es crítico
            }
        }

        // Actualizar contadores
        $this->sentCount += count($currentBatch);
        $this->progress = ($this->totalToSend > 0) ? round(($this->sentCount / $this->totalToSend) * 100) : 100;

        // Verificar si terminamos
        if ($this->sentCount >= $this->totalToSend) {
            $this->finishProcessing();
        }
    }

    public function finishProcessing()
    {
        $this->isProcessing = false;
        $this->progress = 100;
        $this->addDebug("✅ ¡Proceso completado! Se procesaron {$this->sentCount} envíos.");
        session()->flash('success', "Envío masivo finalizado correctamente.");
        Cache::forget($this->batchId); // Limpiar memoria
        $this->reset(['subject', 'messageBody']);
    }

    public function stopProcessing($msg = "Proceso detenido.")
    {
        $this->isProcessing = false;
        $this->addDebug("🛑 $msg");
        session()->flash('error', $msg);
    }

    private function getRecipientsEmails()
    {
        switch ($this->audience) {
            case 'individual':
                return [$this->emailTo];
            case 'section':
                return \App\Models\Enrollment::where('course_schedule_id', $this->sectionId)
                    ->whereIn('status', ['Cursando', 'Activo'])
                    ->join('students', 'enrollments.student_id', '=', 'students.id')
                    ->whereNotNull('students.email')
                    ->pluck('students.email')
                    ->toArray();
            case 'debt':
                return Payment::where('status', 'Pendiente')
                    ->join('students', 'payments.student_id', '=', 'students.id')
                    ->whereNotNull('students.email')
                    ->distinct()
                    ->pluck('students.email')
                    ->toArray();
            case 'all':
                return Student::whereNotNull('email')->pluck('email')->toArray();
            default:
                return [];
        }
    }

    private function addDebug($message)
    {
        // Agregamos al inicio para ver lo más reciente arriba
        array_unshift($this->debugLog, "[" . now()->format('H:i:s') . "] " . $message);
    }

    public function render()
    {
        return view('livewire.admin.email-tester');
    }
}