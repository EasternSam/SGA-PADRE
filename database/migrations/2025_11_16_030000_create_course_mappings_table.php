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
        // Esta tabla almacenará el "enlace" entre un curso de Laravel y su ID en WordPress
        Schema::create('course_mappings', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla de cursos de Laravel
            // Asegúrate de que tu tabla de cursos se llame 'courses'
            $table->foreignId('course_id')
                  ->constrained('courses') // Asume que la tabla es 'courses'
                  ->onDelete('cascade') // Si se borra el curso, se borra el enlace
                  ->unique(); // Un curso de Laravel solo puede tener un enlace

            // Datos del curso en WordPress
            $table->unsignedBigInteger('wp_course_id'); // El ID del Post (curso) en WP
            $table->string('wp_course_name'); // El nombre del curso en WP (para mostrar en el admin)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_mappings');
    }
};