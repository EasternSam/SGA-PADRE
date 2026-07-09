<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('degree_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->onDelete('cascade');
            $table->string('pace')->default('5'); // 4, 5, o 6 materias por semestre
            $table->string('status')->default('active');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('planned_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('degree_plan_id')->constrained('degree_plans')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->integer('target_period'); // Periodo proyectado (ej. Semestre 3)
            $table->string('status')->default('planned'); // planned, in_progress, completed
            $table->timestamps();

            $table->unique(['degree_plan_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planned_modules');
        Schema::dropIfExists('degree_plans');
    }
};
