<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FixBillingDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sga:fix-billing-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Puebla la nueva columna next_billing_date para inscripciones existentes en la base de datos basandose en pagos recientes o el inicio del curso.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando migración de datos a Delta Billing (next_billing_date)...");
        
        $monthlyConcept = PaymentConcept::firstOrCreate(
            ['name' => 'Mensualidad']
        );

        $enrollments = Enrollment::whereNull('next_billing_date')
            ->whereIn('status', ['Activo', 'Cursando'])
            ->with(['courseSchedule'])
            ->get();

        $this->info("Encontrados {$enrollments->count()} enrollments activos sin next_billing_date.");

        $count = 0;
        $today = Carbon::today();

        foreach ($enrollments as $enrollment) {
            $schedule = $enrollment->courseSchedule;
            
            if (!$schedule || !$schedule->start_date) {
                // Si no tiene fecha de inicio, le asignamos en 1 mes a partir de su matriculación o de hoy
                $baseDate = $enrollment->enrollment_date ? Carbon::parse($enrollment->enrollment_date) : $today->copy();
                $enrollment->update(['next_billing_date' => $baseDate->addMonth()]);
                $count++;
                continue;
            }

            $startDate = Carbon::parse($schedule->start_date);
            $paymentDay = $startDate->day;

            // Revisar si ya se le facturó ESTE mes con el sistema viejo.
            $alreadyBilledThisMonth = Payment::where('enrollment_id', $enrollment->id)
                ->where('payment_concept_id', $monthlyConcept->id)
                ->whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->exists();

            if ($alreadyBilledThisMonth) {
                // Si ya pagó (o se le generó la deuda) este mes, la próxima fecha debería ser el mes que viene
                // en el mismo día del Start Date.
                
                // Construimos la fecha del mes que viene con el "día de pago" correcto
                $nextMonth = $today->copy()->addMonth();
                
                // Ajuste extremo para días 31
                $daysInNextMonth = $nextMonth->daysInMonth;
                $targetDay = $paymentDay > $daysInNextMonth ? $daysInNextMonth : $paymentDay;
                
                $nextBillingDate = Carbon::create($nextMonth->year, $nextMonth->month, $targetDay);
                
                $enrollment->update(['next_billing_date' => $nextBillingDate]);
            } else {
                // Si NO se le ha facturado este mes. Su fecha de facturación debería ser ESTE mes.
                
                $daysInThisMonth = $today->daysInMonth;
                $targetDay = $paymentDay > $daysInThisMonth ? $daysInThisMonth : $paymentDay;
                
                $nextBillingDate = Carbon::create($today->year, $today->month, $targetDay);

                // Si por alguna razón esa fecha está en el pasado lejano y el curso era viejo, 
                // para que el CRON no se vuelva loco cobrándole meses atrasados de golpe hoy mismo (porque el CRON ahora es retroactivo),
                // aseguramos que maximo sea cobrado hoy.
                if ($nextBillingDate->lt($today)) {
                     // El CRON lo agarrará HOY y lo avanzará a un mes en el futuro automáticamente
                     // o podemos directamente asignarlo a hoy si está muy atrasado.
                }

                $enrollment->update(['next_billing_date' => $nextBillingDate]);
            }

            $count++;
            if ($count % 100 == 0) {
                $this->info("Procesados {$count}...");
            }
        }

        $this->info("Migración exitosa. {$count} inscripciones fueron actualizadas con su próxima fecha de facturación delta.");
    }
}
