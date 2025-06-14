<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Raza extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'especie_id',
        'descripcion',
        'tamano',
        'peso_promedio_min',
        'peso_promedio_max',
        'esperanza_vida_min',
        'esperanza_vida_max',
        'caracteristicas_especiales',
        'cuidados_especiales',
        'colores_comunes',
        'origen_pais',
        'activo'
    ];

    protected $casts = [
        'peso_promedio_min' => 'decimal:2',
        'peso_promedio_max' => 'decimal:2',
        'colores_comunes' => 'array',
        'activo' => 'boolean'
    ];

    /**
     * Relaciones
     */
    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class);
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

    public function scopeByEspecie($query, int $especieId)
    {
        return $query->where('especie_id', $especieId);
    }

    public function scopeByTamano($query, string $tamano)
    {
        return $query->where('tamano', $tamano);
    }

    public function scopeOrderByNombre($query)
    {
        return $query->orderBy('nombre');
    }

    /**
     * Métodos de utilidad
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->especie->nombre . ' - ' . $this->nombre;
    }

    public function getRangoPesoAttribute(): string
    {
        if ($this->peso_promedio_min && $this->peso_promedio_max) {
            return $this->peso_promedio_min . ' - ' . $this->peso_promedio_max . ' kg';
        } elseif ($this->peso_promedio_min) {
            return 'Desde ' . $this->peso_promedio_min . ' kg';
        } elseif ($this->peso_promedio_max) {
            return 'Hasta ' . $this->peso_promedio_max . ' kg';
        }
        return 'No especificado';
    }

    public function getRangoVidaAttribute(): string
    {
        if ($this->esperanza_vida_min && $this->esperanza_vida_max) {
            return $this->esperanza_vida_min . ' - ' . $this->esperanza_vida_max . ' años';
        } elseif ($this->esperanza_vida_min) {
            return 'Desde ' . $this->esperanza_vida_min . ' años';
        } elseif ($this->esperanza_vida_max) {
            return 'Hasta ' . $this->esperanza_vida_max . ' años';
        }
        return 'No especificado';
    }

    public function getTamanoDescripcionAttribute(): string
    {
        $tamanos = [
            'muy_pequeno' => 'Muy Pequeño',
            'pequeno' => 'Pequeño',
            'mediano' => 'Mediano',
            'grande' => 'Grande',
            'muy_grande' => 'Muy Grande'
        ];

        return $tamanos[$this->tamano] ?? 'No especificado';
    }

    public function getColoresFormateadosAttribute(): string
    {
        if (!$this->colores_comunes || empty($this->colores_comunes)) {
            return 'Variados';
        }

        return implode(', ', $this->colores_comunes);
    }

    public function getTotalPacientesAttribute(): int
    {
        return $this->pacientes()->count();
    }

    public function esRazaPequena(): bool
    {
        return in_array($this->tamano, ['muy_pequeno', 'pequeno']);
    }

    public function esRazaGrande(): bool
    {
        return in_array($this->tamano, ['grande', 'muy_grande']);
    }

    /**
     * Validar si un peso está dentro del rango normal para la raza
     */
    public function pesoEstaEnRango(float $peso): bool
    {
        if (!$this->peso_promedio_min && !$this->peso_promedio_max) {
            return true; // Si no hay rango definido, cualquier peso es válido
        }

        $min = $this->peso_promedio_min ?? 0;
        $max = $this->peso_promedio_max ?? PHP_FLOAT_MAX;

        return $peso >= $min && $peso <= $max;
    }

    /**
     * Constantes para tamaños
     */
    public const TAMANO_MUY_PEQUENO = 'muy_pequeno';
    public const TAMANO_PEQUENO = 'pequeno';
    public const TAMANO_MEDIANO = 'mediano';
    public const TAMANO_GRANDE = 'grande';
    public const TAMANO_MUY_GRANDE = 'muy_grande';
}