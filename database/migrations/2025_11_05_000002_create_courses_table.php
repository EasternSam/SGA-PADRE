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
        // Esta tabla será el "padre" (ej. Informática, Idiomas)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Ej: "Informática"
            $table->text('description')->nullable();
            $table->string('status')->default('Activo'); // Activo, Inactivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};