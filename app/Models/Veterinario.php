<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Models\Cita;
use App\Models\Formula;
use App\Models\FormulaMedicamento;

class Veterinario extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'licencia_medica',
        'especialidad',
        'certificaciones',
        'anos_experiencia',
        'horario_trabajo',
        'duracion_consulta',
        'max_citas_dia',
        'disponible_emergencias',
        'tarifa_consulta',
        'tarifa_emergencia',
        'observaciones'
    ];

    protected $casts = [
        'horario_trabajo' => 'array',
        'disponible_emergencias' => 'boolean',
        'tarifa_consulta' => 'decimal:2',
        'tarifa_emergencia' => 'decimal:2'
    ];

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function propietariosPreferidos(): HasMany
    {
        return $this->hasMany(Propietario::class, 'veterinario_preferido_id');
    }

    // Relación con consultas (cuando se implemente)
    // public function consultas(): HasMany
    // {
    //     return $this->hasMany(Consulta::class);
    // }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('activo', true);
        });
    }

    public function scopeDisponibleEmergencias($query)
    {
        return $query->where('disponible_emergencias', true);
    }

    public function scopePorEspecialidad($query, string $especialidad)
    {
        return $query->where('especialidad', 'like', "%{$especialidad}%");
    }

    /**
     * Atributos calculados
     */
    public function getNombreCompletoAttribute(): string
    {
        return 'Dr. ' . $this->user->nombre_completo;
    }

    public function getTelefonoAttribute(): string
    {
        return $this->user->telefono;
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    /**
     * Métodos de disponibilidad
     */
    public function estaDisponibleEn(Carbon $fechaHora, int $duracionMinutos = 30): bool
    {
        // Verificar si el día está en el horario de trabajo
        if (!$this->trabajaEn($fechaHora)) {
            return false;
        }

        // Verificar si el horario está dentro del rango de trabajo
        if (!$this->horarioEstaDisponible($fechaHora)) {
            return false;
        }

        // Verificar si no tiene citas programadas en ese horario
        return !$this->tieneCitaEn($fechaHora, $duracionMinutos);
    }

    public function trabajaEn(Carbon $fecha): bool
    {
        $diaSemana = strtolower($fecha->format('l')); // monday, tuesday, etc.
        $diasEspanol = [
            'monday' => 'lunes',
            'tuesday' => 'martes', 
            'wednesday' => 'miercoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo'
        ];

        $dia = $diasEspanol[$diaSemana] ?? $diaSemana;
        
        return isset($this->horario_trabajo[$dia]) && 
               !empty($this->horario_trabajo[$dia]);
    }

    public function horarioEstaDisponible(Carbon $fechaHora): bool
    {
        $diaSemana = strtolower($fechaHora->format('l'));
        $diasEspanol = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miercoles', 
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo'
        ];

        $dia = $diasEspanol[$diaSemana] ?? $diaSemana;
        
        if (!isset($this->horario_trabajo[$dia])) {
            return false;
        }

        $horario = $this->horario_trabajo[$dia];
        if (!is_array($horario) || count($horario) < 2) {
            return false;
        }

        $horaInicio = Carbon::createFromFormat('H:i', $horario[0], $fechaHora->timezone);
        $horaFin = Carbon::createFromFormat('H:i', $horario[1], $fechaHora->timezone);
        
        // Establecer la misma fecha
        $horaInicio->setDate($fechaHora->year, $fechaHora->month, $fechaHora->day);
        $horaFin->setDate($fechaHora->year, $fechaHora->month, $fechaHora->day);

        return $fechaHora->between($horaInicio, $horaFin->subMinutes($this->duracion_consulta));
    }

    public function tieneCitaEn(Carbon $fechaHora, int $duracionMinutos = 30): bool
    {
        $horaFin = $fechaHora->copy()->addMinutes($duracionMinutos);

        return $this->citas()
            ->activas()
            ->where(function ($query) use ($fechaHora, $horaFin) {
                $query->whereBetween('fecha_hora', [$fechaHora, $horaFin])
                      ->orWhere(function ($q) use ($fechaHora) {
                          $q->where('fecha_hora', '<=', $fechaHora)
                            ->whereRaw('DATE_ADD(fecha_hora, INTERVAL duracion_minutos MINUTE) > ?', [$fechaHora]);
                      });
            })
            ->exists();
    }

    /**
     * Métodos de horarios disponibles
     */
    public function getHorariosDisponiblesEnFecha(Carbon $fecha): array
    {
        if (!$this->trabajaEn($fecha)) {
            return [];
        }

        $diaSemana = strtolower($fecha->format('l'));
        $diasEspanol = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miercoles',
            'thursday' => 'jueves', 
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo'
        ];

        $dia = $diasEspanol[$diaSemana] ?? $diaSemana;
        $horario = $this->horario_trabajo[$dia];

        if (!is_array($horario) || count($horario) < 2) {
            return [];
        }

        $horaInicio = Carbon::createFromFormat('Y-m-d H:i', $fecha->format('Y-m-d') . ' ' . $horario[0]);
        $horaFin = Carbon::createFromFormat('Y-m-d H:i', $fecha->format('Y-m-d') . ' ' . $horario[1]);

        $horariosDisponibles = [];
        $horaActual = $horaInicio->copy();

        while ($horaActual->addMinutes($this->duracion_consulta)->lte($horaFin)) {
            if ($this->estaDisponibleEn($horaActual, $this->duracion_consulta)) {
                $horariosDisponibles[] = $horaActual->format('H:i');
            }
            $horaActual->addMinutes($this->duracion_consulta);
        }

        return $horariosDisponibles;
    }

    public function getProximosHorariosDisponibles(int $dias = 7): array
    {
        $horariosDisponibles = [];
        $fechaActual = now()->startOfDay();

        for ($i = 0; $i < $dias; $i++) {
            $fecha = $fechaActual->copy()->addDays($i);
            $horarios = $this->getHorariosDisponiblesEnFecha($fecha);
            
            if (!empty($horarios)) {
                $horariosDisponibles[$fecha->format('Y-m-d')] = [
                    'fecha' => $fecha->format('d/m/Y'),
                    'dia_semana' => $fecha->translatedFormat('l'),
                    'horarios' => $horarios
                ];
            }
        }

        return $horariosDisponibles;
    }

    /**
     * Estadísticas
     */
    public function getCitasHoyAttribute()
    {
        return $this->citas()->hoy()->activas()->count();
    }

    public function getCitasEstaSemannaAttribute()
    {
        return $this->citas()->estaSemanana()->activas()->count();
    }

    public function getProximaCitaAttribute()
    {
        return $this->citas()
            ->activas()
            ->where('fecha_hora', '>', now())
            ->orderBy('fecha_hora')
            ->first();
    }

    public function getCitasCompletadasMesAttribute()
    {
        return $this->citas()
            ->completadas()
            ->whereMonth('fecha_hora', now()->month)
            ->whereYear('fecha_hora', now()->year)
            ->count();
    }

    /**
     * Métodos de utilidad
     */
    public function puedeAtenderEmergencias(): bool
    {
        return $this->disponible_emergencias;
    }

    public function getTarifaPorTipo(string $tipoCita): float
    {
        switch ($tipoCita) {
            case Cita::TIPO_EMERGENCIA:
                return $this->tarifa_emergencia ?? $this->tarifa_consulta * 1.5;
            case Cita::TIPO_CIRUGIA:
                return $this->tarifa_consulta * 2;
            case Cita::TIPO_CONSULTA_GENERAL:
            default:
                return $this->tarifa_consulta ?? 50000;
        }
    }

    public function getDuracionPorTipo(string $tipoCita): int
    {
        switch ($tipoCita) {
            case Cita::TIPO_CIRUGIA:
                return 120; // 2 horas
            case Cita::TIPO_EMERGENCIA:
                return 45;
            case Cita::TIPO_VACUNACION:
                return 15;
            case Cita::TIPO_REVISION:
                return 20;
            case Cita::TIPO_CONSULTA_GENERAL:
            default:
                return $this->duracion_consulta;
        }
    }

    /**
 * Relación con fórmulas médicas
 */
