<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Justificaciones de ausencia
        Schema::create('absence_justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->enum('reason', ['medical', 'family', 'travel', 'appointment', 'other'])->default('medical');
            $table->text('description')->nullable();
            $table->string('document_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Reinscripción masiva tracking
        Schema::create('reinscription_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('to_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->integer('total_students')->default(0);
            $table->integer('processed')->default(0);
            $table->integer('promoted')->default(0);
            $table->integer('retained')->default(0);
            $table->integer('excluded')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reinscription_batches');
        Schema::dropIfExists('absence_justifications');
    }
};
