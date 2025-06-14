<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'permisos',
        'activo'
    ];

    protected $casts = [
        'permisos' => 'array',
        'activo' => 'boolean'
    ];

    /**
     * Relación con usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function hasPermission(string $module, string $action): bool
    {
        if (!$this->permisos) {
            return false;
        }

        return isset($this->permisos[$module]) && in_array($action, $this->permisos[$module]);
    }

    /**
     * Agregar un permiso al rol
     */
    public function addPermission(string $module, string $action): void
    {
        $permisos = $this->permisos ?? [];
        
        if (!isset($permisos[$module])) {
            $permisos[$module] = [];
        }

        if (!in_array($action, $permisos[$module])) {
            $permisos[$module][] = $action;
            $this->update(['permisos' => $permisos]);
        }
    }

    /**
     * Remover un permiso del rol
     */
    public function removePermission(string $module, string $action): void
    {
        $permisos = $this->permisos ?? [];
        
        if (isset($permisos[$module])) {
            $permisos[$module] = array_filter($permisos[$module], function($perm) use ($action) {
                return $perm !== $action;
            });
            
            if (empty($permisos[$module])) {
                unset($permisos[$module]);
            }
            
            $this->update(['permisos' => $permisos]);
        }
    }

    /**
     * Scope para roles activos
     */
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Constantes para los nombres de roles
     */
    public const ADMINISTRADOR = 'administrador';
    public const VETERINARIO = 'veterinario';
    public const AUXILIAR = 'auxiliar';
    public const RECEPCIONISTA = 'recepcionista';
    public const CLIENTE = 'cliente';
}