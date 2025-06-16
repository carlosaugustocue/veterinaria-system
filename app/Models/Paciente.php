<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Cita;

class Paciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'propietario_id',
        'especie_id',
        'raza_id',
        'fecha_nacimiento',
        'sexo',
        'peso',
        'color',
        'senales_particulares',
        'microchip',
        'numero_registro',
        'pedigree',
        'estado',
        'fecha_registro',
        'observaciones_generales',
        'esterilizado',
        'fecha_esterilizacion',
        'alergias_conocidas',
        'medicamentos_cronicos',
        'condiciones_medicas',
        'nivel_actividad',
        'temperamento',
        'foto_url',
        'fotos_adicionales',
        'criador_nombre',
        'criador_contacto',
        'fecha_adopcion',
        'lugar_adopcion',
        'fecha_ultima_consulta',
        'fecha_proxima_vacuna',
        'fecha_proxima_desparasitacion',
        'total_consultas',
        'seguro_compania',
        'seguro_poliza',
        'seguro_vencimiento'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_registro' => 'date',
        'fecha_esterilizacion' => 'date',
        'fecha_adopcion' => 'date',
        'fecha_ultima_consulta' => 'date',
        'fecha_proxima_vacuna' => 'date',
        'fecha_proxima_desparasitacion' => 'date',
        'seguro_vencimiento' => 'date',
        'peso' => 'decimal:3',
        'esterilizado' => 'boolean',
        'fotos_adicionales' => 'array'
    ];

    /**
     * Relaciones básicas (que existen)
     */
    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class);
    }

    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class);
    }

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class);
    }

    /**
     * RELACIONES COMENTADAS TEMPORALMENTE (hasta crear los modelos)
     */
    public function citas(): HasMany
    {
         return $this->hasMany(Cita::class);
     }

    public function consultas(): HasMany
        {
         return $this->hasMany(Consulta::class);
     }

    //  public function vacunas(): HasMany
    //  {
    //      return $this->hasMany(Vacuna::class);
    //  }

    //  public function hospitalizaciones(): HasMany
    //  {
    //      return $this->hasMany(Hospitalizacion::class);
    //  }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeByPropietario($query, int $propietarioId)
    {
        return $query->where('propietario_id', $propietarioId);
    }

    public function scopeByEspecie($query, int $especieId)
    {
        return $query->where('especie_id', $especieId);
    }

    public function scopeByRaza($query, int $razaId)
    {
        return $query->where('raza_id', $razaId);
    }

    public function scopeEsterilizados($query)
    {
        return $query->where('esterilizado', true);
    }

    public function scopeConAlergias($query)
    {
        return $query->whereNotNull('alergias_conocidas');
    }

    public function scopeConSeguro($query)
    {
        return $query->whereNotNull('seguro_compania');
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('microchip', 'like', "%{$termino}%")
              ->orWhere('numero_registro', 'like', "%{$termino}%")
              ->orWhereHas('propietario.user', function ($q2) use ($termino) {
                  $q2->where('nombre', 'like', "%{$termino}%")
                     ->orWhere('apellido', 'like', "%{$termino}%");
              });
        });
    }

    public function scopeProximasVacunas($query, int $dias = 30)
    {
        return $query->where('fecha_proxima_vacuna', '<=', now()->addDays($dias))
                    ->where('fecha_proxima_vacuna', '>', now());
    }

    public function scopeVacunasVencidas($query)
    {
        return $query->where('fecha_proxima_vacuna', '<', now());
    }

    /**
     * Atributos calculados
     */
    public function getEdadAttribute(): string
    {
        $edad = Carbon::parse($this->fecha_nacimiento)->diff(Carbon::now());
        
        if ($edad->y > 0) {
            return $edad->y . ' año' . ($edad->y > 1 ? 's' : '') . 
                   ($edad->m > 0 ? ' y ' . $edad->m . ' mes' . ($edad->m > 1 ? 'es' : '') : '');
        } elseif ($edad->m > 0) {
            return $edad->m . ' mes' . ($edad->m > 1 ? 'es' : '');
        } else {
            return $edad->d . ' día' . ($edad->d > 1 ? 's' : '');
        }
    }

    public function getEdadEnAnosAttribute(): float
    {
        return Carbon::parse($this->fecha_nacimiento)->diffInYears(Carbon::now(), true);
    }

    public function getEdadEnMesesAttribute(): int
    {
        return Carbon::parse($this->fecha_nacimiento)->diffInMonths(Carbon::now());
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre . ' (' . $this->propietario->nombre_completo . ')';
    }

    public function getEspecieRazaAttribute(): string
    {
        return $this->especie->nombre . ' - ' . $this->raza->nombre;
    }

    public function getFotoUrlAttribute($value): ?string
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function getFotosAdicionalesUrlsAttribute(): array
    {
        if (!$this->fotos_adicionales) {
            return [];
        }

        return array_map(function ($foto) {
            return asset('storage/' . $foto);
        }, $this->fotos_adicionales);
    }

    /**
     * Métodos de utilidad - Estado
     */
    public function estaVivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function estaFallecido(): bool
    {
        return $this->estado === 'fallecido';
    }

    public function estaPerdido(): bool
    {
        return $this->estado === 'perdido';
    }

    public function fueAdoptado(): bool
    {
        return $this->estado === 'adoptado';
    }

    /**
     * Métodos de utilidad - Características
     */
    public function tieneAlergias(): bool
    {
        return !empty($this->alergias_conocidas);
    }

    public function tieneMedicamentosCronicos(): bool
    {
        return !empty($this->medicamentos_cronicos);
    }

    public function tieneCondicionesMedicas(): bool
    {
        return !empty($this->condiciones_medicas);
    }

    public function tieneMicrochip(): bool
    {
        return !empty($this->microchip);
    }

    public function tieneSeguro(): bool
    {
        return !empty($this->seguro_compania) && 
               ($this->seguro_vencimiento === null || $this->seguro_vencimiento > now());
    }

    public function seguroVencido(): bool
    {
        return !empty($this->seguro_compania) && 
               $this->seguro_vencimiento && 
               $this->seguro_vencimiento < now();
    }

    /**
     * Métodos de utilidad - Peso y salud
     */
    public function pesoEstaEnRangoNormal(): bool
    {
        if (!$this->peso || !$this->raza) {
            return true; // No podemos validar sin datos
        }

        return $this->raza->pesoEstaEnRango($this->peso);
    }

    public function esCachorro(): bool
    {
        return $this->edad_en_anos < 1;
    }

    public function esAdulto(): bool
    {
        return $this->edad_en_anos >= 1 && $this->edad_en_anos < 7;
    }

    public function esSenior(): bool
    {
        // Varía según el tamaño de la raza
        $edadSenior = $this->raza->esRazaGrande() ? 6 : 7;
        return $this->edad_en_anos >= $edadSenior;
    }

    /**
     * MÉTODOS COMENTADOS TEMPORALMENTE (hasta crear los modelos Cita y Consulta)
     */
    

    public function getProximaCitaAttribute()
    {
        return $this->citas()
            ->where('fecha_hora', '>', now())
            ->whereIn('estado', ['programada', 'confirmada'])
            ->orderBy('fecha_hora')
            ->first();
    }

    public function necesitaVacuna(): bool
    {
        return $this->fecha_proxima_vacuna && $this->fecha_proxima_vacuna <= now()->addDays(7);
    }

    public function vacunaVencida(): bool
    {
        return $this->fecha_proxima_vacuna && $this->fecha_proxima_vacuna < now();
    }

    public function necesitaDesparasitacion(): bool
    {
        return $this->fecha_proxima_desparasitacion && $this->fecha_proxima_desparasitacion <= now()->addDays(7);
    }

    /**
     * Métodos de actualización (simplificados temporalmente)
     */
    public function actualizarEstadisticas(): void
{
    $ultimaConsulta = $this->consultas()->latest('fecha_hora')->first();
    
    $this->update([
        'total_consultas' => $this->consultas()->count(),
        'fecha_ultima_consulta' => $ultimaConsulta?->fecha_hora?->toDateString()
    ]);
}

    public function actualizarPeso(float $nuevoPeso): void
    {
        $this->update(['peso' => $nuevoPeso]);
    }

    public function marcarEsterilizado(string $fecha = null): void
    {
        $this->update([
            'esterilizado' => true,
            'fecha_esterilizacion' => $fecha ? Carbon::parse($fecha) : now()
        ]);
    }

    public function tieneCitasPendientes(): bool
{
    return $this->citas()
        ->whereIn('estado', [Cita::ESTADO_PROGRAMADA, Cita::ESTADO_CONFIRMADA])
        ->where('fecha_hora', '>', now())
        ->exists();
}

