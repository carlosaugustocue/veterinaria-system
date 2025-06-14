<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use App\Models\Formula;

class Consulta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cita_id',
        'paciente_id',
        'veterinario_id',
        'propietario_id',
        'fecha_hora',
        'tipo_consulta',
        'motivo_consulta',
        'sintomas_reportados',
        'sintomas_observados',
        'signos_vitales',
        'examen_fisico',
        'comportamiento',
        'diagnostico_provisional',
        'diagnostico_definitivo',
        'diagnosticos_diferenciales',
        'tratamiento_realizado',
        'plan_tratamiento',
        'medicamentos_prescritos',
        'dosis_instrucciones',
        'procedimientos_realizados',
        'estudios_solicitados',
        'estudios_resultados',
        'recomendaciones_generales',
        'cuidados_especiales',
        'dieta_recomendada',
        'restricciones',
        'requiere_seguimiento',
        'dias_seguimiento',
        'motivo_seguimiento',
        'fecha_proximo_control',
        'estado_paciente',
        'pronostico',
        'observaciones_adicionales',
        'notas_internas',
        'archivos_adjuntos',
        'estado_consulta',
        'costo_consulta',
        'costo_procedimientos',
        'costo_medicamentos',
        'total_consulta',
        'duracion_minutos',
        'creado_por_user_id',
        'modificado_por_user_id',
        'fecha_aprobacion',
        'aprobado_por_user_id'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_proximo_control' => 'date',
        'fecha_aprobacion' => 'datetime',
        'signos_vitales' => 'array',
        'archivos_adjuntos' => 'array',
        'requiere_seguimiento' => 'boolean',
        'costo_consulta' => 'decimal:2',
        'costo_procedimientos' => 'decimal:2',
        'costo_medicamentos' => 'decimal:2',
        'total_consulta' => 'decimal:2'
    ];

    /**
     * Relaciones
     */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
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

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_user_id');
    }

    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por_user_id');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_user_id');
    }

    // Relación con seguimientos
    public function seguimientos(): HasMany
    {
        return $this->hasMany(Consulta::class, 'cita_id', 'cita_id')
                    ->where('tipo_consulta', self::TIPO_SEGUIMIENTO)
                    ->where('id', '!=', $this->id);
    }

    /**
     * Scopes de estado
     */
    public function scopeCompletadas($query)
    {
        return $query->where('estado_consulta', self::ESTADO_COMPLETADA);
    }

    public function scopeEnProgreso($query)
    {
        return $query->where('estado_consulta', self::ESTADO_EN_PROGRESO);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado_consulta', self::ESTADO_APROBADA);
    }

    public function scopeEnRevision($query)
    {
        return $query->where('estado_consulta', self::ESTADO_REVISION);
    }

    /**
     * Scopes de tipo
     */
    public function scopeConsultasGenerales($query)
    {
        return $query->where('tipo_consulta', self::TIPO_CONSULTA_GENERAL);
    }

    public function scopeEmergencias($query)
    {
        return $query->where('tipo_consulta', self::TIPO_EMERGENCIA);
    }

    public function scopeSeguimientos($query)
    {
        return $query->where('tipo_consulta', self::TIPO_SEGUIMIENTO);
    }

    public function scopeCirugias($query)
    {
        return $query->where('tipo_consulta', self::TIPO_CIRUGIA);
    }

    /**
     * Scopes de tiempo
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_hora', today());
    }

    public function scopeEstaSemanana($query)
    {
        return $query->whereBetween('fecha_hora', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeEstesMes($query)
    {
        return $query->whereMonth('fecha_hora', now()->month)
                    ->whereYear('fecha_hora', now()->year);
    }

    public function scopeByPaciente($query, int $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopeByVeterinario($query, int $veterinarioId)
    {
        return $query->where('veterinario_id', $veterinarioId);
    }

    public function scopeRequierenSeguimiento($query)
    {
        return $query->where('requiere_seguimiento', true)
                    ->whereNotNull('fecha_proximo_control');
    }

    public function scopeSeguimientosPendientes($query)
    {
        return $query->where('requiere_seguimiento', true)
                    ->where('fecha_proximo_control', '<=', now()->addDays(7))
                    ->whereDoesntHave('seguimientos');
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

    public function getEdadPacienteEnConsultaAttribute(): string
    {
        $edadEnConsulta = Carbon::parse($this->paciente->fecha_nacimiento)
                               ->diff($this->fecha_hora);
        
        if ($edadEnConsulta->y > 0) {
            return $edadEnConsulta->y . ' año' . ($edadEnConsulta->y > 1 ? 's' : '');
        } elseif ($edadEnConsulta->m > 0) {
            return $edadEnConsulta->m . ' mes' . ($edadEnConsulta->m > 1 ? 'es' : '');
        } else {
            return $edadEnConsulta->d . ' día' . ($edadEnConsulta->d > 1 ? 's' : '');
        }
    }

    public function getPesoEnConsultaAttribute(): ?float
    {
        return $this->signos_vitales['peso'] ?? $this->paciente->peso;
    }

    public function getTemperaturaAttribute(): ?float
    {
        return $this->signos_vitales['temperatura'] ?? null;
    }

    public function getFrecuenciaCardiacaAttribute(): ?int
    {
        return $this->signos_vitales['frecuencia_cardiaca'] ?? null;
    }

    public function getFrecuenciaRespiratoriaAttribute(): ?int
    {
        return $this->signos_vitales['frecuencia_respiratoria'] ?? null;
    }

    public function getDuracionFormateadaAttribute(): string
    {
        if (!$this->duracion_minutos) {
            return 'No registrada';
        }

        $horas = intval($this->duracion_minutos / 60);
        $minutos = $this->duracion_minutos % 60;

        if ($horas > 0) {
            return $horas . 'h ' . $minutos . 'min';
        }
        return $minutos . 'min';
    }

    public function getDiasDesdeConsultaAttribute(): int
    {
        return $this->fecha_hora->diffInDays(now());
    }

    /**
     * Métodos de utilidad - Estados
     */
    public function estaCompletada(): bool
    {
        return $this->estado_consulta === self::ESTADO_COMPLETADA;
    }

    public function estaEnProgreso(): bool
    {
        return $this->estado_consulta === self::ESTADO_EN_PROGRESO;
    }

    public function estaAprobada(): bool
    {
        return $this->estado_consulta === self::ESTADO_APROBADA;
    }

    public function estaEnRevision(): bool
    {
        return $this->estado_consulta === self::ESTADO_REVISION;
    }

    public function requiereSeguimiento(): bool
    {
        return $this->requiere_seguimiento;
    }

    public function tieneSeguimientoPendiente(): bool
    {
        return $this->requiere_seguimiento && 
               $this->fecha_proximo_control && 
               $this->fecha_proximo_control <= now()->addDays(7);
    }

    /**
     * Métodos de utilidad - Tipos
     */
    public function esConsultaGeneral(): bool
    {
        return $this->tipo_consulta === self::TIPO_CONSULTA_GENERAL;
    }

    public function esEmergencia(): bool
    {
        return $this->tipo_consulta === self::TIPO_EMERGENCIA;
    }

    public function esSeguimiento(): bool
    {
        return $this->tipo_consulta === self::TIPO_SEGUIMIENTO;
    }

    public function esCirugia(): bool
    {
        return $this->tipo_consulta === self::TIPO_CIRUGIA;
    }

    /**
     * Métodos de utilidad - Médicos
     */
    public function tieneDiagnostico(): bool
    {
        return !empty($this->diagnostico_definitivo) || !empty($this->diagnostico_provisional);
    }

    public function tieneTratamiento(): bool
    {
        return !empty($this->plan_tratamiento) || !empty($this->medicamentos_prescritos);
    }

    public function tieneSignosVitales(): bool
    {
        return !empty($this->signos_vitales);
    }

    public function tieneArchivosAdjuntos(): bool
    {
        return !empty($this->archivos_adjuntos);
    }

    /**
     * Métodos de acción
     */
    public function completar(int $userId = null): bool
    {
        return $this->update([
            'estado_consulta' => self::ESTADO_COMPLETADA,
            'modificado_por_user_id' => $userId
        ]);
    }

    public function aprobar(int $userId = null): bool
    {
        if (!$this->estaCompletada()) {
            return false;
        }

        return $this->update([
            'estado_consulta' => self::ESTADO_APROBADA,
            'fecha_aprobacion' => now(),
            'aprobado_por_user_id' => $userId
        ]);
    }

    public function enviarARevision(int $userId = null): bool
    {
        return $this->update([
            'estado_consulta' => self::ESTADO_REVISION,
            'modificado_por_user_id' => $userId
        ]);
    }

    public function programarSeguimiento(int $dias, string $motivo = null): bool
    {
        return $this->update([
            'requiere_seguimiento' => true,
            'dias_seguimiento' => $dias,
            'fecha_proximo_control' => now()->addDays($dias),
            'motivo_seguimiento' => $motivo
        ]);
    }

    public function actualizarSignosVitales(array $signos): bool
    {
        $signosActuales = $this->signos_vitales ?? [];
        $signosNuevos = array_merge($signosActuales, $signos);
        
        return $this->update(['signos_vitales' => $signosNuevos]);
    }

    public function agregarArchivo(string $archivo, string $tipo = 'imagen'): bool
    {
        $archivos = $this->archivos_adjuntos ?? [];
        
        $archivos[] = [
            'archivo' => $archivo,
            'tipo' => $tipo,
            'fecha' => now()->toDateTimeString()
        ];
        
        return $this->update(['archivos_adjuntos' => $archivos]);
    }

    /**
     * Métodos de estadísticas
     */
    public function getResumenMedico(): array
    {
        return [
            'consulta_id' => $this->id,
            'fecha' => $this->fecha_formateada,
            'tipo' => $this->tipo_consulta,
            'veterinario' => $this->veterinario->nombre_completo,
            'motivo' => $this->motivo_consulta,
            'diagnostico' => $this->diagnostico_definitivo ?? $this->diagnostico_provisional,
            'estado_paciente' => $this->estado_paciente,
            'pronostico' => $this->pronostico,
            'seguimiento' => $this->requiere_seguimiento,
            'duracion' => $this->duracion_formateada
        ];
    }

    public function formulas(): HasMany
{
    return $this->hasMany(Formula::class);
}

