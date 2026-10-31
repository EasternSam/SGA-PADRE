<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layouts del dashboard modular. Cada fila pertenece a un usuario (su diseño
 * personal) o a un rol (diseño por defecto definido por un Admin), nunca a ambos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('role', 50)->nullable()->unique();
            $table->json('widgets');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_layouts');
    }
};
