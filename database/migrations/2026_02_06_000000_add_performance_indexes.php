<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper para verificar si un índice existe de forma segura.
     */
    protected function hasIndex($table, $indexName)
    {
        $conn = Schema::getConnection();
        $driver = $conn->getDriverName();

        // Intento directo vía SQL para evitar dependencias de Doctrine a veces fallidas en migraciones
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $dbName = $conn->getDatabaseName();
            $result = DB::select("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?", [$dbName, $table, $indexName]);
            return count($result) > 0;
        }
        
        // Fallback para SQLite
        if ($driver === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return count($result) > 0;
        }

        return false;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isMysql = (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb');

        // USERS: Índice en nombre (string 255 puede fallar)
        Schema::table('users', function (Blueprint $table) use ($isMysql) {
            if (!$this->hasIndex('users', 'users_name_index')) {
                if ($isMysql) {
                    DB::statement('CREATE INDEX users_name_index ON users (name(50))');
                } else {
                    $table->index('name', 'users_name_index');
                }
            }
        });

        // STUDENTS: (Solo si no fueron creados por la otra migración)
        Schema::table('students', function (Blueprint $table) use ($isMysql) {
            if (!$this->hasIndex('students', 'students_student_code_index')) {
                $table->index('student_code', 'students_student_code_index');
            }
            // Este es el compuesto crítico
            if (!$this->hasIndex('students', 'students_first_name_last_name_index')) {
                if ($isMysql) {
                    DB::statement('CREATE INDEX students_first_name_last_name_index ON students (first_name(50), last_name(50))');
                } else {
                    $table->index(['first_name', 'last_name'], 'students_first_name_last_name_index');
                }
            }
        });
        
        // PAYMENTs, ENROLLMENTS, ETC... (Ya cubiertos en la otra, pero por si acaso)
        // La lógica de verificación hasIndex evita duplicados y errores.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users' => ['users_name_index'],
            'students' => ['students_student_code_index', 'students_first_name_last_name_index'],
        ];

        foreach ($tables as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexes) {
                foreach ($indexes as $index) {
                    if ($this->hasIndex($tableName, $index)) {
                        $table->dropIndex($index);
                    }
                }
            });
        }
    }
};