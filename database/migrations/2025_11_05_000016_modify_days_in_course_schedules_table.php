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
            // 1. Eliminar la columna antigua que solo permitía un día
            $table->dropColumn('day_of_week');
            
            // 2. Añadir una nueva columna JSON para almacenar un array de días
            $table->json('days_of_week')->nullable()->after('module_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            // 1. Revertir el cambio: eliminar la columna JSON
            $table->dropColumn('days_of_week');

            // 2. Re-añadir la columna de string original
            $table->string('day_of_week')->nullable()->after('module_id');
        });
    }
};