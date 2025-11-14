<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Student;
use App\Models\CourseSchedule;
use App\Models\Payment;
use Carbon\Carbon; // <-- IMPORTAR CARBON

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'access_expires_at', // <-- AÑADIDO: Para permitir asignación masiva
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
            'access_expires_at' => 'datetime', // <-- AÑADIDO: Para castear a Carbon
        ];
    }

    /**
     * Relación: Un Usuario (Estudiante) tiene un perfil de Estudiante.
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * Relación: Un Usuario (Profesor) tiene muchas secciones/horarios asignados.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(CourseSchedule::class, 'teacher_id');
    }

    /**
     * Relación: Un Usuario (Admin) ha registrado muchos pagos.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }
}