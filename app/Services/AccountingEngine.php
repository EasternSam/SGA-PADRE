<?php

namespace App\Services;

use App\Models\AccountingEntry;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountingEngine
{
    /**
     * Creates a perfectly balanced accounting entry.
     *
     * @param int $journalId
     * @param string $date
     * @param string $description
     * @param mixed|null $reference Model instance for the polymorphic relation
     * @param array $lines Format: [['account_id' => 1, 'debit' => 100, 'credit' => 0, 'desc' => ''], ...]
     * @param string $status
     * @return AccountingEntry
     * @throws Exception
     */
    public function makeEntry(int $journalId, string $date, string $description, $reference, array $lines, string $status = 'posted')
    {
        // 0. Strict Lock Date Validation
        $lockDate = \App\Models\Setting::where('key', 'accounting_lock_date')->value('value');
        if ($lockDate && $date <= $lockDate) {
            throw new Exception("BLOQUEO DE PERÍODO: El período contable se encuentra cerrado hasta el {$lockDate}. No se pueden registrar ni modificar asientos con fecha {$date} o anterior.");
        }

        // 1. Validate Debits == Credits strictly
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        // Round to 2 decimals to prevent floating point inaccuracies
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new Exception("The accounting entry is not balanced. Debits: {$totalDebit}, Credits: {$totalCredit}");
        }

        // 2. Wrap the creation inside a single SQL Transaction
        return DB::transaction(function () use ($journalId, $date, $description, $reference, $lines, $status) {
            
            // 3. Create Transaction Header
            $entry = new AccountingEntry([
                'accounting_journal_id' => $journalId,
                'date' => $date,
                'description' => $description,
                'status' => $status,
            ]);

            // Link the payment/invoice via polymorphic relation
            if ($reference) {
                $entry->reference()->associate($reference);
            }

            $entry->save();

            // 4. Create Transaction Lines
            foreach ($lines as $line) {
                $entry->lines()->create([
                    'accounting_account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['desc'] ?? null,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Helper to automatically register a completed student payment.
     * Debit: Cash/Bank (1.1.0.0)
     * Credit: Cuentas por Cobrar (1.2.0.0) OR Ingresos (4.1.0.0)
     */
    public function registerStudentPayment(\App\Models\Payment $payment)
    {
        $journal = \App\Models\AccountingJournal::where('name', 'Diario de Ingresos')->first();
        if (!$journal) return null;

        $cajaAccount = \App\Models\AccountingAccount::where('code', \App\Models\Setting::val('account_cash_default', '1.1.0.0'))->first();
        $cxCAccount = \App\Models\AccountingAccount::where('code', \App\Models\Setting::val('account_cxc_default', '1.2.0.0'))->first();
        $ingresoAccount = \App\Models\AccountingAccount::where('code', \App\Models\Setting::val('account_income_default', '4.1.0.0'))->first();

        // If paying a previous debt (enrollment), credit Cuentas por Cobrar. 
        // Else, direct credit to Ingresos.
        $creditAccount = $payment->enrollment_id ? $cxCAccount : $ingresoAccount;
        $studentName = $payment->student ? ($payment->student->getFullNameAttribute() ?? 'ID '.$payment->student_id) : 'Desconocido';

        $lines = [
            [
                'account_id' => $cajaAccount->id,
                'debit' => $payment->amount,
                'credit' => 0,
                'desc' => "Cobro de pago #{$payment->id}"
            ],
            [
                'account_id' => $creditAccount->id,
                'debit' => 0,
                'credit' => $payment->amount,
                'desc' => "Abono por pago #{$payment->id}"
            ]
        ];

        $dateStr = $payment->created_at 
            ? \Carbon\Carbon::parse($payment->created_at)->format('Y-m-d') 
            : now()->format('Y-m-d');

        return $this->makeEntry($journal->id, $dateStr, "Pago de Estudiante {$studentName}", $payment, $lines);
    }

    /**
     * Helper to automatically register a new student debt (e.g. Enrollment).
     */
    public function registerStudentDebt(\App\Models\Enrollment $enrollment, $amount)
    {
        $journal = \App\Models\AccountingJournal::where('name', 'Diario de Ingresos')->first();
        if (!$journal) return null;

        $cxCAccount = \App\Models\AccountingAccount::where('code', \App\Models\Setting::val('account_cxc_default', '1.2.0.0'))->first();
        $ingresoDiferidoAccount = \App\Models\AccountingAccount::where('code', \App\Models\Setting::val('account_deferred_income', '2.1.0.0'))->first();
        $studentName = $enrollment->student ? ($enrollment->student->getFullNameAttribute() ?? 'ID '.$enrollment->student_id) : 'Desconocido';

        $lines = [
            [
                'account_id' => $cxCAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'desc' => "CXC generada por matricula #{$enrollment->id}"
            ],
            [
                'account_id' => $ingresoDiferidoAccount->id,
                'debit' => 0,
                'credit' => $amount,
                'desc' => "Ingreso diferido por matricula #{$enrollment->id}"
            ]
        ];

        return $this->makeEntry($journal->id, $enrollment->created_at->format('Y-m-d'), "Deuda de Estudiante {$studentName}", $enrollment, $lines);
    }

    /**
     * Helper to automatically register a supplier expense with DGII tax retentions.
     */
    public function registerExpense(\App\Models\Expense $expense, int $journalId)
    {
        $lines = [];

        // 1. Débito al Gasto (Por el Subtotal)
        if ($expense->subtotal > 0) {
            $lines[] = [
                'account_id' => $expense->expense_account_id,
                'debit' => $expense->subtotal,
                'credit' => 0,
                'desc' => "Gasto Operativo: {$expense->description}"
            ];
        }

        // 2. Débito a ITBIS Pagado por Adelantado (Activo)
        if ($expense->itbis_amount > 0) {
            $itbisAdelantado = \App\Models\AccountingAccount::where('code', \App\Models\Setting::val('account_itbis_advance', '1.1.4.0'))->first();
            if ($itbisAdelantado) {
                $lines[] = [
                    'account_id' => $itbisAdelantado->id,
                    'debit' => $expense->itbis_amount,
                    'credit' => 0,
                    'desc' => "ITBIS en Compras (NCF: {$expense->ncf})"
                ];
            } else {
                throw new Exception("Configuración faltante: No se encontró la cuenta contable para el ITBIS Pagado por Adelantado (1.1.4.0).");
            }
        }

        // 3. Crédito a ITBIS Retenido por Pagar (Pasivo)
        if ($expense->itbis_retained > 0) {
            $itbisRetenido = \App\Models\AccountingAccount::where('code', \App\Models\Setting::val('account_itbis_retained', '2.1.4.0'))->first();
            if ($itbisRetenido) {
                $lines[] = [
                    'account_id' => $itbisRetenido->id,
                    'debit' => 0,
                    'credit' => $expense->itbis_retained,
                    'desc' => "Retención ITBIS (NCF: {$expense->ncf})"
                ];
            } else {
                throw new Exception("Configuración faltante: No se encontró la cuenta contable de pasivo para ITBIS Retenido (2.1.4.0).");
            }
        }

        // 4. Crédito a ISR Retenido por Pagar (Pasivo)
        if ($expense->isr_retained > 0) {
            $isrRetenido = \App\Models\AccountingAccount::where('code', \App\Models\Setting::val('account_isr_retained', '2.1.5.0'))->first();
            if ($isrRetenido) {
                $lines[] = [
                    'account_id' => $isrRetenido->id,
                    'debit' => 0,
                    'credit' => $expense->isr_retained,
                    'desc' => "Retención ISR (NCF: {$expense->ncf})"
                ];
            } else {
                throw new Exception("Configuración faltante: No se encontró la cuenta contable de pasivo para ISR Retenido (2.1.5.0).");
            }
        }

        // 5. Crédito a Cuenta de Pago o CxP (Monto Total Neto Pagado/Por Pagar)
        if ($expense->total_amount > 0) {
            $lines[] = [
                'account_id' => $expense->payment_account_id,
                'debit' => 0,
                'credit' => $expense->total_amount,
                'desc' => "Pago/Deuda a Suplidor (Ref: {$expense->reference_number})"
            ];
        }

        $supplierName = $expense->supplier ? $expense->supplier->name : 'N/A';
        $entryDescription = "Factura Suplidor: {$supplierName}" . ($expense->reference_number ? " [Ref: {$expense->reference_number}]" : "");

        return $this->makeEntry($journalId, $expense->expense_date->format('Y-m-d'), $entryDescription, $expense, $lines);
    }
}
