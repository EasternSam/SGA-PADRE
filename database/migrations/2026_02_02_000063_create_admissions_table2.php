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
        Schema::table('admissions', function (Blueprint $table) {
            $table->json('documents')->nullable()->after('previous_gpa'); 
            $table->text('address')->nullable()->after('documents');
            $table->string('work_place')->nullable()->after('address');
            $table->string('disease')->nullable()->after('work_place');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['documents', 'address', 'work_place', 'disease']);
        });
    }
};