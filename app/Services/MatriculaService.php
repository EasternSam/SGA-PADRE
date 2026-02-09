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
use Illuminate\Support\Str; // Importante
use App\Services\MoodleApiService;
use App\Mail\MoodleCredentialsMail; // Importamos el Mailable

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

        // ESTRATEGIA 1: Pagos Agrupados
        $groupedEnrollments = Enrollment::where('payment_id', $payment->id)->get();

        foreach ($groupedEnrollments as $enrollment) {
            if ($enrollment->status === 'Pendiente' || $enrollment->status === 'Reservado') {
                $enrollment->status = 'Cursando';
                $enrollment->save();
                $activated++;
                
                $this->syncWithMoodle($enrollment);
            }
        }

        // ESTRATEGIA 2: Pagos Individuales
        if ($payment->enrollment_id) {
            $enrollment = Enrollment::find($payment->enrollment_id);
            if ($enrollment && $enrollment->payment_id !== $payment->id) {
                if ($enrollment->status === 'Pendiente' || $enrollment->status === 'Reservado') {
                    $enrollment->status = 'Cursando';
                    $enrollment->save();
                    $activated++;

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
     * Prioridad: Sección > Módulo > Curso
     */
    private function syncWithMoodle(Enrollment $enrollment)
    {
        try {
            $schedule = $enrollment->courseSchedule;
            if (!$schedule) return;

            // --- LÓGICA DE CASCADA ---
            // 1. Verificar si la SECCIÓN tiene un ID de Moodle
            $moodleCourseId = $schedule->moodle_course_id;

            // 2. Si no, verificar si el MÓDULO tiene un ID de Moodle
            if (empty($moodleCourseId)) {
                $moodleCourseId = $schedule->module->moodle_course_id ?? null;
            }

            // 3. Si no, verificar si el CURSO padre tiene un ID de Moodle
            if (empty($moodleCourseId)) {
                $moodleCourseId = $schedule->module->course->moodle_course_id ?? null;
            }

            // Si después de todo esto no hay ID, salimos
            if (empty($moodleCourseId)) {
                return;
            }

            $student = $enrollment->student;
            $user = $student->user;

            if (!$user) {
                Log::warning("Moodle Sync: El estudiante ID {$student->id} no tiene usuario de sistema asociado.");
                return;
            }

            // Resolvemos el servicio
            $moodleService = app(MoodleApiService::class);

            // --- CORRECCIÓN CRÍTICA: CONTRASEÑA MÁS COMPATIBLE ---
            // El log indicó que Moodle exige caracteres como *, -, o #.
            // Cambiamos el formato a: Sga*Matricula#12
            // Sga (Mayus/Minus) + * (Especial) + Matricula (Numero) + # (Especial) + Random
            $code = $student->student_code ?? 'Student';
            $moodlePassword = 'Sga*' . $code . '#' . rand(10,99);

            // 1. Sincronizar Usuario
            $moodleUserId = $moodleService->syncUser($user, $moodlePassword);

            if ($moodleUserId) {
                // Guardar el ID de Moodle localmente
                if ($user->moodle_user_id !== $moodleUserId) {
                    $user->moodle_user_id = $moodleUserId;
                    $user->saveQuietly();
                    
                    // --- ENVIAR CORREO CON CREDENCIALES (SOLO SI ES USUARIO NUEVO EN MOODLE/LOCAL) ---
                    // Como acabamos de guardar el ID, asumimos que es la primera vez que sincronizamos
                    // o que no lo teníamos registrado.
                    try {
                        Mail::to($user->email)->send(new MoodleCredentialsMail($user, $moodlePassword));
                        Log::info("Correo de credenciales Moodle enviado a {$user->email}");
                    } catch (\Exception $e) {
                        Log::error("Error enviando correo Moodle: " . $e->getMessage());
                    }
                }

                // 2. Matricular en el curso específico encontrado
                $moodleService->enrollUser($moodleUserId, $moodleCourseId);
                
                Log::info("Moodle Sync: Usuario {$user->email} matriculado en Moodle ID {$moodleCourseId}.");
            } else {
                Log::error("Moodle Sync: Fallo al crear/buscar usuario {$user->email}. Revisa los logs de error anteriores.");
            }

        } catch (\Exception $e) {
            Log::error("Moodle Sync Error (Enrollment {$enrollment->id}): " . $e->getMessage());
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