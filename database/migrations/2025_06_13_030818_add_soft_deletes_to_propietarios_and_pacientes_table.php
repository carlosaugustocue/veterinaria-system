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
        // Agregar deleted_at a propietarios
        Schema::table('propietarios', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Agregar deleted_at a pacientes
        Schema::table('pacientes', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propietarios', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};