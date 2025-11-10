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
        Schema::table('students', function (Blueprint $table) {
            // Añadir la columna user_id que será la llave foránea
            $table->foreignId('user_id')
                  ->nullable() // Puede ser nulo si el estudiante no tiene cuenta
                  ->after('id') // Colocarla después del ID del estudiante
                  ->constrained('users') // Apunta a la tabla 'users'
                  ->onDelete('set null'); // Si se borra el usuario, poner el user_id en null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Eliminar la llave foránea y la columna
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};