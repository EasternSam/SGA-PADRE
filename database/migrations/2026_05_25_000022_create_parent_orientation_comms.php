<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Parent portal access tokens
        Schema::create('parent_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('pin', 6)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'is_active']);
        });

        // Orientation/Psychology records
        Schema::create('orientation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['interview', 'observation', 'referral', 'followup', 'psychological', 'family', 'academic'])->default('interview');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'referred'])->default('open');
            $table->date('next_followup')->nullable();
            $table->foreignId('counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_confidential')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        // Communication/Notification log
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['whatsapp', 'email', 'sms', 'push', 'internal'])->default('internal');
            $table->enum('type', ['individual', 'section', 'grade', 'all'])->default('individual');
            $table->string('subject');
            $table->text('body');
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('recipients_count')->default(0);
            $table->enum('status', ['draft', 'sent', 'failed'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('orientation_records');
        Schema::dropIfExists('parent_access_tokens');
    }
};
