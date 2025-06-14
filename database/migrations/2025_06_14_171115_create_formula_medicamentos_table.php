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
        Schema::create('formula_medicamentos', function (Blueprint $table) {
            $table->id();
            
            // Relación con la fórmula
            $table->foreignId('formula_id')->constrained('formulas')->onDelete('cascade');
            
            // Información del medicamento
            $table->string('nombre_medicamento', 200); // Nombre comercial del medicamento
            $table->string('principio_activo', 200)->nullable(); // Principio activo
            $table->string('concentracion', 100)->nullable(); // Concentración (ej: 500mg, 5%)
            $table->enum('forma_farmaceutica', [
                'tableta',
                'cápsula', 
                'jarabe',
                'suspensión',
                'inyectable',
                'crema',
                'pomada',
                'gotas',
                'spray',
                'polvo',
                'shampoo',        // ⭐ AGREGADO
                'loción',         // ⭐ AGREGADO
                'gel',            // ⭐ AGREGADO
                'parche',         // ⭐ AGREGADO
                'supositorio',    // ⭐ AGREGADO
                'collar',         // ⭐ AGREGADO (para pulgas, etc.)
                'pipeta',         // ⭐ AGREGADO (spot-on)
                'otro'
            ])->default('tableta');
            
            // Dosificación y administración
            $table->string('dosis', 100); // Dosis por toma (ej: 1 tableta, 5ml, 2mg/kg)
            $table->string('frecuencia', 100); // Frecuencia (ej: cada 8 horas, 2 veces al día)
            $table->string('duracion_tratamiento', 100)->nullable(); // Duración (ej: 7 días, 2 semanas)
            $table->decimal('cantidad_total', 8, 2); // Cantidad total a dispensar
            $table->enum('unidad_medida', [
                'mg',
                'g', 
                'ml',
                'tabletas',
                'cápsulas',
                'aplicaciones',
                'dosis',
                'otro'
            ])->default('tabletas');
            
            // Vía de administración
            $table->enum('via_administracion', [
                'oral',
                'tópica',
                'ocular',
                'auditiva',
                'subcutánea',
                'intramuscular', 
                'intravenosa',
                'rectal',
                'otra'
            ])->default('oral');
            
            // Instrucciones detalladas
            $table->text('instrucciones_uso')->nullable(); // Instrucciones específicas de uso
            $table->text('contraindicaciones')->nullable(); // Contraindicaciones importantes
            $table->text('efectos_secundarios')->nullable(); // Efectos secundarios a vigilar
            $table->text('interacciones')->nullable(); // Interacciones medicamentosas
            $table->text('observaciones')->nullable(); // Observaciones adicionales
            
            // Información comercial
            $table->decimal('precio_unitario', 8, 2)->nullable(); // Precio por unidad
            $table->decimal('costo_total', 8, 2)->nullable(); // Costo total del medicamento
            $table->string('codigo_medicamento', 50)->nullable(); // Código del medicamento
            $table->string('lote_medicamento', 50)->nullable(); // Lote del medicamento
            $table->date('fecha_vencimiento_med')->nullable(); // Vencimiento del medicamento
            
            // Control farmacéutico
            $table->boolean('requiere_receta')->default(true); // Requiere receta médica
            $table->boolean('es_controlado')->default(false); // Es medicamento controlado
            $table->integer('orden_administracion')->default(1); // Orden de administración
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['formula_id']);
            $table->index(['nombre_medicamento']);
            $table->index(['principio_activo']);
            $table->index(['forma_farmaceutica']);
            $table->index(['via_administracion']);
            $table->index(['requiere_receta']);
            $table->index(['es_controlado']);
            $table->index(['orden_administracion']);
            $table->index(['codigo_medicamento']);
            
            // Índice para evitar medicamentos duplicados en la misma fórmula
            $table->unique(['formula_id', 'nombre_medicamento', 'concentracion'], 'unique_medicamento_formula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_medicamentos');
    }
};