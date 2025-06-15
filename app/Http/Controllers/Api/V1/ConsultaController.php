<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use Illuminate\Http\Request;
use App\Http\Resources\ConsultaResource;
use Illuminate\Support\Facades\Validator;

class ConsultaController extends Controller
{
    public function index()
    {
        $consultas = Consulta::with(['cita', 'paciente', 'veterinario'])->paginate();
        return ConsultaResource::collection($consultas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cita_id' => 'required|exists:citas,id',
            'motivo_consulta' => 'required|string',
            'sintomas' => 'required|array',
            'sintomas.*' => 'required|string',
            'diagnostico' => 'required|string',
            'tratamiento' => 'required|string',
            'observaciones' => 'nullable|string',
            'proxima_cita' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $consulta = Consulta::create($request->all());
        return new ConsultaResource($consulta);
    }

    public function show(Consulta $consulta)
    {
        return new ConsultaResource($consulta->load(['cita', 'paciente', 'veterinario', 'formulas']));
    }

    public function update(Request $request, Consulta $consulta)
    {
        $validator = Validator::make($request->all(), [
            'motivo_consulta' => 'sometimes|string',
            'sintomas' => 'sometimes|array',
            'sintomas.*' => 'required|string',
            'diagnostico' => 'sometimes|string',
            'tratamiento' => 'sometimes|string',
            'observaciones' => 'nullable|string',
            'proxima_cita' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $consulta->update($request->all());
        return new ConsultaResource($consulta);
    }

    public function registrarSintomas(Request $request, Consulta $consulta)
    {
        $validator = Validator::make($request->all(), [
            'sintomas' => 'required|array',
            'sintomas.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sintomas = $consulta->registrarSintomas($request->sintomas);
        return response()->json(['sintomas' => $sintomas]);
    }

    public function registrarDiagnostico(Request $request, Consulta $consulta)
    {
        $validator = Validator::make($request->all(), [
            'diagnostico_id' => 'required|exists:diagnosticos,id',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $diagnostico = $consulta->registrarDiagnostico(
            $request->diagnostico_id,
            $request->observaciones
        );
        return response()->json(['diagnostico' => $diagnostico]);
    }
}
