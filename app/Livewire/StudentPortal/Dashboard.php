<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Carbon\Carbon;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public ?Student $student;
    
    public Collection $activeEnrollments;    
    public Collection $pendingEnrollments;   
    public Collection $completedEnrollments; 
    public Collection $pendingPayments;      
    public Collection $paymentHistory;       

    public bool $showProfileModal = false;
    
    public $mobile_phone; 
    public $birth_date;   
    public $address;      
    public $gender;       
    public $city;         
    public $sector;       

    public function mount()
    {
        $student = Auth::user()?->student; 

        if (!$student) {
            if (!request()->routeIs('profile.edit')) {
                session()->flash('error', 'Su cuenta de usuario no está enlazada a un perfil de estudiante.');
                return redirect()->route('profile.edit');
            }
            $this->student = null; 
            $this->initEmptyCollections();
            return;
        }

        $this->student = $student;
        
        // --- Cargar datos actuales ---
        $this->mobile_phone = $this->student->mobile_phone ?? $this->student->phone; 
        $this->birth_date = $this->student->birth_date ? $this->student->birth_date->format('Y-m-d') : null;
        $this->address = $this->student->address;
        $this->gender = $this->student->gender;
        $this->city = $this->student->city;
        $this->sector = $this->student->sector;

        // --- Lógica de Apertura Automática (ONBOARDING) ---
        // 1. Verificamos si falta información (Vacío o N/A)
        $hasIncompleteData = (
            $this->isIncomplete($this->mobile_phone) || 
            $this->isIncomplete($this->address) ||
            $this->isIncomplete($this->birth_date) ||
            $this->isIncomplete($this->city)
        );

        // 2. Solo abrimos automáticamente si hay datos incompletos Y NO se ha mostrado ya en esta sesión.
        // Esto evita el bucle infinito si el usuario decide dejarlo vacío.
        if ($hasIncompleteData && !session()->has('profile_onboarding_seen')) {
            $this->showProfileModal = true;
        }

        $this->loadStudentData();
    }

    private function initEmptyCollections()
    {
        $this->activeEnrollments = collect();
        $this->pendingEnrollments = collect();
        $this->completedEnrollments = collect();
        $this->pendingPayments = collect();
        $this->paymentHistory = collect();
    }

    private function loadStudentData()
    {
        $baseQuery = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher'
            ])
            ->where('student_id', $this->student->id);

        $this->activeEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Cursando', 'cursando', 'Activo', 'activo'])
            ->get();

        $this->pendingEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Pendiente', 'pendiente', 'Enrolled', 'enrolled'])
            ->get();

        $this->completedEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Completado', 'completado'])
            ->get();

        $this->pendingPayments = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module'])
            ->where('student_id', $this->student->id)
            ->where('status', 'Pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->paymentHistory = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module'])
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Verifica si el campo está incompleto.
     * AHORA: Retorna true si es vacío o "N/A".
     */
    private function isIncomplete($value)
    {
        return empty($value) || strtoupper(trim($value)) === 'N/A';
    }

    /**
     * Convierte "N/A" o vacíos a NULL para limpiar la base de datos.
     */
    private function sanitizeInput($value)
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if (empty($trimmed) || strtoupper($trimmed) === 'N/A') {
                return null;
            }
            return $trimmed;
        }
        return $value;
    }

    /**
     * Abre el modal manualmente (Botón Editar)
     */
    public function openProfileModal()
    {
        $this->showProfileModal = true;
        $this->dispatch('open-modal', 'complete-profile-modal');
    }

    public function saveProfile()
    {
        $this->validate([
            'mobile_phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|in:Masculino,Femenino,Otro',
            'city' => 'nullable|string|max:100',
            'sector' => 'nullable|string|max:100',
        ]);

        $dataToUpdate = [
            'mobile_phone' => $this->sanitizeInput($this->mobile_phone),
            'birth_date' => $this->sanitizeInput($this->birth_date),
            'address' => $this->sanitizeInput($this->address),
            'gender' => $this->sanitizeInput($this->gender),
            'city' => $this->sanitizeInput($this->city),
            'sector' => $this->sanitizeInput($this->sector),
        ];

        if ($this->student) {
            // Sincronizar teléfono principal si está vacío o sucio
            if ($this->isIncomplete($this->student->phone) || empty($this->student->phone)) {
                $dataToUpdate['phone'] = $dataToUpdate['mobile_phone'];
            }

            $this->student->update($dataToUpdate);
            $this->student->refresh();

            session()->flash('message', 'Perfil actualizado exitosamente.');
        }

        $this->closeProfileModal();
    }

    public function closeProfileModal()
    {
        $this->showProfileModal = false;
        $this->dispatch('close-modal', 'complete-profile-modal');
        
        // Marcamos en sesión que ya interactuó con el modal para no volver a abrirlo automáticamente
        session()->put('profile_onboarding_seen', true);
    }

    public function render()
    {
        return view('livewire.student-portal.dashboard');
    }
}