<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * CORRECCIÓN: Esta migración AÑADE la columna 'day_of_week'
     * que faltaba en la tabla 'course_schedules'.
     * La migración anterior (con el mismo timestamp) intentaba
     * renombrarla erróneamente.
     */
    public function up(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            // Añadir la columna que faltaba
            // La colocamos después de 'teacher_id' (creada en la migración ...014)
            $table->string('day_of_week', 50)->nullable()->after('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dropColumn('day_of_week');
        });
    }
};