<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cita' => new CitaResource($this->whenLoaded('cita')),
            'paciente' => new PacienteResource($this->whenLoaded('paciente')),
            'veterinario' => new VeterinarioResource($this->whenLoaded('veterinario')),
            'propietario' => new PropietarioResource($this->whenLoaded('propietario')),
            'fecha_hora' => $this->fecha_hora,
            'tipo_consulta' => $this->tipo_consulta,
            'motivo_consulta' => $this->motivo_consulta,
            'sintomas_reportados' => $this->sintomas_reportados,
            'sintomas_observados' => $this->sintomas_observados,
            'signos_vitales' => $this->signos_vitales,
            'examen_fisico' => $this->examen_fisico,
            'comportamiento' => $this->comportamiento,
            'diagnostico_provisional' => $this->diagnostico_provisional,
            'diagnostico_definitivo' => $this->diagnostico_definitivo,
            'diagnosticos_diferenciales' => $this->diagnosticos_diferenciales,
            'tratamiento_realizado' => $this->tratamiento_realizado,
            'plan_tratamiento' => $this->plan_tratamiento,
            'medicamentos_prescritos' => $this->medicamentos_prescritos,
            'dosis_instrucciones' => $this->dosis_instrucciones,
            'procedimientos_realizados' => $this->procedimientos_realizados,
            'estudios_solicitados' => $this->estudios_solicitados,
            'estudios_resultados' => $this->estudios_resultados,
            'recomendaciones_generales' => $this->recomendaciones_generales,
            'cuidados_especiales' => $this->cuidados_especiales,
            'dieta_recomendada' => $this->dieta_recomendada,
            'restricciones' => $this->restricciones,
            'requiere_seguimiento' => $this->requiere_seguimiento,
            'dias_seguimiento' => $this->dias_seguimiento,
            'motivo_seguimiento' => $this->motivo_seguimiento,
            'fecha_proximo_control' => $this->fecha_proximo_control,
            'estado_paciente' => $this->estado_paciente,
            'pronostico' => $this->pronostico,
            'observaciones_adicionales' => $this->observaciones_adicionales,
            'notas_internas' => $this->notas_internas,
            'archivos_adjuntos' => $this->archivos_adjuntos,
            'estado_consulta' => $this->estado_consulta,
            'costo_consulta' => $this->costo_consulta,
            'costo_procedimientos' => $this->costo_procedimientos,
            'costo_medicamentos' => $this->costo_medicamentos,
            'total_consulta' => $this->total_consulta,
            'duracion_minutos' => $this->duracion_minutos,
            'creado_por' => new UserResource($this->whenLoaded('creadoPor')),
            'modificado_por' => new UserResource($this->whenLoaded('modificadoPor')),
            'aprobado_por' => new UserResource($this->whenLoaded('aprobadoPor')),
            'seguimientos' => ConsultaResource::collection($this->whenLoaded('seguimientos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
