<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: 'Solicitud de Diploma', 'Retiro de Materia'
            $table->text('description')->nullable();
            
            // Lógica Condicional
            $table->boolean('requires_payment')->default(false);
            $table->decimal('payment_amount', 10, 2)->default(0); // Monto a cobrar si aplica
            $table->boolean('requires_enrolled_course')->default(false); // Requiere seleccionar una materia cursando
            $table->boolean('requires_completed_course')->default(false); // Requiere seleccionar una materia aprobada
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Insertar datos semilla básicos
        DB::table('request_types')->insert([
            [
                'name' => 'Solicitud de Diploma',
                'description' => 'Emisión de diploma de graduación.',
                'requires_payment' => true,
                'payment_amount' => 500.00,
                'requires_enrolled_course' => false,
                'requires_completed_course' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Retiro de Materia',
                'description' => 'Retiro formal de una asignatura en curso.',
                'requires_payment' => false,
                'payment_amount' => 0,
                'requires_enrolled_course' => true,
                'requires_completed_course' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Certificado de Notas',
                'description' => 'Historial académico oficial.',
                'requires_payment' => true,
                'payment_amount' => 150.00,
                'requires_enrolled_course' => false,
                'requires_completed_course' => false,
                'is_active' => true,
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('request_types');
    }
};