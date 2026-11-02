<?php

namespace App\Http\Controllers;

use App\Services\DgiiExportService;
use Gridbase\BillsCore\BillsCoreServiceProvider;
use Gridbase\BillsCore\Contracts\IssuerConfigInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DgiiReportController extends Controller
{
    public function index(Request $request, DgiiExportService $exporter, IssuerConfigInterface $issuer): Response
    {
        $yearMonth = $request->input('yearMonth', date('Y-m'));

        $analysis = $exporter->getFullTaxAnalysis($yearMonth);

        $records606 = collect($analysis['records606']);
        $records607 = collect($analysis['records607']);
        $records608 = collect($analysis['records608']);

        return Inertia::render('Finance/DgiiReports/Index', [
            'yearMonth' => $yearMonth,
            'summary606' => [
                'count' => $records606->count(),
                'totalAmount' => (float) $records606->sum('total_monto_facturado'),
                'totalItbis' => (float) $records606->sum('itbis_facturado'),
                'totalItbisRetained' => (float) $records606->sum('itbis_retenido'),
                'totalIsrRetained' => (float) $records606->sum('monto_retencion_renta'),
            ],
            'summary607' => [
                'count' => $records607->count(),
                'totalAmount' => (float) $records607->sum('monto_facturado'),
                'totalItbis' => (float) $records607->sum('itbis_facturado'),
            ],
            'summary608' => [
                'count' => $records608->count(),
            ],
            'validation606' => $analysis['validation606'],
            'validation607' => $analysis['validation607'],
            'validation608' => $analysis['validation608'],
            'it1Summary' => $analysis['it1Summary'],
            'billsCore' => [
                'version' => BillsCoreServiceProvider::VERSION,
                'engine' => BillsCoreServiceProvider::ENGINE_NAME,
                'emitterRnc' => $analysis['emitterRnc'],
                'emitterName' => $analysis['emitterName'],
                'environment' => $analysis['environment'],
            ],
            'isElectronicBillingEnabled' => $issuer->isElectronicBillingEnabled(),
        ]);
    }

    public function download606(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $request->input('yearMonth', date('Y-m'));
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
        $yearMonth = $request->input('yearMonth', date('Y-m'));
        $result = $exporter->generate607($yearMonth);

        if ($result['count'] === 0) {
            return redirect()->back()->with('error', 'No hay ingresos/pagos registrados con NCF válido para este período.');
        }

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain']);
    }

    public function download608(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $request->input('yearMonth', date('Y-m'));
        $result = $exporter->generate608($yearMonth);

        if ($result['count'] === 0) {
            return redirect()->back()->with('error', 'No hay comprobantes anulados para este período.');
        }

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain']);
    }
}
