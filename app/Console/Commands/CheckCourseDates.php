<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CourseSchedule;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Mail\CourseNotificationMail; // Importar Mailable
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail; // Importar Facade Mail
use Illuminate\Support\Facades\Log;

class CheckCourseDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sga:check-course-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica fechas de inicio y fin de cursos para enviar notificaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando verificación de fechas de cursos...');

        // 1. Cursos por INICIAR en 3 días
        $startThreshold = Carbon::now()->addDays(3)->format('Y-m-d');
        
        $startingSchedules = CourseSchedule::whereDate('start_date', $startThreshold)
            ->with(['module.course', 'teacher', 'enrollments.student']) // Cargar estudiantes inscritos
            ->get();

        if ($startingSchedules->count() > 0) {
            $admins = User::role('Admin')->get();
            
            foreach ($startingSchedules as $schedule) {
                $courseName = $schedule->module->course->name ?? 'Curso desconocido';
                $moduleName = $schedule->module->name ?? 'Módulo';
                
                $title = "Curso por Iniciar: $courseName";
                $message = "El módulo '$moduleName' está programado para iniciar en 3 días ({$schedule->start_date}).";
                
                // Notificar Admins
                Notification::send($admins, new SystemNotification(
                    $title,
                    $message,
                    'info',
                    route('admin.courses.index') 
                ));

                // Notificar Profesor
                if ($schedule->teacher) {
                     $schedule->teacher->notify(new SystemNotification(
                        'Recordatorio de Inicio de Clases',
                        "Tu clase de '$courseName - $moduleName' inicia en 3 días.",
                        'info'
                     ));
                }

                // NUEVO: Notificar Estudiantes Inscritos
                foreach ($schedule->enrollments as $enrollment) {
                    if ($enrollment->student && $enrollment->student->email) {
                        try {
                            Mail::to($enrollment->student->email)->send(new CourseNotificationMail($schedule, 'start'));
                        } catch (\Exception $e) {
                            Log::error("Error enviando correo de inicio de curso a {$enrollment->student->email}: " . $e->getMessage());
                        }
                    }
                }
            }
            $this->info("Notificadas " . $startingSchedules->count() . " clases por iniciar.");
        }

        // 2. Cursos por TERMINAR en 7 días
        $endThreshold = Carbon::now()->addDays(7)->format('Y-m-d');

        $endingSchedules = CourseSchedule::whereDate('end_date', $endThreshold)
            ->with(['module.course', 'enrollments.student'])
            ->get();

        if ($endingSchedules->count() > 0) {
            $admins = User::role('Admin')->get();
            
            foreach ($endingSchedules as $schedule) {
                $courseName = $schedule->module->course->name ?? 'Curso desconocido';
                
                // Notificar Admins
                Notification::send($admins, new SystemNotification(
                    "Curso por Finalizar: $courseName",
                    "El módulo '{$schedule->module->name}' finaliza en una semana ({$schedule->end_date}).",
                    'warning'
                ));

                // NUEVO: Notificar Estudiantes Inscritos
                foreach ($schedule->enrollments as $enrollment) {
                    if ($enrollment->student && $enrollment->student->email) {
                        try {
                            Mail::to($enrollment->student->email)->send(new CourseNotificationMail($schedule, 'end'));
                        } catch (\Exception $e) {
                            Log::error("Error enviando correo de fin de curso a {$enrollment->student->email}: " . $e->getMessage());
                        }
                    }
                }
            }
            $this->info("Notificadas " . $endingSchedules->count() . " clases por finalizar.");
        }

        $this->info('Verificación completada.');
    }
}