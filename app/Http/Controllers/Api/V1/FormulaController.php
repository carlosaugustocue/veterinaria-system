<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Formula;
use Illuminate\Http\Request;
use App\Http\Resources\FormulaResource;
use Illuminate\Support\Facades\Validator;

class FormulaController extends Controller
{
    public function index()
    {
        $formulas = Formula::with(['consulta', 'medicamentos'])->paginate();
        return FormulaResource::collection($formulas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consulta_id' => 'required|exists:consultas,id',
            'medicamentos' => 'required|array',
            'medicamentos.*.medicamento_id' => 'required|exists:medicamentos,id',
            'medicamentos.*.dosis' => 'required|string',
            'medicamentos.*.frecuencia' => 'required|string',
            'medicamentos.*.duracion' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $formula = Formula::create($request->all());
        return new FormulaResource($formula);
    }

    public function show(Formula $formula)
    {
        return new FormulaResource($formula->load(['consulta', 'medicamentos']));
    }

    public function update(Request $request, Formula $formula)
    {
        $validator = Validator::make($request->all(), [
            'medicamentos' => 'sometimes|array',
            'medicamentos.*.medicamento_id' => 'required|exists:medicamentos,id',
            'medicamentos.*.dosis' => 'required|string',
            'medicamentos.*.frecuencia' => 'required|string',
            'medicamentos.*.duracion' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $formula->update($request->all());
        return new FormulaResource($formula);
    }

    public function enviarPorEmail(Formula $formula)
    {
        try {
            $formula->enviarPorEmail();
            return response()->json(['message' => 'Fórmula enviada por email correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar la fórmula por email'], 500);
        }
    }

    public function enviarPorWhatsApp(Formula $formula)
    {
        try {
            $formula->enviarPorWhatsApp();
            return response()->json(['message' => 'Fórmula enviada por WhatsApp correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar la fórmula por WhatsApp'], 500);
        }
    }
}
