<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\DegreePlan;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\MatriculaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class AdmissionController extends Controller
{
    /**
     * Muestra el pipeline de admisiones con filtros y métricas en vivo.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');

        $query = Admission::with(['course', 'user.student']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('identification_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        $admissions = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $stats = [
            'total' => Admission::count(),
            'pending' => Admission::where('status', 'pending')->count(),
            'approved' => Admission::where('status', 'approved')->count(),
            'rejected' => Admission::where('status', 'rejected')->count(),
        ];

        $courses = Course::orderBy('name')->get(['id', 'name', 'code', 'program_type', 'registration_fee']);
        $admissionsOpen = in_array((string) Setting::get('admissions_open', '0'), ['1', 'true'], true);

        return Inertia::render('Admissions/Pipeline', [
            'admissions' => $admissions,
            'courses' => $courses,
            'admissionsOpen' => $admissionsOpen,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Activa o desactiva el modo de Admisiones Abiertas (controla la visibilidad de Selección de Materias en portal estudiantil).
     */
    public function toggleAdmissionsMode(Request $request)
    {
        $current = in_array((string) Setting::get('admissions_open', '0'), ['1', 'true'], true);
        $nextState = $request->has('open')
            ? filter_var($request->input('open'), FILTER_VALIDATE_BOOLEAN)
            : !$current;

        Setting::set('admissions_open', $nextState ? '1' : '0', 'academic', 'boolean');

        return redirect()->back()->with(
            'success',
            $nextState
                ? 'Modo de Admisiones Abiertas activado: Selección de Materias visible para los estudiantes.'
                : 'Modo de Admisiones Abiertas cerrado: Selección de Materias oculta para los estudiantes.'
        );
    }

    /**
     * Guarda la revisión de documentos y notas de una admisión.
     */
    public function saveReview(Request $request, Admission $admission)
    {
        $validated = $request->validate([
            'document_status' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $docStatus = $validated['document_status'] ?? ($admission->document_status ?? []);
        $admission->document_status = $docStatus;
        $admission->notes = $validated['notes'] ?? $admission->notes;

        $hasRejection = in_array('rejected', $docStatus, true);

        if ($hasRejection) {
            $admission->status = 'rejected';
            $admission->save();

            // Notificar rechazo
            $docLabels = [
                'birth_certificate' => 'Acta de Nacimiento',
                'id_card' => 'Cédula / Identidad',
                'high_school_record' => 'Récord de Bachillerato',
                'photo' => 'Fotografía 2x2',
                'medical_certificate' => 'Certificado Médico',
                'payment_receipt' => 'Recibo de Pago',
                'bachelor_certificate' => 'Certificado de Bachiller',
            ];
            $rejectedDocs = [];
            foreach ($docStatus as $key => $st) {
                if ($st === 'rejected') {
                    $rejectedDocs[] = $docLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
                }
            }
            $listaDocs = implode(', ', $rejectedDocs);
            $msg = "Tu solicitud requiere correcciones. Documentos observados: {$listaDocs}. Notas: " . ($validated['notes'] ?? '');

            if ($admission->user) {
                try {
                    $admission->user->notify(new SystemNotification('Acción Requerida: Admisión', $msg, 'danger', '/portal-aspirante'));
                } catch (\Exception $e) {
                    Log::warning('No se pudo enviar notificación de rechazo: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', 'Solicitud marcada con observaciones y aspirante notificado.');
        }

        if ($admission->status === 'rejected') {
            $admission->status = 'pending';
        }

        $admission->save();
        return redirect()->back()->with('success', 'Revisión de documentos guardada exitosamente.');
    }

    /**
     * Aprueba oficialmente la admisión y matricula al aspirante como Estudiante Activo en su Curso o Carrera.
     */
    public function approve(Request $request, Admission $admission)
    {
        try {
            DB::transaction(function () use ($admission, $request) {
                $courseId = $request->filled('course_id')
                    ? (int) $request->input('course_id')
                    : $admission->course_id;

                // 1. Obtener o crear Usuario
                $user = null;
                if ($admission->user_id) {
                    $user = User::find($admission->user_id);
                }
                if (!$user && !empty($admission->email)) {
                    $user = User::where('email', $admission->email)->first();
                }

                $cleanCedula = preg_replace('/[^0-9]/', '', (string) $admission->identification_id);
                $kioskPin = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

                if (!$user) {
                    $password = $cleanCedula ?: ($admission->identification_id ?: '12345678');
                    $user = User::withoutEvents(function () use ($admission, $password, $kioskPin) {
                        return User::create([
                            'name' => trim("{$admission->first_name} {$admission->last_name}"),
                            'email' => $admission->email,
                            'password' => Hash::make($password),
                            'kiosk_pin' => $kioskPin,
                            'access_expires_at' => null,
                            'must_change_password' => true,
                        ]);
                    });
                } else {
                    $updates = ['access_expires_at' => null];
                    if (empty($user->getRawOriginal('kiosk_pin'))) {
                        $updates['kiosk_pin'] = $kioskPin;
                    }
                    $user->update($updates);
                }

                // 2. Asignar rol Estudiante y remover Solicitante
                if ($user->hasRole('Solicitante')) {
                    $user->removeRole('Solicitante');
                }
                if (!$user->hasRole('Estudiante')) {
                    $user->assignRole('Estudiante');
                }

                // 3. Crear o actualizar registro de Estudiante con estado Activo y matrícula oficial
                $student = Student::where('user_id', $user->id)->first();
                if (!$student && !empty($admission->email)) {
                    $student = Student::where('email', $admission->email)->first();
                }
                if (!$student && !empty($admission->identification_id)) {
                    $student = Student::where('cedula', $admission->identification_id)->first();
                }

                $existingCode = (string) ($student?->student_code ?? '');
                $hasOfficialCode = $existingCode !== ''
                    && !str_starts_with($existingCode, 'PRE-')
                    && !str_starts_with($existingCode, 'TMP-');

                $studentCode = $hasOfficialCode
                    ? $existingCode
                    : app(MatriculaService::class)->generateUniqueStudentCode('[ADM]');

                $studentPayload = [
                    'user_id' => $user->id,
                    'first_name' => $admission->first_name,
                    'last_name' => $admission->last_name,
                    'email' => $admission->email ?: $user->email,
                    'cedula' => $admission->identification_id ?: $student?->cedula,
                    'mobile_phone' => $admission->phone ?: $student?->mobile_phone,
                    'birth_date' => $admission->birth_date ?: $student?->birth_date,
                    'address' => $admission->address ?: $student?->address,
                    'course_id' => $courseId,
                    'student_code' => $studentCode,
                    'status' => 'Activo',
                ];

                if (!$student) {
                    $student = Student::withoutEvents(function () use ($studentPayload) {
                        return Student::create($studentPayload);
                    });
                } else {
                    Student::withoutEvents(function () use ($student, $studentPayload) {
                        $student->update($studentPayload);
                    });
                }

                // 4. Inscribir automáticamente al estudiante en el Curso o Carrera y vincular orden de pago
                if ($courseId) {
                    $course = Course::with('modules.schedules')->find($courseId);
                    if ($course) {
                        $this->enrollStudentInCourse($student, $course, $user);
                    }
                }

                // 5. Actualizar estado de la admisión
                $docStatus = $request->input('document_status', $admission->document_status ?? []);
                if (is_array($docStatus) && !empty($docStatus)) {
                    foreach ($docStatus as $k => $v) {
                        $docStatus[$k] = 'approved';
                    }
                }

                $notes = trim((string) $admission->notes);
                if ($request->filled('notes')) {
                    $notes = trim((string) $request->input('notes'));
                }
                $notes = trim($notes . "\n[Aprobado el " . now()->format('d/m/Y H:i') . " — Matrícula: {$studentCode}]");

                $admission->update([
                    'status' => 'approved',
                    'course_id' => $courseId,
                    'document_status' => $docStatus,
                    'notes' => $notes,
                    'user_id' => $user->id,
                ]);

                // 6. Notificar al estudiante
                try {
                    $user->notify(new SystemNotification(
                        '¡Admisión Aprobada!',
                        "Tu solicitud de admisión ha sido aprobada con matrícula {$studentCode}. Ya estás inscrito en tu programa académico.",
                        'success',
                        '/student/dashboard'
                    ));
                } catch (\Exception $e) {
                    Log::warning('No se pudo enviar notificación de aprobación: ' . $e->getMessage());
                }
            });

            return redirect()->back()->with('success', '¡Admisión aprobada exitosamente! El estudiante ya se encuentra Activo e inscrito en su programa académico.');
        } catch (\Exception $e) {
            Log::error('Error al aprobar admisión: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Error al procesar la admisión: ' . $e->getMessage()]);
        }
    }

    /**
     * Inscribe formalmente al estudiante admitido en su Curso Técnico o Carrera Universitaria.
     */
    public function enrollStudentInCourse(Student $student, Course $course, User $user): void
    {
        if ($course->program_type === 'degree') {
            DegreePlan::firstOrCreate(
                ['student_id' => $student->id],
                ['pace' => 'normal', 'status' => 'active']
            );
        }

        // Limpiar inscripciones preliminares en otros cursos que no tengan pagos realizados
        $otherPendingEnrollments = Enrollment::where('student_id', $student->id)
            ->whereIn('status', ['Pendiente', 'Reservado'])
            ->whereHas('courseSchedule.module', fn($q) => $q->where('course_id', '!=', $course->id))
            ->get();

        foreach ($otherPendingEnrollments as $oldEnrollment) {
            if (!$oldEnrollment->is_paid) {
                Payment::where('enrollment_id', $oldEnrollment->id)
                    ->where('status', 'Pendiente')
                    ->delete();
                $oldEnrollment->delete();
            }
        }

        // Asegurar que el curso/carrera tenga al menos un módulo activo
        $modules = $course->modules()->orderBy('period_number')->orderBy('order')->orderBy('id')->get();
        if ($modules->isEmpty()) {
            $defaultModule = Module::create([
                'course_id' => $course->id,
                'name' => "Módulo 1: Fundamentos de {$course->name}",
                'code' => ($course->code ?: 'MOD') . '-01',
                'price' => $course->monthly_fee ?: 0,
                'duration_hours' => 40,
                'credits' => 3,
                'period_number' => 1,
                'order' => 1,
                'status' => 'Activo',
            ]);
            $modules = collect([$defaultModule]);
        }

        // Seleccionar los módulos iniciales a inscribir
        if ($course->program_type === 'degree') {
            $periodOneModules = $modules->where('period_number', 1)->values();
            $initialModules = $periodOneModules->isNotEmpty()
                ? $periodOneModules
                : collect([$modules->first()]);
        } else {
            $initialModules = collect([$modules->first()]);
        }

        $createdEnrollments = [];
        foreach ($initialModules as $module) {
            $schedule = CourseSchedule::where('module_id', $module->id)
                ->where('status', 'Activo')
                ->orderBy('id')
                ->first()
                ?? CourseSchedule::where('module_id', $module->id)->orderBy('id')->first();

            if (!$schedule) {
                $schedule = CourseSchedule::create([
                    'module_id' => $module->id,
                    'section_name' => 'Sección A - ' . ($module->code ?: $course->code ?: 'GEN'),
                    'days_of_week' => ['Lunes', 'Miércoles'],
                    'start_time' => '18:00:00',
                    'end_time' => '20:00:00',
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths(4)->toDateString(),
                    'capacity' => 30,
                    'modality' => 'Presencial',
                    'status' => 'Activo',
                ]);
            }

            $enrollment = Enrollment::withTrashed()
                ->where('student_id', $student->id)
                ->where('course_schedule_id', $schedule->id)
                ->first();

            if (!$enrollment) {
                $enrollment = Enrollment::withoutEvents(function () use ($student, $schedule) {
                    return Enrollment::create([
                        'student_id' => $student->id,
                        'course_schedule_id' => $schedule->id,
                        'status' => 'Cursando',
                        'next_billing_date' => now()->addMonth(),
                    ]);
                });
            } else {
                if (method_exists($enrollment, 'trashed') && $enrollment->trashed()) {
                    $enrollment->restore();
                }
                if ($enrollment->status !== 'Completado') {
                    $enrollment->update([
                        'status' => 'Cursando',
                        'next_billing_date' => $enrollment->next_billing_date ?: now()->addMonth(),
                    ]);
                }
            }

            $createdEnrollments[] = $enrollment;
        }

        $primaryEnrollment = $createdEnrollments[0] ?? null;

        // Verificar si ya tiene un pago de inscripción pagado o pendiente
        $paidInscription = Payment::where('student_id', $student->id)
            ->whereIn('status', ['Completado', 'Pagado', 'paid'])
            ->whereHas('paymentConcept', fn($q) => $q->where('name', 'like', '%Inscripci%'))
            ->latest()
            ->first();

        if ($paidInscription && $primaryEnrollment) {
            if (!$paidInscription->enrollment_id) {
                $paidInscription->update(['enrollment_id' => $primaryEnrollment->id]);
            }
            foreach ($createdEnrollments as $enr) {
                if (!$enr->payment_id) {
                    $enr->update(['payment_id' => $paidInscription->id]);
                }
            }
            return;
        }

        if ($course->registration_fee > 0) {
            $concept = PaymentConcept::firstOrCreate(
                ['name' => 'Inscripción'],
                ['description' => 'Pago de admisión institucional', 'amount' => $course->registration_fee]
            );

            $existingPending = Payment::where('student_id', $student->id)
                ->where('payment_concept_id', $concept->id)
                ->where('status', 'Pendiente')
                ->first();

            if (!$existingPending) {
                Payment::withoutEvents(function () use ($user, $student, $concept, $course, $primaryEnrollment) {
                    Payment::create([
                        'user_id' => $user->id,
                        'student_id' => $student->id,
                        'enrollment_id' => $primaryEnrollment?->id,
                        'payment_concept_id' => $concept->id,
                        'amount' => $course->registration_fee,
                        'original_amount' => $course->registration_fee,
                        'discount_amount' => 0,
                        'currency' => 'DOP',
                        'status' => 'Pendiente',
                        'gateway' => 'Sistema',
                        'due_date' => now()->addDays(7),
                        'notes' => 'Generado automáticamente al aprobar admisión.',
                    ]);
                });
            } elseif ($primaryEnrollment && !$existingPending->enrollment_id) {
                $existingPending->update(['enrollment_id' => $primaryEnrollment->id]);
            }
        }
    }

    /**
     * Rechaza una admisión con observaciones.
     */
    public function reject(Request $request, Admission $admission)
    {
        $validated = $request->validate([
            'notes' => 'required|string',
        ], [
            'notes.required' => 'Debe indicar el motivo de rechazo u observaciones.',
        ]);

        $admission->update([
            'status' => 'rejected',
            'notes' => $validated['notes'],
        ]);

        if ($admission->user) {
            try {
                $admission->user->notify(new SystemNotification(
                    'Solicitud de Admisión No Aprobada',
                    'Tu solicitud ha sido revisada con observaciones: ' . $validated['notes'],
                    'danger',
                    '/portal-aspirante'
                ));
            } catch (\Exception $e) {}
        }

        return redirect()->back()->with('success', 'Solicitud rechazada correctamente.');
    }
}