public function getCitasHoyAttribute()
{
    return $this->citas()
        ->whereDate('fecha_hora', today())
        ->whereIn('estado', [Cita::ESTADO_PROGRAMADA, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_EN_PROCESO])
        ->get();
}

public function getTotalCitasAttribute(): int
{
    return $this->citas()->count();
}

public function getCitasCompletadasAttribute(): int
{
    return $this->citas()->where('estado', Cita::ESTADO_COMPLETADA)->count();
}

public function getCitasCanceladasAttribute(): int
{
    return $this->citas()->where('estado', Cita::ESTADO_CANCELADA)->count();
}

public function getUltimaCitaAttribute()
{
    return $this->citas()->latest('fecha_hora')->first();
}

public function getUltimaConsultaAttribute()
{
    return $this->consultas()->latest('fecha_hora')->first();
}

/**
 * NUEVOS MÉTODOS PARA CONSULTAS MÉDICAS
 */
public function getHistorialMedicoAttribute()
{
    return $this->consultas()
        ->with(['veterinario.user', 'cita'])
        ->orderBy('fecha_hora', 'desc')
        ->get();
}

public function historial()
{
    return $this->hasMany(Consulta::class, 'paciente_id');
}

public function getConsultasEsteAnoAttribute()
{
    return $this->consultas()
        ->whereYear('fecha_hora', now()->year)
        ->count();
}

