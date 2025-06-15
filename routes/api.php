<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TestApiController;
use App\Http\Controllers\Api\V1\CitaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Ruta básica de usuario autenticado
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    
    // ✅ RUTA DE PRUEBA SIN AUTENTICACIÓN
    Route::get('/test/public', function () {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando correctamente - Sin autenticación',
            'timestamp' => now()->toISOString(),
            'laravel_version' => app()->version()
        ]);
    })->name('test.public');
    
    // ✅ RUTAS AUTENTICADAS
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // RUTAS DE TESTING
        Route::prefix('test')->name('test.')->group(function () {
            Route::get('/ping', [TestApiController::class, 'ping'])->name('ping');
            Route::get('/responses', [TestApiController::class, 'testResponses'])->name('responses');
            Route::get('/pagination', [TestApiController::class, 'testPagination'])->name('pagination');
            Route::post('/validation', [TestApiController::class, 'testValidation'])->name('validation');
        });
        
        // RUTAS DE CITAS
        Route::prefix('citas')->name('citas.')->group(function () {
            Route::get('/', [CitaController::class, 'index'])->name('index');
            Route::post('/', [CitaController::class, 'store'])->name('store');
            Route::get('/disponibilidad', [CitaController::class, 'disponibilidad'])->name('disponibilidad');
            Route::get('/{cita}', [CitaController::class, 'show'])->name('show');
            Route::put('/{cita}', [CitaController::class, 'update'])->name('update');
            Route::delete('/{cita}', [CitaController::class, 'destroy'])->name('destroy');
            Route::post('/{cita}/confirmar', [CitaController::class, 'confirmar'])->name('confirmar');
            Route::post('/{cita}/reprogramar', [CitaController::class, 'reprogramar'])->name('reprogramar');
        });
        
        // RUTAS CON MIDDLEWARE DE ROLES
        Route::middleware(['role:administrador'])->group(function () {
            Route::get('/test/admin-only', [TestApiController::class, 'adminOnly'])->name('test.admin');
        });
        
        Route::middleware(['role:veterinario'])->group(function () {
            Route::get('/test/vet-only', [TestApiController::class, 'vetOnly'])->name('test.vet');
        });
        
        Route::middleware(['role:cliente'])->group(function () {
            Route::get('/test/client-only', [TestApiController::class, 'clientOnly'])->name('test.client');
        });
    });
});