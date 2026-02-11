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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Verificar si la columna ya existe antes de agregarla para evitar errores
            if (!Schema::hasColumn('activity_logs', 'description')) {
                $table->text('description')->nullable()->after('action');
            }
            
            // También verificar 'payload' por si acaso faltara, ya que se usa en el Trait
            if (!Schema::hasColumn('activity_logs', 'payload')) {
                $table->json('payload')->nullable()->after('description');
            }
            
            // Verificar 'changes' usado en el Trait
            if (!Schema::hasColumn('activity_logs', 'changes')) {
                $table->json('changes')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('activity_logs', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('activity_logs', 'payload')) {
                $table->dropColumn('payload');
            }
            if (Schema::hasColumn('activity_logs', 'changes')) {
                $table->dropColumn('changes');
            }
        });
    }
};