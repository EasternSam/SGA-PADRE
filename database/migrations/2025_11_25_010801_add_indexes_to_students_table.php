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
        Schema::table('students', function (Blueprint $table) {
            // Índices individuales para búsquedas exactas rápidas
            $table->index('cedula', 'idx_students_cedula');
            $table->index('student_code', 'idx_students_code'); // Asumiendo que usas student_code o matricula
            $table->index('email', 'idx_students_email');
            
            // Índice para mejorar el ordenamiento por nombre si lo usas
            $table->index('first_name', 'idx_students_firstname');

            // Índice compuesto para búsquedas por nombre completo (muy efectivo para 'LIKE')
            $table->index(['last_name', 'first_name'], 'idx_students_name_composite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_cedula');
            $table->dropIndex('idx_students_code');
            $table->dropIndex('idx_students_email');
            $table->dropIndex('idx_students_firstname');
            $table->dropIndex('idx_students_name_composite');
        });
    }
};