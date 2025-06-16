<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use App\Models\Propietario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Registro de nuevo usuario
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Password::min(8)],
                'telefono' => 'required|string|max:20',
                'cedula' => 'required|string|max:20|unique:users',
                'direccion' => 'required|string|max:255',
                'ciudad' => 'required|string|max:100',
                'fecha_nacimiento' => 'required|date|before:today',
                'sexo' => 'required|in:masculino,femenino,otro',
                'role' => 'sometimes|string|in:cliente,veterinario,recepcionista,administrador'
            ]);

            DB::beginTransaction();

            // Crear usuario
            $user = User::create([
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'telefono' => $validated['telefono'],
                'cedula' => $validated['cedula'],
                'direccion' => $validated['direccion'],
                'ciudad' => $validated['ciudad'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'sexo' => $validated['sexo'],
                'role_id' => $this->getRoleId($validated['role'] ?? 'cliente'),
                'activo' => true
            ]);

            // Si es cliente, crear registro de propietario
            if ($user->role->nombre === 'cliente') {
                Propietario::create([
                    'user_id' => $user->id,
                    'acepta_promociones' => true,
                    'acepta_recordatorios' => true,
                    'total_mascotas' => 0,
                    'total_citas' => 0
                ]);
            }

            // Crear token de acceso
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'email' => $user->email,
                    'role' => $user->role->nombre
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Usuario registrado exitosamente', 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al registrar usuario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Inicio de sesión
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            // Buscar usuario
            $user = User::where('email', $validated['email'])->first();

            // Verificar si existe y está activo
            if (!$user || !$user->activo) {
                return $this->errorResponse('Credenciales incorrectas o cuenta inactiva', 401);
            }

            // Verificar si está bloqueado
            if ($user->bloqueado_hasta && $user->bloqueado_hasta->isFuture()) {
                return $this->errorResponse(
                    'Cuenta bloqueada hasta: ' . $user->bloqueado_hasta->format('Y-m-d H:i:s'),
                    401
                );
            }

            // Verificar contraseña
            if (!Hash::check($validated['password'], $user->password)) {
                // Incrementar intentos fallidos
                $user->increment('intentos_fallidos');
                
                // Bloquear después de 5 intentos
                if ($user->intentos_fallidos >= 5) {
                    $user->bloqueado_hasta = now()->addMinutes(15);
                    $user->save();
                    
                    return $this->errorResponse(
                        'Demasiados intentos fallidos. Cuenta bloqueada por 15 minutos.',
                        401
                    );
                }

                return $this->errorResponse('Credenciales incorrectas', 401);
            }

            // Login exitoso - resetear intentos
            $user->update([
                'intentos_fallidos' => 0,
                'ultimo_acceso' => now(),
                'bloqueado_hasta' => null
            ]);

            // Crear token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Cargar relaciones según el rol
            $user->load('role');
            
            if ($user->role->nombre === 'cliente') {
                $user->load('propietario');
            } elseif ($user->role->nombre === 'veterinario') {
                $user->load('veterinario');
            }

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'role' => [
                        'id' => $user->role->id,
                        'nombre' => $user->role->nombre
                    ]
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Inicio de sesión exitoso');

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error al iniciar sesión: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revocar el token actual
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Sesión cerrada exitosamente');

        } catch (\Exception $e) {
            return $this->errorResponse('Error al cerrar sesión: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener usuario actual
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load('role');

            // Cargar relaciones según el rol
            if ($user->role->nombre === 'cliente') {
                $user->load('propietario.pacientes');
            } elseif ($user->role->nombre === 'veterinario') {
                $user->load('veterinario');
            }

            return $this->successResponse([
                'user' => $user
            ], 'Datos del usuario obtenidos exitosamente');

        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener datos del usuario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Refrescar token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revocar token actual
            $user->currentAccessToken()->delete();
            
            // Crear nuevo token
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Token actualizado exitosamente');

        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar token: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener ID del rol por nombre
     */
    private function getRoleId(string $roleName): int
    {
        $roles = [
            'administrador' => 1,
            'veterinario' => 2,
            'recepcionista' => 3,
            'cliente' => 4,
            'auxiliar' => 5
        ];

        return $roles[$roleName] ?? 4; // Por defecto cliente
    }
}