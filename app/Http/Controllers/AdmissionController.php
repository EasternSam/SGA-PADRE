<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Notifications\SystemNotification;
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

        $query = Admission::with(['course', 'user']);

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

        $courses = Course::orderBy('name')->get(['id', 'name', 'code', 'registration_fee']);
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

        // Si todos están aprobados, podemos proceder a aprobar
        $allApproved = !empty($docStatus) && !in_array('pending', $docStatus) && !in_array('rejected', $docStatus);
        $hasRejection = in_array('rejected', $docStatus);

        if ($allApproved) {
            return $this->approve($request, $admission);
        } elseif ($hasRejection) {
            $admission->status = 'rejected';
            $admission->save();

            // Notificar rechazo
            $rejectedDocs = [];
            foreach ($docStatus as $key => $st) {
                if ($st === 'rejected') {
                    $rejectedDocs[] = ucwords(str_replace('_', ' ', $key));
                }
            }
            $listaDocs = implode(', ', $rejectedDocs);
            $msg = "Tu solicitud requiere correcciones. Documentos observados: {$listaDocs}. Notas: " . ($validated['notes'] ?? '');

            if ($admission->user) {
                try {
                    $admission->user->notify(new SystemNotification('Acción Requerida: Admisión', $msg, 'danger', '/portal'));
                } catch (\Exception $e) {
                    Log::warning('No se pudo enviar notificación de rechazo: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', 'Solicitud marcada con observaciones y aspirante notificado.');
        }

        $admission->save();
        return redirect()->back()->with('success', 'Revisión de documentos guardada exitosamente.');
    }

    /**
     * Aprueba oficialmente la admisión y matricula al aspirante como Estudiante.
     */
    public function approve(Request $request, Admission $admission)
    {
        try {
            DB::transaction(function () use ($admission, $request) {
                // 1. Obtener o crear Usuario
                $user = null;
                if ($admission->user_id) {
                    $user = User::find($admission->user_id);
                }
                if (!$user) {
                    $user = User::where('email', $admission->email)->first();
                }

                if (!$user) {
                    $password = $admission->identification_id ?: '12345678';
                    $user = User::withoutEvents(function () use ($admission, $password) {
                        return User::create([
                            'name' => trim("{$admission->first_name} {$admission->last_name}"),
                            'email' => $admission->email,
                            'password' => Hash::make($password),
                            'access_expires_at' => now()->addMonths(6),
                            'must_change_password' => true,
                        ]);
                    });
                }

                // 2. Asignar rol Estudiante
                if ($user->hasRole('Solicitante')) {
                    $user->removeRole('Solicitante');
                }
                if (!$user->hasRole('Estudiante')) {
                    $user->assignRole('Estudiante');
                }

                // 3. Crear o actualizar registro de Estudiante
                $student = Student::where('user_id', $user->id)->first();
                $preMatricula = 'PRE-' . date('y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

                if (!$student) {
                    $student = Student::withoutEvents(function () use ($user, $admission, $preMatricula) {
                        return Student::create([
                            'user_id' => $user->id,
                            'first_name' => $admission->first_name,
                            'last_name' => $admission->last_name,
                            'email' => $admission->email,
                            'cedula' => $admission->identification_id,
                            'status' => 'Prospecto',
                            'phone' => $admission->phone,
                            'birth_date' => $admission->birth_date,
                            'address' => $admission->address,
                            'enrollment_date' => now(),
                            'course_id' => $admission->course_id,
                            'student_code' => $preMatricula,
                        ]);
                    });
                } else {
                    $student->update([
                        'course_id' => $admission->course_id,
                        'status' => 'Prospecto',
                    ]);
                }

                // 4. Generar cobro de inscripción si aplica
                if ($admission->course_id) {
                    $course = Course::find($admission->course_id);
                    if ($course && $course->registration_fee > 0) {
                        $concept = PaymentConcept::firstOrCreate(
                            ['name' => 'Inscripción'],
                            ['description' => 'Pago de admisión institucional', 'amount' => $course->registration_fee]
                        );

                        $exists = Payment::where('student_id', $student->id)
                            ->where('payment_concept_id', $concept->id)
                            ->where('status', 'Pendiente')
                            ->exists();

                        if (!$exists) {
                            Payment::withoutEvents(function () use ($user, $student, $concept, $course) {
                                Payment::create([
                                    'user_id' => $user->id,
                                    'student_id' => $student->id,
                                    'payment_concept_id' => $concept->id,
                                    'amount' => $course->registration_fee,
                                    'currency' => 'DOP',
                                    'status' => 'Pendiente',
                                    'gateway' => 'Sistema',
                                    'due_date' => now()->addDays(7),
                                    'notes' => 'Generado automáticamente al aprobar admisión.',
                                ]);
                            });
                        }
                    }
                }

                // 5. Actualizar estado de la admisión
                $docStatus = $admission->document_status ?? [];
                if (!empty($docStatus)) {
                    foreach ($docStatus as $k => $v) {
                        $docStatus[$k] = 'approved';
                    }
                }

                $notes = $admission->notes . "\n[Aprobado el " . now()->format('d/m/Y H:i') . "]";
                if ($request->filled('notes')) {
                    $notes = $request->input('notes') . "\n" . $notes;
                }

                $admission->update([
                    'status' => 'approved',
                    'document_status' => $docStatus,
                    'notes' => $notes,
                    'user_id' => $user->id,
                ]);

                // 6. Notificar al estudiante
                try {
                    $user->notify(new SystemNotification(
                        '¡Admisión Aprobada!',
                        'Tu solicitud de admisión ha sido aprobada. Se ha habilitado tu acceso al SGA institucional.',
                        'success',
                        '/portal'
                    ));
                } catch (\Exception $e) {
                    Log::warning('No se pudo enviar notificación de aprobación: ' . $e->getMessage());
                }
            });

            return redirect()->back()->with('success', '¡Admisión aprobada exitosamente y usuario formalizado!');
        } catch (\Exception $e) {
            Log::error('Error al aprobar admisión: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Error al procesar la admisión: ' . $e->getMessage()]);
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
                    '/portal'
                ));
            } catch (\Exception $e) {}
        }

        return redirect()->back()->with('success', 'Solicitud rechazada correctamente.');
    }
}
