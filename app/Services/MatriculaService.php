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
                    $user->save();
                }

                $student->save();
                
                // Llamamos a la activación explícitamente dentro de la transacción
                $this->activarInscripcion($payment);

            }); 

        } catch (\Exception $e) {
            Log::error("Error al generar matrícula para estudiante ID {$student->id}: " . $e->getMessage());
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
                
                // Intento de matriculación en Moodle
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

                    // Intento de matriculación en Moodle
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
     * Prioridad de Enlace: Sección > Módulo > Curso
     */
    private function syncWithMoodle(Enrollment $enrollment)
    {
        try {
            $schedule = $enrollment->courseSchedule;
            if (!$schedule) return;

            // --- 1. DETECTAR EL ID DEL CURSO EN MOODLE (CASCADA) ---
            
            // A) Verificar si la SECCIÓN (Horario) tiene un enlace específico
            $moodleCourseId = $schedule->moodle_course_id;

            // B) Si no, verificar si el MÓDULO tiene un enlace
            if (empty($moodleCourseId)) {
                $moodleCourseId = $schedule->module->moodle_course_id ?? null;
            }

            // C) Si no, verificar si el CURSO padre tiene un enlace
            if (empty($moodleCourseId)) {
                $moodleCourseId = $schedule->module->course->moodle_course_id ?? null;
            }

            // Si después de todo esto no hay ID, no hacemos nada (no es un curso virtual)
            if (empty($moodleCourseId)) {
                return;
            }

            // --- 2. PREPARAR DATOS DEL USUARIO ---
            $student = $enrollment->student;
            $user = $student->user;

            if (!$user) {
                Log::warning("Moodle Sync: El estudiante ID {$student->id} no tiene usuario de sistema asociado.");
                return;
            }

            // Resolvemos el servicio de Moodle
            $moodleService = app(MoodleApiService::class);

            // Generamos credenciales que cumplan la política estricta de Moodle 5.0
            // Requisito: 8 chars, 1 mayúscula, 1 minúscula, 1 número, 1 no alfanumérico (*, #, etc)
            $code = $student->student_code ?? 'ST' . $student->id;
            
            // Contraseña: Sga* + Matricula + # + 2 numeros random (Ej: Sga*2026001#45)
            $moodlePassword = 'Sga*' . $code . '#' . rand(10,99);
            
            // Usuario: Usamos la matrícula en minúsculas (Ej: 2026001)
            // Esto evita problemas con correos duplicados o largos
            $moodleUsername = strtolower($code);

            // --- 3. SINCRONIZAR USUARIO (CREAR O BUSCAR) ---
            // Le pasamos el username explícito para que no use el email como usuario
            $moodleUserId = $moodleService->syncUser($user, $moodlePassword, $moodleUsername);

            if ($moodleUserId) {
                
                // --- 4. GESTIÓN DE ID LOCAL Y ENVÍO DE CORREO ---
                // Si el ID es nuevo o diferente, significa que acabamos de crear/vincular al usuario
                if ($user->moodle_user_id !== (string)$moodleUserId) {
                    $user->moodle_user_id = $moodleUserId;
                    $user->saveQuietly(); // Guardamos sin disparar eventos de observer

                    // Enviar correo de credenciales
                    // Intentamos usar el email personal del estudiante, si no, el del usuario
                    $emailDestino = $student->email ?? $user->email;
                    
                    if ($emailDestino) {
                        try {
                            Mail::to($emailDestino)->send(new MoodleCredentialsMail($user, $moodlePassword, $moodleUsername));
                            Log::info("Correo de credenciales Moodle enviado a: {$emailDestino} (User: {$moodleUsername})");
                        } catch (\Exception $e) {
                            Log::error("Error enviando correo Moodle a {$emailDestino}: " . $e->getMessage());
                        }
                    }
                }

                // --- 5. MATRICULAR EN EL CURSO ---
                $moodleService->enrollUser($moodleUserId, $moodleCourseId);
                
                Log::info("Moodle Sync: Usuario {$moodleUsername} matriculado en curso Moodle ID {$moodleCourseId}.");
            } else {
                Log::error("Moodle Sync: No se pudo obtener ID de usuario para {$user->email}. Verifica logs anteriores.");
            }

        } catch (\Exception $e) {
            Log::error("Moodle Sync Error Crítico (Enrollment {$enrollment->id}): " . $e->getMessage());
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

            if ($emailTaken || $codeTaken) {
                $nextSequence++;
            } else {
                return $candidateCode;
            }
        } while (true);
    }
}