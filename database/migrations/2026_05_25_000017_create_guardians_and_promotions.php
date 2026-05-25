<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Padres / Tutores
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('cedula', 13)->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('phone_alt')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->enum('relationship', ['padre', 'madre', 'tutor', 'abuelo', 'abuela', 'tio', 'tia', 'otro'])->default('padre');
            $table->string('occupation')->nullable();
            $table->string('workplace')->nullable();
            $table->boolean('is_emergency_contact')->default(true);
            $table->timestamps();
        });

        // Relación estudiante ↔ tutor (many-to-many)
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['guardian_id', 'student_id']);
        });

        // Historial de promoción / repitencia
        Schema::create('promotion_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('result', ['promoted', 'retained', 'transferred', 'withdrawn', 'graduated'])->default('promoted');
            $table->decimal('final_average', 5, 2)->nullable();
            $table->text('observations')->nullable();
            $table->date('decision_date')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id'], 'promo_student_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_records');
        Schema::dropIfExists('guardian_student');
        Schema::dropIfExists('guardians');
    }
};
