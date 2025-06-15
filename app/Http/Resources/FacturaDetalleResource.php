<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'factura_id' => $this->factura_id,
            'servicio' => new ServicioResource($this->whenLoaded('servicio')),
            'descripcion' => $this->descripcion,
            'cantidad' => $this->cantidad,
            'precio_unitario' => $this->precio_unitario,
            'descuento' => $this->descuento,
            'impuesto' => $this->impuesto,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado,
            'metadatos' => $this->metadatos,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
