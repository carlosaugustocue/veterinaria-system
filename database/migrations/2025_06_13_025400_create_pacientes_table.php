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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            
            // Información básica
            $table->string('nombre', 100);
            $table->foreignId('propietario_id')->constrained('propietarios')->onDelete('cascade');
            $table->foreignId('especie_id')->constrained('especies');
            $table->foreignId('raza_id')->constrained('razas');
            
            // Datos físicos
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['M', 'F']);
            $table->decimal('peso', 6, 3)->nullable(); // Peso actual en kg con 3 decimales
            $table->string('color', 100)->nullable();
            $table->text('senales_particulares')->nullable(); // Marcas, cicatrices, etc.
            
            // Identificación
            $table->string('microchip', 50)->nullable()->unique();
            $table->string('numero_registro', 50)->nullable(); // Registro oficial si existe
            $table->string('pedigree', 100)->nullable(); // Información de pedigree
            
            // Estado y control
            $table->enum('estado', ['activo', 'fallecido', 'perdido', 'adoptado'])->default('activo');
            $table->date('fecha_registro');
            $table->text('observaciones_generales')->nullable();
            
            // Información médica básica
            $table->boolean('esterilizado')->default(false);
            $table->date('fecha_esterilizacion')->nullable();
            $table->text('alergias_conocidas')->nullable();
            $table->text('medicamentos_cronicos')->nullable();
            $table->text('condiciones_medicas')->nullable(); // Enfermedades crónicas
            $table->enum('nivel_actividad', ['bajo', 'moderado', 'alto'])->default('moderado');
            $table->enum('temperamento', ['docil', 'normal', 'agresivo', 'ansioso', 'jugueton'])->default('normal');
            
            // Multimedia
            $table->string('foto_url')->nullable();
            $table->json('fotos_adicionales')->nullable(); // Array de URLs de fotos
            
            // Información del criador/origen
            $table->string('criador_nombre', 100)->nullable();
            $table->string('criador_contacto', 100)->nullable();
            $table->date('fecha_adopcion')->nullable(); // Si fue adoptado
            $table->string('lugar_adopcion', 100)->nullable();
            
            // Seguimiento veterinario
            $table->date('fecha_ultima_consulta')->nullable();
            $table->date('fecha_proxima_vacuna')->nullable();
            $table->date('fecha_proxima_desparasitacion')->nullable();
            $table->integer('total_consultas')->default(0);
            
            // Información de seguro (si aplica)
            $table->string('seguro_compania', 100)->nullable();
            $table->string('seguro_poliza', 50)->nullable();
            $table->date('seguro_vencimiento')->nullable();
            
            $table->timestamps();
            
            // Índices para optimización de consultas
            $table->index(['propietario_id']);
            $table->index(['especie_id', 'raza_id']);
            $table->index(['estado']);
            $table->index(['fecha_registro']);
            $table->index(['fecha_nacimiento']); // Para cálculos de edad
            $table->index(['esterilizado']);
            $table->index(['fecha_ultima_consulta']);
            $table->index(['fecha_proxima_vacuna']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};