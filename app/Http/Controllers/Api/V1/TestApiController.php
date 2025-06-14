<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestApiController extends BaseApiController
{
    /**
     * Test básico de autenticación
     */
    public function ping(): JsonResponse
    {
        return $this->successResponse([
            'message' => 'API funcionando correctamente',
            'timestamp' => now()->toISOString(),
            'user' => [
                'id' => $this->getUser()->id,
                'name' => $this->getUser()->nombre_completo,
                'role' => $this->getUser()->role->nombre
            ]
        ], 'Test exitoso');
    }

    /**
     * Test de middleware de roles - Solo administradores
     */
    public function adminOnly(): JsonResponse
    {
        return $this->successResponse([
            'message' => 'Acceso de administrador confirmado',
            'user_role' => $this->getUser()->role->nombre,
            'permissions' => $this->getUser()->role->permisos
        ], 'Acceso administrativo exitoso');
    }

    /**
     * Test de middleware de roles - Solo veterinarios
     */
    public function vetOnly(): JsonResponse
    {
        $veterinario = $this->getVeterinario();
        
        return $this->successResponse([
            'message' => 'Acceso de veterinario confirmado',
            'veterinario' => [
                'id' => $veterinario->id,
                'licencia' => $veterinario->licencia_medica,
                'especialidad' => $veterinario->especialidad
            ]
        ], 'Acceso veterinario exitoso');
    }

    /**
     * Test de middleware de roles - Solo clientes
     */
    public function clientOnly(): JsonResponse
    {
        $propietario = $this->getPropietario();
        
        return $this->successResponse([
            'message' => 'Acceso de cliente confirmado',
            'propietario' => [
                'id' => $propietario->id,
                'total_mascotas' => $propietario->total_mascotas,
                'ocupacion' => $propietario->ocupacion
            ]
        ], 'Acceso cliente exitoso');
    }

    /**
     * Test de error de validación
     */
    public function testValidation(Request $request): JsonResponse
    {
        $request->validate([
            'required_field' => 'required|string',
            'email_field' => 'required|email'
        ]);

        return $this->successResponse([
            'data' => $request->all()
        ], 'Validación exitosa');
    }

    /**
     * Test de respuesta paginada
     */
    public function testPagination(): JsonResponse
    {
        // Simular datos paginados usando el modelo User
        $users = \App\Models\User::paginate(5);
        
        return $this->successPaginatedResponse($users, 'Paginación funcionando');
    }

    /**
     * Test de diferentes tipos de respuesta
     */
    public function testResponses(Request $request): JsonResponse
    {
        $type = $request->get('type', 'success');

        switch ($type) {
            case 'error':
                return $this->errorResponse('Error de prueba', 400);
                
            case 'not_found':
                return $this->notFoundResponse('Recurso de prueba no encontrado');
                
            case 'unauthorized':
                return $this->unauthorizedResponse('No autorizado para esta prueba');
                
            case 'forbidden':
                return $this->forbiddenResponse('Prohibido acceder a esta prueba');
                
            case 'validation':
                return $this->validationErrorResponse([
                    'field1' => ['Error en campo 1'],
                    'field2' => ['Error en campo 2']
                ]);
                
            case 'created':
                return $this->createdResponse(['id' => 123], 'Recurso de prueba creado');
                
            default:
                return $this->successResponse([
                    'available_types' => ['error', 'not_found', 'unauthorized', 'forbidden', 'validation', 'created']
                ], 'Respuesta exitosa por defecto');
        }
    }
}