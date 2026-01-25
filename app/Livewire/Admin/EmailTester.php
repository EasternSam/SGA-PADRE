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
        // 1. Optimización: Iniciar vacío.
        // No cargamos nada al entrar para que el renderizado sea instantáneo.
        $this->availableSections = [];
    }

    public function updated($propertyName)
    {
        // 2. Carga bajo demanda solo si es necesario
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
            // 3. Consulta Ultraligera: Seleccionar solo columnas necesarias y limitar resultados.
            // Esto evita hidratar modelos pesados completos.
            $this->availableSections = CourseSchedule::query()
                ->select('id', 'module_id', 'section_name') // Solo lo vital
                ->with(['module:id,course_id,name', 'module.course:id,name']) // Eager loading optimizado
                ->whereHas('enrollments') // Solo secciones con alumnos
                ->latest()
                ->limit(50) // Límite estricto para evitar bloqueo por memoria
                ->get()
                ->map(function($schedule) {
                    $course = $schedule->module->course->name ?? 'Curso';
                    $module = $schedule->module->name ?? 'Módulo';
                    return [
                        'id' => $schedule->id,
                        'name' => "{$course} - {$module} ({$schedule->section_name})"
                    ];
                })
                ->toArray();
        } catch (\Exception $e) { 
            // Fallo silencioso para no romper la UI
        }
    }

    public function calculateRecipients()
    {
        // 4. Optimización de Conteo: Usar consultas directas sin cargar modelos
        switch ($this->audience) {
            case 'individual':
                $this->recipientCount = !empty($this->emailTo) ? 1 : 0;
                break;
            case 'section':
                $this->recipientCount = $this->sectionId 
                    ? \App\Models\Enrollment::where('course_schedule_id', $this->sectionId)
                        ->whereIn('status', ['Cursando', 'Activo'])
                        ->count() 
                    : 0;
                break;
            case 'debt':
                $this->recipientCount = Payment::where('status', 'Pendiente')
                    ->distinct('student_id')
                    ->count('student_id');
                break;
            case 'all':
                $this->recipientCount = Student::whereNotNull('email')->count();
                break;
        }
    }

    public function startSending()
    {
        $this->validate();
        $this->debugLog = [];
        
        $recipients = $this->getRecipientsEmails();

        if (empty($recipients)) {
            $this->addDebug("⚠️ No hay destinatarios válidos.");
            return;
        }

        $this->batchId = 'email_batch_' . uniqid();
        $this->totalToSend = count($recipients);
        $this->sentCount = 0;
        $this->progress = 0;
        
        // Guardamos en caché por 30 mins
        Cache::put($this->batchId, $recipients, 1800);

        $this->isProcessing = true;
        $this->addDebug("🚀 Iniciando envío a {$this->totalToSend} destinatarios.");
    }

    public function processBatch()
    {
        // 5. FIX CRÍTICO SQLITE: Liberar el bloqueo de sesión INMEDIATAMENTE.
        // Esto permite que el resto del sistema (navegación, otras pestañas) funcione
        // mientras este script sigue ejecutándose en el servidor.
        session_write_close();

        if (!$this->isProcessing || !$this->batchId) return;

        $allRecipients = Cache::get($this->batchId);

        if (!$allRecipients) {
            $this->stopProcessing("Error: La lista de envío expiró.");
            return;
        }

        // Procesar lote pequeño (3 emails) para no saturar SMTP ni timeout
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
                // Logueo mínimo
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
        
        Cache::forget($this->batchId);
        $this->reset(['subject', 'messageBody']);
        
        // Requerimos re-abrir sesión solo para flash message final si es necesario, 
        // pero en este contexto basta con limpiar variables.
    }

    public function stopProcessing($msg = "Proceso detenido.")
    {
        $this->isProcessing = false;
        $this->addDebug("🛑 $msg");
    }

    private function getRecipientsEmails()
    {
        // 6. Extracción Optimizada (Pluck) para no cargar memoria
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
                // Límite de seguridad para "Todos" en hosting compartido
                return Student::whereNotNull('email')->take(500)->pluck('email')->toArray();
            default:
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