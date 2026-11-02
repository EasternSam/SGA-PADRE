<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Student;
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

        $tz = config('app.timezone', 'America/Santo_Domingo');
        $now = Carbon::now($tz);
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $todayStr = $now->toDateString();

        // 1. Filtrado para KPIs clásicos y Transacciones
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

        $aggregate = (clone $query)->selectRaw("
            COALESCE(SUM(CASE WHEN status IN ('Completado', 'Pagado', 'paid') THEN amount ELSE 0 END), 0) as income,
            COALESCE(SUM(CASE WHEN LOWER(status) IN ('pendiente', 'pending', 'parcial', 'partial') THEN amount ELSE 0 END), 0) as pending,
            COUNT(*) as total_count
        ")->first();

        $totalIncome = (float) ($aggregate->income ?? 0);
        $totalPending = (float) ($aggregate->pending ?? 0);
        $transactionsCount = (int) ($aggregate->total_count ?? 0);

        // 2. Transacciones para explorador / tabla
        $payments = (clone $query)
            ->with(['student.user', 'paymentConcept'])
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function ($p) {
                $studentUser = $p->student->user ?? null;
                $studentName = $studentUser->name ?? ($p->student ? trim($p->student->first_name . ' ' . $p->student->last_name) : ($p->company_name ?: 'Estudiante General'));
                $studentEmail = $studentUser->email ?? ($p->student->email ?? '');

                return [
                    'id' => $p->id,
                    'transaction_id' => $p->transaction_id,
                    'student_id' => $p->student_id,
                    'student_name' => $studentName ?: 'Estudiante General',
                    'student_email' => $studentEmail,
                    'concept_name' => $p->paymentConcept->name ?? ($p->concept_label ?: 'Concepto General'),
                    'amount' => (float) $p->amount,
                    'gateway' => $p->gateway ?: 'Efectivo',
                    'status' => $p->status ?: 'Completado',
                    'ncf' => $p->ncf,
                    'date' => $p->created_at ? $p->created_at->format('d/m/Y') : '-',
                    'time' => $p->created_at ? $p->created_at->format('h:i A') : '',
                ];
            });

        // 3. Estadísticas Core estilo Gridbase Bills DashboardController
        $totalClients = Student::count();

        $revenueAllTime = (float) Payment::whereIn('status', ['Completado', 'Pagado', 'paid'])->sum('amount');
        $pendingAllTime = (float) Payment::whereIn(DB::raw('LOWER(status)'), ['pendiente', 'pending', 'parcial', 'partial'])->sum('amount');

        $overdueScope = function ($q) use ($todayStr) {
            $q->whereIn(DB::raw('LOWER(status)'), ['pendiente', 'pending', 'vencido', 'overdue', 'parcial', 'partial'])
              ->where(function ($sub) use ($todayStr) {
                  $sub->whereNotNull('due_date')->where('due_date', '<', $todayStr)
                      ->orWhere(function ($fallback) {
                          $fallback->whereNull('due_date')->where('created_at', '<=', Carbon::now()->subDays(15));
                      });
              });
        };

        $overdueAmount = (float) Payment::where($overdueScope)->sum('amount');
        $overdueCount = Payment::where($overdueScope)->count();

        // Si no hay vencidas con >15 días pero sí hay pendientes, incluirlas para visibilidad en el widget
        if ($overdueCount === 0 && $pendingAllTime > 0) {
            $overdueScope = function ($q) {
                $q->whereIn(DB::raw('LOWER(status)'), ['pendiente', 'pending', 'parcial', 'partial']);
            };
            $overdueAmount = $pendingAllTime;
            $overdueCount = Payment::where($overdueScope)->count();
        }

        $revenueThisMonth = (float) Payment::whereIn('status', ['Completado', 'Pagado', 'paid'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $revenueLastMonth = (float) Payment::whereIn('status', ['Completado', 'Pagado', 'paid'])
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');

        $validInvoiceStatuses = ['Completado', 'Pagado', 'paid', 'Pendiente', 'pending', 'Parcial', 'partial'];

        $invoicedAmountThisMonth = (float) Payment::whereIn('status', $validInvoiceStatuses)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $invoicedAmountLastMonth = (float) Payment::whereIn('status', $validInvoiceStatuses)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');

        $invoicesThisMonth = Payment::whereIn('status', $validInvoiceStatuses)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $invoicesLastMonth = Payment::whereIn('status', $validInvoiceStatuses)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        // ITBIS / Impuestos (Gastos 606 + retenciones)
        $taxThisMonth = (float) Expense::where('status', '!=', 'cancelled')
            ->whereBetween('expense_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('itbis_amount');

        $taxLastMonth = (float) Expense::where('status', '!=', 'cancelled')
            ->whereBetween('expense_date', [$startOfLastMonth->toDateString(), $endOfLastMonth->toDateString()])
            ->sum('itbis_amount');

        $taxTotal = (float) Expense::where('status', '!=', 'cancelled')
            ->sum('itbis_amount');

        $taxPending = (float) Expense::whereIn(DB::raw('LOWER(status)'), ['pending', 'pendiente'])
            ->sum('itbis_amount');

        // 4. Datos mensuales para UIBarChart (Últimos 12 meses — Ingresos, Gastos y Flujo Neto)
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $mStart = $month->copy()->startOfMonth();
            $mEnd = $month->copy()->endOfMonth();

            $mRev = (float) Payment::whereIn('status', ['Completado', 'Pagado', 'paid'])
                ->whereBetween('created_at', [$mStart, $mEnd])
                ->sum('amount');

            $mExp = (float) Expense::where('status', '!=', 'cancelled')
                ->whereBetween('expense_date', [$mStart->toDateString(), $mEnd->toDateString()])
                ->sum('total_amount');

            $label = ucfirst(rtrim($month->locale('es')->translatedFormat('M'), '.'));
            $fullMonth = ucfirst($month->locale('es')->translatedFormat('F Y'));

            $monthlyData[] = [
                'month' => $fullMonth,
                'label' => $label,
                'revenue' => round($mRev, 2),
                'expense' => round($mExp, 2),
                'net' => round($mRev - $mExp, 2),
            ];
        }

        // Mantener chartData de 6 meses para retrocompatibilidad
        $last6 = array_slice($monthlyData, -6);
        $chartLabels = array_column($last6, 'label');
        $chartIncome = array_column($last6, 'revenue');
        $chartPending = array_column($last6, 'expense');

        // 5. Actividad Reciente (estilo Bills recent_invoices)
        $recentInvoices = Payment::with(['student.user', 'paymentConcept'])
            ->whereNotIn(DB::raw('LOWER(status)'), ['cancelled', 'cancelado', 'anulado'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(6)
            ->get()
            ->map(function ($p) {
                $studentUser = $p->student->user ?? null;
                $studentName = $studentUser->name ?? ($p->student ? trim($p->student->first_name . ' ' . $p->student->last_name) : ($p->company_name ?: 'Consumidor Final'));
                $conceptName = $p->paymentConcept->name ?? ($p->concept_label ?: 'Servicio Educativo');
                $invNumber = $p->ncf ?: ($p->bills_invoice_number ?: ($p->transaction_id ?: ('REC-' . str_pad((string) $p->id, 5, '0', STR_PAD_LEFT))));
                $rawStatus = strtolower((string) $p->status);
                $mappedStatus = in_array($rawStatus, ['completado', 'pagado', 'paid'], true)
                    ? 'paid'
                    : (in_array($rawStatus, ['pendiente', 'pending'], true) ? 'sent' : ($rawStatus === 'parcial' ? 'partial' : 'draft'));

                $isCreditNote = str_starts_with((string) $p->ncf, 'B04') || str_starts_with((string) $p->ncf, 'E34');

                return [
                    'id' => $p->id,
                    'invoice_number' => $invNumber,
                    'encf' => $p->ncf,
                    'company_name' => $studentName ?: 'Consumidor Final',
                    'contact_name' => $conceptName,
                    'total' => (float) $p->amount,
                    'currency' => $p->currency ?: 'DOP',
                    'status' => $mappedStatus,
                    'is_credit_note' => $isCreditNote,
                    'issue_date' => $p->created_at ? $p->created_at->format('Y-m-d') : null,
                    'issue_date_formatted' => $p->created_at ? $p->created_at->format('d/m/Y') : '-',
                ];
            });

        // 6. Cuentas por Cobrar / Vencidas (estilo Bills overdue_invoices)
        $overdueInvoices = Payment::with(['student.user', 'paymentConcept'])
            ->where($overdueScope)
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($p) {
                $studentUser = $p->student->user ?? null;
                $studentName = $studentUser->name ?? ($p->student ? trim($p->student->first_name . ' ' . $p->student->last_name) : ($p->company_name ?: 'Estudiante'));
                $conceptName = $p->paymentConcept->name ?? ($p->concept_label ?: 'Arancel Pendiente');
                $invNumber = $p->ncf ?: ($p->transaction_id ?: ('COB-' . str_pad((string) $p->id, 5, '0', STR_PAD_LEFT)));

                return [
                    'id' => $p->id,
                    'invoice_number' => $invNumber,
                    'company_name' => $studentName ?: 'Estudiante',
                    'contact_name' => $conceptName,
                    'total' => (float) $p->amount,
                    'currency' => $p->currency ?: 'DOP',
                    'status' => 'overdue',
                    'due_date' => $p->due_date ? $p->due_date->format('d/m/Y') : ($p->created_at ? $p->created_at->format('d/m/Y') : '-'),
                    'balance' => (float) $p->amount,
                ];
            });

        // 7. Conceptos de Pago
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
            'stats' => [
                'total_clients' => $totalClients,
                'total_revenue' => round($revenueAllTime, 2),
                'pending_amount' => round($pendingAllTime, 2),
                'overdue_amount' => round($overdueAmount, 2),
                'overdue_count' => $overdueCount,
                'revenue_this_month' => round($revenueThisMonth, 2),
                'revenue_last_month' => round($revenueLastMonth, 2),
                'invoiced_amount_this_month' => round($invoicedAmountThisMonth, 2),
                'invoiced_amount_last_month' => round($invoicedAmountLastMonth, 2),
                'invoices_this_month' => $invoicesThisMonth,
                'invoices_last_month' => $invoicesLastMonth,
                'tax_collected_this_month' => round($taxThisMonth, 2),
                'tax_collected_last_month' => round($taxLastMonth, 2),
                'tax_collected_total' => round($taxTotal, 2),
                'tax_pending' => round($taxPending, 2),
            ],
            'monthly_data' => $monthlyData,
            'recent_invoices' => $recentInvoices,
            'overdue_invoices' => $overdueInvoices,
            'exchange_rates' => [
                'USD' => 60.35,
                'EUR' => 65.90,
                'updated_at' => $now->format('d/m/Y h:i A'),
            ],
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
