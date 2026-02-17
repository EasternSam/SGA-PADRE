<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Importar DB

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
            // CORRECCIÓN: Limitar la longitud de las columnas en el índice para MySQL
            // Laravel permite especificar la longitud en el array del índice
            // Esto evita el error "Specified key was too long"
            
            // Verificamos si es MySQL o MariaDB para aplicar la limitación de longitud
            $driver = DB::getDriverName();

            if ($driver === 'mysql' || $driver === 'mariadb') {
                // Para MySQL/MariaDB usamos DB::raw para especificar la longitud del índice
                // O intentamos la sintaxis de array soportada por versiones recientes de Laravel si es posible,
                // pero DB::raw es más seguro para compatibilidad.
                // Sin embargo, Laravel tiene un método index() que acepta raw expressions.
                
                // Opción más compatible con migraciones: Crear el índice con raw SQL solo para MySQL
                DB::statement('CREATE INDEX idx_students_name_composite ON students (last_name(50), first_name(50))');
            } else {
                // Para otros motores (como SQLite o PostgreSQL) que manejan esto diferente o no tienen el mismo límite por defecto
                $table->index(['last_name', 'first_name'], 'idx_students_name_composite');
            }
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