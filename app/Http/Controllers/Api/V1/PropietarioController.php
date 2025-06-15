<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Propietario;
use Illuminate\Http\Request;
use App\Http\Resources\PropietarioResource;
use Illuminate\Support\Facades\Validator;

class PropietarioController extends Controller
{
    public function index()
    {
        $propietarios = Propietario::with(['pacientes'])->paginate();
        return PropietarioResource::collection($propietarios);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'cedula' => 'required|string|unique:propietarios',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|unique:propietarios',
            'direccion' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $propietario = Propietario::create($request->all());
        return new PropietarioResource($propietario);
    }

    public function show(Propietario $propietario)
    {
        return new PropietarioResource($propietario->load(['pacientes']));
    }

    public function update(Request $request, Propietario $propietario)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'apellido' => 'sometimes|string|max:255',
            'cedula' => 'sometimes|string|unique:propietarios,cedula,' . $propietario->id,
            'telefono' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:propietarios,email,' . $propietario->id,
            'direccion' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $propietario->update($request->all());
        return new PropietarioResource($propietario);
    }

    public function destroy(Propietario $propietario)
    {
        $propietario->delete();
        return response()->json(['message' => 'Propietario eliminado correctamente']);
    }

    public function buscar(Request $request)
    {
        $termino = $request->get('q');
        $propietarios = Propietario::where('nombre', 'LIKE', "%{$termino}%")
            ->orWhere('apellido', 'LIKE', "%{$termino}%")
            ->orWhere('cedula', 'LIKE', "%{$termino}%")
            ->orWhere('email', 'LIKE', "%{$termino}%")
            ->with(['pacientes'])
            ->paginate();
        
        return PropietarioResource::collection($propietarios);
    }
}
