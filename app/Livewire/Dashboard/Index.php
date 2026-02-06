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
use Illuminate\Support\Facades\Cache; // Importar Cache
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

    // Colección para actividades (Inicializada vacía)
    public $recentActivities;

    // Datos para el gráfico
    public $chartLabels = [];
    public $chartDataWeb = [];
    public $chartDataSystem = [];

    // Bandera para Lazy Loading
    public $readyToLoad = false;

    // VARIABLE DE DEBUG
    public $debugTrace = [];

    public function mount()
    {
        $this->recentActivities = collect(); // Colección vacía inicial
        $this->addTrace('Inicio del componente Dashboard (Mount)');

        // Carga de contadores LIGERA (con caché corto de 5 minutos para evitar queries repetitivas en F5)
        // Esto es opcional, pero ayuda si hay muchos usuarios concurrentes.
        $stats = Cache::remember('dashboard_stats_counts', 300, function () {
            return [
                'students' => Student::count(),
                'courses' => Course::count(),
                'enrollments' => Enrollment::count(),
                'teachers' => $this->countTeachers(),
            ];
        });

        $this->totalStudents = $stats['students'];
        $this->totalCourses = $stats['courses'];
        $this->totalEnrollments = $stats['enrollments'];
        $this->totalTeachers = $stats['teachers'];
    }

    /**
     * Cuenta profesores de manera segura verificando la existencia de la tabla/rol.
     */
    private function countTeachers()
    {
        if (!class_exists(\Spatie\Permission\Models\Role::class)) {
            return 0;
        }

        try {
            // Intentar buscar por nombre de rol en una sola query optimizada
            // Asumiendo que la relación users() en Role existe y es correcta.
            $roleName = Role::whereIn('name', ['teacher', 'Profesor'])->first()?->name;
            
            if ($roleName) {
                return User::role($roleName)->count();
            }
        } catch (\Exception $e) {
            Log::error('Error contando profesores: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * Método invocado por wire:init para cargar datos pesados (API, Gráficos, Logs).
     */
    public function loadStats(WordpressApiService $wpService)
    {
        $this->readyToLoad = true;
        
        // 1. Cargar Actividades Recientes (Ahora es Lazy)
        $this->loadRecentActivities();

        // 2. Preparar gráfico (Llamada API pesada con Caché)
        $this->prepareChartData($wpService);

        // Disparar evento para que el JS renderice el gráfico
        $this->dispatch('stats-loaded', [
            'web' => $this->chartDataWeb,
            'system' => $this->chartDataSystem,
            'labels' => $this->chartLabels
        ]);
    }

    private function loadRecentActivities()
    {
        if (class_exists(ActivityLog::class)) {
            // Traemos solo las columnas necesarias para pintar la lista
            $this->recentActivities = ActivityLog::with('causer:id,name') 
                ->latest()
                ->take(5)
                ->get(['id', 'description', 'causer_id', 'created_at', 'properties']); // Agregué properties por si acaso
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
            $dataPreview = array_slice($data, 0, 20) + ['...' => 'truncated (ahorro de memoria)'];
        } elseif (is_string($data) && strlen($data) > 500) {
            $dataPreview = substr($data, 0, 500) . '... (truncated)';
        }

        $entry = Carbon::now()->toTimeString() . ' - ' . $message;
        $this->debugTrace[] = $entry;
        
        // Log::info('[DASHBOARD_DEBUG] ' . $message, is_array($dataPreview) ? $dataPreview : []);
    }

    /**
     * Prepara los datos para el gráfico de inscripciones.
     * AHORA CON CACHÉ DE 1 HORA PARA LA API DE WORDPRESS Y COMPATIBILIDAD SQLITE.
     */
    private function prepareChartData(WordpressApiService $wpService)
    {
        $this->chartLabels = [];
        $this->chartDataWeb = [];
        $this->chartDataSystem = [];

        // Cacheamos la respuesta de WP por 1 hora (3600 seg) para no saturar la API externa
        $wpStats = Cache::remember('dashboard_wp_stats', 3600, function () use ($wpService) {
            $this->addTrace('Cache Miss: Solicitando datos a WordPress API...');
            try {
                return $wpService->getEnrollmentStats();
            } catch (\Exception $e) {
                $this->addTrace('Excepción al conectar con WP: ' . $e->getMessage());
                return ['labels' => [], 'data' => []];
            }
        });

        // Mapa de datos normalizado de la Web (API)
        $wpDataMap = [];
        if (!empty($wpStats['labels']) && !empty($wpStats['data'])) {
            foreach ($wpStats['labels'] as $index => $label) {
                if (isset($wpStats['data'][$index])) {
                    $cleanLabel = $this->normalizeLabel($label);
                    $wpDataMap[$cleanLabel] = $wpStats['data'][$index];
                }
            }
        }

        // Optimización SQL Compatible: Obtener conteos agrupados por mes
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $driver = DB::connection()->getDriverName();

        // Selección de sintaxis según driver (SQLite vs MySQL)
        if ($driver === 'sqlite') {
            $selectRaw = "strftime('%Y', created_at) as year, strftime('%m', created_at) as month, count(*) as total";
            $groupBy = ['year', 'month'];
        } else {
            // MySQL / MariaDB / PostgreSQL (generalmente compatible con YEAR/MONTH o extract)
            $selectRaw = 'YEAR(created_at) as year, MONTH(created_at) as month, count(*) as total';
            $groupBy = ['year', 'month'];
        }

        $systemEnrollmentsByMonth = Enrollment::selectRaw($selectRaw)
            ->where('created_at', '>=', $startDate)
            ->groupBy(...$groupBy) // Desempaquetar array para groupBy
            ->get()
            ->keyBy(function($item) {
                // Normalizar keys para búsqueda rápida
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        // 2. Construir datos de los últimos 12 meses
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            // Generar etiqueta local
            $monthNameShort = $date->locale('es')->isoFormat('MMM'); 
            $monthLabel = ucfirst(str_replace('.', '', $monthNameShort)); 
            
            $this->chartLabels[] = $monthLabel;

            // Key para buscar en la colección agrupada (YYYY-MM)
            $lookupKey = $date->format('Y-m');

            // --- 1. Obtener TOTAL REAL de inscripciones del mes (Desde la colección, 0 DB queries aquí) ---
            $totalEnrollmentsInMonth = isset($systemEnrollmentsByMonth[$lookupKey]) 
                ? $systemEnrollmentsByMonth[$lookupKey]->total 
                : 0;

            // --- 2. Obtener inscripciones WEB (Desde la API de WP cacheada) ---
            $searchKey = $this->normalizeLabel($monthNameShort);
            $webCount = (int) ($wpDataMap[$searchKey] ?? 0);
            
            // --- 3. Calcular inscripciones SISTEMA (Físico) ---
            $systemCount = max(0, $totalEnrollmentsInMonth - $webCount);

            $this->chartDataWeb[] = $webCount;
            $this->chartDataSystem[] = $systemCount;
        }
        
        $this->addTrace('Datos Finales Calculados');
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
        // Inicializar como colección vacía para evitar errores si readyToLoad es falso
        $recentEnrollments = collect();

        // Solo cargamos la tabla si el componente está listo (Lazy Loading visual)
        if ($this->readyToLoad) {
            // Consulta base optimizada con Eager Loading selectivo para la TABLA
            // Solo traemos los campos necesarios para pintar la tabla
            $query = Enrollment::with([
                'student:id,user_id,first_name,last_name,email', // Optimizado
                'student.user:id,name,email', // Optimizado
                'courseSchedule:id,module_id,teacher_id,section_name', // Optimizado
                'courseSchedule.module:id,course_id,name', 
                'courseSchedule.module.course:id,name', 
                'courseSchedule.teacher:id,name' 
            ])->latest();

            if ($this->enrollmentFilter !== 'all') {
                $query->where('status', $this->enrollmentFilter);
            }

            // Limitamos los campos de la tabla principal también
            $recentEnrollments = $query->take(5)->get();
        }

        return view('livewire.dashboard.index', [
            'recentEnrollments' => $recentEnrollments,
            'chartLabels' => $this->chartLabels,
            'chartDataWeb' => $this->chartDataWeb,
            'chartDataSystem' => $this->chartDataSystem,
        ]);
    }
}