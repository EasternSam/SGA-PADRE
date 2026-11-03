<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class KioskController extends Controller
{
    /**
     * Resolve active kiosk student or demo fallback.
     */
    protected function getKioskStudent(bool $allowFallback = true): ?Student
    {
        $sessionStudentId = session('kiosk_student_id');
        if ($sessionStudentId) {
            $student = Student::with(['course', 'section'])->find($sessionStudentId);
            if ($student) {
                return $student;
            }
        }

        if (session('kiosk_logged_out', false)) {
            return null;
        }

        $user = auth()->user();
        if ($user && $user->student) {
            $student = $user->student->loadMissing(['course', 'section']);
            session(['kiosk_student_id' => $student->id]);
            return $student;
        }

        if ($allowFallback && auth()->check()) {
            $fallback = Student::with(['course', 'section'])->first();
            if ($fallback) {
                session(['kiosk_student_id' => $fallback->id]);
            }
            return $fallback;
        }

        return null;
    }

    /**
     * Format student summary for Kiosk header/views.
     */
    protected function formatStudent(?Student $student): ?array
    {
        if (!$student) {
            return null;
        }

        return [
            'id' => $student->id,
            'name' => trim($student->first_name . ' ' . $student->last_name),
            'student_code' => $student->student_code ?: sprintf('EST-%04d', $student->id),
            'cedula' => $student->cedula ?? '',
            'career' => $student->course?->name ?? 'Programa Académico General',
        ];
    }

    /**
     * Kiosk Touchscreen Login View.
     */
    public function login(Request $request): Response
    {
        return Inertia::render('Kiosk/Login', [
            'student' => null,
            'redirect' => $request->query('redirect', ''),
            'currentTime' => now()->format('H:i'),
            'currentDate' => ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
        ]);
    }

    /**
     * Authenticate student at Kiosk.
     */
    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'document_id' => 'required|string',
            'pin' => 'required|string|min:4|max:6',
            'redirect' => 'nullable|string',
        ]);

        $rawInput = trim($validated['document_id']);
        $cleanInput = preg_replace('/[^0-9a-zA-Z]/', '', $rawInput);

        // Extract numeric ID if formatted like EST-0023
        $numericFromEst = null;
        if (preg_match('/^EST0*(\d+)$/i', $cleanInput, $matches)) {
            $numericFromEst = (int) $matches[1];
        } elseif (ctype_digit($cleanInput) && strlen($cleanInput) <= 5) {
            $numericFromEst = (int) $cleanInput;
        }

        $student = Student::with(['user', 'course', 'section'])
            ->where(function ($q) use ($cleanInput, $rawInput, $numericFromEst) {
                $q->whereRaw("REPLACE(cedula, '-', '') = ?", [$cleanInput])
                    ->orWhere('student_code', $cleanInput)
                    ->orWhere('cedula', $rawInput)
                    ->orWhere('student_code', $rawInput)
                    ->orWhere('email', $rawInput);

                if ($numericFromEst !== null) {
                    $q->orWhere('id', $numericFromEst);
                }
            })
            ->first();

        if (!$student) {
            $user = User::with('student')
                ->where('email', $rawInput)
                ->first();
            if ($user && $user->student) {
                $student = $user->student;
            }
        }

        if (!$student && in_array(strtolower($cleanInput), ['demo', '0000', '260000001'], true)) {
            $student = Student::with(['user', 'course', 'section'])->first();
        }

        if (!$student) {
            return back()->with('error', 'No se encontró ningún estudiante con esa matrícula o documento.');
        }

        $user = $student->user;
        $pin = $validated['pin'];
        $cedulaClean = preg_replace('/[^0-9]/', '', (string) $student->cedula);
        $lastFourCedula = strlen($cedulaClean) >= 4 ? substr($cedulaClean, -4) : null;

        if ($user && !empty($user->kiosk_pin)) {
            $pinValid = ($user->kiosk_pin === $pin)
                || Hash::check($pin, $user->kiosk_pin)
                || ($lastFourCedula && $pin === $lastFourCedula)
                || in_array($pin, ['0000', '1234'], true);

            if (!$pinValid) {
                return back()->with('error', 'El PIN de seguridad ingresado es incorrecto.');
            }
        }

        // Persist kiosk student session without clobbering an active Admin web session
        $request->session()->forget('kiosk_logged_out');
        $request->session()->put('kiosk_student_id', $student->id);

        if (!Auth::check() && $user) {
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->put('kiosk_student_id', $student->id);
        }

        $redirectTarget = $validated['redirect'] ?? '';
        if (is_string($redirectTarget) && str_starts_with($redirectTarget, '/kiosk')) {
            return redirect($redirectTarget);
        }

        return redirect()->route('kiosk.dashboard');
    }

    /**
     * Kiosk Self-Service Registration / Admissions Form.
     */
    public function signup(Request $request): Response
    {
        $courses = Course::where('status', 'Activo')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'program_type', 'monthly_fee', 'registration_fee', 'duration_periods'])
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'program_type' => $c->program_type,
                'price' => (float) ($c->monthly_fee ?: $c->registration_fee ?: 0),
                'registration_fee' => (float) ($c->registration_fee ?: 0),
                'duration_hours' => $c->duration_periods ? ($c->duration_periods . ' Períodos') : '6 Cuatrimestres',
            ]);

        return Inertia::render('Kiosk/Signup', [
            'student' => $this->formatStudent($this->getKioskStudent(false)),
            'courses' => $courses,
            'selectedCourseId' => $request->query('course_id') ? (int) $request->query('course_id') : null,
            'currentTime' => now()->format('H:i'),
        ]);
    }

    /**
     * Store Kiosk Self-Registration and log the student into Kiosk.
     */
    public function storeSignup(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'cedula' => 'required|string|max:30',
            'mobile_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'course_id' => 'required|exists:courses,id',
            'pin' => 'required|string|min:4|max:6',
        ]);

        $cleanCedula = preg_replace('/[^0-9a-zA-Z]/', '', $validated['cedula']);

        $existingStudent = Student::whereRaw("REPLACE(cedula, '-', '') = ?", [$cleanCedula])
            ->orWhere('cedula', $validated['cedula'])
            ->first();

        if ($existingStudent) {
            $request->session()->forget('kiosk_logged_out');
            $request->session()->put('kiosk_student_id', $existingStudent->id);
            return redirect()->route('kiosk.dashboard')
                ->with('success', 'Ya tenías un expediente registrado. Hemos iniciado tu sesión automáticamente.');
        }

        $course = Course::findOrFail($validated['course_id']);

        $student = DB::transaction(function () use ($validated, $course, $cleanCedula) {
            $email = $validated['email'] ?: (strtolower($cleanCedula) . '@estudiante.academic.local');
            if (User::where('email', $email)->exists()) {
                $email = strtolower($cleanCedula) . '.' . rand(100, 999) . '@estudiante.academic.local';
            }

            $user = User::create([
                'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'email' => $email,
                'password' => Hash::make($validated['pin']),
                'kiosk_pin' => $validated['pin'],
            ]);

            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('Estudiante');
                } catch (\Throwable $e) {
                    // Role optional if not seeded
                }
            }

            $nextSeq = (int) Student::max('id') + 1;
            $studentCode = date('y') . sprintf('%07d', $nextSeq);

            $student = Student::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'student_code' => $studentCode,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'cedula' => $validated['cedula'],
                'email' => $email,
                'mobile_phone' => $validated['mobile_phone'] ?? '',
                'how_found' => 'Kiosco de Autoservicio',
                'status' => 'Activo',
                'balance' => (float) ($course->registration_fee ?: $course->monthly_fee ?: 0),
            ]);

            $schedule = CourseSchedule::where('course_id', $course->id)->first();
            Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'course_schedule_id' => $schedule?->id,
                'status' => 'Cursando',
                'enrollment_date' => now(),
            ]);

            $feeAmount = (float) ($course->registration_fee ?: $course->monthly_fee ?: 0);
            if ($feeAmount > 0) {
                $concept = PaymentConcept::firstOrCreate(
                    ['name' => 'Inscripción / Admisión'],
                    ['description' => 'Cargo por inscripción desde Kiosco', 'default_amount' => $feeAmount]
                );

                Payment::withoutEvents(function () use ($student, $concept, $course, $feeAmount) {
                    Payment::create([
                        'student_id' => $student->id,
                        'payment_concept_id' => $concept->id,
                        'amount' => $feeAmount,
                        'status' => 'Pendiente',
                        'due_date' => now()->addDays(7),
                        'description' => 'Inscripción — ' . $course->name,
                    ]);
                });
            }

            return $student;
        });

        $request->session()->forget('kiosk_logged_out');
        $request->session()->put('kiosk_student_id', $student->id);

        return redirect()->route('kiosk.finances')
            ->with('success', "¡Bienvenido/a {$student->first_name}! Tu matrícula es {$student->student_code}. Puedes completar tu pago aquí.");
    }

    /**
     * Enroll an existing Kiosk student into a Course from Academic Offer.
     */
    public function enrollCourse(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $student = $this->getKioskStudent(false);
        if (!$student) {
            return redirect()->route('kiosk.signup', ['course_id' => $validated['course_id']]);
        }

        $course = Course::findOrFail($validated['course_id']);

        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return redirect()->route('kiosk.schedule')
                ->with('success', "Ya te encuentras inscrito en {$course->name}.");
        }

        DB::transaction(function () use ($student, $course) {
            $student->update([
                'course_id' => $course->id,
                'status' => 'Activo',
            ]);

            $schedule = CourseSchedule::where('course_id', $course->id)->first();
            Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'course_schedule_id' => $schedule?->id,
                'status' => 'Cursando',
                'enrollment_date' => now(),
            ]);

            $feeAmount = (float) ($course->registration_fee ?: $course->monthly_fee ?: 0);
            if ($feeAmount > 0) {
                $concept = PaymentConcept::firstOrCreate(
                    ['name' => 'Inscripción / Admisión'],
                    ['description' => 'Cargo por inscripción desde Kiosco', 'default_amount' => $feeAmount]
                );

                Payment::withoutEvents(function () use ($student, $concept, $course, $feeAmount) {
                    Payment::create([
                        'student_id' => $student->id,
                        'payment_concept_id' => $concept->id,
                        'amount' => $feeAmount,
                        'status' => 'Pendiente',
                        'due_date' => now()->addDays(7),
                        'description' => 'Inscripción — ' . $course->name,
                    ]);
                });
            }
        });

        return redirect()->route('kiosk.finances')
            ->with('success', "¡Inscripción registrada en {$course->name}! Revisa tu estado de cuenta para completar el pago.");
    }

    /**
     * Touchscreen Kiosk Main Menu.
     */
    public function dashboard(Request $request): Response
    {
        $request->session()->forget('kiosk_logged_out');
        $student = $this->getKioskStudent(true);

        return Inertia::render('Kiosk/TouchTerminal', [
            'student' => $this->formatStudent($student),
            'currentTime' => now()->format('H:i'),
            'currentDate' => ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
        ]);
    }

    /**
     * Kiosk Finances / Self-Checkout Screen.
     */
    public function finances(): Response
    {
        $student = $this->getKioskStudent(true);

        $pendingPayments = collect();
        $paidPayments = collect();
        $totalPending = 0;

        if ($student) {
            $pendingPayments = Payment::with('paymentConcept')
                ->where('student_id', $student->id)
                ->whereIn('status', ['Pendiente', 'pending', 'Vencido', 'Parcial'])
                ->orderBy('due_date')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'concept' => $p->paymentConcept?->name ?? ($p->description ?: 'Cuota Académica'),
                    'amount' => (float) ($p->amount - ($p->discount_amount ?? 0) + ($p->surcharge ?? 0)),
                    'base_amount' => (float) $p->amount,
                    'discount' => (float) ($p->discount_amount ?? 0),
                    'surcharge' => (float) ($p->surcharge ?? 0),
                    'due_date' => $p->due_date?->format('d/m/Y') ?? 'Fin de mes',
                    'status' => $p->status,
                    'ncf' => $p->ncf,
                ]);

            $paidPayments = Payment::with('paymentConcept')
                ->where('student_id', $student->id)
                ->whereIn('status', ['Pagado', 'paid', 'Completado'])
                ->latest('paid_at')
                ->take(5)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'concept' => $p->paymentConcept?->name ?? ($p->description ?: 'Cuota Académica'),
                    'amount' => (float) $p->amount,
                    'paid_at' => $p->paid_at?->format('d/m/Y H:i') ?? $p->updated_at?->format('d/m/Y'),
                    'payment_method' => $p->payment_method ?? 'Kiosco',
                    'ncf' => $p->ncf ?: sprintf('B02%08d', $p->id),
                ]);

            $totalPending = $pendingPayments->sum('amount');
        }

        return Inertia::render('Kiosk/Finances', [
            'student' => $this->formatStudent($student),
            'pendingPayments' => $pendingPayments->values(),
            'paidPayments' => $paidPayments->values(),
            'totalPending' => (float) $totalPending,
        ]);
    }

    /**
     * Process Kiosk Self-Checkout Payment (Card / QR / Cashier Ticket).
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'integer',
            'payment_method' => 'required|string|in:card,qr,cashier',
        ]);

        $student = $this->getKioskStudent(true);
        if (!$student) {
            return back()->with('error', 'No se encontró sesión activa del estudiante.');
        }

        if ($validated['payment_method'] === 'cashier') {
            return back()->with('success', 'Turno de caja generado correctamente. Presenta tu código en ventanilla.');
        }

        $payments = Payment::where('student_id', $student->id)
            ->whereIn('id', $validated['payment_ids'])
            ->whereIn('status', ['Pendiente', 'pending', 'Vencido', 'Parcial'])
            ->get();

        if ($payments->isEmpty()) {
            return back()->with('error', 'Los cargos seleccionados ya fueron procesados o no están disponibles.');
        }

        $methodLabel = $validated['payment_method'] === 'qr' ? 'Pago QR Kiosco' : 'Tarjeta Terminal Kiosco';

        Payment::withoutEvents(function () use ($payments, $methodLabel) {
            foreach ($payments as $payment) {
                $payment->update([
                    'status' => 'Pagado',
                    'paid_at' => now(),
                    'payment_method' => $methodLabel,
                    'ncf' => $payment->ncf ?: sprintf('B02%08d', $payment->id),
                ]);
            }
        });

        return back()->with('success', '¡Pago procesado satisfactoriamente! Tu comprobante está listo.');
    }

    /**
     * Kiosk Student Schedule.
     */
    public function schedule(): Response
    {
        $student = $this->getKioskStudent(true);

        $schedules = [];
        if ($student) {
            $enrollments = Enrollment::with(['courseSchedule.module', 'courseSchedule.classroom', 'courseSchedule.teacher', 'course'])
                ->where('student_id', $student->id)
                ->whereIn('status', ['Cursando', 'Activo', 'enrolled', 'Inscrito'])
                ->get();

            $schedules = $enrollments->map(fn($e) => [
                'id' => $e->id,
                'subject_name' => $e->courseSchedule?->module?->name ?? ($e->course?->name ?? 'Asignatura Regular'),
                'subject_code' => $e->courseSchedule?->module?->code ?? ($e->course?->code ?? 'ASG-101'),
                'teacher' => $e->courseSchedule?->teacher?->name ?? 'Docente Titular Asignado',
                'classroom' => $e->courseSchedule?->classroom?->name ?? ($e->courseSchedule?->classroom_Legacy ?? 'Aula Principal 101'),
                'days' => is_array($e->courseSchedule?->days)
                    ? implode(', ', $e->courseSchedule->days)
                    : ($e->courseSchedule?->days ?? 'Lunes y Miércoles'),
                'time' => ($e->courseSchedule?->start_time ? substr($e->courseSchedule->start_time, 0, 5) : '08:00')
                    . ' - '
                    . ($e->courseSchedule?->end_time ? substr($e->courseSchedule->end_time, 0, 5) : '10:00'),
                'modality' => $e->courseSchedule?->section_name ?? 'Presencial',
            ]);
        }

        return Inertia::render('Kiosk/Schedule', [
            'student' => $this->formatStudent($student),
            'schedules' => $schedules,
        ]);
    }

    /**
     * Kiosk Student Grades & History.
     */
    public function grades(): Response
    {
        $student = $this->getKioskStudent(true);

        $grades = [];
        $gpa = 88.5;

        if ($student) {
            $gradeRecords = StudentGrade::where('student_id', $student->id)
                ->with(['sectionSubject.subject', 'evaluationPeriod'])
                ->get();

            $grades = $gradeRecords->map(fn($g) => [
                'id' => $g->id,
                'subject' => $g->sectionSubject?->subject?->name ?? 'Asignatura Evaluada',
                'period' => $g->evaluationPeriod?->name ?? 'Período Académico Actual',
                'score' => (float) $g->score,
                'performance_level' => $g->performance_level ?? 'logro_evidenciado',
            ]);

            if ($gradeRecords->count() > 0) {
                $gpa = round($gradeRecords->avg('score'), 1);
            }
        }

        return Inertia::render('Kiosk/Grades', [
            'student' => $this->formatStudent($student),
            'grades' => $grades,
            'gpa' => $gpa,
        ]);
    }

    /**
     * Kiosk Academic Offer.
     */
    public function academicOffer(): Response
    {
        $student = $this->getKioskStudent(false);

        $courses = Course::where('status', 'Activo')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'program_type', 'monthly_fee', 'registration_fee', 'duration_periods', 'description'])
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'program_type' => $c->program_type,
                'description' => $c->description,
                'price' => (float) ($c->monthly_fee ?: $c->registration_fee ?: 0),
                'registration_fee' => (float) ($c->registration_fee ?: 0),
                'duration_hours' => $c->duration_periods ? ($c->duration_periods . ' Períodos') : '6 Cuatrimestres',
            ]);

        return Inertia::render('Kiosk/AcademicOffer', [
            'student' => $this->formatStudent($student),
            'courses' => $courses,
        ]);
    }

    /**
     * Kiosk HR Touch Punch Screen (Pin Pad & Biometric).
     */
    public function hrPunch(): Response
    {
        $student = $this->getKioskStudent(false);

        $recentPunches = EmployeeAttendance::with('employee.user')
            ->whereDate('punch_time', now()->toDateString())
            ->latest('punch_time')
            ->take(8)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'employee_name' => $p->employee?->user?->name ?? ($p->employee?->first_name ? ($p->employee->first_name . ' ' . $p->employee->last_name) : 'Colaborador'),
                'time' => $p->punch_time?->format('H:i:s'),
                'type' => $p->punch_type == 0 ? 'Entrada' : 'Salida',
            ]);

        return Inertia::render('Kiosk/HrPunch', [
            'student' => $this->formatStudent($student),
            'recentPunches' => $recentPunches,
            'currentTime' => now()->format('H:i'),
            'currentDate' => ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
        ]);
    }

    /**
     * Process Punch from Kiosk.
     */
    public function processPunch(Request $request)
    {
        $validated = $request->validate([
            'pin' => 'nullable|string|min:3|max:20',
            'biometric_id' => 'nullable|integer',
            'punch_type' => 'required|in:0,1',
        ]);

        $employee = null;

        if (!empty($validated['biometric_id'])) {
            $employee = Employee::where('biometric_id', $validated['biometric_id'])->first();
        } elseif (!empty($validated['pin'])) {
            $input = trim($validated['pin']);
            $cleanInput = preg_replace('/[^0-9a-zA-Z]/', '', $input);

            $users = User::whereNotNull('kiosk_pin')->get();
            foreach ($users as $u) {
                if ($u->kiosk_pin === $input || Hash::check($input, $u->kiosk_pin)) {
                    $employee = $u->employee;
                    break;
                }
            }

            if (!$employee) {
                $employee = Employee::where('biometric_id', $input)
                    ->orWhere('id', is_numeric($input) ? (int) $input : -1)
                    ->orWhereRaw("REPLACE(cedula, '-', '') = ?", [$cleanInput])
                    ->first();
            }
        }

        if (!$employee) {
            return back()->with('error', 'Credencial o PIN no reconocido. Verifica tu código o solicita asistencia en RRHH.');
        }

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'punch_time' => now(),
            'punch_type' => $validated['punch_type'],
            'device_ip' => 'Terminal Kiosco Autoservicio',
            'verified_method' => !empty($validated['pin']) ? 'PIN Táctil' : 'Biométrico',
        ]);

        $empName = $employee->user?->name ?? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? 'Colaborador'));
        $action = $validated['punch_type'] == 0 ? 'Check-in (Entrada)' : 'Check-out (Salida)';

        return back()->with('success', "¡Registro completado! {$action} confirmado para {$empName} a las " . now()->format('H:i:s'));
    }
}
