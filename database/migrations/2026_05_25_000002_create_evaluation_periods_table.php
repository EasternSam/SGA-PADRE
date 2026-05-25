<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');               // Ej: "Primer Período", "P1"
            $table->unsignedTinyInteger('number'); // 1, 2, 3, 4
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'active', 'grading', 'closed'])->default('upcoming');
            $table->timestamps();

            $table->unique(['academic_year_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_periods');
    }
};
