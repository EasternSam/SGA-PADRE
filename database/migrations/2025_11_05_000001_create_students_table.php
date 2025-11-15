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

            // --- INICIO DE CAMPOS FUSIONADOS ---
            
            // Columna de User (de tu archivo ...012_add_user_id...)
            // Se quitó ->after('id')
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // --- ¡¡LA COLUMNA QUE FALTABA!! ---
            // (Requerida por MatriculaService)
            // Se quitó ->after('user_id')
            $table->string('student_code')->nullable()->unique();
            
            // --- FIN DE CAMPOS FUSIONADOS ---

            // --- Campos de la imagen (de tu archivo ...001...) ---
            $table->string('first_name'); // Nombre(s)
            $table->string('last_name'); // Apellido(s)
            $table->string('cedula')->unique(); // meta: cedula
            $table->string('email')->nullable(); // meta: email
            
            // --- Campos de teléfono (fusionados de ...001 y ...013) ---
            $table->string('home_phone')->nullable(); // Teléfono (de ...001)
            $table->string('mobile_phone')->nullable(); // Celular (de ...001)
            // $table->string('phone', 20)->nullable(); // (Este 'phone' de ...013 era redundante, usamos los 2 de arriba)

            $table->text('address')->nullable(); // meta: direccion
            $table->string('city')->nullable(); // Ciudad
            $table->string('sector')->nullable(); // Sector
            $table->date('birth_date')->nullable(); // Fecha de Nacimiento
            $table->string('gender', 20)->nullable(); // Sexo (longitud aumentada de ...013)
            $table->string('nationality')->nullable(); // Nacionalidad
            $table->string('how_found')->nullable(); // Cómo se Entero?
            $table->boolean('is_minor')->default(false); // Menor de Edad?

            // --- INICIO DE CAMPOS FUSIONADOS (de tu archivo ...013...) ---
            $table->string('tutor_name')->nullable();
            $table->string('tutor_cedula', 20)->nullable();
            $table->string('tutor_phone', 20)->nullable();
            $table->string('tutor_relationship', 50)->nullable();
            // --- FIN DE CAMPOS FUSIONADOS ---

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