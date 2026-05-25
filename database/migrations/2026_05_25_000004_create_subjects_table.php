<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // Ej: "Lengua Española", "Biología"
            $table->string('code')->unique();        // Ej: "LE", "MAT", "BIO"
            $table->string('area');                   // Área curricular MINERD
            $table->boolean('is_core')->default(true); // Obligatoria vs electiva
            $table->unsignedTinyInteger('weekly_hours')->default(4); // Horas semanales
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot: qué asignaturas van en qué grado
        Schema::create('grade_level_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekly_hours')->nullable(); // Override por grado
            $table->timestamps();

            $table->unique(['grade_level_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_level_subject');
        Schema::dropIfExists('subjects');
    }
};
