<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_period_id')->constrained()->cascadeOnDelete();

            // Calificación numérica MINERD (0-100)
            $table->unsignedDecimal('score', 5, 2)->nullable(); // null = aún no calificado

            // Nivel de desempeño MINERD (auto-calculado)
            $table->enum('performance_level', [
                'destacado',           // 89-100
                'logro_evidenciado',   // 77-88
                'en_proceso',          // 65-76
                'insuficiente',        // <65
            ])->nullable();

            $table->boolean('is_recovery')->default(false);     // ¿Es nota de recuperación?
            $table->boolean('is_extraordinary')->default(false); // ¿Es evaluación extraordinaria?
            $table->text('observations')->nullable();            // Observaciones del docente

            $table->foreignId('recorded_by')   // Profesor que registró la nota
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();

            $table->timestamps();

            // Un estudiante tiene una sola nota por asignatura por período
            $table->unique(['student_id', 'section_subject_id', 'evaluation_period_id', 'is_recovery', 'is_extraordinary'], 'student_grade_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
