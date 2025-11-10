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
        Schema::create('payment_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // --- CAMPOS AÑADIDOS ---
            // Tu componente también guarda la descripción
            $table->text('description')->nullable(); 
            // Esta es la columna que causa el error
            $table->boolean('is_fixed_amount')->default(true); 
            // --- FIN DE CAMPOS AÑADIDOS ---

            $table->decimal('default_amount', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_concepts');
    }
};