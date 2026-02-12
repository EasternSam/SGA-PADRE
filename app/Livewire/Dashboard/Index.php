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

    // Listeners para actualizar caché si ocurre algo en otro lado
    protected $listeners = [
        'studentAdded' => 'refreshStats', 
        'courseAdded' => 'refreshStats', 
        'enrollmentUpdated' => 'refreshStats',
        '$refresh'
    ];

    public function mount()
    {
        $this->recentActivities = collect(); 
        $this->addTrace('Inicio del componente Dashboard (Mount)');

        $this->loadCachedStats();
    }

    public function refreshStats()
    {
        // Forzar recálculo
        Cache::forget('dashboard_stats_counts_v' . Cache::get('dashboard_version', 'init'));
        $this->loadCachedStats();
    }

    /**
     * Carga estadísticas usando caché inteligente basado en versión.
     */
    private function loadCachedStats()
    {
        // 1. Obtener versión global del dashboard (gestionada por Observers)
        // Si no existe, usamos 'init'.
        $version = Cache::get('dashboard_version', 'init');

        // 2. Clave única por versión.
        $cacheKey = "dashboard_stats_counts_v{$version}";

        // 3. Cachear por 24 horas (o hasta que cambie la versión)
        $stats = Cache::remember($cacheKey, 60 * 60 * 24, function () {
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
        
        $this->loadRecentActivities();
        $this->prepareChartData($wpService);

        $this->dispatch('stats-loaded', [
            'web' => $this->chartDataWeb,
            'system' => $this->chartDataSystem,
            'labels' => $this->chartLabels
        ]);
    }

    private function loadRecentActivities()
    {
        if (class_exists(ActivityLog::class)) {
            // Cache corto para actividades (1 min) o versión
            $this->recentActivities = Cache::remember('dashboard_recent_activities', 60, function() {
                 return ActivityLog::with('user:id,name') // CORREGIDO: user en lugar de causer
                    ->latest()
                    ->take(5)
                    // CORREGIDO: solicitamos las columnas de nuestra nueva tabla de auditoría
                    ->get(['id', 'description', 'user_id', 'created_at', 'action', 'payload']);
            });
        }
    }

    private function addTrace($message, $data = null)
    {
        // (Lógica de traza mantenida igual)
        $dataPreview = $data;
        if (is_array($data) && count($data) > 20) {
            $dataPreview = array_slice($data, 0, 20) + ['...' => 'truncated'];
        } elseif (is_string($data) && strlen($data) > 500) {
            $dataPreview = substr($data, 0, 500) . '...';
        }
        $entry = Carbon::now()->toTimeString() . ' - ' . $message;
        $this->debugTrace[] = $entry;
    }

    private function prepareChartData(WordpressApiService $wpService)
    {
        $this->chartLabels = [];
        $this->chartDataWeb = [];
        $this->chartDataSystem = [];

        // Cache para WP API (1 hora) - Independiente de la versión del sistema local
        $wpStats = Cache::remember('dashboard_wp_stats', 3600, function () use ($wpService) {
            $this->addTrace('Cache Miss: Solicitando datos a WordPress API...');
            try {
                return $wpService->getEnrollmentStats();
            } catch (\Exception $e) {
                return ['labels' => [], 'data' => []];
            }
        });

        $wpDataMap = [];
        if (!empty($wpStats['labels']) && !empty($wpStats['data'])) {
            foreach ($wpStats['labels'] as $index => $label) {
                if (isset($wpStats['data'][$index])) {
                    $cleanLabel = $this->normalizeLabel($label);
                    $wpDataMap[$cleanLabel] = $wpStats['data'][$index];
                }
            }
        }

        // Cache para Gráfico Local (Vinculado a versión Dashboard)
        // Usamos la misma versión que los contadores para invalidar si entra un alumno
        $version = Cache::get('dashboard_version', 'init');
        $cacheKey = "dashboard_chart_system_v{$version}";

        $systemData = Cache::remember($cacheKey, 60 * 60 * 24, function() {
             // ... Lógica pesada de agrupación por meses ...
             $startDate = Carbon::now()->subMonths(11)->startOfMonth();
             $driver = DB::connection()->getDriverName();
             
             if ($driver === 'sqlite') {
                $selectRaw = "strftime('%Y', created_at) as year, strftime('%m', created_at) as month, count(*) as total";
                $groupBy = ['year', 'month'];
             } else {
                $selectRaw = 'YEAR(created_at) as year, MONTH(created_at) as month, count(*) as total';
                $groupBy = ['year', 'month'];
             }

             return Enrollment::selectRaw($selectRaw)
                ->where('created_at', '>=', $startDate)
                ->groupBy(...$groupBy)
                ->get()
                ->keyBy(function($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });
        });

        // Construir datos finales (Combinación rápida en memoria)
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthNameShort = $date->locale('es')->isoFormat('MMM'); 
            $monthLabel = ucfirst(str_replace('.', '', $monthNameShort)); 
            
            $this->chartLabels[] = $monthLabel;
            $lookupKey = $date->format('Y-m');

            // Leer de la colección cacheada
            $totalEnrollmentsInMonth = isset($systemData[$lookupKey]) ? $systemData[$lookupKey]->total : 0;

            $searchKey = $this->normalizeLabel($monthNameShort);
            $webCount = (int) ($wpDataMap[$searchKey] ?? 0);
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
        $recentEnrollments = collect();

        if ($this->readyToLoad) {
            // Lista reciente NO se cachea por versión larga, 
            // ya que necesitamos ver el último registro al instante.
            // Podríamos usar un caché muy corto (10s) o dejarlo directo.
            $query = Enrollment::with([
                'student:id,user_id,first_name,last_name,email', 
                'student.user:id,name,email', 
                'courseSchedule:id,module_id,teacher_id,section_name', 
                'courseSchedule.module:id,course_id,name', 
                'courseSchedule.module.course:id,name', 
                'courseSchedule.teacher:id,name' 
            ])->latest();

            if ($this->enrollmentFilter !== 'all') {
                $query->where('status', $this->enrollmentFilter);
            }

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