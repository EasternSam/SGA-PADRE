<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración de Índices Comprensiva para Rendimiento Máximo.
 *
 * Auditoría realizada sobre TODAS las tablas del sistema.
 * Solo se agregan índices que NO existen previamente.
 *
 * Tablas ya indexadas por migraciones anteriores:
 *   - students: cedula, student_code, email, first_name, last_name (composite), course_id+status
 *   - enrollments: student_id+status, course_schedule_id+status, payment_id
 *   - payments: created_at+status, student_id+status, transaction_id
 *   - admissions: status, email
 *   - activity_logs: user_id, created_at, action, ip_address, user_id+created_at
 *   - users: name
 *   - employee_attendances: biometric_id
 *
 * Esta migración agrega los índices FALTANTES en las demás tablas.
 */
return new class extends Migration
{
    /**
     * Helper para verificar si un índice existe (compatible MySQL/SQLite).
     */
    protected function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        try {
            if ($driver === 'sqlite') {
                return count(DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName])) > 0;
            }
            if ($driver === 'mysql' || $driver === 'mariadb') {
                $dbName = Schema::getConnection()->getDatabaseName();
                return count(DB::select("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?", [$dbName, $table, $indexName])) > 0;
            }
        } catch (\Exception $e) {}

        return false;
    }

    /**
     * Helper seguro: agrega índice solo si no existe.
     */
    protected function safeIndex(string $table, $columns, string $indexName): void
    {
        if ($this->hasIndex($table, $indexName)) return;
        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // Silenciar si el índice ya existe por otro nombre
        }
    }

    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════
        // 1. COURSE_SCHEDULES — Tabla MUY consultada (secciones)
        //    FKs: module_id, professor_id (ya tienen FK constraint pero NO siempre índice en SQLite)
        //    Filtros comunes: status, start_date, end_date
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('course_schedules', 'module_id',     'cs_module_id_idx');
        $this->safeIndex('course_schedules', 'professor_id',  'cs_professor_id_idx');
        $this->safeIndex('course_schedules', 'status',        'cs_status_idx');
        $this->safeIndex('course_schedules', ['status', 'start_date'], 'cs_status_start_idx');
        $this->safeIndex('course_schedules', ['module_id', 'status'],  'cs_module_status_idx');

        // ══════════════════════════════════════════════════════════════
        // 2. MODULES — FK: course_id, filtros: status, code
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('modules', 'course_id', 'modules_course_id_idx');
        $this->safeIndex('modules', 'status',    'modules_status_idx');
        $this->safeIndex('modules', ['course_id', 'order'], 'modules_course_order_idx');

        // ══════════════════════════════════════════════════════════════
        // 3. ATTENDANCES — Consultas por fecha, enrollment, schedule
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('attendances', 'course_schedule_id',  'att_schedule_id_idx');
        $this->safeIndex('attendances', 'attendance_date',     'att_date_idx');
        $this->safeIndex('attendances', ['course_schedule_id', 'attendance_date'], 'att_schedule_date_idx');
        $this->safeIndex('attendances', 'status', 'att_status_idx');

        // ══════════════════════════════════════════════════════════════
        // 4. CALL_LOGS — FKs sin índice explícito
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('call_logs', 'enrollment_id', 'cl_enrollment_id_idx');
        $this->safeIndex('call_logs', 'agent_id',      'cl_agent_id_idx');
        $this->safeIndex('call_logs', 'student_id',    'cl_student_id_idx');
        $this->safeIndex('call_logs', 'status',        'cl_status_idx');

        // ══════════════════════════════════════════════════════════════
        // 5. PAYMENTS — Índices adicionales a los existentes
        //    Filtros comunes adicionales: gateway, payment_concept_id, enrollment_id
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('payments', 'enrollment_id',       'pay_enrollment_id_idx');
        $this->safeIndex('payments', 'payment_concept_id',  'pay_concept_id_idx');
        $this->safeIndex('payments', 'gateway',             'pay_gateway_idx');
        $this->safeIndex('payments', 'status',              'pay_status_idx');

        // ══════════════════════════════════════════════════════════════
        // 6. STUDENT_REQUESTS — FK + filtros de estado
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('student_requests')) {
            $this->safeIndex('student_requests', 'student_id', 'sr_student_id_idx');
            $this->safeIndex('student_requests', 'status',     'sr_status_idx');
            $this->safeIndex('student_requests', 'type',       'sr_type_idx');
            $this->safeIndex('student_requests', ['student_id', 'status'], 'sr_student_status_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 7. CLASSROOM_RESERVATIONS — Consultas por fecha y aula
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('classroom_reservations')) {
            $this->safeIndex('classroom_reservations', 'classroom_id',   'cr_classroom_id_idx');
            $this->safeIndex('classroom_reservations', 'reserved_date',  'cr_reserved_date_idx');
            $this->safeIndex('classroom_reservations', ['classroom_id', 'reserved_date'], 'cr_classroom_date_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 8. ACCOUNTING — Contabilidad necesita velocidad en reportes
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('accounting_accounts', 'parent_id',  'acct_parent_id_idx');
        $this->safeIndex('accounting_accounts', 'type',       'acct_type_idx');
        $this->safeIndex('accounting_accounts', 'is_active',  'acct_active_idx');

        if (Schema::hasTable('accounting_entries')) {
            $this->safeIndex('accounting_entries', 'accounting_journal_id', 'ae_journal_id_idx');
            $this->safeIndex('accounting_entries', 'date',                  'ae_date_idx');
            $this->safeIndex('accounting_entries', 'status',                'ae_status_idx');
            $this->safeIndex('accounting_entries', ['reference_type', 'reference_id'], 'ae_reference_idx');
        }

        if (Schema::hasTable('accounting_entry_lines')) {
            $this->safeIndex('accounting_entry_lines', 'accounting_entry_id',   'ael_entry_id_idx');
            $this->safeIndex('accounting_entry_lines', 'accounting_account_id', 'ael_account_id_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 9. EXPENSES — Reportes financieros
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('expenses')) {
            $this->safeIndex('expenses', 'expense_date',      'exp_date_idx');
            $this->safeIndex('expenses', 'status',            'exp_status_idx');
            $this->safeIndex('expenses', 'expense_account_id','exp_expense_acct_idx');
            $this->safeIndex('expenses', 'payment_account_id','exp_payment_acct_idx');
            $this->safeIndex('expenses', ['expense_date', 'status'], 'exp_date_status_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 10. SUPPLIERS
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('suppliers')) {
            $this->safeIndex('suppliers', 'rnc', 'sup_rnc_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 11. EMPLOYEES — FK + filtros
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('employees')) {
            $this->safeIndex('employees', 'user_id',    'emp_user_id_idx');
            $this->safeIndex('employees', 'status',     'emp_status_idx');
            $this->safeIndex('employees', 'department', 'emp_department_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 12. EMPLOYEE_ATTENDANCES — punch_time es clave para reportes
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('employee_attendances')) {
            $this->safeIndex('employee_attendances', 'punch_time', 'ea_punch_time_idx');
            $this->safeIndex('employee_attendances', ['biometric_id', 'punch_time'], 'ea_bio_punch_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 13. EMPLOYEE_EVENTS
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('employee_events')) {
            $this->safeIndex('employee_events', 'employee_id', 'ee_employee_id_idx');
            $this->safeIndex('employee_events', 'type',        'ee_type_idx');
            $this->safeIndex('employee_events', 'event_date',  'ee_event_date_idx');
            $this->safeIndex('employee_events', ['employee_id', 'type'], 'ee_emp_type_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 14. PAYROLLS + PAYROLL_ITEMS
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('payrolls')) {
            $this->safeIndex('payrolls', 'status', 'pr_status_idx');
        }

        if (Schema::hasTable('payroll_items')) {
            $this->safeIndex('payroll_items', 'payroll_id',  'pi_payroll_id_idx');
            $this->safeIndex('payroll_items', 'employee_id', 'pi_employee_id_idx');
            $this->safeIndex('payroll_items', ['payroll_id', 'employee_id'], 'pi_payroll_emp_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 15. INVENTORY_ITEMS
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('inventory_items')) {
            $this->safeIndex('inventory_items', 'category',     'inv_category_idx');
            $this->safeIndex('inventory_items', 'status',       'inv_status_idx');
            $this->safeIndex('inventory_items', 'classroom_id', 'inv_classroom_id_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 16. ENROLLMENTS — Índices adicionales para queries del portal
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('enrollments', 'status',   'enr_status_idx');
        $this->safeIndex('enrollments', 'created_at', 'enr_created_at_idx');

        // ══════════════════════════════════════════════════════════════
        // 17. STUDENTS — user_id (FK sin índice explícito)
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('students', 'user_id',  'std_user_id_idx');
        $this->safeIndex('students', 'status',   'std_status_idx');

        // ══════════════════════════════════════════════════════════════
        // 18. NCF_SEQUENCES — Lookups fiscales
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('ncf_sequences')) {
            $this->safeIndex('ncf_sequences', 'type', 'ncf_type_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 19. ADMISSIONS — Filtros adicionales
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('admissions')) {
            $this->safeIndex('admissions', 'created_at', 'adm_created_at_idx');
            $this->safeIndex('admissions', 'cedula',     'adm_cedula_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 20. ACADEMIC_EVENTS — Calendario
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('academic_events')) {
            $this->safeIndex('academic_events', 'start_date', 'aev_start_date_idx');
            $this->safeIndex('academic_events', 'end_date',   'aev_end_date_idx');
            $this->safeIndex('academic_events', 'type',       'aev_type_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 21. USERS — email ya es unique (tiene índice), 
        //     pero agreguemos uno para login por matrícula si se hace
        // ══════════════════════════════════════════════════════════════
        $this->safeIndex('users', 'kiosk_pin', 'users_kiosk_pin_idx');

        // ══════════════════════════════════════════════════════════════
        // 22. CERTIFICATE_TEMPLATES
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('certificate_templates')) {
            $this->safeIndex('certificate_templates', 'is_active', 'ct_active_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 23. MODULE_PREREQUISITES
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('module_prerequisites')) {
            $this->safeIndex('module_prerequisites', 'module_id',       'mp_module_id_idx');
            $this->safeIndex('module_prerequisites', 'prerequisite_id', 'mp_prereq_id_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 24. SCHOLARSHIPS
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('scholarships')) {
            $this->safeIndex('scholarships', 'is_active', 'sch_active_idx');
        }

        // ══════════════════════════════════════════════════════════════
        // 25. NOTIFICATIONS (Laravel)
        // ══════════════════════════════════════════════════════════════
        if (Schema::hasTable('notifications')) {
            $this->safeIndex('notifications', 'read_at', 'notif_read_at_idx');
        }
    }

    public function down(): void
    {
        $indexes = [
            'course_schedules'       => ['cs_module_id_idx','cs_professor_id_idx','cs_status_idx','cs_status_start_idx','cs_module_status_idx'],
            'modules'                => ['modules_course_id_idx','modules_status_idx','modules_course_order_idx'],
            'attendances'            => ['att_schedule_id_idx','att_date_idx','att_schedule_date_idx','att_status_idx'],
            'call_logs'              => ['cl_enrollment_id_idx','cl_agent_id_idx','cl_student_id_idx','cl_status_idx'],
            'payments'               => ['pay_enrollment_id_idx','pay_concept_id_idx','pay_gateway_idx','pay_status_idx'],
            'student_requests'       => ['sr_student_id_idx','sr_status_idx','sr_type_idx','sr_student_status_idx'],
            'classroom_reservations' => ['cr_classroom_id_idx','cr_reserved_date_idx','cr_classroom_date_idx'],
            'accounting_accounts'    => ['acct_parent_id_idx','acct_type_idx','acct_active_idx'],
            'accounting_entries'     => ['ae_journal_id_idx','ae_date_idx','ae_status_idx','ae_reference_idx'],
            'accounting_entry_lines' => ['ael_entry_id_idx','ael_account_id_idx'],
            'expenses'               => ['exp_date_idx','exp_status_idx','exp_expense_acct_idx','exp_payment_acct_idx','exp_date_status_idx'],
            'suppliers'              => ['sup_rnc_idx'],
            'employees'              => ['emp_user_id_idx','emp_status_idx','emp_department_idx'],
            'employee_attendances'   => ['ea_punch_time_idx','ea_bio_punch_idx'],
            'employee_events'        => ['ee_employee_id_idx','ee_type_idx','ee_event_date_idx','ee_emp_type_idx'],
            'payrolls'               => ['pr_status_idx'],
            'payroll_items'          => ['pi_payroll_id_idx','pi_employee_id_idx','pi_payroll_emp_idx'],
            'inventory_items'        => ['inv_category_idx','inv_status_idx','inv_classroom_id_idx'],
            'enrollments'            => ['enr_status_idx','enr_created_at_idx'],
            'students'               => ['std_user_id_idx','std_status_idx'],
            'ncf_sequences'          => ['ncf_type_idx'],
            'admissions'             => ['adm_created_at_idx','adm_cedula_idx'],
            'academic_events'        => ['aev_start_date_idx','aev_end_date_idx','aev_type_idx'],
            'users'                  => ['users_kiosk_pin_idx'],
            'certificate_templates'  => ['ct_active_idx'],
            'module_prerequisites'   => ['mp_module_id_idx','mp_prereq_id_idx'],
            'scholarships'           => ['sch_active_idx'],
            'notifications'          => ['notif_read_at_idx'],
        ];

        foreach ($indexes as $table => $idxList) {
            if (!Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $t) use ($table, $idxList) {
                foreach ($idxList as $idx) {
                    if ($this->hasIndex($table, $idx)) {
                        try { $t->dropIndex($idx); } catch (\Exception $e) {}
                    }
                }
            });
        }
    }
};
