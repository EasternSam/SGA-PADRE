<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Services\MoodleApiService;
use App\Mail\MoodleCredentialsMail;

/**
 * Servicio para manejar la lógica de generación de matrícula
 * y activación de cuentas de estudiante.
 */
class MatriculaService
{
    /**
     * Procesa un pago y, si es el pago de inscripción,
     * genera la matrícula y actualiza la cuenta del usuario.
     */
    public function generarMatricula(Payment $payment)
    {
        if ($payment->status !== 'Completado' || !$payment->student) {
            Log::warning("MatriculaService: El pago {$payment->id} no está 'Completado' o no tiene estudiante.");
            return;
        }

        $student = $payment->student;
        $user = $student->user;

        // Si el estudiante ya tiene matrícula, solo activamos la inscripción
        if ($student->student_code) {
            Log::info("MatriculaService: Estudiante existente {$student->id}. Activando inscripción.");
            $this->activarInscripcion($payment);
            return;
        }

        // Si es un estudiante nuevo (temporal), procedemos a matricular
        try {
            DB::transaction(function () use ($payment, $student, $user) {
                
                Log::info("MatriculaService: Estudiante nuevo {$student->id}. Iniciando transacción.");

                if (!$student->student_code) {
                    $student->student_code = $this->generateUniqueStudentCode();
                }
                
                if ($user) {
                    $matricula = $student->student_code;
                    $newEmail = $matricula . '@centu.edu.do';
                    $newPassword = Hash::make($matricula);

                    // Verificar colisión de email (raro pero posible)
                    $emailExists = User::where('email', $newEmail)->where('id', '!=', $user->id)->exists();
                    
                    if ($emailExists) {
                         $matricula = $this->generateUniqueStudentCode(); 
                         $newEmail = $matricula . '@centu.edu.do';
                         $newPassword = Hash::make($matricula);
                         $student->student_code = $matricula; 
                    }

                    $user->email = $newEmail;
                    $user->password = $newPassword;
                    $user->access_expires_at = null; // Quitar expiración temporal
                    
                    // Asegurar que no intentamos guardar campos eliminados
                    // Si el modelo User en memoria aún tiene moodle_password sucio, lo limpiamos
                    if(isset($user->moodle_password)) {
                        unset($user->moodle_password);
                    }
                    
                    $user->save();
                }

                $student->save();
                
                // Llamamos a la activación explícitamente dentro de la transacción
                // PERO protegemos que un fallo ahí no revierta la matrícula
                try {
                    $this->activarInscripcion($payment);
                } catch (\Throwable $e) {
                    Log::error("MatriculaService: Error en activación secundaria (Moodle/Pagos), pero la matrícula se generó. Error: " . $e->getMessage());
                }

            }); 

            Log::info("MatriculaService: Transacción completada para {$student->id}");

        } catch (\Exception $e) {
            Log::error("Error CRÍTICO al generar matrícula para estudiante ID {$student->id}: " . $e->getMessage());
        }
    }

    /**
     * Cambia el estado de la inscripción asociada al pago.
     * Pasa de 'Pendiente' a 'Cursando'.
     */
    public function activarInscripcion(Payment $payment)
    {
        $activated = 0;

        // ESTRATEGIA 1: Pagos Agrupados (Carreras/Selección de Materias)
        $groupedEnrollments = Enrollment::where('payment_id', $payment->id)->get();

        foreach ($groupedEnrollments as $enrollment) {
            if ($enrollment->status === 'Pendiente' || $enrollment->status === 'Reservado') {
                $enrollment->status = 'Cursando';
                $enrollment->save();
                $activated++;
                
                // Intento de matriculación en Moodle (NO BLOQUEANTE)
                $this->syncWithMoodle($enrollment);
            }
        }

        // ESTRATEGIA 2: Pagos Individuales (Cursos Libres/Diplomados)
        if ($payment->enrollment_id) {
            $enrollment = Enrollment::find($payment->enrollment_id);
            if ($enrollment && $enrollment->payment_id !== $payment->id) {
                // Verificar estado actual para no re-procesar
                if ($enrollment->status === 'Pendiente' || $enrollment->status === 'Reservado') {
                    $enrollment->status = 'Cursando';
                    $enrollment->save();
                    $activated++;

                    // Intento de matriculación en Moodle (NO BLOQUEANTE)
                    $this->syncWithMoodle($enrollment);
                }
            }
        }

        if ($activated > 0) {
            Log::info("MatriculaService: Pago {$payment->id} procesado. Se activaron {$activated} inscripciones.");
        }
    }

