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
            // Helper para verificar existencia (básico)
            // Nota: Si usas SQLite en pruebas, DB::statement con sintaxis MySQL podría fallar si no se controla,
            // por eso el IF es crucial.

            // Índice compuesto nombre completo
            if (! $this->indexExists('students', 'students_first_name_last_name_index')) {
                if ($isMysql) {
                    DB::statement('CREATE INDEX students_first_name_last_name_index ON students (first_name(50), last_name(50))');
                } else {
                    $table->index(['first_name', 'last_name'], 'students_first_name_last_name_index');
                }
            }
            
            // Otros índices simples (usualmente seguros si son de 1 sola columna string < 191, o ints)
            if (! $this->indexExists('students', 'students_cedula_index')) {
                $table->index('cedula', 'students_cedula_index');
            }
            if (! $this->indexExists('students', 'students_student_code_index')) {
                $table->index('student_code', 'students_student_code_index');
            }
            // Índice compuesto course_id (int) + status (string). Status suele ser corto, pero limitamos por seguridad en MySQL
            if (! $this->indexExists('students', 'students_course_id_status_index')) {
                if ($isMysql) {
                    // Asumiendo status varchar(255), lo limitamos a 50
                    DB::statement('CREATE INDEX students_course_id_status_index ON students (course_id, status(50))');
                } else {
                    $table->index(['course_id', 'status'], 'students_course_id_status_index');
                }
            }
        });

        // 2. TABLA ENROLLMENTS
        Schema::table('enrollments', function (Blueprint $table) use ($isMysql) {
            if (! $this->indexExists('enrollments', 'enrollments_student_id_status_index')) {
                if ($isMysql) {
                     DB::statement('CREATE INDEX enrollments_student_id_status_index ON enrollments (student_id, status(50))');
                } else {
                    $table->index(['student_id', 'status'], 'enrollments_student_id_status_index');
                }
            }
            if (! $this->indexExists('enrollments', 'enrollments_course_schedule_id_status_index')) {
                if ($isMysql) {
                     DB::statement('CREATE INDEX enrollments_course_schedule_id_status_index ON enrollments (course_schedule_id, status(50))');
                } else {
                    $table->index(['course_schedule_id', 'status'], 'enrollments_course_schedule_id_status_index');
                }
            }
            if (! $this->indexExists('enrollments', 'enrollments_payment_id_index')) {
                $table->index('payment_id', 'enrollments_payment_id_index');
            }
        });

        // 3. TABLA PAYMENTS
        Schema::table('payments', function (Blueprint $table) use ($isMysql) {
             if (! $this->indexExists('payments', 'payments_created_at_status_index')) {
                if ($isMysql) {
                    DB::statement('CREATE INDEX payments_created_at_status_index ON payments (created_at, status(50))');
                } else {
                    $table->index(['created_at', 'status'], 'payments_created_at_status_index');
                }
             }
             if (! $this->indexExists('payments', 'payments_student_id_status_index')) {
                if ($isMysql) {
                    DB::statement('CREATE INDEX payments_student_id_status_index ON payments (student_id, status(50))');
                } else {
                    $table->index(['student_id', 'status'], 'payments_student_id_status_index');
                }
             }
             if (! $this->indexExists('payments', 'payments_transaction_id_index')) {
                // Transaction ID suele ser string largo, limitamos.
                 if ($isMysql) {
                    DB::statement('CREATE INDEX payments_transaction_id_index ON payments (transaction_id(100))');
                } else {
                    $table->index('transaction_id', 'payments_transaction_id_index');
                }
             }
        });

        // 4. TABLA ADMISSIONS
        // Verifica si la tabla existe primero, ya que es nueva
        if (Schema::hasTable('admissions')) {
            Schema::table('admissions', function (Blueprint $table) use ($isMysql) {
                if (! $this->indexExists('admissions', 'admissions_status_index')) {
                     if ($isMysql) {
                        DB::statement('CREATE INDEX admissions_status_index ON admissions (status(50))');
                    } else {
                        $table->index('status', 'admissions_status_index');
                    }
                }
                if (! $this->indexExists('admissions', 'admissions_email_index')) {
                    if ($isMysql) {
                        DB::statement('CREATE INDEX admissions_email_index ON admissions (email(100))');
                    } else {
                        $table->index('email', 'admissions_email_index');
                    }
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
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        try {
            $indexes = $dbSchemaManager->listTableIndexes($table);
            return array_key_exists($indexName, $indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En el down simplemente intentamos borrar los índices
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
                'payments_transaction_id_index',
                'payments_ncf_ncf_type_index'
            ],
            'admissions' => [
                'admissions_status_index',
                'admissions_email_index',
                'admissions_identification_id_index'
            ]
        ];

        foreach ($tables as $tableName => $indexes) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexes, $tableName) {
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