<?php

namespace App\Http\Controllers;

use App\Services\DgiiExportService;
use Gridbase\BillsCore\BillsCoreServiceProvider;
use Gridbase\BillsCore\Contracts\IssuerConfigInterface;
use Gridbase\BillsCore\Services\DgiiExcelService;
use Gridbase\BillsCore\Services\DgiiPrevalidatorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DgiiReportController extends Controller
{
    public function index(Request $request, DgiiExportService $exporter, IssuerConfigInterface $issuer): Response
    {
        $yearMonth = $exporter->resolveActiveYearMonth($request->input('yearMonth'));

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

    public function exportExcel(
        Request $request,
        string $type,
        DgiiExportService $exporter,
        DgiiExcelService $excelService,
        IssuerConfigInterface $issuer
    ) {
        $yearMonth = $exporter->resolveActiveYearMonth($request->input('yearMonth'));
        [$year, $month] = array_pad(explode('-', $yearMonth), 2, date('m'));
        $period = "{$year}{$month}";
        $rnc = preg_replace('/[^0-9]/', '', $issuer->getEmitterRnc());
        $normType = strtolower($type);

        $analysis = $exporter->getFullTaxAnalysis($yearMonth);
        $isXlsx = in_array($normType, ['rs1', 'rs2', 'rs3', 'rs4'], true);

        switch ($normType) {
            case '606':
                $spreadsheet = $excelService->generate606Excel($rnc, $period, $analysis['records606']);
                $filename = "DGII_606_{$rnc}_{$period}.xls";
                break;

            case '608':
                $spreadsheet = $excelService->generate608Excel($rnc, $period, $analysis['records608']);
                $filename = "DGII_608_{$rnc}_{$period}.xls";
                break;

            case 'it1':
                $spreadsheet = $excelService->generateIt1Excel($analysis['it1Declaration']);
                $filename = "DGII_IT1_{$rnc}_{$period}.xls";
                break;

            case 'ir2':
                $spreadsheet = $excelService->generateIr2Excel($analysis['ir2Declaration']);
                $filename = "DGII_IR2_{$rnc}_{$year}.xls";
                break;

            case 'dss':
                $spreadsheet = $excelService->generateDssExcel($analysis['otherDeclarations']['dss'] ?? [], $year, $month);
                $filename = "DGII_DSS07_{$rnc}_{$period}.xls";
                break;

            case 'daf':
                $spreadsheet = $excelService->generateDafExcel($analysis['otherDeclarations']['daf'] ?? []);
                $filename = "DGII_DAF_{$rnc}_{$year}.xls";
                break;

            case 'rs1':
            case 'rs2':
            case 'rs3':
            case 'rs4':
                $spreadsheet = $excelService->generateRstExcel($normType, $analysis['otherDeclarations'][$normType] ?? []);
                $filename = 'DGII_' . strtoupper($normType) . "_{$rnc}_{$year}.xlsx";
                break;

            case '607':
            default:
                $spreadsheet = $excelService->generate607Excel($rnc, $period, $analysis['records607']);
                $filename = "DGII_607_{$rnc}_{$period}.xls";
                break;
        }

        $mime = $isXlsx
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'application/vnd.ms-excel';

        $tmpFile = tempnam(sys_get_temp_dir(), 'dgii_xls_');
        $writer = $isXlsx
            ? new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet)
            : new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        @$writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type' => $mime,
            'Cache-Control' => 'max-age=0',
        ])->deleteFileAfterSend(true);
    }

    public function download606(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $exporter->resolveActiveYearMonth($request->input('yearMonth'));
        $result = $exporter->generate606($yearMonth);

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function download607(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $exporter->resolveActiveYearMonth($request->input('yearMonth'));
        $result = $exporter->generate607($yearMonth);

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function download608(Request $request, DgiiExportService $exporter)
    {
        $yearMonth = $exporter->resolveActiveYearMonth($request->input('yearMonth'));
        $result = $exporter->generate608($yearMonth);

        return response()->streamDownload(function () use ($result) {
            echo $result['content'];
        }, $result['filename'], ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
