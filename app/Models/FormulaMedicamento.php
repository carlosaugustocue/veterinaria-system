<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaMedicamento extends Model
{
    use HasFactory;

    protected $table = 'formula_medicamentos';

    protected $fillable = [
        'formula_id',
        'nombre_medicamento',
        'principio_activo',
        'concentracion',
        'forma_farmaceutica',
        'dosis',
        'frecuencia',
        'duracion_tratamiento',
        'cantidad_total',
        'unidad_medida',
        'via_administracion',
        'instrucciones_uso',
        'contraindicaciones',
        'efectos_secundarios',
        'interacciones',
        'observaciones',
        'precio_unitario',
        'costo_total',
        'codigo_medicamento',
        'lote_medicamento',
        'fecha_vencimiento_med',
        'requiere_receta',
        'es_controlado',
        'orden_administracion'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'fecha_vencimiento_med' => 'date',
        'requiere_receta' => 'boolean',
        'es_controlado' => 'boolean'
    ];

    /**
     * Relaciones
     */
    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    /**
     * Atributos calculados
     */
    public function getDosisCompletaAttribute(): string
    {
        return $this->dosis . ' cada ' . $this->frecuencia . ' por ' . $this->duracion_tratamiento;
    }

    public function getInstruccionesCompletasAttribute(): string
    {
        $instrucciones = "Administrar {$this->dosis} {$this->via_administracion} cada {$this->frecuencia}";
        
        if ($this->duracion_tratamiento) {
            $instrucciones .= " durante {$this->duracion_tratamiento}";
        }
        
        if ($this->instrucciones_uso) {
            $instrucciones .= ". {$this->instrucciones_uso}";
        }
        
        return $instrucciones;
    }

    public function getMedicamentoCompletoAttribute(): string
    {
        $nombre = $this->nombre_medicamento;
        
        if ($this->concentracion) {
            $nombre .= " {$this->concentracion}";
        }
        
        if ($this->forma_farmaceutica) {
            $nombre .= " ({$this->forma_farmaceutica})";
        }
        
        return $nombre;
    }

    public function getTotalDosisAttribute(): string
    {
        return "Cantidad total: {$this->cantidad_total} {$this->unidad_medida}";
    }

    /**
     * Métodos de utilidad
     */
    public function esMedicamentoControlado(): bool
    {
        return $this->es_controlado;
    }

    public function requiereRecetaMedica(): bool
    {
        return $this->requiere_receta;
    }

    public function tieneContraindicaciones(): bool
    {
        return !empty($this->contraindicaciones);
    }

    public function tieneInteracciones(): bool
    {
        return !empty($this->interacciones);
    }

    /**
     * Calcular costo total basado en cantidad y precio unitario
     */
    public function calcularCostoTotal(): void
    {
        if ($this->cantidad_total && $this->precio_unitario) {
            $this->costo_total = $this->cantidad_total * $this->precio_unitario;
            $this->save();
        }
    }

    /**
     * Scopes
     */
    public function scopeControlados($query)
    {
        return $query->where('es_controlado', true);
    }

    public function scopeConReceta($query)
    {
        return $query->where('requiere_receta', true);
    }

    public function scopeByFormaFarmaceutica($query, string $forma)
    {
        return $query->where('forma_farmaceutica', $forma);
    }

    public function scopeOrderByAdministracion($query)
    {
        return $query->orderBy('orden_administracion');
    }

    /**
     * Constantes para formas farmacéuticas
     */
    public const FORMA_TABLETA = 'tableta';
    public const FORMA_CAPSULA = 'cápsula';
    public const FORMA_JARABE = 'jarabe';
    public const FORMA_SUSPENSION = 'suspensión';
    public const FORMA_INYECTABLE = 'inyectable';
    public const FORMA_CREMA = 'crema';
    public const FORMA_POMADA = 'pomada';
    public const FORMA_GOTAS = 'gotas';
    public const FORMA_SPRAY = 'spray';
    public const FORMA_POLVO = 'polvo';
    public const FORMA_SHAMPOO = 'shampoo';        // ⭐ NUEVO
    public const FORMA_LOCION = 'loción';          // ⭐ NUEVO
    public const FORMA_GEL = 'gel';                // ⭐ NUEVO
    public const FORMA_PARCHE = 'parche';          // ⭐ NUEVO
    public const FORMA_SUPOSITORIO = 'supositorio'; // ⭐ NUEVO
    public const FORMA_COLLAR = 'collar';          // ⭐ NUEVO
    public const FORMA_PIPETA = 'pipeta';          // ⭐ NUEVO
    public const FORMA_OTRO = 'otro';

    /**
     * Constantes para vías de administración
     */
    public const VIA_ORAL = 'oral';
    public const VIA_TOPICA = 'tópica';
    public const VIA_OCULAR = 'ocular';
    public const VIA_AUDITIVA = 'auditiva';
    public const VIA_SUBCUTANEA = 'subcutánea';
    public const VIA_INTRAMUSCULAR = 'intramuscular';
    public const VIA_INTRAVENOSA = 'intravenosa';
    public const VIA_RECTAL = 'rectal';

    /**
     * Constantes para unidades de medida
     */
    public const UNIDAD_MG = 'mg';
    public const UNIDAD_G = 'g';
    public const UNIDAD_ML = 'ml';
    public const UNIDAD_TABLETAS = 'tabletas';
    public const UNIDAD_CAPSULAS = 'cápsulas';
    public const UNIDAD_APLICACIONES = 'aplicaciones';
    public const UNIDAD_DOSIS = 'dosis';
}