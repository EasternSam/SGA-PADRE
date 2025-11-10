<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Asumiendo que usas Spatie

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; // Asumiendo HasRoles

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación: Un Usuario (Estudiante) tiene un perfil de Estudiante.
     */
    public function student()
    {
        // Un usuario (tipo estudiante) tiene UN perfil de estudiante
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * Relación: Un Usuario (Profesor) tiene muchas secciones/horarios asignados.
     * ¡ESTA ES LA CORRECCIÓN PARA EL ERROR DEL TEACHER DASHBOARD!
     */
    public function schedules()
    {
        // Un usuario (tipo profesor) tiene MUCHOS horarios asignados
        return $this->hasMany(CourseSchedule::class, 'teacher_id');
    }

    /**
     * Relación: Un Usuario (Admin) ha registrado muchos pagos.
     */
    public function payments()
    {
        // Un usuario (tipo admin) ha registrado MUCHOS pagos
        return $this->hasMany(Payment::class, 'user_id');
    }
}