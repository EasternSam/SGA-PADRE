<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Monitor Dell 24"
            $table->string('serial_number')->nullable()->unique(); // Para rastreo único
            $table->string('asset_tag')->nullable()->unique(); // Código de inventario interno (etiqueta)
            
            // Categoría: PC, Monitor, Proyector, Mobiliario, Aire Acondicionado, etc.
            $table->string('category'); 
            
            // Estado: Operativo, Defectuoso, En Reparación, Obsoleto/Baja
            $table->string('status')->default('Operativo');
            
            // Ubicación: Si es null, está en Almacén/Bodega
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            
            $table->text('notes')->nullable(); // Detalles del defecto o especificaciones
            $table->date('purchase_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};