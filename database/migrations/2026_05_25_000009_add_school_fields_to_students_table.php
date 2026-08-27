<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Relaciones escolares
            $table->foreignId('grade_level_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->after('grade_level_id')->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->after('section_id')->constrained()->nullOnDelete();
            $table->date('enrollment_date')->nullable()->after('academic_year_id');

            // Datos médicos / emergencia
            $table->string('blood_type', 5)->nullable()->after('birth_date');
            $table->text('allergies')->nullable()->after('blood_type');
            $table->text('medical_conditions')->nullable()->after('allergies');
            $table->string('emergency_contact_name')->nullable()->after('medical_conditions');
            $table->string('emergency_contact_phone', 20)->nullable()->after('emergency_contact_name');

            // Historial escolar
            $table->string('previous_school')->nullable()->after('emergency_contact_phone');
            $table->json('documents_status')->nullable()->after('previous_school'); // {"acta_nacimiento": true, "foto": false, ...}

            // Índices
            $table->index('grade_level_id');
            $table->index('section_id');
            $table->index('academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['grade_level_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn([
                'grade_level_id', 'section_id', 'academic_year_id', 'enrollment_date',
                'blood_type', 'allergies', 'medical_conditions',
                'emergency_contact_name', 'emergency_contact_phone',
                'previous_school', 'documents_status',
            ]);
        });
    }
};
