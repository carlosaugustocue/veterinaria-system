<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormulaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página de bienvenida
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Estadísticas para el dashboard (API endpoints)
    Route::get('/api/dashboard/estadisticas-semanales', [DashboardController::class, 'estadisticasSemanales'])
        ->name('dashboard.estadisticas-semanales');
    Route::get('/api/dashboard/actividad-reciente', [DashboardController::class, 'actividadReciente'])
        ->name('dashboard.actividad-reciente');
    
    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Fórmulas médicas (del sistema anterior)
    Route::prefix('formulas')->name('formulas.')->group(function () {
        Route::get('/', [FormulaController::class, 'index'])->name('index');
        Route::get('/create', [FormulaController::class, 'create'])->name('create');
        Route::post('/', [FormulaController::class, 'store'])->name('store');
        Route::get('/{formula}', [FormulaController::class, 'show'])->name('show');
        Route::get('/{formula}/edit', [FormulaController::class, 'edit'])->name('edit');
        Route::put('/{formula}', [FormulaController::class, 'update'])->name('update');
        Route::delete('/{formula}', [FormulaController::class, 'destroy'])->name('destroy');
        
        // Acciones especiales
        Route::post('/{formula}/imprimir', [FormulaController::class, 'marcarImpresa'])->name('imprimir');
        Route::post('/{formula}/entregar', [FormulaController::class, 'marcarEntregada'])->name('entregar');
        Route::post('/{formula}/cancelar', [FormulaController::class, 'cancelar'])->name('cancelar');
        
        // Validación
        Route::post('/validar-hash', [FormulaController::class, 'validarHash'])->name('validar-hash');
        
        // Estadísticas
        Route::get('/estadisticas/general', [FormulaController::class, 'estadisticas'])->name('estadisticas');
    });
    
    // ⭐ PRÓXIMAS RUTAS (comentadas por ahora)
    /*
    Route::prefix('citas')->name('citas.')->group(function () {
        // Rutas de citas
    });
    
    Route::prefix('pacientes')->name('pacientes.')->group(function () {
        // Rutas de pacientes  
    });
    
    Route::prefix('consultas')->name('consultas.')->group(function () {
        // Rutas de consultas
    });
    */
});

// Incluir rutas de autenticación (Breeze)
require __DIR__.'/auth.php';