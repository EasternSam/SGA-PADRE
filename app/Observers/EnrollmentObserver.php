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
}