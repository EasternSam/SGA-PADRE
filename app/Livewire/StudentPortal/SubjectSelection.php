<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Admission;
use App\Models\Payment; 
use App\Models\PaymentConcept; 
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubjectSelection extends Component
{
    public $student;
    public $career;
    
    public $groupedModules = [];
    public $selectedSchedules = []; 
    
    public $totalCredits = 0;
    public $totalCost = 0;
    
    public $successMessage = '';
    public $errorMessage = '';
    public $debugMessage = '';

    public function mount()
    {
        $this->student = Auth::user()->student;

        if (!$this->student) {
            abort(403, 'No tienes un perfil de estudiante asociado.');
        }

        // --- DETECCIÓN DE CARRERA INTELIGENTE ---
        $this->career = $this->student->course;

        if (!$this->career && $this->student->user_id) {
            $admission = Admission::where('user_id', $this->student->user_id)->latest()->first();
            if ($admission && $admission->course) {
                $this->career = $admission->course;
                $this->student->update(['course_id' => $admission->course_id]);
            }
        }

        // Fallback: Buscar en historial académico
        if (!$this->career) {
            $lastEnrollment = Enrollment::where('student_id', $this->student->id)
                ->with(['courseSchedule.module.course'])
                ->latest()
                ->first();

            if ($lastEnrollment && $lastEnrollment->courseSchedule) {
                $this->career = $lastEnrollment->courseSchedule->module->course;
                $this->student->update(['course_id' => $this->career->id]);
            }
        }

        if ($this->career) {
            $this->loadAvailableOfferings();
        } else {
            $this->debugMessage = "⚠️ No se pudo determinar tu carrera/curso. Por favor contacta a Registro.";
        }
    }

    public function loadAvailableOfferings()
    {
        // 1. Materias APROBADAS (Historial real)
        $approvedIds = Enrollment::where('student_id', $this->student->id)
            ->whereIn('enrollments.status', ['Aprobado', 'Completado', 'Equivalida']) 
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->pluck('course_schedules.module_id')
            ->toArray();

        // 2. Materias que ya estoy CURSANDO o PREMATRICULADO (Para no mostrarlas disponibles)
        $currentIds = Enrollment::where('student_id', $this->student->id)
            ->whereIn('enrollments.status', ['Cursando', 'Pendiente'])
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->pluck('course_schedules.module_id')
            ->toArray();

        // 3. Cargar Módulos y Horarios
        $modules = Module::where('course_id', $this->career->id)
            ->with(['prerequisites', 'schedules' => function($q) {
                $q->where('status', 'Activo')
                  ->where('start_date', '>=', now()->subMonths(6)); 
            }])
            ->orderBy('period_number')
            ->orderBy('order')
            ->get();

        $grouped = [];

        foreach ($modules as $module) {
            $isApproved = in_array($module->id, $approvedIds);
            $isTaking = in_array($module->id, $currentIds);
            
            $missingPrereqs = [];
            
            // Solo validamos prerrequisitos si no la ha aprobado ni la está tomando
            if (!$isApproved && !$isTaking) {
                foreach ($module->prerequisites as $prereq) {
                    if (!in_array($prereq->id, $approvedIds)) {
                        $missingPrereqs[] = $prereq->name;
                    }
                }
            }

            // Determinar estado visual
            $status = 'disponible';
            if ($isApproved) $status = 'aprobada';
            elseif ($isTaking) $status = 'cursando';
            elseif (!empty($missingPrereqs)) $status = 'bloqueada';

            $period = $module->period_number ?? 0;
            if (!isset($grouped[$period])) {
                $grouped[$period] = [];
            }

            $grouped[$period][] = [
                'id' => $module->id,
                'code' => $module->code,
                'name' => $module->name,
                'credits' => $module->credits,
                'status' => $status,
                'missing_prereqs' => $missingPrereqs,
                'schedules' => $module->schedules,
                'price' => $module->price,
            ];
        }

        $this->groupedModules = $grouped;
    }

    public function toggleSection($moduleId, $scheduleId)
    {
        $this->resetMessages();

        // Deseleccionar si ya estaba seleccionado
        if (isset($this->selectedSchedules[$moduleId]) && $this->selectedSchedules[$moduleId] == $scheduleId) {
            unset($this->selectedSchedules[$moduleId]);
            $this->calculateTotals();
            return;
        }

        $schedule = CourseSchedule::with('module')->find($scheduleId);
        if (!$schedule) return;

        // Validación visual de cupo
        if ($schedule->isFull()) {
            $this->errorMessage = "La sección {$schedule->section_name} está llena.";
            return;
        }

        if ($conflict = $this->checkTimeConflict($schedule)) {
            $this->errorMessage = "Conflicto de horario con {$conflict->module->name}.";
            return;
        }

        $this->selectedSchedules[$moduleId] = $scheduleId;
        $this->calculateTotals();
    }

    private function checkTimeConflict($newSchedule)
    {
        $selectedIds = array_values($this->selectedSchedules);
        $existingSchedules = CourseSchedule::whereIn('id', $selectedIds)
                                           ->where('module_id', '!=', $newSchedule->module_id)
                                           ->get();

        $daysNew = is_array($newSchedule->days_of_week) ? $newSchedule->days_of_week : [$newSchedule->days_of_week];
        $startNew = Carbon::parse($newSchedule->start_time);
        $endNew = Carbon::parse($newSchedule->end_time);

        foreach ($existingSchedules as $existing) {
            $daysExisting = is_array($existing->days_of_week) ? $existing->days_of_week : [$existing->days_of_week];
            $commonDays = array_intersect($daysNew, $daysExisting);

            if (empty($commonDays)) continue;

            $startExist = Carbon::parse($existing->start_time);
            $endExist = Carbon::parse($existing->end_time);

            if ($startNew < $endExist && $endNew > $startExist) {
                return $existing;
            }
        }
        return null;
    }

    public function calculateTotals()
    {
        $this->totalCredits = 0;
        $this->totalCost = 0;
        $costoPorCredito = $this->career->credit_price ?? 0;

        $schedules = CourseSchedule::with('module')->findMany(array_values($this->selectedSchedules));

        foreach ($schedules as $schedule) {
            $this->totalCredits += $schedule->module->credits;
            
            if ($schedule->module->price > 0) {
                $this->totalCost += $schedule->module->price;
            } else {
                $this->totalCost += ($schedule->module->credits * $costoPorCredito);
            }
        }
    }

    public function confirmSelection()
    {
        if (empty($this->selectedSchedules)) {
            $this->errorMessage = "No has seleccionado ninguna materia.";
            return;
        }

        try {
            DB::transaction(function () {
                // 1. Crear concepto de deuda (Cuenta por cobrar)
                $concept = PaymentConcept::firstOrCreate(
                    ['name' => 'Colegiatura Ciclo Actual'],
                    ['amount' => 0] 
                );

                // 2. Crear DEUDA (Estado Pendiente)
                // Esto genera el balance en la cuenta del estudiante, pero no bloquea su acceso académico.
                $payment = Payment::create([
                    'user_id' => $this->student->user_id,
                    'student_id' => $this->student->id,
                    'payment_concept_id' => $concept->id,
                    'amount' => $this->totalCost,
                    'status' => 'Pendiente', 
                    'gateway' => 'Sistema', 
                    'notes' => 'Selección de materias. Total asignaturas: ' . count($this->selectedSchedules),
                    'due_date' => Carbon::now()->addDays(30), // Fecha límite de pago (ej. 30 días o fin de mes)
                ]);

                // 3. Crear INSCRIPCIONES ACTIVAS
                foreach ($this->selectedSchedules as $modId => $schedId) {
                    
                    // A. BLOQUEO DE FILA: Mantenemos esto para evitar sobrecupo físico del aula
                    $schedule = CourseSchedule::lockForUpdate()->find($schedId);

                    if ($schedule->isFull()) {
                        throw new \Exception("La sección {$schedule->section_name} de {$schedule->module->name} se llenó justo ahora.");
                    }

                    // B. Verificar duplicados
                    $exists = Enrollment::where('student_id', $this->student->id)
                        ->where('course_schedule_id', $schedId)
                        ->exists();

                    if (!$exists) {
                        Enrollment::create([
                            'student_id' => $this->student->id,
                            'course_schedule_id' => $schedId,
                            'payment_id' => $payment->id, 
                            'status' => 'Cursando', // <-- ESTADO ACTIVO: Estudiante entra a clases de inmediato
                            'final_grade' => null,
                            'enrollment_date' => now(),
                        ]);
                    }
                }
            });
            
            $this->reset(['selectedSchedules', 'totalCredits', 'totalCost']);
            
            // Redirigimos al Dashboard porque ya tienen sus materias cargadas
            return redirect()->route('student.dashboard')->with('message', '¡Inscripción exitosa! Tu carga académica ha sido actualizada. Recuerda revisar tu estado de cuenta.');

        } catch (\Exception $e) {
            $this->errorMessage = "No se pudo procesar la inscripción: " . $e->getMessage();
            Log::error("Error en selección de materias: " . $e->getMessage());
        }
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.student-portal.subject-selection', [
            'groupedModules' => $this->groupedModules
        ])->layout('layouts.dashboard');
    }
}