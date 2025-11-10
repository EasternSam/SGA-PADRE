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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('course_schedule_id')->constrained('course_schedules')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['Presente', 'Ausente', 'Tardanza', 'Justificado']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Clave única para evitar duplicados por día
            $table->unique(['enrollment_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};