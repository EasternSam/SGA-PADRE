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
            // Índice para búsquedas por usuario (filtro de dropdown)
            // Ayuda en: where('user_id', $id)
            if (!collect(DB::select("SHOW INDEXES FROM activity_logs WHERE Key_name = 'activity_logs_user_id_index'"))->count()) {
                 $table->index('user_id');
            }

            // Índice para ordenamiento cronológico y rangos de fecha
            // Ayuda en: orderBy('created_at', 'desc') y whereBetween('created_at', ...)
            if (!collect(DB::select("SHOW INDEXES FROM activity_logs WHERE Key_name = 'activity_logs_created_at_index'"))->count()) {
                $table->index('created_at');
            }

            // Índice para búsquedas por tipo de acción
            // Ayuda en: where('action', 'like', ...)
            if (!collect(DB::select("SHOW INDEXES FROM activity_logs WHERE Key_name = 'activity_logs_action_index'"))->count()) {
                $table->index('action');
            }

            // Índice para búsquedas por IP
            // Ayuda en: where('ip_address', ...)
            if (!collect(DB::select("SHOW INDEXES FROM activity_logs WHERE Key_name = 'activity_logs_ip_address_index'"))->count()) {
                $table->index('ip_address');
            }
            
            // Opcional: Índice compuesto para consultas muy comunes (Usuario + Fecha)
            // Esto optimiza drásticamente: "Ver actividad del usuario X este mes"
            if (!collect(DB::select("SHOW INDEXES FROM activity_logs WHERE Key_name = 'activity_logs_user_date_index'"))->count()) {
                $table->index(['user_id', 'created_at'], 'activity_logs_user_date_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Laravel automáticamente busca el índice basado en la convención tabla_columna_index
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['action']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex('activity_logs_user_date_index');
        });
    }
};