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
        Schema::create('veterinarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('licencia_medica', 50)->unique();
            $table->string('especialidad', 100)->nullable();
            $table->text('certificaciones')->nullable();
            $table->integer('anos_experiencia')->default(0);
            $table->json('horario_trabajo')->nullable();
            $table->integer('duracion_consulta')->default(30);
            $table->integer('max_citas_dia')->default(16);
            $table->boolean('disponible_emergencias')->default(true);
            $table->decimal('tarifa_consulta', 8, 2)->nullable();
            $table->decimal('tarifa_emergencia', 8, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['user_id']);
            $table->index(['licencia_medica']);
            $table->index(['especialidad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinarios');
    }
};