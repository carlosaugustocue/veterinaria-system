<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'paciente' => new PacienteResource($this->whenLoaded('paciente')),
            'veterinario' => new VeterinarioResource($this->whenLoaded('veterinario')),
            'propietario' => new PropietarioResource($this->whenLoaded('propietario')),
            'fecha_hora' => $this->fecha_hora,
            'duracion_minutos' => $this->duracion_minutos,
            'tipo_cita' => $this->tipo_cita,
            'estado' => $this->estado,
            'motivo_consulta' => $this->motivo_consulta,
            'fecha_confirmacion' => $this->fecha_confirmacion,
            'confirmado_por' => new UserResource($this->whenLoaded('confirmadoPor')),
            'observaciones' => $this->observaciones,
            'sintomas_reportados' => $this->sintomas_reportados,
            'prioridad' => $this->prioridad,
            'hora_llegada' => $this->hora_llegada,
            'hora_inicio_atencion' => $this->hora_inicio_atencion,
            'hora_fin_atencion' => $this->hora_fin_atencion,
            'motivo_cancelacion' => $this->motivo_cancelacion,
            'cancelado_por' => new UserResource($this->whenLoaded('canceladoPor')),
            'fecha_cancelacion' => $this->fecha_cancelacion,
            'cita_origen' => new CitaResource($this->whenLoaded('citaOrigen')),
            'reprogramaciones' => CitaResource::collection($this->whenLoaded('reprogramaciones')),
            'consulta' => new ConsultaResource($this->whenLoaded('consulta')),
            'costo_consulta' => $this->costo_consulta,
            'costo_adicional' => $this->costo_adicional,
            'descuento' => $this->descuento,
            'total_pagado' => $this->total_pagado,
            'estado_pago' => $this->estado_pago,
            'recordatorio_24h_enviado' => $this->recordatorio_24h_enviado,
            'recordatorio_2h_enviado' => $this->recordatorio_2h_enviado,
            'fecha_recordatorio_24h' => $this->fecha_recordatorio_24h,
            'fecha_recordatorio_2h' => $this->fecha_recordatorio_2h,
            'creado_por' => new UserResource($this->whenLoaded('creadoPor')),
            'modificado_por' => new UserResource($this->whenLoaded('modificadoPor')),
            'metadatos' => $this->metadatos,
            'fecha_formateada' => $this->fecha_formateada,
            'fecha_solo' => $this->fecha_solo,
            'hora_sola' => $this->hora_sola,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
