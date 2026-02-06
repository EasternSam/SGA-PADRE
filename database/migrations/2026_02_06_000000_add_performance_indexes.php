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
        // 1. Optimización tabla Students
        // Acelera el buscador global y los modals de pago
        Schema::table('students', function (Blueprint $table) {
            $table->index('student_code'); 
            $table->index('user_id');
            // Índice compuesto para búsquedas por nombre completo
            $table->index(['first_name', 'last_name']);
        });

        // 2. Optimización tabla Enrollments
        // CRÍTICO para el Dashboard (filtra por status y estudiante miles de veces)
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index(['student_id', 'status']); 
            $table->index('course_schedule_id');
            $table->index('payment_id'); // Para la relación inversa rápida
        });

        // 3. Optimización tabla Payments
        // Acelera el historial de pagos y reportes financieros
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['student_id', 'status']);
            $table->index('created_at'); // Para filtros de fecha en reportes
            $table->index('due_date');   // Para detectar mora rápidamente
            $table->index('ncf');        // Para búsquedas fiscales
        });

        // 4. Optimización tabla Course Schedules
        // Acelera la carga del detalle del curso
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->index(['module_id', 'status']);
            $table->index('teacher_id');
        });
        
        // 5. Optimización tabla Users
        // Acelera el login y búsqueda por email
        Schema::table('users', function (Blueprint $table) {
            // email ya suele ser unique (indexado), pero si agregamos búsqueda por nombre:
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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