<?php

namespace App\Livewire\Kiosk\Auth;

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\User;
use App\Models\Student;
use App\Models\Enrollment;
use App\Services\AccountingEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Signup extends Component
{
    public $step = 1;
    
    // Step 1: Schedule Selection
    public $search = '';
    public $availableSchedules = [];
    public $selectedScheduleId = null;
    public $selectedScheduleDetails = null;

    // Step 2: Personal Details & Kiosk Auth
    public $first_name = '';
    public $last_name = '';
    public $cedula = '';
    public $email = '';
    public $phone = '';
    public $pin = '';
    
    // Virtual Keyboard/Numpad support
    public $focusedInput = 'first_name';

    public function mount()
    {
        // Fetch active schedules with available capacity (limit 20 for UI demo)
        $this->availableSchedules = CourseSchedule::with(['module.course', 'teacher'])
            ->limit(20)
            ->get()
            ->map(function ($schedule) {
                $days = $schedule->days_of_week ?? [];
                if (is_string($days)) $days = json_decode($days, true) ?? [];
                $daysStr = is_array($days) ? implode(', ', $days) : ($schedule->days_of_week ?? 'Por definir');

                $start = $schedule->start_time ? Carbon::parse($schedule->start_time)->format('h:i A') : '--:--';
                $end = $schedule->end_time ? Carbon::parse($schedule->end_time)->format('h:i A') : '--:--';

                return [
                    'id' => $schedule->id,
                    'course_name' => $schedule->module?->course?->name ?? 'Materia General',
                    'module_name' => $schedule->module?->name ?? 'Módulo',
                    'teacher_name' => $schedule->teacher ? ($schedule->teacher->first_name . ' ' . $schedule->teacher->last_name) : 'Profesor Por Asignar',
                    'schedule_str' => "$daysStr | $start - $end",
                    'cost' => $schedule->module?->cost ?? 0,
                ];
            })->toArray();
    }

    public function getFilteredSchedulesProperty()
    {
        if (empty($this->search)) {
            return $this->availableSchedules;
        }

        $term = strtolower($this->search);
        return array_filter($this->availableSchedules, function ($schedule) use ($term) {
            return str_contains(strtolower($schedule['course_name']), $term) ||
                   str_contains(strtolower($schedule['module_name']), $term) ||
                   str_contains(strtolower($schedule['teacher_name']), $term);
        });
    }

    public function selectSchedule($id)
    {
        $this->selectedScheduleId = $id;
        $this->selectedScheduleDetails = collect($this->availableSchedules)->firstWhere('id', $id);
        $this->nextStep();
    }

    public function nextStep()
    {
        if ($this->step == 2) {
            $this->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'cedula' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'pin' => 'required|numeric|digits:4',
            ], [
                'first_name.required' => 'El nombre es requerido.',
                'last_name.required' => 'El apellido es requerido.',
                'cedula.required' => 'La cédula o matrícula es requerida.',
                'email.required' => 'El email es requerido.',
                'phone.required' => 'El teléfono es requerido.',
                'pin.required' => 'El PIN de 4 dígitos es requerido.',
                'pin.digits' => 'El PIN debe ser exactamente de 4 dígitos numéricos.',
            ]);
            
            // Verificación si correo o cedula ya existen (para que Kiosk Signup sea un flujo cerrado seguro)
            $existingUser = User::where('email', $this->email)
                ->orWhereHas('student', function($q) {
                    $q->where('cedula', $this->cedula);
                })->first();

            if ($existingUser) {
                $this->addError('email', 'Este usuario ya existe. Si eres tú, procede al Kiosco de Login Principal e ingresa.');
                return;
            }
        }

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function setFocus($field)
    {
        $this->focusedInput = $field;
    }

    public function appendDigit($digit)
    {
        // For Numpad specifically or Keyboard mapped
        if ($this->focusedInput === 'pin' && strlen($this->pin) < 4) {
            $this->pin .= $digit;
        } elseif ($this->focusedInput === 'phone') {
            $this->phone .= $digit;
        } elseif ($this->focusedInput === 'cedula') {
            $this->cedula .= $digit;
        } elseif ($this->focusedInput === 'first_name') {
            $this->first_name .= $digit;
            $this->first_name = mb_convert_case($this->first_name, MB_CASE_TITLE, "UTF-8");
        } elseif ($this->focusedInput === 'last_name') {
            $this->last_name .= $digit;
            $this->last_name = mb_convert_case($this->last_name, MB_CASE_TITLE, "UTF-8");
        } elseif ($this->focusedInput === 'email') {
            $this->email .= $digit;
        } elseif ($this->focusedInput === 'search') {
            $this->search .= $digit;
        }
    }

    public function deleteDigit()
    {
        if ($this->focusedInput === 'pin' && strlen($this->pin) > 0) {
            $this->pin = substr($this->pin, 0, -1);
        } elseif ($this->focusedInput === 'phone' && strlen($this->phone) > 0) {
            $this->phone = substr($this->phone, 0, -1);
        } elseif ($this->focusedInput === 'cedula' && strlen($this->cedula) > 0) {
            $this->cedula = substr($this->cedula, 0, -1);
        } elseif ($this->focusedInput === 'first_name' && strlen($this->first_name) > 0) {
            $this->first_name = mb_substr($this->first_name, 0, -1);
        } elseif ($this->focusedInput === 'last_name' && strlen($this->last_name) > 0) {
            $this->last_name = mb_substr($this->last_name, 0, -1);
        } elseif ($this->focusedInput === 'email' && strlen($this->email) > 0) {
            $this->email = substr($this->email, 0, -1);
        } elseif ($this->focusedInput === 'search' && strlen($this->search) > 0) {
            $this->search = substr($this->search, 0, -1);
        }
    }

    public function register()
    {
        // 1. Double check validations before executing
        if ($this->step !== 3 || !$this->selectedScheduleId) return;

        try {
            DB::transaction(function () {
                // Generar matrícula provisoria (puede ser el timestamp para agilidad, o dejarla nula para que admin la asigne)
                $tempCode = 'K' . date('ymhis');

                // A. Crear Usuario (Credenciales)
                $user = User::create([
                    'name' => "{$this->first_name} {$this->last_name}",
                    'email' => $this->email,
                    'password' => Hash::make($this->cedula), // Default password
                    'kiosk_pin' => $this->pin, // Magic Kiosk Secret
                ]);

                // Asignar rol de estudiante
                $user->assignRole('Estudiante');

                // B. Crear Perfil de Estudiante
                $student = Student::create([
                    'user_id' => $user->id,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'cedula' => $this->cedula,
                    'email' => $this->email,
                    'mobile_phone' => $this->phone,
                    'student_code' => $tempCode,
                    'status' => 'Activo', // O 'Inscrito'
                ]);

                // C. Cargar el Horario y crear Inscripción
                $schedule = CourseSchedule::with('module.course')->find($this->selectedScheduleId);
                
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Pendiente', // Pendiente de realizar el pago en el quiosco
                ]);

                // D. Generar DEUDA Cuentas por Cobrar e Ingresos vía Accounting Engine
                $amount = $schedule->module?->cost ?? 0;
                if ($amount > 0) {
                    $engine = app(AccountingEngine::class);
                    $engine->registerStudentDebt($enrollment, $amount);
                }

                // Log entry
                Log::info("[KIOSK-SIGNUP] Nuevo estudiante registrado e inscrito asíncronamente en el kiosco: {$student->id} ({$student->first_name}) - Deuda Generada: \${$amount}");

                // E. Auto-Auth the user into the terminal
                Auth::login($user);
                session()->regenerate();
            });

            // Redirect smoothly to directly pay for the just-inscribed course!
            return redirect()->route('kiosk.finances')->with('notify', [
                'type' => 'success', 
                'message' => '¡Felicidades, ya estás inscrito! Realiza tu pago deslizando tu tarjeta abajo para activar tu ingreso.'
            ]);

        } catch (\Exception $e) {
            Log::error("[KIOSK-SIGNUP] Fallo catastrófico al auto-inscribir: " . $e->getMessage());
            $this->addError('registration', 'Hubo un problema. Por favor solicite asistencia en caja. ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.kiosk.auth.signup')->layout('layouts.kiosk');
    }
}
