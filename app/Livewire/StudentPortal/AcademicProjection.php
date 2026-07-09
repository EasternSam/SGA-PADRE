<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Module;
use App\Models\Enrollment;
use App\Models\DegreePlan;
use App\Models\PlannedModule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcademicProjection extends Component
{
    public $student;
    public $career;
    public $degreePlan;
    public $pace = 5;

    public $approvedModuleIds = [];
    public $currentModuleIds = [];
    public $plannedModulesGrouped = []; // target_period => [planned_modules]
    public $modulesByPeriod = [];       // period_number => [modules]
    
    // Progress metrics
    public $totalCredits = 0;
    public $completedCredits = 0;
    public $inProgressCredits = 0;
    public $plannedCredits = 0;
    public $progressPercentage = 0;

    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->student = Auth::user()->student;

        if (!$this->student) {
            abort(403, 'No tienes un perfil de estudiante asociado.');
        }

        $this->career = $this->student->course;

        if (!$this->career) {
            $this->errorMessage = '⚠️ No se pudo determinar tu carrera/curso. Por favor contacta a Registro.';
            return;
        }

        // Cargar o crear el plan de carrera
        $this->degreePlan = DegreePlan::firstOrCreate(
            ['student_id' => $this->student->id],
            ['pace' => '5', 'status' => 'active']
        );

        $this->pace = (int) $this->degreePlan->pace;

        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->career) return;

        // 1. Materias aprobadas/equivalidas
        $this->approvedModuleIds = Enrollment::where('student_id', $this->student->id)
            ->whereIn('enrollments.status', ['Aprobado', 'Completado', 'Equivalida'])
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->pluck('course_schedules.module_id')
            ->toArray();

        // 2. Materias cursando o prematriculadas
        $this->currentModuleIds = Enrollment::where('student_id', $this->student->id)
            ->whereIn('enrollments.status', ['Cursando', 'Pendiente'])
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->pluck('course_schedules.module_id')
            ->toArray();

        // 3. Cargar materias planificadas
        $planned = PlannedModule::where('degree_plan_id', $this->degreePlan->id)
            ->with('module')
            ->get();

        $this->plannedModulesGrouped = [];
        foreach ($planned as $pm) {
            $this->plannedModulesGrouped[$pm->target_period][] = $pm;
        }

        // 4. Cargar todos los módulos del pensum (carrera) con sus prerrequisitos
        $modules = Module::where('course_id', $this->career->id)
            ->with('prerequisites')
            ->orderBy('period_number')
            ->orderBy('order')
            ->get();

        // Agrupar por periodos del pensum e identificar estados
        $this->modulesByPeriod = [];
        $this->totalCredits = 0;
        $this->completedCredits = 0;
        $this->inProgressCredits = 0;
        $this->plannedCredits = 0;

        foreach ($modules as $module) {
            $this->totalCredits += $module->credits;

            // Determinar estado
            $status = 'bloqueada';
            $missingPrereqs = [];

            $isApproved = in_array($module->id, $this->approvedModuleIds);
            $isCurrent = in_array($module->id, $this->currentModuleIds);
            
            // Buscar si está planificada
            $isPlanned = false;
            $plannedPeriod = null;
            $plannedId = null;

            if (isset($planned)) {
                $plannedItem = $planned->firstWhere('module_id', $module->id);
                if ($plannedItem) {
                    $isPlanned = true;
                    $plannedPeriod = $plannedItem->target_period;
                    $plannedId = $plannedItem->id;
                }
            }

            if ($isApproved) {
                $status = 'aprobada';
                $this->completedCredits += $module->credits;
            } elseif ($isCurrent) {
                $status = 'cursando';
                $this->inProgressCredits += $module->credits;
            } elseif ($isPlanned) {
                $status = 'planificada';
                $this->plannedCredits += $module->credits;
            } else {
                // Verificar prerrequisitos
                $allMet = true;
                foreach ($module->prerequisites as $prereq) {
                    if (!in_array($prereq->id, $this->approvedModuleIds)) {
                        $allMet = false;
                        $missingPrereqs[] = $prereq->name;
                    }
                }
                $status = $allMet ? 'disponible' : 'bloqueada';
            }

            $period = $module->period_number ?? 1;
            $this->modulesByPeriod[$period][] = [
                'id' => $module->id,
                'name' => $module->name,
                'code' => $module->code,
                'credits' => $module->credits,
                'status' => $status,
                'missing_prereqs' => $missingPrereqs,
                'planned_period' => $plannedPeriod,
                'planned_id' => $plannedId,
            ];
        }

        // Calcular porcentaje de progreso real (completado + cursando)
        if ($this->totalCredits > 0) {
            $this->progressPercentage = round((($this->completedCredits) / $this->totalCredits) * 100);
        } else {
            $this->progressPercentage = 0;
        }
    }

    public function changePace($newPace)
    {
        $this->pace = (int) $newPace;
        $this->degreePlan->update(['pace' => $newPace]);
        $this->successMessage = "Ritmo de estudio actualizado a {$newPace} materias por período.";
        $this->loadData();
    }

    /**
     * Algoritmo de Proyección Automática (Advising Suggestion)
     * Proyecta secuencialmente todas las materias pendientes del pensum en períodos futuros respetando prerrequisitos.
     */
    public function autoGeneratePlan()
    {
        try {
            DB::transaction(function () {
                // Eliminar materias planificadas existentes
                PlannedModule::where('degree_plan_id', $this->degreePlan->id)->delete();

                // Cargar todas las materias de la carrera
                $allModules = Module::where('course_id', $this->career->id)
                    ->with('prerequisites')
                    ->get();

                // Listas auxiliares para simular el progreso
                $simulatedApproved = $this->approvedModuleIds;
                $simulatedCurrent = $this->currentModuleIds;

                // Identificar materias pendientes
                $pendingModules = $allModules->filter(function ($m) use ($simulatedApproved, $simulatedCurrent) {
                    return !in_array($m->id, $simulatedApproved) && !in_array($m->id, $simulatedCurrent);
                })->values();

                // Empezar a planificar a partir del próximo período disponible.
                // Determinamos el número del último período cursado o actual
                $startPeriod = 1;
                $lastEnrollment = Enrollment::where('student_id', $this->student->id)
                    ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
                    ->join('modules', 'course_schedules.module_id', '=', 'modules.id')
                    ->orderBy('modules.period_number', 'desc')
                    ->first();
                
                if ($lastEnrollment) {
                    $startPeriod = ($lastEnrollment->period_number ?? 1) + 1;
                }

                $period = $startPeriod;
                $maxPeriodsLimit = 24; // Límite de seguridad para evitar bucles infinitos

                while ($pendingModules->count() > 0 && $period < $maxPeriodsLimit) {
                    $plannedInThisPeriod = [];

                    // Buscar asignaturas que tengan prerrequisitos cumplidos en nuestra lista simulada
                    foreach ($pendingModules as $key => $module) {
                        if (count($plannedInThisPeriod) >= $this->pace) {
                            break;
                        }

                        $prereqsMet = true;
                        foreach ($module->prerequisites as $prereq) {
                            if (!in_array($prereq->id, $simulatedApproved)) {
                                $prereqsMet = false;
                                break;
                            }
                        }

                        if ($prereqsMet) {
                            $plannedInThisPeriod[] = $module;
                            $pendingModules->forget($key);
                        }
                    }

                    // Si no pudimos planificar nada en este periodo pero quedan pendientes,
                    // podría ser que no se cumplen los requisitos. Para evitar bucle, forzamos salida
                    if (empty($plannedInThisPeriod)) {
                        Log::warning("Advising: Se detectó un posible bloqueo de prerrequisitos o inconsistencia en pensum.");
                        break;
                    }

                    // Registrar en la base de datos y simular su aprobación para el siguiente semestre
                    foreach ($plannedInThisPeriod as $module) {
                        PlannedModule::create([
                            'degree_plan_id' => $this->degreePlan->id,
                            'module_id' => $module->id,
                            'target_period' => $period,
                            'status' => 'planned'
                        ]);
                        // Se agrega a la lista de aprobados simulados para el próximo periodo
                        $simulatedApproved[] = $module->id;
                    }

                    $period++;
                }
            });

            $this->successMessage = "¡Plan de carrera proyectado automáticamente con éxito!";
            $this->loadData();

        } catch (\Exception $e) {
            $this->errorMessage = "Error al generar la proyección: " . $e->getMessage();
            Log::error("Error auto-generando proyección: " . $e->getMessage());
        }
    }

    /**
     * Planifica manualmente una materia en un período específico.
     */
    public function planModuleManually($moduleId, $period)
    {
        $module = Module::with('prerequisites')->find($moduleId);
        if (!$module) return;

        // Validar prerrequisitos: Deben estar aprobados o planificados en un período ANTERIOR
        foreach ($module->prerequisites as $prereq) {
            $isApproved = in_array($prereq->id, $this->approvedModuleIds);
            
            // Buscar si el prerrequisito está planificado en un periodo anterior
            $isPlannedBefore = PlannedModule::where('degree_plan_id', $this->degreePlan->id)
                ->where('module_id', $prereq->id)
                ->where('target_period', '<', $period)
                ->exists();

            if (!$isApproved && !$isPlannedBefore) {
                $this->errorMessage = "No puedes planificar {$module->name} en el Período {$period} porque requiere {$prereq->name}, la cual no está aprobada ni planificada para un período anterior.";
                return;
            }
        }

        // Validar límite de materias en este período
        $currentCount = PlannedModule::where('degree_plan_id', $this->degreePlan->id)
            ->where('target_period', $period)
            ->count();

        if ($currentCount >= $this->pace) {
            $this->errorMessage = "Has alcanzado el límite de {$this->pace} materias para el Período {$period}.";
            return;
        }

        // Guardar
        try {
            PlannedModule::updateOrCreate(
                [
                    'degree_plan_id' => $this->degreePlan->id,
                    'module_id' => $moduleId,
                ],
                [
                    'target_period' => $period,
                    'status' => 'planned'
                ]
            );

            $this->successMessage = "Materia planificada en el Período {$period}.";
            $this->loadData();

        } catch (\Exception $e) {
            $this->errorMessage = "Error al planificar materia: " . $e->getMessage();
        }
    }

    /**
     * Elimina una materia de la planificación.
     */
    public function unplanModule($plannedId)
    {
        $pm = PlannedModule::find($plannedId);
        if (!$pm) return;

        $moduleId = $pm->module_id;
        $period = $pm->target_period;

        // Validar si es prerrequisito de alguna otra materia planificada en períodos posteriores
        $dependentPlanned = PlannedModule::where('degree_plan_id', $this->degreePlan->id)
            ->where('target_period', '>', $period)
            ->get();

        foreach ($dependentPlanned as $dp) {
            $hasPrereqRelation = DB::table('module_prerequisites')
                ->where('module_id', $dp->module_id)
                ->where('prerequisite_id', $moduleId)
                ->exists();

            if ($hasPrereqRelation) {
                $this->errorMessage = "No puedes desplanificar esta materia porque es prerrequisito de {$dp->module->name}, la cual está planificada para el Período {$dp->target_period}.";
                return;
            }
        }

        $pm->delete();
        $this->successMessage = "Materia eliminada de la proyección.";
        $this->loadData();
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.student-portal.academic-projection')
            ->layout('layouts.dashboard');
    }
}
