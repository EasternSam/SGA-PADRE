<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
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
    public $debugMessage = ''; // Para ayudarte a entender qué pasa

    public function mount()
    {
        $this->student = Auth::user()->student;

        if (!$this->student) {
            abort(403, 'No tienes un perfil de estudiante asociado.');
        }

        // --- DETECCIÓN DE CARRERA ---
        
        // 1. Intento Directo: Ver si el estudiante tiene la carrera en su tabla
        // (Asegúrate de que el modelo Student tenga la relación 'course')
        $this->career = $this->student->course;

        // 2. Intento Histórico: Si no tiene, buscar en su última inscripción
        if (!$this->career) {
            $lastEnrollment = Enrollment::where('student_id', $this->student->id)
                ->with(['courseSchedule.module.course'])
                ->latest()
                ->first();

            if ($lastEnrollment && $lastEnrollment->courseSchedule) {
                $this->career = $lastEnrollment->courseSchedule->module->course;
            }
        }

        if ($this->career) {
            $this->loadAvailableOfferings();
        } else {
            $this->debugMessage = "No se detectó ninguna carrera asociada al estudiante (ID: {$this->student->id}). Verifica que el campo 'course_id' en la tabla 'students' tenga valor.";
        }
    }

    public function loadAvailableOfferings()
    {
        // 1. Materias ya aprobadas o cursando
        $approvedIds = Enrollment::where('student_id', $this->student->id)
            ->whereIn('status', ['Aprobado', 'Completado', 'Equivalida', 'Cursando']) 
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->pluck('course_schedules.module_id')
            ->toArray();

        // 2. Cargar Módulos y Horarios
        // CAMBIO: Ampliamos el rango de fechas a 6 meses para que veas materias iniciadas en pruebas
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
            // Estado
            $isApproved = in_array($module->id, $approvedIds);
            
            // Prerrequisitos
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

            // Agrupar
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
            ];
        }

        $this->groupedModules = $grouped;
        
        if (empty($this->groupedModules)) {
            $this->debugMessage = "Se encontró la carrera '{$this->career->name}', pero no tiene materias (módulos) registrados o activos.";
        }
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
            foreach ($this->selectedSchedules as $modId => $schedId) {
                $exists = Enrollment::where('student_id', $this->student->id)
                    ->where('course_schedule_id', $schedId)
                    ->exists();

                if (!$exists) {
                    Enrollment::create([
                        'student_id' => $this->student->id,
                        'course_schedule_id' => $schedId,
                        'status' => 'Pendiente',
                        'final_grade' => null,
                    ]);
                }
            }

            DB::commit();
            
            $this->reset(['selectedSchedules', 'totalCredits', 'totalCost']);
            $this->loadAvailableOfferings();
            $this->successMessage = "¡Selección procesada correctamente!";

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