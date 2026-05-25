<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_config', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('minerd_code')->nullable()->comment('Código del centro educativo MINERD');
            $table->string('rnc')->nullable()->comment('RNC del centro');
            $table->string('regional')->nullable()->comment('Regional educativa (01-18)');
            $table->string('district')->nullable()->comment('Distrito educativo');
            $table->enum('shift', ['matutina', 'vespertina', 'jornada_extendida', 'nocturna'])->default('matutina');
            $table->enum('school_type', ['publico', 'privado', 'semioficial'])->default('privado');
            $table->enum('level', ['inicial', 'primario', 'secundario', 'primario_secundario'])->default('primario_secundario');
            $table->string('director_name')->nullable();
            $table->string('director_cedula')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('motto')->nullable()->comment('Lema del centro');
            $table->json('extra_config')->nullable();
            $table->timestamps();
        });

        // Calendario escolar MINERD
        Schema::create('school_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['school_day', 'holiday', 'teacher_day', 'exam_day', 'event', 'vacation', 'makeup_day'])->default('school_day');
            $table->string('name')->nullable()->comment('Nombre del feriado/evento');
            $table->text('description')->nullable();
            $table->boolean('affects_attendance')->default(true);
            $table->timestamps();

            $table->unique(['academic_year_id', 'date']);
        });

        // Componentes de evaluación (ponderación por asignatura)
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_period_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Ej: "Prueba escrita", "Tareas", "Participación", "Proyecto"
            $table->decimal('weight', 5, 2); // Porcentaje (30.00)
            $table->integer('max_score')->default(100);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Notas parciales por componente
        Schema::create('partial_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_component_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'grade_component_id']);
        });

        // Conducta / Disciplina
        Schema::create('discipline_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('severity', ['leve', 'grave', 'muy_grave']);
            $table->string('category')->nullable(); // Ej: "Uniforme", "Respeto", "Puntualidad"
            $table->text('description');
            $table->text('action_taken')->nullable(); // Sanción aplicada
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('parent_notified')->default(false);
            $table->date('parent_notified_at')->nullable();
            $table->text('follow_up')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_records');
        Schema::dropIfExists('partial_grades');
        Schema::dropIfExists('grade_components');
        Schema::dropIfExists('school_calendar');
        Schema::dropIfExists('school_config');
    }
};
