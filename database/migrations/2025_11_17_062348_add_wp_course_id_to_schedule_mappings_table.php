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
        Schema::table('schedule_mappings', function (Blueprint $table) {
            // Añadimos la columna que falta
            $table->unsignedBigInteger('wp_course_id')->after('course_schedule_id')->nullable();
            // Añadimos un índice para que las búsquedas sean más rápidas
            $table->index('wp_course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_mappings', function (Blueprint $table) {
            // Esto permite deshacer el cambio si es necesario
            $table->dropColumn('wp_course_id');
        });
    }
};