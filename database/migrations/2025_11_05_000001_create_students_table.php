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
        // Reemplaza el CPT `estudiante`
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // --- Campos de la imagen ---
            $table->string('first_name'); // Nombre(s)
            $table->string('last_name'); // Apellido(s)
            $table->string('cedula')->unique(); // meta: cedula
            $table->string('email')->nullable(); // meta: email
            $table->string('home_phone')->nullable(); // Teléfono
            $table->string('mobile_phone')->nullable(); // Celular
            $table->text('address')->nullable(); // meta: direccion
            $table->string('city')->nullable(); // Ciudad
            $table->string('sector')->nullable(); // Sector
            $table->date('birth_date')->nullable(); // Fecha de Nacimiento
            $table->string('gender')->nullable(); // Sexo
            $table->string('nationality')->nullable(); // Nacionalidad
            $table->string('how_found')->nullable(); // Cómo se Entero?
            $table->boolean('is_minor')->default(false); // Menor de Edad?
            $table->string('status')->default('Activa'); // Activa

            // Campo de sincronización con WordPress
            $table->unsignedBigInteger('wp_student_post_id')->nullable()->index();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};