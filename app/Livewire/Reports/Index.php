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
        
        // Optimización: Cargar solo campos necesarios
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
            // Traemos datos mínimos para llenar el select, más detalles se cargan al generar el reporte
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
        // Aumentar el tiempo límite por si el rango de fechas es muy amplio
        set_time_limit(120);

        $this->validateFilters();

        $this->generatedReportType = $this->reportType;
        $this->reportData = null;

        // Limpiar memoria antes de generar
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
    }

    // 1. REPORTE DE ASISTENCIA (ULTRA OPTIMIZADO)
    public function generateAttendanceReport()
    {
        Log::info("--- INICIO REPORTE ASISTENCIA OPTIMIZADO ---");

        // 1. Cargar datos de la sección (CourseSchedule)
        $schedule = CourseSchedule::with(['module:id,name,course_id', 'module.course:id,name', 'teacher:id,name'])
            ->select('id', 'module_id', 'teacher_id', 'section_name', 'start_time', 'end_time', 'days_of_week', 'start_date', 'end_date')
            ->find($this->schedule_id);

        if (!$schedule) {
            return;
        }

        // 2. Obtener Estudiantes (Solo ID y Nombres)
        // Usamos whereHas para filtrar solo los que tienen enrollment en esta sección
        $students = Student::whereHas('enrollments', function($q) {
            $q->where('course_schedule_id', $this->schedule_id);
        })
        ->select('id', 'first_name', 'last_name')
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

        // 3. Generar Rango de Fechas (Headers)
        $start = Carbon::parse($this->date_from);
        $end = Carbon::parse($this->date_to);
        $period = CarbonPeriod::create($start, $end);
        
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        // 4. Mapeo de Enrollment ID -> Student ID
        // Esto es crucial para conectar la asistencia (que usa enrollment_id) con el estudiante
        $enrollmentMap = DB::table('enrollments')
            ->where('course_schedule_id', $this->schedule_id)
            ->pluck('student_id', 'id'); // Retorna [enrollment_id => student_id]

        // 5. Consulta de Asistencias (RAW / toBase)
        // ALERTA DE OPTIMIZACIÓN: Usamos toBase() para que Eloquent NO hidrate modelos.
        // Esto reduce el uso de memoria drásticamente y evita el "congelamiento".
        // Devuelve objetos stdClass simples en lugar de modelos Attendance pesados.
        $attendances = DB::table('attendances')
            ->where('course_schedule_id', $this->schedule_id)
            ->whereBetween('attendance_date', [$this->date_from, $this->date_to])
            ->select('enrollment_id', 'attendance_date', 'status')
            ->get(); 

        Log::info("Registros raw recuperados: " . $attendances->count());

        // 6. Construcción de la Matriz en Memoria
        $attendanceMatrix = [];
        
        foreach ($attendances as $record) {
            // $record es stdClass. $record->enrollment_id es int/string.
            $studentId = $enrollmentMap[$record->enrollment_id] ?? null;
            
            if ($studentId) {
                // $record->attendance_date viene como string de la BD (ej: "2023-10-25"), 
                // por lo que no necesitamos formatearlo con Carbon si ya tiene el formato correcto.
                // Si la BD devuelve timestamp completo, cortamos los primeros 10 chars.
                $dateKey = substr($record->attendance_date, 0, 10); 
                
                $attendanceMatrix[$studentId][$dateKey] = $record->status;
            }
        }

        // 7. Asignar a la variable pública para la vista
        $this->reportData = [
            'schedule' => $schedule,
            'students' => $students, // Colección ligera de estudiantes
            'dates' => $dates,       // Array de strings
            'matrix' => $attendanceMatrix, // Array puro [student_id][date] => status
            'start_date' => $start,
            'end_date' => $end,
        ];
        
        Log::info("--- FIN REPORTE ASISTENCIA ---");
    }

    // 2. REPORTE DE CALIFICACIONES
    public function generateGradesReport()
    {
        $schedule = CourseSchedule::with(['module.course', 'teacher'])->find($this->schedule_id);
        
        $enrollments = Enrollment::with('student', 'grades') // Eager loading de grades
            ->where('course_schedule_id', $this->schedule_id)
            ->get()
            ->sortBy('student.last_name');

        $this->reportData = [
            'schedule' => $schedule,
            'enrollments' => $enrollments,
        ];
    }

    // 3. REPORTE DE PAGOS
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

        // Usamos limit para proteger la memoria
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

    // 4. REPORTE DE ESTUDIANTES
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

    // 5. REPORTE DE CALENDARIO
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

    // 6. REPORTE DE ASIGNACIÓN
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