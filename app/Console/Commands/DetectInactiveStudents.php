<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollment;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;

class DetectInactiveStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sga:detect-inactive-students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta estudiantes con 6 ausencias consecutivas y los marca como Suspendidos para detener la facturación automática.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando escaneo de deserciones silenciosas...');

        // Solo analizamos estudiantes cursando activamente
        $enrollments = Enrollment::whereIn('status', ['Cursando', 'En Curso', 'Activo'])
            ->with('student', 'courseSchedule.module')
            ->get();

        $suspensionCount = 0;

        foreach ($enrollments as $enrollment) {
            // Obtenemos las últimas 6 asistencias
            $recentAttendances = Attendance::where('enrollment_id', $enrollment->id)
                ->orderBy('attendance_date', 'desc')
                ->take(6)
                ->get();

            // Solo actuamos si el estudiante TIENE al menos 6 asistencias registradas en la db
            if ($recentAttendances->count() === 6) {
                // Verificamos si TODAS son 'Ausente'
                $allAbsent = $recentAttendances->every(function ($att) {
                    return strtolower($att->status) === 'ausente';
                });

                if ($allAbsent) {
                    // Acción de suspensión
                    $enrollment->status = 'Suspendido';
                    $enrollment->save();

                    // Registrar actividad para auditoría
                    activity()
                        ->performedOn($enrollment)
                        ->log("Suspensión Automática: El sistema ha suspendido a este estudiante tras registrar 6 ausencias consecutivas.");

                    $this->warn("Estudiante [{$enrollment->student->fullName}] suspendido por inasistencia recurrente.");
                    
                    $suspensionCount++;
                }
            }
        }

        $this->info("Escaneo finalizado. Total de estudiantes suspendidos: {$suspensionCount}");
    }
}
