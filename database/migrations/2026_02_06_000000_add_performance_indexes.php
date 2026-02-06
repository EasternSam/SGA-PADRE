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

        if ($driver === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return count($result) > 0;
        }

        // Para MySQL/MariaDB y otros
        // Laravel suele prefijar los índices. Si no usamos un nombre manual, Laravel genera uno.
        // Aquí asumimos que los nombres pasados son los que Laravel generaría o los manuales.
        // Una forma segura en MySQL es intentar y capturar, pero para verificar:
        if ($driver === 'mysql') {
            $dbName = $conn->getDatabaseName();
            $result = DB::select("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?", [$dbName, $table, $indexName]);
            return count($result) > 0;
        }
        
        // Fallback: intentar usar Doctrine si está disponible, o asumir false para intentar crear
        try {
            return $conn->getDoctrineSchemaManager()->listTableIndexes($table)[$indexName] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nombres de índices estándar que Laravel genera: table_column_index
        
        // 1. Optimización tabla Students
        Schema::table('students', function (Blueprint $table) {
            if (!$this->hasIndex('students', 'students_student_code_index')) {
                $table->index('student_code', 'students_student_code_index');
            }
            if (!$this->hasIndex('students', 'students_user_id_index')) {
                $table->index('user_id', 'students_user_id_index');
            }
            if (!$this->hasIndex('students', 'students_first_name_last_name_index')) {
                $table->index(['first_name', 'last_name'], 'students_first_name_last_name_index');
            }
        });

        // 2. Optimización tabla Enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            if (!$this->hasIndex('enrollments', 'enrollments_student_id_status_index')) {
                $table->index(['student_id', 'status'], 'enrollments_student_id_status_index');
            }
            if (!$this->hasIndex('enrollments', 'enrollments_course_schedule_id_index')) {
                $table->index('course_schedule_id', 'enrollments_course_schedule_id_index');
            }
            if (!$this->hasIndex('enrollments', 'enrollments_payment_id_index')) {
                $table->index('payment_id', 'enrollments_payment_id_index');
            }
        });

        // 3. Optimización tabla Payments
        Schema::table('payments', function (Blueprint $table) {
            if (!$this->hasIndex('payments', 'payments_student_id_status_index')) {
                $table->index(['student_id', 'status'], 'payments_student_id_status_index');
            }
            if (!$this->hasIndex('payments', 'payments_created_at_index')) {
                $table->index('created_at', 'payments_created_at_index');
            }
            if (!$this->hasIndex('payments', 'payments_due_date_index')) {
                $table->index('due_date', 'payments_due_date_index');
            }
            if (!$this->hasIndex('payments', 'payments_ncf_index')) {
                $table->index('ncf', 'payments_ncf_index');
            }
        });

        // 4. Optimización tabla Course Schedules
        Schema::table('course_schedules', function (Blueprint $table) {
            if (!$this->hasIndex('course_schedules', 'course_schedules_module_id_status_index')) {
                $table->index(['module_id', 'status'], 'course_schedules_module_id_status_index');
            }
            if (!$this->hasIndex('course_schedules', 'course_schedules_teacher_id_index')) {
                $table->index('teacher_id', 'course_schedules_teacher_id_index');
            }
        });
        
        // 5. Optimización tabla Users
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_name_index')) {
                $table->index('name', 'users_name_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En rollback simplemente intentamos borrar. Si no existe, no pasa nada grave en la mayoría de drivers o podemos capturar.
        
        $tables = [
            'students' => ['students_student_code_index', 'students_user_id_index', 'students_first_name_last_name_index'],
            'enrollments' => ['enrollments_student_id_status_index', 'enrollments_course_schedule_id_index', 'enrollments_payment_id_index'],
            'payments' => ['payments_student_id_status_index', 'payments_created_at_index', 'payments_due_date_index', 'payments_ncf_index'],
            'course_schedules' => ['course_schedules_module_id_status_index', 'course_schedules_teacher_id_index'],
            'users' => ['users_name_index']
        ];

        foreach ($tables as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexes) {
                foreach ($indexes as $index) {
                    // Solo intentamos borrar si existe para evitar errores en rollback
                    if ($this->hasIndex($tableName, $index)) {
                        $table->dropIndex($index);
                    }
                }
            });
        }
    }
};