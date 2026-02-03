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
    
    // Estructura: [ 'periodo_X' => [ modulos... ] ]
    public $groupedModules = [];
    
    // Estructura: [ moduleId => scheduleId ]
    public $selectedSchedules = []; 
    
    public $totalCredits = 0;
    public $totalCost = 0;
    
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->student = Auth::user()->student;

        if (!$this->student) {
            abort(403, 'No tienes un perfil de estudiante asociado.');
        }

        // Determinar la carrera del estudiante
        // Prioridad 1: Asignación directa en modelo Student (si existiera)
        // Prioridad 2: Última inscripción válida
        $lastEnrollment = Enrollment::where('student_id', $this->student->id)
            ->with(['courseSchedule.module.course'])
            ->latest()
            ->first();

        if ($lastEnrollment && $lastEnrollment->courseSchedule) {
            $this->career = $lastEnrollment->courseSchedule->module->course;
        }

        // Fallback: Si es nuevo ingreso y no tiene inscripciones, 
        // deberíamos buscar su solicitud aprobada o asignación manual.
        // Por ahora asumimos que si entra aquí ya tiene una carrera vinculada de alguna forma.
        
        if ($this->career) {
            $this->loadAvailableOfferings();
        }
    }

    /**
     * Carga y procesa toda la oferta académica.
     */
    public function loadAvailableOfferings()
    {
        if (!$this->career) return;

        // 1. Materias Aprobadas o Cursando (para filtrar y validar prerrequisitos)
        $approvedIds = Enrollment::where('student_id', $this->student->id)
            ->whereIn('status', ['Aprobado', 'Completado', 'Equivalida', 'Cursando']) 
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->pluck('course_schedules.module_id')
            ->toArray();

        // 2. Cargar Módulos con Horarios Activos
        $modules = Module::where('course_id', $this->career->id)
            ->with(['prerequisites', 'schedules' => function($q) {
                $q->where('status', 'Activo')
                  ->where('start_date', '>=', now()->subDays(60)); // Mostrar horarios recientes/futuros
            }])
            ->orderBy('period_number')
            ->orderBy('order')
            ->get();

        $grouped = [];

        foreach ($modules as $module) {
            // Estado de la materia
            $isApproved = in_array($module->id, $approvedIds);
            
            // Verificar Prerrequisitos
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

            // Agrupar por periodo
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
                'schedules' => $module->schedules, // Colección Eloquent
            ];
        }

        $this->groupedModules = $grouped;
    }

    /**
     * Seleccionar o deseleccionar una sección
     */
    public function toggleSection($moduleId, $scheduleId)
    {
        $this->resetMessages();

        // Caso 1: Deseleccionar (click en la misma que ya tengo)
        if (isset($this->selectedSchedules[$moduleId]) && $this->selectedSchedules[$moduleId] == $scheduleId) {
            unset($this->selectedSchedules[$moduleId]);
            $this->calculateTotals();
            return;
        }

        // Caso 2: Nueva Selección (o cambio de sección)
        $schedule = CourseSchedule::with('module')->find($scheduleId);

        if (!$schedule) return;

        // Validación A: Cupos
        if ($schedule->isFull()) {
            $this->errorMessage = "La sección {$schedule->section_name} de {$schedule->module->name} está llena.";
            return;
        }

        // Validación B: Cruce de Horario
        if ($conflict = $this->checkTimeConflict($schedule)) {
            $this->errorMessage = "Conflicto de horario con {$conflict->module->name} ({$conflict->day_of_week} {$conflict->start_time}-{$conflict->end_time}).";
            return;
        }

        // Si pasa, asignamos
        $this->selectedSchedules[$moduleId] = $scheduleId;
        $this->calculateTotals();
    }

    /**
     * Verifica conflictos de horario con las materias YA seleccionadas.
     * Retorna el horario conflictivo o null.
     */
    private function checkTimeConflict($newSchedule)
    {
        foreach ($this->selectedSchedules as $modId => $selSchedId) {
            // Ignorar la misma materia (porque la estamos reemplazando)
            if ($modId == $newSchedule->module_id) continue;

            $existing = CourseSchedule::find($selSchedId);
            if (!$existing) continue;

            // 1. Intersección de Días
            $daysNew = is_array($newSchedule->days_of_week) ? $newSchedule->days_of_week : [$newSchedule->days_of_week];
            $daysExisting = is_array($existing->days_of_week) ? $existing->days_of_week : [$existing->days_of_week];
            
            $commonDays = array_intersect($daysNew, $daysExisting);

            if (empty($commonDays)) continue; // No coinciden días, no hay choque

            // 2. Solapamiento de Horas
            // Lógica: (StartA < EndB) y (EndA > StartB)
            $startNew = Carbon::parse($newSchedule->start_time);
            $endNew = Carbon::parse($newSchedule->end_time);
            
            $startExist = Carbon::parse($existing->start_time);
            $endExist = Carbon::parse($existing->end_time);

            if ($startNew < $endExist && $endNew > $startExist) {
                return $existing; // Hay conflicto
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
                $this->totalCost += $schedule->module->price; // Sumar costo unitario
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
                // Verificar si ya existe inscripción para evitar duplicados
                $exists = Enrollment::where('student_id', $this->student->id)
                    ->where('course_schedule_id', $schedId)
                    ->exists();

                if (!$exists) {
                    Enrollment::create([
                        'student_id' => $this->student->id,
                        'course_schedule_id' => $schedId,
                        'status' => 'Pendiente', // Estado inicial
                        'final_grade' => null,
                    ]);
                }
            }

            DB::commit();
            
            $this->reset(['selectedSchedules', 'totalCredits', 'totalCost']);
            $this->loadAvailableOfferings(); // Recargar para actualizar estados
            $this->successMessage = "¡Selección procesada correctamente! Tus materias están en estado Pendiente.";

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = "Error al guardar la selección: " . $e->getMessage();
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