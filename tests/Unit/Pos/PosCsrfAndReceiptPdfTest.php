<?php

namespace Tests\Unit\Pos;

use App\Http\Controllers\PosController;
use ReflectionClass;
use Tests\TestCase;

class PosCsrfAndReceiptPdfTest extends TestCase
{
    public function test_pos_uses_fresh_xsrf_cookie_and_shared_inertia_csrf_token(): void
    {
        $basePath = dirname(__DIR__, 3);

        $inertiaMiddleware = file_get_contents($basePath . '/app/Http/Middleware/HandleInertiaRequests.php');
        $posUtils = file_get_contents($basePath . '/resources/js/components/pos/utils.js');
        $posIndex = file_get_contents($basePath . '/resources/js/Pages/Pos/Index.jsx');
        $layout = file_get_contents($basePath . '/resources/js/Layouts/AuthenticatedLayout.jsx');

        $this->assertStringContainsString("'csrf_token'", $inertiaMiddleware);
        $this->assertStringContainsString('getXsrfCookieToken', $posUtils);
        $this->assertStringContainsString('getCsrfHeaders', $posUtils);
        $this->assertStringContainsString("credentials: 'same-origin'", $posIndex);
        $this->assertStringContainsString('getCsrfHeaders(', $posIndex);
        $this->assertStringContainsString('props?.csrf_token', $layout);
    }

    public function test_pos_receipt_uses_dompdf_endpoint_instead_of_window_print(): void
    {
        $basePath = dirname(__DIR__, 3);

        $receiptModal = file_get_contents($basePath . '/resources/js/components/pos/ReceiptModal.jsx');
        $adminRoutes = file_get_contents($basePath . '/routes/admin.php');

        $this->assertStringNotContainsString('window.print()', $receiptModal);
        $this->assertStringContainsString('/admin/pos/receipt/', $receiptModal);
        $this->assertStringContainsString("'/pos/receipt/{payment}/pdf'", $adminRoutes);

        $reflection = new ReflectionClass(PosController::class);
        $this->assertTrue($reflection->hasMethod('receiptPdf'));
        $this->assertFileExists($basePath . '/resources/views/reports/pos-receipt-pdf.blade.php');
    }
}
