<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cita;
use App\Models\Consulta;

class Propietario extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ocupacion',
        'observaciones',
        'preferencia_contacto',
        'acepta_promociones',
        'acepta_recordatorios',
        'historial_credito',
        'limite_credito',
        'saldo_pendiente',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'contacto_emergencia_relacion',
        'veterinario_preferido_id',
        'horarios_preferidos',
        'total_mascotas',
        'total_citas',
        'fecha_ultima_visita'
    ];

    protected $casts = [
        'acepta_promociones' => 'boolean',
        'acepta_recordatorios' => 'boolean',
        'historial_credito' => 'array',
        'limite_credito' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'horarios_preferidos' => 'array',
        'fecha_ultima_visita' => 'date'
    ];

    /**
     * Relaciones básicas
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class);
    }

    public function veterinarioPreferido(): BelongsTo
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_preferido_id');
    }

    /**
     * RELACIONES COMENTADAS TEMPORALMENTE (hasta crear los modelos Cita y Consulta)
     */
    public function citas()
    {
        return $this->hasManyThrough(Cita::class, Paciente::class);
    }

    public function consultas()
    {
        return $this->hasManyThrough(Consulta::class, Paciente::class);
    }

    /**
     * Atributos calculados
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->user->nombre_completo;
    }

    public function getTelefonoAttribute(): string
    {
        return $this->user->telefono;
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    public function getDireccionAttribute(): string
    {
        return $this->user->direccion ?? '';
    }

    public function getCiudadAttribute(): string
    {
        return $this->user->ciudad ?? '';
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('activo', true);
        });
    }

    public function scopeConMascotas($query)
    {
        return $query->where('total_mascotas', '>', 0);
    }

    public function scopeConSaldoPendiente($query)
    {
        return $query->where('saldo_pendiente', '>', 0);
    }

    public function scopeAceptaPromociones($query)
    {
        return $query->where('acepta_promociones', true);
    }

    public function scopeByVeterinarioPreferido($query, int $veterinarioId)
    {
        return $query->where('veterinario_preferido_id', $veterinarioId);
    }

    /**
     * Métodos de utilidad
     */
    public function puedeRecibirPromociones(): bool
    {
        return $this->acepta_promociones;
    }

    public function puedeRecibirRecordatorios(): bool
    {
        return $this->acepta_recordatorios;
    }

    public function tieneLimiteCredito(): bool
    {
        return $this->limite_credito > 0;
    }

    public function tieneSaldoPendiente(): bool
    {
        return $this->saldo_pendiente > 0;
    }

    public function tieneContactoEmergencia(): bool
    {
        return !empty($this->contacto_emergencia_nombre) && !empty($this->contacto_emergencia_telefono);
    }

    public function tieneVeterinarioPreferido(): bool
    {
        return !is_null($this->veterinario_preferido_id);
    }

    /**
     * Métodos de contacto
     */
    public function getContactoPrincipal(): string
    {
        switch ($this->preferencia_contacto) {
            case 'email':
                return $this->email;
            case 'whatsapp':
                return 'WhatsApp: ' . $this->telefono;
            case 'telefono':
            default:
                return $this->telefono;
        }
    }

    public function getContactoEmergencia(): ?string
    {
        if (!$this->tieneContactoEmergencia()) {
            return null;
        }

        return $this->contacto_emergencia_nombre . ' (' . $this->contacto_emergencia_telefono . ')';
    }

    /**
     * Métodos de estadísticas (simplificados temporalmente)
     */
    public function actualizarEstadisticas(): void
{
    $ultimaConsulta = $this->consultas()->latest('fecha_hora')->first();
    
    $this->update([
        'total_mascotas' => $this->pacientes()->count(),
        'total_citas' => $this->citas()->count(),
        'fecha_ultima_visita' => $ultimaConsulta?->fecha_hora?->toDateString()
    ]);
}

    public function getMascotasActivasAttribute()
    {
        return $this->pacientes()->where('estado', 'activo')->get();
    }

    public function getMascotasInactivasAttribute()
    {
        return $this->pacientes()->where('estado', '!=', 'activo')->get();
    }

    // MÉTODOS COMENTADOS TEMPORALMENTE (hasta crear los modelos Cita y Consulta)
    public function getProximasCitasAttribute()
    {
        return $this->citas()
            ->where('fecha_hora', '>', now())
            ->whereIn('estado', ['programada', 'confirmada'])
            ->orderBy('fecha_hora')
            ->limit(5)
            ->get();
    }

    public function getHistorialRecienteAttribute()
    {
        return $this->consultas()
            ->with(['paciente', 'veterinario.user'])
            ->latest('fecha_hora')
            ->limit(10)
            ->get();
    }

    /**
     * Métodos de crédito
     */
    public function agregarMovimientoCredito(float $monto, string $tipo, string $descripcion): void
    {
        $historial = $this->historial_credito ?? [];
        
        $historial[] = [
            'fecha' => now()->toDateString(),
            'tipo' => $tipo, // 'cargo', 'pago', 'descuento'
            'monto' => $monto,
            'descripcion' => $descripcion,
            'saldo_anterior' => $this->saldo_pendiente,
            'saldo_nuevo' => $this->saldo_pendiente + ($tipo === 'pago' ? -$monto : $monto)
        ];

        $this->update([
            'historial_credito' => $historial,
            'saldo_pendiente' => $tipo === 'pago' ? 
                max(0, $this->saldo_pendiente - $monto) : 
                $this->saldo_pendiente + $monto
        ]);
    }

    public function puedeTomarCredito(float $monto): bool
    {
        if ($this->limite_credito <= 0) {
            return false;
        }

        return ($this->saldo_pendiente + $monto) <= $this->limite_credito;
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

public function getCitasEstaSemannaAttribute()
{
    return $this->citas()
        ->whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
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

public function getSaldoTotalPendienteAttribute(): float
{
    return $this->citas()
        ->where('estado_pago', '!=', Cita::ESTADO_PAGO_PAGADO)
        ->get()
        ->sum('saldo_pendiente');
}

public function getGastoTotalEsteAnoAttribute(): float
{
    return $this->citas()
        ->whereYear('fecha_hora', now()->year)
        ->where('estado', Cita::ESTADO_COMPLETADA)
        ->sum('total_pagado');
}

public function getVeterinarioMasFrecuenteAttribute()
{
    return $this->citas()
        ->select('veterinario_id')
        ->selectRaw('COUNT(*) as total_citas')
        ->groupBy('veterinario_id')
        ->orderByDesc('total_citas')
        ->with('veterinario.user')
        ->first()?->veterinario;
}

public function getConsultasEsteAnoAttribute()
{
    return $this->consultas()
        ->whereYear('fecha_hora', now()->year)
        ->count();
}

public function getGastoMedicoEsteAnoAttribute(): float
{
    return $this->consultas()
        ->whereYear('fecha_hora', now()->year)
        ->where('estado_consulta', Consulta::ESTADO_COMPLETADA)
        ->sum('total_consulta');
}

public function getHistorialMedicoCompletoAttribute()
{
    return $this->consultas()
        ->with(['paciente', 'veterinario.user', 'cita'])
        ->orderBy('fecha_hora', 'desc')
        ->get()
        ->groupBy('paciente.nombre');
}

public function tieneMascotasConSeguimientoPendiente(): bool
{
    return $this->consultas()
        ->where('requiere_seguimiento', true)
        ->where('fecha_proximo_control', '<=', now()->addDays(7))
        ->exists();
}

public function getMascotasConSeguimientoPendienteAttribute()
{
    return $this->consultas()
        ->with('paciente')
        ->where('requiere_seguimiento', true)
        ->where('fecha_proximo_control', '<=', now()->addDays(7))
        ->get()
        ->unique('paciente_id')
        ->pluck('paciente');
}

public function getResumenMedicoFamiliaAttribute(): array
{
    $resumen = [];
    
    foreach ($this->pacientes as $paciente) {
        $ultimaConsulta = $paciente->consultas()->latest('fecha_hora')->first();
        
        $resumen[] = [
            'mascota' => $paciente->nombre,
            'especie' => $paciente->especie->nombre,
            'ultima_consulta' => $ultimaConsulta?->fecha_formateada,
            'diagnostico_reciente' => $ultimaConsulta?->diagnostico_definitivo,
            'estado' => $ultimaConsulta?->estado_paciente,
            'necesita_seguimiento' => $paciente->necesitaSeguimiento()
        ];
    }
    
    return $resumen;
}

public function getVeterinarioMasFrecuenteEnConsultasAttribute()
{
    return $this->consultas()
        ->select('veterinario_id')
        ->selectRaw('COUNT(*) as total_consultas')
        ->groupBy('veterinario_id')
        ->orderByDesc('total_consultas')
        ->with('veterinario.user')
        ->first()?->veterinario;
}

public function getProximosControlesAttribute()
{
    return $this->consultas()
        ->where('requiere_seguimiento', true)
        ->where('fecha_proximo_control', '>', now())
        ->with(['paciente', 'veterinario.user'])
        ->orderBy('fecha_proximo_control')
        ->limit(5)
        ->get();
}

    /**
     * Constantes
     */
    public const PREFERENCIA_EMAIL = 'email';
    public const PREFERENCIA_TELEFONO = 'telefono';
    public const PREFERENCIA_WHATSAPP = 'whatsapp';

    public const RELACION_FAMILIAR = 'familiar';
    public const RELACION_AMIGO = 'amigo';
    public const RELACION_VECINO = 'vecino';
    public const RELACION_OTRO = 'otro';
}