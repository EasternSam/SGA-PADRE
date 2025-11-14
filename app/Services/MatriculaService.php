<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Servicio para manejar la lógica de generación de matrícula
 * y activación de cuentas de estudiante.
 */
class MatriculaService
{
    /**
     * Procesa un pago y, si es el pago de inscripción,
     * genera la matrícula y actualiza la cuenta del usuario.
     *
     * @param Payment $payment El pago que se acaba de completar.
     * @return void
     */
    public function generarMatricula(Payment $payment)
    {
        // Asegurarse de que el pago esté completado y tenga un estudiante
        if ($payment->status !== 'Completado' || !$payment->student) {
            return;
        }

        $student = $payment->student;
        $user = $student->user; // Obtener el usuario vinculado

        // Si el estudiante ya tiene matrícula, solo activamos la inscripción
        if ($student->student_code && $user && !$user->access_expires_at) {
            $this->activarInscripcion($payment);
            return;
        }

        // Si es un estudiante nuevo (temporal), procedemos a matricular
        try {
            DB::transaction(function () use ($payment, $student, $user) {
                
                // 1. Generar la matrícula (student_code) si no la tiene
                if (!$student->student_code) {
                    $student->student_code = $this->generateUniqueStudentCode();
                }
                
                // 2. Actualizar el Usuario (la parte clave)
                if ($user) {
                    $matricula = $student->student_code;
                    $newEmail = $matricula . '@centu.edu.do';
                    $newPassword = Hash::make($matricula);

                    // Validar que el nuevo email no exista (colisión)
                    $emailExists = User::where('email', $newEmail)->where('id', '!=', $user->id)->exists();
                    if ($emailExists) {
                        // Si hay colisión, se re-intenta con un sufijo,
                        // aunque 'generateUniqueStudentCode' debería evitarlo.
                         $matricula = $this.generateUniqueStudentCode(); // Generar uno nuevo
                         $newEmail = $matricula . '@centu.edu.do';
                         $newPassword = Hash::make($matricula);
                         $student->student_code = $matricula; // Guardar el nuevo
                    }

                    // Actualizar el usuario temporal a permanente
                    $user->email = $newEmail;
                    $user->password = $newPassword;
                    $user->access_expires_at = null; // <-- Hacer la cuenta permanente
                    $user->save();
                }

                // 3. Guardar el student_code en el estudiante
                $student->save();

                // 4. Activar la inscripción
                $this->activarInscripcion($payment);

            }); // Fin de la transacción

        } catch (\Exception $e) {
            \Log::error("Error al generar matrícula para estudiante ID {$student->id}: " . $e->getMessage());
            // No revertir el pago, solo loguear el error de matriculación
        }
    }

    /**
     * Cambia el estado de la inscripción asociada al pago.
     */
    private function activarInscripcion(Payment $payment)
    {
        if ($payment->enrollment) {
            $payment->enrollment->status = 'Cursando'; // O 'Activo'
            $payment->enrollment->save();
        }
    }

    /**
     * Genera un código de estudiante único.
     * Ejemplo: 202500001
     */
    private function generateUniqueStudentCode(): string
    {
        $year = Carbon::now()->year;
        
        // Buscar el último estudiante de este año para obtener el consecutivo
        $lastStudent = Student::where('student_code', 'LIKE', $year . '%')
                              ->orderBy('student_code', 'desc')
                              ->first();
        
        $nextNumber = 1;
        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->student_code, 4); // Obtener '00001'
            $nextNumber = $lastNumber + 1;
        }

        $newCode = $year . str_pad($nextNumber, 5, '0', STR_PAD_LEFT); // '2025' + '00001'

        // Asegurarse de que es realmente único (por si acaso)
        while (Student::where('student_code', $newCode)->exists()) {
            $nextNumber++;
            $newCode = $year . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }

        return $newCode;
    }
}