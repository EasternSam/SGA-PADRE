<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyPayments extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'sga:process-monthly-payments';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Genera cargos mensuales automáticos para inscripciones activas';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        Log::info('Iniciando proceso de generación de mensualidades...');

        // 1. Buscar inscripciones activas (Cursando) que tengan horarios asignados
        $activeEnrollments = Enrollment::with(['courseSchedule.module.course', 'student'])
            ->where('status', 'Cursando')
            ->whereHas('courseSchedule')
            ->get();

        $count = 0;

        foreach ($activeEnrollments as $enrollment) {
            $schedule = $enrollment->courseSchedule;
            $course = $schedule->module->course;

            // Si el curso no tiene costo mensual, saltamos
            if ($course->monthly_fee <= 0) {
                continue;
            }

            // Fechas clave
            $startDate = Carbon::parse($schedule->start_date);
            $endDate = Carbon::parse($schedule->end_date);
            $today = Carbon::today();

            // Validación: ¿Estamos dentro del periodo del curso?
            if ($today->lt($startDate) || $today->gt($endDate)) {
                continue;
            }

            // LÓGICA DE FECHA DE CORTE:
            // Generamos cargo si hoy es el mismo día del mes que la fecha de inicio.
            // Ejemplo: Inició el 15 de Enero. Cobramos el 15 de Febrero, 15 de Marzo, etc.
            if ($today->day !== $startDate->day) {
                // Manejo especial para meses cortos (ej: inicio día 31, pero estamos en febrero)
                if (!($today->isLastOfMonth() && $startDate->day > $today->day)) {
                    continue; 
                }
            }

            // Verificar si ya existe un cargo generado para este mes y año específico
            // para evitar duplicados si el comando corre dos veces.
            $exists = Payment::where('enrollment_id', $enrollment->id)
                ->where('student_id', $enrollment->student_id)
                ->where('payment_concept_id', $this->getMonthlyConceptId())
                ->whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->exists();

            if ($exists) {
                continue;
            }

            // Generar el cargo pendiente
            try {
                Payment::create([
                    'student_id' => $enrollment->student_id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $this->getMonthlyConceptId(),
                    'amount' => $course->monthly_fee,
                    'currency' => 'DOP',
                    'status' => 'Pendiente', // El estudiante debe pagarlo
                    'gateway' => 'Sistema',
                    'due_date' => $today->copy()->addDays(5), // Damos 5 días de gracia antes de bloquear/vencer
                ]);

                $count++;
                Log::info("Cargo mensual generado para Estudiante ID: {$enrollment->student_id}, Curso: {$course->name}");

            } catch (\Exception $e) {
                Log::error("Error generando pago para enrollment {$enrollment->id}: " . $e->getMessage());
            }
        }

        $this->info("Proceso finalizado. Se generaron {$count} cargos mensuales.");
    }

    /**
     * Helper para obtener o crear el concepto de pago "Mensualidad"
     */
    private function getMonthlyConceptId()
    {
        $concept = PaymentConcept::firstOrCreate(
            ['name' => 'Mensualidad'],
            ['description' => 'Pago mensual recurrente del curso', 'amount' => 0]
        );
        return $concept->id;
    }
}