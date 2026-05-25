<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEnrollment extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'grade_level_id', 'section_id',
        'enrollment_code', 'status', 'enrollment_type', 'enrollment_date',
        'withdrawal_date', 'withdrawal_reason', 'previous_school', 'transfer_certificate',
        'doc_birth_certificate', 'doc_photos', 'doc_grades_record',
        'doc_medical_certificate', 'doc_vaccination_card', 'doc_parent_id',
        'doc_report_card', 'doc_good_conduct',
        'processed_by', 'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'withdrawal_date' => 'date',
        'doc_birth_certificate' => 'boolean',
        'doc_photos' => 'boolean',
        'doc_grades_record' => 'boolean',
        'doc_medical_certificate' => 'boolean',
        'doc_vaccination_card' => 'boolean',
        'doc_parent_id' => 'boolean',
        'doc_report_card' => 'boolean',
        'doc_good_conduct' => 'boolean',
    ];

    const STATUSES = [
        'pending'         => '⏳ Pendiente',
        'approved'        => '✅ Aprobada',
        'enrolled'        => '🎓 Matriculado',
        'transferred_out' => '🔄 Trasladado',
        'withdrawn'       => '❌ Retirado',
        'graduated'       => '🎉 Egresado',
    ];

    const STATUS_COLORS = [
        'pending'         => 'yellow',
        'approved'        => 'blue',
        'enrolled'        => 'green',
        'transferred_out' => 'purple',
        'withdrawn'       => 'red',
        'graduated'       => 'emerald',
    ];

    const ENROLLMENT_TYPES = [
        'new'      => 'Nuevo Ingreso',
        'renewal'  => 'Reinscripción',
        'transfer' => 'Transferencia',
    ];

    const REQUIRED_DOCS = [
        'doc_birth_certificate'  => 'Acta de Nacimiento',
        'doc_photos'             => 'Fotos 2x2',
        'doc_grades_record'      => 'Récord de Notas',
        'doc_medical_certificate'=> 'Certificado Médico',
        'doc_vaccination_card'   => 'Tarjeta de Vacunación',
        'doc_parent_id'          => 'Cédula Padre/Tutor',
        'doc_report_card'        => 'Boletín de Notas Anterior',
        'doc_good_conduct'       => 'Carta de Buena Conducta',
    ];

    // ── Accessors ────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getDocumentsCompletedAttribute(): int
    {
        return collect(array_keys(self::REQUIRED_DOCS))
            ->filter(fn($doc) => $this->{$doc})
            ->count();
    }

    public function getDocumentsTotalAttribute(): int
    {
        return count(self::REQUIRED_DOCS);
    }

    public function getDocumentsPercentageAttribute(): float
    {
        return $this->documents_total > 0
            ? round(($this->documents_completed / $this->documents_total) * 100, 0)
            : 0;
    }

    // ── Relationships ────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Helpers ──────────────────────────────────────────────

    public static function generateCode(int $yearId, int $gradeLevelId): string
    {
        $year = AcademicYear::find($yearId);
        $prefix = $year ? substr($year->name, 0, 4) : date('Y');
        $count = self::where('academic_year_id', $yearId)->count() + 1;
        return sprintf('%s-%02d-%04d', $prefix, $gradeLevelId, $count);
    }
}
