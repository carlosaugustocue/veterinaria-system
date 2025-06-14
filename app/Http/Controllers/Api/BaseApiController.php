<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;

class BaseApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Constructor - Laravel 12 compatible
     */
    public function __construct()
    {
        // En Laravel 12, el middleware se define en las rutas, no en el constructor
        // Este constructor puede estar vacío o tener otra lógica de inicialización
    }

    /**
     * Verificar permisos del usuario para una acción específica
     */
    protected function checkPermission(string $module, string $action): bool
    {
        return auth()->user()->hasPermission($module, $action);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    protected function checkRole(string $role): bool
    {
        return auth()->user()->hasRole($role);
    }

    /**
     * Obtener el usuario autenticado
     */
    protected function getUser()
    {
        return auth()->user();
    }

    /**
     * Obtener el propietario del usuario autenticado (si es cliente)
     */
    protected function getPropietario()
    {
        return $this->getUser()->propietario;
    }

    /**
     * Obtener el veterinario del usuario autenticado (si es veterinario)
     */
    protected function getVeterinario()
    {
        return $this->getUser()->veterinario;
    }
}