public function formulas(): HasMany
{
    return $this->hasMany(Formula::class);
}

/**
 * Obtener fórmulas activas del veterinario
 */
public function formulasActivas(): HasMany
{
    return $this->hasMany(Formula::class)->activas();
}

/**
 * Estadísticas de fórmulas
 */
public function getFormulasHoyAttribute()
{
    return $this->formulas()->hoy()->count();
}

public function getFormulasEstaSemannaAttribute()
{
    return $this->formulas()->estaSemanana()->count();
}

public function getFormulasRequierenControlAttribute()
{
    return $this->formulas()->requierenControl()->count();
}

public function getTotalFormulasAttribute(): int
{
    return $this->formulas()->count();
}

public function getFormulasActivasCountAttribute(): int
{
    return $this->formulasActivas()->count();
}

/**
 * Obtener medicamentos más prescritos por el veterinario
 */
public function getMedicamentosMasPrescritosAttribute(): array
{
    return FormulaMedicamento::whereHas('formula', function ($q) {
        $q->where('veterinario_id', $this->id);
    })
    ->selectRaw('nombre_medicamento, COUNT(*) as total')
    ->groupBy('nombre_medicamento')
    ->orderByDesc('total')
    ->limit(5)
    ->pluck('total', 'nombre_medicamento')
    ->toArray();
}

