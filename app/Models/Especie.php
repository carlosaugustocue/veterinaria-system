<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'nombre_cientifico',
        'icono',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    /**
     * Relaciones
     */
    public function razas(): HasMany
    {
        return $this->hasMany(Raza::class);
    }

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConRazas($query)
    {
        return $query->has('razas');
    }

    /**
     * Métodos de utilidad
     */
    public function getRazasActivasAttribute()
    {
        return $this->razas()->where('activo', true)->orderBy('nombre')->get();
    }

    public function getTotalRazasAttribute(): int
    {
        return $this->razas()->count();
    }

    public function getTotalPacientesAttribute(): int
    {
        return $this->pacientes()->count();
    }

    /**
     * Obtener icono con fallback
     */
    public function getIconoAttribute($value): string
    {
        return $value ?? $this->getIconoPorDefecto();
    }

    /**
     * Iconos por defecto según la especie
     */
    private function getIconoPorDefecto(): string
    {
        $iconos = [
            'perro' => '🐕',
            'gato' => '🐱',
            'ave' => '🐦',
            'conejo' => '🐰',
            'reptil' => '🦎',
            'pez' => '🐠',
            'roedor' => '🐹',
        ];

        $nombreLower = strtolower($this->nombre);
        return $iconos[$nombreLower] ?? '🐾';
    }

    /**
     * Constantes para especies comunes
     */
    public const PERRO = 'Perro';
    public const GATO = 'Gato';
    public const AVE = 'Ave';
    public const CONEJO = 'Conejo';
    public const REPTIL = 'Reptil';
    public const PEZ = 'Pez';
    public const ROEDOR = 'Roedor';
}