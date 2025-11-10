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
            // Añadimos la columna 'section_name' que falta en la tabla de horarios
            // La migración ...000016... debió hacer esto, pero la forzamos aquí.
            $table->string('section_name')->nullable()->after('module_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dropColumn('section_name');
        });
    }
};