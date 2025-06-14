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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('veterinario_id')->constrained('veterinarios')->onDelete('restrict');
            $table->foreignId('propietario_id')->constrained('propietarios')->onDelete('cascade');
            
            // Información de la cita
            $table->dateTime('fecha_hora'); // Fecha y hora programada
            $table->integer('duracion_minutos')->default(30); // Duración en minutos
            $table->enum('tipo_cita', [
                'consulta_general',
                'vacunacion', 
                'cirugia',
                'emergencia',
                'seguimiento',
                'revision',
                'desparasitacion',
                'estetica',
                'otro'
            ])->default('consulta_general');
            
            // Control de estado
            $table->enum('estado', [
                'programada',
                'confirmada', 
                'en_proceso',
                'completada',
                'cancelada',
                'no_asistio'
            ])->default('programada');
            
            // Información adicional
            $table->text('motivo_consulta')->nullable(); // Razón de la cita
            $table->text('observaciones')->nullable(); // Notas especiales
            $table->text('sintomas_reportados')->nullable(); // Síntomas que reporta el propietario
            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal');
            
            // Control de tiempo
            $table->dateTime('hora_llegada')->nullable(); // Cuándo llegó el paciente
            $table->dateTime('hora_inicio_atencion')->nullable(); // Cuándo empezó la consulta
            $table->dateTime('hora_fin_atencion')->nullable(); // Cuándo terminó
            
            // Información de cancelación/reprogramación
            $table->string('motivo_cancelacion')->nullable();
            $table->foreignId('cancelado_por_user_id')->nullable()->constrained('users');
            $table->timestamp('fecha_cancelacion')->nullable();
            $table->foreignId('cita_origen_id')->nullable()->constrained('citas'); // Si es reprogramación
            
            // Información de facturación
            $table->decimal('costo_consulta', 8, 2)->nullable();
            $table->decimal('costo_adicional', 8, 2)->default(0);
            $table->decimal('descuento', 8, 2)->default(0);
            $table->decimal('total_pagado', 8, 2)->default(0);
            $table->enum('estado_pago', ['pendiente', 'pagado', 'parcial'])->default('pendiente');
            
            // Recordatorios
            $table->boolean('recordatorio_24h_enviado')->default(false);
            $table->boolean('recordatorio_2h_enviado')->default(false);
            $table->timestamp('fecha_recordatorio_24h')->nullable();
            $table->timestamp('fecha_recordatorio_2h')->nullable();
            
            // Información del personal (quién creó/modificó)
            $table->foreignId('creado_por_user_id')->constrained('users');
            $table->foreignId('modificado_por_user_id')->nullable()->constrained('users');
            
            // Metadatos
            $table->json('metadatos')->nullable(); // Para información adicional flexible
            
            $table->timestamps();
            $table->softDeletes(); // Para mantener historial
            
            // Índices para optimización
            $table->index(['fecha_hora']);
            $table->index(['veterinario_id', 'fecha_hora']);
            $table->index(['paciente_id']);
            $table->index(['propietario_id']);
            $table->index(['estado']);
            $table->index(['tipo_cita']);
            $table->index(['prioridad']);
            $table->index(['estado_pago']);
            $table->index(['fecha_hora', 'estado']); // Para consultas de disponibilidad
            
            // Índice compuesto para evitar doble agendamiento
            $table->unique(['veterinario_id', 'fecha_hora', 'deleted_at'], 'unique_vet_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};