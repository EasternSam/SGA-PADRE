<?php

namespace App\Services\Pos;

use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registra un cobro de caja. El servidor recalcula todo lo monetario: el cliente
 * solo envía intención (concepto, cantidad, descuento, efectivo entregado).
 *
 * Semántica de montos (igual que el resto del SGA):
 *   original_amount = bruto, discount_amount = descuento, amount = neto cobrado.
 */
class PosCheckoutService
{
    /**
     * @throws ValidationException
     */
    public function checkout(array $data, User $cashier): Payment
    {
        $student = $this->resolveStudent($data);
        $concept = PaymentConcept::findOrFail($data['payment_concept_id']);
        $isOpenConcept = $concept->name === config('pos.open_concept_name');

        $label = trim((string) ($data['concept_label'] ?? ''));
        if ($isOpenConcept && $label === '') {
            $this->reject('concept_label', 'Describa el cobro diverso.');
        }

        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $unitCents = $this->unitPriceCents($concept, $isOpenConcept, $data['unit_amount'] ?? null);
        $subtotalCents = $unitCents * $quantity;
        $discountCents = $this->toCents($data['discount_amount'] ?? 0);

        if ($discountCents > $subtotalCents) {
            $this->reject('discount_amount', 'El descuento no puede superar el subtotal.');
        }

        $totalCents = $subtotalCents - $discountCents;
        if ($totalCents <= 0) {
            $this->reject('discount_amount', 'El total a cobrar debe ser mayor que cero.');
        }

        [$cashCents, $changeCents] = $this->cashMovement($data, $totalCents);

        $isCreditFiscal = ($data['ncf_type'] ?? 'B02') === 'B01';
        $rnc = $isCreditFiscal ? preg_replace('/\D/', '', (string) ($data['rnc_client'] ?? '')) : null;
        if ($isCreditFiscal && !in_array(strlen($rnc), [9, 11], true)) {
            $this->reject('rnc_client', 'El crédito fiscal (B01) requiere un RNC (9 dígitos) o cédula (11 dígitos).');
        }

        $payment = DB::transaction(fn () => Payment::create([
            'student_id' => $student->id,
            'payment_concept_id' => $concept->id,
            'quantity' => $quantity,
            'concept_label' => $label !== '' ? $label : null,
            'original_amount' => $this->fromCents($subtotalCents),
            'discount_amount' => $this->fromCents($discountCents),
            'amount' => $this->fromCents($totalCents),
            'cash_received' => $cashCents === null ? null : $this->fromCents($cashCents),
            'change_amount' => $changeCents === null ? null : $this->fromCents($changeCents),
            'currency' => 'DOP',
            'gateway' => $data['gateway'],
            'status' => 'Completado',
            'notes' => $data['notes'] ?? null,
            'user_id' => $cashier->id,
            // Código DGII solicitado; el NCF lo asigna el modelo (secuencia local) o Bills.
            'ncf_type_requested' => $isCreditFiscal ? '01' : '02',
            'rnc_client' => $rnc ?: null,
            'company_name' => $isCreditFiscal ? ($data['company_name'] ?? null) : null,
        ]));

        // Bills puede haber asignado NCF durante la sincronización en `created`.
        return $payment->refresh()->load(['student', 'paymentConcept', 'user']);
    }

    private function resolveStudent(array $data): Student
    {
        if (filter_var($data['walk_in'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $walkIn = Student::where('student_code', config('pos.walk_in_student_code'))->first();
            if (!$walkIn) {
                $this->reject('walk_in', 'La ficha de cliente general no está configurada (php artisan db:seed --class=PosSeeder).');
            }
            return $walkIn;
        }

        if (empty($data['student_id'])) {
            $this->reject('student_id', 'Seleccione un estudiante o marque “Cliente general”.');
        }

        return Student::findOrFail($data['student_id']);
    }

    private function unitPriceCents(PaymentConcept $concept, bool $isOpenConcept, mixed $requested): int
    {
        $catalogCents = $this->toCents($concept->default_amount ?: $concept->amount);

        if ($concept->is_fixed_amount) {
            return $catalogCents;
        }

        $requestedCents = $requested === null || $requested === '' ? null : $this->toCents($requested);

        if ($isOpenConcept && !$requestedCents) {
            $this->reject('unit_amount', 'Indique el precio del cobro diverso.');
        }

        return $requestedCents ?: $catalogCents;
    }

    /** @return array{0: ?int, 1: ?int} [efectivo recibido, cambio] en centavos */
    private function cashMovement(array $data, int $totalCents): array
    {
        if ($data['gateway'] !== 'Efectivo') {
            return [null, null];
        }

        $cashCents = $this->toCents($data['cash_received'] ?? 0);
        if ($cashCents < $totalCents) {
            $this->reject('cash_received', 'El efectivo recibido es menor que el total.');
        }

        return [$cashCents, $cashCents - $totalCents];
    }

    private function toCents(mixed $value): int
    {
        return (int) round(((float) $value) * 100);
    }

    private function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
