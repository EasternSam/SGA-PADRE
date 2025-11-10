<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renombramos esto mentalmente a "Secciones"
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();

            // Una sección es de un MÓDULO (ej. "Excel Avanzado")
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            
            // Una sección tiene un PROFESOR (usamos 'professor_id' para claridad, apuntando a 'users')
            $table->foreignId('professor_id')->nullable()->constrained('users')->onDelete('set null');

            // $table->json('days'); // ['Lunes', 'Miércoles'] <-- ESTA LÍNEA CAUSA EL CONFLICTO. LA COMENTAMOS.
                                     // La columna correcta 'days_of_week' se añade en la migración ...0016.
            
            $table->time('start_time');
            $table->time('end_time');
            $table->date('start_date'); // Fecha de inicio del curso
            $table->date('end_date'); // Fecha de fin del curso
            $table->string('room')->nullable(); // Aula
            $table->integer('capacity')->default(20);
            $table->string('status')->default('Programada'); // Programada, Abierta, Cerrada, Cancelada

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_schedules');
    }
};