<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;
    protected $guarded = []; // Permitir asignación masiva

    /**
     * Un registro de llamada pertenece a una inscripción.
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Un registro de llamada pertenece a un agente (User).
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Un registro de llamada pertenece a un estudiante.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}