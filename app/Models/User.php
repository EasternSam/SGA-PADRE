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
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- IMPORTAR Attribute
use App\Models\Student;
use App\Models\CourseSchedule;
use App\Models\Payment;
use Carbon\Carbon; // <-- IMPORTAR CARBON

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            // Auto-generar un PIN de Kiosco de 4 dígitos si no se especificó uno
            if (empty($user->kiosk_pin)) {
                $user->kiosk_pin = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'access_expires_at',
        'profile_photo_path',
        'kiosk_pin',
        // Seguridad / Tracking
        'last_login_at',
        'last_login_ip',
        'failed_login_count',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'kiosk_pin',
        'failed_login_count',
        'locked_until',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
            'password'          => 'hashed',
            'access_expires_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'locked_until'      => 'datetime',
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

    /**
     * Verifica si el usuario tiene acceso (no ha expirado)
     * ESTA ES LA MEJORA AÑADIDA
     */
    public function hasActiveAccess(): bool
    {
        // Si no tiene fecha de expiración (null), tiene acceso (ej. ya pagó)
        if (is_null($this->access_expires_at)) {
            return true;
        }
        // Si tiene fecha, verifica que no haya pasado
        return $this->access_expires_at->isFuture();
    }

    /**
     * Obtener la URL de la foto de perfil.
     * Si tiene foto subida, devuelve esa. Si no, devuelve UI Avatars.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->profile_photo_path
                    ? asset('storage/' . $this->profile_photo_path)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=FFFFFF&background=1E3A8A&bold=true';
            }
        );
    }

    /**
     * Relación: Un Usuario tiene un registro como Empleado de RRHH
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Relación: Intentos de login auditados.
     */
    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class);
    }

    /**
     * ¿Está la cuenta bloqueada?
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}