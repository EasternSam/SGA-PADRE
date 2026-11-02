<?php

namespace Tests\Unit\BillsCore;

use PHPUnit\Framework\TestCase;

class BillsCoreEmbeddedTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->basePath = dirname(__DIR__, 3);
    }

    public function test_bills_core_package_is_registered_in_composer_and_exists(): void
    {
        $composerJson = json_decode(file_get_contents($this->basePath . '/composer.json'), true);

        $this->assertArrayHasKey(
            'Gridbase\\BillsCore\\',
            $composerJson['autoload']['psr-4'] ?? [],
            'composer.json must autoload Gridbase\\BillsCore\\ from packages/gridbase/bills-core/src/'
        );

        $packageDir = $this->basePath . '/packages/gridbase/bills-core/src';
        $this->assertDirectoryExists($packageDir);
        $this->assertFileExists($packageDir . '/BillsCoreServiceProvider.php');
        $this->assertFileExists($packageDir . '/Services/DocumentTotalsCalculator.php');
        $this->assertFileExists($packageDir . '/Services/Dgii/XmlBuilderService.php');
        $this->assertFileExists($packageDir . '/Services/Dgii/XmlSignatureService.php');
        $this->assertFileExists($packageDir . '/Services/Dgii/DgiiAuthService.php');
        $this->assertFileExists($packageDir . '/Services/Dgii/DgiiApiService.php');
        $this->assertFileExists($packageDir . '/Services/Dgii/EcfManagerService.php');
        $this->assertFileExists($packageDir . '/Services/DgiiPrevalidatorService.php');
        $this->assertFileExists($packageDir . '/Services/DgiiReportBuilderService.php');
    }

    public function test_document_totals_calculator_computes_itbis_and_exempt_lines(): void
    {
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/DocumentTotalsCalculator.php';

        $result = \Gridbase\BillsCore\Services\DocumentTotalsCalculator::calculate([
            ['description' => 'Mensualidad Académica', 'quantity' => 1, 'unit_price' => 5000, 'is_exempt' => true],
            ['description' => 'Certificación Especial Gravada', 'quantity' => 2, 'unit_price' => 1000, 'tax_rate' => 18, 'is_exempt' => false],
        ], 'amount', 0, 18.0, false);

        $this->assertSame(7000.0, $result['subtotal']);
        $this->assertSame(5000.0, $result['exempt_amount']);
        $this->assertSame(2000.0, $result['taxable_amount']);
        $this->assertSame(360.0, $result['tax_amount']);
        $this->assertSame(7360.0, $result['total']);
    }

    public function test_dgii_report_builder_generates_23_column_607_and_validates_with_prevalidator(): void
    {
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/DgiiPrevalidatorService.php';
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/DgiiReportBuilderService.php';

        $validator = new \Gridbase\BillsCore\Services\DgiiPrevalidatorService();
        $builder = new \Gridbase\BillsCore\Services\DgiiReportBuilderService($validator);

        // Valid RNC: 101142456 (CENTU RNC) or 131793916
        $records607 = [
            [
                'rnc_cliente' => '40225369871',
                'tipo_identificacion' => '2',
                'ncf' => 'E320000000001',
                'ncf_modificado' => '',
                'tipo_ingreso' => '01',
                'fecha_comprobante' => '20261015',
                'fecha_pago' => '20261015',
                'monto_facturado' => 4500.00,
                'itbis_facturado' => 0.00,
                'itbis_retenido' => 0.00,
                'itbis_percibido' => '',
                'retencion_isr' => 0.00,
                'isr_percibido' => '',
                'isc' => 0.00,
                'otros_impuestos' => 0.00,
                'propina_legal' => 0.00,
                'efectivo' => 4500.00,
                'bancos' => 0.00,
                'tarjeta' => 0.00,
                'credito' => 0.00,
                'bonos' => 0.00,
                'permuta' => 0.00,
                'otras_formas' => 0.00,
            ],
        ];

        $txt = $builder->build607Txt('101142456', '202610', $records607);
        $lines = explode("\r\n", $txt);

        $this->assertCount(2, $lines);
        $this->assertSame('607|101142456|202610|1', $lines[0]);

        $columns = explode('|', $lines[1]);
        $this->assertCount(23, $columns, 'Official DGII 607 detail line must have exactly 23 pipe-delimited columns');
        $this->assertSame('4500.00', $columns[7], 'Column 8 (index 7) must be Monto Facturado');
        $this->assertSame('0.00', $columns[8], 'Column 9 (index 8) must be ITBIS Facturado');
    }

    public function test_xml_builder_generates_valid_dgii_ecf_xml(): void
    {
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/Dgii/XmlBuilderService.php';

        $builder = new \Gridbase\BillsCore\Services\Dgii\XmlBuilderService();
        $xml = $builder->buildFromArray([
            'ecf_type' => 32,
            'encf' => 'E320000000001',
            'issue_date' => '2026-10-15',
            'subtotal' => 3500.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'total' => 3500.00,
            'buyer_rnc' => '40225369871',
            'buyer_name' => 'Juan Perez',
            'items' => [
                [
                    'description' => 'Inscripción Cuatrimestral',
                    'quantity' => 1,
                    'unit_price' => 3500.00,
                    'amount' => 3500.00,
                    'is_exempt' => true,
                    'tax_rate' => 0.0,
                ],
            ],
        ], [
            'company_tax_id' => '101142456',
            'dgii_razon_social' => 'UNIVERSIDAD NACIONAL TECNOLOGICA',
            'company_address' => 'Santo Domingo, D.N.',
        ]);

        $this->assertStringContainsString('<ECF>', $xml);
        $this->assertStringContainsString('<TipoeCF>32</TipoeCF>', $xml);
        $this->assertStringContainsString('<eNCF>E320000000001</eNCF>', $xml);
        $this->assertStringContainsString('<RNCEmisor>101142456</RNCEmisor>', $xml);
        $this->assertStringContainsString('<MontoExento>3500.00</MontoExento>', $xml);
        $this->assertStringContainsString('<MontoTotal>3500.00</MontoTotal>', $xml);
    }

    public function test_powered_by_gridbase_bills_branding_is_embedded_in_ui_and_pdfs(): void
    {
        $badgeComponent = $this->basePath . '/resources/js/components/bills/PoweredByBills.jsx';
        $this->assertFileExists($badgeComponent);
        $badgeContent = file_get_contents($badgeComponent);
        $this->assertStringContainsString('Powered by Gridbase Bills', $badgeContent);

        $uiFiles = [
            '/resources/js/Layouts/AuthenticatedLayout.jsx',
            '/resources/js/Pages/Pos/Index.jsx',
            '/resources/js/Pages/Finance/Dashboard.jsx',
            '/resources/js/Pages/Finance/DgiiReports/Index.jsx',
            '/resources/js/components/pos/ReceiptModal.jsx',
            '/resources/js/components/pos/TicketPanel.jsx',
        ];

        foreach ($uiFiles as $relPath) {
            $content = file_get_contents($this->basePath . $relPath);
            $this->assertStringContainsString(
                'PoweredByBills',
                $content,
                "Expected {$relPath} to embed the PoweredByBills component"
            );
        }

        $pdfFiles = [
            '/resources/views/reports/pos-receipt-pdf.blade.php',
            '/resources/views/reports/thermal-invoice.blade.php',
            '/resources/views/reports/layouts/pdf.blade.php',
        ];

        foreach ($pdfFiles as $relPath) {
            $content = file_get_contents($this->basePath . $relPath);
            $this->assertStringContainsString(
                'Powered by Gridbase Bills',
                $content,
                "Expected {$relPath} to display 'Powered by Gridbase Bills'"
            );
        }
    }

    public function test_prevalidator_modulo11_and_it1_summary_and_ecf_security_code(): void
    {
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Contracts/IssuerConfigInterface.php';
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/DgiiPrevalidatorService.php';
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/DgiiReportBuilderService.php';
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/Dgii/XmlSignatureService.php';
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/Dgii/XmlBuilderService.php';
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/Dgii/DgiiAuthService.php';
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/Dgii/DgiiApiService.php';
        require_once $this->basePath . '/packages/gridbase/bills-core/src/Services/Dgii/EcfManagerService.php';

        $validator = new \Gridbase\BillsCore\Services\DgiiPrevalidatorService();
        $this->assertTrue($validator->isValidRnc('101142456'));
        $this->assertFalse($validator->isValidRnc('101142450'));
        $this->assertTrue($validator->isValidNcfFormat('E320000000001', '607'));
        $this->assertTrue($validator->isValidNcfFormat('B0100000001', '606'));

        $builder = new \Gridbase\BillsCore\Services\DgiiReportBuilderService($validator);
        $txt606 = $builder->build606Txt('101142456', '202610', [
            [
                'rnc_proveedor' => '101142456',
                'tipo_id' => '1',
                'tipo_bienes' => '02',
                'ncf' => 'B0100000001',
                'fecha_comprobante' => '20261010',
                'fecha_pago' => '20261010',
                'monto_servicios' => 10000.00,
                'monto_bienes' => 0.00,
                'total_monto_facturado' => 10000.00,
                'itbis_facturado' => 1800.00,
                'itbis_retenido' => 0.00,
                'itbis_adelantar' => 1800.00,
                'forma_pago' => '02',
            ],
        ]);
        $lines606 = explode("\r\n", $txt606);
        $this->assertCount(23, explode('|', $lines606[1]));

        $it1 = $builder->calculateIt1Summary(
            [['total_monto_facturado' => 10000, 'itbis_facturado' => 1800, 'itbis_adelantar' => 1800]],
            [['monto_facturado' => 50000, 'itbis_facturado' => 0]]
        );
        $this->assertSame(50000.0, $it1['operaciones_exentas']);
        $this->assertSame(1800.0, $it1['saldo_a_favor']);

        $sig = new \Gridbase\BillsCore\Services\Dgii\XmlSignatureService();
        $code1 = $sig->computeDeterministicSecurityCode('<ECF>test</ECF>', '101142456', 'E320000000001');
        $code2 = $sig->computeDeterministicSecurityCode('<ECF>test</ECF>', '101142456', 'E320000000001');
        $this->assertSame(6, strlen($code1));
        $this->assertSame($code1, $code2);
    }
}
