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
        Schema::table('course_schedules', function (Blueprint $table) {
            // Agregamos la columna 'modality' después de 'section_name' (si existe) o 'status'
            // Le ponemos un valor por defecto para no romper registros existentes.
            $table->string('modality')->default('Presencial')->after('status'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dropColumn('modality');
        });
    }
};