/**
 * Obtener la fórmula principal de la consulta
 */
public function formula(): HasOne
{
    return $this->hasOne(Formula::class);
}

/**
 * Verificar si la consulta tiene fórmulas
 */
public function tieneFormulas(): bool
{
    return $this->formulas()->exists();
}

/**
 * Verificar si la consulta tiene fórmula activa
 */
public function tieneFormulaActiva(): bool
{
    return $this->formulas()->activas()->exists();
}

/**
 * Obtener total de medicamentos prescritos
 */
public function getTotalMedicamentosAttribute(): int
{
    return $this->formulas()
        ->with('medicamentos')
        ->get()
        ->sum(function ($formula) {
            return $formula->medicamentos->count();
        });
}

/**
 * Verificar si puede crear fórmula
 */
public function puedeCrearFormula(): bool
{
    return $this->estaCompletada() && 
           ($this->plan_tratamiento || $this->medicamentos_prescritos);
}

/**
 * Crear fórmula médica desde la consulta
 */
public function crearFormula(array $datosFormula, array $medicamentos, int $userId): ?Formula
{
    if (!$this->puedeCrearFormula()) {
        return null;
    }

    // Datos base de la fórmula
    $datosBase = [
        'consulta_id' => $this->id,
        'paciente_id' => $this->paciente_id,
        'veterinario_id' => $this->veterinario_id,
        'propietario_id' => $this->propietario_id,
        'fecha_prescripcion' => $this->fecha_hora,
        'diagnostico_resumido' => $this->diagnostico_definitivo ?? $this->diagnostico_provisional,
        'creada_por_user_id' => $userId
    ];

    $formula = Formula::create(array_merge($datosBase, $datosFormula));

    // Agregar medicamentos
    foreach ($medicamentos as $index => $medicamento) {
        $medicamento['orden_administracion'] = $index + 1;
        $formula->agregarMedicamento($medicamento);
    }

    // Calcular costo total
    $formula->update(['costo_estimado' => $formula->calcularCostoTotal()]);

    return $formula;
}

