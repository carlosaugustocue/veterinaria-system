<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\FormulaMedicamento;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Veterinario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FormulaController extends Controller
{
    /**
     * Constructor - Middleware de autenticación
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Listar fórmulas con filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Formula::with([
                'paciente:id,nombre,propietario_id',
                'paciente.propietario.user:id,nombre,apellido',
                'veterinario.user:id,nombre,apellido',
                'consulta:id,fecha_hora,diagnostico_definitivo',
                'medicamentos:id,formula_id,nombre_medicamento,dosis,frecuencia'
            ]);

            // Filtros
            if ($request->filled('veterinario_id')) {
                $query->where('veterinario_id', $request->veterinario_id);
            }

            if ($request->filled('paciente_id')) {
                $query->where('paciente_id', $request->paciente_id);
            }

            if ($request->filled('propietario_id')) {
                $query->where('propietario_id', $request->propietario_id);
            }

            if ($request->filled('estado')) {
                $query->where('estado_formula', $request->estado);
            }

            if ($request->filled('numero_formula')) {
                $query->where('numero_formula', 'like', '%' . $request->numero_formula . '%');
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_prescripcion', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_prescripcion', '<=', $request->fecha_hasta);
            }

            if ($request->boolean('requiere_control')) {
                $query->where('requiere_control', true);
            }

            if ($request->boolean('vencidas')) {
                $query->vencidas();
            }

            if ($request->boolean('proximas_vencer')) {
                $query->proximasAVencer(7);
            }

            // Autorización por rol
            $user = Auth::user();
            if ($user->hasRole('cliente')) {
                $query->where('propietario_id', $user->propietario->id ?? 0);
            } elseif ($user->hasRole('veterinario')) {
                $query->where('veterinario_id', $user->veterinario->id ?? 0);
            }

            // Ordenamiento
            $sortField = $request->get('sort', 'fecha_prescripcion');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $formulas = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Fórmulas obtenidas exitosamente',
                'data' => $formulas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener fórmulas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva fórmula médica
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'consulta_id' => 'required|exists:consultas,id',
                'diagnostico_resumido' => 'required|string|max:500',
                'observaciones_generales' => 'nullable|string',
                'instrucciones_especiales' => 'nullable|string',
                'requiere_control' => 'boolean',
                'dias_tratamiento' => 'nullable|integer|min:1|max:365',
                'farmacia_sugerida' => 'nullable|string|max:200',
                'medicamentos' => 'required|array|min:1',
                'medicamentos.*.nombre_medicamento' => 'required|string|max:200',
                'medicamentos.*.dosis' => 'required|string|max:100',
                'medicamentos.*.frecuencia' => 'required|string|max:100',
                'medicamentos.*.duracion_tratamiento' => 'required|string|max:100',
                'medicamentos.*.cantidad_total' => 'required|numeric|min:0.01',
                'medicamentos.*.via_administracion' => 'required|in:oral,tópica,ocular,auditiva,subcutánea,intramuscular,intravenosa,rectal,otra',
                'medicamentos.*.precio_unitario' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Verificar que la consulta existe y puede tener fórmula
            $consulta = Consulta::with(['paciente', 'veterinario', 'propietario'])->findOrFail($request->consulta_id);
            
            if (!$consulta->puedeCrearFormula()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta consulta no puede tener fórmulas'
                ], 422);
            }

            // Verificar autorización
            $user = Auth::user();
            if ($user->hasRole('veterinario') && $consulta->veterinario_id !== $user->veterinario->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes autorización para crear fórmulas para esta consulta'
                ], 403);
            }

            // Crear fórmula
            $formulaData = [
                'consulta_id' => $consulta->id,
                'paciente_id' => $consulta->paciente_id,
                'veterinario_id' => $consulta->veterinario_id,
                'propietario_id' => $consulta->propietario_id,
                'fecha_prescripcion' => now(),
                'diagnostico_resumido' => $request->diagnostico_resumido,
                'observaciones_generales' => $request->observaciones_generales,
                'instrucciones_especiales' => $request->instrucciones_especiales,
                'requiere_control' => $request->boolean('requiere_control'),
                'farmacia_sugerida' => $request->farmacia_sugerida,
                'creada_por_user_id' => $user->id
            ];

            // Configurar fechas de control
            if ($request->boolean('requiere_control') && $request->filled('dias_tratamiento')) {
                $formulaData['dias_tratamiento'] = $request->dias_tratamiento;
                $formulaData['fecha_proximo_control'] = now()->addDays($request->dias_tratamiento);
            }

            // Fecha de vencimiento (30 días por defecto)
            $formulaData['fecha_vencimiento'] = now()->addDays(30);

            $formula = Formula::create($formulaData);

            // Agregar medicamentos
            $costoTotal = 0;
            foreach ($request->medicamentos as $index => $medicamentoData) {
                $medicamentoData['formula_id'] = $formula->id;
                $medicamentoData['orden_administracion'] = $index + 1;

                // Calcular costo si se proporciona
                if (isset($medicamentoData['precio_unitario']) && isset($medicamentoData['cantidad_total'])) {
                    $medicamentoData['costo_total'] = $medicamentoData['cantidad_total'] * $medicamentoData['precio_unitario'];
                    $costoTotal += $medicamentoData['costo_total'];
                }

                FormulaMedicamento::create($medicamentoData);
            }

            // Actualizar costo total
            $formula->update(['costo_estimado' => $costoTotal]);

            DB::commit();

            // Cargar relaciones para respuesta
            $formula->load([
                'paciente:id,nombre',
                'veterinario.user:id,nombre,apellido',
                'medicamentos'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fórmula médica creada exitosamente',
                'data' => [
                    'formula' => $formula,
                    'resumen' => $formula->resumen
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear fórmula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar fórmula específica
     */
    public function show($id): JsonResponse
    {
        try {
            $formula = Formula::with([
                'paciente:id,nombre,fecha_nacimiento,peso',
                'paciente.especie:id,nombre',
                'paciente.raza:id,nombre',
                'paciente.propietario.user:id,nombre,apellido,telefono',
                'veterinario.user:id,nombre,apellido',
                'veterinario:id,user_id,licencia_medica,especialidad',
                'consulta:id,fecha_hora,diagnostico_definitivo,plan_tratamiento',
                'medicamentos' => function($query) {
                    $query->orderBy('orden_administracion');
                },
                'creadaPor:id,nombre,apellido'
            ])->findOrFail($id);

            // Verificar autorización
            $user = Auth::user();
            if ($user->hasRole('cliente') && $formula->propietario_id !== $user->propietario->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes autorización para ver esta fórmula'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fórmula obtenida exitosamente',
                'data' => [
                    'formula' => $formula,
                    'resumen' => $formula->resumen,
                    'estadisticas' => [
                        'total_medicamentos' => $formula->total_medicamentos,
                        'dias_vigencia' => $formula->dias_vigencia,
                        'esta_activa' => $formula->estaActiva(),
                        'fue_impresa' => $formula->fueImpresa(),
                        'fue_entregada' => $formula->fueEntregada()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener fórmula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar fórmula como impresa
     */
    public function marcarImpresa($id): JsonResponse
    {
        try {
            $formula = Formula::findOrFail($id);

            // Verificar autorización
            $user = Auth::user();
            if (!$user->hasPermission('prescripciones', 'actualizar')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes autorización para imprimir fórmulas'
                ], 403);
            }

            $formula->marcarComoImpresa($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Fórmula marcada como impresa',
                'data' => [
                    'fecha_impresion' => $formula->fecha_impresion,
                    'veces_impresa' => $formula->veces_impresa
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar fórmula como impresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar fórmula como entregada
     */
    public function marcarEntregada(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'recibido_por' => 'required|string|max:200'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $formula = Formula::findOrFail($id);

            // Verificar autorización
            $user = Auth::user();
            if (!$user->hasPermission('prescripciones', 'actualizar')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes autorización para entregar fórmulas'
                ], 403);
            }

            $formula->marcarComoEntregada($request->recibido_por, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Fórmula marcada como entregada',
                'data' => [
                    'fecha_entrega' => $formula->fecha_entrega,
                    'recibido_por' => $formula->recibido_por
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar fórmula como entregada: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar fórmula
     */
    public function cancelar(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'motivo' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $formula = Formula::findOrFail($id);

            // Verificar autorización
            $user = Auth::user();
            if ($user->hasRole('veterinario') && $formula->veterinario_id !== $user->veterinario->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo el veterinario que prescribió puede cancelar esta fórmula'
                ], 403);
            }

            if (!$formula->estaActiva()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar fórmulas activas'
                ], 422);
            }

            $formula->cancelar($request->motivo, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Fórmula cancelada exitosamente',
                'data' => [
                    'estado' => $formula->estado_formula,
                    'motivo' => $request->motivo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar fórmula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de fórmulas
     */
    public function estadisticas(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Formula::query();

            // Filtrar por rol
            if ($user->hasRole('veterinario')) {
                $query->where('veterinario_id', $user->veterinario->id);
            } elseif ($user->hasRole('cliente')) {
                $query->where('propietario_id', $user->propietario->id);
            }

            // Filtros de fecha
            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_prescripcion', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_prescripcion', '<=', $request->fecha_hasta);
            }

            $estadisticas = [
                'totales' => [
                    'total_formulas' => $query->count(),
                    'activas' => $query->clone()->activas()->count(),
                    'vencidas' => $query->clone()->vencidas()->count(),
                    'canceladas' => $query->clone()->where('estado_formula', 'cancelada')->count(),
                    'impresas' => $query->clone()->where('impresa', true)->count(),
                    'entregadas' => $query->clone()->where('entregada_propietario', true)->count()
                ],
                'controles' => [
                    'requieren_control' => $query->clone()->requierenControl()->count(),
                    'proximas_vencer' => $query->clone()->proximasAVencer(7)->count()
                ],
                'financiero' => [
                    'valor_total' => $query->sum('costo_estimado') ?? 0,
                    'valor_promedio' => $query->avg('costo_estimado') ?? 0
                ],
                'periodo' => [
                    'hoy' => $query->clone()->hoy()->count(),
                    'esta_semana' => $query->clone()->estaSemanana()->count(),
                    'este_mes' => $query->clone()->whereMonth('fecha_prescripcion', now()->month)->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar hash de verificación
     */
    public function validarHash(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'numero_formula' => 'required|string',
                'hash' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $esValido = Formula::validarHash($request->numero_formula, $request->hash);

            if ($esValido) {
                $formula = Formula::where('numero_formula', $request->numero_formula)->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Fórmula válida',
                    'data' => [
                        'valida' => true,
                        'formula' => [
                            'numero' => $formula->numero_formula,
                            'fecha' => $formula->fecha_formateada,
                            'paciente' => $formula->paciente->nombre,
                            'veterinario' => $formula->veterinario_completo,
                            'estado' => $formula->estado_formula
                        ]
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Fórmula no válida o no encontrada',
                    'data' => ['valida' => false]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar fórmula: ' . $e->getMessage()
            ], 500);
        }
    }
}