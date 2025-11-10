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
        Schema::table('courses', function (Blueprint $table) {
            // --- ¡CORRECCIÓN! ---
            // Añadimos 'credits' primero, ya que tampoco existía.
            // La colocamos después de 'name'.
            $table->integer('credits')->default(0)->after('name');
            
            // Ahora añadimos 'code' después de 'credits', que acabamos de crear.
            $table->string('code')->unique()->nullable()->after('credits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Si hacemos rollback, eliminamos ambas columnas
            $table->dropColumn(['code', 'credits']);
        });
    }
};