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

    // Listener para actualizar si se registra un pago en otra pestaña/modal
    protected $listeners = ['paymentAdded' => 'refreshData', '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'dateFilter' => ['except' => 'this_month'],
    ];

    public function mount()
    {
        $this->calculateKPIs();
    }

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

            // Usamos clones para consultas limpias de agregación
            return [
                'income' => (clone $query)->whereIn('status', ['Completado', 'Pagado'])->sum('amount'),
                'pending' => (clone $query)->whereIn('status', ['Pendiente', 'pendiente'])->sum('amount'),
                'count' => (clone $query)->count()
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

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = ucfirst($date->locale('es')->isoFormat('MMM'));
            $this->chartLabels[] = $monthName;

            // Optimizamos usando rangos de fecha (aprovecha índices de BD)
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $income = Payment::whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['Completado', 'Pagado'])
                ->sum('amount');
            
            $pending = Payment::whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['Pendiente', 'pendiente'])
                ->sum('amount');

            $this->chartDataIncome[] = $income;
            $this->chartDataPending[] = $pending;
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
            ->latest();

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

        $payments = $paymentsQuery->paginate(15);

        return view('livewire.admin.finance-dashboard', [
            'payments' => $payments
        ]);
    }
}