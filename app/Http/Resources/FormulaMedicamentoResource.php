<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormulaMedicamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'formula_id' => $this->formula_id,
            'nombre_medicamento' => $this->nombre_medicamento,
            'principio_activo' => $this->principio_activo,
            'concentracion' => $this->concentracion,
            'forma_farmaceutica' => $this->forma_farmaceutica,
            'dosis' => $this->dosis,
            'frecuencia' => $this->frecuencia,
            'duracion_tratamiento' => $this->duracion_tratamiento,
            'cantidad_total' => $this->cantidad_total,
            'unidad_medida' => $this->unidad_medida,
            'via_administracion' => $this->via_administracion,
            'instrucciones_uso' => $this->instrucciones_uso,
            'contraindicaciones' => $this->contraindicaciones,
            'efectos_secundarios' => $this->efectos_secundarios,
            'interacciones' => $this->interacciones,
            'observaciones' => $this->observaciones,
            'precio_unitario' => $this->precio_unitario,
            'costo_total' => $this->costo_total,
            'codigo_medicamento' => $this->codigo_medicamento,
            'lote_medicamento' => $this->lote_medicamento,
            'fecha_vencimiento_med' => $this->fecha_vencimiento_med,
            'requiere_receta' => $this->requiere_receta,
            'es_controlado' => $this->es_controlado,
            'orden_administracion' => $this->orden_administracion,
            'dosis_completa' => $this->dosis_completa,
            'instrucciones_completas' => $this->instrucciones_completas,
            'formula' => new FormulaResource($this->whenLoaded('formula')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
