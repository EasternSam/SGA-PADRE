<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentConcept;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FinanceDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $statusFilter = $request->input('statusFilter', '');
        $dateFilter = $request->input('dateFilter', 'this_month');

        // 1. Filtrado para KPIs y Transacciones
        $query = Payment::query();
        $this->applyDateFilter($query, $dateFilter);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student.user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('ncf', 'like', "%{$search}%");
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // KPIs calculados
        $aggregate = (clone $query)->selectRaw("
            COALESCE(SUM(CASE WHEN status IN ('Completado', 'Pagado') THEN amount ELSE 0 END), 0) as income,
            COALESCE(SUM(CASE WHEN LOWER(status) = 'pendiente' THEN amount ELSE 0 END), 0) as pending,
            COUNT(*) as total_count
        ")->first();

        $totalIncome = (float) ($aggregate->income ?? 0);
        $totalPending = (float) ($aggregate->pending ?? 0);
        $transactionsCount = (int) ($aggregate->total_count ?? 0);

        // 2. Transacciones
        $payments = (clone $query)
            ->with(['student.user', 'paymentConcept'])
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function ($p) {
                $studentUser = $p->student->user ?? null;
                $studentName = $studentUser->name ?? ($p->student->name ?? 'Estudiante General');
                $studentEmail = $studentUser->email ?? ($p->student->email ?? '');

                return [
                    'id' => $p->id,
                    'transaction_id' => $p->transaction_id,
                    'student_id' => $p->student_id,
                    'student_name' => $studentName,
                    'student_email' => $studentEmail,
                    'concept_name' => $p->paymentConcept->name ?? 'Concepto General',
                    'amount' => (float) $p->amount,
                    'gateway' => $p->gateway ?: 'Efectivo',
                    'status' => $p->status ?: 'Completado',
                    'ncf' => $p->ncf,
                    'date' => $p->created_at ? $p->created_at->format('d/m/Y') : '-',
                    'time' => $p->created_at ? $p->created_at->format('h:i A') : '',
                ];
            });

        // 3. Gráfico de Flujo de Caja (Últimos 6 meses)
        $months = 6;
        $chartLabels = [];
        $chartIncome = [];
        $chartPending = [];

        $driver = DB::connection()->getDriverName();
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

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

        $stats = Payment::selectRaw($selectRaw)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = ucfirst(str_replace('.', '', $date->locale('es')->isoFormat('MMM')));
            $key = $date->format('Y-m');

            $chartLabels[] = $monthName;
            $chartIncome[] = $stats->has($key) ? (float) $stats[$key]->income : 0;
            $chartPending[] = $stats->has($key) ? (float) $stats[$key]->pending : 0;
        }

        // 4. Conceptos de Pago
        $concepts = PaymentConcept::orderBy('name')->take(6)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
                'amount' => (float) $c->amount,
                'is_fixed_amount' => (bool) $c->is_fixed_amount,
            ];
        });

        return Inertia::render('Finance/Dashboard', [
            'kpis' => [
                'totalIncome' => $totalIncome,
                'totalPending' => $totalPending,
                'transactionsCount' => $transactionsCount,
            ],
            'chartData' => [
                'labels' => $chartLabels,
                'income' => $chartIncome,
                'pending' => $chartPending,
            ],
            'payments' => $payments,
            'concepts' => $concepts,
            'filters' => [
                'search' => $search,
                'statusFilter' => $statusFilter,
                'dateFilter' => $dateFilter,
            ],
        ]);
    }

    private function applyDateFilter(Builder $query, string $filter): void
    {
        switch ($filter) {
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
}
