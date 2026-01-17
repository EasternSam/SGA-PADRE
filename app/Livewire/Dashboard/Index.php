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
use Spatie\Permission\Models\Role; // Importamos el modelo Role

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

    // VARIABLE DE DEBUG (Para ver en herramientas de desarrollo o dump)
    public $debugTrace = [];

    /**
     * Carga inicial de datos estáticos.
     */
    public function mount(WordpressApiService $wpService)
    {
        $this->recentActivities = new Collection();
        $this->addTrace('Inicio del componente Dashboard');

        try {
            $this->totalStudents = Student::count();
            $this->totalCourses = Course::count();
            $this->totalEnrollments = Enrollment::count();
            
            // Verificación segura de roles
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $this->totalTeachers = 0;
                
                // Intentamos buscar 'teacher'
                if (Role::where('name', 'teacher')->exists()) {
                    $this->totalTeachers = User::role('teacher')->count();
                } 
                // Si es 0 o no existe, intentamos buscar 'Profesor'
                elseif (Role::where('name', 'Profesor')->exists()) {
                    $this->totalTeachers = User::role('Profesor')->count();
                }
                // Si tampoco existe, se queda en 0 y no explota
            } else {
                 $this->totalTeachers = 0; 
            }

            if (class_exists(ActivityLog::class)) {
                $this->recentActivities = ActivityLog::with('causer')
                    ->latest()
                    ->take(5)
                    ->get();
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
        $entry = Carbon::now()->toTimeString() . ' - ' . $message;
        if ($data !== null) {
            $entry .= ' | Data: ' . json_encode($data);
        }
        $this->debugTrace[] = $entry;
        // También logueamos a archivo para persistencia
        Log::info('[DASHBOARD_DEBUG] ' . $message, is_array($data) ? $data : []);
    }

    /**
     * Prepara los datos para el gráfico de inscripciones.
     */
    private function prepareChartData(WordpressApiService $wpService)
    {
        $this->chartLabels = [];
        $this->chartDataWeb = [];
        $this->chartDataSystem = [];

        // 1. Obtener datos de WordPress
        $this->addTrace('Solicitando datos a WordPress API...');
        
        try {
            $wpStats = $wpService->getEnrollmentStats();
            $this->addTrace('Respuesta cruda de WP', $wpStats);
        } catch (\Exception $e) {
            $this->addTrace('Excepción al conectar con WP: ' . $e->getMessage());
            $wpStats = ['labels' => [], 'data' => []];
        }

        // Mapa de datos normalizado
        $wpDataMap = [];
        
        if (!empty($wpStats['labels']) && !empty($wpStats['data'])) {
            foreach ($wpStats['labels'] as $index => $label) {
                if (isset($wpStats['data'][$index])) {
                    // Normalización agresiva: minúsculas, sin puntos, sin espacios
                    $cleanLabel = $this->normalizeLabel($label);
                    $wpDataMap[$cleanLabel] = $wpStats['data'][$index];
                    $this->addTrace("Mapeando WP: Original='{$label}' -> Clean='{$cleanLabel}' -> Val={$wpStats['data'][$index]}");
                }
            }
        } else {
            $this->addTrace('ADVERTENCIA: WP devolvió datos vacíos o estructura incorrecta.');
        }

        // 2. Construir datos de los últimos 7 meses
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            // Generar etiqueta local (Laravel)
            // Forzamos locale español explícitamente para asegurar coincidencia
            $monthNameShort = $date->locale('es')->isoFormat('MMM'); 
            $monthLabel = ucfirst(str_replace('.', '', $monthNameShort)); // "Ene", "Feb" (sin puntos)
            
            $this->chartLabels[] = $monthLabel;

            // --- Matching WEB ---
            $searchKey = $this->normalizeLabel($monthNameShort);
            $webCount = $wpDataMap[$searchKey] ?? 0;

            // Debug del matching específico
            if (!isset($wpDataMap[$searchKey]) && !empty($wpDataMap)) {
                $this->addTrace("Fallo de coincidencia para: '{$monthLabel}' (Key buscada: '{$searchKey}'). Claves disponibles en WP: " . implode(', ', array_keys($wpDataMap)));
            }

            $this->chartDataWeb[] = (int) $webCount;

            // --- Datos SISTEMA ---
            $systemCount = 0;
            
            // Consulta de fechas locales
            $enrollmentQuery = Enrollment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            if (class_exists(ActivityLog::class)) {
                $enrollmentIds = $enrollmentQuery->pluck('id');
                
                if ($enrollmentIds->isNotEmpty()) {
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
        
        $this->addTrace('Datos Finales Calculados', [
            'Labels' => $this->chartLabels,
            'Web' => $this->chartDataWeb,
            'System' => $this->chartDataSystem
        ]);
    }

    /**
     * Normaliza una etiqueta de mes para comparación (quita puntos, espacios, minúsculas)
     */
    private function normalizeLabel($label)
    {
        // Convertir a minúsculas, quitar puntos (ej: "ene." -> "ene"), quitar espacios
        return strtolower(trim(str_replace('.', '', $label)));
    }

    public function setFilter($status)
    {
        $this->enrollmentFilter = $status;
    }

    public function render()
    {
        $query = Enrollment::with([
            'student.user', 
            'courseSchedule.module.course', 
            'courseSchedule.teacher'
        ])->latest();

        if ($this->enrollmentFilter !== 'all') {
            $query->where('status', $this->enrollmentFilter);
        }

        $recentEnrollments = $query->take(5)->get();

        return view('livewire.dashboard.index', [
            'recentEnrollments' => $recentEnrollments,
            'chartLabels' => $this->chartLabels,
            'chartDataWeb' => $this->chartDataWeb,
            'chartDataSystem' => $this->chartDataSystem,
        ]);
    }
}