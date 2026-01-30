<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Payment;
use App\Models\PaymentConcept;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class FinanceDashboard extends Component
{
    use WithPagination;

    // Filtros
    public $search = '';
    public $statusFilter = '';
    public $dateFilter = 'this_month'; // 'all', 'today', 'this_week', 'this_month', 'last_month'

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
        // Carga inicial ligera de KPIs
        $this->calculateKPIs();
    }

    public function loadChart()
    {
        $this->readyToLoad = true;
        $this->prepareChartData();
        
        $this->dispatch('finance-chart-loaded', [
            'income' => $this->chartDataIncome,
            'pending' => $this->chartDataPending,
            'labels' => $this->chartLabels
        ]);
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedDateFilter() { 
        $this->resetPage(); 
        $this->calculateKPIs(); // Recalcular KPIs al cambiar fecha
        $this->loadChart(); // Recargar gráfico
    }

    private function calculateKPIs()
    {
        $query = Payment::query();
        $this->applyDateFilter($query);

        // Usamos clones para no afectar la query base si fuera necesario, 
        // pero aquí son consultas separadas de agregación.
        
        // Ingresos Reales (Completados)
        $this->totalIncome = (clone $query)->whereIn('status', ['Completado', 'Pagado'])->sum('amount');

        // Deuda Pendiente (Pendientes)
        $this->totalPending = (clone $query)->whereIn('status', ['Pendiente', 'pendiente'])->sum('amount');

        // Total Transacciones
        $this->transactionsCount = (clone $query)->count();
    }

    private function prepareChartData()
    {
        // Gráfico de los últimos 6 meses (fijo, independiente del filtro de tabla para dar contexto)
        $months = 6;
        $this->chartLabels = [];
        $this->chartDataIncome = [];
        $this->chartDataPending = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = ucfirst($date->locale('es')->isoFormat('MMM'));
            $this->chartLabels[] = $monthName;

            // Ingresos del mes
            $income = Payment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereIn('status', ['Completado', 'Pagado'])
                ->sum('amount');
            
            // Deuda generada en el mes (que sigue pendiente o se generó pendiente en ese mes)
            // Nota: Esto es aproximado, muestra cuánto se "facturó" y quedó pendiente en ese mes.
            $pending = Payment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereIn('status', ['Pendiente', 'pendiente'])
                ->sum('amount');

            $this->chartDataIncome[] = $income;
            $this->chartDataPending[] = $pending;
        }
    }

    private function applyDateFilter($query)
    {
        switch ($this->dateFilter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'last_month':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                      ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;
            case 'all':
            default:
                // No aplicar filtro de fecha
                break;
        }
    }

    public function render()
    {
        $paymentsQuery = Payment::with(['student.user', 'paymentConcept'])
            ->latest();

        // Aplicar Filtros
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