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
        Schema::table('citas', function (Blueprint $table) {
            // Campos para confirmación
            $table->timestamp('fecha_confirmacion')->nullable()->after('estado');
            $table->unsignedBigInteger('confirmado_por_user_id')->nullable()->after('fecha_confirmacion');
            
            // Índices y foreign keys
            $table->foreign('confirmado_por_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Índice para búsquedas por estado y fecha de confirmación
            $table->index(['estado', 'fecha_confirmacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            // Eliminar foreign key primero
            $table->dropForeign(['confirmado_por_user_id']);
            
            // Eliminar índice
            $table->dropIndex(['estado', 'fecha_confirmacion']);
            
            // Eliminar columnas
            $table->dropColumn(['fecha_confirmacion', 'confirmado_por_user_id']);
        });
    }
};