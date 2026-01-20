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
        Schema::table('student_requests', function (Blueprint $table) {
            // Verificamos si la columna existe antes de agregarla para evitar errores si ya se intentó correr
            if (!Schema::hasColumn('student_requests', 'course_id')) {
                // Agregamos course_id después de student_id
                $table->foreignId('course_id')
                      ->nullable()
                      ->after('student_id')
                      ->constrained('courses')
                      ->onDelete('cascade'); // Si se borra el curso, se borran las solicitudes relacionadas
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_requests', function (Blueprint $table) {
            if (Schema::hasColumn('student_requests', 'course_id')) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            }
        });
    }
};