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
        // Generamos un ID corto para rastrear esta ejecución específica en los logs
        $traceId = '[MAT-' . strtoupper(Str::random(6)) . ']';

        Log::info("{$traceId} INICIO: Procesando generación de matrícula para Pago ID: {$payment->id}");

        // Verificación 1: Estado del pago
        if ($payment->status !== 'Completado') {
            Log::warning("{$traceId} ABORTADO: El pago no está en estado 'Completado'. Estado actual: {$payment->status}");
            return;
        }

        // Verificación 2: Relación Student
        if (!$payment->student) {
            Log::error("{$traceId} ERROR CRÍTICO: El objeto Payment no tiene un estudiante asociado (relación es null). Enrollment ID: {$payment->enrollment_id}");
            return;
        }

        $student = $payment->student;
        $user = $student->user;

        Log::info("{$traceId} DATOS: Estudiante ID: {$student->id}, Usuario ID: " . ($user ? $user->id : 'NULL'));

        // Si el estudiante ya tiene matrícula, solo activamos la inscripción
        if ($student->student_code) {
            Log::info("{$traceId} ESTADO: Estudiante ya tiene matrícula ({$student->student_code}). Procediendo a activar inscripción.");
            $this->activarInscripcion($payment, $traceId);
            return;
        }

        // Si es un estudiante nuevo (temporal), procedemos a matricular
        try {
            DB::transaction(function () use ($payment, $student, $user, $traceId) {
                
                Log::info("{$traceId} TRANSACCIÓN: Iniciando transacción de base de datos.");

                if (!$student->student_code) {
                    Log::info("{$traceId} GENERACIÓN: Calculando nuevo código de estudiante...");
                    $student->student_code = $this->generateUniqueStudentCode($traceId);
                    Log::info("{$traceId} GENERACIÓN: Código asignado temporalmente: {$student->student_code}");
                }
                
                if ($user) {
                    $matricula = $student->student_code;
                    $newEmail = $matricula . '@centu.edu.do';
                    $newPassword = Hash::make($matricula);

                    // Verificar colisión de email (raro pero posible)
                    $emailExists = User::where('email', $newEmail)->where('id', '!=', $user->id)->exists();
                    
                    if ($emailExists) {
                         Log::warning("{$traceId} COLISIÓN: El email {$newEmail} ya existe. Regenerando matrícula.");
                         $matricula = $this->generateUniqueStudentCode($traceId); 
                         $newEmail = $matricula . '@centu.edu.do';
                         $newPassword = Hash::make($matricula);
                         $student->student_code = $matricula; 
                         Log::info("{$traceId} RE-GENERACIÓN: Nueva matrícula asignada: {$student->student_code}");
                    }

                    $user->email = $newEmail;
                    $user->password = $newPassword;
                    $user->access_expires_at = null; // Quitar expiración temporal
                    
                    if($user->save()){
                        Log::info("{$traceId} USUARIO: Usuario actualizado correctamente (Email: {$newEmail}).");
                    } else {
                        Log::error("{$traceId} USUARIO: Falló el guardado del usuario.");
                    }
                } else {
                    Log::warning("{$traceId} USUARIO: No se encontró usuario asociado al estudiante. Se guardará solo el estudiante.");
                }

                if($student->save()){
                    Log::info("{$traceId} ESTUDIANTE: Estudiante guardado correctamente con matrícula {$student->student_code}.");
                } else {
                    Log::error("{$traceId} ESTUDIANTE: Falló el guardado del modelo Student.");
                }
                
                // Llamamos a la activación explícitamente dentro de la transacción
                $this->activarInscripcion($payment, $traceId);

            }); 

            Log::info("{$traceId} ÉXITO: Transacción completada y commited.");

        } catch (\Exception $e) {
            Log::error("{$traceId} EXCEPCIÓN: Error al generar matrícula. " . 
                "Mensaje: " . $e->getMessage() . 
                " | Archivo: " . $e->getFile() . 
                " | Línea: " . $e->getLine()
            );
        }
    }

    /**
     * Cambia el estado de la inscripción asociada al pago.
     * Pasa de 'Pendiente' a 'Cursando'.
     */
    public function activarInscripcion(Payment $payment, $traceId = null)
    {
        $traceId = $traceId ?? '[MAT-ACTIV]';
        $activated = 0;

        Log::info("{$traceId} ACTIVACIÓN: Buscando inscripciones para activar...");

        // ESTRATEGIA 1: Pagos Agrupados (Carreras/Selección de Materias)
        $groupedEnrollments = Enrollment::where('payment_id', $payment->id)->get();
        Log::info("{$traceId} ACTIVACIÓN: Encontradas " . $groupedEnrollments->count() . " inscripciones agrupadas.");

        foreach ($groupedEnrollments as $enrollment) {
            if ($enrollment->status === 'Pendiente' || $enrollment->status === 'Reservado') {
                $enrollment->status = 'Cursando';
                $enrollment->save();
                $activated++;
                
                // Intento de matriculación en Moodle
                $this->syncWithMoodle($enrollment, $traceId);
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

                    Log::info("{$traceId} ACTIVACIÓN: Inscripción individual {$enrollment->id} activada.");

                    // Intento de matriculación en Moodle
                    $this->syncWithMoodle($enrollment, $traceId);
                }
            }
        }

        if ($activated > 0) {
            Log::info("{$traceId} RESUMEN: Pago procesado. Se activaron {$activated} inscripciones en total.");
        } else {
            Log::info("{$traceId} RESUMEN: No se requirió activar ninguna inscripción (posiblemente ya estaban activas).");
        }
    }

    /**
     * Sincroniza la inscripción con Moodle con lógica de CASCADA.
     * Prioridad de Enlace: Sección > Módulo > Curso
     */
    private function syncWithMoodle(Enrollment $enrollment, $traceId = null)
    {
        $traceId = $traceId ?? '[MOODLE-SYNC]';

        try {
            $schedule = $enrollment->courseSchedule;
            if (!$schedule) {
                Log::warning("{$traceId} MOODLE: No hay horario asociado a la inscripción {$enrollment->id}.");
                return;
            }

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
                // Log::info("{$traceId} MOODLE: No se encontró ID de curso Moodle en la cascada para inscripción {$enrollment->id}.");
                return;
            }

            $student = $enrollment->student;
            $user = $student->user;

            if (!$user) {
                Log::warning("{$traceId} MOODLE: El estudiante ID {$student->id} no tiene usuario de sistema asociado.");
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
                        Log::info("{$traceId} MOODLE: Correo Bienvenida enviado a: {$emailDestino} (Usuario creado)");
                    } catch (\Exception $e) {
                        Log::error("{$traceId} MOODLE: Error enviando correo: " . $e->getMessage());
                    }
                } else {
                    // Log::info("{$traceId} MOODLE: Usuario {$moodleUsername} ya existía. No se envió nueva contraseña.");
                }

                // Matricular en el curso
                $moodleService->enrollUser($moodleUserId, $moodleCourseId);
                
                Log::info("{$traceId} MOODLE: Usuario {$moodleUsername} matriculado en curso Moodle ID {$moodleCourseId}.");
            } else {
                Log::error("{$traceId} MOODLE: No se pudo obtener ID de usuario para {$user->email}.");
            }

        } catch (\Exception $e) {
            Log::error("{$traceId} MOODLE EXCEPTION (Enrollment {$enrollment->id}): " . $e->getMessage());
        }
    }

    private function generateUniqueStudentCode($traceId = ''): string
    {
        Log::info("{$traceId} CODE-GEN: Buscando último estudiante para secuencia...");
        
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
            Log::info("{$traceId} CODE-GEN: Último encontrado {$lastStudent->student_code}. Siguiente secuencia base: {$nextSequence}");
        } else {
            Log::info("{$traceId} CODE-GEN: No se encontraron estudiantes previos este año. Iniciando en 1.");
        }

        $attempts = 0;
        do {
            $paddedSequence = str_pad($nextSequence, 7, '0', STR_PAD_LEFT);
            $candidateCode = $yearPrefix . $paddedSequence;
            
            $emailTaken = User::where('email', $candidateCode . '@centu.edu.do')->exists();
            $codeTaken = Student::where('student_code', $candidateCode)->exists();

            if ($emailTaken || $codeTaken) {
                Log::warning("{$traceId} CODE-GEN: Colisión encontrada para {$candidateCode}. Incrementando secuencia.");
                $nextSequence++;
                $attempts++;
                if ($attempts > 50) {
                    Log::error("{$traceId} CODE-GEN: Demasiados intentos de generación de código. Posible bucle infinito.");
                    throw new \Exception("Error generando matrícula única después de 50 intentos.");
                }
            } else {
                Log::info("{$traceId} CODE-GEN: Código disponible encontrado: {$candidateCode}");
                return $candidateCode;
            }
        } while (true);
    }
}