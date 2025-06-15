<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Veterinario;
use App\Http\Resources\VeterinarioResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VeterinarioController extends Controller
{
    public function index()
    {
        $veterinarios = Veterinario::with(['user', 'citas', 'propietariosPreferidos'])->get();
        return VeterinarioResource::collection($veterinarios);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'licencia_medica' => 'required|string|max:255',
            'especialidad' => 'required|string|max:255',
            'certificaciones' => 'nullable|array',
            'anos_experiencia' => 'nullable|integer',
            'horario_trabajo' => 'nullable|array',
            'duracion_consulta' => 'nullable|integer',
            'max_citas_dia' => 'nullable|integer',
            'disponible_emergencias' => 'boolean',
            'tarifa_consulta' => 'nullable|numeric',
            'tarifa_emergencia' => 'nullable|numeric',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $veterinario = Veterinario::create($request->all());
        return new VeterinarioResource($veterinario);
    }

    public function show(Veterinario $veterinario)
    {
        return new VeterinarioResource($veterinario->load(['user', 'citas', 'propietariosPreferidos']));
    }

    public function update(Request $request, Veterinario $veterinario)
    {
        $validator = Validator::make($request->all(), [
            'licencia_medica' => 'string|max:255',
            'especialidad' => 'string|max:255',
            'certificaciones' => 'nullable|array',
            'anos_experiencia' => 'nullable|integer',
            'horario_trabajo' => 'nullable|array',
            'duracion_consulta' => 'nullable|integer',
            'max_citas_dia' => 'nullable|integer',
            'disponible_emergencias' => 'boolean',
            'tarifa_consulta' => 'nullable|numeric',
            'tarifa_emergencia' => 'nullable|numeric',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $veterinario->update($request->all());
        return new VeterinarioResource($veterinario);
    }

    public function destroy(Veterinario $veterinario)
    {
        $veterinario->delete();
        return response()->json(['message' => 'Veterinario eliminado correctamente']);
    }

    public function citas(Veterinario $veterinario)
    {
        return VeterinarioResource::collection($veterinario->citas);
    }

    public function propietariosPreferidos(Veterinario $veterinario)
    {
        return VeterinarioResource::collection($veterinario->propietariosPreferidos);
    }
}
