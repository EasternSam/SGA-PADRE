<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->string('name');                   // Ej: "A", "B", "C"
            $table->string('full_name')->nullable();  // Ej: "3ro Primaria A" (computed)
            $table->foreignId('homeroom_teacher_id')  // Maestro/a titular
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('classroom_id')         // Aula asignada
                  ->nullable()
                  ->constrained('classrooms')
                  ->nullOnDelete();
            $table->unsignedSmallInteger('capacity')->default(35);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['academic_year_id', 'grade_level_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