public function getConsultasCompletadasAttribute()
{
    return $this->consultas()
        ->where('estado_consulta', Consulta::ESTADO_COMPLETADA)
        ->count();
}

public function tieneConsultasPendientes(): bool
{
    return $this->consultas()
        ->where('estado_consulta', Consulta::ESTADO_EN_PROGRESO)
        ->exists();
}

public function necesitaSeguimiento(): bool
{
    return $this->consultas()
        ->where('requiere_seguimiento', true)
        ->where('fecha_proximo_control', '<=', now()->addDays(7))
        ->exists();
}

public function getProximoSeguimientoAttribute()
{
    return $this->consultas()
        ->where('requiere_seguimiento', true)
        ->where('fecha_proximo_control', '>', now())
        ->orderBy('fecha_proximo_control')
        ->first();
}

public function getResumenMedicoRecienteAttribute(): array
{
    $ultimasConsultas = $this->consultas()
        ->with('veterinario.user')
        ->latest('fecha_hora')
        ->limit(3)
        ->get();

    return $ultimasConsultas->map(function ($consulta) {
        return [
            'fecha' => $consulta->fecha_formateada,
            'veterinario' => $consulta->veterinario->nombre_completo,
            'diagnostico' => $consulta->diagnostico_definitivo,
            'estado' => $consulta->estado_paciente
        ];
    })->toArray();
}

public function getDiagnosticosMasFrecuentesAttribute(): array
{
    return $this->consultas()
        ->whereNotNull('diagnostico_definitivo')
        ->where('diagnostico_definitivo', '!=', '')
        ->selectRaw('diagnostico_definitivo, COUNT(*) as total')
        ->groupBy('diagnostico_definitivo')
        ->orderByDesc('total')
        ->limit(5)
        ->pluck('total', 'diagnostico_definitivo')
        ->toArray();
}

    /**
     * Constantes
     */
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_FALLECIDO = 'fallecido';
    public const ESTADO_PERDIDO = 'perdido';
    public const ESTADO_ADOPTADO = 'adoptado';

    public const SEXO_MACHO = 'M';
    public const SEXO_HEMBRA = 'F';

    public const ACTIVIDAD_BAJA = 'bajo';
    public const ACTIVIDAD_MODERADA = 'moderado';
    public const ACTIVIDAD_ALTA = 'alto';

    public const TEMPERAMENTO_DOCIL = 'docil';
    public const TEMPERAMENTO_NORMAL = 'normal';
    public const TEMPERAMENTO_AGRESIVO = 'agresivo';
    public const TEMPERAMENTO_ANSIOSO = 'ansioso';
    public const TEMPERAMENTO_JUGUETON = 'jugueton';
}