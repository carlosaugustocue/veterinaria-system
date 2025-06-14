<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Consulta;
use App\Models\Formula;
use App\Models\Propietario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener estadísticas según el rol del usuario
        $stats = $this->getEstadisticas($user);
        
        // Obtener citas de hoy
        $citasHoy = $this->getCitasHoy($user);
        
        // Obtener controles pendientes
        $controlesPendientes = $this->getControlesPendientes($user);
        
        // Obtener fórmulas próximas a vencer
        $formulasProximasVencer = $this->getFormulasProximasVencer($user);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'citasHoy' => $citasHoy,
            'controlesPendientes' => $controlesPendientes,
            'formulasProximasVencer' => $formulasProximasVencer,
        ]);
    }

    /**
     * Obtener estadísticas generales
     */
    private function getEstadisticas($user)
    {
        $stats = [
            'citasHoy' => 0,
            'totalPacientes' => 0,
            'consultasEsteMes' => 0,
            'formulasActivas' => 0,
        ];

        if ($user->hasRole('administrador')) {
            // Administrador ve todas las estadísticas
            $stats = [
                'citasHoy' => Cita::hoy()->activas()->count(),
                'totalPacientes' => Paciente::where('estado', 'activo')->count(),
                'consultasEsteMes' => Consulta::whereMonth('fecha_hora', now()->month)
                                             ->whereYear('fecha_hora', now()->year)
                                             ->count(),
                'formulasActivas' => Formula::activas()->count(),
            ];
            
        } elseif ($user->hasRole('veterinario')) {
            // Veterinario ve solo sus estadísticas
            $veterinarioId = $user->veterinario->id ?? 0;
            
            $stats = [
                'citasHoy' => Cita::hoy()->where('veterinario_id', $veterinarioId)->activas()->count(),
                'totalPacientes' => Paciente::whereHas('citas', function($q) use ($veterinarioId) {
                    $q->where('veterinario_id', $veterinarioId);
                })->where('estado', 'activo')->distinct()->count(),
                'consultasEsteMes' => Consulta::where('veterinario_id', $veterinarioId)
                                              ->whereMonth('fecha_hora', now()->month)
                                              ->whereYear('fecha_hora', now()->year)
                                              ->count(),
                'formulasActivas' => Formula::where('veterinario_id', $veterinarioId)->activas()->count(),
            ];
            
        } elseif ($user->hasRole('cliente')) {
            // Cliente ve solo sus estadísticas
            $propietarioId = $user->propietario->id ?? 0;
            
            $stats = [
                'citasHoy' => Cita::hoy()->where('propietario_id', $propietarioId)->activas()->count(),
                'totalPacientes' => Paciente::where('propietario_id', $propietarioId)
                                          ->where('estado', 'activo')
                                          ->count(),
                'consultasEsteMes' => Consulta::where('propietario_id', $propietarioId)
                                              ->whereMonth('fecha_hora', now()->month)
                                              ->whereYear('fecha_hora', now()->year)
                                              ->count(),
                'formulasActivas' => Formula::where('propietario_id', $propietarioId)->activas()->count(),
            ];
            
        } elseif ($user->hasRole('recepcionista')) {
            // Recepcionista ve estadísticas generales limitadas
            $stats = [
                'citasHoy' => Cita::hoy()->activas()->count(),
                'totalPacientes' => Paciente::where('estado', 'activo')->count(),
                'consultasEsteMes' => Consulta::whereMonth('fecha_hora', now()->month)
                                             ->whereYear('fecha_hora', now()->year)
                                             ->count(),
                'formulasActivas' => Formula::activas()->count(),
            ];
        }

        return $stats;
    }

    /**
     * Obtener citas de hoy
     */
    private function getCitasHoy($user)
    {
        $query = Cita::with([
            'paciente:id,nombre,propietario_id',
            'propietario.user:id,nombre,apellido',
            'veterinario.user:id,nombre,apellido'
        ])->hoy()->activas();

        // Filtrar según el rol
        if ($user->hasRole('veterinario')) {
            $query->where('veterinario_id', $user->veterinario->id ?? 0);
        } elseif ($user->hasRole('cliente')) {
            $query->where('propietario_id', $user->propietario->id ?? 0);
        }

        return $query->orderBy('fecha_hora')
                    ->limit(5)
                    ->get();
    }

    /**
     * Obtener controles médicos pendientes
     */
    private function getControlesPendientes($user)
    {
        $query = Consulta::with([
            'paciente:id,nombre',
            'veterinario.user:id,nombre,apellido'
        ])->where('requiere_seguimiento', true)
          ->where('fecha_proximo_control', '<=', now()->addDays(7))
          ->where('fecha_proximo_control', '>', now());

        // Filtrar según el rol
        if ($user->hasRole('veterinario')) {
            $query->where('veterinario_id', $user->veterinario->id ?? 0);
        } elseif ($user->hasRole('cliente')) {
            $query->where('propietario_id', $user->propietario->id ?? 0);
        }

        return $query->orderBy('fecha_proximo_control')
                    ->limit(5)
                    ->get();
    }

    /**
     * Obtener fórmulas próximas a vencer
     */
    private function getFormulasProximasVencer($user)
    {
        $query = Formula::with([
            'paciente:id,nombre',
            'veterinario.user:id,nombre,apellido'
        ])->proximasAVencer(7);

        // Filtrar según el rol
        if ($user->hasRole('veterinario')) {
            $query->where('veterinario_id', $user->veterinario->id ?? 0);
        } elseif ($user->hasRole('cliente')) {
            $query->where('propietario_id', $user->propietario->id ?? 0);
        }

        return $query->orderBy('fecha_vencimiento')
                    ->limit(5)
                    ->get();
    }

    /**
     * Obtener estadísticas para gráficos (API endpoint)
     */
    public function estadisticasSemanales(Request $request)
    {
        $user = Auth::user();
        $fechaInicio = now()->startOfWeek();
        $fechaFin = now()->endOfWeek();

        $query = Cita::whereBetween('fecha_hora', [$fechaInicio, $fechaFin]);

        // Filtrar según el rol
        if ($user->hasRole('veterinario')) {
            $query->where('veterinario_id', $user->veterinario->id ?? 0);
        } elseif ($user->hasRole('cliente')) {
            $query->where('propietario_id', $user->propietario->id ?? 0);
        }

        // Agrupar por día de la semana
        $estadisticas = $query->selectRaw('
            DATE(fecha_hora) as fecha,
            COUNT(*) as total_citas,
            SUM(CASE WHEN estado = "completada" THEN 1 ELSE 0 END) as citas_completadas
        ')
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $estadisticas
        ]);
    }

    /**
     * Obtener actividad reciente (API endpoint)
     */
    public function actividadReciente(Request $request)
    {
        $user = Auth::user();
        $limite = $request->get('limite', 10);

        $actividades = collect();

        // Obtener citas recientes
        $citasQuery = Cita::with(['paciente', 'veterinario.user'])
                          ->where('created_at', '>=', now()->subDays(7));

        if ($user->hasRole('veterinario')) {
            $citasQuery->where('veterinario_id', $user->veterinario->id ?? 0);
        } elseif ($user->hasRole('cliente')) {
            $citasQuery->where('propietario_id', $user->propietario->id ?? 0);
        }

        $citas = $citasQuery->orderBy('created_at', 'desc')
                           ->limit($limite)
                           ->get()
                           ->map(function ($cita) {
                               return [
                                   'tipo' => 'cita',
                                   'descripcion' => "Cita programada para {$cita->paciente->nombre}",
                                   'fecha' => $cita->created_at,
                                   'usuario' => $cita->creadoPor->nombre_completo ?? 'Sistema'
                               ];
                           });

        $actividades = $actividades->merge($citas);

        // Obtener consultas recientes
        $consultasQuery = Consulta::with(['paciente', 'veterinario.user'])
                                 ->where('created_at', '>=', now()->subDays(7));

        if ($user->hasRole('veterinario')) {
            $consultasQuery->where('veterinario_id', $user->veterinario->id ?? 0);
        } elseif ($user->hasRole('cliente')) {
            $consultasQuery->where('propietario_id', $user->propietario->id ?? 0);
        }

        $consultas = $consultasQuery->orderBy('created_at', 'desc')
                                   ->limit($limite)
                                   ->get()
                                   ->map(function ($consulta) {
                                       return [
                                           'tipo' => 'consulta',
                                           'descripcion' => "Consulta completada para {$consulta->paciente->nombre}",
                                           'fecha' => $consulta->created_at,
                                           'usuario' => $consulta->creadoPor->nombre_completo ?? 'Sistema'
                                       ];
                                   });

        $actividades = $actividades->merge($consultas);

        // Ordenar por fecha y limitar
        $actividades = $actividades->sortByDesc('fecha')->take($limite)->values();

        return response()->json([
            'success' => true,
            'data' => $actividades
        ]);
    }
}