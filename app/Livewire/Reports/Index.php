<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Course;
use App\Models\User;
use App\Models\Student;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Index extends Component
{
    // Selección de Reporte
    public $reportType = 'attendance';

    // Filtros
    public $course_id = '';
    public $module_id = '';
    public $schedule_id = '';
    public $teacher_id = '';
    public $date_from;
    public $date_to;
    public $payment_status = 'all';

    // Datos para selects
    public $courses = [];
    public $modules = [];
    public $schedules = [];
    public $teachers = [];

    // Datos del Reporte Generado
    public $reportData = null;
    public $generatedReportType = null;

    public function mount()
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');
        
        // Cargar solo campos necesarios para los selectores iniciales
        $this->courses = Course::select('id', 'name')->orderBy('name')->get();
        $this->teachers = User::role('Profesor')->select('id', 'name')->orderBy('name')->get();
    }

    public function updatedCourseId($value)
    {
        if ($value) {
            $this->modules = Module::where('course_id', $value)->select('id', 'name')->orderBy('name')->get();
        } else {
            $this->modules = [];
        }
        
        $this->module_id = '';
        $this->schedules = [];
        $this->schedule_id = '';
    }

    public function updatedModuleId($value)
    {
        if ($value) {
            // Traemos datos mínimos para llenar el select
            $this->schedules = CourseSchedule::where('module_id', $value)
                ->select('id', 'section_name', 'start_time', 'end_time', 'days_of_week')
                ->get();
        } else {
            $this->schedules = [];
        }
        $this->schedule_id = '';
    }

    public function generateReport()
    {
        // Aumentar el tiempo límite para reportes grandes
        set_time_limit(120);

        // Validamos filtros antes de procesar
        $this->validateFilters();

        $this->generatedReportType = $this->reportType;
        $this->reportData = null;

        // Limpieza de memoria proactiva
        gc_collect_cycles();

        switch ($this->reportType) {
            case 'attendance':
                $this->generateAttendanceReport();
                break;
            case 'grades':
                $this->generateGradesReport();
                break;
            case 'payments':
                $this->generatePaymentsReport();
                break;
            case 'students':
                $this->generateStudentsReport();
                break;
            case 'calendar':
                $this->generateCalendarReport();
                break;
            case 'assignments':
                $this->generateAssignmentsReport();
                break;
        }
    }

    protected function validateFilters()
    {
        $rules = [
            'reportType' => 'required',
        ];

        if (in_array($this->reportType, ['attendance', 'grades', 'students'])) {
            $rules['course_id'] = 'required';
            $rules['module_id'] = 'required';
            $rules['schedule_id'] = 'required';
        }

        if (in_array($this->reportType, ['attendance', 'payments', 'calendar'])) {
            $rules['date_from'] = 'required|date';
            $rules['date_to'] = 'required|date|after_or_equal:date_from';
        }

        $this->validate($rules, [
            'course_id.required' => 'Seleccione un curso.',
            'module_id.required' => 'Seleccione un módulo.',
            'schedule_id.required' => 'Seleccione una sección.',
            'date_from.required' => 'La fecha de inicio es requerida.',
            'date_to.after_or_equal' => 'La fecha fin debe ser igual o posterior a la fecha de inicio.',
        ]);

        // Validación Adicional: Prevenir congelamiento del navegador por exceso de columnas
        if ($this->reportType === 'attendance') {
            $start = Carbon::parse($this->date_from);
            $end = Carbon::parse($this->date_to);
            $daysDiff = $start->diffInDays($end);

            if ($daysDiff > 62) {
                throw ValidationException::withMessages([
                    'date_to' => 'El rango de fechas es demasiado amplio (' . $daysDiff . ' días). Para evitar problemas de rendimiento en la vista previa, seleccione un máximo de 2 meses (60 días).'
                ]);
            }
        }
    }

    // --- LÓGICA OPTIMIZADA (SOLUCIÓN AL CONGELAMIENTO) ---
    public function generateAttendanceReport()
    {
        Log::info("--- INICIO REPORTE ASISTENCIA OPTIMIZADO ---");

        // 1. Cargar datos de la sección (CourseSchedule)
        // Mantenemos Eloquent aquí porque es UN SOLO registro, no afecta el rendimiento.
        $schedule = CourseSchedule::with(['module:id,name,course_id', 'module.course:id,name', 'teacher:id,name'])
            ->select('id', 'module_id', 'teacher_id', 'section_name', 'start_time', 'end_time', 'days_of_week', 'start_date', 'end_date')
            ->find($this->schedule_id);

        if (!$schedule) {
            return;
        }

        // 2. Obtener Estudiantes (OPTIMIZADO: DB::table)
        // CAMBIO CRÍTICO: Usamos DB::table en lugar de Student::whereHas.
        // Esto evita que Livewire intente serializar/hidratar Modelos Eloquent para cada estudiante,
        // lo cual es la causa principal de que se "trabe" cuando hay muchos datos.
        $students = DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.course_schedule_id', $this->schedule_id)
            ->select('students.id', 'students.first_name', 'students.last_name')
            ->distinct() // Evitar duplicados si hay inconsistencias en enrollments
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->get();

        // 3. Generar Rango de Fechas (Strings simples)
        $start = Carbon::parse($this->date_from);
        $end = Carbon::parse($this->date_to);
        $period = CarbonPeriod::create($start, $end);
        
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        // 4. Mapeo de Enrollment ID -> Student ID
        $enrollmentMap = DB::table('enrollments')
            ->where('course_schedule_id', $this->schedule_id)
            ->pluck('student_id', 'id');

        // 5. Consulta de Asistencias "CRUDA" (Sin Modelos Eloquent)
        $attendances = DB::table('attendances')
            ->where('course_schedule_id', $this->schedule_id)
            ->whereBetween('attendance_date', [$this->date_from, $this->date_to])
            ->select('enrollment_id', 'attendance_date', 'status')
            ->get(); 

        Log::info("Registros raw recuperados: " . $attendances->count());

        // 6. Construcción de la Matriz en Memoria
        $attendanceMatrix = [];
        
        foreach ($attendances as $record) {
            $studentId = $enrollmentMap[$record->enrollment_id] ?? null;
            
            if ($studentId) {
                $dateKey = substr($record->attendance_date, 0, 10); 
                $attendanceMatrix[$studentId][$dateKey] = $record->status;
            }
        }

        // 7. Asignar a la variable pública
        $this->reportData = [
            'schedule' => $schedule,
            'students' => $students,       // Ahora es una colección ligera de stdClass
            'dates' => $dates,
            'matrix' => $attendanceMatrix,
            'start_date' => $start,
            'end_date' => $end,
        ];
        
        Log::info("--- FIN REPORTE ASISTENCIA ---");
    }

    // --- OTROS REPORTES (Mantenidos) ---
    
    public function generateGradesReport()
    {
        $schedule = CourseSchedule::with(['module.course', 'teacher'])->find($this->schedule_id);
        
        $enrollments = Enrollment::with('student', 'grades')
            ->where('course_schedule_id', $this->schedule_id)
            ->get()
            ->sortBy('student.last_name');

        $this->reportData = [
            'schedule' => $schedule,
            'enrollments' => $enrollments,
        ];
    }

    public function generatePaymentsReport()
    {
        $query = Payment::with(['student', 'paymentConcept', 'enrollment.courseSchedule.module.course']);

        if ($this->date_from && $this->date_to) {
            $query->whereBetween('created_at', [$this->date_from . ' 00:00:00', $this->date_to . ' 23:59:59']);
        }

        if ($this->teacher_id) {
            $query->whereHas('enrollment.courseSchedule', function($q) {
                $q->where('teacher_id', $this->teacher_id);
            });
        }

        if ($this->course_id) {
            $query->whereHas('enrollment.courseSchedule', function($q) {
                $q->where('course_id', $this->course_id);
            });
        }
        
        if ($this->payment_status !== 'all') {
            $query->where('status', $this->payment_status);
        }

        $payments = $query->latest()->limit(500)->get();

        $debts = [];
        if ($this->payment_status === 'pending' || $this->payment_status === 'all') {
            $debts = Enrollment::with(['student', 'courseSchedule.module.course'])
                ->whereDoesntHave('payment', function($q) {
                    $q->where('status', 'paid');
                })
                ->where('status', 'Cursando') 
                ->limit(50) 
                ->get();
        }

        $this->reportData = [
            'payments' => $payments,
            'debts' => $debts,
            'filter_status' => $this->payment_status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];
    }

    public function generateStudentsReport()
    {
        $schedule = CourseSchedule::with(['module.course'])->find($this->schedule_id);
        
        $enrollments = Enrollment::with('student')
            ->where('course_schedule_id', $this->schedule_id)
            ->get()
            ->sortBy('student.last_name');

        $this->reportData = [
            'schedule' => $schedule,
            'enrollments' => $enrollments,
        ];
    }

    public function generateCalendarReport()
    {
        $schedules = CourseSchedule::with(['module.course', 'teacher'])
            ->where(function($q) {
                $q->whereBetween('start_date', [$this->date_from, $this->date_to])
                  ->orWhereBetween('end_date', [$this->date_from, $this->date_to])
                  ->orWhere(function($sub) {
                      $sub->where('start_date', '<=', $this->date_from)
                          ->where('end_date', '>=', $this->date_to);
                  });
            })
            ->orderBy('start_date')
            ->limit(200)
            ->get();

        $this->reportData = [
            'schedules' => $schedules,
            'start_date' => $this->date_from,
            'end_date' => $this->date_to,
        ];
    }

    public function generateAssignmentsReport()
    {
        $query = CourseSchedule::with(['module.course', 'teacher'])
            ->orderBy('teacher_id')
            ->orderBy('start_date');

        if ($this->teacher_id) {
            $query->where('teacher_id', $this->teacher_id);
        }
        
        if ($this->course_id) {
            $query->whereHas('module', function($q) {
                $q->where('course_id', $this->course_id);
            });
        }

        $assignments = $query->get();

        $this->reportData = [
            'assignments' => $assignments,
        ];
    }

    public function render()
    {
        return view('livewire.reports.index')
            ->layout('layouts.dashboard');
    }
}