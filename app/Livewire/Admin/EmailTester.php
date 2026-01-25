<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
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
    
    // Datos para la vista
    public $availableSections = [];
    public $recipientCount = 0;
    
    // Log de consola visual
    public $debugLog = [];

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
        // Optimización: Cargar secciones solo con los campos necesarios y eager loading
        // Limitamos a las últimas 100 secciones activas para evitar sobrecarga inicial
        // Si necesitas más, podríamos implementar un buscador dinámico
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
            ->toArray(); // Convertir a array para evitar serialización pesada de modelos
    }

    // Calcular destinatarios cada vez que cambian los filtros
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['audience', 'sectionId', 'emailTo'])) {
            $this->calculateRecipients();
        }
    }

    public function calculateRecipients()
    {
        // Optimización: Usar count() directo en DB en lugar de traer modelos
        switch ($this->audience) {
            case 'individual':
                $this->recipientCount = !empty($this->emailTo) ? 1 : 0;
                break;
            
            case 'section':
                if ($this->sectionId) {
                    $this->recipientCount = \App\Models\Enrollment::where('course_schedule_id', $this->sectionId)
                        ->whereIn('status', ['Cursando', 'Activo'])
                        ->count();
                } else {
                    $this->recipientCount = 0;
                }
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

    public function sendEmail()
    {
        $this->validate();
        $this->debugLog = []; 
        $this->addDebug("Iniciando proceso de envío masivo...");

        $emailsSent = 0;
        $emailsFailed = 0;

        try {
            // 1. Obtener lista de correos (Optimizada para solo traer emails)
            $recipients = $this->getRecipientsEmails();

            if (empty($recipients)) {
                $this->addDebug("⚠️ No se encontraron destinatarios válidos.");
                return;
            }

            $count = count($recipients);
            $this->addDebug("Destinatarios encontrados: " . $count);

            // 2. Iterar y enviar
            foreach ($recipients as $email) {
                try {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue; // Saltar silenciosamente inválidos
                    }

                    // Enviar
                    Mail::to($email)->send(new CustomSystemMail($this->subject, $this->messageBody));
                    $emailsSent++;

                } catch (\Exception $e) {
                    $emailsFailed++;
                    // Solo loguear errores graves para no saturar la consola visual
                    if($emailsFailed <= 5) { 
                        $this->addDebug("❌ Fallo al enviar a ($email): " . $e->getMessage());
                    }
                }
            }

            if ($emailsFailed > 5) {
                $this->addDebug("... y " . ($emailsFailed - 5) . " fallos más.");
            }

            // 3. Resultado final
            if ($emailsSent > 0) {
                $this->addDebug("✅ Proceso finalizado. Enviados: $emailsSent. Fallidos: $emailsFailed.");
                session()->flash('success', "Se enviaron $emailsSent correos correctamente.");
                $this->reset(['subject', 'messageBody']);
            } else {
                $this->addDebug("⚠️ No se pudo enviar ningún correo.");
                session()->flash('error', 'No se enviaron correos. Revise el log.');
            }

        } catch (\Exception $e) {
            $this->addDebug("❌ ERROR CRÍTICO SISTEMA: " . $e->getMessage());
            Log::error("Error Central Correos: " . $e->getMessage());
            session()->flash('error', 'Error crítico en el sistema de envíos.');
        }
    }

    private function getRecipientsEmails()
    {
        // Optimización: Pluck directo desde la base de datos para no hidratar modelos
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
        $this->debugLog[] = "[" . now()->format('H:i:s') . "] " . $message;
    }

    public function render()
    {
        return view('livewire.admin.email-tester');
    }
}