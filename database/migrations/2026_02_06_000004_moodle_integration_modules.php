<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('modules', 'moodle_course_id')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('moodle_course_id')->nullable()->after('name')->comment('ID del curso en Moodle');
            });
        }
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('moodle_course_id');
        });
    }
};