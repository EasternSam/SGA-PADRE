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
        Schema::table('student_requests', function (Blueprint $table) {
            // 1. Añadimos request_type_id solo si no existe
            if (!Schema::hasColumn('student_requests', 'request_type_id')) {
                $table->foreignId('request_type_id')->nullable()->after('student_id')->constrained('request_types');
            }
            
            // 2. Añadimos course_id solo si no existe
            if (!Schema::hasColumn('student_requests', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('request_type_id')->constrained('courses')->onDelete('set null');
            }
            
            // 3. Añadimos payment_id solo si no existe
            if (!Schema::hasColumn('student_requests', 'payment_id')) {
                $table->foreignId('payment_id')->nullable()->after('course_id')->constrained('payments')->onDelete('set null');
            }
            
            // 4. Modificamos details para ser nullable (esto siempre es seguro ejecutar)
            $table->text('details')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_requests', function (Blueprint $table) {
            if (Schema::hasColumn('student_requests', 'request_type_id')) {
                // Primero intentamos botar la foránea por convención estándar de Laravel
                // tabla_columna_foreign
                try {
                    $table->dropForeign(['request_type_id']);
                } catch (\Exception $e) {
                    // Si falla porque tiene otro nombre, ignoramos en desarrollo
                }
                $table->dropColumn('request_type_id');
            }
            
            if (Schema::hasColumn('student_requests', 'course_id')) {
                try {
                    $table->dropForeign(['course_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('course_id');
            }
            
            if (Schema::hasColumn('student_requests', 'payment_id')) {
                try {
                    $table->dropForeign(['payment_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('payment_id');
            }
        });
    }
};