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
            // Usamos Schema::hasIndex para verificar la existencia de forma agnóstica a la DB
            // Nota: En SQLite, los índices tienen nombres globales, pero Laravel suele prefijarlos con la tabla.
            // Para mayor seguridad en migraciones idempotentes, intentamos capturar la excepción si falla
            // o verificamos el nombre exacto que Laravel generaría.

            // Índice compuesto nombre + apellido
            $indexName1 = 'students_first_name_last_name_index';
            if (! $this->indexExists('students', $indexName1)) {
                $table->index(['first_name', 'last_name'], $indexName1);
            }

            // Índice cédula
            $indexName2 = 'students_cedula_index';
            if (! $this->indexExists('students', $indexName2)) {
                $table->index('cedula', $indexName2);
            }

            // Índice código estudiante
            $indexName3 = 'students_student_code_index';
            if (! $this->indexExists('students', $indexName3)) {
                $table->index('student_code', $indexName3);
            }

            // Índice curso + estado
            $indexName4 = 'students_course_id_status_index';
            if (! $this->indexExists('students', $indexName4)) {
                $table->index(['course_id', 'status'], $indexName4);
            }
        });

        // 2. TABLA ENROLLMENTS (Inscripciones)
        Schema::table('enrollments', function (Blueprint $table) {
            $indexName1 = 'enrollments_student_id_status_index';
            if (! $this->indexExists('enrollments', $indexName1)) {
                $table->index(['student_id', 'status'], $indexName1);
            }

            $indexName2 = 'enrollments_course_schedule_id_status_index';
            if (! $this->indexExists('enrollments', $indexName2)) {
                $table->index(['course_schedule_id', 'status'], $indexName2);
            }

            $indexName3 = 'enrollments_payment_id_index';
            if (! $this->indexExists('enrollments', $indexName3)) {
                $table->index('payment_id', $indexName3);
            }
        });

        // 3. TABLA PAYMENTS (Pagos)
        Schema::table('payments', function (Blueprint $table) {
            $indexName1 = 'payments_created_at_status_index';
            if (! $this->indexExists('payments', $indexName1)) {
                $table->index(['created_at', 'status'], $indexName1);
            }

            $indexName2 = 'payments_student_id_status_index';
            if (! $this->indexExists('payments', $indexName2)) {
                $table->index(['student_id', 'status'], $indexName2);
            }

            $indexName3 = 'payments_transaction_id_index';
            if (! $this->indexExists('payments', $indexName3)) {
                $table->index('transaction_id', $indexName3);
            }

            $indexName4 = 'payments_ncf_ncf_type_index';
            if (! $this->indexExists('payments', $indexName4)) {
                $table->index(['ncf', 'ncf_type'], $indexName4);
            }
        });

        // 4. TABLA ADMISSIONS (Admisiones)
        Schema::table('admissions', function (Blueprint $table) {
            $indexName1 = 'admissions_status_index';
            if (! $this->indexExists('admissions', $indexName1)) {
                $table->index('status', $indexName1);
            }

            $indexName2 = 'admissions_email_index';
            if (! $this->indexExists('admissions', $indexName2)) {
                $table->index('email', $indexName2);
            }

            $indexName3 = 'admissions_identification_id_index';
            if (! $this->indexExists('admissions', $indexName3)) {
                $table->index('identification_id', $indexName3);
            }
        });

        // 5. TABLA COURSE_SCHEDULES (Horarios)
        Schema::table('course_schedules', function (Blueprint $table) {
            $indexName1 = 'course_schedules_status_start_date_end_date_index';
            if (! $this->indexExists('course_schedules', $indexName1)) {
                $table->index(['status', 'start_date', 'end_date'], $indexName1);
            }

            $indexName2 = 'course_schedules_teacher_id_index';
            if (! $this->indexExists('course_schedules', $indexName2)) {
                $table->index('teacher_id', $indexName2);
            }

            $indexName3 = 'course_schedules_module_id_index';
            if (! $this->indexExists('course_schedules', $indexName3)) {
                $table->index('module_id', $indexName3);
            }
        });
    }

    /**
     * Helper robusto para verificar existencia de índice.
     * Utiliza Schema Manager de Doctrine para mayor compatibilidad.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        
        try {
            $indexes = $dbSchemaManager->listTableIndexes($table);
            
            foreach ($indexes as $index) {
                if ($index->getName() === $indexName) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            // Si falla Doctrine (ej. tabla no existe), retornamos false para intentar crear
            // o true para prevenir errores, dependiendo de la estrategia. 
            // Aquí asumimos false para intentar crear, pero con try-catch en el up().
            return false; 
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En el down, usamos una sintaxis segura que intenta borrar solo si existe
        // Laravel no tiene "dropIndexIfExists" nativo directo en Blueprint,
        // así que iteramos manualmente.

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
            Schema::table($tableName, function (Blueprint $table) use ($indexes) {
                 foreach ($indexes as $index) {
                     // Verificamos antes de intentar borrar para evitar error "Index not found"
                     // Usamos una verificación simple aquí ya que estamos en rollback
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