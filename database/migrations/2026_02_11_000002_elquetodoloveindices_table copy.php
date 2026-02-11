<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Envolver cada creación de índice en un try-catch independiente
            // para que si uno falla (porque existe), los demás continúen.
        });

        // Hacemos las llamadas fuera del closure principal para manejar excepciones individualmente
        
        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('user_id', 'activity_logs_user_id_index');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('created_at', 'activity_logs_created_at_index');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('action', 'activity_logs_action_index');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('ip_address', 'activity_logs_ip_address_index');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('activity_logs', function (Blueprint $table) {
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