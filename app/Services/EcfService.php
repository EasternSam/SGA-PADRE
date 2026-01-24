<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\NcfSequence;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EcfService
{
    /**
     * Asigna un NCF y Código de Seguridad a un pago completado.
     */
    public function emitirComprobante(Payment $payment)
    {
        // 1. Determinar el tipo de comprobante
        // CORRECCIÓN: Priorizar la selección del modal (rnc_client en el pago)
        // Si el pago tiene un RNC de cliente específico, es Crédito Fiscal (31)
        if (!empty($payment->rnc_client)) {
            $tipo = '31'; 
        } 
        // Si ya tiene un tipo definido en base de datos (por el modal), lo respetamos
        elseif (!empty($payment->ncf_type)) {
            $tipo = $payment->ncf_type;
        }
        // Si no, usamos la lógica por defecto del estudiante
        else {
            $tipo = $this->determinarTipoComprobante($payment->student);
        }

        // 2. Buscar la secuencia activa para ese tipo
        $secuencia = NcfSequence::where('type_code', $tipo)
            ->where('is_active', true)
            ->whereDate('expiration_date', '>=', now())
            ->first();

        if (!$secuencia) {
            Log::error("ECF: No hay secuencia disponible para el tipo {$tipo}");
            return; 
        }

        // 3. Generar el NCF
        $ncf = $secuencia->getNextNcf();

        if (!$ncf) {
            Log::error("ECF: Secuencia agotada para el tipo {$tipo}");
            return;
        }

        // 4. Generar Código de Seguridad (Simulado 6 chars)
        $securityCode = strtoupper(Str::random(6));

        // 5. Actualizar el pago
        $payment->update([
            'ncf' => $ncf,
            'ncf_type' => $tipo, // Guardamos el tipo correcto (31 o 32)
            'ncf_expiration' => $secuencia->expiration_date,
            'security_code' => $securityCode,
            'dgii_status' => 'generated'
        ]);
        
        Log::info("ECF: Comprobante {$ncf} ({$tipo}) asignado al pago {$payment->id}");
    }

    private function determinarTipoComprobante($student)
    {
        // Lógica fallback: Si el perfil del estudiante tiene RNC
        if ($student && !empty($student->rnc) && (strlen($student->rnc) == 9 || strlen($student->rnc) == 11)) {
            return '31'; // e-CF Crédito Fiscal
        }

        return '32'; // e-CF Consumo
    }
}