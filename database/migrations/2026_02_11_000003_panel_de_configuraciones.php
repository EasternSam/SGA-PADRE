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
        // 1. Lógica para 'system_options'
        if (!Schema::hasTable('system_options')) {
            // Si la tabla no existe, la creamos desde cero
            Schema::create('system_options', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('string'); 
                $table->timestamps();
            });
        } else {
            // Si la tabla YA existe, verificamos si falta la columna 'type'
            // SQLite: Hacemos la comprobación ANTES de entrar al Schema::table para evitar errores
            if (!Schema::hasColumn('system_options', 'type')) {
                Schema::table('system_options', function (Blueprint $table) {
                    $table->string('type')->default('string'); // En SQLite 'after' suele ser ignorado o problemático, se añade al final
                });
            }
        }

        // 2. Lógica para 'settings'
        if (!Schema::hasTable('settings')) {
            // Si NO existe la tabla settings, la creamos completa
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('type')->default('string');
                $table->timestamps();
            });
        } else {
            // Si la tabla YA existe (tu caso actual), verificamos las columnas nuevas una por una.
            
            // Verificar columna 'group'
            if (!Schema::hasColumn('settings', 'group')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->string('group')->default('general');
                });
            }
            
            // Verificar columna 'type'
            if (!Schema::hasColumn('settings', 'type')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->string('type')->default('string');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En producción generalmente evitamos borrar estas tablas de configuración 
        // para no perder ajustes del sistema.
        
        /* // Ejemplo de reversión segura verificando columnas
        if (Schema::hasTable('system_options')) {
            if (Schema::hasColumn('system_options', 'type')) {
                Schema::table('system_options', function (Blueprint $table) {
                    $table->dropColumn('type');
                });
            }
        }
        */
    }
};