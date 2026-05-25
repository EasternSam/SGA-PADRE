<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Circulares y comunicaciones escolares
        Schema::create('school_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('type', ['circular', 'announcement', 'alert', 'event', 'memo'])->default('announcement');
            $table->enum('priority', ['normal', 'important', 'urgent'])->default('normal');
            $table->enum('audience', ['all', 'teachers', 'parents', 'students', 'staff'])->default('all');
            $table->foreignId('grade_level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('requires_acknowledgment')->default(false);
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index(['academic_year_id', 'is_published', 'publish_date']);
        });

        // Acuses de recibo
        Schema::create('announcement_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('school_announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at');
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_acknowledgments');
        Schema::dropIfExists('school_announcements');
    }
};
