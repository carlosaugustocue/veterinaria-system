<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido', 
        'email',
        'password',
        'telefono',
        'cedula',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'ciudad',
        'role_id',
        'activo',
        'ultimo_acceso',
        'intentos_fallidos',
        'bloqueado_hasta'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'fecha_nacimiento' => 'date',
            'ultimo_acceso' => 'datetime',
            'bloqueado_hasta' => 'datetime',
            'activo' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con el rol
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Obtener nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->nombre === $roleName;
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $module, string $action): bool
    {
        if (!$this->role || !$this->role->permisos) {
            return false;
        }

        $permisos = $this->role->permisos;
        return isset($permisos[$module]) && in_array($action, $permisos[$module]);
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive(): bool
    {
        return $this->activo && (!$this->bloqueado_hasta || $this->bloqueado_hasta < now());
    }

    /**
     * Verificar si el usuario está bloqueado
     */
    public function isBlocked(): bool
    {
        return $this->bloqueado_hasta && $this->bloqueado_hasta > now();
    }

    /**
     * Bloquear usuario temporalmente
     */
    public function blockUser(int $minutes = 15): void
    {
        $this->update([
            'bloqueado_hasta' => now()->addMinutes($minutes),
            'intentos_fallidos' => 0
        ]);
    }

    /**
     * Incrementar intentos fallidos
     */
    public function incrementFailedAttempts(): void
    {
        $this->increment('intentos_fallidos');
        
        if ($this->intentos_fallidos >= 5) {
            $this->blockUser();
        }
    }

    /**
     * Resetear intentos fallidos
     */
    public function resetFailedAttempts(): void
    {
        $this->update([
            'intentos_fallidos' => 0,
            'ultimo_acceso' => now()
        ]);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('nombre', $roleName);
        });
    }

    /**
 * Relación con propietario (para usuarios con rol cliente)
 */
public function propietario(): HasOne
{
    return $this->hasOne(Propietario::class);
}

/**
 * Relación con veterinario (para usuarios con rol veterinario)
 */
public function veterinario(): HasOne
{
    return $this->hasOne(Veterinario::class);
}

/**
 * Relación con auxiliar (para usuarios con rol auxiliar)
 */
public function auxiliar(): HasOne
{
    return $this->hasOne(Auxiliar::class);
}

/**
 * Relación con recepcionista (para usuarios con rol recepcionista)
 */
public function recepcionista(): HasOne
{
    return $this->hasOne(Recepcionista::class);
}

// MÉTODOS ADICIONALES QUE PUEDES AGREGAR:

/**
 * Verificar si el usuario es propietario
 */
public function esPropietario(): bool
{
    return $this->hasRole(Role::CLIENTE) && $this->propietario !== null;
}

/**
 * Verificar si el usuario es veterinario
 */
public function esVeterinario(): bool
{
    return $this->hasRole(Role::VETERINARIO) && $this->veterinario !== null;
}

/**
 * Obtener el perfil específico según el rol
 */
public function getPerfilAttribute()
{
    switch ($this->role->nombre) {
        case Role::CLIENTE:
            return $this->propietario;
        case Role::VETERINARIO:
            return $this->veterinario;
        case Role::AUXILIAR:
            return $this->auxiliar;
        case Role::RECEPCIONISTA:
            return $this->recepcionista;
        default:
            return null;
    }
}

/**
 * Obtener información completa del usuario con su perfil
 */
public function getInfoCompletaAttribute(): array
{
    return [
        'usuario' => [
            'id' => $this->id,
            'nombre_completo' => $this->nombre_completo,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'rol' => $this->role->nombre
        ],
        'perfil' => $this->perfil
    ];
}
}