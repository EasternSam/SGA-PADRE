<?php

namespace App\Services\Pos;

use App\Models\Payment;
use App\Models\Setting;

/**
 * Representación única de un cobro para el POS (recibo, historial, cierre de caja).
 * Nunca fabrica datos fiscales: si no hay NCF, lo reporta como pendiente.
 */
class PosReceiptPresenter
{
    public function present(Payment $payment): array
    {
        $payment->loadMissing(['student', 'paymentConcept', 'user']);
        $student = $payment->student;
        $isWalkIn = $student?->isWalkIn() ?? false;

        return [
            'id' => $payment->id,
            'date' => $payment->created_at?->format('d/m/Y h:i A'),
            'time' => $payment->created_at?->format('h:i A'),
            'concept' => $payment->concept_label ?: ($payment->paymentConcept?->name ?? 'Cobro'),
            'quantity' => (int) ($payment->quantity ?? 1),
            'subtotal' => (float) ($payment->original_amount ?? $payment->amount),
            'discount_amount' => (float) ($payment->discount_amount ?? 0),
            'total' => (float) $payment->amount,
            'gateway' => $payment->gateway,
            'cash_received' => $payment->cash_received === null ? null : (float) $payment->cash_received,
            'change_amount' => $payment->change_amount === null ? null : (float) $payment->change_amount,
            'status' => $payment->status,
            'notes' => $payment->notes,
            'ncf' => $payment->ncf ?: null,
            'ncf_status' => $payment->ncf ? 'issued' : 'pending',
            'ncf_kind' => $payment->ncf_type_requested === '01' ? 'B01' : 'B02',
            'security_code' => $payment->security_code,
            'rnc_client' => $payment->rnc_client,
            'company_name' => $payment->company_name,
            'cashier' => $payment->user?->name,
            'customer' => [
                'walk_in' => $isWalkIn,
                'name' => $isWalkIn ? 'Cliente general' : ($student?->full_name ?? 'Sin estudiante'),
                'code' => $isWalkIn ? null : $student?->student_code,
                'cedula' => $isWalkIn ? null : $student?->cedula,
            ],
        ];
    }

    /**
     * Datos del emisor para el encabezado del recibo; null si no están configurados.
     */
    public function issuer(): array
    {
        return [
            'name' => Setting::val('institution_name', config('app.name')),
            'rnc' => Setting::val('ecf_rnc_emisor') ?: null,
            'address' => Setting::val('contact_address') ?: null,
        ];
    }
}
