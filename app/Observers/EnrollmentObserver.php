<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\User;
use App\Models\ActivityLog; // Importante para registrar
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class EnrollmentObserver
{
    /**
     * Handle the Enrollment "created" event.
     */
    public function created(Enrollment $enrollment)
    {
        $source = auth()->check() ? 'Sistema' : 'Página Web';
        $type = auth()->check() ? 'info' : 'success';
        
        $studentName = $enrollment->student->full_name ?? 'Estudiante';
        $courseName = $enrollment->courseSchedule->module->course->name ?? 'Curso';
        $sectionName = $enrollment->courseSchedule->section_name ?? 'Sección';

        // 1. Notificación al Admin (Existente)
        $admins = User::role('Admin')->get();
        Notification::send($admins, new SystemNotification(
            "Nueva Inscripción ($source)",
            "$studentName se ha inscrito en $courseName.",
            $type,
            route('admin.students.profile', $enrollment->student_id)
        ));

        // 2. REGISTRO DE ACTIVIDAD (NUEVO)
        try {
            ActivityLog::create([
                'user_id' => auth()->id(), // Puede ser null si es registro público
                'action' => 'Inscripción',
                'description' => "Se inscribió al estudiante $studentName en $courseName - $sectionName.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'payload' => json_encode([
                    'student_id' => $enrollment->student_id,
                    'schedule_id' => $enrollment->course_schedule_id,
                    'status' => $enrollment->status
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error("Error registrando log de inscripción: " . $e->getMessage());
        }

        // Sincronizar inscripción a WordPress
        $this->syncEnrollmentToWordpress($enrollment);
    }

    /**
     * Handle the Enrollment "updated" event.
     */
    public function updated(Enrollment $enrollment)
    {
        // Sincronizar inscripción modificada a WordPress
        $this->syncEnrollmentToWordpress($enrollment);
    }

    /**
     * Handle the Enrollment "updating" event.
     */
    public function updating(Enrollment $enrollment)
    {
        // Detectar cambios importantes para el log
        if ($enrollment->isDirty('status')) {
            $oldStatus = $enrollment->getOriginal('status');
            $newStatus = $enrollment->status;
            
            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Estado Inscripción',
                    'description' => "Cambio de estado de inscripción para {$enrollment->student->full_name}: $oldStatus -> $newStatus",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } catch (\Exception $e) {}
        }

        // Lógica de calificación (Existente)
        if ($enrollment->isDirty('final_grade')) {
            if (!is_null($enrollment->final_grade)) {
                $enrollment->status = 'Completado';
            } elseif (is_null($enrollment->final_grade)) {
                $enrollment->status = 'Cursando';
            }
        }
    }
    
    /**
     * Handle the Enrollment "deleted" event.
     */
    public function deleted(Enrollment $enrollment)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Anulación',
                'description' => "Se eliminó/anuló la inscripción de {$enrollment->student->full_name} en " . ($enrollment->courseSchedule->module->name ?? 'Módulo'),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {}

        // Sincronizar anulación a WordPress
        try {
            $student = $enrollment->student;
            $courseSchedule = $enrollment->courseSchedule;
            
            if ($student && $courseSchedule) {
                $courseName = $courseSchedule->module->course->name ?? 'Curso';
                $wpApiService = app(\App\Services\WordpressApiService::class);
                $wpApiService->syncEnrollment([
                    'cedula'          => $student->cedula,
                    'course_name'     => $courseName,
                    'schedule_string' => '',
                    'status'          => 'Cancelado',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error al sincronizar anulación a WordPress: " . $e->getMessage());
        }
    }

    /**
     * Auxiliar para sincronizar la inscripción con WordPress.
     */
    protected function syncEnrollmentToWordpress(Enrollment $enrollment)
    {
        try {
            $student = $enrollment->student;
            $courseSchedule = $enrollment->courseSchedule;
            
            if (!$student || !$courseSchedule) {
                return;
            }

            // Módulo -> Curso
            $courseName = $courseSchedule->module->course->name ?? 'Curso';
            $scheduleString = $courseSchedule->section_name ?? 'Horario';

            $wpApiService = app(\App\Services\WordpressApiService::class);
            $wpApiService->syncEnrollment([
                'cedula'          => $student->cedula,
                'course_name'     => $courseName,
                'schedule_string' => $scheduleString,
                'status'          => $enrollment->status,
            ]);
        } catch (\Exception $e) {
            Log::error("Error al sincronizar inscripción a WordPress: " . $e->getMessage());
        }
    }
}