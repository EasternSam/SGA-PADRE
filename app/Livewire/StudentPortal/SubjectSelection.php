<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Admission;
use App\Models\Payment; // Importante
use App\Models\PaymentConcept; // Importante
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
                $this->debugMessage = "Carrera detectada y vinculada: {$this->career->name}";
            }
        }

        if (!$this->career) {
            $lastEnrollment = Enrollment::where('student_id', $this->student->id)
                ->with(['courseSchedule.module.course'])
                ->latest()->first();

            if ($lastEnrollment && $lastEnrollment->courseSchedule) {
                $this->career = $lastEnrollment->courseSchedule->module->course;
                $this->student->update(['course_id' => $this->career->id]);
            }
        }

        if ($this->career) {
            $this->loadAvailableOfferings();
        } else {
            $this->debugMessage = "⚠️ No se pudo determinar tu carrera. Por favor contacta a Registro.";
        }
    }

    public function loadAvailableOfferings()
    {
        // CORRECCIÓN: 'enrollments.status' para evitar ambigüedad
        $approvedIds = Enrollment::where('student_id', $this->student->id)
            ->whereIn('enrollments.status', ['Aprobado', 'Completado', 'Equivalida', 'Cursando']) 
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->pluck('course_schedules.module_id')
            ->toArray();

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
            
            $missingPrereqs = [];
            if (!$isApproved) {
                foreach ($module->prerequisites as $prereq) {
                    if (!in_array($prereq->id, $approvedIds)) {
                        $missingPrereqs[] = $prereq->name;
                    }
                }
            }

            $status = 'disponible';
            if ($isApproved) $status = 'aprobada';
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

        if (isset($this->selectedSchedules[$moduleId]) && $this->selectedSchedules[$moduleId] == $scheduleId) {
            unset($this->selectedSchedules[$moduleId]);
            $this->calculateTotals();
            return;
        }

        $schedule = CourseSchedule::with('module')->find($scheduleId);
        if (!$schedule) return;

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
        foreach ($this->selectedSchedules as $modId => $selSchedId) {
            if ($modId == $newSchedule->module_id) continue;

            $existing = CourseSchedule::find($selSchedId);
            if (!$existing) continue;

            $daysNew = is_array($newSchedule->days_of_week) ? $newSchedule->days_of_week : [$newSchedule->days_of_week];
            $daysExisting = is_array($existing->days_of_week) ? $existing->days_of_week : [$existing->days_of_week];
            
            $commonDays = array_intersect($daysNew, $daysExisting);

            if (empty($commonDays)) continue;

            $startNew = Carbon::parse($newSchedule->start_time);
            $endNew = Carbon::parse($newSchedule->end_time);
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

        foreach ($this->selectedSchedules as $modId => $schedId) {
            $schedule = CourseSchedule::with('module')->find($schedId);
            if ($schedule) {
                $this->totalCredits += $schedule->module->credits;
                $this->totalCost += $schedule->module->price;
            }
        }
    }

    public function confirmSelection()
    {
        if (empty($this->selectedSchedules)) {
            $this->errorMessage = "No has seleccionado ninguna materia.";
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Crear concepto si no existe (para reinscripción/selección)
            $concept = PaymentConcept::firstOrCreate(
                ['name' => 'Selección de Materias'],
                ['price' => 0, 'is_tuition' => false]
            );

            // 2. Crear LA DEUDA UNIFICADA (Pago Pendiente)
            $payment = Payment::create([
                'user_id' => $this->student->user_id,
                'student_id' => $this->student->id,
                'payment_concept_id' => $concept->id,
                'amount' => $this->totalCost,
                'status' => 'Pendiente', // Pendiente de pago
                'notes' => 'Selección de materias Ciclo Actual. Total materias: ' . count($this->selectedSchedules),
                'due_date' => Carbon::now()->addDays(7),
            ]);

            // 3. Crear las inscripciones vinculadas a ese pago
            foreach ($this->selectedSchedules as $modId => $schedId) {
                // Verificar duplicados
                $exists = Enrollment::where('student_id', $this->student->id)
                    ->where('course_schedule_id', $schedId)
                    ->exists();

                if (!$exists) {
                    Enrollment::create([
                        'student_id' => $this->student->id,
                        'course_schedule_id' => $schedId,
                        'payment_id' => $payment->id, // <-- Vinculación clave
                        'status' => 'Pendiente', 
                        'final_grade' => null,
                    ]);
                }
            }

            DB::commit();
            
            $this->reset(['selectedSchedules', 'totalCredits', 'totalCost']);
            $this->loadAvailableOfferings();
            
            return redirect()->route('student.payments')->with('message', 'Selección confirmada. Se ha generado una deuda única por el total.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = "Error: " . $e->getMessage();
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