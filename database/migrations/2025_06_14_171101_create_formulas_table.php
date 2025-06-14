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
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->foreignId('consulta_id')->constrained('consultas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('veterinario_id')->constrained('veterinarios')->onDelete('restrict');
            $table->foreignId('propietario_id')->constrained('propietarios')->onDelete('cascade');
            
            // Información principal de la fórmula
            $table->string('numero_formula', 50)->unique(); // F2025-000001
            $table->dateTime('fecha_prescripcion'); // Fecha y hora de prescripción
            $table->string('diagnostico_resumido', 500)->nullable(); // Diagnóstico resumido
            $table->text('observaciones_generales')->nullable(); // Observaciones del veterinario
            $table->text('instrucciones_especiales')->nullable(); // Instrucciones especiales
            
            // Control de vigencia
            $table->date('fecha_vencimiento')->nullable(); // Fecha de vencimiento de la fórmula
            $table->enum('estado_formula', [
                'activa',
                'usada', 
                'cancelada',
                'vencida'
            ])->default('activa');
            
            // Información farmacéutica
            $table->string('farmacia_sugerida', 200)->nullable(); // Farmacia sugerida
            $table->decimal('costo_estimado', 10, 2)->nullable(); // Costo estimado total
            
            // Control y seguimiento
            $table->boolean('requiere_control')->default(false); // Requiere control médico
            $table->integer('dias_tratamiento')->nullable(); // Duración total del tratamiento
            $table->date('fecha_proximo_control')->nullable(); // Fecha del próximo control
            $table->text('notas_farmaceuticas')->nullable(); // Notas para el farmacéutico
            
            // Identificación y seguridad
            $table->string('codigo_barras', 50)->nullable(); // Código de barras para identificación
            $table->string('hash_verificacion', 32)->nullable(); // Hash para verificación
            
            // Control de impresión y entrega
            $table->boolean('impresa')->default(false); // Si fue impresa
            $table->dateTime('fecha_impresion')->nullable(); // Cuándo fue impresa
            $table->integer('veces_impresa')->default(0); // Número de veces impresa
            $table->boolean('entregada_propietario')->default(false); // Si fue entregada
            $table->dateTime('fecha_entrega')->nullable(); // Cuándo fue entregada
            $table->string('recibido_por', 200)->nullable(); // Quién la recibió
            
            // Auditoría y control
            $table->foreignId('creada_por_user_id')->constrained('users'); // Quién la creó
            $table->foreignId('verificada_por_user_id')->nullable()->constrained('users'); // Quién la verificó
            $table->dateTime('fecha_verificacion')->nullable(); // Cuándo fue verificada
            
            $table->timestamps();
            $table->softDeletes(); // Para mantener historial
            
            // Índices para optimización
            $table->index(['numero_formula']);
            $table->index(['consulta_id']);
            $table->index(['paciente_id', 'fecha_prescripcion']);
            $table->index(['veterinario_id', 'fecha_prescripcion']);
            $table->index(['propietario_id']);
            $table->index(['estado_formula']);
            $table->index(['fecha_prescripcion']);
            $table->index(['fecha_vencimiento']);
            $table->index(['requiere_control', 'fecha_proximo_control']);
            $table->index(['impresa']);
            $table->index(['entregada_propietario']);
            $table->index(['hash_verificacion']);
            
            // Índice único para evitar duplicados por consulta
            $table->unique(['consulta_id', 'deleted_at'], 'unique_consulta_formula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulas');
    }
};