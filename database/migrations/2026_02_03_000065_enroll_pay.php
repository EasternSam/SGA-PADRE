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
        Schema::table('enrollments', function (Blueprint $table) {
            // Campo para agrupar varias materias bajo una sola deuda (Pago)
            // Se usa nullable para mantener compatibilidad con inscripciones antiguas
            $table->foreignId('payment_id')
                  ->nullable()
                  ->after('course_schedule_id') // O after student_id
                  ->constrained('payments')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }
};