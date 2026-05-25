<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();

            // Asistencia del período
            $table->unsignedSmallInteger('days_attended')->default(0);
            $table->unsignedSmallInteger('days_absent')->default(0);
            $table->unsignedSmallInteger('days_late')->default(0);
            $table->unsignedSmallInteger('total_school_days')->default(0);

            // Observaciones generales
            $table->text('teacher_observations')->nullable();
            $table->text('counselor_observations')->nullable();

            // Conducta / Comportamiento
            $table->enum('conduct', ['excelente', 'bueno', 'satisfactorio', 'necesita_mejorar'])->nullable();

            // Control de entrega
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->boolean('parent_signature')->default(false);
            $table->string('pdf_path')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'evaluation_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
