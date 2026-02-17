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
        // 1. Eliminar de students si existe (corrección de la migración anterior)
        if (Schema::hasColumn('students', 'profile_photo_path')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('profile_photo_path');
            });
        }

        // 2. Agregar a users si no existe
        if (!Schema::hasColumn('users', 'profile_photo_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile_photo_path', 2048)->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir: Quitar de users y devolver a students (si se desea revertir fielmente)
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo_path');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('profile_photo_path', 2048)->nullable()->after('email');
        });
    }
};