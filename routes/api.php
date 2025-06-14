<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TestApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Ruta básica sin autenticación
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| API V1 - Rutas de Prueba
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    
    // ✅ RUTA DE PRUEBA SIN AUTENTICACIÓN (para probar que la API funciona)
    Route::get('/test/public', function () {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando correctamente - Sin autenticación',
            'timestamp' => now()->toISOString(),
            'laravel_version' => app()->version()
        ]);
    })->name('test.public');
    
    // ✅ RUTAS DE TESTING - AUTENTICADAS
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Test básico de funcionamiento
        Route::get('/test/ping', [TestApiController::class, 'ping'])->name('test.ping');
        
        // Test de respuestas estándar
        Route::get('/test/responses', [TestApiController::class, 'testResponses'])->name('test.responses');
        
        // Test de paginación
        Route::get('/test/pagination', [TestApiController::class, 'testPagination'])->name('test.pagination');
        
        // Test de validación
        Route::post('/test/validation', [TestApiController::class, 'testValidation'])->name('test.validation');
        
        // ✅ RUTAS CON MIDDLEWARE DE ROLES
        
        // Solo administradores
        Route::middleware(['role:administrador'])->group(function () {
            Route::get('/test/admin-only', [TestApiController::class, 'adminOnly'])->name('test.admin');
        });
        
        // Solo veterinarios
        Route::middleware(['role:veterinario'])->group(function () {
            Route::get('/test/vet-only', [TestApiController::class, 'vetOnly'])->name('test.vet');
        });
        
        // Solo clientes
        Route::middleware(['role:cliente'])->group(function () {
            Route::get('/test/client-only', [TestApiController::class, 'clientOnly'])->name('test.client');
        });
        
        // Múltiples roles permitidos
        Route::middleware(['role:administrador,veterinario'])->group(function () {
            Route::get('/test/admin-or-vet', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Acceso permitido para administrador o veterinario',
                    'user_role' => auth()->user()->role->nombre
                ]);
            })->name('test.admin-or-vet');
        });
    });
});