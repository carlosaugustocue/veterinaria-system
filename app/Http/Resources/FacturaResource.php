<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'cita' => new CitaResource($this->whenLoaded('cita')),
            'cliente' => new PropietarioResource($this->whenLoaded('cliente')),
            'veterinario' => new VeterinarioResource($this->whenLoaded('veterinario')),
            'servicios' => FacturaDetalleResource::collection($this->whenLoaded('detalles')),
            'subtotal' => $this->subtotal,
            'descuento' => $this->descuento,
            'impuestos' => $this->impuestos,
            'total' => $this->total,
            'metodo_pago' => $this->metodo_pago,
            'estado_pago' => $this->estado_pago,
            'fecha_pago' => $this->fecha_pago,
            'referencia_pago' => $this->referencia_pago,
            'observaciones' => $this->observaciones,
            'total_servicios' => $this->whenCounted('detalles'),
            'pagos' => PagoResource::collection($this->whenLoaded('pagos')),
            'metadatos' => $this->metadatos,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
