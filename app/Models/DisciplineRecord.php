<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplineRecord extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'date', 'severity',
        'category', 'description', 'action_taken',
        'reported_by', 'parent_notified', 'parent_notified_at', 'follow_up',
    ];

    protected $casts = [
        'date' => 'date',
        'parent_notified' => 'boolean',
        'parent_notified_at' => 'date',
    ];

    const SEVERITIES = [
        'leve'      => '🟡 Leve',
        'grave'     => '🟠 Grave',
        'muy_grave' => '🔴 Muy Grave',
    ];

    const CATEGORIES = [
        'puntualidad'        => 'Puntualidad',
        'uniforme'           => 'Uniforme/Presentación',
        'respeto'            => 'Falta de Respeto',
        'agresion_verbal'    => 'Agresión Verbal',
        'agresion_fisica'    => 'Agresión Física',
        'uso_celular'        => 'Uso de Celular',
        'dano_propiedad'     => 'Daño a Propiedad',
        'copia_examen'       => 'Copia en Examen',
        'inasistencia'       => 'Inasistencia Injustificada',
        'sustancias'         => 'Sustancias Prohibidas',
        'bullying'           => 'Bullying/Acoso',
        'otro'               => 'Otro',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
