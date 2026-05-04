<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar el setting para activar/desactivar facturación electrónica
        DB::table('settings')->insertOrIgnore([
            'key' => 'enable_electronic_billing',
            'value' => 'true',
            'group' => 'finance',
            'type' => 'boolean',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el setting si se hace rollback
        DB::table('settings')->where('key', 'enable_electronic_billing')->delete();
    }
};
