<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Jobs\SendBulkEmailJob;

#[Layout('layouts.dashboard')]
class EmailTester extends Component
{
    // Audiencia
    public $audienceType = ''; // individual, section, course, debtors, all
    public $targetId = '';
    
    // Búsqueda Individual
    public $individualSearch = '';
    public $individualResults = [];
    public $selectedIndividualId = '';
    public $selectedIndividualEmail = '';
    public $selectedIndividualName = '';

    // Formulario de Envío
    public $subject = '';
    public $messageBody = '';
    
    // Diagnóstico
    public $debugLog = [];

    // Colecciones para Selects
    public $availableCourses = [];
    public $availableSections = [];

    public function mount()
    {
        $this->availableCourses = Course::where('status', 'Activo')->get();
        $this->availableSections = CourseSchedule::where('status', 'Activa')->with('module.course')->get();
    }

    public function updatedAudienceType()
    {
        // Limpiamos selecciones previas cuando cambian de pestaña
        $this->targetId = '';
        $this->individualSearch = '';
        $this->individualResults = [];
        $this->selectedIndividualId = '';
    }

    public function searchStudents()
    {
        if (strlen($this->individualSearch) < 3) {
            $this->individualResults = [];
            return;
        }

        $this->individualResults = Student::where('status', 'Activo')
            ->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->individualSearch . '%')
                  ->orWhere('last_name', 'like', '%' . $this->individualSearch . '%')
                  ->orWhere('cedula', 'like', '%' . $this->individualSearch . '%')
                  ->orWhere('student_code', 'like', '%' . $this->individualSearch . '%');
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'student_code'])
            ->toArray();
    }

    public function selectStudent($studentId, $email, $name)
    {
        $this->selectedIndividualId = $studentId;
        $this->selectedIndividualEmail = $email;
        $this->selectedIndividualName = $name;
        $this->individualResults = [];
        $this->individualSearch = $name; // Para que el usuario vea a quién seleccionó
    }

    public function loadTemplate($type)
    {
        switch ($type) {
            case 'cambio_aula':
                $this->subject = 'Aviso Importante: Cambio de Aula/Horario';
                $this->messageBody = "Hola [NOMBRE_ESTUDIANTE],\n\nTe escribimos para notificarte un cambio importante en tu curso [NOMBRE_CURSO].\n\nPor favor, comunícate con la administración o revisa tu portal para conocer los detalles del nuevo horario o ubicación.\n\nAtentamente,\nAdministración.";
                break;
            case 'recordatorio_pago':
                $this->subject = 'Recordatorio de Pago Vencido';
                $this->messageBody = "Hola [NOMBRE_ESTUDIANTE],\n\nEsperamos que te encuentres bien. Te recordamos que tienes un balance pendiente en tu cuenta.\n\nPara evitar recargos o suspensiones, por favor realiza tu pago a través de nuestro portal web o en caja lo antes posible.\n\nSi ya realizaste tu pago, por favor ignora este mensaje.\n\nAtentamente,\nDepartamento de Finanzas.";
                break;
            case 'aviso_general':
                $this->subject = 'Aviso Académico';
                $this->messageBody = "Hola [NOMBRE_ESTUDIANTE],\n\n...";
                break;
        }
    }

    public function sendEmail()
    {
        $this->validate([
            'audienceType' => 'required',
            'subject' => 'required|string|min:3',
            'messageBody' => 'required|string|min:10',
        ]);

        $this->debugLog = [];
        $this->addDebug("--- INICIANDO PROTOCOLO COMUNICACIÓN AVANZADA ---");
        $this->addDebug("Audiencia Seleccionada: " . strtoupper($this->audienceType));

        $recipients = [];

        try {
            switch ($this->audienceType) {
                case 'individual':
                    if (empty($this->selectedIndividualEmail)) {
                        throw new \Exception("Debe seleccionar un estudiante válido.");
                    }
                    $recipients[] = [
                        'email' => $this->selectedIndividualEmail,
                        'name' => $this->selectedIndividualName,
                        'course' => 'Curso Actual'
                    ];
                    break;

                case 'section':
                    if (empty($this->targetId)) throw new \Exception("Debe seleccionar una sección.");
                    
                    $enrollments = Enrollment::where('course_schedule_id', $this->targetId)
                        ->whereIn('status', ['Activo', 'Cursando'])
                        ->with('student', 'courseSchedule.module.course')
                        ->get();
                        
                    foreach ($enrollments as $enrollment) {
                        if ($enrollment->student && $enrollment->student->email) {
                            $recipients[] = [
                                'email' => $enrollment->student->email,
                                'name' => $enrollment->student->first_name,
                                'course' => $enrollment->courseSchedule->module->course->name ?? 'Módulo'
                            ];
                        }
                    }
                    break;

                case 'course':
                    if (empty($this->targetId)) throw new \Exception("Debe seleccionar un curso.");
                    
                    $enrollments = Enrollment::where('course_id', $this->targetId)
                        ->whereIn('status', ['Activo', 'Cursando'])
                        ->with('student', 'course')
                        ->get();
                        
                    foreach ($enrollments as $enrollment) {
                        if ($enrollment->student && $enrollment->student->email) {
                            $recipients[] = [
                                'email' => $enrollment->student->email,
                                'name' => $enrollment->student->first_name,
                                'course' => $enrollment->course->name ?? 'Curso'
                            ];
                        }
                    }
                    // Quitar duplicados por si un estudiante cursa 2 módulos
                    $recipients = collect($recipients)->unique('email')->values()->toArray();
                    break;

                case 'debtors':
                    // Buscar facturas vencidas (due_date < today) en estado Pendiente
                    $debts = Payment::whereIn('status', ['Pendiente', 'pendiente'])
                        ->whereDate('due_date', '<', now())
                        ->with(['student.enrollments' => function($q) {
                            $q->whereIn('status', ['Activo', 'Cursando'])->with('course');
                        }])
                        ->get();
                        
                    foreach ($debts as $debt) {
                        if ($debt->student && $debt->student->email) {
                            $courseName = $debt->student->enrollments->first()->course->name ?? 'Sus Cursos';
                            $recipients[] = [
                                'email' => $debt->student->email,
                                'name' => $debt->student->first_name,
                                'course' => $courseName,
                                'balance' => $debt->amount // Por si en el futuro usamos suma total
                            ];
                        }
                    }
                    $recipients = collect($recipients)->unique('email')->values()->toArray();
                    break;
                    
                case 'all':
                    $students = Student::where('status', 'Activo')
                        ->whereNotNull('email')
                        ->get(['email', 'first_name']);
                    
                    foreach ($students as $student) {
                        $recipients[] = [
                            'email' => $student->email,
                            'name' => $student->first_name,
                            'course' => 'SGA Centu'
                        ];
                    }
                    break;
            }

            $count = count($recipients);
            if ($count === 0) {
                throw new \Exception("No se encontraron destinatarios válidos para esta audiencia.");
            }

            $this->addDebug("Se resolvieron {$count} destinatarios válidos.");
            $this->addDebug("Enviando trabajo a la cola (Background Job)...");

            // Lanzar el Trabajo en Segundo Plano
            dispatch(new SendBulkEmailJob($recipients, $this->subject, $this->messageBody));

            $this->addDebug("✅ Correos encolados exitosamente.");
            session()->flash('success', "Proceso iniciado. {$count} correos se están enviando en segundo plano.");
            
            // Opcional: Limpiar todo
            // $this->reset(['subject', 'messageBody', 'audienceType', 'targetId', 'individualSearch']);

        } catch (\Exception $e) {
            $this->addDebug("❌ ERROR: " . $e->getMessage());
            Log::error("Error Panel Comunicacion: " . $e->getMessage());
            session()->flash('error', $e->getMessage());
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