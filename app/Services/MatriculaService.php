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

            // --- LÓGICA DE CASCADA ---
            // 1. Verificar si la SECCIÓN tiene un ID de Moodle
            $moodleCourseId = $schedule->moodle_course_id;

            // 2. Si no, verificar si el MÓDULO tiene un enlace
            if (empty($moodleCourseId)) {
                $moodleCourseId = $schedule->module->moodle_course_id ?? null;
            }

            // 3. Si no, verificar si el CURSO padre tiene un enlace
            if (empty($moodleCourseId)) {
                $moodleCourseId = $schedule->module->course->moodle_course_id ?? null;
            }

            // Si después de todo esto no hay ID, no hacemos nada
            if (empty($moodleCourseId)) {
                return;
            }

            $student = $enrollment->student;
            $user = $student->user;

            if (!$user) {
                Log::warning("Moodle Sync: El estudiante ID {$student->id} no tiene usuario de sistema asociado.");
                return;
            }

            $moodleService = app(MoodleApiService::class);

            // --- GESTIÓN DE USUARIO ---
            $code = $student->student_code ?? 'Student';
            $moodleUsername = strtolower($code); // Usuario = Matrícula
            
            // Generamos una contraseña segura SOLO por si hay que crear al usuario.
            // Si ya existe, Moodle NO la actualizará porque el servicio syncUser está programado así.
            $moodlePassword = 'Sga*' . $code . '#' . rand(10,99);

            // Sincronizar: Buscamos si existe. Si no, lo creamos.
            // NOTA: syncUser ahora devuelve un array ['id' => ..., 'was_created' => bool] (según lo definimos en MoodleApiService)
            // Si tu versión actual de MoodleApiService solo devuelve ID (int/string), 
            // asumiremos que si ya tenía ID guardado localmente, NO es nuevo.
            
            $result = $moodleService->syncUser($user, $moodlePassword, $moodleUsername);
            
            // Adaptamos la respuesta por si el servicio devuelve solo ID o Array
            $moodleUserId = is_array($result) ? $result['id'] : $result;
            $wasCreated = is_array($result) ? ($result['was_created'] ?? false) : false;

            // Si el servicio devuelve solo ID, deducimos si es nuevo comparando con la BD local
            if (!is_array($result) && $moodleUserId) {
                $wasCreated = ($user->moodle_user_id !== (string)$moodleUserId);
            }

            if ($moodleUserId) {
                
                // Si es la primera vez que lo vinculamos (o lo acabamos de crear)
                if ($user->moodle_user_id !== (string)$moodleUserId) {
                    $user->moodle_user_id = $moodleUserId;
                    $user->saveQuietly();
                }

                // --- ENVIAR CORREO (SOLO SI ES NUEVO) ---
                if ($wasCreated) {
                    $emailDestino = $student->email ?? $user->email;
                    try {
                        Mail::to($emailDestino)->send(new MoodleCredentialsMail($user, $moodlePassword, $moodleUsername));
                        Log::info("Correo Bienvenida Moodle enviado a: {$emailDestino} (Usuario creado)");
                    } catch (\Exception $e) {
                        Log::error("Error enviando correo Moodle: " . $e->getMessage());
                    }
                } else {
                    Log::info("Usuario Moodle {$moodleUsername} ya existía. No se envió nueva contraseña.");
                }

                // Matricular en el curso
                $moodleService->enrollUser($moodleUserId, $moodleCourseId);
                
                Log::info("Moodle Sync: Usuario {$moodleUsername} matriculado en curso Moodle ID {$moodleCourseId}.");
            } else {
                Log::error("Moodle Sync: No se pudo obtener ID de usuario para {$user->email}.");
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