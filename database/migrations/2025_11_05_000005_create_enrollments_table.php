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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            // Un estudiante se inscribe en una SECCIÓN (horario)
            $table->foreignId('course_schedule_id')->constrained('course_schedules')->onDelete('cascade');
            
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            // Campos para el expediente
            $table->string('status')->default('Cursando'); // Cursando, Completado, Retirado
            $table->decimal('final_grade', 5, 2)->nullable(); // Calificación final
            $table->text('observations')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};