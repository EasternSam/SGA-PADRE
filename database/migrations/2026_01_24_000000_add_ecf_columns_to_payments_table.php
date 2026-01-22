<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Campos específicos para Facturación Electrónica (e-CF)
            $table->string('ncf', 11)->nullable()->index()->after('transaction_id'); // Ej: E3100000001
            $table->string('ncf_type', 2)->nullable()->after('ncf'); // 31 (Crédito), 32 (Consumo)
            $table->string('security_code', 6)->nullable()->after('ncf_type'); // Código de 6 caracteres del XML firmado
            $table->date('ncf_expiration')->nullable()->after('security_code'); // Vencimiento de la secuencia
            $table->string('dgii_track_id')->nullable()->after('ncf_expiration'); // ID de rastreo de la DGII
            $table->string('dgii_status')->default('pending')->after('dgii_track_id'); // accepted, rejected
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'ncf', 
                'ncf_type', 
                'security_code', 
                'ncf_expiration', 
                'dgii_track_id', 
                'dgii_status'
            ]);
        });
    }
};