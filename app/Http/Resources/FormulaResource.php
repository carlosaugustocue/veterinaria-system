<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormulaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'consulta' => new ConsultaResource($this->whenLoaded('consulta')),
            'paciente' => new PacienteResource($this->whenLoaded('paciente')),
            'veterinario' => new VeterinarioResource($this->whenLoaded('veterinario')),
            'propietario' => new PropietarioResource($this->whenLoaded('propietario')),
            'numero_formula' => $this->numero_formula,
            'fecha_prescripcion' => $this->fecha_prescripcion,
            'diagnostico_resumido' => $this->diagnostico_resumido,
            'observaciones_generales' => $this->observaciones_generales,
            'instrucciones_especiales' => $this->instrucciones_especiales,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'estado_formula' => $this->estado_formula,
            'farmacia_sugerida' => $this->farmacia_sugerida,
            'costo_estimado' => $this->costo_estimado,
            'requiere_control' => $this->requiere_control,
            'dias_tratamiento' => $this->dias_tratamiento,
            'fecha_proximo_control' => $this->fecha_proximo_control,
            'notas_farmaceuticas' => $this->notas_farmaceuticas,
            'codigo_barras' => $this->codigo_barras,
            'hash_verificacion' => $this->hash_verificacion,
            'impresa' => $this->impresa,
            'fecha_impresion' => $this->fecha_impresion,
            'veces_impresa' => $this->veces_impresa,
            'entregada_propietario' => $this->entregada_propietario,
            'fecha_entrega' => $this->fecha_entrega,
            'recibido_por' => $this->recibido_por,
            'creada_por' => new UserResource($this->whenLoaded('creadaPor')),
            'verificada_por' => new UserResource($this->whenLoaded('verificadaPor')),
            'medicamentos' => FormulaMedicamentoResource::collection($this->whenLoaded('medicamentos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
