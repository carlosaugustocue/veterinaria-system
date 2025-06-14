<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            
            // Datos de autenticación
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'nombre' => $request->user()->nombre,
                    'apellido' => $request->user()->apellido,
                    'email' => $request->user()->email,
                    'telefono' => $request->user()->telefono,
                    'rol' => $request->user()->role?->nombre,
                    'permisos' => $request->user()->role?->permisos ?? [],
                    'activo' => $request->user()->activo,
                    'ultimo_acceso' => $request->user()->ultimo_acceso,
                    // Datos específicos según el rol
                    'perfil' => $this->getPerfilData($request->user()),
                ] : null,
            ],
            
            // Flash messages
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            
            // Configuración global de la aplicación
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => app()->getLocale(),
            ],
            
            // Datos globales útiles
            'globals' => [
                'csrf_token' => csrf_token(),
                'current_route' => $request->route()?->getName(),
                'current_url' => $request->url(),
                'can' => $this->getPermissions($request->user()),
            ],
        ];
    }

    /**
     * Obtener datos del perfil según el rol del usuario
     */
    private function getPerfilData($user)
    {
        if (!$user || !$user->role) {
            return null;
        }

        switch ($user->role->nombre) {
            case 'veterinario':
                return $user->veterinario ? [
                    'id' => $user->veterinario->id,
                    'licencia_medica' => $user->veterinario->licencia_medica,
                    'especialidad' => $user->veterinario->especialidad,
                    'anos_experiencia' => $user->veterinario->anos_experiencia,
                    'disponible_emergencias' => $user->veterinario->disponible_emergencias,
                    'tarifa_consulta' => $user->veterinario->tarifa_consulta,
                ] : null;

            case 'cliente':
                return $user->propietario ? [
                    'id' => $user->propietario->id,
                    'ocupacion' => $user->propietario->ocupacion,
                    'preferencia_contacto' => $user->propietario->preferencia_contacto,
                    'acepta_promociones' => $user->propietario->acepta_promociones,
                    'total_mascotas' => $user->propietario->total_mascotas,
                    'veterinario_preferido_id' => $user->propietario->veterinario_preferido_id,
                ] : null;

            default:
                return null;
        }
    }

    /**
     * Obtener permisos del usuario para el frontend
     */
    private function getPermissions($user)
    {
        if (!$user || !$user->role) {
            return [];
        }

        $permisos = $user->role->permisos ?? [];
        $can = [];

        // Convertir permisos a formato más útil para el frontend
        foreach ($permisos as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                $can["{$modulo}.{$accion}"] = true;
            }
        }

        // Agregar permisos específicos por rol
        switch ($user->role->nombre) {
            case 'administrador':
                $can['admin.all'] = true;
                break;
            case 'veterinario':
                $can['medical.all'] = true;
                break;
            case 'cliente':
                $can['own.data'] = true;
                break;
        }

        return $can;
    }
}