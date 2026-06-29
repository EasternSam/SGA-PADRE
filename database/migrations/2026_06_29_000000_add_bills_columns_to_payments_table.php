<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('bills_invoice_id')->nullable()->after('dgii_status');
            $table->string('bills_invoice_number')->nullable()->index()->after('bills_invoice_id');
            $table->string('bills_sync_status')->default('pending')->after('bills_invoice_number');
            $table->text('bills_sync_error')->nullable()->after('bills_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'bills_invoice_id',
                'bills_invoice_number',
                'bills_sync_status',
                'bills_sync_error'
            ]);
        });
    }
};
