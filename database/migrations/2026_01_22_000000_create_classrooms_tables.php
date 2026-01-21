<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Edificios
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Edificio A
            $table->timestamps();
        });

        // 2. Tabla de Aulas/Laboratorios
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->string('name'); // Ej: Lab. 01, Aula 303
            $table->integer('capacity')->default(0);
            $table->integer('pc_count')->default(0); // Cantidad de computadoras
            $table->string('type')->default('Aula'); // Aula, Laboratorio, etc.
            $table->text('equipment')->nullable(); // JSON o texto: TV, Proyector...
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Modificación a Horarios (Relación)
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->foreignId('classroom_id')->nullable()->after('module_id')->constrained('classrooms')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropColumn('classroom_id');
        });
        Schema::dropIfExists('classrooms');
        Schema::dropIfExists('buildings');
    }
};