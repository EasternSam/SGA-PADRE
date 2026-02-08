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
        // 1. TABLA STUDENTS
        Schema::table('students', function (Blueprint $table) {
            if (! $this->indexExists('students', 'students_first_name_last_name_index')) {
                $table->index(['first_name', 'last_name'], 'students_first_name_last_name_index');
            }
            if (! $this->indexExists('students', 'students_cedula_index')) {
                $table->index('cedula', 'students_cedula_index');
            }
            if (! $this->indexExists('students', 'students_student_code_index')) {
                $table->index('student_code', 'students_student_code_index');
            }
            if (! $this->indexExists('students', 'students_course_id_status_index')) {
                $table->index(['course_id', 'status'], 'students_course_id_status_index');
            }
        });

        // 2. TABLA ENROLLMENTS (Inscripciones)
        Schema::table('enrollments', function (Blueprint $table) {
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

        // 3. TABLA PAYMENTS (Pagos)
        Schema::table('payments', function (Blueprint $table) {
            if (! $this->indexExists('payments', 'payments_created_at_status_index')) {
                $table->index(['created_at', 'status'], 'payments_created_at_status_index');
            }
            if (! $this->indexExists('payments', 'payments_student_id_status_index')) {
                $table->index(['student_id', 'status'], 'payments_student_id_status_index');
            }
            if (! $this->indexExists('payments', 'payments_transaction_id_index')) {
                $table->index('transaction_id', 'payments_transaction_id_index');
            }
            if (! $this->indexExists('payments', 'payments_ncf_ncf_type_index')) {
                $table->index(['ncf', 'ncf_type'], 'payments_ncf_ncf_type_index');
            }
        });

        // 4. TABLA ADMISSIONS (Admisiones)
        Schema::table('admissions', function (Blueprint $table) {
            if (! $this->indexExists('admissions', 'admissions_status_index')) {
                $table->index('status', 'admissions_status_index');
            }
            if (! $this->indexExists('admissions', 'admissions_email_index')) {
                $table->index('email', 'admissions_email_index');
            }
            if (! $this->indexExists('admissions', 'admissions_identification_id_index')) {
                $table->index('identification_id', 'admissions_identification_id_index');
            }
        });

        // 5. TABLA COURSE_SCHEDULES (Horarios)
        Schema::table('course_schedules', function (Blueprint $table) {
            if (! $this->indexExists('course_schedules', 'course_schedules_status_start_date_end_date_index')) {
                $table->index(['status', 'start_date', 'end_date'], 'course_schedules_status_start_date_end_date_index');
            }
            if (! $this->indexExists('course_schedules', 'course_schedules_teacher_id_index')) {
                $table->index('teacher_id', 'course_schedules_teacher_id_index');
            }
            if (! $this->indexExists('course_schedules', 'course_schedules_module_id_index')) {
                $table->index('module_id', 'course_schedules_module_id_index');
            }
        });
    }

    /**
     * Verifica si un índice existe, compatible con SQLite y MySQL sin depender de Doctrine.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        // 1. Método nativo de Laravel (si funciona)
        try {
            if (Schema::hasIndex($table, $indexName)) {
                return true;
            }
        } catch (\Exception $e) {
            // Si falla (ej: falta doctrine/dbal), usamos fallback manual abajo
        }

        // 2. Fallback manual para SQLite
        if ($driver === 'sqlite') {
            // En SQLite los nombres de índice son globales en la base de datos
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name = ?", [$indexName]);
            return count($result) > 0;
        }

        // 3. Fallback manual para MySQL/MariaDB
        if ($driver === 'mysql') {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        }

        // 4. Fallback para PostgreSQL
        if ($driver === 'pgsql') {
            $result = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
            return count($result) > 0;
        }

        return false;
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
                'payments_transaction_id_index',
                'payments_ncf_ncf_type_index'
            ],
            'admissions' => [
                'admissions_status_index',
                'admissions_email_index',
                'admissions_identification_id_index'
            ],
            'course_schedules' => [
                'course_schedules_status_start_date_end_date_index',
                'course_schedules_teacher_id_index',
                'course_schedules_module_id_index'
            ]
        ];

        foreach ($tables as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($indexes, $tableName) {
                 foreach ($indexes as $index) {
                     // Usamos el mismo helper para verificar antes de borrar
                     if ($this->indexExists($tableName, $index)) {
                        $table->dropIndex($index);
                     }
                 }
            });
        }
    }
};