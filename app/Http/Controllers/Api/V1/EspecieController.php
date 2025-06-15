<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Especie;
use App\Http\Resources\EspecieResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EspecieController extends Controller
{
    public function index()
    {
        $especies = Especie::with(['razas', 'pacientes'])->get();
        return EspecieResource::collection($especies);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'nombre_cientifico' => 'nullable|string|max:255',
            'icono' => 'nullable|string|max:255',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $especie = Especie::create($request->all());
        return new EspecieResource($especie);
    }

    public function show(Especie $especie)
    {
        return new EspecieResource($especie->load(['razas', 'pacientes']));
    }

    public function update(Request $request, Especie $especie)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'nombre_cientifico' => 'nullable|string|max:255',
            'icono' => 'nullable|string|max:255',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $especie->update($request->all());
        return new EspecieResource($especie);
    }

    public function destroy(Especie $especie)
    {
        $especie->delete();
        return response()->json(['message' => 'Especie eliminada correctamente']);
    }

    public function razas(Especie $especie)
    {
        return EspecieResource::collection($especie->razas);
    }

    public function pacientes(Especie $especie)
    {
        return EspecieResource::collection($especie->pacientes);
    }
}
