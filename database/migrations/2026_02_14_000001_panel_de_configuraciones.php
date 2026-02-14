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
            Schema::table('system_options', function (Blueprint $table) {
                if (!Schema::hasColumn('system_options', 'type')) {
                    $table->string('type')->default('string')->after('value');
                }
            });
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
            // Si la tabla YA existe (tu caso actual), verificamos las columnas nuevas
            // para asegurarnos de que coincida con la estructura deseada sin borrar datos.
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'group')) {
                    $table->string('group')->default('general')->after('value');
                }
                
                if (!Schema::hasColumn('settings', 'type')) {
                    $table->string('type')->default('string')->after('group'); // o after 'value' si group no existiera
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En producción generalmente evitamos borrar estas tablas de configuración 
        // para no perder ajustes del sistema, pero si necesitas revertir cambios:
        
        /* if (Schema::hasColumn('system_options', 'type')) {
            Schema::table('system_options', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
        
        // No borramos las tablas completas por seguridad
        */
    }
};