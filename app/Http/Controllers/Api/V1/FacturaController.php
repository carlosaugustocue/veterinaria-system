<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use Illuminate\Http\Request;
use App\Http\Resources\FacturaResource;
use Illuminate\Support\Facades\Validator;

class FacturaController extends Controller
{
    public function index()
    {
        $facturas = Factura::with(['cita', 'cliente'])->paginate();
        return FacturaResource::collection($facturas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cita_id' => 'required|exists:citas,id',
            'cliente_id' => 'required|exists:propietarios,id',
            'servicios' => 'required|array',
            'servicios.*.servicio_id' => 'required|exists:servicios,id',
            'servicios.*.cantidad' => 'required|integer|min:1',
            'servicios.*.precio' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $factura = Factura::create($request->all());
        return new FacturaResource($factura);
    }

    public function show(Factura $factura)
    {
        return new FacturaResource($factura->load(['cita', 'cliente', 'detalles']));
    }

    public function update(Request $request, Factura $factura)
    {
        $validator = Validator::make($request->all(), [
            'servicios' => 'sometimes|array',
            'servicios.*.servicio_id' => 'required|exists:servicios,id',
            'servicios.*.cantidad' => 'required|integer|min:1',
            'servicios.*.precio' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'metodo_pago' => 'sometimes|in:efectivo,tarjeta,transferencia',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $factura->update($request->all());
        return new FacturaResource($factura);
    }

    public function procesarPago(Request $request, Factura $factura)
    {
        $validator = Validator::make($request->all(), [
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'monto' => 'required|numeric|min:0',
            'referencia' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $pago = $factura->procesarPago(
                $request->metodo_pago,
                $request->monto,
                $request->referencia
            );
            return response()->json(['pago' => $pago]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar el pago'], 500);
        }
    }

    public function generarPDF(Factura $factura)
    {
        try {
            $pdf = $factura->generarPDF();
            return response()->download($pdf);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }
    }
}
