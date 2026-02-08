<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('course_schedules', 'moodle_course_id')) {
            Schema::table('course_schedules', function (Blueprint $table) {
                $table->string('moodle_course_id')->nullable()->after('modality')->comment('ID del curso en Moodle (Sobreescribe módulo y curso)');
            });
        }
    }

    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dropColumn('moodle_course_id');
        });
    }
};