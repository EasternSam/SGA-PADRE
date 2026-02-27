<?php

use App\Models\Payment;
use App\Models\Expense;
use App\Models\Supplier;
use App\Services\DgiiExportService;
use Carbon\Carbon;

try {
    $out = "--- INSERCION DE DATOS DE PRUEBA ---\n";
    
    // 1. Agregar un Gasto para el 606
    $supplier = Supplier::first();
    if($supplier) {
        $expense = Expense::create([
            'supplier_id' => $supplier->id,
            'ncf' => 'B0100000001',
            'expense_type_606' => '02',
            'expense_date' => now(),
            'expense_account_id' => \App\Models\AccountingAccount::where('type', 'expense')->first()->id ?? 1,
            'payment_account_id' => \App\Models\AccountingAccount::where('type', 'asset')->first()->id ?? 2,
            'subtotal' => 15000,
            'itbis_amount' => 2700,
            'itbis_retained' => 0,
            'isr_retained' => 0,
            'total_amount' => 17700,
            'status' => 'paid',
            'description' => 'Gasto de Prueba DGII'
        ]);
        $out .= "Gasto Generado para 606: " . $expense->ncf . "\n";
    }

    $out .= "\n--- GENERANDO REPORTES DGII ---\n";
    $service = app(DgiiExportService::class);
    $period = Carbon::now()->format('Y-m');

    $res606 = $service->generate606($period);
    $out .= "=== REPORTE 606 (COMPRAS Y GASTOS) ===\n";
    $out .= "Registros encontrados: " . $res606['count'] . "\n";
    $out .= "Nombre del archivo: " . $res606['filename'] . "\n";
    $out .= "Contenido:\n--------------------------------------------------\n";
    $out .= $res606['content'] . "\n--------------------------------------------------\n\n";

    $res607 = $service->generate607($period);
    $out .= "=== REPORTE 607 (VENTAS E INGRESOS) ===\n";
    $out .= "Registros encontrados: " . $res607['count'] . "\n";
    $out .= "Nombre del archivo: " . $res607['filename'] . "\n";
    $out .= "Contenido:\n--------------------------------------------------\n";
    $out .= $res607['content'] . "\n--------------------------------------------------\n\n";

    file_put_contents('dgii_output.log', $out);

} catch (\Exception $e) {
    file_put_contents('dgii_output.log', "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}
