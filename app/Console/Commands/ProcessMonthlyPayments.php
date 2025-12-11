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
    protected $description = 'Genera cargos mensuales automáticos para estudiantes activos en cursos con mensualidad.';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de cobro de mensualidades...');
        Log::info('CRON Mensualidades: Iniciando ejecución.');

        // 1. Obtener concepto de 'Mensualidad'
        $monthlyConcept = PaymentConcept::firstOrCreate(
            ['name' => 'Mensualidad'],
            ['description' => 'Pago recurrente del curso']
        );

        // 2. Buscar inscripciones activas (Cursando)
        // Cargamos la relación profunda para llegar al precio del curso
        $activeEnrollments = Enrollment::where('status', 'Cursando')
            ->with(['courseSchedule.module.course', 'student'])
            ->get();

        $count = 0;

        foreach ($activeEnrollments as $enrollment) {
            $schedule = $enrollment->courseSchedule;
            
            // Validaciones de integridad
            if (!$schedule || !$schedule->module || !$schedule->module->course) {
                continue;
            }

            $course = $schedule->module->course;

            // Si el curso no tiene costo mensual o es gratuito, saltar
            if ($course->monthly_fee <= 0) {
                continue;
            }

            // Fechas clave
            $startDate = Carbon::parse($schedule->start_date);
            $endDate = Carbon::parse($schedule->end_date);
            $today = Carbon::today();

            // A. ¿Estamos dentro del periodo del curso?
            // Si hoy es antes del inicio O después del fin, no cobramos.
            if ($today->lt($startDate) || $today->gt($endDate)) {
                continue;
            }

            // B. ¿Hoy toca cobrar?
            // Cobramos el mismo día del mes en que inició el curso.
            // Ej: Si inició el 15, cobramos los 15 de cada mes.
            $paymentDay = $startDate->day;
            
            // Ajuste para fin de mes (ej: si inició el 31 y estamos en Febrero, cobramos el 28/29)
            if ($today->day !== $paymentDay) {
                // Si hoy NO es el día de pago, verificamos si es fin de mes y el día de pago era mayor
                // Ej: Inició el 31, hoy es 28 Feb (fin de mes). 31 > 28, entonces sí cobramos hoy.
                if (! ($today->isLastOfMonth() && $paymentDay > $today->day) ) {
                    continue; // No es día de cobro
                }
            }

            // C. Evitar duplicados del mes actual
            // Verificamos si ya existe un pago de "Mensualidad" para este estudiante/inscripción en este mes/año.
            $alreadyBilled = Payment::where('enrollment_id', $enrollment->id)
                ->where('payment_concept_id', $monthlyConcept->id)
                ->whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->exists();

            if ($alreadyBilled) {
                continue;
            }

            // D. GENERAR EL COBRO
            try {
                Payment::create([
                    'student_id' => $enrollment->student_id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $monthlyConcept->id,
                    'amount' => $course->monthly_fee,
                    'currency' => 'DOP',
                    'status' => 'Pendiente', // Se genera como deuda
                    'gateway' => 'Sistema Automático',
                    'due_date' => $today->copy()->addDays(5), // 5 días para pagar antes de vencerse
                ]);

                $this->info("Cobro generado: Estudiante #{$enrollment->student_id} - Curso {$course->name}");
                Log::info("CRON Mensualidades: Cobro generado enrollment #{$enrollment->id}");
                $count++;

            } catch (\Exception $e) {
                Log::error("CRON Mensualidades Error: Enrollment #{$enrollment->id} - " . $e->getMessage());
            }
        }

        $this->info("Proceso finalizado. Se generaron {$count} cobros.");
        Log::info("CRON Mensualidades: Finalizado. Total generados: {$count}");
    }
}