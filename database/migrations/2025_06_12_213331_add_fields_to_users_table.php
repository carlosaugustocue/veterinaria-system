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
        Schema::table('users', function (Blueprint $table) {
            // Modificar campo name existente
            $table->renameColumn('name', 'nombre');
            
            // Agregar nuevos campos después del nombre
            $table->string('apellido', 100)->after('nombre');
            $table->string('telefono', 20)->nullable()->after('password');
            $table->string('cedula', 20)->unique()->nullable()->after('telefono');
            $table->date('fecha_nacimiento')->nullable()->after('cedula');
            $table->enum('sexo', ['M', 'F'])->nullable()->after('fecha_nacimiento');
            $table->text('direccion')->nullable()->after('sexo');
            $table->string('ciudad', 100)->nullable()->after('direccion');
            
            // Agregar relación con roles
            $table->foreignId('role_id')->constrained('roles')->after('ciudad');
            
            // Campos de control
            $table->boolean('activo')->default(true)->after('role_id');
            $table->timestamp('ultimo_acceso')->nullable()->after('activo');
            $table->integer('intentos_fallidos')->default(0)->after('ultimo_acceso');
            $table->timestamp('bloqueado_hasta')->nullable()->after('intentos_fallidos');
            
            // Índices para optimización
            $table->index(['email', 'activo']);
            $table->index(['role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex(['email', 'activo']);
            $table->dropIndex(['role_id']);
            
            // Eliminar foreign key
            $table->dropForeign(['role_id']);
            
            // Eliminar columnas agregadas
            $table->dropColumn([
                'apellido', 'telefono', 'cedula', 'fecha_nacimiento',
                'sexo', 'direccion', 'ciudad', 'role_id', 'activo',
                'ultimo_acceso', 'intentos_fallidos', 'bloqueado_hasta'
            ]);
            
            // Restaurar nombre original de la columna
            $table->renameColumn('nombre', 'name');
        });
    }
};