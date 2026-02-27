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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name');
            $table->string('reference_number')->nullable()->comment('Invoice Number, NCF, Recibo, etc.');
            $table->date('expense_date');
            $table->date('due_date')->nullable();
            
            // Relación con el Catálogo de Cuentas
            $table->foreignId('expense_account_id')->constrained('accounting_accounts')->onDelete('restrict')->comment('Cuenta de Gasto (Debe)');
            $table->foreignId('payment_account_id')->constrained('accounting_accounts')->onDelete('restrict')->comment('Cuenta de Activo/Pasivo origen (Haber)');
            
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('paid')->comment('pending, paid, void');
            $table->text('description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
