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
        // 1. TABLA STUDENTS
        Schema::table('students', function (Blueprint $table) {
            $this->createIndexSafely($table, ['first_name', 'last_name'], 'students_first_name_last_name_index');
            $this->createIndexSafely($table, 'cedula', 'students_cedula_index');
            $this->createIndexSafely($table, 'student_code', 'students_student_code_index');
            $this->createIndexSafely($table, ['course_id', 'status'], 'students_course_id_status_index');
        });

        // 2. TABLA ENROLLMENTS (Inscripciones)
        Schema::table('enrollments', function (Blueprint $table) {
            $this->createIndexSafely($table, ['student_id', 'status'], 'enrollments_student_id_status_index');
            $this->createIndexSafely($table, ['course_schedule_id', 'status'], 'enrollments_course_schedule_id_status_index');
            $this->createIndexSafely($table, 'payment_id', 'enrollments_payment_id_index');
        });

        // 3. TABLA PAYMENTS (Pagos)
        Schema::table('payments', function (Blueprint $table) {
            $this->createIndexSafely($table, ['created_at', 'status'], 'payments_created_at_status_index');
            $this->createIndexSafely($table, ['student_id', 'status'], 'payments_student_id_status_index');
            $this->createIndexSafely($table, 'transaction_id', 'payments_transaction_id_index');
            $this->createIndexSafely($table, ['ncf', 'ncf_type'], 'payments_ncf_ncf_type_index');
        });

        // 4. TABLA ADMISSIONS (Admisiones)
        Schema::table('admissions', function (Blueprint $table) {
            $this->createIndexSafely($table, 'status', 'admissions_status_index');
            $this->createIndexSafely($table, 'email', 'admissions_email_index');
            $this->createIndexSafely($table, 'identification_id', 'admissions_identification_id_index');
        });

        // 5. TABLA COURSE_SCHEDULES (Horarios)
        Schema::table('course_schedules', function (Blueprint $table) {
            $this->createIndexSafely($table, ['status', 'start_date', 'end_date'], 'course_schedules_status_start_date_end_date_index');
            $this->createIndexSafely($table, 'teacher_id', 'course_schedules_teacher_id_index');
            $this->createIndexSafely($table, 'module_id', 'course_schedules_module_id_index');
        });
    }

    /**
     * Intenta crear un índice y silencia cualquier error si ya existe.
     * Esta es la forma más robusta de lograr idempotencia en migraciones de índices.
     */
    protected function createIndexSafely(Blueprint $table, $columns, $indexName)
    {
        try {
            $table->index($columns, $indexName);
        } catch (\Exception $e) {
            // Ignoramos el error "index already exists"
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

        foreach ($tables as $table => $indexes) {
            Schema::table($table, function (Blueprint $table) use ($indexes) {
                 foreach ($indexes as $index) {
                     try {
                        $table->dropIndex($index);
                     } catch (\Exception $e) {
                         // Ignoramos si no existe al hacer rollback
                     }
                 }
            });
        }
    }
};