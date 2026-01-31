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
        Schema::create('module_prerequisites', function (Blueprint $table) {
            $table->id();
            
            // La materia que tiene el requisito
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            
            // La materia que ES el requisito
            $table->foreignId('prerequisite_id')->constrained('modules')->onDelete('cascade');
            
            // Tipo de requisito (opcional: 'obligatorio', 'correquisito' -simultáneo-)
            $table->string('type')->default('obligatorio'); 

            $table->timestamps();

            // Evitar duplicados
            $table->unique(['module_id', 'prerequisite_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_prerequisites');
    }
};