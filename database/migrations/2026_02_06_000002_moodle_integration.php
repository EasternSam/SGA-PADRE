<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Añadir ID de Moodle a la tabla de cursos (o course_mappings si prefieres)
        Schema::table('courses', function (Blueprint $table) {
            $table->string('moodle_course_id')->nullable()->after('id')->comment('ID del curso en Moodle');
        });

        // Añadir ID de Moodle a la tabla de usuarios/estudiantes
        Schema::table('users', function (Blueprint $table) {
            $table->string('moodle_user_id')->nullable()->after('email')->comment('ID del usuario en Moodle');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('moodle_course_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('moodle_user_id');
        });
    }
};