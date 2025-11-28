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
    // --- Propiedades ---
    public ?Student $student;
    
    // Colecciones de datos
    public Collection $activeEnrollments;    
    public Collection $pendingEnrollments;   
    public Collection $completedEnrollments; 
    public Collection $pendingPayments;      
    public Collection $paymentHistory;       

    // --- Propiedades para el Modal de Completar Perfil ---
    public bool $showProfileModal = false;
    
    // Campos del formulario (NUEVOS)
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
        
        // --- Cargar datos actuales en el formulario ---
        // Usamos el operador ?? para evitar errores si el campo es null
        $this->mobile_phone = $this->student->mobile_phone ?? $this->student->phone; // Si no hay móvil, sugerimos el fijo
        $this->birth_date = $this->student->birth_date ? $this->student->birth_date->format('Y-m-d') : null;
        $this->address = $this->student->address;
        $this->gender = $this->student->gender;
        $this->city = $this->student->city;
        $this->sector = $this->student->sector;

        // --- Verificar si falta información clave ---
        // Activamos el modal si algún campo crítico tiene "N/A" o está vacío (según tu preferencia)
        if (
            $this->isIncomplete($this->mobile_phone) || 
            $this->isIncomplete($this->address) ||
            $this->isIncomplete($this->birth_date) ||
            $this->isIncomplete($this->city)
        ) {
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
     * Verifica si el campo tiene datos "basura" como N/A que necesitan limpieza.
     * Si está vacío (null/empty string), retorna false porque es opcional.
     */
    private function isIncomplete($value)
    {
        if (empty($value)) return false; 
        return strtoupper(trim($value)) === 'N/A';
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

        if ($this->student) {
            $this->student->update([
                'mobile_phone' => $this->mobile_phone,
                // Opcional: Si 'phone' está vacío, lo llenamos con el móvil también
                'phone' => $this->student->phone ?? $this->mobile_phone, 
                'birth_date' => $this->birth_date,
                'address' => $this->address,
                'gender' => $this->gender,
                'city' => $this->city,
                'sector' => $this->sector,
            ]);
            
            $this->student->refresh();
            session()->flash('message', 'Perfil actualizado exitosamente.');
        }

        $this->closeProfileModal();
    }

    public function closeProfileModal()
    {
        $this->showProfileModal = false;
        // Importante: Disparar evento para cerrar el modal en AlpineJS
        $this->dispatch('close-modal', 'complete-profile-modal');
    }

    public function render()
    {
        return view('livewire.student-portal.dashboard');
    }
}