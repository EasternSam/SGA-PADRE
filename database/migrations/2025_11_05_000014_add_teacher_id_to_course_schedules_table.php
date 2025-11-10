<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Esta migración añade la columna 'teacher_id' que faltaba
     * para vincular un profesor (usuario) a una sección.
     */
    public function up(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            // Añadir la columna teacher_id que será la llave foránea
            $table->foreignId('teacher_id')
                  ->nullable() // Puede ser nulo si una sección no tiene profesor asignado
                  ->after('module_id') // Colocarla después del module_id
                  ->constrained('users') // Apunta a la tabla 'users'
                  ->onDelete('set null'); // Si se borra el usuario/profesor, la sección no se borra, solo queda sin asignar.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            // Eliminar la llave foránea y la columna
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};