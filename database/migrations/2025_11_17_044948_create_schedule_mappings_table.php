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
        Schema::create('schedule_mappings', function (Blueprint $table) {
            $table->id();
            
            // El string exacto del formulario de WP, ej: "Sábado a las 9:00AM - 12:00PM (Presencial)"
            $table->string('wp_schedule_string')->unique(); 
            
            // El ID del horario en la tabla course_schedules de Laravel
            $table->foreignId('course_schedule_id')->constrained('course_schedules')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_mappings');
    }
};