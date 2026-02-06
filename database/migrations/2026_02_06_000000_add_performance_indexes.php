<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper para verificar si un índice existe (SQLite/MySQL compatible)
     */
    protected function indexExists($table, $indexName)
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $indexes = $dbSchemaManager->listTableIndexes($table);

        return array_key_exists($indexName, $indexes);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Optimización tabla Students
        Schema::table('students', function (Blueprint $table) {
            // Verificar antes de crear para evitar error "index already exists"
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('students');

            if (!array_key_exists('students_student_code_index', $indexes)) {
                $table->index('student_code');
            }
            if (!array_key_exists('students_user_id_index', $indexes)) {
                $table->index('user_id');
            }
            if (!array_key_exists('students_first_name_last_name_index', $indexes)) {
                $table->index(['first_name', 'last_name']);
            }
        });

        // 2. Optimización tabla Enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('enrollments');

            if (!array_key_exists('enrollments_student_id_status_index', $indexes)) {
                $table->index(['student_id', 'status']);
            }
            if (!array_key_exists('enrollments_course_schedule_id_index', $indexes)) {
                $table->index('course_schedule_id');
            }
            if (!array_key_exists('enrollments_payment_id_index', $indexes)) {
                $table->index('payment_id');
            }
        });

        // 3. Optimización tabla Payments
        Schema::table('payments', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('payments');

            if (!array_key_exists('payments_student_id_status_index', $indexes)) {
                $table->index(['student_id', 'status']);
            }
            if (!array_key_exists('payments_created_at_index', $indexes)) {
                $table->index('created_at');
            }
            if (!array_key_exists('payments_due_date_index', $indexes)) {
                $table->index('due_date');
            }
            if (!array_key_exists('payments_ncf_index', $indexes)) {
                $table->index('ncf');
            }
        });

        // 4. Optimización tabla Course Schedules
        Schema::table('course_schedules', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('course_schedules');

            if (!array_key_exists('course_schedules_module_id_status_index', $indexes)) {
                $table->index(['module_id', 'status']);
            }
            if (!array_key_exists('course_schedules_teacher_id_index', $indexes)) {
                $table->index('teacher_id');
            }
        });
        
        // 5. Optimización tabla Users
        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('users');

            if (!array_key_exists('users_name_index', $indexes)) {
                $table->index('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En el rollback, usamos dropIndex con un array, Laravel maneja la verificación interna mejor,
        // pero por seguridad envolvemos en try-catch si es necesario, aunque dropIndex suele ser seguro si existe.
        
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['student_code']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['first_name', 'last_name']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'status']);
            $table->dropIndex(['course_schedule_id']);
            $table->dropIndex(['payment_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['ncf']);
        });

        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dropIndex(['module_id', 'status']);
            $table->dropIndex(['teacher_id']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};