<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Payment;
use App\Models\PaymentConcept;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache; // <-- Importante

use Livewire\Attributes\On;

#[Layout('layouts.dashboard')]
class FinanceDashboard extends Component
{
    use WithPagination;

    // Filtros
    public $search = '';
    public $statusFilter = '';
    public $dateFilter = 'this_month'; 

    // Datos del Gráfico (Lazy Loading)
    public $readyToLoad = false;
    public $chartDataIncome = [];
    public $chartDataPending = [];
    public $chartLabels = [];

    // KPIs
    public $totalIncome = 0;
    public $totalPending = 0;
    public $transactionsCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'dateFilter' => ['except' => 'this_month'],
    ];

    public function mount()
    {
        $this->calculateKPIs();
    }

    #[On('paymentAdded')]
    public function refreshData()
    {
        $this->calculateKPIs();
        if ($this->readyToLoad) {
            $this->loadChart();
        }
    }

    /**
     * Carga el gráfico usando caché inteligente.
     */
    public function loadChart()
    {
        $this->readyToLoad = true;
        
        // 1. Obtener versión actual de datos (gestionada por PaymentObserver)
        $version = Cache::get('finance_data_version', 'init');

        // 2. Llave única. Si PaymentObserver cambia la versión, esta llave cambia automáticamente.
        $cacheKey = "finance_chart_data_v{$version}";

        // Cacheamos por 24 horas (o hasta que cambie la versión)
        $chartData = Cache::remember($cacheKey, 60 * 60 * 24, function () {
            $this->prepareChartData(); // Rellena las variables locales
            return [
                'income' => $this->chartDataIncome,
                'pending' => $this->chartDataPending,
                'labels' => $this->chartLabels
            ];
        });

        // Restaurar datos desde caché
        $this->chartDataIncome = $chartData['income'];
        $this->chartDataPending = $chartData['pending'];
        $this->chartLabels = $chartData['labels'];
        
        $this->dispatch('finance-chart-loaded', $chartData);
    }

    /**
     * Re-emite los datos del gráfico cuando el usuario navega hacia atrás usando SPA.
     */
    #[On('triggerLoadChart')]
    public function reloadChartForNavigation()
    {
        if ($this->readyToLoad && !empty($this->chartLabels)) {
            $this->dispatch('finance-chart-loaded', [
                'income' => $this->chartDataIncome,
                'pending' => $this->chartDataPending,
                'labels' => $this->chartLabels
            ]);
        } else {
            $this->loadChart();
        }
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    
    public function updatedDateFilter() { 
        $this->resetPage(); 
        $this->calculateKPIs(); 
        if ($this->readyToLoad) $this->loadChart();
    }

    /**
     * Calcula los totales (KPIs) usando caché inteligente.
     */
    private function calculateKPIs()
    {
        $version = Cache::get('finance_data_version', 'init');
        
        // Llave compuesta: Versión + Filtros
        $cacheKey = "finance_kpis_v{$version}_" . $this->dateFilter . '_' . $this->statusFilter . '_' . md5($this->search);

        $kpis = Cache::remember($cacheKey, 60 * 60 * 24, function () {
            $query = Payment::query();
            $this->applyDateFilter($query);
            
            if ($this->search) {
                 $query->where(function($q) {
                    $q->whereHas('student.user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })->orWhere('transaction_id', 'like', '%' . $this->search . '%');
                });
            }

            $aggregate = (clone $query)->selectRaw("
                COALESCE(SUM(CASE WHEN status IN ('Completado', 'Pagado') THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN LOWER(status) = 'pendiente' THEN amount ELSE 0 END), 0) as pending,
                COUNT(*) as total_count
            ")->first();

            return [
                'income' => $aggregate->income ?? 0,
                'pending' => $aggregate->pending ?? 0,
                'count' => $aggregate->total_count ?? 0
            ];
        });

        $this->totalIncome = $kpis['income'];
        $this->totalPending = $kpis['pending'];
        $this->transactionsCount = $kpis['count'];
    }

    private function prepareChartData()
    {
        $months = 6;
        $this->chartLabels = [];
        $this->chartDataIncome = [];
        $this->chartDataPending = [];

        // Hacemos una única consulta agrupada para los 6 meses
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $selectRaw = "
                cast(strftime('%Y', created_at) as integer) as year,
                cast(strftime('%m', created_at) as integer) as month,
                SUM(CASE WHEN status IN ('Completado', 'Pagado') THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN LOWER(status) = 'pendiente' THEN amount ELSE 0 END) as pending
            ";
        } else {
            $selectRaw = "
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                SUM(CASE WHEN status IN ('Completado', 'Pagado') THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN LOWER(status) = 'pendiente' THEN amount ELSE 0 END) as pending
            ";
        }

        // Extraemos los datos agrupados por año y mes
        $stats = Payment::selectRaw($selectRaw)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = ucfirst($date->locale('es')->isoFormat('MMM'));
            $key = $date->format('Y-m');

            $this->chartLabels[] = $monthName;
            $this->chartDataIncome[] = $stats->has($key) ? $stats[$key]->income : 0;
            $this->chartDataPending[] = $stats->has($key) ? $stats[$key]->pending : 0;
        }
    }

    private function applyDateFilter(Builder $query)
    {
        switch ($this->dateFilter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'last_month':
                $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;
        }
    }

    public function render()
    {
        // La lista paginada NO se cachea porque depende de la página actual y es ligera
        $paymentsQuery = Payment::with(['student.user', 'paymentConcept'])
            ->latest('updated_at');

        $this->applyDateFilter($paymentsQuery);

        if ($this->search) {
            $paymentsQuery->where(function($q) {
                $q->whereHas('student.user', function ($uq) {
                    $uq->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('transaction_id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $paymentsQuery->where('status', $this->statusFilter);
        }

        $payments = $paymentsQuery->simplePaginate(15);

        return view('livewire.admin.finance-dashboard', [
            'payments' => $payments
        ]);
    }
}