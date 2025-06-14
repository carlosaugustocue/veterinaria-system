<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Formula extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consulta_id',
        'paciente_id',
        'veterinario_id',
        'propietario_id',
        'numero_formula',
        'fecha_prescripcion',
        'diagnostico_resumido',
        'observaciones_generales',
        'instrucciones_especiales',
        'fecha_vencimiento',
        'estado_formula',
        'farmacia_sugerida',
        'costo_estimado',
        'requiere_control',
        'dias_tratamiento',
        'fecha_proximo_control',
        'notas_farmaceuticas',
        'codigo_barras',
        'hash_verificacion',
        'impresa',
        'fecha_impresion',
        'veces_impresa',
        'entregada_propietario',
        'fecha_entrega',
        'recibido_por',
        'creada_por_user_id',
        'verificada_por_user_id',
        'fecha_verificacion'
    ];

    protected $casts = [
        'fecha_prescripcion' => 'datetime',
        'fecha_vencimiento' => 'date',
        'fecha_proximo_control' => 'date',
        'fecha_impresion' => 'datetime',
        'fecha_entrega' => 'datetime',
        'fecha_verificacion' => 'datetime',
        'costo_estimado' => 'decimal:2',
        'impresa' => 'boolean',
        'entregada_propietario' => 'boolean',
        'requiere_control' => 'boolean'
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($formula) {
            if (empty($formula->numero_formula)) {
                $formula->numero_formula = static::generarNumeroFormula();
            }
            
            if (empty($formula->hash_verificacion)) {
                $formula->hash_verificacion = static::generarHashVerificacion();
            }
            
            if (empty($formula->codigo_barras)) {
                $formula->codigo_barras = static::generarCodigoBarras($formula->numero_formula);
            }
        });
    }

    /**
     * Relaciones
     */
    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function veterinario(): BelongsTo
    {
        return $this->belongsTo(Veterinario::class);
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class);
    }

    public function creadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por_user_id');
    }

    public function verificadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificada_por_user_id');
    }

    public function medicamentos(): HasMany
    {
        return $this->hasMany(FormulaMedicamento::class);
    }

    /**
     * Scopes
     */
    public function scopeActivas($query)
    {
        return $query->where('estado_formula', self::ESTADO_ACTIVA);
    }

    public function scopeVencidas($query)
    {
        return $query->where('fecha_vencimiento', '<', now())
                    ->where('estado_formula', self::ESTADO_ACTIVA);
    }

    public function scopeProximasAVencer($query, int $dias = 7)
    {
        return $query->where('fecha_vencimiento', '<=', now()->addDays($dias))
                    ->where('fecha_vencimiento', '>', now())
                    ->where('estado_formula', self::ESTADO_ACTIVA);
    }

    public function scopeByVeterinario($query, int $veterinarioId)
    {
        return $query->where('veterinario_id', $veterinarioId);
    }

    public function scopeByPaciente($query, int $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopeRequierenControl($query)
    {
        return $query->where('requiere_control', true)
                    ->where('fecha_proximo_control', '<=', now()->addDays(7))
                    ->where('estado_formula', self::ESTADO_ACTIVA);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_prescripcion', today());
    }

    public function scopeEstaSemanana($query)
    {
        return $query->whereBetween('fecha_prescripcion', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Atributos calculados
     */
    public function getFechaFormateadaAttribute(): string
    {
        return $this->fecha_prescripcion->format('d/m/Y H:i');
    }

    public function getFechaSoloAttribute(): string
    {
        return $this->fecha_prescripcion->format('d/m/Y');
    }

    public function getVencimientoFormateadoAttribute(): string
    {
        return $this->fecha_vencimiento ? $this->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento';
    }

    public function getVeterinarioCompletoAttribute(): string
    {
        return $this->veterinario->nombre_completo . ' - ' . $this->veterinario->licencia_medica;
    }

    public function getPacienteCompletoAttribute(): string
    {
        return $this->paciente->nombre . ' (' . $this->paciente->especie->nombre . ')';
    }

    public function getTotalMedicamentosAttribute(): int
    {
        return $this->medicamentos()->count();
    }

    public function getDiasVigenciaAttribute(): int
    {
        if (!$this->fecha_vencimiento) {
            return 365; // Sin vencimiento
        }
        
        return max(0, now()->diffInDays($this->fecha_vencimiento, false));
    }

    public function getDiasDesdeCreacionAttribute(): int
    {
        return $this->fecha_prescripcion->diffInDays(now());
    }

    /**
     * Métodos de utilidad - Estados
     */
    public function estaActiva(): bool
    {
        return $this->estado_formula === self::ESTADO_ACTIVA && 
               ($this->fecha_vencimiento === null || $this->fecha_vencimiento > now());
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento < now();
    }

    public function estaCancelada(): bool
    {
        return $this->estado_formula === self::ESTADO_CANCELADA;
    }

    public function estaUsada(): bool
    {
        return $this->estado_formula === self::ESTADO_USADA;
    }

    public function requiereControl(): bool
    {
        return $this->requiere_control && 
               $this->fecha_proximo_control && 
               $this->fecha_proximo_control <= now()->addDays(7);
    }

    public function fueImpresa(): bool
    {
        return $this->impresa;
    }

    public function fueEntregada(): bool
    {
        return $this->entregada_propietario;
    }

    /**
     * Métodos de acción
     */
    public function marcarComoImpresa(int $userId = null): bool
    {
        return $this->update([
            'impresa' => true,
            'fecha_impresion' => now(),
            'veces_impresa' => $this->veces_impresa + 1
        ]);
    }

    public function marcarComoEntregada(string $recibidoPor, int $userId = null): bool
    {
        return $this->update([
            'entregada_propietario' => true,
            'fecha_entrega' => now(),
            'recibido_por' => $recibidoPor
        ]);
    }

    public function cancelar(string $motivo = null, int $userId = null): bool
    {
        return $this->update([
            'estado_formula' => self::ESTADO_CANCELADA,
            'observaciones_generales' => $this->observaciones_generales . 
                "\n[CANCELADA] " . ($motivo ?? 'Sin motivo especificado') . ' - ' . now()->format('d/m/Y H:i')
        ]);
    }

    public function marcarComoUsada(int $userId = null): bool
    {
        return $this->update([
            'estado_formula' => self::ESTADO_USADA
        ]);
    }

    public function verificar(int $userId): bool
    {
        return $this->update([
            'verificada_por_user_id' => $userId,
            'fecha_verificacion' => now()
        ]);
    }

    /**
     * Métodos para agregar medicamentos
     */
    public function agregarMedicamento(array $datosMedicamento): FormulaMedicamento
    {
        return $this->medicamentos()->create($datosMedicamento);
    }

    public function calcularCostoTotal(): float
    {
        return $this->medicamentos()->sum('costo_total');
    }

    /**
     * Métodos estáticos para generación
     */
    public static function generarNumeroFormula(): string
    {
        $year = date('Y');
        $ultimo = static::whereYear('fecha_prescripcion', $year)
                       ->orderBy('numero_formula', 'desc')
                       ->first();
                       
        if ($ultimo && preg_match('/F' . $year . '-(\d+)/', $ultimo->numero_formula, $matches)) {
            $siguiente = intval($matches[1]) + 1;
        } else {
            $siguiente = 1;
        }
        
        return 'F' . $year . '-' . str_pad($siguiente, 6, '0', STR_PAD_LEFT);
    }

    public static function generarHashVerificacion(): string
    {
        return substr(hash('sha256', uniqid() . now()->timestamp), 0, 16);
    }

    public static function generarCodigoBarras(string $numeroFormula): string
    {
        // Generar código de barras basado en el número de fórmula
        return 'VET' . str_replace(['F', '-'], '', $numeroFormula);
    }

    /**
     * Método para generar PDF (placeholder)
     */
    public function generarPDF(): string
    {
        // TODO: Implementar generación de PDF con DomPDF o similar
        // Por ahora retornamos un placeholder
        return "PDF generado para fórmula: {$this->numero_formula}";
    }

    /**
     * Validar hash de verificación
     */
    public static function validarHash(string $numeroFormula, string $hash): bool
    {
        return static::where('numero_formula', $numeroFormula)
                    ->where('hash_verificacion', $hash)
                    ->exists();
    }

    /**
     * Obtener resumen de la fórmula
     */
    public function getResumenAttribute(): array
    {
        return [
            'numero' => $this->numero_formula,
            'fecha' => $this->fecha_formateada,
            'veterinario' => $this->veterinario_completo,
            'paciente' => $this->paciente_completo,
            'total_medicamentos' => $this->total_medicamentos,
            'estado' => $this->estado_formula,
            'vigencia' => $this->dias_vigencia . ' días',
            'costo_estimado' => $this->costo_estimado
        ];
    }

    /**
     * Constantes
     */
    public const ESTADO_ACTIVA = 'activa';
    public const ESTADO_USADA = 'usada';
    public const ESTADO_CANCELADA = 'cancelada';
    public const ESTADO_VENCIDA = 'vencida';
}