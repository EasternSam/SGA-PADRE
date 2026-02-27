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
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_journal_id')->constrained('accounting_journals')->onDelete('restrict');
            $table->date('date');
            $table->nullableMorphs('reference'); // reference_id, reference_type (For Payment or Invoice)
            $table->string('description');
            $table->enum('status', ['draft', 'posted', 'void'])->default('posted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_entries');
    }
};
