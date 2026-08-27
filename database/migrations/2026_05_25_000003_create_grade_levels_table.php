<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Ej: "1ro Primaria", "3ro Secundaria"
            $table->string('short_name');           // Ej: "1ro P", "3ro S"
            $table->enum('level', ['inicial', 'primario', 'secundario']);
            $table->unsignedTinyInteger('cycle');    // 1 o 2
            $table->unsignedTinyInteger('grade_number'); // 1-6
            $table->string('modality')->nullable();  // Solo Sec. 2do ciclo: académica, técnico-profesional, artes
            $table->unsignedTinyInteger('min_passing_score')->default(70); // 65 para Primario, 70 Secundario
            $table->unsignedSmallInteger('order')->default(0); // Para ordenamiento global
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['level', 'cycle', 'grade_number', 'modality']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};
