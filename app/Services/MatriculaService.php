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
            Log::info("MatriculaService: Estudiante existente. Activando.");
            $this->activarInscripcion($payment);
            return;
        }

        try {
            DB::transaction(function () use ($payment, $student, $user) {
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
            Log::error("Error generar matrícula: " . $e->getMessage());
        }
    }

    public function activarInscripcion(Payment $payment)
    {
        $activated = 0;
        $groupedEnrollments = Enrollment::where('payment_id', $payment->id)->get();

        foreach ($groupedEnrollments as $enrollment) {
            if (in_array($enrollment->status, ['Pendiente', 'Reservado'])) {
                $enrollment->status = 'Cursando';
                $enrollment->save();
                $activated++;
                $this->syncWithMoodle($enrollment);
            }
        }

        if ($payment->enrollment_id) {
            $enrollment = Enrollment::find($payment->enrollment_id);
            if ($enrollment && $enrollment->payment_id !== $payment->id) {
                if (in_array($enrollment->status, ['Pendiente', 'Reservado'])) {
                    $enrollment->status = 'Cursando';
                    $enrollment->save();
                    $activated++;
                    $this->syncWithMoodle($enrollment);
                }
            }
        }

        if ($activated > 0) Log::info("MatriculaService: Pago procesado.");
    }

    private function syncWithMoodle(Enrollment $enrollment)
    {
        try {
            $schedule = $enrollment->courseSchedule;
            if (!$schedule) return;

            // --- CASCADA ---
            $moodleCourseId = $schedule->moodle_course_id;
            if (empty($moodleCourseId)) $moodleCourseId = $schedule->module->moodle_course_id ?? null;
            if (empty($moodleCourseId)) $moodleCourseId = $schedule->module->course->moodle_course_id ?? null;
            if (empty($moodleCourseId)) return;

            $student = $enrollment->student;
            $user = $student->user;
            if (!$user) return;

            $moodleService = app(MoodleApiService::class);

            $code = $student->student_code ?? 'Student';
            $moodleUsername = strtolower($code);
            
            // Generamos contraseña segura (solo se usará si el usuario es NUEVO)
            $moodlePassword = 'Sga*' . $code . '#' . rand(10,99);

            // Sincronizar: Ahora devuelve array ['id', 'was_created']
            $syncResult = $moodleService->syncUser($user, $moodlePassword, $moodleUsername);

            if ($syncResult && isset($syncResult['id'])) {
                $moodleUserId = $syncResult['id'];
                $wasCreated = $syncResult['was_created'];

                // Si es la primera vez que enlazamos este usuario con Moodle en Laravel
                if ($user->moodle_user_id !== (string)$moodleUserId) {
                    $user->moodle_user_id = $moodleUserId;
                    $user->saveQuietly();

                    // --- LÓGICA INTELIGENTE DE CORREO ---
                    // Solo enviamos credenciales si el usuario FUE CREADO AHORA.
                    // Si ya existía, no le mandamos contraseña porque la del email no funcionaría.
                    if ($wasCreated) {
                        $emailDestino = $student->email ?? $user->email;
                        try {
                            Mail::to($emailDestino)->send(new MoodleCredentialsMail($user, $moodlePassword, $moodleUsername));
                            Log::info("Correo Bienvenida Moodle enviado a: {$emailDestino}");
                        } catch (\Exception $e) {
                            Log::error("Error mail Moodle: " . $e->getMessage());
                        }
                    } else {
                        Log::info("Usuario Moodle {$moodleUsername} ya existía. Vinculado sin enviar nuevas credenciales.");
                    }
                }

                // Matricular
                $moodleService->enrollUser($moodleUserId, $moodleCourseId);
                Log::info("Moodle Sync: Usuario {$moodleUsername} enrolado en {$moodleCourseId}");
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
            if ($emailTaken || $codeTaken) { $nextSequence++; } else { return $candidateCode; }
        } while (true);
    }
}