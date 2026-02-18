<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tuvimos que renombrar '...create_course_schedules_table' a '...create_sections_table'
// Así que creamos este nuevo archivo para los Módulos.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Esta tabla es el "módulo" (ej. Excel, Word, Inglés Básico)
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            
            // Un módulo pertenece a un curso padre
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            
            $table->string('name'); // Ej: "Excel Avanzado"
            $table->string('code')->unique()->nullable(); // Ej: "INF-101"
            $table->text('description')->nullable();

            // --- AGREGADO: Columna 'order' necesaria para el Curriculum ---
            $table->integer('order')->default(0); 
            
            $table->decimal('price', 10, 2)->default(0); // El precio está en el módulo
            $table->integer('duration_hours')->nullable(); // Duración total
            
            $table->string('status')->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};