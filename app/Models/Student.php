<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity; // <-- IMPORTANTE

class Student extends Model
{
    use HasFactory, RecordsActivity; // <-- ACTIVAR AUDITORÍA

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // Vinculación con User
        'course_id', // <-- NUEVO: Carrera/Curso asignado
        'scholarship_id', // Beca asignada
        'student_code', // Matrícula
        'first_name',
        'last_name',
        'cedula',
        'email',
        'gender',
        'birth_date',
        'nationality',
        'address',
        'sector',
        'city',
        'home_phone', // Teléfono residencial/fijo
        'mobile_phone', // Teléfono celular
        'how_found', // Fuente de captación
        'status', // Activo, Inactivo, etc.
        'balance', // Balance financiero
        'rnc', // RNC para comprobantes fiscales
        
        // Campos de Tutor (para menores de edad)
        'is_minor',
        'tutor_name',
        'tutor_cedula',
        'tutor_phone',
        'tutor_relationship',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array
     */
    protected $casts = [
        'birth_date' => 'date',
        'is_minor' => 'boolean',
        'balance' => 'decimal:2',
    ];

    /**
     * Un estudiante (Student) pertenece a un usuario (User).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la Carrera/Curso principal.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relación con la Beca asignada.
     */
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    /**
     * Un estudiante tiene muchas inscripciones (Enrollments).
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Un estudiante tiene muchos pagos (Payments).
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Un estudiante puede tener muchas solicitudes.
     */
    public function requests()
    {
        return $this->hasMany(StudentRequest::class);
    }

    /**
     * Relación con el Plan de Carrera del estudiante.
     */
    public function degreePlan()
    {
        return $this->hasOne(DegreePlan::class);
    }

    /**
     * Booted method to register model events.
     */
    protected static function booted()
    {
        static::created(function ($student) {
            try {
                $wpApiService = app(\App\Services\WordpressApiService::class);
                $wpApiService->syncStudent([
                    'cedula'     => $student->cedula,
                    'first_name' => $student->first_name,
                    'last_name'  => $student->last_name,
                    'email'      => $student->email,
                    'phone'      => $student->mobile_phone ?: $student->home_phone,
                    'address'    => $student->address,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error al sincronizar estudiante creado a WordPress: " . $e->getMessage());
            }

            // Enviar correo de bienvenida si es un usuario recién creado (primer inicio de sesión)
            try {
                $user = $student->user;
                if ($user && $user->created_at && $user->created_at->gt(now()->subMinutes(2))) {
                    $cleanCedula = preg_replace('/[^0-9]/', '', $student->cedula);
                    $institutionName = \App\Models\Setting::val('institution_name', config('app.name', 'Academic+'));
                    $subject = '🎓 ¡Bienvenido a ' . $institutionName . '! Tus credenciales de acceso';
                    $loginUrl = url('/login');
                    $message = "¡Hola {$student->first_name}!\n\n"
                             . "Te damos la más cordial bienvenida a nuestra plataforma académica **{$institutionName}**.\n\n"
                             . "Tu cuenta ya está activa para ingresar a la plataforma y gestionar tus clases, calificaciones y pagos.\n\n"
                             . "Puedes acceder desde el siguiente enlace:\n"
                             . "{$loginUrl}\n\n"
                             . "Tus credenciales de acceso son:\n"
                             . "• Usuario: {$student->email} (o tu número de cédula: {$student->cedula})\n"
                             . "• Contraseña temporal: {$cleanCedula} (tu cédula sin guiones)\n\n"
                             . "Te sugerimos cambiar tu contraseña al ingresar por primera vez por motivos de seguridad.\n\n"
                             . "¡Mucho éxito en tus estudios!";

                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CustomSystemMail($subject, $message));
                    \Illuminate\Support\Facades\Log::info("Correo de bienvenida enviado automáticamente a {$user->email}.");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error al enviar correo de bienvenida al estudiante: " . $e->getMessage());
            }
        });

        static::updated(function ($student) {
            try {
                $wpApiService = app(\App\Services\WordpressApiService::class);
                $wpApiService->syncStudent([
                    'cedula'     => $student->cedula,
                    'first_name' => $student->first_name,
                    'last_name'  => $student->last_name,
                    'email'      => $student->email,
                    'phone'      => $student->mobile_phone ?: $student->home_phone,
                    'address'    => $student->address,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error al sincronizar estudiante actualizado a WordPress: " . $e->getMessage());
            }
        });
    }

    /**
     * Obtiene el nombre completo del estudiante.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}