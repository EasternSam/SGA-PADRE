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
    public function generarMatricula(Payment $payment)
    {
        if ($payment->status !== 'Completado' || !$payment->student) {
            Log::warning("MatriculaService: El pago {$payment->id} no está 'Completado'.");
            return;
        }

        $student = $payment->student;
        $user = $student->user;

        if ($student->student_code) {
            Log::info("MatriculaService: Estudiante existente {$student->id}. Activando inscripción.");
            $this->activarInscripcion($payment);
            return;
        }

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

                    $emailExists = User::where('email', $newEmail)->where('id', '!=', $user->id)->exists();
                    
                    if ($emailExists) {
                         $matricula = $this->generateUniqueStudentCode(); 
                         $newEmail = $matricula . '@centu.edu.do';
                         $newPassword = Hash::make($matricula);
                         $student->student_code = $matricula; 
                    }

                    $user->email = $newEmail;
                    $user->password = $newPassword;
                    $user->access_expires_at = null;
                    $user->save();
                }

                $student->save();
                $this->activarInscripcion($payment);

            }); 

        } catch (\Exception $e) {
            Log::error("Error al generar matrícula para estudiante ID {$student->id}: " . $e->getMessage());
        }
    }

    public function activarInscripcion(Payment $payment)
    {
        $activated = 0;
        $groupedEnrollments = Enrollment::where('payment_id', $payment->id)->get();

        foreach ($groupedEnrollments as $enrollment) {
            if ($enrollment->status === 'Pendiente' || $enrollment->status === 'Reservado') {
                $enrollment->status = 'Cursando';
                $enrollment->save();
                $activated++;
                $this->syncWithMoodle($enrollment);
            }
        }

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

            if (!$user) {
                Log::warning("Moodle Sync: Estudiante sin usuario. ID: {$student->id}");
                return;
            }

            $moodleService = app(MoodleApiService::class);

            // 1. Credenciales Seguras para Moodle
            $code = $student->student_code ?? 'Student';
            $moodleUsername = strtolower($code); // Usuario = Matrícula
            $moodlePassword = 'Sga*' . $code . '#' . rand(10,99); // Contraseña Fuerte

            // 2. Sincronizar Usuario (Crear si no existe)
            $moodleUserId = $moodleService->syncUser($user, $moodlePassword, $moodleUsername);

            if ($moodleUserId) {
                // Si es la primera vez que lo vinculamos (o lo acabamos de crear)
                if ($user->moodle_user_id !== (string)$moodleUserId) {
                    $user->moodle_user_id = $moodleUserId;
                    $user->saveQuietly();

                    // --- 3. ENVIAR CORREO DE BIENVENIDA ---
                    // Usamos el email personal preferiblemente
                    $emailDestino = $student->email ?? $user->email;
                    
                    try {
                        Mail::to($emailDestino)->send(new MoodleCredentialsMail($user, $moodlePassword, $moodleUsername));
                        Log::info("Correo Moodle enviado a: {$emailDestino}");
                    } catch (\Exception $e) {
                        Log::error("Error enviando correo Moodle: " . $e->getMessage());
                    }
                }

                // 4. Matricular en el curso
                $moodleService->enrollUser($moodleUserId, $moodleCourseId);
                Log::info("Moodle Sync: Usuario {$moodleUsername} inscrito en curso {$moodleCourseId}");
            }
        } catch (\Exception $e) {
            Log::error("Moodle Sync Error: " . $e->getMessage());
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