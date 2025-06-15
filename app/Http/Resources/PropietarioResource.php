<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropietarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'ocupacion' => $this->ocupacion,
            'observaciones' => $this->observaciones,
            'preferencia_contacto' => $this->preferencia_contacto,
            'acepta_promociones' => $this->acepta_promociones,
            'acepta_recordatorios' => $this->acepta_recordatorios,
            'historial_credito' => $this->historial_credito,
            'limite_credito' => $this->limite_credito,
            'saldo_pendiente' => $this->saldo_pendiente,
            'contacto_emergencia_nombre' => $this->contacto_emergencia_nombre,
            'contacto_emergencia_telefono' => $this->contacto_emergencia_telefono,
            'contacto_emergencia_relacion' => $this->contacto_emergencia_relacion,
            'veterinario_preferido' => new VeterinarioResource($this->whenLoaded('veterinarioPreferido')),
            'horarios_preferidos' => $this->horarios_preferidos,
            'total_mascotas' => $this->total_mascotas,
            'total_citas' => $this->total_citas,
            'fecha_ultima_visita' => $this->fecha_ultima_visita,
            'pacientes' => PacienteResource::collection($this->whenLoaded('pacientes')),
            'citas' => CitaResource::collection($this->whenLoaded('citas')),
            'consultas' => ConsultaResource::collection($this->whenLoaded('consultas')),
            'nombre_completo' => $this->nombre_completo,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
