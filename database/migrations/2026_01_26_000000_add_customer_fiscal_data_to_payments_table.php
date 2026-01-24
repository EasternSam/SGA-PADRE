<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Agregamos columnas para guardar los datos fiscales del cliente solicitante
            $table->string('rnc_client', 20)->nullable()->after('ncf_type');
            $table->string('company_name', 150)->nullable()->after('rnc_client');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['rnc_client', 'company_name']);
        });
    }
};