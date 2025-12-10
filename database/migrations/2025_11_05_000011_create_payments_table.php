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
        // Reemplaza CPT `sga_pago`
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->onDelete('set null');
            $table->foreignId('payment_concept_id')->nullable()->constrained('payment_concepts')->onDelete('set null');
            
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('DOP');
            $table->string('status'); // 'Completado', 'Fallido', 'Pendiente'
            $table->string('gateway'); // 'Azul', 'Cardnet', 'Manual', 'Sistema'
            $table->string('transaction_id')->nullable()->index();
            
            // --- NUEVO CAMPO ---
            $table->date('due_date')->nullable(); // Fecha límite de pago
            // -------------------

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};