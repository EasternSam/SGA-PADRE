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
            // 1. Agregar 'description' (Texto legible del evento)
            if (!Schema::hasColumn('activity_logs', 'description')) {
                $table->text('description')->nullable()->after('action');
            }
            
            // 2. Agregar 'payload' (Datos técnicos completos en JSON)
            if (!Schema::hasColumn('activity_logs', 'payload')) {
                $table->json('payload')->nullable()->after('description');
            }
            
            // 3. Agregar 'changes' (Diferencias específicas update, si se usa separado)
            if (!Schema::hasColumn('activity_logs', 'changes')) {
                $table->json('changes')->nullable()->after('description');
            }

            // 4. Agregar 'ip_address' (Dirección IP del cliente)
            if (!Schema::hasColumn('activity_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('changes');
            }

            // 5. Agregar 'user_agent' (Navegador/Dispositivo)
            if (!Schema::hasColumn('activity_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            // 6. Opcional: Agregar columnas polimórficas por si se quieren usar índices directos a futuro
            if (!Schema::hasColumn('activity_logs', 'subject_type')) {
                $table->string('subject_type')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('activity_logs', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $columns = [
                'description', 
                'payload', 
                'changes', 
                'ip_address', 
                'user_agent',
                'subject_type',
                'subject_id'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('activity_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};