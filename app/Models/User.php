<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Asumiendo que usas Spatie
use Laravel\Sanctum\HasApiTokens; // ¡Añadido!
use Illuminate\Database\Eloquent\Relations\HasOne; // ¡Añadido!
use Illuminate\Database\Eloquent\Relations\HasMany; // ¡Añadido!
use App\Models\Student; // ¡Añadido!
use App\Models\CourseSchedule; // ¡Añadido!
use App\Models\Payment; // ¡Añadido!

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // 'HasApiTokens' añadido

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
    public function student(): HasOne // Tipo de retorno añadido
    {
        // Un usuario (tipo estudiante) tiene UN perfil de estudiante
        // Se mantiene tu lógica original con la clave foránea
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * Relación: Un Usuario (Profesor) tiene muchas secciones/horarios asignados.
     */
    public function schedules(): HasMany // Tipo de retorno añadido
    {
        // Un usuario (tipo profesor) tiene MUCHOS horarios asignados
        // Se mantiene tu lógica original con la clave foránea
        return $this->hasMany(CourseSchedule::class, 'teacher_id');
    }

    /**
     * Relación: Un Usuario (Admin) ha registrado muchos pagos.
     */
    public function payments(): HasMany // Tipo de retorno añadido
    {
        // Un usuario (tipo admin) ha registrado MUCHOS pagos
        // Se mantiene tu lógica original con la clave foránea
        return $this->hasMany(Payment::class, 'user_id');
    }
}