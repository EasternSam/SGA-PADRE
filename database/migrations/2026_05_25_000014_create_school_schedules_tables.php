<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bloques horarios (configuración de la tanda)
        Schema::create('time_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Ej: "1er Bloque", "Recreo", "Almuerzo"
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['class', 'break', 'lunch', 'assembly'])->default('class');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Horario escolar (asignación de asignatura a bloque/día/sección)
        Schema::create('school_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_block_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('classroom_name')->nullable();
            $table->enum('day_of_week', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes']); 
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['section_id', 'time_block_id', 'day_of_week'], 'schedule_unique');
            $table->index(['teacher_id', 'day_of_week', 'time_block_id'], 'teacher_schedule_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_schedules');
        Schema::dropIfExists('time_blocks');
    }
};
