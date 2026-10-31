<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CourseSchedule;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\PayrollItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TeacherPortalController extends Controller
{
    /**
     * Resolve active teacher user or fallback.
     */
    protected function getActiveTeacherId(): int
    {
        $user = auth()->user();
        if ($user->hasRole('Profesor')) {
            return $user->id;
        }

        // For Admin preview: find a teacher with schedules or default to user id
        $firstSched = CourseSchedule::whereNotNull('teacher_id')->first();
        return $firstSched ? $firstSched->teacher_id : $user->id;
    }

    /**
     * Teacher Portal Dashboard.
     */
    public function dashboard(): Response
    {
        $teacherId = $this->getActiveTeacherId();
        $user = auth()->user();

        $schedules = CourseSchedule::where('teacher_id', $teacherId)
            ->with(['module.course', 'classroom'])
            ->withCount('enrollments')
            ->get();

        // If no schedules for this teacher, fetch any available for demonstration
        if ($schedules->isEmpty() && ($user->hasRole('Admin') || $user->hasRole('SuperAdmin'))) {
            $schedules = CourseSchedule::with(['module.course', 'classroom'])
                ->withCount('enrollments')
                ->take(5)
                ->get();
        }

        $totalSections = $schedules->count();
        $totalStudents = $schedules->sum('enrollments_count');

        $todayName = ucfirst(now()->locale('es')->isoFormat('dddd'));
        $nowTime = now()->format('H:i:s');

        $nextClass = $schedules->first();

        $sectionsData = $schedules->map(fn($s) => [
            'id' => $s->id,
            'subject_name' => $s->module?->name ?? 'Asignatura',
            'subject_code' => $s->module?->code ?? 'ASG',
            'course_name' => $s->module?->course?->name ?? 'Carrera Institucional',
            'classroom' => $s->classroom?->name ?? 'Aula General',
            'days' => $s->days ?? 'Lunes y Miércoles',
            'start_time' => $s->start_time ? substr($s->start_time, 0, 5) : '08:00',
            'end_time' => $s->end_time ? substr($s->end_time, 0, 5) : '10:00',
            'students_count' => $s->enrollments_count,
            'status' => $s->status ?? 'active',
            'is_locked' => (bool)($s->is_locked ?? false),
        ]);

        return Inertia::render('TeacherPortal/Dashboard', [
            'teacher' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'sections' => $sectionsData,
            'stats' => [
                'total_sections' => $totalSections,
                'total_students' => $totalStudents,
                'today_classes_count' => min($totalSections, 2),
            ],
            'nextClass' => $nextClass ? [
                'subject_name' => $nextClass->module?->name ?? 'Asignatura',
                'classroom' => $nextClass->classroom?->name ?? 'Aula',
                'time' => ($nextClass->start_time ? substr($nextClass->start_time, 0, 5) : '08:00') . ' - ' . ($nextClass->end_time ? substr($nextClass->end_time, 0, 5) : '10:00'),
            ] : null,
        ]);
    }

    /**
     * Teacher Grade Book for a section.
     */
    public function grades(Request $request, $sectionId = null): Response
    {
        $teacherId = $this->getActiveTeacherId();

        $allTeacherSections = CourseSchedule::where('teacher_id', $teacherId)
            ->with('module')
            ->get();

        if ($allTeacherSections->isEmpty() && auth()->user()->hasRole('Admin')) {
            $allTeacherSections = CourseSchedule::with('module')->take(10)->get();
        }

        $activeSectionId = $sectionId ?: ($allTeacherSections->first()?->id ?? 1);
        $section = CourseSchedule::with(['module.course', 'classroom'])->find($activeSectionId);

        $enrollments = [];
        $isLocked = false;

        if ($section) {
            $isLocked = (bool)($section->is_locked ?? false);

            $enrollmentModels = Enrollment::with('student')
                ->where('course_schedule_id', $section->id)
                ->get();

            $enrollments = $enrollmentModels->map(fn($e, $idx) => [
                'index' => $idx + 1,
                'enrollment_id' => $e->id,
                'student_id' => $e->student_id,
                'student_code' => $e->student?->student_code ?? sprintf('EST-%04d', $e->student_id),
                'student_name' => ($e->student?->last_name ?? '') . ', ' . ($e->student?->first_name ?? 'Estudiante'),
                'final_grade' => $e->final_grade !== null ? (string)$e->final_grade : '',
                'status' => $e->status,
            ])->sortBy('student_name')->values()->toArray();
        }

        return Inertia::render('TeacherPortal/GradeBook', [
            'sections' => $allTeacherSections->map(fn($s) => [
                'id' => $s->id,
                'name' => ($s->module?->code ? "[{$s->module->code}] " : '') . ($s->module?->name ?? "Sección #{$s->id}"),
            ]),
            'selectedSection' => $section ? [
                'id' => $section->id,
                'subject_name' => $section->module?->name ?? 'Asignatura',
                'subject_code' => $section->module?->code ?? 'ASG',
                'course_name' => $section->module?->course?->name ?? 'Carrera Institucional',
                'classroom' => $section->classroom?->name ?? 'Aula General',
                'is_locked' => $isLocked,
            ] : null,
            'enrollments' => $enrollments,
            'isLocked' => $isLocked,
        ]);
    }

    /**
     * Save Grades from Teacher.
     */
    public function saveGrades(Request $request, $sectionId)
    {
        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.enrollment_id' => 'required|exists:enrollments,id',
            'grades.*.final_grade' => 'nullable|numeric|min:0|max:100',
        ]);

        $section = CourseSchedule::findOrFail($sectionId);
        if ($section->is_locked && !auth()->user()->hasRole('Admin')) {
            return back()->with('error', 'Esta sección se encuentra sellada oficialmente por Registro.');
        }

        DB::beginTransaction();
        try {
            foreach ($validated['grades'] as $item) {
                $grade = $item['final_grade'] !== null && $item['final_grade'] !== ''
                    ? min(100, max(0, (float)$item['final_grade']))
                    : null;

                Enrollment::where('id', $item['enrollment_id'])->update([
                    'final_grade' => $grade,
                    'status' => $grade !== null ? ($grade >= 70 ? 'Aprobado' : 'Reprobado') : 'Cursando',
                ]);
            }

            DB::commit();
            return back()->with('success', 'Calificaciones actualizadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar calificaciones: ' . $e->getMessage());
        }
    }

    /**
     * Roll Call / Asistencia for a section.
     */
    public function attendance(Request $request, $sectionId = null): Response
    {
        $teacherId = $this->getActiveTeacherId();

        $allTeacherSections = CourseSchedule::where('teacher_id', $teacherId)
            ->with('module')
            ->get();

        if ($allTeacherSections->isEmpty() && auth()->user()->hasRole('Admin')) {
            $allTeacherSections = CourseSchedule::with('module')->take(10)->get();
        }

        $activeSectionId = $sectionId ?: ($allTeacherSections->first()?->id ?? 1);
        $section = CourseSchedule::with(['module.course', 'classroom'])->find($activeSectionId);

        $date = $request->query('date', now()->format('Y-m-d'));

        $enrollments = [];
        $isLocked = false;

        if ($section) {
            $isLocked = (bool)($section->is_locked ?? false);

            $enrollmentModels = Enrollment::with('student')
                ->where('course_schedule_id', $section->id)
                ->get();

            $existingAttendances = Attendance::where('course_schedule_id', $section->id)
                ->whereDate('attendance_date', $date)
                ->get()
                ->keyBy('enrollment_id');

            $enrollments = $enrollmentModels->map(fn($e, $idx) => [
                'index' => $idx + 1,
                'enrollment_id' => $e->id,
                'student_id' => $e->student_id,
                'student_code' => $e->student?->student_code ?? sprintf('EST-%04d', $e->student_id),
                'student_name' => ($e->student?->last_name ?? '') . ', ' . ($e->student?->first_name ?? 'Estudiante'),
                'status' => $existingAttendances->get($e->id)?->status ?? 'Presente',
            ])->sortBy('student_name')->values()->toArray();
        }

        return Inertia::render('TeacherPortal/RollCall', [
            'sections' => $allTeacherSections->map(fn($s) => [
                'id' => $s->id,
                'name' => ($s->module?->code ? "[{$s->module->code}] " : '') . ($s->module?->name ?? "Sección #{$s->id}"),
            ]),
            'selectedSection' => $section ? [
                'id' => $section->id,
                'subject_name' => $section->module?->name ?? 'Asignatura',
                'subject_code' => $section->module?->code ?? 'ASG',
                'classroom' => $section->classroom?->name ?? 'Aula General',
            ] : null,
            'attendanceDate' => $date,
            'enrollments' => $enrollments,
            'isLocked' => $isLocked,
        ]);
    }

    /**
     * Save Attendance for Teacher Section.
     */
    public function saveAttendance(Request $request, $sectionId)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.enrollment_id' => 'required|exists:enrollments,id',
            'attendances.*.status' => 'required|in:Presente,Ausente,Tardanza,Excusa',
        ]);

        $section = CourseSchedule::findOrFail($sectionId);

        DB::beginTransaction();
        try {
            foreach ($validated['attendances'] as $item) {
                Attendance::updateOrCreate(
                    [
                        'enrollment_id' => $item['enrollment_id'],
                        'course_schedule_id' => $section->id,
                        'attendance_date' => $validated['date'],
                    ],
                    [
                        'status' => $item['status'],
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'Registro de asistencia guardado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar asistencia: ' . $e->getMessage());
        }
    }

    /**
     * Display Teacher Payroll Receipts.
     */
    public function payroll(): Response
    {
        $user = auth()->user();
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();

        // Fallback for Admin preview
        if (!$employee && ($user->hasRole('Admin') || $user->hasRole('SuperAdmin'))) {
            $employee = Employee::with('user')->first();
        }

        $items = [];
        $totalPaid = 0;

        if ($employee) {
            $payrollItems = PayrollItem::where('employee_id', $employee->id)
                ->with('payroll')
                ->orderBy('id', 'desc')
                ->get();

            $items = $payrollItems->map(fn($pi) => [
                'id' => $pi->id,
                'payroll_name' => $pi->payroll?->name ?? 'Liquidación de Nómina',
                'period' => ($pi->payroll?->start_date?->format('d/m/Y') ?? '') . ' - ' . ($pi->payroll?->end_date?->format('d/m/Y') ?? ''),
                'base_amount' => (float)$pi->base_amount,
                'deductions' => (float)$pi->deductions,
                'net_amount' => (float)$pi->net_amount,
                'status' => $pi->payroll?->status ?? 'Borrador',
                'created_at' => $pi->created_at?->format('d/m/Y'),
            ])->toArray();

            $totalPaid = array_sum(array_column($items, 'net_amount'));
        }

        return Inertia::render('TeacherPortal/MyPayroll', [
            'employee' => $employee ? [
                'id' => $employee->id,
                'position' => $employee->position ?? 'Docente',
                'contract_type' => $employee->contract_type ?? 'Mensual',
                'base_salary' => (float)($employee->base_salary ?? 0),
            ] : null,
            'items' => $items,
            'totalPaid' => $totalPaid,
        ]);
    }
}
