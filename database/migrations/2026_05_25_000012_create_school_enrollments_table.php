<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('enrollment_code')->unique()->nullable();
            $table->enum('status', ['pending', 'approved', 'enrolled', 'transferred_out', 'withdrawn', 'graduated'])->default('pending');
            $table->enum('enrollment_type', ['new', 'renewal', 'transfer'])->default('new');
            $table->date('enrollment_date')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->string('withdrawal_reason')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('transfer_certificate')->nullable();

            // Documentos checklist
            $table->boolean('doc_birth_certificate')->default(false);
            $table->boolean('doc_photos')->default(false);
            $table->boolean('doc_grades_record')->default(false);
            $table->boolean('doc_medical_certificate')->default(false);
            $table->boolean('doc_vaccination_card')->default(false);
            $table->boolean('doc_parent_id')->default(false);
            $table->boolean('doc_report_card')->default(false);
            $table->boolean('doc_good_conduct')->default(false);

            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_enrollments');
    }
};
