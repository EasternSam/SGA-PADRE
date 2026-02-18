<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $isMysql = (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb');

        // Hacemos las llamadas fuera del closure principal para manejar excepciones individualmente
        
        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                // User ID suele ser INT, no hay problema
                $table->index('user_id', 'activity_logs_user_id_index');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('created_at', 'activity_logs_created_at_index');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('activity_logs', function (Blueprint $table) use ($isMysql) {
                // CORRECCIÓN: Usamos sintaxis estándar de Laravel para mayor compatibilidad
                // Laravel y MySQL moderno manejan índices de strings sin necesidad de prefijos fijos en la mayoría de los casos
                $table->index('action', 'activity_logs_action_index');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                // IP suele ser corta (45 chars), seguro.
                $table->index('ip_address', 'activity_logs_ip_address_index');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                // Composite INT + DATETIME, seguro.
                $table->index(['user_id', 'created_at'], 'activity_logs_user_date_index');
            });
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            try { $table->dropIndex('activity_logs_user_id_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('activity_logs_created_at_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('activity_logs_action_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('activity_logs_ip_address_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('activity_logs_user_date_index'); } catch (\Exception $e) {}
        });
    }
};