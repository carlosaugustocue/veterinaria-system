<?php
// ========== AuxiliarResource.php ==========
// Guardar en: app/Http/Resources/AuxiliarResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuxiliarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'area_trabajo' => $this->area_trabajo,
            'turno' => $this->turno,
            'fecha_ingreso' => $this->fecha_ingreso,
            'salario' => $this->salario,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}