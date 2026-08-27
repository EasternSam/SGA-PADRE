<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')           // Profesor de la asignatura en esta sección
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->json('schedule')->nullable();      // {"lunes": "8:00-9:30", "miércoles": "8:00-9:30"}
            $table->timestamps();

            $table->unique(['section_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_subjects');
    }
};
