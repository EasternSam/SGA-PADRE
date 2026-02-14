<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Asegurar tabla system_options y columna 'type'
        if (!Schema::hasTable('system_options')) {
            Schema::create('system_options', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('string'); 
                $table->timestamps();
            });
        } else {
            // Si la tabla ya existe, verificamos si falta la columna 'type'
            if (!Schema::hasColumn('system_options', 'type')) {
                Schema::table('system_options', function (Blueprint $table) {
                    $table->string('type')->default('string')->after('value');
                });
            }
        }

        // 2. Asegurar tabla settings
        // Verificar si la tabla 'settings' NO existe antes de crearla.
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('type')->default('string');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // No borramos nada para proteger datos en producción
    }
};