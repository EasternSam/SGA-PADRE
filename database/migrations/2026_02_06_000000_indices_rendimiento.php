<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper para verificar si un índice existe en MySQL.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        try {
            // Verifica en la tabla de estadísticas de MySQL
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper para verificar y eliminar índice de forma segura.
     */
    protected function dropIndexIfExists(Blueprint $table, string $tableName, string $indexName)
    {
        if ($this->indexExists($tableName, $indexName)) {
            $table->dropIndex($indexName);
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TABLA STUDENTS
        Schema::table('students', function (Blueprint $table) {
            if (!$this->indexExists('students', 'students_first_name_last_name_index')) {
                $table->index(['first_name', 'last_name'], 'students_first_name_last_name_index');
            }
            if (!$this->indexExists('students', 'students_cedula_index')) {
                $table->index('cedula', 'students_cedula_index');
            }
            if (!$this->indexExists('students', 'students_student_code_index')) {
                $table->index('student_code', 'students_student_code_index');
            }
            if (!$this->indexExists('students', 'students_course_id_status_index')) {
                $table->index(['course_id', 'status'], 'students_course_id_status_index');
            }
        });

        // 2. TABLA ENROLLMENTS (Inscripciones)
        Schema::table('enrollments', function (Blueprint $table) {
            if (!$this->indexExists('enrollments', 'enrollments_student_id_status_index')) {
                $table->index(['student_id', 'status'], 'enrollments_student_id_status_index');
            }
            if (!$this->indexExists('enrollments', 'enrollments_course_schedule_id_status_index')) {
                $table->index(['course_schedule_id', 'status'], 'enrollments_course_schedule_id_status_index');
            }
            if (!$this->indexExists('enrollments', 'enrollments_payment_id_index')) {
                $table->index('payment_id', 'enrollments_payment_id_index');
            }
        });

        // 3. TABLA PAYMENTS (Pagos)
        Schema::table('payments', function (Blueprint $table) {
            if (!$this->indexExists('payments', 'payments_created_at_status_index')) {
                $table->index(['created_at', 'status'], 'payments_created_at_status_index');
            }
            if (!$this->indexExists('payments', 'payments_student_id_status_index')) {
                $table->index(['student_id', 'status'], 'payments_student_id_status_index');
            }
            if (!$this->indexExists('payments', 'payments_transaction_id_index')) {
                $table->index('transaction_id', 'payments_transaction_id_index');
            }
            if (!$this->indexExists('payments', 'payments_ncf_ncf_type_index')) {
                $table->index(['ncf', 'ncf_type'], 'payments_ncf_ncf_type_index');
            }
        });

        // 4. TABLA ADMISSIONS (Admisiones)
        Schema::table('admissions', function (Blueprint $table) {
            if (!$this->indexExists('admissions', 'admissions_status_index')) {
                $table->index('status', 'admissions_status_index');
            }
            if (!$this->indexExists('admissions', 'admissions_email_index')) {
                $table->index('email', 'admissions_email_index');
            }
            if (!$this->indexExists('admissions', 'admissions_identification_id_index')) {
                $table->index('identification_id', 'admissions_identification_id_index');
            }
        });

        // 5. TABLA COURSE_SCHEDULES (Horarios)
        Schema::table('course_schedules', function (Blueprint $table) {
            if (!$this->indexExists('course_schedules', 'course_schedules_status_start_date_end_date_index')) {
                $table->index(['status', 'start_date', 'end_date'], 'course_schedules_status_start_date_end_date_index');
            }
            if (!$this->indexExists('course_schedules', 'course_schedules_teacher_id_index')) {
                $table->index('teacher_id', 'course_schedules_teacher_id_index');
            }
            if (!$this->indexExists('course_schedules', 'course_schedules_module_id_index')) {
                $table->index('module_id', 'course_schedules_module_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // También protegemos el rollback verificando si existen antes de borrar
        Schema::table('students', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'students', 'students_first_name_last_name_index');
            $this->dropIndexIfExists($table, 'students', 'students_cedula_index');
            $this->dropIndexIfExists($table, 'students', 'students_student_code_index');
            $this->dropIndexIfExists($table, 'students', 'students_course_id_status_index');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'enrollments', 'enrollments_student_id_status_index');
            $this->dropIndexIfExists($table, 'enrollments', 'enrollments_course_schedule_id_status_index');
            $this->dropIndexIfExists($table, 'enrollments', 'enrollments_payment_id_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'payments', 'payments_created_at_status_index');
            $this->dropIndexIfExists($table, 'payments', 'payments_student_id_status_index');
            $this->dropIndexIfExists($table, 'payments', 'payments_transaction_id_index');
            $this->dropIndexIfExists($table, 'payments', 'payments_ncf_ncf_type_index');
        });

        Schema::table('admissions', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'admissions', 'admissions_status_index');
            $this->dropIndexIfExists($table, 'admissions', 'admissions_email_index');
            $this->dropIndexIfExists($table, 'admissions', 'admissions_identification_id_index');
        });

        Schema::table('course_schedules', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'course_schedules', 'course_schedules_status_start_date_end_date_index');
            $this->dropIndexIfExists($table, 'course_schedules', 'course_schedules_teacher_id_index');
            $this->dropIndexIfExists($table, 'course_schedules', 'course_schedules_module_id_index');
        });
    }
};