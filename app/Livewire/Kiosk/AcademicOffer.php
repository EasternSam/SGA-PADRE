<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\PaymentConcept;
use App\Services\AccountingEngine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcademicOffer extends Component
{
    public $availableSchedules = [];

    public function mount()
    {
        $user = Auth::user();
        
        if (!$user || !$user->student) {
            return redirect()->route('kiosk.login');
        }

        $student = $user->student;

        // Fetch active schedules that have available capacity
        // En un caso real, filtraríamos por las materias que tocan en el pensum de este estudiante específico, 
        // pero para la demostración del Kiosco traeremos horarios activos genéricos.
        $this->availableSchedules = CourseSchedule::with(['module.course', 'teacher'])
            // Si trackearmos capacidad: ->whereColumn('enrolled_count', '<', 'capacity')
            ->limit(10)
            ->get()
            ->map(function ($schedule) {
                // Formatting data for the UI
                $days = $schedule->days_of_week ?? [];
                if (is_string($days)) $days = json_decode($days, true) ?? [];
                $daysStr = is_array($days) ? implode(', ', $days) : ($schedule->days_of_week ?? 'Por definir');

                $start = $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') : '--:--';
                $end = $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') : '--:--';

                return [
                    'id' => $schedule->id,
                    'course_name' => $schedule->module?->course?->name ?? 'Materia General',
                    'module_name' => $schedule->module?->name ?? 'Módulo',
                    'teacher_name' => $schedule->teacher ? ($schedule->teacher->first_name . ' ' . $schedule->teacher->last_name) : 'Profesor Por Asignar',
                    'schedule_str' => "$daysStr | $start - $end",
                    'cost' => $schedule->module?->cost ?? 0, // Assuming module has a cost
                ];
            });
    }

    public function enroll($scheduleId)
    {
        $user = Auth::user();
        if (!$user || !$user->student) return;

        $student = $user->student;
        $schedule = CourseSchedule::with('module.course')->find($scheduleId);

        if (!$schedule) {
            $this->dispatch('kiosk-notification', ['type' => 'error', 'message' => 'El horario seleccionado ya no está disponible.']);
            return;
        }

        // Verificar si ya está inscrito
        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('course_schedule_id', $scheduleId)
            ->whereIn('status', ['Pendiente', 'Cursando', 'Completado'])
            ->exists();

        if ($alreadyEnrolled) {
            $this->dispatch('kiosk-notification', ['type' => 'error', 'message' => 'Ya estás inscrito en esta asignatura.']);
            return;
        }

        try {
            DB::transaction(function () use ($student, $schedule) {
                // 1. Crear la inscripción (Deuda originada)
                $enrollment = new Enrollment();
                $enrollment->student_id = $student->id;
                $enrollment->course_schedule_id = $schedule->id;
                $enrollment->status = 'Pendiente'; // Queda pendiente hasta que pague
                $enrollment->save();

                // 2. Determinar el costo (del módulo o un default)
                $amount = $schedule->module?->cost ?? 0;

                // 3. Registrar la cuenta por cobrar en el AccountingEngine si tiene costo
                if ($amount > 0) {
                    $engine = app(AccountingEngine::class);
                    $engine->registerStudentDebt($enrollment, $amount);
                }
            });

            Log::info("[KIOSK-ENROLL] Estudiante {$student->id} inscrito auto. en Schedule {$schedule->id}.");

            $this->dispatch('kiosk-notification', [
                'type' => 'success', 
                'message' => '¡Inscrito correctamente! Se ha generado el cargo a tu estado de cuenta.'
            ]);

            // Refrescar para ocultar la opción inscrita
            $this->availableSchedules = collect($this->availableSchedules)->filter(function ($s) use ($scheduleId) {
                return $s['id'] != $scheduleId;
            })->values();

        } catch (\Exception $e) {
            Log::error("[KIOSK-ENROLL] Error al inscribir: " . $e->getMessage());
            $this->dispatch('kiosk-notification', ['type' => 'error', 'message' => 'Ocurrió un error interno. Intenta en Admisiones.']);
        }
        $this->availableSchedules = collect($this->availableSchedules)->filter(function ($s) use ($scheduleId) {
            return $s['id'] != $scheduleId;
        })->values();
    }

    public function goBack()
    {
        return $this->redirect(route('kiosk.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.kiosk.academic-offer')->layout('layouts.kiosk');
    }
}
