<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * CORRECCIÓN 3 (LA DEFINITIVA):
     * Esta migración ahora también modifica 'is_minor' (que ya existía)
     * y solo añade los campos que 100% faltan.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            
            // --- 1. MODIFICAR COLUMNAS EXISTENTES ---
            // (Asumiendo que 'email', 'gender', 'address' y 'is_minor' 
            // ya existían en la migración ...001)
            
            $table->string('email')->nullable()->change();
            $table->string('gender', 20)->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->boolean('is_minor')->default(false)->change(); // <-- MODIFICAR, NO AÑADIR


            // --- 2. AÑADIR SÓLO COLUMNAS FALTANTES ---
            // (birth_date, gender, address, y is_minor ya existían)
            
            $table->string('phone', 20)->nullable()->after('email');
            
            // --- CAMPOS DE TUTOR (van después de 'is_minor' que ya existe) ---
            $table->string('tutor_name')->nullable()->after('is_minor');
            $table->string('tutor_cedula', 20)->nullable()->after('tutor_name');
            $table->string('tutor_phone', 20)->nullable()->after('tutor_cedula');
            $table->string('tutor_relationship', 50)->nullable()->after('tutor_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            
            // --- 1. ELIMINAR SÓLO LAS COLUMNAS QUE AÑADIMOS ---
            $table->dropColumn([
                'phone',
                // 'is_minor' ya no está aquí
                'tutor_name',
                'tutor_cedula',
                'tutor_phone',
                'tutor_relationship'
            ]);

            // --- 2. REVERTIR LOS 'change()' ---
            
            $table->string('gender')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->boolean('is_minor')->default(false)->change(); // Revertir 'is_minor'

            // $table->string('email')->nullable(false)->change();
        });
    }
};