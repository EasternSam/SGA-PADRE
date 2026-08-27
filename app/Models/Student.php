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

        // Campos escolares MINERD
        'grade_level_id',
        'section_id',
        'academic_year_id',
        'enrollment_date',
        'blood_type',
        'allergies',
        'medical_conditions',
        'emergency_contact_name',
        'emergency_contact_phone',
        'previous_school',
        'documents_status',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array
     */
    protected $casts = [
        'birth_date'       => 'date',
        'enrollment_date'  => 'date',
        'is_minor'         => 'boolean',
        'balance'          => 'decimal:2',
        'documents_status' => 'array',
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

    // ── Relaciones Escolares MINERD ─────────────────────────

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentGrades()
    {
        return $this->hasMany(StudentGrade::class);
    }

    public function reportCards()
    {
        return $this->hasMany(ReportCard::class);
    }

    /**
     * Obtiene el nombre completo del estudiante.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Promedio general del estudiante en un período.
     */
    public function getAverageForPeriod(int $periodId): ?float
    {
        $grades = $this->studentGrades()
            ->where('evaluation_period_id', $periodId)
            ->whereNotNull('score')
            ->get();

        return $grades->isEmpty() ? null : round($grades->avg('score'), 2);
    }
}