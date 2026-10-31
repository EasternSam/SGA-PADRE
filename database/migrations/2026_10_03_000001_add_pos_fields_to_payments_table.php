<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos que el POS calculaba pero nunca persistía: cantidad, descripción libre
 * del concepto abierto, efectivo recibido y cambio entregado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'quantity')) {
                $table->unsignedSmallInteger('quantity')->default(1)->after('payment_concept_id');
            }
            if (!Schema::hasColumn('payments', 'concept_label')) {
                $table->string('concept_label', 150)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('payments', 'cash_received')) {
                $table->decimal('cash_received', 12, 2)->nullable()->after('discount_amount');
            }
            if (!Schema::hasColumn('payments', 'change_amount')) {
                $table->decimal('change_amount', 12, 2)->nullable()->after('cash_received');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            foreach (['change_amount', 'cash_received', 'concept_label', 'quantity'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
