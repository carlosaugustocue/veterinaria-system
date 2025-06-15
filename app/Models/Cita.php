<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Consulta;

class Cita extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'paciente_id',
        'veterinario_id',
        'propietario_id',
        'fecha_hora',
        'duracion_minutos',
        'tipo_cita',
        'estado',
        'motivo_consulta',
        'fecha_confirmacion',
        'confirmado_por_user_id',
        'observaciones',
        'sintomas_reportados',
        'prioridad',
        'hora_llegada',
        'hora_inicio_atencion',
        'hora_fin_atencion',
        'motivo_cancelacion',
        'cancelado_por_user_id',
        'fecha_cancelacion',
        'cita_origen_id',
        'costo_consulta',
        'costo_adicional',
        'descuento',
        'total_pagado',
        'estado_pago',
        'recordatorio_24h_enviado',
        'recordatorio_2h_enviado',
        'fecha_recordatorio_24h',
        'fecha_recordatorio_2h',
        'creado_por_user_id',
        'modificado_por_user_id',
        'metadatos'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'hora_llegada' => 'datetime',
        'hora_inicio_atencion' => 'datetime',
        'fecha_confirmacion' => 'datetime',
        'hora_fin_atencion' => 'datetime',
        'fecha_cancelacion' => 'datetime',
        'fecha_recordatorio_24h' => 'datetime',
        'fecha_recordatorio_2h' => 'datetime',
        'costo_consulta' => 'decimal:2',
        'costo_adicional' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total_pagado' => 'decimal:2',
        'recordatorio_24h_enviado' => 'boolean',
        'recordatorio_2h_enviado' => 'boolean',
        'metadatos' => 'array'
    ];

    /**
     * Relaciones
     */
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

    public function canceladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelado_por_user_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_user_id');
    }

    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por_user_id');
    }

    public function citaOrigen(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_origen_id');
    }

    public function reprogramaciones(): HasMany
    {
        return $this->hasMany(Cita::class, 'cita_origen_id');
    }

    
    public function consulta(): HasOne
    {
        return $this->hasOne(Consulta::class);
    }

    /**
     * Scopes de estado
     */
    public function scopeProgramadas($query)
    {
        return $query->where('estado', self::ESTADO_PROGRAMADA);
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('estado', self::ESTADO_CONFIRMADA);
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', self::ESTADO_EN_PROCESO);
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', self::ESTADO_COMPLETADA);
    }

    public function scopeCanceladas($query)
    {
        return $query->where('estado', self::ESTADO_CANCELADA);
    }

    public function scopeActivas($query)
    {
        return $query->whereIn('estado', [
            self::ESTADO_PROGRAMADA,
            self::ESTADO_CONFIRMADA,
            self::ESTADO_EN_PROCESO
        ]);
    }

    /**
     * Scopes de tiempo
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_hora', today());
    }

    public function scopeManana($query)
    {
        return $query->whereDate('fecha_hora', today()->addDay());
    }

    public function scopeEstaSemanana($query)
    {
        return $query->whereBetween('fecha_hora', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeProximasSemanas($query, int $semanas = 2)
    {
        return $query->whereBetween('fecha_hora', [
            now(),
            now()->addWeeks($semanas)
        ]);
    }

    public function scopeEnRangoFecha($query, string $desde, string $hasta)
    {
        return $query->whereBetween('fecha_hora', [
            Carbon::parse($desde)->startOfDay(),
            Carbon::parse($hasta)->endOfDay()
        ]);
    }

    public function scopeProximas24Horas($query)
    {
        return $query->whereBetween('fecha_hora', [now(), now()->addHours(24)]);
    }

    public function scopeProximas2Horas($query)
    {
        return $query->whereBetween('fecha_hora', [now(), now()->addHours(2)]);
    }

    /**
     * Scopes de filtrado
     */
    public function scopeByVeterinario($query, int $veterinarioId)
    {
        return $query->where('veterinario_id', $veterinarioId);
    }

    public function scopeByPaciente($query, int $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopeByPropietario($query, int $propietarioId)
    {
        return $query->where('propietario_id', $propietarioId);
    }

    public function scopeByTipo($query, string $tipo)
    {
        return $query->where('tipo_cita', $tipo);
    }

    public function scopeByPrioridad($query, string $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopeUrgentes($query)
    {
        return $query->where('prioridad', self::PRIORIDAD_URGENTE);
    }

    public function scopeEmergencias($query)
    {
        return $query->where('tipo_cita', self::TIPO_EMERGENCIA);
    }

    /**
     * Scopes de recordatorios
     */
    public function scopeNecesitaRecordatorio24h($query)
    {
        return $query->where('recordatorio_24h_enviado', false)
                    ->where('fecha_hora', '>', now()->addHours(23))
                    ->where('fecha_hora', '<=', now()->addHours(25))
                    ->activas();
    }

    public function scopeNecesitaRecordatorio2h($query)
    {
        return $query->where('recordatorio_2h_enviado', false)
                    ->where('fecha_hora', '>', now()->addHours(1))
                    ->where('fecha_hora', '<=', now()->addHours(3))
                    ->activas();
    }

    /**
     * Atributos calculados
     */
    public function getFechaFormateadaAttribute(): string
    {
        return $this->fecha_hora->format('d/m/Y H:i');
    }

    public function getFechaSoloAttribute(): string
    {
        return $this->fecha_hora->format('d/m/Y');
    }

    public function getHoraSolaAttribute(): string
    {
        return $this->fecha_hora->format('H:i');
    }

    public function getDiaSemanaAttribute(): string
    {
        $dias = [
            'Sunday' => 'Domingo',
            'Monday' => 'Lunes', 
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado'
        ];
        
        return $dias[$this->fecha_hora->format('l')] ?? $this->fecha_hora->format('l');
    }

    public function getHoraFinEstimadaAttribute(): Carbon
    {
        return $this->fecha_hora->copy()->addMinutes($this->duracion_minutos);
    }

    public function getTotalFacturadoAttribute(): float
    {
        return ($this->costo_consulta + $this->costo_adicional) - $this->descuento;
    }

    public function getSaldoPendienteAttribute(): float
    {
        return max(0, $this->total_facturado - $this->total_pagado);
    }

    public function getDuracionRealAttribute(): ?int
    {
        if (!$this->hora_inicio_atencion || !$this->hora_fin_atencion) {
            return null;
        }
        
        return $this->hora_inicio_atencion->diffInMinutes($this->hora_fin_atencion);
    }

    public function getTiempoEsperaAttribute(): ?int
    {
        if (!$this->hora_llegada || !$this->hora_inicio_atencion) {
            return null;
        }
        
        return $this->hora_llegada->diffInMinutes($this->hora_inicio_atencion);
    }

    /**
     * Métodos de utilidad - Estados
     */
    public function estaProgramada(): bool
    {
        return $this->estado === self::ESTADO_PROGRAMADA;
    }

    public function estaConfirmada(): bool
    {
        return $this->estado === self::ESTADO_CONFIRMADA;
    }

    public function estaEnProceso(): bool
    {
        return $this->estado === self::ESTADO_EN_PROCESO;
    }

    public function estaCompletada(): bool
    {
        return $this->estado === self::ESTADO_COMPLETADA;
    }

    public function estaCancelada(): bool
    {
        return $this->estado === self::ESTADO_CANCELADA;
    }

    public function noAsistio(): bool
    {
        return $this->estado === self::ESTADO_NO_ASISTIO;
    }

    public function estaActiva(): bool
    {
        return in_array($this->estado, [
            self::ESTADO_PROGRAMADA,
            self::ESTADO_CONFIRMADA,
            self::ESTADO_EN_PROCESO
        ]);
    }

    /**
     * Métodos de utilidad - Tiempo
     */
    public function esHoy(): bool
    {
        return $this->fecha_hora->isToday();
    }

    public function esManana(): bool
    {
        return $this->fecha_hora->isTomorrow();
    }

    public function esProxima(): bool
    {
        return $this->fecha_hora->isFuture();
    }

    public function esPasada(): bool
    {
        return $this->fecha_hora->isPast();
    }

    public function faltanHoras(): int
    {
        return max(0, now()->diffInHours($this->fecha_hora, false));
    }

    public function faltanMinutos(): int
    {
        return max(0, now()->diffInMinutes($this->fecha_hora, false));
    }

    /**
     * Métodos de utilidad - Acciones
     */
    public function puedeSerCancelada(): bool
    {
        return $this->estaActiva() && $this->faltanHoras() >= 4;
    }

    public function puedeSerReprogramada(): bool
    {
        return $this->estaActiva() && $this->faltanHoras() >= 2;
    }

    public function puedeSerConfirmada(): bool
    {
        return $this->estaProgramada();
    }

    public function puedeIniciarAtencion(): bool
    {
        return $this->estaConfirmada() && $this->hora_llegada;
    }

    public function necesitaRecordatorio24h(): bool
    {
        if ($this->recordatorio_24h_enviado || !$this->estaActiva()) {
            return false;
        }
        
        $horasRestantes = $this->faltanHoras();
        return $horasRestantes <= 24 && $horasRestantes > 23;
    }

    public function necesitaRecordatorio2h(): bool
    {
        if ($this->recordatorio_2h_enviado || !$this->estaActiva()) {
            return false;
        }
        
        $horasRestantes = $this->faltanHoras();
        return $horasRestantes <= 2 && $horasRestantes > 1;
    }

    /**
     * Métodos de acción
     */
    public function confirmar(int $userId = null): bool
    {
        if (!$this->puedeSerConfirmada()) {
            return false;
        }

        return $this->update([
            'estado' => self::ESTADO_CONFIRMADA,
            'modificado_por_user_id' => $userId
        ]);
    }

    public function cancelar(string $motivo, int $userId = null): bool
    {
        if (!$this->puedeSerCancelada()) {
            return false;
        }

        return $this->update([
            'estado' => self::ESTADO_CANCELADA,
            'motivo_cancelacion' => $motivo,
            'fecha_cancelacion' => now(),
            'cancelado_por_user_id' => $userId,
            'modificado_por_user_id' => $userId
        ]);
    }

    public function marcarLlegada(): bool
    {
        if (!$this->estaConfirmada()) {
            return false;
        }

        return $this->update([
            'hora_llegada' => now()
        ]);
    }

    public function iniciarAtencion(int $userId = null): bool
    {
        if (!$this->puedeIniciarAtencion()) {
            return false;
        }

        return $this->update([
            'estado' => self::ESTADO_EN_PROCESO,
            'hora_inicio_atencion' => now(),
            'modificado_por_user_id' => $userId
        ]);
    }

    public function completarAtencion(int $userId = null): bool
    {
        if (!$this->estaEnProceso()) {
            return false;
        }

        return $this->update([
            'estado' => self::ESTADO_COMPLETADA,
            'hora_fin_atencion' => now(),
            'modificado_por_user_id' => $userId
        ]);
    }

    public function marcarRecordatorio24hEnviado(): bool
    {
        return $this->update([
            'recordatorio_24h_enviado' => true,
            'fecha_recordatorio_24h' => now()
        ]);
    }

    public function marcarRecordatorio2hEnviado(): bool
    {
        return $this->update([
            'recordatorio_2h_enviado' => true,
            'fecha_recordatorio_2h' => now()
        ]);
    }

    public function tieneConsulta(): bool
{
    return $this->consulta !== null;
}

public function consultaCompletada(): bool
{
    return $this->tieneConsulta() && 
           $this->consulta->estado_consulta === Consulta::ESTADO_COMPLETADA;
}

public function puedeCrearConsulta(): bool
{
    return $this->estaCompletada() && !$this->tieneConsulta();
}

public function crearConsulta(array $datosConsulta, int $userId): ?Consulta
{
    if (!$this->puedeCrearConsulta()) {
        return null;
    }

    $datosBase = [
        'cita_id' => $this->id,
        'paciente_id' => $this->paciente_id,
        'veterinario_id' => $this->veterinario_id,
        'propietario_id' => $this->propietario_id,
        'fecha_hora' => $this->fecha_hora,
        'tipo_consulta' => $this->mapearTipoConsulta(),
        'creado_por_user_id' => $userId
    ];

    return Consulta::create(array_merge($datosBase, $datosConsulta));
}

private function mapearTipoConsulta(): string
{
    $mapeo = [
        self::TIPO_CONSULTA_GENERAL => Consulta::TIPO_CONSULTA_GENERAL,
        self::TIPO_EMERGENCIA => Consulta::TIPO_EMERGENCIA,
        self::TIPO_SEGUIMIENTO => Consulta::TIPO_SEGUIMIENTO,
        self::TIPO_CIRUGIA => Consulta::TIPO_CIRUGIA,
        self::TIPO_VACUNACION => Consulta::TIPO_VACUNACION,
        self::TIPO_REVISION => Consulta::TIPO_REVISION,
        self::TIPO_ESTETICA => Consulta::TIPO_ESTETICA
    ];

    return $mapeo[$this->tipo_cita] ?? Consulta::TIPO_CONSULTA_GENERAL;
}

public function getEstadoMedicoAttribute(): string
{
    if (!$this->tieneConsulta()) {
        return $this->estaCompletada() ? 'Pendiente consulta' : 'Sin atender';
    }

    $consulta = $this->consulta;
    
    switch ($consulta->estado_consulta) {
        case Consulta::ESTADO_EN_PROGRESO:
            return 'Consulta en progreso';
        case Consulta::ESTADO_COMPLETADA:
            return 'Consulta completada';
        case Consulta::ESTADO_APROBADA:
            return 'Consulta aprobada';
        case Consulta::ESTADO_REVISION:
            return 'En revisión médica';
        default:
            return 'Estado desconocido';
    }
}

public function confirmadoPor(): BelongsTo
{
    return $this->belongsTo(User::class, 'confirmado_por_user_id');
}

    /**
     * Constantes
     */
    public const ESTADO_PROGRAMADA = 'programada';
    public const ESTADO_CONFIRMADA = 'confirmada';
    public const ESTADO_EN_PROCESO = 'en_proceso';
    public const ESTADO_COMPLETADA = 'completada';
    public const ESTADO_CANCELADA = 'cancelada';
    public const ESTADO_NO_ASISTIO = 'no_asistio';

    public const TIPO_CONSULTA_GENERAL = 'consulta_general';
    public const TIPO_VACUNACION = 'vacunacion';
    public const TIPO_CIRUGIA = 'cirugia';
    public const TIPO_EMERGENCIA = 'emergencia';
    public const TIPO_SEGUIMIENTO = 'seguimiento';
    public const TIPO_REVISION = 'revision';
    public const TIPO_DESPARASITACION = 'desparasitacion';
    public const TIPO_ESTETICA = 'estetica';
    public const TIPO_OTRO = 'otro';

    public const PRIORIDAD_BAJA = 'baja';
    public const PRIORIDAD_NORMAL = 'normal';
    public const PRIORIDAD_ALTA = 'alta';
    public const PRIORIDAD_URGENTE = 'urgente';

    public const ESTADO_PAGO_PENDIENTE = 'pendiente';
    public const ESTADO_PAGO_PAGADO = 'pagado';
    public const ESTADO_PAGO_PARCIAL = 'parcial';
}