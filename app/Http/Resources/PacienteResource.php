<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PacienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'propietario' => new PropietarioResource($this->whenLoaded('propietario')),
            'especie' => new EspecieResource($this->whenLoaded('especie')),
            'raza' => new RazaResource($this->whenLoaded('raza')),
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'sexo' => $this->sexo,
            'peso' => $this->peso,
            'color' => $this->color,
            'senales_particulares' => $this->senales_particulares,
            'microchip' => $this->microchip,
            'numero_registro' => $this->numero_registro,
            'pedigree' => $this->pedigree,
            'estado' => $this->estado,
            'fecha_registro' => $this->fecha_registro,
            'observaciones_generales' => $this->observaciones_generales,
            'esterilizado' => $this->esterilizado,
            'fecha_esterilizacion' => $this->fecha_esterilizacion,
            'alergias_conocidas' => $this->alergias_conocidas,
            'medicamentos_cronicos' => $this->medicamentos_cronicos,
            'condiciones_medicas' => $this->condiciones_medicas,
            'nivel_actividad' => $this->nivel_actividad,
            'temperamento' => $this->temperamento,
            'foto_url' => $this->foto_url,
            'fotos_adicionales' => $this->fotos_adicionales,
            'criador_nombre' => $this->criador_nombre,
            'criador_contacto' => $this->criador_contacto,
            'fecha_adopcion' => $this->fecha_adopcion,
            'lugar_adopcion' => $this->lugar_adopcion,
            'fecha_ultima_consulta' => $this->fecha_ultima_consulta,
            'fecha_proxima_vacuna' => $this->fecha_proxima_vacuna,
            'fecha_proxima_desparasitacion' => $this->fecha_proxima_desparasitacion,
            'total_consultas' => $this->total_consultas,
            'seguro_compania' => $this->seguro_compania,
            'seguro_poliza' => $this->seguro_poliza,
            'seguro_vencimiento' => $this->seguro_vencimiento,
            'edad' => $this->edad,
            'edad_en_anos' => $this->edad_en_anos,
            'edad_en_meses' => $this->edad_en_meses,
            'nombre_completo' => $this->nombre_completo,
            'especie_raza' => $this->especie_raza,
            'fotos_adicionales_urls' => $this->fotos_adicionales_urls,
            'citas' => CitaResource::collection($this->whenLoaded('citas')),
            'consultas' => ConsultaResource::collection($this->whenLoaded('consultas')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
