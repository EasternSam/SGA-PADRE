<?php

namespace App\Services\Pos;

use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registra un cobro de caja o genera una deuda (cargo a cuenta) para un estudiante.
 * El servidor recalcula todo lo monetario: el cliente solo envía intención
 * (concepto, cantidad, descuento, efectivo entregado o fecha de vencimiento).
 *
 * Semántica de montos (igual que el resto del SGA):
 *   original_amount = bruto, discount_amount = descuento, amount = neto.
 */
class PosCheckoutService
{
    /**
     * @throws ValidationException
     */
    public function checkout(array $data, User $cashier): Payment
    {
        $isDebt = ($data['operation_type'] ?? 'charge') === 'debt';

        if ($isDebt && filter_var($data['walk_in'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $this->reject('student_id', 'Para generar una deuda en cuenta debe seleccionar un estudiante registrado.');
        }

        $student = $this->resolveStudent($data);
        $concept = PaymentConcept::findOrFail($data['payment_concept_id']);
        $isOpenConcept = $concept->name === config('pos.open_concept_name');

        // Si se está liquidando una deuda existente del estudiante:
        $existingPending = null;
        if (!$isDebt && !empty($data['pending_payment_id'])) {
            $existingPending = Payment::where('id', $data['pending_payment_id'])
                ->where('student_id', $student->id)
                ->whereIn('status', ['Pendiente', 'pending', 'Vencido', 'Parcial'])
                ->first();

            if (!$existingPending) {
                $this->reject('pending_payment_id', 'El cargo pendiente seleccionado ya fue pagado o no pertenece a este estudiante.');
            }
        }

        $label = trim((string) ($data['concept_label'] ?? ($existingPending?->concept_label ?? '')));
        if ($isOpenConcept && $label === '' && !$existingPending) {
            $this->reject('concept_label', 'Describa el cobro diverso.');
        }

        $quantity = max(1, (int) ($data['quantity'] ?? ($existingPending?->quantity ?: 1)));

        if ($existingPending) {
            $subtotalCents = $this->toCents($existingPending->original_amount ?: $existingPending->amount);
        } else {
            $unitCents = $this->unitPriceCents($concept, $isOpenConcept, $data['unit_amount'] ?? null);
            $subtotalCents = $unitCents * $quantity;
        }

        $discountCents = $this->toCents($data['discount_amount'] ?? ($existingPending?->discount_amount ?? 0));

        if ($discountCents > $subtotalCents) {
            $this->reject('discount_amount', 'El descuento no puede superar el subtotal.');
        }

        $totalCents = $subtotalCents - $discountCents;
        if ($totalCents <= 0) {
            $this->reject('discount_amount', 'El total debe ser mayor que cero.');
        }

        if ($isDebt) {
            $dueDate = !empty($data['due_date'])
                ? $data['due_date']
                : now()->addDays(30)->toDateString();

            $payment = DB::transaction(function () use (
                $student,
                $concept,
                $quantity,
                $label,
                $subtotalCents,
                $discountCents,
                $totalCents,
                $dueDate,
                $data,
                $cashier
            ) {
                $created = Payment::create([
                    'student_id' => $student->id,
                    'payment_concept_id' => $concept->id,
                    'quantity' => $quantity,
                    'concept_label' => $label !== '' ? $label : null,
                    'original_amount' => $this->fromCents($subtotalCents),
                    'discount_amount' => $this->fromCents($discountCents),
                    'amount' => $this->fromCents($totalCents),
                    'cash_received' => null,
                    'change_amount' => null,
                    'currency' => 'DOP',
                    'gateway' => 'Cargo en Caja',
                    'status' => 'Pendiente',
                    'due_date' => $dueDate,
                    'notes' => $data['notes'] ?? null,
                    'user_id' => $cashier->id,
                ]);

                if (!$student->isWalkIn() && \Illuminate\Support\Facades\Schema::hasColumn('students', 'balance')) {
                    $student->increment('balance', (float) $this->fromCents($totalCents));
                }

                return $created;
            });

            return $payment->refresh()->load(['student', 'paymentConcept', 'user']);
        }

        [$cashCents, $changeCents] = $this->cashMovement($data, $totalCents);

        $isCreditFiscal = ($data['ncf_type'] ?? 'B02') === 'B01';
        $rnc = $isCreditFiscal ? preg_replace('/\D/', '', (string) ($data['rnc_client'] ?? '')) : null;
        if ($isCreditFiscal && !in_array(strlen($rnc), [9, 11], true)) {
            $this->reject('rnc_client', 'El crédito fiscal (B01) requiere un RNC (9 dígitos) o cédula (11 dígitos).');
        }

        $payment = DB::transaction(function () use (
            $existingPending,
            $student,
            $concept,
            $quantity,
            $label,
            $subtotalCents,
            $discountCents,
            $totalCents,
            $cashCents,
            $changeCents,
            $data,
            $cashier,
            $isCreditFiscal,
            $rnc
        ) {
            if ($existingPending) {
                $previousDebtAmount = (float) $existingPending->amount;
                $existingPending->update([
                    'quantity' => $quantity,
                    'concept_label' => $label !== '' ? $label : $existingPending->concept_label,
                    'original_amount' => $this->fromCents($subtotalCents),
                    'discount_amount' => $this->fromCents($discountCents),
                    'amount' => $this->fromCents($totalCents),
                    'cash_received' => $cashCents === null ? null : $this->fromCents($cashCents),
                    'change_amount' => $changeCents === null ? null : $this->fromCents($changeCents),
                    'gateway' => $data['gateway'],
                    'status' => 'Completado',
                    'notes' => $data['notes'] ?? $existingPending->notes,
                    'user_id' => $cashier->id,
                    'ncf_type_requested' => $isCreditFiscal ? '01' : '02',
                    'rnc_client' => $rnc ?: null,
                    'company_name' => $isCreditFiscal ? ($data['company_name'] ?? null) : null,
                ]);

                if (!$student->isWalkIn() && \Illuminate\Support\Facades\Schema::hasColumn('students', 'balance')) {
                    $newBalance = max(0, (float) $student->balance - $previousDebtAmount);
                    $student->update(['balance' => $newBalance]);
                }

                return $existingPending;
            }

            return Payment::create([
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
                'ncf_type_requested' => $isCreditFiscal ? '01' : '02',
                'rnc_client' => $rnc ?: null,
                'company_name' => $isCreditFiscal ? ($data['company_name'] ?? null) : null,
            ]);
        });

        // Bills puede haber asignado NCF durante la sincronización en `created`/`updated`.
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
        if (($data['gateway'] ?? '') !== 'Efectivo') {
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
