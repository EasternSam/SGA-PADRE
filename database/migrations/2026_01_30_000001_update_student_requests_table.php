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
        Schema::table('student_requests', function (Blueprint $table) {
            // 1. Añadimos la relación al Tipo de Solicitud (Nullable para soportar datos viejos)
            $table->foreignId('request_type_id')->nullable()->after('student_id')->constrained('request_types');
            
            // 2. Columnas opcionales para la nueva lógica
            $table->foreignId('course_id')->nullable()->after('request_type_id')->constrained('courses')->onDelete('set null');
            $table->foreignId('payment_id')->nullable()->after('course_id')->constrained('payments')->onDelete('set null');
            
            // 3. Hacemos 'details' nullable por si acaso la nueva lógica lo permite
            $table->text('details')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_requests', function (Blueprint $table) {
            $table->dropForeign(['request_type_id']);
            $table->dropColumn('request_type_id');
            
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
            
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
            
            // Revertir details a no-nulo (cuidado si hay nulos)
            // $table->text('details')->nullable(false)->change(); 
        });
    }
};