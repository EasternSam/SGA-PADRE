<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Attendance;
use Carbon\Carbon;

class MassiveDataSeeder extends Seeder
{
    /**
     * Llena la base de datos con datos de prueba realistas vinculando
     * Estudiantes con Módulos, Pagos y Asistencias.
     */
    public function run(): void
    {
        $students = Student::all();
        $schedules = CourseSchedule::all();
        
        // --- 1. Crear Conceptos de Pago Base si no existen ---
        $conceptInscripcion = PaymentConcept::firstOrCreate(
            ['code' => 'INSC'],
            ['name' => 'Inscripción General', 'amount' => 1500, 'type' => 'ingreso']
        );
        
        $conceptCuota = PaymentConcept::firstOrCreate(
            ['code' => 'MENS'],
            ['name' => 'Cuota Mensual', 'amount' => 2500, 'type' => 'ingreso']
        );

        if ($students->isEmpty() || $schedules->isEmpty()) {
            $this->command->error("Faltan Estudiantes o Secciones. Ejecuta php artisan db:seed --class=DemoDataSeeder primero.");
            return;
        }

        $this->command->info("Iniciando Inserción Masiva de Datos Transaccionales...");
        $this->command->getOutput()->progressStart($students->count());

        foreach ($students as $student) {
            // Inscribir a cada estudiante en 1 a 3 cursos aleatorios
            $randomSchedules = $schedules->random(rand(1, 3));

            foreach ($randomSchedules as $schedule) {
                // Generar Fechas Ficticias (Hace 1 a 6 meses)
                $enrollmentDate = Carbon::now()->subMonths(rand(1, 6))->subDays(rand(1, 28));

                // 1. Crear la Inscripción
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Cursando',
                    'next_billing_date' => $enrollmentDate->copy()->addMonth(), // Siguiente mes
                ]);

                // Manipular el created_at directamente
                $enrollment->created_at = $enrollmentDate;
                $enrollment->saveQuietly(); // saveQuietly para evitar triggers si los hay

                // 2. Crear Pago de Inscripción (Pagado)
                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $conceptInscripcion->id,
                    'amount' => $conceptInscripcion->amount,
                    'currency' => 'DOP',
                    'status' => 'Pagado',
                    'gateway' => 'Cash',
                    'due_date' => $enrollmentDate,
                    'created_at' => $enrollmentDate,
                    'updated_at' => $enrollmentDate,
                ]);

                // 3. Crear Historial de Pagos Mensuales (Algunos pagados, otros pendientes)
                $monthsElapsed = $enrollmentDate->diffInMonths(Carbon::now());
                
                for ($m = 1; $m <= $monthsElapsed; $m++) {
                    $dueDate = $enrollmentDate->copy()->addMonths($m);
                    
                    // 20% probabilidad de estar Atrasado/Pendiente
                    $isPaid = rand(1, 100) > 20;

                    Payment::create([
                        'student_id' => $student->id,
                        'enrollment_id' => $enrollment->id,
                        'payment_concept_id' => $conceptCuota->id,
                        'amount' => $conceptCuota->amount,
                        'currency' => 'DOP',
                        'status' => $isPaid ? 'Pagado' : 'Pendiente',
                        'gateway' => $isPaid ? ['CardNet', 'Transferencia', 'Cash'][rand(0, 2)] : null,
                        'due_date' => $dueDate,
                        'created_at' => $dueDate->copy()->subDays(5), // Factura emitida 5 días antes
                        'updated_at' => $isPaid ? $dueDate->copy()->addDays(rand(0, 3)) : $dueDate->copy()->subDays(5),
                    ]);
                }

                // 4. Crear Asistencias (Últimos 10 días de clase)
                for ($d = 0; $d < 10; $d++) {
                    $classDate = Carbon::now()->subDays($d * 3); // Simular clases cada 3 días
                    
                    Attendance::create([
                        'student_id' => $student->id,
                        'course_schedule_id' => $schedule->id,
                        'date' => $classDate->toDateString(),
                        'status' => ['presente', 'presente', 'presente', 'ausente', 'tarde'][rand(0, 4)], // 60% presente
                        'observations' => rand(1, 10) > 8 ? 'Llegó muy tarde' : null,
                    ]);
                }
            }

            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info("¡Datos Masivos Inyectados Exitosamente!");
    }
}
