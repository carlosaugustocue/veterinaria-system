<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EspecieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'nombre_cientifico' => $this->nombre_cientifico,
            'icono' => $this->icono,
            'activo' => $this->activo,
            'razas' => RazaResource::collection($this->whenLoaded('razas')),
            'pacientes' => PacienteResource::collection($this->whenLoaded('pacientes')),
            'razas_activas' => $this->when(isset($this->razas_activas), $this->razas_activas),
            'total_razas' => $this->when(isset($this->total_razas), $this->total_razas),
            'total_pacientes' => $this->when(isset($this->total_pacientes), $this->total_pacientes),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