/**
 * Obtener resumen de fórmulas
 */
public function getResumenFormulasAttribute(): array
{
    return $this->formulas->map(function ($formula) {
        return [
            'numero' => $formula->numero_formula,
            'fecha' => $formula->fecha_formateada,
            'medicamentos' => $formula->total_medicamentos,
            'estado' => $formula->estado_formula,
            'costo' => $formula->costo_estimado
        ];
    })->toArray();
}


    /**
     * Constantes
     */
    public const TIPO_CONSULTA_GENERAL = 'consulta_general';
    public const TIPO_EMERGENCIA = 'emergencia';
    public const TIPO_SEGUIMIENTO = 'seguimiento';
    public const TIPO_CIRUGIA = 'cirugia';
    public const TIPO_VACUNACION = 'vacunacion';
    public const TIPO_REVISION = 'revision';
    public const TIPO_ESTETICA = 'estetica';
    public const TIPO_DIAGNOSTICO = 'diagnostico';
    public const TIPO_OTRO = 'otro';

    public const ESTADO_EN_PROGRESO = 'en_progreso';
    public const ESTADO_COMPLETADA = 'completada';
    public const ESTADO_REVISION = 'revision';
    public const ESTADO_APROBADA = 'aprobada';

    public const ESTADO_PACIENTE_ESTABLE = 'estable';
    public const ESTADO_PACIENTE_MEJORADO = 'mejorado';
    public const ESTADO_PACIENTE_SIN_CAMBIOS = 'sin_cambios';
    public const ESTADO_PACIENTE_EMPEORADO = 'empeorado';
    public const ESTADO_PACIENTE_CRITICO = 'critico';
    public const ESTADO_PACIENTE_RECUPERADO = 'recuperado';

    public const PRONOSTICO_EXCELENTE = 'excelente';
    public const PRONOSTICO_BUENO = 'bueno';
    public const PRONOSTICO_RESERVADO = 'reservado';
    public const PRONOSTICO_MALO = 'malo';
    public const PRONOSTICO_GRAVE = 'grave';

    public const REQUIERE_FORMULA_SI = true;
    public const REQUIERE_FORMULA_NO = false;
}