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
        Schema::table('payment_concepts', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_concepts', 'amount')) {
                // Agregamos amount para definir el costo base del concepto
                $table->decimal('amount', 10, 2)->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_concepts', function (Blueprint $table) {
            if (Schema::hasColumn('payment_concepts', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};