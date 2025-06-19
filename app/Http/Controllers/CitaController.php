<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Veterinario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CitaController extends BaseApiController
{
    /**
     * Listar citas con filtros
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Cita::with([
                'paciente:id,nombre,especie_id,raza_id',
                'paciente.especie:id,nombre',
                'paciente.raza:id,nombre',
                'veterinario.user:id,nombre,apellido',
                'propietario.user:id,nombre,apellido,telefono'
            ]);

            // Filtrar por rol del usuario
            $user = Auth::user();
            
            if ($user->hasRole('cliente')) {
                // Los clientes solo ven sus propias citas
                $propietarioId = $user->propietario->id ?? null;
                if (!$propietarioId) {
                    return $this->errorResponse('No se encontró el propietario asociado', 404);
                }
                $query->where('propietario_id', $propietarioId);
                
            } elseif ($user->hasRole('veterinario')) {
                // Los veterinarios ven solo sus citas asignadas
                $veterinarioId = $user->veterinario->id ?? null;
                if (!$veterinarioId) {
                    return $this->errorResponse('No se encontró el veterinario asociado', 404);
                }
                $query->where('veterinario_id', $veterinarioId);
            }
            // Los administradores y recepcionistas ven todas las citas

            // Filtros opcionales
            
            // Filtro por fecha específica
            if ($request->filled('fecha')) {
                $fecha = Carbon::parse($request->fecha);
                $query->whereDate('fecha_hora', $fecha);
            }
            
            // Filtro por rango de fechas
            if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                $fechaDesde = Carbon::parse($request->fecha_desde)->startOfDay();
                $fechaHasta = Carbon::parse($request->fecha_hasta)->endOfDay();
                $query->whereBetween('fecha_hora', [$fechaDesde, $fechaHasta]);
            }
            
            // Filtro por estado
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }
            
            // Filtro por tipo de cita
            if ($request->filled('tipo_cita')) {
                $query->where('tipo_cita', $request->tipo_cita);
            }
            
            // Filtro por veterinario (solo admin/recepcionista)
            if ($request->filled('veterinario_id') && !$user->hasRole('veterinario')) {
                $query->where('veterinario_id', $request->veterinario_id);
            }
            
            // Filtro por paciente
            if ($request->filled('paciente_id')) {
                $query->where('paciente_id', $request->paciente_id);
            }
            
            // Filtro por propietario (solo admin/recepcionista/veterinario)
            if ($request->filled('propietario_id') && !$user->hasRole('cliente')) {
                $query->where('propietario_id', $request->propietario_id);
            }
            
            // Filtro para citas de hoy
            if ($request->boolean('hoy')) {
                $query->hoy();
            }
            
            // Filtro para citas de esta semana
            if ($request->boolean('esta_semana')) {
                $query->estaSemana();
            }
            
            // Filtro para citas próximas (futuras)
            if ($request->boolean('proximas')) {
                $query->proximas();
            }
            
            // Ordenamiento
            $orderBy = $request->get('order_by', 'fecha_hora');
            $orderDir = $request->get('order_dir', 'asc');
            $query->orderBy($orderBy, $orderDir);
            
            // Paginación
            $perPage = $request->get('per_page', 15);
            $citas = $query->paginate($perPage);
            
            // Agregar estadísticas resumidas
            $stats = [
                'total' => $citas->total(),
                'por_estado' => [
                    'programadas' => $query->clone()->where('estado', Cita::ESTADO_PROGRAMADA)->count(),
                    'confirmadas' => $query->clone()->where('estado', Cita::ESTADO_CONFIRMADA)->count(),
                    'completadas' => $query->clone()->where('estado', Cita::ESTADO_COMPLETADA)->count(),
                    'canceladas' => $query->clone()->where('estado', Cita::ESTADO_CANCELADA)->count(),
                ]
            ];
            
            return $this->successResponse([
                'citas' => $citas->items(),
                'pagination' => [
                    'total' => $citas->total(),
                    'per_page' => $citas->perPage(),
                    'current_page' => $citas->currentPage(),
                    'last_page' => $citas->lastPage(),
                    'from' => $citas->firstItem(),
                    'to' => $citas->lastItem()
                ],
                'stats' => $stats,
                'filters_applied' => $request->all()
            ], 'Citas obtenidas exitosamente');
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener las citas: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Crear nueva cita
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validación de datos
            $validated = $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'veterinario_id' => 'required|exists:veterinarios,id',
                'fecha_hora' => 'required|date|after:now',
                'tipo_cita' => 'required|in:' . implode(',', [
                    Cita::TIPO_CONSULTA_GENERAL,
                    Cita::TIPO_EMERGENCIA,
                    Cita::TIPO_CIRUGIA,
                    Cita::TIPO_VACUNACION,
                    Cita::TIPO_DESPARASITACION,
                    Cita::TIPO_SEGUIMIENTO,
                    Cita::TIPO_REVISION,
                    Cita::TIPO_ESTETICA,
                    Cita::TIPO_OTRO
                ]),
                'motivo' => 'required|string|max:500',
                'sintomas' => 'nullable|string|max:1000',
                'duracion_estimada' => 'nullable|integer|min:15|max:480', // 15 min a 8 horas
                'requiere_ayuno' => 'nullable|boolean',
                'observaciones_previas' => 'nullable|string|max:1000'
            ]);

            // Verificar permisos según el rol
            $user = Auth::user();
            
            // Obtener el paciente para verificar el propietario
            $paciente = Paciente::findOrFail($validated['paciente_id']);
            
            // Validaciones de autorización según rol
            if ($user->hasRole('cliente')) {
                // Los clientes solo pueden crear citas para sus propias mascotas
                if ($paciente->propietario_id !== $user->propietario->id) {
                    return $this->errorResponse('No puedes crear citas para mascotas que no te pertenecen', 403);
                }
            }
            
            // Verificar restricción de tiempo mínimo (4 horas de anticipación)
            $fechaHora = Carbon::parse($validated['fecha_hora']);
            $horasAnticipacion = now()->diffInHours($fechaHora, false);
            
            if ($horasAnticipacion < 4 && $validated['tipo_cita'] !== Cita::TIPO_EMERGENCIA) {
                return $this->errorResponse('Las citas deben programarse con al menos 4 horas de anticipación', 422);
            }
            
            // Verificar disponibilidad del veterinario
            $veterinario = Veterinario::findOrFail($validated['veterinario_id']);
            
            // Duración de la cita (30 minutos por defecto)
            $duracion = $validated['duracion_estimada'] ?? 30;
            
            // Verificar que no haya conflictos de horario
            $fechaInicio = $fechaHora;
            $fechaFin = $fechaHora->copy()->addMinutes($duracion);
            
            $citasConflicto = Cita::where('veterinario_id', $veterinario->id)
                ->whereIn('estado', [Cita::ESTADO_PROGRAMADA, Cita::ESTADO_CONFIRMADA])
                ->where(function($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
                        ->orWhere(function($q) use ($fechaInicio, $fechaFin) {
                            $q->where('fecha_hora', '<=', $fechaInicio)
                              ->whereRaw('DATE_ADD(fecha_hora, INTERVAL duracion_minutos MINUTE) > ?', [$fechaInicio]);
                        });
                })
                ->exists();
                
            if ($citasConflicto) {
                return $this->errorResponse('El veterinario no está disponible en ese horario', 422);
            }
            
            // Crear la cita
            $cita = Cita::create([
                'paciente_id' => $paciente->id,
                'propietario_id' => $paciente->propietario_id,
                'veterinario_id' => $veterinario->id,
                'fecha_hora' => $fechaHora,
                'tipo_cita' => $validated['tipo_cita'],
                'estado' => Cita::ESTADO_PROGRAMADA,
                'motivo' => $validated['motivo'],
                'sintomas' => $validated['sintomas'] ?? null,
                'duracion_minutos' => $duracion,
                'requiere_ayuno' => $validated['requiere_ayuno'] ?? false,
                'observaciones_previas' => $validated['observaciones_previas'] ?? null,
                'prioridad' => $this->getPrioridadPorTipo($validated['tipo_cita']),
                'costo_consulta' => $this->getCostoPorTipo($validated['tipo_cita'], $veterinario),
                'estado_pago' => Cita::ESTADO_PAGO_PENDIENTE,
                'creado_por_user_id' => $user->id
            ]);
            
            // Cargar relaciones para la respuesta
            $cita->load([
                'paciente:id,nombre,especie_id,raza_id',
                'paciente.especie:id,nombre',
                'paciente.raza:id,nombre',
                'veterinario.user:id,nombre,apellido',
                'propietario.user:id,nombre,apellido,telefono,email'
            ]);
            
            return $this->successResponse([
                'cita' => $cita,
                'mensaje_confirmacion' => $this->getMensajeConfirmacion($cita)
            ], 'Cita creada exitosamente', 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear la cita: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Generar mensaje de confirmación para la cita
     */
    private function getMensajeConfirmacion(Cita $cita): string
    {
        $fecha = Carbon::parse($cita->fecha_hora)->locale('es');
        
        return sprintf(
            "Cita programada para %s el %s a las %s con %s. %s",
            $cita->paciente->nombre,
            $fecha->translatedFormat('l j \\d\\e F'),
            $fecha->format('h:i A'),
            $cita->veterinario->user->nombre_completo,
            $cita->requiere_ayuno ? 'IMPORTANTE: El paciente debe estar en ayunas.' : ''
        );
    }
    
    /**
     * Ver detalle de una cita
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $cita = Cita::with([
                'paciente:id,nombre,especie_id,raza_id,fecha_nacimiento,peso,sexo',
                'paciente.especie:id,nombre',
                'paciente.raza:id,nombre',
                'veterinario.user:id,nombre,apellido,telefono,email',
                'veterinario:id,user_id,licencia_medica,especialidad,tarifa_consulta',
                'propietario.user:id,nombre,apellido,telefono,email',
                'propietario:id,user_id,ocupacion,preferencia_contacto',
                'creadoPor:id,nombre,apellido',
                'modificadoPor:id,nombre,apellido',
                'canceladoPor:id,nombre,apellido'
                // 'consulta' // Comentado temporalmente hasta corregir el modelo
            ])->findOrFail($id);
            
            // Verificar autorización según rol
            $user = Auth::user();
            
            if ($user->hasRole('cliente')) {
                // Los clientes solo pueden ver sus propias citas
                if ($cita->propietario_id !== $user->propietario->id) {
                    return $this->errorResponse('No tienes autorización para ver esta cita', 403);
                }
            } elseif ($user->hasRole('veterinario')) {
                // Los veterinarios solo pueden ver sus citas asignadas
                if ($cita->veterinario_id !== $user->veterinario->id) {
                    return $this->errorResponse('No tienes autorización para ver esta cita', 403);
                }
            }
            
            // Agregar información adicional
            $response = [
                'cita' => $cita,
                'puede_modificar' => $this->puedeModificar($cita, $user),
                'puede_cancelar' => $this->puedeCancelar($cita, $user),
                'puede_confirmar' => $cita->estado === Cita::ESTADO_PROGRAMADA,
                'tiempo_restante' => $this->getTiempoRestante($cita),
                'historial_cambios' => $this->getHistorialCambios($cita)
            ];
            
            // Si tiene consulta, agregar información resumida
            // Temporalmente deshabilitado hasta corregir el modelo Cita
            $response['info_consulta'] = [
                'tiene_consulta' => false,
                'nota' => 'Relación con consultas temporalmente deshabilitada'
            ];
            
            return $this->successResponse($response, 'Cita obtenida exitosamente');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Cita no encontrada', 404);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener la cita: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Verificar si el usuario puede modificar la cita
     */
    private function puedeModificar(Cita $cita, $user): bool
    {
        // No se pueden modificar citas completadas o canceladas
        if (in_array($cita->estado, [Cita::ESTADO_COMPLETADA, Cita::ESTADO_CANCELADA])) {
            return false;
        }
        
        // Verificar tiempo mínimo (2 horas antes)
        $horasRestantes = now()->diffInHours($cita->fecha_hora, false);
        if ($horasRestantes < 2) {
            return false;
        }
        
        // Verificar permisos según rol
        if ($user->hasRole('administrador') || $user->hasRole('recepcionista')) {
            return true;
        }
        
        if ($user->hasRole('veterinario')) {
            return $cita->veterinario_id === $user->veterinario->id;
        }
        
        if ($user->hasRole('cliente')) {
            return $cita->propietario_id === $user->propietario->id;
        }
        
        return false;
    }
    
    /**
     * Verificar si el usuario puede cancelar la cita
     */
    private function puedeCancelar(Cita $cita, $user): bool
    {
        // No se pueden cancelar citas ya completadas o canceladas
        if (in_array($cita->estado, [Cita::ESTADO_COMPLETADA, Cita::ESTADO_CANCELADA])) {
            return false;
        }
        
        // Admin siempre puede cancelar
        if ($user->hasRole('administrador')) {
            return true;
        }
        
        // Otros roles tienen restricciones similares a modificar
        return $this->puedeModificar($cita, $user);
    }
    
    /**
     * Obtener tiempo restante hasta la cita
     */
    private function getTiempoRestante(Cita $cita): array
    {
        $ahora = now();
        $fechaCita = Carbon::parse($cita->fecha_hora);
        
        if ($fechaCita->isPast()) {
            return [
                'texto' => 'Cita pasada',
                'horas' => 0,
                'minutos' => 0
            ];
        }
        
        $diff = $ahora->diff($fechaCita);
        
        return [
            'texto' => $fechaCita->diffForHumans(),
            'dias' => $diff->days,
            'horas' => $diff->h,
            'minutos' => $diff->i
        ];
    }
    
    /**
     * Obtener historial de cambios de la cita
     */
    private function getHistorialCambios(Cita $cita): array
    {
        $historial = [];
        
        // Creación
        $historial[] = [
            'accion' => 'Cita creada',
            'fecha' => $cita->created_at->format('Y-m-d H:i:s'),
            'usuario' => $cita->creadoPor ? $cita->creadoPor->nombre_completo : 'Sistema'
        ];
        
        // Última modificación
        if ($cita->updated_at->gt($cita->created_at) && $cita->modificadoPor) {
            $historial[] = [
                'accion' => 'Cita modificada',
                'fecha' => $cita->updated_at->format('Y-m-d H:i:s'),
                'usuario' => $cita->modificadoPor->nombre_completo
            ];
        }
        
        // Cancelación
        if ($cita->estado === Cita::ESTADO_CANCELADA && $cita->fecha_cancelacion) {
            $historial[] = [
                'accion' => 'Cita cancelada',
                'fecha' => $cita->fecha_cancelacion,
                'usuario' => $cita->canceladoPor ? $cita->canceladoPor->nombre_completo : 'Sistema',
                'motivo' => $cita->motivo_cancelacion
            ];
        }
        
        return $historial;
    }
    
    /**
     * Actualizar cita
     * TODO: Implementar
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Por implementar...
        return $this->successResponse([], 'Método update por implementar');
    }
    
    /**
     * Cancelar cita
     * TODO: Implementar
     */
    public function destroy($id): JsonResponse
    {
        // Por implementar...
        return $this->successResponse([], 'Método destroy por implementar');
    }
    
    /**
     * Confirmar cita
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function confirmar($id): JsonResponse
    {
        try {
            $cita = Cita::with([
                'paciente:id,nombre',
                'veterinario.user:id,nombre,apellido',
                'propietario.user:id,nombre,apellido,telefono,email'
            ])->findOrFail($id);
            
            // Verificar autorización
            $user = Auth::user();
            
            // Los clientes pueden confirmar sus propias citas
            if ($user->hasRole('cliente')) {
                if ($cita->propietario_id !== $user->propietario->id) {
                    return $this->errorResponse('No tienes autorización para confirmar esta cita', 403);
                }
            }
            
            // Los veterinarios pueden confirmar sus citas asignadas
            if ($user->hasRole('veterinario')) {
                if ($cita->veterinario_id !== $user->veterinario->id) {
                    return $this->errorResponse('No tienes autorización para confirmar esta cita', 403);
                }
            }
            
            // Verificar que la cita esté en estado programada
            if ($cita->estado !== Cita::ESTADO_PROGRAMADA) {
                return $this->errorResponse(
                    'Solo se pueden confirmar citas que estén en estado programada. Estado actual: ' . $cita->estado,
                    422
                );
            }
            
            // Verificar que la fecha de la cita sea futura
            if (Carbon::parse($cita->fecha_hora)->isPast()) {
                return $this->errorResponse('No se pueden confirmar citas pasadas', 422);
            }
            
            // Actualizar estado
            $cita->update([
                'estado' => Cita::ESTADO_CONFIRMADA,
                'fecha_confirmacion' => now(),
                'confirmado_por_user_id' => $user->id,
                'modificado_por_user_id' => $user->id
            ]);
            
            // Recargar la cita con las relaciones actualizadas
            $cita->refresh();
            
            // Preparar respuesta con información adicional
            $response = [
                'cita' => $cita,
                'mensaje_confirmacion' => $this->getMensajeConfirmacionCita($cita),
                'recordatorio' => [
                    'texto' => 'Se enviará recordatorio automático 24 horas antes de la cita',
                    'fecha_recordatorio' => Carbon::parse($cita->fecha_hora)->subDay()->format('Y-m-d H:i:s')
                ]
            ];
            
            return $this->successResponse(
                $response,
                'Cita confirmada exitosamente'
            );
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Cita no encontrada', 404);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al confirmar la cita: ' . $e->getMessage(),
                500
            );
        }
    }
    
    /**
     * Generar mensaje de confirmación específico
     */
    private function getMensajeConfirmacionCita(Cita $cita): string
    {
        $fecha = Carbon::parse($cita->fecha_hora)->locale('es');
        
        $mensaje = sprintf(
            "✅ Cita CONFIRMADA para %s el %s a las %s con %s.",
            $cita->paciente->nombre,
            $fecha->translatedFormat('l j \\d\\e F'),
            $fecha->format('h:i A'),
            $cita->veterinario->user->nombre_completo
        );
        
        if ($cita->requiere_ayuno) {
            $mensaje .= " ⚠️ RECORDATORIO: El paciente debe estar en ayunas.";
        }
        
        $mensaje .= " 📧 Recibirá un recordatorio 24 horas antes.";
        
        return $mensaje;
    }
    
    /**
     * Reprogramar cita
     * TODO: Implementar
     */
    public function reprogramar(Request $request, $id): JsonResponse
    {
        // Por implementar...
        return $this->successResponse([], 'Método reprogramar por implementar');
    }
    
    /**
     * Ver disponibilidad de horarios
     * TODO: Implementar
     */
    /**
 * Ver disponibilidad de horarios para un veterinario en una fecha específica
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function disponibilidad(Request $request): JsonResponse
{
    try {
        // Validar parámetros requeridos
        $validated = $request->validate([
            'veterinario_id' => 'required|exists:veterinarios,id',
            'fecha' => 'required|date|after_or_equal:today',
            'duracion' => 'nullable|integer|min:15|max:480' // 15 min a 8 horas
        ]);

        $veterinarioId = $validated['veterinario_id'];
        $fecha = Carbon::parse($validated['fecha']);
        $duracionSolicitada = $validated['duracion'] ?? 30; // 30 minutos por defecto

        // Verificar que el veterinario existe y está activo
        $veterinario = Veterinario::with('user:id,nombre,apellido')
            ->findOrFail($veterinarioId);

        // Definir horario laboral (puedes moverlo a configuración o al modelo Veterinario)
        $horaInicio = $fecha->copy()->setTime(8, 0);  // 8:00 AM
        $horaFin = $fecha->copy()->setTime(18, 0);    // 6:00 PM
        $intervaloMinutos = 30; // Intervalos de 30 minutos

        // Obtener citas existentes del veterinario para esa fecha
        $citasExistentes = Cita::where('veterinario_id', $veterinarioId)
            ->whereDate('fecha_hora', $fecha)
            ->whereIn('estado', [Cita::ESTADO_PROGRAMADA, Cita::ESTADO_CONFIRMADA])
            ->orderBy('fecha_hora')
            ->get(['fecha_hora', 'duracion_minutos', 'tipo_cita', 'paciente_id']);

        // Generar todos los horarios posibles
        $horariosDisponibles = [];
        $current = $horaInicio->copy();

        while ($current->lt($horaFin)) {
            $horaActual = $current->format('H:i');
            $finBloque = $current->copy()->addMinutes($duracionSolicitada);

            // Verificar que el bloque completo esté dentro del horario laboral
            if ($finBloque->lte($horaFin)) {
                // Verificar si no hay conflicto con citas existentes
                $hayConflicto = false;

                foreach ($citasExistentes as $citaExistente) {
                    $inicioCitaExistente = Carbon::parse($citaExistente->fecha_hora);
                    $finCitaExistente = $inicioCitaExistente->copy()->addMinutes($citaExistente->duracion_minutos);

                    // Verificar solapamiento
                    if ($current->lt($finCitaExistente) && $finBloque->gt($inicioCitaExistente)) {
                        $hayConflicto = true;
                        break;
                    }
                }

                // Si no hay conflicto y la hora no ha pasado (para fecha de hoy)
                if (!$hayConflicto) {
                    $ahora = now();
                    $horaCompleta = $fecha->copy()->setTimeFromTimeString($horaActual);
                    
                    // Si es hoy, solo mostrar horarios futuros (con al menos 2 horas de anticipación)
                    if ($fecha->isToday()) {
                        if ($horaCompleta->gt($ahora->addHours(2))) {
                            $horariosDisponibles[] = $horaActual;
                        }
                    } else {
                        $horariosDisponibles[] = $horaActual;
                    }
                }
            }

            $current->addMinutes($intervaloMinutos);
        }

        // Obtener próximas citas del veterinario (para contexto)
        $proximasCitas = Cita::where('veterinario_id', $veterinarioId)
            ->whereDate('fecha_hora', $fecha)
            ->whereIn('estado', [Cita::ESTADO_PROGRAMADA, Cita::ESTADO_CONFIRMADA])
            ->with('paciente:id,nombre')
            ->orderBy('fecha_hora')
            ->get()
            ->map(function ($cita) {
                return [
                    'hora' => Carbon::parse($cita->fecha_hora)->format('H:i'),
                    'paciente' => $cita->paciente->nombre ?? 'Paciente',
                    'tipo' => $cita->tipo_cita,
                    'duracion' => $cita->duracion_minutos
                ];
            });

        // Preparar respuesta
        $response = [
            'disponible' => count($horariosDisponibles) > 0,
            'fecha' => $fecha->format('Y-m-d'),
            'veterinario' => [
                'id' => $veterinario->id,
                'nombre' => $veterinario->user->nombre . ' ' . $veterinario->user->apellido,
                'especialidad' => $veterinario->especialidad ?? 'Medicina General'
            ],
            'horarios_disponibles' => $horariosDisponibles,
            'horario_laboral' => [
                'inicio' => '08:00',
                'fin' => '18:00',
                'intervalo_minutos' => $intervaloMinutos
            ],
            'duracion_solicitada' => $duracionSolicitada,
            'proximas_citas' => $proximasCitas,
            'estadisticas' => [
                'total_horarios_posibles' => (int) (($horaFin->diffInMinutes($horaInicio)) / $intervaloMinutos),
                'horarios_ocupados' => $citasExistentes->count(),
                'horarios_disponibles' => count($horariosDisponibles)
            ]
        ];

        return $this->successResponse($response, 'Disponibilidad verificada exitosamente');

    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->errorResponse('Parámetros inválidos', 422, $e->errors());
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return $this->errorResponse('Veterinario no encontrado', 404);
    } catch (\Exception $e) {
        return $this->errorResponse(
            'Error al verificar disponibilidad: ' . $e->getMessage(),
            500
        );
    }
}
    
    /**
     * Obtener prioridad según tipo de cita
     */
    private function getPrioridadPorTipo(string $tipo): string
    {
        $prioridades = [
            Cita::TIPO_EMERGENCIA => Cita::PRIORIDAD_URGENTE,
            Cita::TIPO_CIRUGIA => Cita::PRIORIDAD_ALTA,
            Cita::TIPO_CONSULTA_GENERAL => Cita::PRIORIDAD_NORMAL,
            Cita::TIPO_VACUNACION => Cita::PRIORIDAD_NORMAL,
            Cita::TIPO_SEGUIMIENTO => Cita::PRIORIDAD_NORMAL,
            Cita::TIPO_REVISION => Cita::PRIORIDAD_BAJA,
            Cita::TIPO_DESPARASITACION => Cita::PRIORIDAD_BAJA,
            Cita::TIPO_ESTETICA => Cita::PRIORIDAD_BAJA,
            Cita::TIPO_OTRO => Cita::PRIORIDAD_NORMAL
        ];
        
        return $prioridades[$tipo] ?? Cita::PRIORIDAD_NORMAL;
    }
    
    /**
     * Obtener costo base según tipo de cita
     */
    private function getCostoPorTipo(string $tipo, Veterinario $veterinario): float
    {
        $costoBase = $veterinario->tarifa_consulta ?? 50000;
        
        $multiplicadores = [
            Cita::TIPO_EMERGENCIA => 1.5,
            Cita::TIPO_CIRUGIA => 3.0,
            Cita::TIPO_CONSULTA_GENERAL => 1.0,
            Cita::TIPO_VACUNACION => 0.8,
            Cita::TIPO_SEGUIMIENTO => 0.7,
            Cita::TIPO_REVISION => 0.9,
            Cita::TIPO_DESPARASITACION => 0.6,
            Cita::TIPO_ESTETICA => 1.2,
            Cita::TIPO_OTRO => 1.0
        ];
        
        $multiplicador = $multiplicadores[$tipo] ?? 1.0;
        return $costoBase * $multiplicador;
    }
}