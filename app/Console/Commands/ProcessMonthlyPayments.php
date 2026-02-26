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
        $this->info('Iniciando proceso de cobro de mensualidades (Delta Billing)...');
        Log::info('CRON Mensualidades: Iniciando ejecución.');

        // 1. Obtener concepto de 'Mensualidad'
        $monthlyConcept = PaymentConcept::firstOrCreate(
            ['name' => 'Mensualidad'],
            ['description' => 'Pago recurrente del curso']
        );

        $today = Carbon::today();

        // 2. Buscar inscripciones activas (Cursando) cuya próxima fecha de facturación sea HASTA hoy.
        // Esto soluciona problemas de caídas del servidor: si el cron no corrió ayer, hoy lo cobrará de todos modos.
        $dueEnrollments = Enrollment::whereIn('status', ['Cursando', 'Activo']) // Algunos lugares usan Activo o Cursando
            ->whereNotNull('next_billing_date')
            ->whereDate('next_billing_date', '<=', $today)
            ->with(['courseSchedule.module.course', 'student'])
            ->get();

        $count = 0;

        foreach ($dueEnrollments as $enrollment) {
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

            // Validar si la inscripción está dentro del periodo general del curso
            // Para no seguir cobrando una vez que el curso terminó
            $endDate = $schedule->end_date ? Carbon::parse($schedule->end_date) : null;
            if ($endDate && $today->gt($endDate)) {
                 $this->info("Saltado: Estudiante #{$enrollment->student_id} - El curso ya finalizó el {$endDate->toDateString()}");
                 continue;
            }

            try {
                // Generar el cobro atómicamente y avanzar el contador al próximo mes
                DB::transaction(function () use ($enrollment, $course, $monthlyConcept) {
                    
                    // PREVENCIÓN DE DUPLICADOS EXTREMA: 
                    // Asegurarnos que en los últimos X días no se le haya generado una mensualidad ya.
                    // Aunque esto teóricamente es imposible ahora por el avance automático de la fecha.
                    $recentPayment = Payment::where('student_id', $enrollment->student_id)
                        ->where('payment_concept_id', $monthlyConcept->id)
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->exists();
                        
                    if (!$recentPayment) {
                        Payment::create([
                            'student_id' => $enrollment->student_id,
                            'enrollment_id' => $enrollment->id,
                            'payment_concept_id' => $monthlyConcept->id,
                            'amount' => $course->monthly_fee,
                            'currency' => 'DOP',
                            'status' => 'Pendiente', // Se genera como deuda
                            'gateway' => 'Sistema Automático',
                            'due_date' => now()->addDays(5), // 5 días para pagar antes de vencerse
                        ]);
                    }

                    // AVANZAR DE MES (Delta Tracking)
                    // Le sumamos 1 mes exactamente a la fecha que le correspondía, NO a la fecha de hoy.
                    // Esto evita desfases si el cron se retrasó 2 días.
                    $currentBillingDate = Carbon::parse($enrollment->next_billing_date);
                    $newBillingDate = $currentBillingDate->addMonth();

                    $enrollment->update([
                        'next_billing_date' => $newBillingDate
                    ]);
                });

                $this->info("Cobro generado: Estudiante #{$enrollment->student_id} - Curso {$course->name}");
                Log::info("CRON Mensualidades: Cobro generado enrollment #{$enrollment->id}");
                $count++;

            } catch (\Exception $e) {
                Log::error("CRON Mensualidades Error: Enrollment #{$enrollment->id} - " . $e->getMessage());
            }
        }

        $this->info("Proceso finalizado. Se generaron {$count} cobros.");
        Log::info("CRON Mensualidades: Finalizado. Total generados: {$count} usando Delta Tracking.");
    }
}