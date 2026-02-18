<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        $isMysql = ($driver === 'mysql' || $driver === 'mariadb');

        // 1. TABLA STUDENTS
        Schema::table('students', function (Blueprint $table) use ($isMysql) {
            
            // Índice compuesto nombre completo
            if (! $this->indexExists('students', 'students_first_name_last_name_index')) {
                // En MySQL moderno, generalmente no es necesario especificar la longitud para índices normales de string
                // a menos que sean BLOB/TEXT muy largos. Para VARCHARs típicos (191/255), el índice completo está bien.
                $table->index(['first_name', 'last_name'], 'students_first_name_last_name_index');
            }
            
            if (! $this->indexExists('students', 'students_cedula_index')) {
                $table->index('cedula', 'students_cedula_index');
            }
            if (! $this->indexExists('students', 'students_student_code_index')) {
                $table->index('student_code', 'students_student_code_index');
            }
            
            // Índice compuesto course_id (INT) + status (STRING)
            if (! $this->indexExists('students', 'students_course_id_status_index')) {
                // Eliminamos la restricción de longitud (20) que causaba el error 1089
                $table->index(['course_id', 'status'], 'students_course_id_status_index');
            }
        });

        // 2. TABLA ENROLLMENTS
        Schema::table('enrollments', function (Blueprint $table) use ($isMysql) {
            if (! $this->indexExists('enrollments', 'enrollments_student_id_status_index')) {
                $table->index(['student_id', 'status'], 'enrollments_student_id_status_index');
            }
            if (! $this->indexExists('enrollments', 'enrollments_course_schedule_id_status_index')) {
                $table->index(['course_schedule_id', 'status'], 'enrollments_course_schedule_id_status_index');
            }
            if (! $this->indexExists('enrollments', 'enrollments_payment_id_index')) {
                $table->index('payment_id', 'enrollments_payment_id_index');
            }
        });

        // 3. TABLA PAYMENTS
        Schema::table('payments', function (Blueprint $table) use ($isMysql) {
             if (! $this->indexExists('payments', 'payments_created_at_status_index')) {
                $table->index(['created_at', 'status'], 'payments_created_at_status_index');
             }
             if (! $this->indexExists('payments', 'payments_student_id_status_index')) {
                $table->index(['student_id', 'status'], 'payments_student_id_status_index');
             }
             if (! $this->indexExists('payments', 'payments_transaction_id_index')) {
                $table->index('transaction_id', 'payments_transaction_id_index');
             }
        });

        // 4. TABLA ADMISSIONS
        if (Schema::hasTable('admissions')) {
            Schema::table('admissions', function (Blueprint $table) use ($isMysql) {
                if (! $this->indexExists('admissions', 'admissions_status_index')) {
                    // Corrección principal: quitamos status(20) que causaba el error "Incorrect prefix key"
                    $table->index('status', 'admissions_status_index');
                }
                if (! $this->indexExists('admissions', 'admissions_email_index')) {
                    $table->index('email', 'admissions_email_index');
                }
            });
        }
    }

    /**
     * Helper simple para verificar índices en MySQL/SQLite
     */
    protected function indexExists($table, $indexName)
    {
        $conn = Schema::getConnection();
        
        try {
            // Intento 1: Doctrine
            if (method_exists($conn, 'getDoctrineSchemaManager')) {
                $dbSchemaManager = $conn->getDoctrineSchemaManager();
                $indexes = $dbSchemaManager->listTableIndexes($table);
                return array_key_exists($indexName, $indexes);
            }
            
            // Intento 2: Consulta SQL directa
            $driver = $conn->getDriverName();
            if ($driver === 'mysql' || $driver === 'mariadb') {
                $dbName = $conn->getDatabaseName();
                $result = DB::select("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?", [$dbName, $table, $indexName]);
                return count($result) > 0;
            } else if ($driver === 'sqlite') {
                 $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
                 return count($result) > 0;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'students' => [
                'students_first_name_last_name_index',
                'students_cedula_index',
                'students_student_code_index',
                'students_course_id_status_index'
            ],
            'enrollments' => [
                'enrollments_student_id_status_index',
                'enrollments_course_schedule_id_status_index',
                'enrollments_payment_id_index'
            ],
            'payments' => [
                'payments_created_at_status_index',
                'payments_student_id_status_index',
                'payments_transaction_id_index'
            ],
            'admissions' => [
                'admissions_status_index',
                'admissions_email_index'
            ]
        ];

        foreach ($tables as $tableName => $indexes) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexes) {
                     foreach ($indexes as $index) {
                         try {
                             $table->dropIndex($index);
                         } catch (\Exception $e) {}
                     }
                });
            }
        }
    }
};