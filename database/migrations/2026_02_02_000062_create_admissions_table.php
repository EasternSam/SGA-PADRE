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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            
            // Datos Personales
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('identification_id')->nullable(); // Cédula/Pasaporte
            $table->date('birth_date')->nullable();
            
            // Datos Académicos
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // Carrera de interés
            $table->string('previous_school')->nullable();
            $table->float('previous_gpa')->nullable(); // Promedio anterior

            // Estado del Proceso
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};