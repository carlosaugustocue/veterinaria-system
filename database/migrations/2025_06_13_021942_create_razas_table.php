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
        Schema::create('razas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('especie_id')->constrained('especies')->onDelete('cascade');
            $table->text('descripcion')->nullable();
            $table->enum('tamano', ['muy_pequeno', 'pequeno', 'mediano', 'grande', 'muy_grande'])->nullable();
            $table->decimal('peso_promedio_min', 5, 2)->nullable(); // kg
            $table->decimal('peso_promedio_max', 5, 2)->nullable(); // kg
            $table->integer('esperanza_vida_min')->nullable(); // años
            $table->integer('esperanza_vida_max')->nullable(); // años
            $table->text('caracteristicas_especiales')->nullable();
            $table->text('cuidados_especiales')->nullable();
            $table->json('colores_comunes')->nullable(); // Array de colores típicos
            $table->string('origen_pais', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index(['especie_id', 'activo']);
            $table->index(['tamano']);
            $table->unique(['nombre', 'especie_id']); // No puede haber razas duplicadas por especie
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razas');
    }
};