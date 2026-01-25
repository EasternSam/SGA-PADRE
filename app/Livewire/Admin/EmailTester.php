<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Mail\CustomSystemMail;
use App\Models\CourseSchedule; // Para las secciones
use App\Models\Payment;        // Para deudores
use App\Models\Student;        // Para todos los estudiantes
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class EmailTester extends Component
{
    // Campos del Formulario
    public $audience = 'individual'; // individual, section, debt, all
    public $emailTo;                 // Solo para individual
    public $sectionId;               // Solo para audiencia 'section'
    
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
        // Cargar secciones activas para el dropdown (con estudiantes inscritos)
        $this->availableSections = CourseSchedule::with(['module.course'])
            ->whereHas('enrollments') // Solo secciones con alumnos
            ->get()
            ->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'name' => ($schedule->module->course->name ?? 'Curso') . ' - ' . ($schedule->module->name ?? 'Módulo') . ' (' . $schedule->section_name . ')'
                ];
            });
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
                // Contar estudiantes únicos con al menos un pago pendiente
                $this->recipientCount = Payment::where('status', 'Pendiente')
                    ->distinct('student_id')
                    ->count('student_id');
                break;

            case 'all':
                $this->recipientCount = Student::whereHas('user')->count();
                break;
        }
    }

    public function sendEmail()
    {
        $this->validate();
        $this->debugLog = []; // Limpiar log anterior
        $this->addDebug("Iniciando proceso de envío masivo...");

        $emailsSent = 0;
        $emailsFailed = 0;

        try {
            // 1. Obtener lista de correos según la audiencia
            $recipients = $this->getRecipientsList();

            if ($recipients->isEmpty()) {
                $this->addDebug("⚠️ No se encontraron destinatarios válidos para esta selección.");
                return;
            }

            $this->addDebug("Destinatarios encontrados: " . $recipients->count());

            // 2. Iterar y enviar
            foreach ($recipients as $recipient) {
                try {
                    // Verificar si el destinatario es un objeto (Student) o un string (email directo)
                    $email = is_string($recipient) ? $recipient : $recipient->email;
                    $name = is_string($recipient) ? 'Usuario' : $recipient->first_name;

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->addDebug("⏩ Saltando correo inválido: $email");
                        continue;
                    }

                    // Enviar
                    Mail::to($email)->send(new CustomSystemMail($this->subject, $this->messageBody));
                    $emailsSent++;

                } catch (\Exception $e) {
                    $emailsFailed++;
                    $this->addDebug("❌ Fallo al enviar a ($email): " . $e->getMessage());
                }
            }

            // 3. Resultado final
            if ($emailsSent > 0) {
                $this->addDebug("✅ Proceso finalizado. Enviados: $emailsSent. Fallidos: $emailsFailed.");
                session()->flash('success', "Se enviaron $emailsSent correos correctamente.");
                
                // Limpiar formulario
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

    private function getRecipientsList()
    {
        switch ($this->audience) {
            case 'individual':
                return collect([$this->emailTo]);
            
            case 'section':
                return \App\Models\Enrollment::where('course_schedule_id', $this->sectionId)
                    ->whereIn('status', ['Cursando', 'Activo'])
                    ->with('student')
                    ->get()
                    ->pluck('student') // Extraer modelos Student
                    ->filter(fn($s) => !empty($s->email)); // Filtrar sin email

            case 'debt':
                // Obtener estudiantes que tienen pagos pendientes
                return Payment::where('status', 'Pendiente')
                    ->with('student')
                    ->get()
                    ->pluck('student')
                    ->unique('id') // Evitar duplicados si debe varios meses
                    ->filter(fn($s) => !empty($s->email));

            case 'all':
                return Student::whereNotNull('email')->get();

            default:
                return collect();
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