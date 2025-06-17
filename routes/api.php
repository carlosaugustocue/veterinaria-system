<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TestApiController;
use App\Http\Controllers\Api\V1\CitaController;
use App\Http\Controllers\Api\V1\PropietarioController;
use App\Http\Controllers\Api\V1\PacienteController;
use App\Http\Controllers\Api\V1\ConsultaController;
use App\Http\Controllers\Api\V1\FormulaController;
use App\Http\Controllers\Api\V1\FacturaController;
use App\Http\Controllers\Api\V1\VeterinarioController;
use App\Http\Controllers\Api\V1\AuthController;


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

      // RUTAS DE AUTENTICACIÓN (Sin middleware de autenticación)
      Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    // Route::prefix('propietarios')->name('propietarios.')->group(function () {
    //     Route::get('/', [PropietarioController::class, 'index'])->name('index');
    //     Route::post('/', [PropietarioController::class, 'store'])->name('store');
    //     Route::get('/buscar', [PropietarioController::class, 'buscar'])->name('buscar');
    //     Route::get('/{propietario}', [PropietarioController::class, 'show'])->name('show');
    //     Route::put('/{propietario}', [PropietarioController::class, 'update'])->name('update');
    //     Route::delete('/{propietario}', [PropietarioController::class, 'destroy'])->name('destroy');
    // });

    // Route::prefix('pacientes')->name('pacientes.')->group(function () {
    //     Route::get('/', [PacienteController::class, 'index'])->name('index');
    //     Route::post('/', [PacienteController::class, 'store'])->name('store');
    //     Route::get('/{paciente}', [PacienteController::class, 'show'])->name('show');
    //     Route::put('/{paciente}', [PacienteController::class, 'update'])->name('update');
    //     Route::delete('/{paciente}', [PacienteController::class, 'destroy'])->name('destroy');
    //     Route::get('/{paciente}/historial', [PacienteController::class, 'historial'])->name('historial');
    // });
    
    // ✅ RUTAS AUTENTICADAS
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // RUTAS DE TESTING
        Route::prefix('test')->name('test.')->group(function () {
            Route::get('/ping', [TestApiController::class, 'ping'])->name('ping');
            Route::get('/responses', [TestApiController::class, 'testResponses'])->name('responses');
            Route::get('/pagination', [TestApiController::class, 'testPagination'])->name('pagination');
            Route::post('/validation', [TestApiController::class, 'testValidation'])->name('validation');
        });

        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        });

        RUTAS DE PROPIETARIOS
        Route::prefix('propietarios')->name('propietarios.')->group(function () {
            Route::get('/', [PropietarioController::class, 'index'])->name('index');
            Route::post('/', [PropietarioController::class, 'store'])->name('store');
            Route::get('/buscar', [PropietarioController::class, 'buscar'])->name('buscar');
            Route::get('/{propietario}', [PropietarioController::class, 'show'])->name('show');
            Route::put('/{propietario}', [PropietarioController::class, 'update'])->name('update');
            Route::delete('/{propietario}', [PropietarioController::class, 'destroy'])->name('destroy');
        });

        RUTAS DE PACIENTES
        Route::prefix('pacientes')->name('pacientes.')->group(function () {
            Route::get('/', [PacienteController::class, 'index'])->name('index');
            Route::post('/', [PacienteController::class, 'store'])->name('store');
            Route::get('/{paciente}', [PacienteController::class, 'show'])->name('show');
            Route::put('/{paciente}', [PacienteController::class, 'update'])->name('update');
            Route::delete('/{paciente}', [PacienteController::class, 'destroy'])->name('destroy');
            Route::get('/{paciente}/historial', [PacienteController::class, 'historial'])->name('historial');
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

        // RUTAS DE CONSULTAS
        Route::prefix('consultas')->name('consultas.')->group(function () {
            Route::get('/', [ConsultaController::class, 'index'])->name('index');
            Route::post('/', [ConsultaController::class, 'store'])->name('store');
            Route::get('/{consulta}', [ConsultaController::class, 'show'])->name('show');
            Route::put('/{consulta}', [ConsultaController::class, 'update'])->name('update');
            Route::post('/{consulta}/sintomas', [ConsultaController::class, 'registrarSintomas'])->name('sintomas');
            Route::post('/{consulta}/diagnostico', [ConsultaController::class, 'registrarDiagnostico'])->name('diagnostico');
        });

        // RUTAS DE FÓRMULAS
        Route::prefix('formulas')->name('formulas.')->group(function () {
            Route::get('/', [FormulaController::class, 'index'])->name('index');
            Route::post('/', [FormulaController::class, 'store'])->name('store');
            Route::get('/{formula}', [FormulaController::class, 'show'])->name('show');
            Route::put('/{formula}', [FormulaController::class, 'update'])->name('update');
            Route::post('/{formula}/email', [FormulaController::class, 'enviarPorEmail'])->name('email');
            Route::post('/{formula}/whatsapp', [FormulaController::class, 'enviarPorWhatsApp'])->name('whatsapp');
        });

        // RUTAS DE FACTURAS
        Route::prefix('facturas')->name('facturas.')->group(function () {
            Route::get('/', [FacturaController::class, 'index'])->name('index');
            Route::post('/', [FacturaController::class, 'store'])->name('store');
            Route::get('/{factura}', [FacturaController::class, 'show'])->name('show');
            Route::put('/{factura}', [FacturaController::class, 'update'])->name('update');
            Route::post('/{factura}/pago', [FacturaController::class, 'procesarPago'])->name('pago');
            Route::get('/{factura}/pdf', [FacturaController::class, 'generarPDF'])->name('pdf');
        });

        Route::prefix('veterinarios')->name('veterinarios.')->group(function () {
            Route::get('/', [VeterinarioController::class, 'index'])->name('index');
            Route::post('/', [VeterinarioController::class, 'store'])->name('store');
            Route::get('/{veterinario}', [VeterinarioController::class, 'show'])->name('show');
            Route::put('/{veterinario}', [VeterinarioController::class, 'update'])->name('update');
            Route::delete('/{veterinario}', [VeterinarioController::class, 'destroy'])->name('destroy');
            Route::get('/{veterinario}/citas', [VeterinarioController::class, 'citas'])->name('citas');
            Route::get('/{veterinario}/propietarios-preferidos', [VeterinarioController::class, 'propietariosPreferidos'])->name('propietarios-preferidos');
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