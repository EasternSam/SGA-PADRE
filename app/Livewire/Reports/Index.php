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

        // MODIFICADO: Quitamos 'attendance' de aquí porque las fechas vendrán de la BD
        if (in_array($this->reportType, ['payments', 'calendar'])) {
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

    // --- LÓGICA OPTIMIZADA (SOLUCIÓN AL CONGELAMIENTO + FILTRO PAGO + FECHAS INTELIGENTES) ---
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

        // --- LÓGICA AGREGADA: USAR FECHAS DE LA SECCIÓN ---
        $this->date_from = $schedule->start_date;
        $this->date_to = $schedule->end_date;
        // ------------------------------------------------

        // Validación de Rango (Movida aquí y aumentada)
        $start = Carbon::parse($this->date_from);
        $end = Carbon::parse($this->date_to);
        $daysDiff = $start->diffInDays($end);

        // Aumentado a 180 días (aprox 6 meses) para permitir semestres completos
        if ($daysDiff > 180) {
            throw ValidationException::withMessages([
                'schedule_id' => 'El rango de fechas de la sección es demasiado amplio (' . $daysDiff . ' días). El sistema limita a 180 días (aprox. 6 meses) para garantizar el rendimiento.'
            ]);
        }

        // 2. Obtener Estudiantes (OPTIMIZADO: DB::table)
        $students = DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.course_schedule_id', $this->schedule_id)
            // FILTRO CRÍTICO: Excluir 'Pendiente' (No han pagado)
            ->whereNotIn('enrollments.status', ['Pendiente', 'pendiente']) 
            ->select('students.id', 'students.first_name', 'students.last_name')
            ->distinct() 
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->get();

        // 3. Generar Rango de Fechas Inteligente (Lógica de AttendancePdfController)
        
        // A) Obtener días teóricos del horario
        $allowedDays = $this->normalizeDays($schedule->days_of_week);
        $scheduleDates = collect();

        // Generamos todas las fechas del periodo
        $period = CarbonPeriod::create($start, $end);
        
        foreach ($period as $date) {
            // Solo agregamos si el día coincide con la configuración de la sección (ej. Sábados)
            if (!empty($allowedDays) && in_array($date->dayOfWeekIso, $allowedDays)) {
                $scheduleDates->push($date->copy());
            }
        }

        // B) Obtener fechas reales donde YA se registró asistencia
        // Esto captura días extras o cambios de fecha que tienen asistencia
        $recordedDates = Attendance::where('course_schedule_id', $this->schedule_id)
            ->select('attendance_date')
            ->distinct()
            ->get()
            ->pluck('attendance_date')
            ->map(fn($date) => Carbon::parse($date));

        // C) Fusión: Días Programados + Días Extras con asistencia
        $datesCollection = $scheduleDates->merge($recordedDates)
            ->unique(fn($d) => $d->format('Y-m-d'))
            ->sortBy(fn($d) => $d->timestamp);

        // Convertimos a array de strings para la vista y consultas
        $dates = $datesCollection->map(fn($d) => $d->format('Y-m-d'))->values()->toArray();

        // 4. Mapeo de Enrollment ID -> Student ID
        $enrollmentMap = DB::table('enrollments')
            ->where('course_schedule_id', $this->schedule_id)
            // FILTRO CRÍTICO: Asegurar consistencia, solo enrollments activos
            ->whereNotIn('status', ['Pendiente', 'pendiente'])
            ->pluck('student_id', 'id');

        // 5. Consulta de Asistencias "CRUDA"
        // Usamos whereIn para traer solo las asistencias de los días calculados
        $attendances = DB::table('attendances')
            ->where('course_schedule_id', $this->schedule_id)
            ->whereIn('attendance_date', $dates)
            ->select('enrollment_id', 'attendance_date', 'status')
            ->get(); 

        // 6. Construcción de la Matriz en Memoria
        $attendanceMatrix = [];
        
        foreach ($attendances as $record) {
            $studentId = $enrollmentMap[$record->enrollment_id] ?? null;
            
            // Solo procesamos asistencias de estudiantes válidos (no pendientes)
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

    /**
     * Normaliza los días a un array de enteros ISO (1=Lunes ... 7=Domingo).
     */
    private function normalizeDays($days)
    {
        if (empty($days)) return [];

        // Si viene como string JSON o lista separada por comas, decodificar
        if (is_string($days)) {
            $decoded = json_decode($days, true);
            $days = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) 
                ? $decoded 
                : explode(',', $days);
        }

        if (!is_array($days)) return [];

        $map = [
            // ISO Standards
            1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7,
            
            // Español (sin tildes para búsqueda fácil)
            'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'miércoles' => 3, 
            'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'sábado' => 6, 'domingo' => 7,
            'lu' => 1, 'ma' => 2, 'mi' => 3, 'ju' => 4, 'vi' => 5, 'sa' => 6, 'do' => 7,
            
            // English
            'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7,
        ];

        $result = [];

        foreach ($days as $day) {
            // Caso 1: Entero o string numérico simple
            if (is_numeric($day)) {
                $val = (int)$day;
                if ($val >= 1 && $val <= 7) {
                    $result[] = $val;
                    continue;
                }
            }

            // Caso 2: Texto (Nombre del día)
            if (is_string($day)) {
                $clean = mb_strtolower(trim(str_replace(['"', "'", '[', ']', '{', '}'], '', $day)), 'UTF-8');
                $clean = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $clean);

                if (isset($map[$clean])) {
                    $result[] = $map[$clean];
                    continue;
                }

                // Búsqueda parcial (ej: "Sáb" coincide con "sabado")
                foreach ($map as $key => $val) {
                    if (is_string($key) && (str_starts_with($key, $clean) || str_starts_with($clean, $key))) {
                        $result[] = $val;
                        break;
                    }
                }
            }
        }

        return array_unique($result);
    }

    // --- REPORTE FINANCIERO (CORREGIDO Y ROBUSTO) ---
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
                'modules.price as module_price', 
                'course_schedules.section_name',
                'enrollments.status as enrollment_status',
                
                // Sumamos pagos confirmados
                DB::raw("SUM(CASE WHEN LOWER(payments.status) IN ('paid', 'pagado', 'completado', 'succeeded', 'aprobado') THEN payments.amount ELSE 0 END) as total_paid"),
                
                // Sumamos pagos pendientes
                DB::raw("SUM(CASE WHEN LOWER(payments.status) IN ('pending', 'pendiente', 'created') THEN payments.amount ELSE 0 END) as raw_pending_sum")
            );

        // 2. Aplicar Filtros
        if ($this->course_id) {
            $query->where('courses.id', $this->course_id);
        }

        if ($this->teacher_id) {
            $query->where('course_schedules.teacher_id', $this->teacher_id);
        }

        if ($this->date_from && $this->date_to) {
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
            'modules.price', 
            'course_schedules.section_name',
            'enrollments.status'
        );

        // 4. Ejecución
        $financials = $query->get();

        // 5. Post-procesamiento LÓGICO
        $financials = $financials->map(function ($item) {
            $paid = (float) $item->total_paid;
            $modulePrice = (float) $item->module_price;
            
            // Costo Total = Precio del Módulo
            $item->total_cost = $modulePrice;

            if ($paid > $item->total_cost) {
                $item->total_cost = $paid;
            }
            
            return $item;
        });

        // 6. Filtrado final por estado seleccionado en el UI
        if ($this->payment_status !== 'all') {
            $financials = $financials->filter(function($item) {
                $paid = (float) $item->total_paid;
                $cost = (float) $item->total_cost;
                $balance = round($cost - $paid, 2);

                if ($this->payment_status == 'paid') return $balance <= 0 && $paid > 0; 
                if ($this->payment_status == 'pending') return $balance > 0; 
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
    
    // --- OTROS REPORTES (ACTUALIZADOS PARA EXCLUIR PENDIENTES) ---
    
    public function generateGradesReport()
    {
        $schedule = CourseSchedule::with(['module.course', 'teacher'])->find($this->schedule_id);
        
        $enrollments = Enrollment::with('student')
            ->where('course_schedule_id', $this->schedule_id)
            ->whereNotIn('status', ['Pendiente', 'pendiente']) // Excluir pendientes
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
            ->whereNotIn('status', ['Pendiente', 'pendiente']) // Excluir pendientes
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