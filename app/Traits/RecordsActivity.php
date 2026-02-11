<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait RecordsActivity
{
    /**
     * Iniciar el trait automáticamente al arrancar el modelo.
     */
    protected static function bootRecordsActivity()
    {
        foreach (static::getRecordableEvents() as $event) {
            static::$event(function (Model $model) use ($event) {
                $model->recordActivity($event);
            });
        }
    }

    /**
     * Eventos que vamos a escuchar.
     */
    protected static function getRecordableEvents()
    {
        return ['created', 'updated', 'deleted'];
    }

    /**
     * Registrar la actividad en la base de datos.
     */
    protected function recordActivity($event)
    {
        // Si se ejecuta desde consola o tarea programada, no hay usuario logueado.
        $userId = Auth::id();
        
        $modelName = class_basename($this);
        $action = $this->getActionName($event);
        
        // Construimos una descripción legible
        $description = "$action registro en $modelName";
        $details = $this->getActivityDetails($event);
        
        if ($details) {
            $description .= ": $details";
        }

        try {
            ActivityLog::create([
                'user_id' => $userId,
                'action' => "$modelName $action", // Ej: Payment Creó
                'description' => $description,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Sistema/Automático',
                // Guardamos datos técnicos (cambios o atributos) en formato JSON
                'payload' => json_encode([
                    'id' => $this->id,
                    'model' => static::class,
                    'event' => $event,
                    'changes' => $event === 'updated' ? $this->getChanges() : null,
                    'attributes' => $event !== 'deleted' ? $this->getAttributes() : null, // Guardar todo el objeto al crear
                ]),
            ]);
        } catch (\Exception $e) {
            // Silenciamos errores de log para no detener la operación principal, 
            // pero lo reportamos al log del sistema por si acaso.
            \Illuminate\Support\Facades\Log::error("Error registrando actividad automática: " . $e->getMessage());
        }
    }

    protected function getActionName($event)
    {
        return match($event) {
            'created' => 'Creó',
            'updated' => 'Actualizó',
            'deleted' => 'Eliminó',
            default => $event
        };
    }

    /**
     * Obtener detalles específicos para que el log sea fácil de leer por humanos.
     */
    protected function getActivityDetails($event)
    {
        // 1. Si es un PAGO
        if ($this instanceof \App\Models\Payment) {
            $monto = number_format($this->amount, 2);
            $estado = $this->status;
            return "Monto RD$ $monto (Estado: $estado)";
        }

        // 2. Si es una INSCRIPCIÓN (Enrollment)
        if ($this instanceof \App\Models\Enrollment) {
            // Intentamos obtener nombres relacionados
            $curso = $this->courseSchedule->module->name ?? 'Módulo';
            $seccion = $this->courseSchedule->section_name ?? 'Sección';
            return "En $curso ($seccion) - Estado: {$this->status}";
        }

        // 3. Si es un ESTUDIANTE
        if ($this instanceof \App\Models\Student) {
            return "{$this->first_name} {$this->last_name} (Matrícula: {$this->student_code})";
        }

        return "ID #{$this->id}";
    }
}