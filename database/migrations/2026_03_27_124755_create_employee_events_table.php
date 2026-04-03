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
        Schema::create('employee_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'bonus', 'deduction', 'memo', 'vacation', 'evaluation', 'termination', 'promotion', 'medical'
            $table->date('event_date');
            $table->decimal('amount', 12, 2)->nullable(); // used for bonus/deductions
            $table->tinyInteger('score')->nullable(); // used for evaluations (1-5)
            $table->date('end_date')->nullable(); // used for vacations / medical leaves
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_events');
    }
};
