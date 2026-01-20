<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

class EnrollmentObserver
{
    /**
     * Handle the Enrollment "created" event.
     */
    public function created(Enrollment $enrollment)
    {
        // Determinar origen (suponiendo que si no hay 'causer' en ActivityLog fue Web, 
        // o usando lógica personalizada si tienes un campo 'origin' en enrollment)
        // Por simplicidad, asumiremos que si el usuario logueado es Admin, es Sistema.
        // Si no hay usuario logueado (API), es Web.
        
        $source = auth()->check() ? 'Sistema' : 'Página Web';
        $type = auth()->check() ? 'info' : 'success'; // Verde para Web (dinero!), Azul para Sistema
        
        $studentName = $enrollment->student->full_name ?? 'Estudiante';
        $courseName = $enrollment->courseSchedule->module->course->name ?? 'Curso';

        $admins = User::role('Admin')->get();

        Notification::send($admins, new SystemNotification(
            "Nueva Inscripción ($source)",
            "$studentName se ha inscrito en $courseName.",
            $type,
            route('admin.students.profile', $enrollment->student_id)
        ));
    }

    /**
     * Handle the Enrollment "updating" event.
     * Se ejecuta ANTES de guardar los cambios en la base de datos.
     */
    public function updating(Enrollment $enrollment)
    {
        // Detectar si la calificación final ha cambiado
        if ($enrollment->isDirty('final_grade')) {
            
            // Caso 1: Se asignó una nota (y no es nula)
            if (!is_null($enrollment->final_grade)) {
                // Verificar si ya está en un estado final para evitar sobrescribir estados especiales si los hubiera,
                // pero según tu requerimiento, al tener nota debe estar "Completado".
                // Puedes ajustar 'Completado' por 'Aprobado'/'Reprobado' si tienes la lógica de nota mínima.
                
                $enrollment->status = 'Completado';
            } 
            // Caso 2: Se eliminó la nota (se puso en null/vacío)
            // Es buena práctica revertir el estado a 'Cursando' si el profesor borra la nota por error.
            elseif (is_null($enrollment->final_grade)) {
                $enrollment->status = 'Cursando';
            }
        }
    }
}