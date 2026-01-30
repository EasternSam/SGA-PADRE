<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classroom_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->string('title'); // Motivo: "Conferencia", "Mantenimiento", etc.
            $table->text('description')->nullable();
            $table->date('reserved_date'); // Fecha específica (ej: 2026-02-14)
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Evitar duplicar reservas superpuestas (a nivel de BD o lógica)
            // Aquí dejamos la flexibilidad para validar en lógica
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_reservations');
    }
};