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
        Schema::create('propietarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Información adicional del propietario
            $table->string('ocupacion', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('preferencia_contacto', ['email', 'telefono', 'whatsapp'])->default('telefono');
            $table->boolean('acepta_promociones')->default(true);
            $table->boolean('acepta_recordatorios')->default(true);
            
            // Sistema de crédito/facturación
            $table->json('historial_credito')->nullable(); // Para historial de pagos
            $table->decimal('limite_credito', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);
            
            // Información de emergencia
            $table->string('contacto_emergencia_nombre', 100)->nullable();
            $table->string('contacto_emergencia_telefono', 20)->nullable();
            $table->enum('contacto_emergencia_relacion', ['familiar', 'amigo', 'vecino', 'otro'])->nullable();
            
            // Preferencias
            $table->string('veterinario_preferido_id')->nullable(); // Se agregará FK después
            $table->json('horarios_preferidos')->nullable(); // Horarios preferidos para citas
            
            // Estadísticas
            $table->integer('total_mascotas')->default(0);
            $table->integer('total_citas')->default(0);
            $table->date('fecha_ultima_visita')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id']);
            $table->index(['fecha_ultima_visita']);
            $table->index(['veterinario_preferido_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propietarios');
    }
};