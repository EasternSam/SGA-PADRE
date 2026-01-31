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
        Schema::table('courses', function (Blueprint $table) {
            // Tipo de programa: 'technical' (Instituto - por defecto) o 'degree' (Universidad)
            $table->string('program_type')->default('technical')->after('code'); 
            
            // Campos específicos para Universidad
            $table->integer('total_credits')->default(0)->after('program_type'); // Total créditos de la carrera
            $table->integer('duration_periods')->nullable()->after('total_credits'); // Cantidad de cuatrimestres/semestres
            $table->string('degree_title')->nullable()->after('duration_periods'); // Título a otorgar (ej: Licenciado en...)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['program_type', 'total_credits', 'duration_periods', 'degree_title']);
        });
    }
};