    /**
     * Sincroniza la inscripción con Moodle con lógica de CASCADA.
     * PROTEGIDO: Usa Throwable para atrapar errores fatales y no romper el flujo principal.
     */
    private function syncWithMoodle(Enrollment $enrollment)
    {
        try {
            $schedule = $enrollment->courseSchedule;
            if (!$schedule) return;

            // --- LÓGICA DE CASCADA ---
            $moodleCourseId = $schedule->moodle_course_id;
            if (empty($moodleCourseId)) $moodleCourseId = $schedule->module->moodle_course_id ?? null;
            if (empty($moodleCourseId)) $moodleCourseId = $schedule->module->course->moodle_course_id ?? null;
            
            if (empty($moodleCourseId)) return;

            $student = $enrollment->student;
            $user = $student->user;

            if (!$user) return;

            // Instanciar servicio de forma segura
            if (!class_exists(MoodleApiService::class)) {
                Log::error("Moodle Sync: Clase MoodleApiService no encontrada.");
                return;
            }
            $moodleService = app(MoodleApiService::class);

            $code = $student->student_code ?? 'Student';
            $moodleUsername = strtolower($code);
            $moodlePassword = 'Sga*' . $code . '#' . rand(10,99);

            // Sincronizar
            $syncResult = $moodleService->syncUser($user, $moodlePassword, $moodleUsername);
            
            // Manejo robusto del resultado (sea array o ID simple)
            $moodleUserId = is_array($syncResult) ? ($syncResult['id'] ?? null) : $syncResult;
            $wasCreated = is_array($syncResult) ? ($syncResult['was_created'] ?? false) : false;
            
            // Si devuelve solo ID (versión vieja del servicio), inferimos creación
            if (!is_array($syncResult) && $moodleUserId) {
                $wasCreated = ($user->moodle_user_id !== (string)$moodleUserId);
            }

            if ($moodleUserId) {
                if ($user->moodle_user_id !== (string)$moodleUserId) {
                    $user->moodle_user_id = $moodleUserId;
                    $user->saveQuietly();

                    // Enviar correo solo si es nuevo y la clase existe
                    if ($wasCreated && class_exists(MoodleCredentialsMail::class)) {
                        $emailDestino = $student->email ?? $user->email;
                        try {
                            Mail::to($emailDestino)->send(new MoodleCredentialsMail($user, $moodlePassword, $moodleUsername));
                            Log::info("Correo Moodle enviado a: {$emailDestino}");
                        } catch (\Throwable $e) { // Catch Throwable para errores de Mail
                            Log::error("Error enviando correo Moodle (No crítico): " . $e->getMessage());
                        }
                    }
                }

                $moodleService->enrollUser($moodleUserId, $moodleCourseId);
                Log::info("Moodle Sync: Usuario {$moodleUsername} enrolado en {$moodleCourseId}");
            }

        } catch (\Throwable $e) { // ¡IMPORTANTE! Atrapa Fatal Errors para no revertir la matrícula
            Log::error("Moodle Sync Falló (Ignorado para no afectar matrícula): " . $e->getMessage());
        }
    }

    private function generateUniqueStudentCode(): string
    {
        $yearPrefix = date('y'); 
        $lastStudent = Student::where('student_code', 'like', $yearPrefix . '%')
            ->orderByRaw('LENGTH(student_code) DESC')
            ->orderBy('student_code', 'desc')
            ->lockForUpdate()
            ->first();

        $nextSequence = 1;
        if ($lastStudent) {
            $lastSequenceStr = substr($lastStudent->student_code, strlen($yearPrefix));
            $nextSequence = intval($lastSequenceStr) + 1;
        }

        do {
            $paddedSequence = str_pad($nextSequence, 7, '0', STR_PAD_LEFT);
            $candidateCode = $yearPrefix . $paddedSequence;
            $emailTaken = User::where('email', $candidateCode . '@centu.edu.do')->exists();
            $codeTaken = Student::where('student_code', $candidateCode)->exists();
            if ($emailTaken || $codeTaken) { $nextSequence++; } else { return $candidateCode; }
        } while (true);
    }
}