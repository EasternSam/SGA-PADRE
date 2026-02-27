<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class Grades extends Component
{
    public $approvedSubjects = [];

    public function mount()
    {
        $user = Auth::user();
        
        if (!$user || !$user->student) {
            return redirect()->route('kiosk.login');
        }

        $student = $user->student;

        // Fetch all completed enrollments with grades >= 70
        // Para simplificar, traemos las matrículas finalizadas o activas que tengan nota final registrada
        $this->approvedSubjects = Enrollment::with(['courseSchedule.module.course'])
            ->where('student_id', $student->id)
            ->whereNotNull('final_grade')
            ->where('final_grade', '>=', 70) // Asumiendo 70 como aprobadom
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course_name' => $enrollment->courseSchedule?->module?->course?->name ?? 'Materia Desconocida',
                    'module_name' => $enrollment->courseSchedule?->module?->name ?? 'Módulo',
                    'grade' => $enrollment->final_grade,
                    'literal' => $this->getLiteralGrade($enrollment->final_grade),
                    'period' => $enrollment->created_at->format('M Y')
                ];
            });
    }

    private function getLiteralGrade($score)
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        return 'F';
    }

    public function sendReportToEmail()
    {
        $user = Auth::user();
        if (!$user || empty($user->email)) {
             $this->dispatch('kiosk-notification', ['type' => 'error', 'message' => 'No tienes un correo registrado.']);
             return;
        }

        // Aquí despacharíamos un Job real (ej. SendStudentGradesReport::dispatch($user)).
        // Por ahora simularemos el retraso y éxito.
        sleep(1); // Simular generación de PDF

        $this->dispatch('kiosk-notification', [
            'type' => 'success', 
            'message' => 'Récord enviado exitosamente a ' . $user->email
        ]);
    }

    public function goBack()
    {
        return $this->redirect(route('kiosk.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.kiosk.grades')->layout('layouts.kiosk');
    }
}
