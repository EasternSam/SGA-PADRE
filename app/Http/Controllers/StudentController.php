<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\CedulaLookupService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Directorio y búsqueda de estudiantes con métricas y paginación rápida.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $courseId = $request->input('course_id', 'all');

        $query = Student::query()
            ->with([
                'user:id,name,email,kiosk_pin',
                'course:id,name,code',
                'scholarship:id,name,discount_percentage'
            ]);

        if (!empty($search)) {
            $tokens = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $query->where(function ($q) use ($tokens) {
                foreach ($tokens as $token) {
                    $q->where(function ($sub) use ($token) {
                        $sub->where('first_name', 'like', "%{$token}%")
                            ->orWhere('last_name', 'like', "%{$token}%")
                            ->orWhere('cedula', 'like', "%{$token}%")
                            ->orWhere('student_code', 'like', "%{$token}%")
                            ->orWhere('email', 'like', "%{$token}%")
                            ->orWhere('mobile_phone', 'like', "%{$token}%");
                    });
                }
            });
        }

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        if ($courseId !== 'all' && !empty($courseId)) {
            $query->where('course_id', $courseId);
        }

        $students = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Métricas clave para los KPI cards
        $metrics = [
            'total' => Student::count(),
            'active' => Student::where('status', 'Activo')->count(),
            'scholarships' => Student::whereNotNull('scholarship_id')->count(),
            'with_debt' => Payment::where('status', 'Pendiente')->distinct('student_id')->count('student_id'),
        ];

        $allCourses = Course::select('id', 'name', 'code')->orderBy('name')->get();
        $allScholarships = Scholarship::where('is_active', true)->select('id', 'name', 'discount_percentage')->orderBy('name')->get();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'course_id' => $courseId,
            ],
            'metrics' => $metrics,
            'allCourses' => $allCourses,
            'allScholarships' => $allScholarships,
        ]);
    }

    /**
     * Registro de nuevo estudiante con usuario web y PIN de kiosco.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'cedula' => 'required|string|max:20|unique:students,cedula',
            'email' => 'required|email|max:255|unique:students,email|unique:users,email',
            'mobile_phone' => 'required|string|max:20',
            'home_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'sector' => 'nullable|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'how_found' => 'nullable|string|max:255',
            'course_id' => 'nullable|exists:courses,id',
            'scholarship_id' => 'nullable|exists:scholarships,id',
            'is_minor' => 'boolean',
            'tutor_name' => 'required_if:is_minor,true|nullable|string|max:255',
            'tutor_cedula' => 'nullable|string|max:20',
            'tutor_phone' => 'required_if:is_minor,true|nullable|string|max:20',
            'tutor_relationship' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8',
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.unique' => 'Esta cédula ya está registrada en el sistema.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya pertenece a otro usuario o estudiante.',
            'mobile_phone.required' => 'El teléfono móvil es obligatorio.',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'tutor_name.required_if' => 'El nombre del tutor es requerido si el estudiante es menor.',
            'tutor_phone.required_if' => 'El teléfono del tutor es requerido si el estudiante es menor.',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $cleanCedula = preg_replace('/[^0-9]/', '', $validated['cedula']);
                $initialPassword = !empty($validated['password']) ? $validated['password'] : $cleanCedula;
                $kioskPin = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

                // Crear usuario con rol Estudiante
                $user = User::create([
                    'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                    'email' => $validated['email'],
                    'password' => Hash::make($initialPassword),
                    'kiosk_pin' => $kioskPin,
                    'access_expires_at' => Carbon::now()->addMonths(6),
                ]);

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('Estudiante');
                }

                // Generar código de estudiante si no viene dado
                $year = date('Y');
                $randomDigits = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $studentCode = "EST-{$year}-{$randomDigits}";

                $studentData = [
                    'user_id' => $user->id,
                    'student_code' => $studentCode,
                    'course_id' => $validated['course_id'] ?? null,
                    'scholarship_id' => $validated['scholarship_id'] ?? null,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'cedula' => $validated['cedula'],
                    'email' => $validated['email'],
                    'mobile_phone' => $validated['mobile_phone'],
                    'home_phone' => $validated['home_phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'sector' => $validated['sector'] ?? null,
                    'birth_date' => $validated['birth_date'],
                    'gender' => $validated['gender'] ?? null,
                    'nationality' => $validated['nationality'] ?? 'Dominicana',
                    'how_found' => $validated['how_found'] ?? null,
                    'status' => 'Activo',
                    'balance' => 0.00,
                    'is_minor' => (bool)($validated['is_minor'] ?? false),
                    'tutor_name' => !empty($validated['is_minor']) ? ($validated['tutor_name'] ?? null) : null,
                    'tutor_cedula' => !empty($validated['is_minor']) ? ($validated['tutor_cedula'] ?? null) : null,
                    'tutor_phone' => !empty($validated['is_minor']) ? ($validated['tutor_phone'] ?? null) : null,
                    'tutor_relationship' => !empty($validated['is_minor']) ? ($validated['tutor_relationship'] ?? null) : null,
                ];

                $student = Student::create($studentData);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Creación de Estudiante',
                    'description' => "Se registró nuevo estudiante {$student->full_name} ({$student->student_code})",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Estudiante registrado exitosamente con usuario web y PIN de Kiosco.');
        } catch (\Exception $e) {
            Log::error("Error registrando estudiante: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al registrar estudiante: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualización de datos del estudiante.
     */
    public function update(Request $request, Student $student)
    {
        $userId = $student->user_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'cedula' => [
                'required', 'string', 'max:20',
                Rule::unique('students', 'cedula')->ignore($student->id)
            ],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('students', 'email')->ignore($student->id),
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'mobile_phone' => 'required|string|max:20',
            'home_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'sector' => 'nullable|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'how_found' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'rnc' => 'nullable|string|max:20',
            'course_id' => 'nullable|exists:courses,id',
            'scholarship_id' => 'nullable|exists:scholarships,id',
            'is_minor' => 'boolean',
            'tutor_name' => 'required_if:is_minor,true|nullable|string|max:255',
            'tutor_cedula' => 'nullable|string|max:20',
            'tutor_phone' => 'required_if:is_minor,true|nullable|string|max:20',
            'tutor_relationship' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8',
        ]);

        try {
            DB::transaction(function () use ($student, $validated, $request) {
                $student->update([
                    'course_id' => $validated['course_id'] ?? null,
                    'scholarship_id' => $validated['scholarship_id'] ?? null,
                    'rnc' => $validated['rnc'] ?? null,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'cedula' => $validated['cedula'],
                    'email' => $validated['email'],
                    'mobile_phone' => $validated['mobile_phone'],
                    'home_phone' => $validated['home_phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'sector' => $validated['sector'] ?? null,
                    'birth_date' => $validated['birth_date'],
                    'gender' => $validated['gender'] ?? null,
                    'nationality' => $validated['nationality'] ?? 'Dominicana',
                    'how_found' => $validated['how_found'] ?? null,
                    'status' => $validated['status'] ?? $student->status,
                    'is_minor' => (bool)($validated['is_minor'] ?? false),
                    'tutor_name' => !empty($validated['is_minor']) ? ($validated['tutor_name'] ?? null) : null,
                    'tutor_cedula' => !empty($validated['is_minor']) ? ($validated['tutor_cedula'] ?? null) : null,
                    'tutor_phone' => !empty($validated['is_minor']) ? ($validated['tutor_phone'] ?? null) : null,
                    'tutor_relationship' => !empty($validated['is_minor']) ? ($validated['tutor_relationship'] ?? null) : null,
                ]);

                if ($student->user) {
                    $userData = [
                        'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                    ];

                    if (!Str::endsWith($student->user->getOriginal('email'), '@centu.edu.do')) {
                        $userData['email'] = $validated['email'];
                    }

                    if (!empty($validated['password'])) {
                        $userData['password'] = Hash::make($validated['password']);
                    }

                    $student->user->update($userData);
                }

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Actualización de Estudiante',
                    'description' => "Se actualizaron los datos del estudiante {$student->full_name} (ID #{$student->id})",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Ficha del estudiante actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error actualizando estudiante: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al actualizar estudiante: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminación segura de estudiante.
     */
    public function destroy(Request $request, Student $student)
    {
        try {
            $hasActiveEnrollments = $student->enrollments()
                ->whereIn('status', ['Activo', 'Cursando', 'Completado'])
                ->exists();

            $hasPaidPayments = $student->payments()
                ->where('status', 'Pagado')
                ->exists();

            if ($hasActiveEnrollments || $hasPaidPayments) {
                return redirect()->back()->withErrors([
                    'error' => 'No se puede eliminar el estudiante porque posee inscripciones activas o pagos registrados.'
                ]);
            }

            DB::transaction(function () use ($student, $request) {
                $studentName = $student->full_name;
                $user = $student->user;

                // Eliminar inscripciones y pagos pendientes
                $student->payments()->delete();
                $student->enrollments()->delete();
                $student->delete();

                if ($user) {
                    $user->delete();
                }

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Eliminación de Estudiante',
                    'description' => "Se eliminó al estudiante {$studentName} y su usuario asociado",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->route('admin.students.index')->with('success', 'Estudiante eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al eliminar estudiante: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al eliminar el estudiante: ' . $e->getMessage()]);
        }
    }

    /**
     * Regeneración instantánea del PIN de Kiosco.
     */
    public function resetKioskPin(Student $student)
    {
        try {
            if (!$student->user) {
                return redirect()->back()->withErrors(['error' => 'El estudiante no posee una cuenta de usuario vinculada.']);
            }

            $newPin = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $student->user->kiosk_pin = $newPin;
            $student->user->save();

            return redirect()->back()->with('success', "PIN de Kiosco regenerado con éxito: {$newPin}");
        } catch (\Exception $e) {
            Log::error("Error reseteando PIN de kiosco: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'No se pudo regenerar el PIN: ' . $e->getMessage()]);
        }
    }

    /**
     * Búsqueda en vivo de cédula en padrón electoral de la JCE.
     */
    public function lookupCedula(Request $request)
    {
        $cedula = $request->input('cedula', '');
        $service = app(CedulaLookupService::class);
        $result = $service->lookup($cedula);

        return response()->json($result);
    }

    /**
     * Perfil Integral 360° del Estudiante (Inscripciones, Pagos, Expediente y Documentos).
     */
    public function profile(Student $student)
    {
        $student->load([
            'user:id,name,email,kiosk_pin,created_at',
            'course:id,name,code,registration_fee',
            'scholarship:id,name,discount_percentage,description',
        ]);

        // Inscripciones agrupadas por estado
        $allEnrollments = $student->enrollments()
            ->with([
                'courseSchedule.module.course',
                'courseSchedule.teacher:id,name,email',
                'courseSchedule.classroom:id,name',
                'payment.paymentConcept',
                'individualPayments.paymentConcept',
            ])
            ->orderBy('id', 'desc')
            ->get();

        $pendingEnrollments = $allEnrollments->filter(fn($e) => in_array(strtolower($e->status), ['pendiente', 'enrolled']))->values();
        $activeEnrollments = $allEnrollments->filter(fn($e) => in_array(strtolower($e->status), ['activo', 'cursando']))->values();
        $completedEnrollments = $allEnrollments->filter(fn($e) => in_array(strtolower($e->status), ['completado', 'aprobado']))->values();
        $withdrawnEnrollments = $allEnrollments->filter(fn($e) => in_array(strtolower($e->status), ['retirado']))->values();

        // Historial completo de pagos
        $allPayments = $student->payments()
            ->with([
                'paymentConcept:id,name',
                'user:id,name',
                'enrollment.courseSchedule.module.course',
            ])
            ->orderBy('id', 'desc')
            ->get();

        // Resumen financiero consolidado
        $totalInvoiced = (float) $allPayments->where('status', '!=', 'Cancelado')->sum('amount');
        $totalPaid = (float) $allPayments->where('status', 'Pagado')->sum('amount');
        $totalPending = (float) $allPayments->where('status', 'Pendiente')->sum('amount');

        $allCourses = Course::select('id', 'name', 'code')->orderBy('name')->get();
        $allScholarships = Scholarship::where('is_active', true)->select('id', 'name', 'discount_percentage')->orderBy('name')->get();

        return Inertia::render('Students/Profile', [
            'student' => $student,
            'enrollments' => [
                'all' => $allEnrollments,
                'pending' => $pendingEnrollments,
                'active' => $activeEnrollments,
                'completed' => $completedEnrollments,
                'withdrawn' => $withdrawnEnrollments,
            ],
            'payments' => $allPayments,
            'financialSummary' => [
                'totalInvoiced' => $totalInvoiced,
                'totalPaid' => $totalPaid,
                'totalPending' => $totalPending,
                'balance' => (float) ($student->balance ?? $totalPending),
            ],
            'allCourses' => $allCourses,
            'allScholarships' => $allScholarships,
        ]);
    }

    /**
     * Búsqueda en vivo de secciones y horarios disponibles para inscripción de este alumno.
     */
    public function availableSchedules(Request $request, Student $student)
    {
        $term = trim($request->input('q', ''));

        if (empty($term)) {
            return response()->json([]);
        }

        $schedules = CourseSchedule::with([
                'module.course',
                'teacher:id,name,email',
                'classroom:id,name'
            ])
            ->where(function ($query) use ($term) {
                $query->where('section_name', 'like', "%{$term}%")
                    ->orWhereHas('module', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")
                          ->orWhereHas('course', function ($sq) use ($term) {
                              $sq->where('name', 'like', "%{$term}%")
                                 ->orWhere('code', 'like', "%{$term}%");
                          });
                    });
            })
            ->whereDoesntHave('enrollments', function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->whereIn('status', ['Activo', 'Cursando', 'Pendiente']);
            })
            ->limit(20)
            ->get()
            ->map(function ($sch) {
                $enrolledCount = $sch->enrollments()->whereIn('status', ['Activo', 'Cursando', 'Pendiente'])->count();
                $spots = $sch->capacity ? max(0, $sch->capacity - $enrolledCount) : 99;
                return [
                    'id' => $sch->id,
                    'section_name' => $sch->section_name ?: "Sección #{$sch->id}",
                    'course_name' => $sch->module?->course?->name ?? 'Curso General',
                    'module_name' => $sch->module?->name ?? 'Módulo General',
                    'registration_fee' => (float) ($sch->module?->course?->registration_fee ?? 0),
                    'module_price' => (float) ($sch->module?->price ?? 0),
                    'teacher_name' => $sch->teacher?->name ?? 'Por asignar',
                    'classroom_name' => $sch->classroom?->name ?? 'Virtual / Por asignar',
                    'days' => $sch->days_of_week ?? [],
                    'start_time' => $sch->start_time ? Carbon::parse($sch->start_time)->format('h:i A') : '',
                    'end_time' => $sch->end_time ? Carbon::parse($sch->end_time)->format('h:i A') : '',
                    'start_date' => $sch->start_date ? Carbon::parse($sch->start_date)->format('d/m/Y') : '',
                    'capacity' => $sch->capacity,
                    'enrolled_count' => $enrolledCount,
                    'available_spots' => $spots,
                    'is_full' => $sch->capacity && $enrolledCount >= $sch->capacity,
                ];
            });

        return response()->json($schedules);
    }

    /**
     * Búsqueda de horarios alternativos para cambio de sección dentro del mismo módulo.
     */
    public function availableSchedulesForChange(Request $request, Student $student, Enrollment $enrollment)
    {
        $moduleId = $enrollment->courseSchedule?->module_id;
        if (!$moduleId) {
            return response()->json([]);
        }

        $schedules = CourseSchedule::with(['teacher:id,name', 'classroom:id,name'])
            ->where('module_id', $moduleId)
            ->where('id', '!=', $enrollment->course_schedule_id)
            ->get()
            ->map(function ($sch) {
                $enrolledCount = $sch->enrollments()->whereIn('status', ['Activo', 'Cursando', 'Pendiente'])->count();
                $spots = $sch->capacity ? max(0, $sch->capacity - $enrolledCount) : 99;
                return [
                    'id' => $sch->id,
                    'section_name' => $sch->section_name ?: "Sección #{$sch->id}",
                    'teacher_name' => $sch->teacher?->name ?? 'Sin profesor',
                    'classroom_name' => $sch->classroom?->name ?? 'Sin aula',
                    'days' => $sch->days_of_week ?? [],
                    'start_time' => $sch->start_time ? Carbon::parse($sch->start_time)->format('h:i A') : '',
                    'end_time' => $sch->end_time ? Carbon::parse($sch->end_time)->format('h:i A') : '',
                    'available_spots' => $spots,
                    'is_full' => $sch->capacity && $enrolledCount >= $sch->capacity,
                ];
            })
            ->filter(fn($sch) => !$sch['is_full'])
            ->values();

        return response()->json($schedules);
    }

    /**
     * Inscribir estudiante en sección seleccionada y generar orden de cobro de inscripción.
     */
    public function enroll(Request $request, Student $student)
    {
        $request->validate([
            'course_schedule_id' => 'required|exists:course_schedules,id',
        ]);

        $schedule = CourseSchedule::with('module.course')->findOrFail($request->course_schedule_id);

        $enrolledCount = Enrollment::where('course_schedule_id', $schedule->id)
            ->whereIn('status', ['Activo', 'Cursando', 'Pendiente'])
            ->count();

        if ($schedule->capacity !== null && $schedule->capacity > 0 && $enrolledCount >= $schedule->capacity) {
            return redirect()->back()->withErrors(['error' => 'La sección seleccionada ya no tiene cupos disponibles.']);
        }

        $exists = Enrollment::where('student_id', $student->id)
            ->where('course_schedule_id', $schedule->id)
            ->whereIn('status', ['Activo', 'Cursando', 'Pendiente'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['error' => 'El estudiante ya se encuentra inscrito en esta sección.']);
        }

        try {
            DB::transaction(function () use ($student, $schedule, $request) {
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Pendiente',
                ]);

                $concept = PaymentConcept::firstOrCreate(
                    ['name' => 'Inscripción'],
                    ['description' => 'Pago de inscripción oficial al curso']
                );

                $originalAmount = (float) ($schedule->module?->course?->registration_fee ?? 0);
                if ($originalAmount <= 0) {
                    $originalAmount = (float) ($schedule->module?->price ?? 0);
                }

                $discountAmount = 0;
                $finalAmount = $originalAmount;

                if ($student->scholarship && $student->scholarship->is_active) {
                    $pct = (float) $student->scholarship->discount_percentage;
                    $discountAmount = round($originalAmount * ($pct / 100), 2);
                    $finalAmount = max(0, $originalAmount - $discountAmount);
                }

                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $concept->id,
                    'amount' => $finalAmount,
                    'original_amount' => $originalAmount,
                    'discount_amount' => $discountAmount,
                    'currency' => 'DOP',
                    'status' => 'Pendiente',
                    'gateway' => 'Sistema',
                    'user_id' => Auth::id(),
                    'due_date' => Carbon::now()->addDays(3),
                ]);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Inscripción Académica',
                    'description' => "Se inscribió al estudiante {$student->full_name} en sección #{$schedule->id}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Inscripción procesada correctamente. Se generó el comprobante de cobro pendiente.');
        } catch (\Exception $e) {
            Log::error("Error inscribiendo alumno: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al procesar inscripción: ' . $e->getMessage()]);
        }
    }

    /**
     * Anular inscripción y cancelar cargos pendientes vinculados.
     */
    public function unenroll(Request $request, Student $student, Enrollment $enrollment)
    {
        if ($enrollment->student_id !== $student->id) {
            abort(403, 'Acceso denegado a esta inscripción.');
        }

        try {
            DB::transaction(function () use ($student, $enrollment, $request) {
                Payment::where('enrollment_id', $enrollment->id)
                    ->where('status', 'Pendiente')
                    ->delete();

                $enrollmentId = $enrollment->id;
                $enrollment->delete();

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Anulación de Inscripción',
                    'description' => "Se anuló manualmente la inscripción #{$enrollmentId} de {$student->full_name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Inscripción anulada y cargos pendientes removidos.');
        } catch (\Exception $e) {
            Log::error("Error al anular inscripción: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al anular inscripción: ' . $e->getMessage()]);
        }
    }

    /**
     * Cambio de sección para un módulo ya inscrito.
     */
    public function changeSection(Request $request, Student $student, Enrollment $enrollment)
    {
        if ($enrollment->student_id !== $student->id) {
            abort(403);
        }

        $request->validate([
            'new_course_schedule_id' => 'required|exists:course_schedules,id',
        ]);

        $newSchedule = CourseSchedule::findOrFail($request->new_course_schedule_id);

        if ($newSchedule->isFull()) {
            return redirect()->back()->withErrors(['error' => 'La sección de destino está completa sin cupos disponibles.']);
        }

        try {
            DB::transaction(function () use ($enrollment, $newSchedule, $student, $request) {
                $enrollment->update([
                    'course_schedule_id' => $newSchedule->id
                ]);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Cambio de Sección',
                    'description' => "Se cambió de sección al alumno {$student->full_name} a la sección #{$newSchedule->id}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Sección cambiada exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al cambiar sección: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al cambiar sección: ' . $e->getMessage()]);
        }
    }

    /**
     * Retiro académico de asignatura.
     */
    public function withdraw(Request $request, Student $student, Enrollment $enrollment)
    {
        if ($enrollment->student_id !== $student->id) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($enrollment, $student, $request) {
                $enrollment->update(['status' => 'Retirado']);

                // Cancelar cobros pendientes
                Payment::where('enrollment_id', $enrollment->id)
                    ->where('status', 'Pendiente')
                    ->update(['status' => 'Cancelado']);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Retiro Académico',
                    'description' => "Retiro académico del alumno {$student->full_name} en inscripción #{$enrollment->id}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Estudiante retirado de la materia. Se cancelaron los cobros pendientes.');
        } catch (\Exception $e) {
            Log::error("Error al retirar estudiante: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al procesar retiro: ' . $e->getMessage()]);
        }
    }

    /**
     * Reingreso a sección luego de un retiro.
     */
    public function reEnroll(Request $request, Student $student, Enrollment $enrollment)
    {
        if ($enrollment->student_id !== $student->id) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($enrollment, $student, $request) {
                $enrollment->update(['status' => 'Cursando']);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Reingreso Académico',
                    'description' => "Reingreso a clases del alumno {$student->full_name} en inscripción #{$enrollment->id}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Estudiante reingresado a la sección exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al reingresar estudiante: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al reingresar: ' . $e->getMessage()]);
        }
    }
}
