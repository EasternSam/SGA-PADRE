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
        $schedule = CourseSchedule::with(['module:id,name,course_id', 'module.course:id,name', 'teacher:id,name'])
            ->select('id', 'module_id', 'teacher_id', 'section_name', 'start_time', 'end_time', 'days_of_week', 'start_date', 'end_date')
            ->find($this->schedule_id);

        if (!$schedule) {
            return;
        }

        // 2. Obtener Estudiantes (OPTIMIZADO: DB::table)
        $students = DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.course_schedule_id', $this->schedule_id)
            ->select('students.id', 'students.first_name', 'students.last_name')
            ->distinct() 
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->get();

        // 3. Generar Rango de Fechas
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

        // 5. Consulta de Asistencias "CRUDA"
        $attendances = DB::table('attendances')
            ->where('course_schedule_id', $this->schedule_id)
            ->whereBetween('attendance_date', [$this->date_from, $this->date_to])
            ->select('enrollment_id', 'attendance_date', 'status')
            ->get(); 

        // 6. Construcción de la Matriz en Memoria
        $attendanceMatrix = [];
        
        foreach ($attendances as $record) {
            $studentId = $enrollmentMap[$record->enrollment_id] ?? null;
            
            if ($studentId) {
                $dateKey = substr($record->attendance_date, 0, 10); 
                $attendanceMatrix[$studentId][$dateKey] = $record->status;
            }
        }

        $this->reportData = [
            'schedule' => $schedule,
            'students' => $students,
            'dates' => $dates,
            'matrix' => $attendanceMatrix,
            'start_date' => $start,
            'end_date' => $end,
        ];
        
        Log::info("--- FIN REPORTE ASISTENCIA ---");
    }

    // --- REPORTE FINANCIERO (CORREGIDO PARA EVITAR DUPLICADOS) ---
    public function generatePaymentsReport()
    {
        // 1. Base Query: Empezamos desde las Matrículas
        $query = DB::table('enrollments')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
            ->join('modules', 'course_schedules.module_id', '=', 'modules.id')
            ->join('courses', 'modules.course_id', '=', 'courses.id')
            ->leftJoin('payments', 'enrollments.id', '=', 'payments.enrollment_id')
            ->select(
                'enrollments.id as enrollment_id',
                'students.first_name',
                'students.last_name',
                'students.phone',
                'courses.name as course_name',
                'modules.name as module_name',
                'course_schedules.section_name',
                'enrollments.status as enrollment_status',
                // FIX CRÍTICO: Usar comillas simples ' ' para valores string en SQL
                // LOWER() ayuda si en la base de datos dice "Paid" en lugar de "paid"
                DB::raw("SUM(CASE WHEN LOWER(payments.status) = 'paid' THEN payments.amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN LOWER(payments.status) = 'pending' THEN payments.amount ELSE 0 END) as raw_pending_sum")
            );

        // 2. Aplicar Filtros
        if ($this->course_id) {
            $query->where('courses.id', $this->course_id);
        }

        if ($this->teacher_id) {
            $query->where('course_schedules.teacher_id', $this->teacher_id);
        }

        if ($this->date_from && $this->date_to) {
             // Usamos created_at de enrollment para filtrar inscripciones en el periodo
             $query->whereBetween('enrollments.created_at', [$this->date_from . ' 00:00:00', $this->date_to . ' 23:59:59']);
        }

        // 3. Agrupación
        $query->groupBy(
            'enrollments.id', 
            'students.id', 
            'students.first_name', 
            'students.last_name', 
            'students.phone',
            'courses.name', 
            'modules.name', 
            'course_schedules.section_name',
            'enrollments.status'
        );

        // 4. Ejecución
        $financials = $query->get();

        // 5. Post-procesamiento LÓGICO para arreglar totales incorrectos
        // Recorremos cada registro y corregimos el 'total_cost'
        $financials = $financials->map(function ($item) {
            // Lógica de corrección:
            // Si hay pagos 'paid' (> 0), asumimos que el costo real es igual a lo pagado (o múltiplo de ello),
            // y IGNORAMOS los montos 'pending' que probablemente sean intentos fallidos duplicados.
            
            $paid = (float) $item->total_paid;
            $pendingSum = (float) $item->raw_pending_sum;

            if ($paid > 0) {
                // Caso A: Ya pagó.
                // Asumimos que no debe nada más del mismo concepto.
                // El costo total es lo que pagó.
                $item->total_cost = $paid;
            } else {
                // Caso B: No ha pagado nada.
                // Asumimos que la deuda es el monto del pendiente.
                $item->total_cost = $pendingSum;
            }

            return $item;
        });

        // 6. Filtrado final por estado seleccionado en el UI
        if ($this->payment_status !== 'all') {
            $financials = $financials->filter(function($item) {
                $paid = (float) $item->total_paid;
                $cost = (float) $item->total_cost;
                $balance = $cost - $paid;

                if ($this->payment_status == 'paid') return $balance <= 0.01 && $paid > 0;
                if ($this->payment_status == 'pending') return $balance > 0.01;
                return true;
            });
        }

        $this->reportData = [
            'financials' => $financials,
            'filter_status' => $this->payment_status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];
    }
    
    // --- OTROS REPORTES ---
    
    public function generateGradesReport()
    {
        $schedule = CourseSchedule::with(['module.course', 'teacher'])->find($this->schedule_id);
        
        $enrollments = Enrollment::with('student')
            ->where('course_schedule_id', $this->schedule_id)
            ->get()
            ->sortBy('student.last_name');

        $this->reportData = [
            'schedule' => $schedule,
            'enrollments' => $enrollments,
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