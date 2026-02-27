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
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('vendor_name');
            $table->foreignId('supplier_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('ncf', 19)->nullable()->after('reference_number')->comment('NCF B01, B14, B15, etc.');
            $table->string('expense_type_606', 2)->nullable()->after('ncf')->comment('Tipo de gasto para formato 606');
            $table->decimal('subtotal', 15, 2)->default(0)->after('payment_account_id');
            $table->decimal('itbis_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('itbis_retained', 15, 2)->default(0)->after('itbis_amount');
            $table->decimal('isr_retained', 15, 2)->default(0)->after('itbis_retained');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'supplier_id',
                'ncf',
                'expense_type_606',
                'subtotal',
                'itbis_amount',
                'itbis_retained',
                'isr_retained'
            ]);
            $table->string('vendor_name')->after('id');
        });
    }
};
