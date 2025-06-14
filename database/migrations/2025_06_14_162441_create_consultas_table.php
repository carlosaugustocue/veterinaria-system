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
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('veterinario_id')->constrained('veterinarios')->onDelete('restrict');
            $table->foreignId('propietario_id')->constrained('propietarios')->onDelete('cascade');
            
            // Información básica de la consulta
            $table->dateTime('fecha_hora'); // Fecha y hora real de la consulta
            $table->enum('tipo_consulta', [
                'consulta_general',
                'emergencia',
                'seguimiento',
                'cirugia',
                'vacunacion',
                'revision',
                'estetica',
                'diagnostico',
                'otro'
            ])->default('consulta_general');
            
            // Información médica principal
            $table->text('motivo_consulta'); // Razón de la visita
            $table->text('sintomas_reportados')->nullable(); // Lo que reporta el propietario
            $table->text('sintomas_observados')->nullable(); // Lo que observa el veterinario
            
            // Examen físico
            $table->json('signos_vitales')->nullable(); // Temperatura, FC, FR, peso, etc.
            $table->text('examen_fisico')->nullable(); // Hallazgos del examen
            $table->text('comportamiento')->nullable(); // Comportamiento durante consulta
            
            // Diagnóstico - ⚠️ CAMBIADO A VARCHAR para permitir índices
            $table->string('diagnostico_provisional', 500)->nullable();
            $table->string('diagnostico_definitivo', 500)->nullable();
            $table->text('diagnosticos_diferenciales')->nullable();
            
            // Tratamiento y medicamentos
            $table->text('tratamiento_realizado')->nullable(); // Procedimientos in-situ
            $table->text('plan_tratamiento')->nullable(); // Plan a seguir
            $table->text('medicamentos_prescritos')->nullable(); // Medicamentos recetados
            $table->text('dosis_instrucciones')->nullable(); // Dosificación e instrucciones
            
            // Procedimientos y estudios
            $table->text('procedimientos_realizados')->nullable(); // Cirugías, extracciones, etc.
            $table->text('estudios_solicitados')->nullable(); // Labs, rayos X, etc.
            $table->text('estudios_resultados')->nullable(); // Resultados de estudios
            
            // Seguimiento y recomendaciones
            $table->text('recomendaciones_generales')->nullable();
            $table->text('cuidados_especiales')->nullable();
            $table->text('dieta_recomendada')->nullable();
            $table->text('restricciones')->nullable();
            
            // Control de seguimiento
            $table->boolean('requiere_seguimiento')->default(false);
            $table->integer('dias_seguimiento')->nullable(); // En cuántos días
            $table->text('motivo_seguimiento')->nullable();
            $table->date('fecha_proximo_control')->nullable();
            
            // Estado y evolución
            $table->enum('estado_paciente', [
                'estable',
                'mejorado', 
                'sin_cambios',
                'empeorado',
                'critico',
                'recuperado'
            ])->default('estable');
            
            $table->enum('pronostico', [
                'excelente',
                'bueno',
                'reservado',
                'malo',
                'grave'
            ])->nullable();
            
            // Información adicional
            $table->text('observaciones_adicionales')->nullable();
            $table->text('notas_internas')->nullable(); // Solo para personal médico
            $table->json('archivos_adjuntos')->nullable(); // Fotos, documentos
            
            // Control de calidad
            $table->enum('estado_consulta', [
                'en_progreso',
                'completada',
                'revision',
                'aprobada'
            ])->default('completada');
            
            // Información de facturación
            $table->decimal('costo_consulta', 8, 2)->default(0);
            $table->decimal('costo_procedimientos', 8, 2)->default(0);
            $table->decimal('costo_medicamentos', 8, 2)->default(0);
            $table->decimal('total_consulta', 8, 2)->default(0);
            
            // Timestamps y auditoría
            $table->integer('duracion_minutos')->nullable(); // Duración real
            $table->foreignId('creado_por_user_id')->constrained('users');
            $table->foreignId('modificado_por_user_id')->nullable()->constrained('users');
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->foreignId('aprobado_por_user_id')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes(); // Para mantener historial médico
            
            // Índices para optimización - ⚠️ CORREGIDOS (sin columnas TEXT)
            $table->index(['paciente_id', 'fecha_hora']);
            $table->index(['veterinario_id', 'fecha_hora']);
            $table->index(['cita_id']);
            $table->index(['tipo_consulta']);
            $table->index(['estado_consulta']);
            $table->index(['fecha_hora']);
            $table->index(['requiere_seguimiento']);
            $table->index(['fecha_proximo_control']);
            $table->index(['estado_paciente']);
            $table->index(['pronostico']);
            
            // Índice para búsquedas médicas - ⚠️ AHORA ES POSIBLE (VARCHAR)
            $table->index(['diagnostico_definitivo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};