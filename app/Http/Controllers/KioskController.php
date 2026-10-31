<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class KioskController extends Controller
{
    /**
     * Resolve active kiosk student or demo fallback.
     */
    protected function getKioskStudent(): ?Student
    {
        $user = auth()->user();
        if ($user && $user->student) {
            return $user->student;
        }

        return Student::with(['course', 'section'])->first();
    }

    /**
     * Kiosk Touchscreen Login View.
     */
    public function login(): Response
    {
        return Inertia::render('Kiosk/Login', [
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
        ]);

        $cleanInput = preg_replace('/[^0-9a-zA-Z]/', '', $validated['document_id']);

        $user = User::whereHas('student', function ($q) use ($cleanInput, $validated) {
            $q->whereRaw("REPLACE(cedula, '-', '') = ?", [$cleanInput])
              ->orWhere('student_code', $cleanInput)
              ->orWhere('cedula', $validated['document_id'])
              ->orWhere('student_code', $validated['document_id']);
        })->first();

        if (!$user) {
            $user = User::where('email', $validated['document_id'])->first();
        }

        if (!$user) {
            return back()->with('error', 'No se encontró ningún estudiante con esa Identificación.');
        }

        if ($user->kiosk_pin && $user->kiosk_pin !== $validated['pin']) {
            return back()->with('error', 'El PIN ingresado es incorrecto.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('kiosk.dashboard');
    }

    /**
     * Touchscreen Kiosk Main Menu.
     */
    public function dashboard(): Response
    {
        $student = $this->getKioskStudent();

        return Inertia::render('Kiosk/TouchTerminal', [
            'student' => $student ? [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_code' => $student->student_code ?? sprintf('EST-%04d', $student->id),
                'career' => $student->course?->name ?? 'Carrera General',
            ] : null,
            'currentTime' => now()->format('H:i'),
            'currentDate' => ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
        ]);
    }

    /**
     * Kiosk Finances / Balances Screen.
     */
    public function finances(): Response
    {
        $student = $this->getKioskStudent();

        $pendingPayments = [];
        $totalPending = 0;

        if ($student) {
            $pendingPayments = Payment::with('paymentConcept')
                ->where('student_id', $student->id)
                ->whereIn('status', ['Pendiente', 'pending', 'Vencido'])
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'concept' => $p->paymentConcept?->name ?? 'Cuota Mensual',
                    'amount' => (float)$p->amount,
                    'due_date' => $p->due_date?->format('d/m/Y') ?? 'Fin de mes',
                    'status' => $p->status,
                ]);

            $totalPending = $pendingPayments->sum('amount');
        }

        return Inertia::render('Kiosk/Finances', [
            'student' => $student ? [
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_code' => $student->student_code ?? sprintf('EST-%04d', $student->id),
                'career' => $student->course?->name ?? 'Carrera General',
            ] : null,
            'pendingPayments' => $pendingPayments,
            'totalPending' => (float)$totalPending,
        ]);
    }

    /**
     * Kiosk Student Schedule.
     */
    public function schedule(): Response
    {
        $student = $this->getKioskStudent();

        $schedules = [];
        if ($student) {
            $enrollments = Enrollment::with(['courseSchedule.module', 'courseSchedule.classroom', 'courseSchedule.teacher'])
                ->where('student_id', $student->id)
                ->whereIn('status', ['Cursando', 'Activo', 'enrolled'])
                ->get();

            $schedules = $enrollments->map(fn($e) => [
                'id' => $e->id,
                'subject_name' => $e->courseSchedule?->module?->name ?? 'Asignatura Regular',
                'subject_code' => $e->courseSchedule?->module?->code ?? 'ASG-101',
                'teacher' => $e->courseSchedule?->teacher?->name ?? 'Profesor Asignado',
                'classroom' => $e->courseSchedule?->classroom?->name ?? 'Aula Central 101',
                'days' => $e->courseSchedule?->days ?? 'Lunes y Miércoles',
                'time' => ($e->courseSchedule?->start_time ? substr($e->courseSchedule->start_time, 0, 5) : '08:00') . ' - ' . ($e->courseSchedule?->end_time ? substr($e->courseSchedule->end_time, 0, 5) : '10:00'),
            ]);
        }

        return Inertia::render('Kiosk/Schedule', [
            'student' => $student ? [
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_code' => $student->student_code ?? sprintf('EST-%04d', $student->id),
                'career' => $student->course?->name ?? 'Carrera General',
            ] : null,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Kiosk Student Grades & History.
     */
    public function grades(): Response
    {
        $student = $this->getKioskStudent();

        $grades = [];
        $gpa = 88.5;

        if ($student) {
            $gradeRecords = StudentGrade::where('student_id', $student->id)
                ->with(['sectionSubject.subject', 'evaluationPeriod'])
                ->get();

            $grades = $gradeRecords->map(fn($g) => [
                'id' => $g->id,
                'subject' => $g->sectionSubject?->subject?->name ?? 'Materia',
                'period' => $g->evaluationPeriod?->name ?? 'Período Actual',
                'score' => (float)$g->score,
                'performance_level' => $g->performance_level ?? 'logro_evidenciado',
            ]);

            if ($gradeRecords->count() > 0) {
                $gpa = round($gradeRecords->avg('score'), 1);
            }
        }

        return Inertia::render('Kiosk/Grades', [
            'student' => $student ? [
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_code' => $student->student_code ?? sprintf('EST-%04d', $student->id),
                'career' => $student->course?->name ?? 'Carrera General',
            ] : null,
            'grades' => $grades,
            'gpa' => $gpa,
        ]);
    }

    /**
     * Kiosk Academic Offer.
     */
    public function academicOffer(): Response
    {
        $courses = Course::where('status', 'Activo')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'program_type', 'monthly_fee', 'registration_fee', 'duration_periods'])
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'program_type' => $c->program_type,
                'price' => (float)($c->monthly_fee ?: $c->registration_fee ?: 0),
                'duration_hours' => $c->duration_periods ? ($c->duration_periods . ' Períodos') : '2 Años',
            ]);

        return Inertia::render('Kiosk/AcademicOffer', [
            'courses' => $courses,
        ]);
    }

    /**
     * Kiosk HR Touch Punch Screen (Pin Pad & Biometric).
     */
    public function hrPunch(): Response
    {
        $recentPunches = EmployeeAttendance::with('employee.user')
            ->whereDate('punch_time', now()->toDateString())
            ->latest('punch_time')
            ->take(8)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'employee_name' => $p->employee?->user?->name ?? 'Colaborador',
                'time' => $p->punch_time?->format('H:i:s'),
                'type' => $p->punch_type == 0 ? 'Entrada' : 'Salida',
            ]);

        return Inertia::render('Kiosk/HrPunch', [
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
            'pin' => 'nullable|string|min:4|max:6',
            'biometric_id' => 'nullable|integer',
            'punch_type' => 'required|in:0,1',
        ]);

        $employee = null;

        if (!empty($validated['biometric_id'])) {
            $employee = Employee::where('biometric_id', $validated['biometric_id'])->first();
        } elseif (!empty($validated['pin'])) {
            $users = User::whereNotNull('kiosk_pin')->get();
            foreach ($users as $u) {
                if (Hash::check($validated['pin'], $u->kiosk_pin) || $u->kiosk_pin === $validated['pin']) {
                    $employee = $u->employee;
                    break;
                }
            }
        }

        if (!$employee) {
            return back()->with('error', 'Credencial o PIN inválido. Verifique su número de identificación.');
        }

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'punch_time' => now(),
            'punch_type' => $validated['punch_type'],
            'device_ip' => 'Quiosco Táctil Central',
            'verified_method' => !empty($validated['pin']) ? 'PIN' : 'Biométrico',
        ]);

        $action = $validated['punch_type'] == 0 ? 'ENTRADA' : 'SALIDA';
        return back()->with('success', "¡Marcaje registrado! {$action} confirmada para {$employee->user?->name} a las " . now()->format('H:i:s'));
    }
}
