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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE accounting_accounts MODIFY COLUMN type ENUM('asset', 'liability', 'equity', 'revenue', 'expense', 'cost') NOT NULL");
        } else {
            // SQLite: no soporta MODIFY COLUMN ni ENUM.
            // La columna type en SQLite es TEXT, así que 'cost' ya es un valor válido.
            // Solo necesitamos asegurar que la columna exista (ya existe).
            // No-op: SQLite no tiene restricción ENUM real.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE accounting_accounts MODIFY COLUMN type ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL");
        }
    }
};
