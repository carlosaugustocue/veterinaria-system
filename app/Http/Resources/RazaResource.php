<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RazaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'especie' => new EspecieResource($this->whenLoaded('especie')),
            'descripcion' => $this->descripcion,
            'tamano' => $this->tamano,
            'peso_promedio_min' => $this->peso_promedio_min,
            'peso_promedio_max' => $this->peso_promedio_max,
            'esperanza_vida_min' => $this->esperanza_vida_min,
            'esperanza_vida_max' => $this->esperanza_vida_max,
            'caracteristicas_especiales' => $this->caracteristicas_especiales,
            'cuidados_especiales' => $this->cuidados_especiales,
            'colores_comunes' => $this->colores_comunes,
            'origen_pais' => $this->origen_pais,
            'activo' => $this->activo,
            'pacientes' => PacienteResource::collection($this->whenLoaded('pacientes')),
            'nombre_completo' => $this->nombre_completo,
            'rango_peso' => $this->rango_peso,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
