<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Propietario;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar el rol de administrador
        $adminRole = Role::where('nombre', Role::ADMINISTRADOR)->first();

        if (!$adminRole) {
            $this->command->error('❌ Error: No se encontró el rol de administrador. Ejecuta primero RoleSeeder.');
            return;
        }

        // Verificar si ya existe un usuario administrador
        $existingAdmin = User::where('email', 'admin@veterinaria.com')->first();
        
        if ($existingAdmin) {
            $this->command->info('⚠️  Ya existe un usuario administrador con email: admin@veterinaria.com');
        } else {
            // Crear usuario administrador
            $admin = User::create([
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'email' => 'admin@veterinaria.com',
                'password' => Hash::make('Admin123!'), // Contraseña temporal
                'telefono' => '1234567890',
                'cedula' => '12345678',
                'fecha_nacimiento' => '1990-01-01',
                'sexo' => 'M',
                'direccion' => 'Dirección del Sistema',
                'ciudad' => 'Bogotá',
                'role_id' => $adminRole->id,
                'activo' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info('✅ Usuario administrador creado exitosamente:');
            $this->command->info('   📧 Email: admin@veterinaria.com');
            $this->command->info('   🔑 Password: Admin123!');
            $this->command->info('   👤 Nombre: Admin Sistema');
            $this->command->info('   🎭 Rol: Administrador');
            $this->command->info('');
            $this->command->warn('⚠️  IMPORTANTE: Cambia la contraseña después del primer login');
        }

        // Crear también un veterinario de prueba
        $vetRole = Role::where('nombre', Role::VETERINARIO)->first();
        
        if ($vetRole) {
            // Dr. Juan Pérez
            if (!User::where('email', 'veterinario@veterinaria.com')->exists()) {
                $veterinario = User::create([
                    'nombre' => 'Dr. Juan',
                    'apellido' => 'Pérez',
                    'email' => 'veterinario@veterinaria.com',
                    'password' => Hash::make('Vet123!'),
                    'telefono' => '0987654321',
                    'cedula' => '87654321',
                    'fecha_nacimiento' => '1985-05-15',
                    'sexo' => 'M',
                    'direccion' => 'Calle Veterinaria 123',
                    'ciudad' => 'Bogotá',
                    'role_id' => $vetRole->id,
                    'activo' => true,
                    'email_verified_at' => now(),
                ]);

                $this->command->info('✅ Usuario veterinario creado exitosamente:');
                $this->command->info('   📧 Email: veterinario@veterinaria.com');
                $this->command->info('   🔑 Password: Vet123!');
                $this->command->info('   👤 Nombre: Dr. Juan Pérez');
                $this->command->info('   🎭 Rol: Veterinario');
            }

            // ⭐ NUEVO: Dra. María Rodríguez
            if (!User::where('email', 'maria.rodriguez@veterinaria.com')->exists()) {
                $drMaria = User::create([
                    'nombre' => 'Dra. María',
                    'apellido' => 'Rodríguez',
                    'email' => 'maria.rodriguez@veterinaria.com',
                    'password' => Hash::make('Vet123!'),
                    'telefono' => '3001234567',
                    'cedula' => '98765432',
                    'fecha_nacimiento' => '1988-03-10',
                    'sexo' => 'F',
                    'direccion' => 'Av. Veterinaria 456',
                    'ciudad' => 'Bogotá',
                    'role_id' => $vetRole->id,
                    'activo' => true,
                    'email_verified_at' => now(),
                ]);

                $this->command->info('✅ Usuario veterinaria adicional creado:');
                $this->command->info('   📧 Email: maria.rodriguez@veterinaria.com');
                $this->command->info('   🔑 Password: Vet123!');
                $this->command->info('   👤 Nombre: Dra. María Rodríguez');
                $this->command->info('   🎭 Rol: Veterinario');
            }
        }

        // ⭐ NUEVO: Crear un cliente de prueba CON su registro de propietario
        $clienteRole = Role::where('nombre', Role::CLIENTE)->first();
        
        if ($clienteRole) {
            if (!User::where('email', 'cliente@example.com')->exists()) {
                $cliente = User::create([
                    'nombre' => 'María',
                    'apellido' => 'González',
                    'email' => 'cliente@example.com',
                    'password' => Hash::make('Cliente123!'),
                    'telefono' => '3001234567',
                    'cedula' => '11223344',
                    'fecha_nacimiento' => '1992-08-20',
                    'sexo' => 'F',
                    'direccion' => 'Carrera 10 # 20-30',
                    'ciudad' => 'Bogotá',
                    'role_id' => $clienteRole->id,
                    'activo' => true,
                    'email_verified_at' => now(),
                ]);

                // ⭐ CREAR TAMBIÉN SU REGISTRO DE PROPIETARIO
                Propietario::create([
                    'user_id' => $cliente->id,
                    'ocupacion' => 'Diseñadora Gráfica',
                    'preferencia_contacto' => 'whatsapp',
                    'acepta_promociones' => true,
                    'contacto_emergencia_nombre' => 'José González',
                    'contacto_emergencia_telefono' => '3006543210',
                    'contacto_emergencia_relacion' => 'familiar'
                ]);

                $this->command->info('✅ Usuario cliente creado exitosamente:');
                $this->command->info('   📧 Email: cliente@example.com');
                $this->command->info('   🔑 Password: Cliente123!');
                $this->command->info('   👤 Nombre: María González');
                $this->command->info('   🎭 Rol: Cliente');
                $this->command->info('   🏠 Propietario: Registrado automáticamente');
            }
        }
    }
}