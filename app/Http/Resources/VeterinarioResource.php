<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VeterinarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'licencia_medica' => $this->licencia_medica,
            'especialidad' => $this->especialidad,
            'certificaciones' => $this->certificaciones,
            'anos_experiencia' => $this->anos_experiencia,
            'horario_trabajo' => $this->horario_trabajo,
            'duracion_consulta' => $this->duracion_consulta,
            'max_citas_dia' => $this->max_citas_dia,
            'disponible_emergencias' => $this->disponible_emergencias,
            'tarifa_consulta' => $this->tarifa_consulta,
            'tarifa_emergencia' => $this->tarifa_emergencia,
            'observaciones' => $this->observaciones,
            'citas' => CitaResource::collection($this->whenLoaded('citas')),
            'propietarios_preferidos' => PropietarioResource::collection($this->whenLoaded('propietariosPreferidos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
