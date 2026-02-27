<?php

use App\Models\Payment;
use App\Models\AccountingEntry;
use App\Models\Setting;

try {
    // 1. Desbloquear el periodo para hacer la prueba
    Setting::updateOrCreate(['key' => 'accounting_lock_date'], ['value' => '2020-01-01']);
    
    // 2. Crear Pago
    $p = Payment::create([
        'student_id' => 1,
        'amount' => 9000,
        'currency' => 'DOP',
        'status' => 'Completado',
        'gateway' => 'Cash',
        'transaction_id' => 'TEST-CASH-9000-'.time(),
        'ncf_type_requested' => '02'
    ]);

    echo "NCF Generado: " . $p->fresh()->ncf . "\n";
    
    // 3. Buscar la entrada contable específica
    $entry = AccountingEntry::with('lines.account')
        ->where('reference_type', Payment::class)
        ->where('reference_id', $p->id)
        ->first();
        
    if ($entry) {
        echo "Asiento: " . $entry->description . " | Ref: " . $entry->reference_type . ":" . $entry->reference_id . "\n";
        foreach($entry->lines as $line) {
            echo "  - " . $line->account->name . " | DB: " . $line->debit . " | CR: " . $line->credit . "\n";
        }
    } else {
        echo "CRITICAL: NO ASIENTO ENCONTRADO PARA EL PAGO #" . $p->id . "\n";
    }

} catch (\Exception $e) {
    echo "ERROR EXPLICITO: " . $e->getMessage() . "\n";
}
