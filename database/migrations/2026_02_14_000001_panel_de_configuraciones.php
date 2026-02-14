<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_options', function (Blueprint $table) {
            $table->string('type')->default('string')->after('value'); // Agregamos la columna type
        });
    }

    public function down(): void
    {
        Schema::table('system_options', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};