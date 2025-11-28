<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
            Log::warning("MatriculaService: El pago {$payment->id} no está 'Completado' o no tiene estudiante.");
            return;
        }

        $student = $payment->student;
        $user = $student->user; // Obtener el usuario vinculado

        // Si el estudiante ya tiene matrícula, solo activamos la inscripción
        if ($student->student_code && $user && !$user->access_expires_at) {
            Log::info("MatriculaService: Estudiante existente {$student->id}. Activando inscripción.");
            $this->activarInscripcion($payment);
            return;
        }

        // Si es un estudiante nuevo (temporal), procedemos a matricular
        try {
            DB::transaction(function () use ($payment, $student, $user) {
                
                Log::info("MatriculaService: Estudiante nuevo {$student->id}. Iniciando transacción de matrícula.");

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
                        // Si hay colisión, intentamos generar uno nuevo.
                        // Gracias a la mejora en generateUniqueStudentCode, esto es muy improbable,
                        // pero mantenemos la lógica como seguridad.
                         $matricula = $this->generateUniqueStudentCode();
                         $newEmail = $matricula . '@centu.edu.do';
                         $newPassword = Hash::make($matricula);
                         $student->student_code = $matricula; 
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
            Log::error("Error al generar matrícula para estudiante ID {$student->id}: " . $e->getMessage());
            // No revertir el pago, solo loguear el error de matriculación
        }
    }

    /**
     * Cambia el estado de la inscripción asociada al pago.
     */
    private function activarInscripcion(Payment $payment)
    {
        // En lugar de confiar en la relación '$payment->enrollment' (que puede perderse),
        // buscamos la inscripción usando el 'enrollment_id' del pago.
        
        $enrollment = null;

        if ($payment->enrollment_id) {
            $enrollment = Enrollment::find($payment->enrollment_id);
        }

        // Si encontramos la inscripción, la actualizamos.
        if ($enrollment) {
            Log::info("MatriculaService: Encontrada enrollment {$enrollment->id}. Actualizando estado a 'Cursando'.");
            $enrollment->status = 'Cursando'; // O 'Activo'
            $enrollment->save();
        } else {
            Log::warning("MatriculaService: Pago {$payment->id} completado, pero no se encontró 'enrollment' asociado (enrollment_id: {$payment->enrollment_id}). No se activó ninguna inscripción.");
        }
    }

    /**
     * Genera un código de estudiante con el formato Año (2 dígitos) + 7 dígitos incrementales.
     * Ejemplo: 250001035
     */
    private function generateUniqueStudentCode(): string
    {
        // 1. Obtener los 2 últimos dígitos del año actual (ej: "25")
        $yearPrefix = date('y'); 

        // 2. Buscar la última matrícula de este año.
        // Usamos lockForUpdate para bloquear la lectura y evitar duplicados en concurrencia.
        // Importante: Esta consulta debe ocurrir dentro de una transacción (generarMatricula ya crea una).
        $lastStudent = Student::where('student_code', 'like', $yearPrefix . '%')
            ->orderByRaw('LENGTH(student_code) DESC') // Asegurar que ordenamos bien numéricamente
            ->orderBy('student_code', 'desc')
            ->lockForUpdate()
            ->first();

        // 3. Determinar el siguiente número secuencial
        $nextSequence = 1;
        if ($lastStudent) {
            // Extraer la parte numérica (quitando el prefijo del año)
            // Ejemplo: "250001035" -> "0001035"
            $lastSequenceStr = substr($lastStudent->student_code, strlen($yearPrefix));
            $nextSequence = intval($lastSequenceStr) + 1;
        }

        // 4. Bucle de seguridad para evitar colisión con Emails de Usuarios existentes
        // Esto es útil si borraste un Estudiante pero dejaste su Usuario huérfano ocupando el email.
        do {
            // Formatear a 7 dígitos con ceros a la izquierda (ej: 1 -> "0000001")
            $paddedSequence = str_pad($nextSequence, 7, '0', STR_PAD_LEFT);
            $candidateCode = $yearPrefix . $paddedSequence;
            
            // Verificamos si este código ya está tomado por un usuario como email
            $emailTaken = User::where('email', $candidateCode . '@centu.edu.do')->exists();
            // Verificamos si ya está tomado como matrícula (doble check por seguridad)
            $codeTaken = Student::where('student_code', $candidateCode)->exists();

            if ($emailTaken || $codeTaken) {
                // Si está ocupado, probamos con el siguiente número
                $nextSequence++;
            } else {
                // Si está libre, lo retornamos
                return $candidateCode;
            }

        } while (true);
    }
}