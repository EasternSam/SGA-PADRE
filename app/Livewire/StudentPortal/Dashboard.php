<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use Livewire\WithFileUploads; // Vital para subir archivos
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Admission;
use App\Models\Course;
use App\Models\CourseSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // <--- AGREGADO PARA DEBUG
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy; 
use Illuminate\Support\Collection;
use Carbon\Carbon;

#[Lazy]
#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    use WithFileUploads; // Habilitamos subida de archivos

    public ?Student $student;
    public $user;
    
    // Colecciones
    public Collection $activeDegreeEnrollments; 
    public Collection $activeCourseEnrollments; 
    
    public Collection $pendingEnrollments;   
    public Collection $completedEnrollments; 
    public Collection $pendingPayments;      
    public Collection $paymentHistory;       

    public ?Course $activeCareer = null;
    public bool $showProfileModal = false;
    
    // Datos perfil
    public $mobile_phone; 
    public $birth_date;   
    public $address;      
    public $gender;       
    public $city;         
    public $sector;       
    
    // Foto de Perfil
    public $photo; // Archivo temporal al subir
    public $current_photo_url; // Para mostrar la actual

    // Variables modal
    public $searchAvailableCourse = '';
    public $selectedScheduleId = null;
    public $availableSchedules = [];

    public function placeholder()
    {
        return <<<'HTML'
        <div class="min-h-screen bg-gray-50/50 p-8">
            <div class="animate-pulse space-y-8">
                <div class="h-8 bg-gray-200 rounded w-1/4"></div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="h-32 bg-gray-200 rounded-xl"></div>
                    <div class="h-32 bg-gray-200 rounded-xl"></div>
                    <div class="h-32 bg-gray-200 rounded-xl"></div>
                    <div class="h-32 bg-gray-200 rounded-xl"></div>
                </div>
                <div class="h-64 bg-gray-200 rounded-xl"></div>
            </div>
        </div>
        HTML;
    }

    public function mount()
    {
        $this->user = Auth::user();
        $this->student = $this->user?->student;

        // Inicializar colecciones vacías para evitar errores
        $this->initEmptyCollections();

        if ($this->student) {
            // Carga de datos de perfil
            $this->mobile_phone = $this->student->mobile_phone ?? $this->student->phone; 
            $this->birth_date = $this->student->birth_date ? $this->student->birth_date->format('Y-m-d') : null;
            $this->address = $this->student->address;
            $this->gender = $this->student->gender;
            $this->city = $this->student->city;
            $this->sector = $this->student->sector;
            $this->current_photo_url = $this->student->profile_photo_url; // Usamos el accessor

            // Verificar onboarding
            $hasIncompleteData = (
                $this->isIncomplete($this->mobile_phone) || 
                $this->isIncomplete($this->address) ||
                $this->isIncomplete($this->birth_date) ||
                $this->isIncomplete($this->city)
            );

            if ($hasIncompleteData && !session()->has('profile_onboarding_seen')) {
                $this->showProfileModal = true;
            }

            $this->loadData();
        }
    }

    // --- DEBUG LIFECYCLE: INSPECCIÓN PROFUNDA ---
    public function hydrate()
    {
        Log::info('--- [DEBUG BACKEND] Hydrate (Solicitud Recibida) ---');
        
        // 1. Ver qué actualizaciones solicita el frontend
        // Livewire envía las actualizaciones en un array 'updates' dentro del payload
        $payload = request()->all();
        $updates = data_get($payload, 'components.0.updates', 'No updates found in payload');
        
        Log::info('[DEBUG BACKEND] Payload Updates:', is_array($updates) ? $updates : ['msg' => $updates]);
        
        // 2. Ver si hay archivos temporales en la request (Livewire usa una request separada para upload, 
        // pero luego envía el hash en la request de actualización)
        Log::info('[DEBUG BACKEND] Request methods/inputs:', [
            'method' => request()->method(),
            'has_files' => request()->hasFile('photo') ? 'YES' : 'NO',
            'content_length' => request()->header('Content-Length'),
        ]);
    }

    public function updating($propertyName, $value)
    {
        // Se ejecuta ANTES de que la propiedad cambie. Si esto no sale, Livewire no intentó cambiar nada.
        Log::info("[DEBUG BACKEND] updating() disparado para: {$propertyName}");
    }

    public function updated($propertyName)
    {
        Log::info("[DEBUG BACKEND] updated() disparado para: {$propertyName}");
    }
    // ---------------------------------------------------------

    // Método para coordinar la carga de datos
    public function loadData()
    {
        if (!$this->student) return;

        // 1. Cargar Carrera Activa
        $admission = Admission::where('user_id', $this->user->id)
            ->where('status', 'approved')
            ->whereHas('course', fn($q) => $q->where('program_type', 'degree'))
            ->with('course')
            ->latest()
            ->first();

        if ($admission) {
            $this->activeCareer = $admission->course;
        }

        // 2. Cargar Tablas Pesadas (Inscripciones y Pagos)
        $this->loadStudentDataOptimized();
    }

    private function initEmptyCollections()
    {
        $this->activeDegreeEnrollments = collect();
        $this->activeCourseEnrollments = collect();
        $this->pendingEnrollments = collect();
        $this->completedEnrollments = collect();
        $this->pendingPayments = collect();
        $this->paymentHistory = collect();
    }

    private function loadStudentDataOptimized()
    {
        $allEnrollments = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher',
                'payment'
            ])
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->activeDegreeEnrollments = $allEnrollments->filter(function ($e) {
            $isDegree = optional($e->courseSchedule->module->course)->program_type === 'degree';
            $isActive = in_array(strtolower($e->status), ['cursando', 'activo', 'pendiente', 'pendiente pago', 'enrolled']);
            return $isDegree && $isActive;
        });

        $this->activeCourseEnrollments = $allEnrollments->filter(function ($e) {
            $courseType = optional($e->courseSchedule->module->course)->program_type;
            $isTechnical = $courseType !== 'degree';
            $isActive = in_array(strtolower($e->status), ['cursando', 'activo']);
            return $isTechnical && $isActive;
        });

        $this->pendingEnrollments = $allEnrollments->filter(function ($e) {
            $courseType = optional($e->courseSchedule->module->course)->program_type;
            $isTechnical = $courseType !== 'degree';
            $isPending = in_array(strtolower($e->status), ['pendiente', 'enrolled', 'pendiente pago']);
            return $isTechnical && $isPending;
        });

        $this->completedEnrollments = $allEnrollments->filter(function ($e) {
            return in_array(strtolower($e->status), ['completado', 'aprobado']);
        });

        $allPayments = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module.course'])
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->pendingPayments = $allPayments->whereIn('status', ['Pendiente', 'pendiente']);
        $this->paymentHistory = $allPayments;
    }

    private function isIncomplete($value)
    {
        return empty($value) || strtoupper(trim($value)) === 'N/A';
    }

    private function sanitizeInput($value)
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if (empty($trimmed) || strtoupper($trimmed) === 'N/A') return null;
            return $trimmed;
        }
        return $value;
    }

    public function openProfileModal()
    {
        $this->showProfileModal = true;
        $this->dispatch('open-modal', 'complete-profile-modal');
    }

    // --- DEBUG: Hook que se ejecuta cuando Livewire termina de subir el archivo temporal ---
    public function updatedPhoto()
    {
        Log::info('--- [DEBUG BACKEND] Hook updatedPhoto DISPARADO ---');
        
        if ($this->photo) {
            try {
                // Verificar si es un array (subida múltiple por error) o un objeto UploadedFile
                if (is_array($this->photo)) {
                    Log::warning('[DEBUG BACKEND] $this->photo es un array (¿multiple?). Tomando el primero.');
                    $file = $this->photo[0];
                } else {
                    $file = $this->photo;
                }

                Log::info('[DEBUG BACKEND] Archivo temporal recibido:', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                    'temp_path' => $file->getRealPath(),
                    'livewire_temp_url' => $file->temporaryUrl(),
                ]);

                // Validación manual rápida para ver si falla aquí
                $this->validate(['photo' => 'image|max:10240']); // 10MB
                Log::info('[DEBUG BACKEND] Validación preliminar en updatedPhoto: OK');

            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('[DEBUG BACKEND] Validación falló en updatedPhoto: ', $e->errors());
            } catch (\Exception $e) {
                Log::error('[DEBUG BACKEND] Excepción en updatedPhoto: ' . $e->getMessage());
            }
        } else {
            Log::warning('[DEBUG BACKEND] updatedPhoto se ejecutó pero $this->photo es NULL/VACÍO.');
        }
    }

    public function saveProfile()
    {
        Log::info('--- [DEBUG BACKEND] Acción saveProfile INICIADA ---');
        Log::info('Datos actuales antes de validar:', [
            'photo_exists' => !empty($this->photo),
            'mobile_phone' => $this->mobile_phone
        ]);

        try {
            $this->validate([
                'mobile_phone' => 'nullable|string|max:20',
                'birth_date' => 'nullable|date|before:today',
                'address' => 'nullable|string|max:255',
                'gender' => 'nullable|in:Masculino,Femenino,Otro',
                'city' => 'nullable|string|max:100',
                'sector' => 'nullable|string|max:100',
                'photo' => 'nullable|image|max:10240', // Validación de imagen (máx 10MB para debug)
            ]);
            Log::info('[DEBUG BACKEND] Validación saveProfile: OK');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('[DEBUG BACKEND] Error de Validación en saveProfile:', $e->errors());
            // Es vital re-lanzar esto para que Livewire muestre los errores en el frontend
            throw $e;
        }

        $dataToUpdate = [
            'mobile_phone' => $this->sanitizeInput($this->mobile_phone),
            'birth_date' => $this->sanitizeInput($this->birth_date),
            'address' => $this->sanitizeInput($this->address),
            'gender' => $this->sanitizeInput($this->gender),
            'city' => $this->sanitizeInput($this->city),
            'sector' => $this->sanitizeInput($this->sector),
        ];

        // Procesar nueva foto si se subió
        if ($this->photo) {
            Log::info('[DEBUG BACKEND] Procesando guardado de imagen...');
            try {
                // Borrar foto anterior si existe y no es la default
                if ($this->student->profile_photo_path && Storage::disk('public')->exists($this->student->profile_photo_path)) {
                    Log::info('[DEBUG BACKEND] Eliminando foto antigua: ' . $this->student->profile_photo_path);
                    Storage::disk('public')->delete($this->student->profile_photo_path);
                }
                
                // Guardar nueva foto en 'profile-photos' dentro de 'public'
                $path = $this->photo->store('profile-photos', 'public');
                Log::info('[DEBUG BACKEND] Foto guardada exitosamente en DISCO: ' . $path);
                
                $dataToUpdate['profile_photo_path'] = $path;
            } catch (\Exception $e) {
                Log::error('[DEBUG BACKEND] CRITICAL ERROR guardando en Storage: ' . $e->getMessage());
                session()->flash('error', 'Error interno al guardar la imagen. Revisa los logs.');
                return;
            }
        } else {
            Log::info('[DEBUG BACKEND] No hay foto nueva para guardar en esta petición.');
        }

        if ($this->student) {
            if ($this->isIncomplete($this->student->phone) || empty($this->student->phone)) {
                $dataToUpdate['phone'] = $dataToUpdate['mobile_phone'];
            }
            
            Log::info('[DEBUG BACKEND] Actualizando registro Student en DB...', $dataToUpdate);
            $this->student->update($dataToUpdate);
            $this->student->refresh();
            
            // Actualizar propiedades locales para reflejar cambios en la vista
            $this->mobile_phone = $this->student->mobile_phone;
            $this->address = $this->student->address;
            $this->current_photo_url = $this->student->profile_photo_url; // Refrescar URL
            $this->photo = null; // Limpiar input temporal

            session()->flash('message', 'Perfil actualizado exitosamente.');
            Log::info('[DEBUG BACKEND] Proceso finalizado correctamente.');
        }
        $this->closeProfileModal();
    }

    public function closeProfileModal()
    {
        $this->showProfileModal = false;
        $this->photo = null; // Limpiar imagen temporal si cancela
        $this->dispatch('close-modal', 'complete-profile-modal');
        session()->put('profile_onboarding_seen', true);
    }

    // --- Lógica del Modal de Inscripción ---
    public function openEnrollmentModal()
    {
        $this->reset('searchAvailableCourse', 'selectedScheduleId', 'availableSchedules');
        $this->dispatch('open-modal', 'enroll-student-modal');
    }

    public function updatedSearchAvailableCourse()
    {
        if (strlen($this->searchAvailableCourse) > 2) {
            $this->availableSchedules = CourseSchedule::with([
                    'module:id,course_id,name,code',
                    'module.course:id,name',
                    'teacher:id,first_name,last_name'
                ])
                ->where('status', 'Activo')
                ->where(function($q) {
                    $q->whereHas('module', function($q2) {
                        $q2->where('name', 'like', '%' . $this->searchAvailableCourse . '%')
                           ->orWhere('code', 'like', '%' . $this->searchAvailableCourse . '%');
                    })
                    ->orWhereHas('module.course', function($q3) {
                        $q3->where('name', 'like', '%' . $this->searchAvailableCourse . '%');
                    });
                })
                ->take(10)
                ->get();
        } else {
            $this->availableSchedules = [];
        }
    }

    public function enrollStudent()
    {
        $this->validate(['selectedScheduleId' => 'required|exists:course_schedules,id']);
        
        $schedule = CourseSchedule::with('module.course')->find($this->selectedScheduleId);
        
        $isDegree = $schedule->module->course->program_type === 'degree';
        $initialStatus = $isDegree ? 'Cursando' : 'Pendiente';

        Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $schedule->id,
            'status' => $initialStatus,
            'enrollment_date' => now(),
        ]);
        
        if ($isDegree) {
             session()->flash('message', 'Materia inscrita correctamente.');
        } else {
             session()->flash('message', 'Solicitud creada. Proceda al pago.');
        }
        
        $this->dispatch('close-modal', 'enroll-student-modal');
        $this->loadStudentDataOptimized(); 
    }
    
    public function confirmUnenroll($id) {}

    public function render()
    {
        // --- DEBUG DE ERRORES SILENCIOSOS ---
        // Si la subida falla por tamaño o tipo, Livewire a veces no tira excepción, sino que llena el ErrorBag
        if($this->getErrorBag()->isNotEmpty()){
            Log::error('[DEBUG BACKEND] RENDER DETECTÓ ERRORES EN BAG:', $this->getErrorBag()->toArray());
        }
        // ------------------------------------

        return view('livewire.student-portal.dashboard');
    }
}