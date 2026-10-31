<?php

namespace Database\Seeders;

use App\Models\PaymentConcept;
use App\Models\Student;
use Illuminate\Database\Seeder;

/**
 * Registros de referencia del POS. Idempotente: puede ejecutarse varias veces.
 */
class PosSeeder extends Seeder
{
    public function run(): void
    {
        PaymentConcept::firstOrCreate(
            ['name' => config('pos.open_concept_name')],
            [
                'description' => 'Concepto abierto: el cajero indica descripción y precio.',
                'amount' => 0,
                'default_amount' => 0,
                'is_fixed_amount' => false,
            ]
        );

        // Sin eventos: esta ficha es interna y no debe sincronizarse a WordPress
        // ni disparar correos de bienvenida.
        Student::withoutEvents(fn () => Student::updateOrCreate(
            ['student_code' => config('pos.walk_in_student_code')],
            [
                'first_name' => 'Cliente General',
                'last_name' => '(Público)',
                // Marcador que no aparenta ser una cédula real (la columna es NOT NULL y única).
                'cedula' => config('pos.walk_in_student_code'),
                'email' => null,
                'status' => 'Activo',
            ]
        ));
    }
}
