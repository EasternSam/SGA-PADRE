<?php

namespace App\Livewire\Dashboard;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\WordpressApiService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    // Estadísticas (Contadores Estáticos)
    public $totalStudents = 0;
    public $totalCourses = 0;
    public $totalTeachers = 0;
    public $totalEnrollments = 0;
    
    // Estado del Filtro de Inscripciones
    public $enrollmentFilter = 'all'; 

    // Colección para actividades
    public Collection $recentActivities;

    // Datos para el gráfico
    public $chartLabels = [];
    public $chartDataWeb = [];
    public $chartDataSystem = [];

    // VARIABLE DE DEBUG
    public $debugTrace = [];

    /**
     * Carga inicial de datos estáticos.
     */
    public function mount(WordpressApiService $wpService)
    {
        // Inicializar como colección vacía por defecto
        $this->recentActivities = new Collection();
        $this->addTrace('Inicio del componente Dashboard');

        try {
            // Optimizaciones de conteo: count() es ligero, pero aseguramos
            $this->totalStudents = Student::count();
            $this->totalCourses = Course::count();
            $this->totalEnrollments = Enrollment::count();
            
            // Verificación de roles segura
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $this->totalTeachers = 0;
                // Verificar existencia antes de contar
                if (Role::where('name', 'teacher')->exists()) {
                    $this->totalTeachers = User::role('teacher')->count();
                } elseif (Role::where('name', 'Profesor')->exists()) {
                    $this->totalTeachers = User::role('Profesor')->count();
                }
            } else {
                 $this->totalTeachers = 0; 
            }

            // Cargar actividades recientes optimizado
            if (class_exists(ActivityLog::class)) {
                $this->recentActivities = ActivityLog::with('causer:id,name') // Solo traer ID y nombre del usuario
                    ->latest()
                    ->take(5)
                    ->get(['id', 'description', 'causer_id', 'created_at']); // Solo campos necesarios
            }

            // Preparar gráfico
            $this->prepareChartData($wpService);

        } catch (\Exception $e) {
            $this->addTrace('ERROR CRÍTICO EN MOUNT: ' . $e->getMessage());
            Log::error("Dashboard Error: " . $e->getMessage());
        }
    }

    /**
     * Helper para añadir trazas de debug
     */
    private function addTrace($message, $data = null)
    {
        // Limitamos el tamaño de la data logueada para ahorrar memoria
        $dataPreview = $data;
        if (is_array($data) && count($data) > 20) {
            $dataPreview = array_slice($data, 0, 20) + ['...' => 'truncated'];
        }

        $entry = Carbon::now()->toTimeString() . ' - ' . $message;
        $this->debugTrace[] = $entry;
        
        Log::info('[DASHBOARD_DEBUG] ' . $message, is_array($dataPreview) ? $dataPreview : []);
    }

    /**
     * Prepara los datos para el gráfico de inscripciones.
     */
    private function prepareChartData(WordpressApiService $wpService)
    {
        $this->chartLabels = [];
        $this->chartDataWeb = [];
        $this->chartDataSystem = [];

        $this->addTrace('Solicitando datos a WordPress API...');
        
        try {
            $wpStats = $wpService->getEnrollmentStats();
            $this->addTrace('Respuesta cruda de WP recibida'); // Evitamos loguear todo el array grande
        } catch (\Exception $e) {
            $this->addTrace('Excepción al conectar con WP: ' . $e->getMessage());
            $wpStats = ['labels' => [], 'data' => []];
        }

        // Mapa de datos normalizado
        $wpDataMap = [];
        if (!empty($wpStats['labels']) && !empty($wpStats['data'])) {
            foreach ($wpStats['labels'] as $index => $label) {
                if (isset($wpStats['data'][$index])) {
                    $cleanLabel = $this->normalizeLabel($label);
                    $wpDataMap[$cleanLabel] = $wpStats['data'][$index];
                }
            }
        }

        // 2. Construir datos de los últimos 7 meses
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            // Generar etiqueta local
            $monthNameShort = $date->locale('es')->isoFormat('MMM'); 
            $monthLabel = ucfirst(str_replace('.', '', $monthNameShort)); 
            
            $this->chartLabels[] = $monthLabel;

            // --- Matching WEB ---
            $searchKey = $this->normalizeLabel($monthNameShort);
            $webCount = $wpDataMap[$searchKey] ?? 0;
            $this->chartDataWeb[] = (int) $webCount;

            // --- Datos SISTEMA ---
            $systemCount = 0;
            
            // Consulta optimizada: Usamos count directo en BD en lugar de traer modelos
            $enrollmentQuery = Enrollment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            if (class_exists(ActivityLog::class)) {
                // Solo obtenemos IDs, no objetos completos
                $enrollmentIds = $enrollmentQuery->pluck('id');
                
                if ($enrollmentIds->isNotEmpty()) {
                    // Optimizamos la consulta de logs para contar solo IDs distintos
                    $systemCount = ActivityLog::where('subject_type', Enrollment::class)
                        ->whereIn('subject_id', $enrollmentIds)
                        ->where('event', 'created')
                        ->whereNotNull('causer_id')
                        ->distinct('subject_id')
                        ->count('subject_id');
                }
            } else {
                $systemCount = $enrollmentQuery->count();
            }

            $this->chartDataSystem[] = (int) $systemCount;
        }
        
        $this->addTrace('Datos Finales Calculados (Resumen)', [
            'Labels' => $this->chartLabels,
            'Web_Count' => array_sum($this->chartDataWeb),
            'System_Count' => array_sum($this->chartDataSystem)
        ]);
    }

    private function normalizeLabel($label)
    {
        return strtolower(trim(str_replace('.', '', $label)));
    }

    public function setFilter($status)
    {
        $this->enrollmentFilter = $status;
    }

    public function render()
    {
        // Consulta base optimizada con Eager Loading selectivo
        $query = Enrollment::with([
            'student:id,name,last_name,email,user_id', // Solo campos necesarios de student
            'student.user:id,name,email', // Solo campos necesarios de user
            'courseSchedule:id,module_id,teacher_id', // Solo campos necesarios de schedule
            'courseSchedule.module:id,course_id,name', // Solo campos necesarios de module
            'courseSchedule.module.course:id,name', // Solo campos necesarios de course
            'courseSchedule.teacher:id,name' // Solo campos necesarios de teacher
        ])->latest();

        if ($this->enrollmentFilter !== 'all') {
            $query->where('status', $this->enrollmentFilter);
        }

        // Limitamos los campos de la tabla principal también
        $recentEnrollments = $query->take(5)->get();

        return view('livewire.dashboard.index', [
            'recentEnrollments' => $recentEnrollments,
            'chartLabels' => $this->chartLabels,
            'chartDataWeb' => $this->chartDataWeb,
            'chartDataSystem' => $this->chartDataSystem,
        ]);
    }
}