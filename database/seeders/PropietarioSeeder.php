<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Propietario;
use App\Models\Veterinario;
use Illuminate\Support\Facades\Hash;

class PropietarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles
        $clienteRole = Role::where('nombre', Role::CLIENTE)->first();
        $veterinarioRole = Role::where('nombre', Role::VETERINARIO)->first();

        if (!$clienteRole) {
            $this->command->error('❌ Error: No se encontró el rol de cliente.');
            return;
        }

        // ⭐ MOVIDO A VeterinarioSeeder: Ya no creamos usuarios veterinarios aquí
        // El VeterinarioSeeder se encarga de crear los registros en la tabla veterinarios

        // Datos de propietarios de prueba
        $propietariosData = [
            [
                'user' => [
                    'nombre' => 'Carlos',
                    'apellido' => 'López',
                    'email' => 'carlos.lopez@example.com',
                    'telefono' => '3101234567',
                    'cedula' => '22334455',
                    'direccion' => 'Calle 45 # 12-34',
                    'ciudad' => 'Bogotá'
                ],
                'propietario' => [
                    'ocupacion' => 'Ingeniero de Sistemas',
                    'preferencia_contacto' => 'whatsapp',
                    'acepta_promociones' => true,
                    'contacto_emergencia_nombre' => 'Ana López',
                    'contacto_emergencia_telefono' => '3109876543',
                    'contacto_emergencia_relacion' => 'familiar'
                ]
            ],
            [
                'user' => [
                    'nombre' => 'Ana',
                    'apellido' => 'Martínez',
                    'email' => 'ana.martinez@example.com',
                    'telefono' => '3201234567',
                    'cedula' => '33445566',
                    'direccion' => 'Carrera 20 # 45-67',
                    'ciudad' => 'Medellín'
                ],
                'propietario' => [
                    'ocupacion' => 'Profesora',
                    'preferencia_contacto' => 'email',
                    'acepta_promociones' => true,
                    'contacto_emergencia_nombre' => 'Luis Martínez',
                    'contacto_emergencia_telefono' => '3198765432',
                    'contacto_emergencia_relacion' => 'familiar',
                    'limite_credito' => 500000
                ]
            ],
            [
                'user' => [
                    'nombre' => 'Pedro',
                    'apellido' => 'Sánchez',
                    'email' => 'pedro.sanchez@example.com',
                    'telefono' => '3301234567',
                    'cedula' => '44556677',
                    'direccion' => 'Diagonal 30 # 25-89',
                    'ciudad' => 'Cali'
                ],
                'propietario' => [
                    'ocupacion' => 'Médico',
                    'preferencia_contacto' => 'telefono',
                    'acepta_promociones' => false,
                    'observaciones' => 'Prefiere citas en horas de la tarde'
                ]
            ],
            [
                'user' => [
                    'nombre' => 'Laura',
                    'apellido' => 'Ramírez',
                    'email' => 'laura.ramirez@example.com',
                    'telefono' => '3401234567',
                    'cedula' => '55667788',
                    'direccion' => 'Avenida 15 # 78-90',
                    'ciudad' => 'Barranquilla'
                ],
                'propietario' => [
                    'ocupacion' => 'Abogada',
                    'preferencia_contacto' => 'whatsapp',
                    'acepta_promociones' => true,
                    'contacto_emergencia_nombre' => 'Miguel Ramírez',
                    'contacto_emergencia_telefono' => '3407654321',
                    'contacto_emergencia_relacion' => 'familiar',
                    'horarios_preferidos' => ['mañana' => '08:00-10:00', 'tarde' => '14:00-16:00']
                ]
            ],
            [
                'user' => [
                    'nombre' => 'Roberto',
                    'apellido' => 'Torres',
                    'email' => 'roberto.torres@example.com',
                    'telefono' => '3501234567',
                    'cedula' => '66778899',
                    'direccion' => 'Calle 60 # 15-25',
                    'ciudad' => 'Bucaramanga'
                ],
                'propietario' => [
                    'ocupacion' => 'Comerciante',
                    'preferencia_contacto' => 'telefono',
                    'acepta_promociones' => true,
                    'limite_credito' => 300000,
                    'observaciones' => 'Cliente frecuente desde 2020'
                ]
            ]
        ];

        // ⭐ DESCOMENTADO: Ahora sí podemos obtener un veterinario
        $veterinarioPreferido = Veterinario::first();
        $contadorCreados = 0;

        foreach ($propietariosData as $data) {
            // Verificar si ya existe el usuario
            if (User::where('email', $data['user']['email'])->exists()) {
                continue;
            }

            // Crear usuario
            $user = User::create([
                'nombre' => $data['user']['nombre'],
                'apellido' => $data['user']['apellido'],
                'email' => $data['user']['email'],
                'password' => Hash::make('Cliente123!'),
                'telefono' => $data['user']['telefono'],
                'cedula' => $data['user']['cedula'],
                'fecha_nacimiento' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'sexo' => fake()->randomElement(['M', 'F']),
                'direccion' => $data['user']['direccion'],
                'ciudad' => $data['user']['ciudad'],
                'role_id' => $clienteRole->id,
                'activo' => true,
                'email_verified_at' => now(),
            ]);

            // Crear propietario
            $propietarioData = $data['propietario'];
            $propietarioData['user_id'] = $user->id;
            
            // ⭐ DESCOMENTADO: Asignar veterinario preferido aleatoriamente
            if ($veterinarioPreferido && fake()->boolean(70)) {
                $propietarioData['veterinario_preferido_id'] = $veterinarioPreferido->id;
            }

            Propietario::create($propietarioData);
            $contadorCreados++;
        }

        // Actualizar estadísticas del propietario ya existente
        $propietarioExistente = User::where('email', 'cliente@example.com')->first()?->propietario;
        if ($propietarioExistente) {
            $propietarioExistente->update([
                'ocupacion' => 'Diseñadora Gráfica',
                'preferencia_contacto' => 'whatsapp',
                'acepta_promociones' => true,
                'contacto_emergencia_nombre' => 'José González',
                'contacto_emergencia_telefono' => '3006543210',
                'contacto_emergencia_relacion' => 'familiar',
                // ⭐ NUEVO: También asignar veterinario preferido
                'veterinario_preferido_id' => $veterinarioPreferido?->id
            ]);
            $contadorCreados++;
        }

        $this->command->info("✅ Propietarios creados/actualizados: {$contadorCreados}");
        $this->command->info('');
        $this->command->info('👥 Nuevos propietarios:');
        $this->command->info('   📧 carlos.lopez@example.com (Ingeniero)');
        $this->command->info('   📧 ana.martinez@example.com (Profesora)');  
        $this->command->info('   📧 pedro.sanchez@example.com (Médico)');
        $this->command->info('   📧 laura.ramirez@example.com (Abogada)');
        $this->command->info('   📧 roberto.torres@example.com (Comerciante)');
        $this->command->info('   🔑 Contraseña para todos: Cliente123!');
        
        // ⭐ NUEVO: Mostrar asignaciones de veterinario preferido
        $conVetPreferido = Propietario::whereNotNull('veterinario_preferido_id')->count();
        if ($conVetPreferido > 0) {
            $this->command->info('');
            $this->command->info("💝 {$conVetPreferido} propietarios con veterinario preferido asignado");
        }
    }
}