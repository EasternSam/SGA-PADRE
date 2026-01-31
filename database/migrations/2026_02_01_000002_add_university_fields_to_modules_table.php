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
        Schema::table('modules', function (Blueprint $table) {
            // Créditos de la materia
            $table->integer('credits')->default(0)->after('description');
            
            // Número de período (Cuatrimestre 1, 2, etc.) para armar el Pensum
            $table->integer('period_number')->nullable()->after('credits');
            
            // ¿Es una materia electiva?
            $table->boolean('is_elective')->default(false)->after('period_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['credits', 'period_number', 'is_elective']);
        });
    }
};