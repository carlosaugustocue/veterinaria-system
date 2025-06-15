<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Raza;
use App\Http\Resources\RazaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RazaController extends Controller
{
    public function index()
    {
        $razas = Raza::with(['especie', 'pacientes'])->get();
        return RazaResource::collection($razas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'especie_id' => 'required|exists:especies,id',
            'descripcion' => 'nullable|string',
            'tamano' => 'nullable|string|max:50',
            'peso_promedio_min' => 'nullable|numeric',
            'peso_promedio_max' => 'nullable|numeric',
            'esperanza_vida_min' => 'nullable|integer',
            'esperanza_vida_max' => 'nullable|integer',
            'caracteristicas_especiales' => 'nullable|string',
            'cuidados_especiales' => 'nullable|string',
            'colores_comunes' => 'nullable|array',
            'origen_pais' => 'nullable|string|max:100',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $raza = Raza::create($request->all());
        return new RazaResource($raza);
    }

    public function show(Raza $raza)
    {
        return new RazaResource($raza->load(['especie', 'pacientes']));
    }

    public function update(Request $request, Raza $raza)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'especie_id' => 'exists:especies,id',
            'descripcion' => 'nullable|string',
            'tamano' => 'nullable|string|max:50',
            'peso_promedio_min' => 'nullable|numeric',
            'peso_promedio_max' => 'nullable|numeric',
            'esperanza_vida_min' => 'nullable|integer',
            'esperanza_vida_max' => 'nullable|integer',
            'caracteristicas_especiales' => 'nullable|string',
            'cuidados_especiales' => 'nullable|string',
            'colores_comunes' => 'nullable|array',
            'origen_pais' => 'nullable|string|max:100',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $raza->update($request->all());
        return new RazaResource($raza);
    }

    public function destroy(Raza $raza)
    {
        $raza->delete();
        return response()->json(['message' => 'Raza eliminada correctamente']);
    }

    public function pacientes(Raza $raza)
    {
        return RazaResource::collection($raza->pacientes);
    }

    public function porEspecie($especieId)
    {
        $razas = Raza::where('especie_id', $especieId)
                    ->with(['especie', 'pacientes'])
                    ->get();
        return RazaResource::collection($razas);
    }
}
