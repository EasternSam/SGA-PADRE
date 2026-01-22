<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ncf_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Factura de Crédito Fiscal Electrónica
            $table->string('type_code', 2); // Ej: 31, 32, 41, 43
            $table->string('series', 1)->default('E'); // E para electrónicos
            $table->bigInteger('current_sequence')->default(0); // Último número usado
            $table->bigInteger('limit_sequence'); // Límite autorizado por DGII
            $table->date('expiration_date'); // Fecha vencimiento secuencia
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insertar datos iniciales de prueba (Seed ligero)
        DB::table('ncf_sequences')->insert([
            [
                'name' => 'Crédito Fiscal (e-CF)',
                'type_code' => '31',
                'series' => 'E',
                'current_sequence' => 0,
                'limit_sequence' => 1000,
                'expiration_date' => '2026-12-31',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Consumo (e-CF)',
                'type_code' => '32',
                'series' => 'E',
                'current_sequence' => 0,
                'limit_sequence' => 5000,
                'expiration_date' => '2026-12-31',
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ncf_sequences');
    }
};