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
use App\Services\MoodleApiService; // Importamos el servicio

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
     * AHORA PÚBLICO para ser usado desde PaymentModal (Admin).
     */
    public function activarInscripcion(Payment $payment)
    {
        $activated = 0;

        // ESTRATEGIA 1: Pagos Agrupados (Carreras/Selección de Materias - Relación hasMany)
        // Esto cubre el caso de "Pago de Cuatrimestre" que agrupa varias materias
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

        // ESTRATEGIA 2: Pagos Individuales (Cursos Libres/Diplomados - Relación legacy belongsTo)
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
     * Sincroniza la inscripción con Moodle si corresponde.
     */
    private function syncWithMoodle(Enrollment $enrollment)
    {
        try {
            // Navegar relaciones para encontrar el ID del curso en Moodle
            // Enrollment -> CourseSchedule -> Module -> Course -> moodle_course_id
            $course = $enrollment->courseSchedule->module->course ?? null;

            if (!$course || empty($course->moodle_course_id)) {
                return; // No es un curso de Moodle o no está configurado
            }

            $student = $enrollment->student;
            $user = $student->user;

            if (!$user) {
                Log::warning("Moodle Sync: El estudiante ID {$student->id} no tiene usuario de sistema asociado.");
                return;
            }

            // Resolvemos el servicio aquí para evitar problemas de inyección en constructores antiguos
            $moodleService = app(MoodleApiService::class);

            // Contraseña inicial para Moodle (usamos la matrícula o una por defecto)
            $moodlePassword = $student->student_code ?? 'Centu' . date('Y');

            // 1. Sincronizar Usuario (Crear u obtener ID)
            $moodleUserId = $moodleService->syncUser($user, $moodlePassword);

            if ($moodleUserId) {
                // Guardar el ID de Moodle en local si es nuevo/diferente
                if ($user->moodle_user_id !== $moodleUserId) {
                    $user->moodle_user_id = $moodleUserId;
                    $user->saveQuietly(); // Evitar disparar observadores innecesarios
                }

                // 2. Matricular en el curso
                $moodleService->enrollUser($moodleUserId, $course->moodle_course_id);
                
                Log::info("Moodle Sync: Usuario {$user->email} matriculado en curso Moodle ID {$course->moodle_course_id}.");

                // Aquí podrías disparar un evento o enviar el correo con las credenciales
                // Mail::to($user->email)->send(new MoodleWelcomeMail($user, $moodlePassword, $course));
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