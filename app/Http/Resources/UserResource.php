<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'cedula' => $this->cedula,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'sexo' => $this->sexo,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'role' => new RoleResource($this->whenLoaded('role')),
            'activo' => $this->activo,
            'ultimo_acceso' => $this->ultimo_acceso,
            'intentos_fallidos' => $this->intentos_fallidos,
            'bloqueado_hasta' => $this->bloqueado_hasta,
            'nombre_completo' => $this->nombre_completo,
            'propietario' => new PropietarioResource($this->whenLoaded('propietario')),
            'veterinario' => new VeterinarioResource($this->whenLoaded('veterinario')),
            'auxiliar' => new AuxiliarResource($this->whenLoaded('auxiliar')),
            'recepcionista' => new RecepcionistaResource($this->whenLoaded('recepcionista')),
            'perfil' => $this->perfil,
            'info_completa' => $this->info_completa,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
