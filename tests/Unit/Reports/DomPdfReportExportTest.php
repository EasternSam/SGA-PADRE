<?php

namespace Tests\Unit\Reports;

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ReportCenterController;
use ReflectionClass;
use Tests\TestCase;

class DomPdfReportExportTest extends TestCase
{
    public function test_report_center_and_statements_use_dompdf_instead_of_window_print(): void
    {
        $basePath = dirname(__DIR__, 3);

        $centerJsx = file_get_contents($basePath . '/resources/js/Pages/Reports/Center.jsx');
        $statementsJsx = file_get_contents($basePath . '/resources/js/Pages/Accounting/Statements.jsx');
        $adminRoutes = file_get_contents($basePath . '/routes/admin.php');

        $this->assertStringNotContainsString('window.print()', $centerJsx);
        $this->assertStringContainsString('/admin/reports/export-pdf', $centerJsx);

        $this->assertStringNotContainsString('window.print()', $statementsJsx);
        $this->assertStringContainsString('/admin/finance/statements/pdf', $statementsJsx);

        $this->assertStringContainsString("'/reports/export-pdf'", $adminRoutes);
        $this->assertStringContainsString("'/finance/statements/pdf'", $adminRoutes);

        $reportCenterReflection = new ReflectionClass(ReportCenterController::class);
        $this->assertTrue($reportCenterReflection->hasMethod('exportPdf'));

        $accountingReflection = new ReflectionClass(AccountingController::class);
        $this->assertTrue($accountingReflection->hasMethod('exportStatementsPdf'));

        $this->assertFileExists($basePath . '/resources/views/reports/center-pdf.blade.php');
        $this->assertFileExists($basePath . '/resources/views/reports/statements-pdf.blade.php');
    }
}
