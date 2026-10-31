<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de forma. Las reglas de negocio que dependen del catálogo
 * (precio fijo, descuento ≤ subtotal, efectivo suficiente) viven en PosCheckoutService.
 */
class ProcessPosPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La ruta ya está protegida por role:Admin|Registro|Contabilidad|Caja.
    }

    public function rules(): array
    {
        return [
            'walk_in' => ['sometimes', 'boolean'],
            'student_id' => ['nullable', 'required_unless:walk_in,true,1', 'integer', 'exists:students,id'],
            'payment_concept_id' => ['required', 'integer', 'exists:payment_concepts,id'],
            'concept_label' => ['nullable', 'string', 'max:150'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . config('pos.max_quantity')],
            'unit_amount' => ['nullable', 'numeric', 'min:0.01', 'max:9999999'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'gateway' => ['required', Rule::in(config('pos.gateways'))],
            'cash_received' => ['nullable', 'required_if:gateway,Efectivo', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'ncf_type' => ['required', Rule::in(['B01', 'B02'])],
            'rnc_client' => ['nullable', 'required_if:ncf_type,B01', 'string', 'regex:/^\d{9}$|^\d{11}$/'],
            'company_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required_unless' => 'Seleccione un estudiante o marque “Cliente general”.',
            'cash_received.required_if' => 'Indique el efectivo recibido.',
            'rnc_client.required_if' => 'El crédito fiscal (B01) requiere el RNC del cliente.',
            'rnc_client.regex' => 'El RNC debe tener 9 dígitos o la cédula 11, sin guiones.',
            'gateway.in' => 'Método de pago no válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('rnc_client')) {
            $this->merge(['rnc_client' => preg_replace('/\D/', '', (string) $this->input('rnc_client'))]);
        }
    }
}
