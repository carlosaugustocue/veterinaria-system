<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;
use App\Http\Resources\PacienteResource;
use Illuminate\Support\Facades\Validator;

class PacienteController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::with(['propietario', 'especie', 'raza'])->paginate();
        return PacienteResource::collection($pacientes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'especie_id' => 'required|exists:especies,id',
            'raza_id' => 'required|exists:razas,id',
            'propietario_id' => 'required|exists:propietarios,id',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|in:macho,hembra',
            'color' => 'required|string|max:100',
            'peso' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $paciente = Paciente::create($request->all());
        return new PacienteResource($paciente);
    }

    public function show(Paciente $paciente)
    {
        return new PacienteResource($paciente->load(['propietario', 'especie', 'raza', 'historial']));
    }

    public function update(Request $request, Paciente $paciente)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'especie_id' => 'sometimes|exists:especies,id',
            'raza_id' => 'sometimes|exists:razas,id',
            'propietario_id' => 'sometimes|exists:propietarios,id',
            'fecha_nacimiento' => 'sometimes|date',
            'sexo' => 'sometimes|in:macho,hembra',
            'color' => 'sometimes|string|max:100',
            'peso' => 'sometimes|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $paciente->update($request->all());
        return new PacienteResource($paciente);
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();
        return response()->json(['message' => 'Paciente eliminado correctamente']);
    }

    public function historial(Paciente $paciente)
    {
        return new PacienteResource($paciente->load(['consultas', 'vacunas']));
    }
}
