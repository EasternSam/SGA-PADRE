<?php

namespace App\Http\Controllers;

use App\Services\DgiiExportService;
use Gridbase\BillsCore\BillsCoreServiceProvider;
use Gridbase\BillsCore\Contracts\IssuerConfigInterface;
use Gridbase\BillsCore\Services\DgiiPrevalidatorService;
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
            'records606' => $records606->values()->all(),
            'records607' => $records607->values()->all(),
            'records608' => $records608->values()->all(),
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
            'it1Declaration' => $analysis['it1Declaration'],
            'ir2Declaration' => $analysis['ir2Declaration'],
            'otherDeclarations' => $analysis['otherDeclarations'],
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

    public function prevalidate(Request $request, string $type, DgiiPrevalidatorService $validator, IssuerConfigInterface $issuer)
    {
        $period = (string) $request->input('period', date('Ym'));
        $records = (array) $request->input('records', []);
        $rnc = preg_replace('/[^0-9]/', '', (string) ($request->input('rnc') ?: $issuer->getEmitterRnc()));

        $result = $validator->validateData($type, $rnc, $period, $records);

        return response()->json($result);
    }

    public function exportExcel(Request $request, string $type, IssuerConfigInterface $issuer)
    {
        $yearMonth = (string) $request->input('yearMonth', date('Y-m'));
        $period = str_replace('-', '', $yearMonth);
        $year = substr($period, 0, 4);
        $rnc = preg_replace('/[^0-9]/', '', $issuer->getEmitterRnc());

        $templateMap = [
            '606' => ['file' => 'Formato_DGII_606.xls', 'download' => "DGII_606_{$rnc}_{$period}.xls", 'mime' => 'application/vnd.ms-excel'],
            '607' => ['file' => 'Formato_DGII_607.xls', 'download' => "DGII_607_{$rnc}_{$period}.xls", 'mime' => 'application/vnd.ms-excel'],
            '608' => ['file' => 'Formato_DGII_608.xls', 'download' => "DGII_608_{$rnc}_{$period}.xls", 'mime' => 'application/vnd.ms-excel'],
            'it1' => ['file' => 'IT-1-2020.xls', 'download' => "DGII_IT1_{$rnc}_{$period}.xls", 'mime' => 'application/vnd.ms-excel'],
            'ir2' => ['file' => 'IR-2-2026.xls', 'download' => "DGII_IR2_{$rnc}_{$year}.xls", 'mime' => 'application/vnd.ms-excel'],
            'dss' => ['file' => 'DSS-07.xls', 'download' => "DGII_DSS07_{$rnc}_{$period}.xls", 'mime' => 'application/vnd.ms-excel'],
            'daf' => ['file' => 'DAF.xls', 'download' => "DGII_DAF_{$rnc}_{$year}.xls", 'mime' => 'application/vnd.ms-excel'],
            'rs1' => ['file' => 'RS1-2021.xlsx', 'download' => "DGII_RS1_{$rnc}_{$year}.xlsx", 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'rs2' => ['file' => 'RS2-2021.xlsx', 'download' => "DGII_RS2_{$rnc}_{$year}.xlsx", 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'rs3' => ['file' => 'RS3-2021.xlsx', 'download' => "DGII_RS3_{$rnc}_{$year}.xlsx", 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'rs4' => ['file' => 'RS4-2021.xlsx', 'download' => "DGII_RS4_{$rnc}_{$year}.xlsx", 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];

        $cfg = $templateMap[strtolower($type)] ?? $templateMap['607'];
        $templatePath = resource_path('templates/dgii/' . $cfg['file']);

        if (file_exists($templatePath)) {
            return response()->download($templatePath, $cfg['download'], [
                'Content-Type' => $cfg['mime'],
                'Cache-Control' => 'max-age=0',
            ]);
        }

        return abort(404, 'Plantilla oficial DGII no encontrada.');
    }

    public function download606(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $request->input('yearMonth', date('Y-m'));
        $result = $exporter->generate606($yearMonth);

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function download607(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $request->input('yearMonth', date('Y-m'));
        $result = $exporter->generate607($yearMonth);

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function download608(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $request->input('yearMonth', date('Y-m'));
        $result = $exporter->generate608($yearMonth);

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
