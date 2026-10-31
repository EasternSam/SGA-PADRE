<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\DgiiExportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DgiiReportController extends Controller
{
    public function index(Request $request): Response
    {
        $yearMonth = $request->input('yearMonth', date('Y-m', strtotime('-1 month')));

        $startDate = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        // Resumen 606 (Gastos)
        $expenses606Query = Expense::whereNotNull('ncf')
            ->whereNotNull('supplier_id')
            ->where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate]);

        $count606 = (clone $expenses606Query)->count();
        $total606Amount = (float) (clone $expenses606Query)->sum('subtotal');
        $total606Itbis = (float) (clone $expenses606Query)->sum('itbis_amount');

        // Resumen 607 (Ventas / Cobros)
        $payments607Query = Payment::whereNotNull('ncf')
            ->where('status', 'Completado')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $count607 = (clone $payments607Query)->count();
        $total607Amount = (float) (clone $payments607Query)->sum('amount');

        $isElectronicBillingEnabled = (bool) Setting::val('electronic_billing_enabled', true);

        return Inertia::render('Finance/DgiiReports/Index', [
            'yearMonth' => $yearMonth,
            'summary606' => [
                'count' => $count606,
                'totalAmount' => $total606Amount,
                'totalItbis' => $total606Itbis,
            ],
            'summary607' => [
                'count' => $count607,
                'totalAmount' => $total607Amount,
            ],
            'isElectronicBillingEnabled' => $isElectronicBillingEnabled,
        ]);
    }

    public function download606(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $request->input('yearMonth', date('Y-m', strtotime('-1 month')));
        $result = $exporter->generate606($yearMonth);

        if ($result['count'] === 0) {
            return redirect()->back()->with('error', 'No hay gastos registrados con NCF válido para este período.');
        }

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain']);
    }

    public function download607(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $request->input('yearMonth', date('Y-m', strtotime('-1 month')));
        $result = $exporter->generate607($yearMonth);

        if ($result['count'] === 0) {
            return redirect()->back()->with('error', 'No hay ingresos/pagos registrados con NCF válido para este período.');
        }

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain']);
    }
}