/**
 * Obtener valor total de fórmulas prescritas
 */
public function getValorTotalFormulasAttribute(): float
{
    return $this->formulas()->sum('costo_estimado') ?? 0;
}

/**
 * Verificar si puede crear fórmulas
 */
public function puedeCrearFormulas(): bool
{
    return $this->user->isActive() && $this->user->hasPermission('prescripciones', 'crear');
}

/**
 * Obtener estadísticas médicas del veterinario
 */
public function getEstadisticasMedicasAttribute(): array
{
    return [
        'total_consultas' => $this->consultas()->count() ?? 0,
        'total_formulas' => $this->total_formulas,
        'formulas_activas' => $this->formulas_activas_count,
        'controles_pendientes' => $this->formulas_requieren_control,
        'valor_total_prescripciones' => $this->valor_total_formulas,
        'medicamentos_favoritos' => $this->medicamentos_mas_prescritos
    ];
}

/**
 * Crear fórmula médica
 */
public function crearFormula(Consulta $consulta, array $datosFormula, array $medicamentos, int $userId): Formula
{
    // Datos base
    $datosBase = [
        'consulta_id' => $consulta->id,
        'paciente_id' => $consulta->paciente_id,
        'veterinario_id' => $this->id,
        'propietario_id' => $consulta->propietario_id,
        'fecha_prescripcion' => now(),
        'creada_por_user_id' => $userId
    ];

    $formula = Formula::create(array_merge($datosBase, $datosFormula));

    // Agregar medicamentos
    $costoTotal = 0;
    foreach ($medicamentos as $index => $medicamento) {
        $medicamento['formula_id'] = $formula->id;
        $medicamento['orden_administracion'] = $index + 1;
        
        if (isset($medicamento['cantidad_total']) && isset($medicamento['precio_unitario'])) {
            $medicamento['costo_total'] = $medicamento['cantidad_total'] * $medicamento['precio_unitario'];
            $costoTotal += $medicamento['costo_total'];
        }

        FormulaMedicamento::create($medicamento);
    }

    // Actualizar costo total
    $formula->update(['costo_estimado' => $costoTotal]);

    return $formula;
}



    /**
     * Constantes
     */
    public const ESPECIALIDAD_GENERAL = 'Medicina General';
    public const ESPECIALIDAD_CIRUGIA = 'Cirugía Veterinaria';
    public const ESPECIALIDAD_CARDIOLOGIA = 'Cardiología Veterinaria';
    public const ESPECIALIDAD_DERMATOLOGIA = 'Dermatología Veterinaria';
    public const ESPECIALIDAD_OFTALMOLOGIA = 'Oftalmología Veterinaria';
    public const ESPECIALIDAD_EXOTICOS = 'Animales Exóticos';
}