<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Propietario;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validación completa con todos los campos requeridos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefono' => 'required|string|max:20',
            'cedula' => 'required|string|max:20|unique:users',
            'direccion' => 'required|string|max:255',
            'ciudad' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:masculino,femenino,otro',
            // No validar role - siempre será cliente para registros web
        ]);

        try {
            DB::beginTransaction();

            // Los registros web son siempre para clientes
            $role = Role::where('nombre', 'cliente')->first();
            
            if (!$role) {
                throw new \Exception("Rol 'cliente' no encontrado en el sistema");
            }

            // Crear usuario con todos los campos
            $user = User::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'cedula' => $request->cedula,
                'direccion' => $request->direccion,
                'ciudad' => $request->ciudad,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
                'role_id' => $role->id,
                'activo' => true
            ]);

            // Crear registro de propietario automáticamente
            Propietario::create([
                'user_id' => $user->id,
                'acepta_promociones' => true,
                'acepta_recordatorios' => true,
                'total_mascotas' => 0,
                'total_citas' => 0
            ]);

            event(new Registered($user));

            Auth::login($user);

            DB::commit();

            return redirect(route('dashboard', absolute: false));

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log del error para debugging
            \Log::error('Error en registro: ' . $e->getMessage());
            
            return back()->withErrors([
                'general' => 'Error al crear la cuenta: ' . $e->getMessage()
            ])->withInput();
        }
    }
}