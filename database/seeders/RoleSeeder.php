<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => Role::ADMINISTRADOR,
                'descripcion' => 'Acceso completo al sistema, gestión de usuarios y configuraciones',
                'permisos' => [
                    'usuarios' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'roles' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'veterinarios' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'auxiliares' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'recepcionistas' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'propietarios' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'pacientes' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'citas' => ['crear', 'leer', 'actualizar', 'eliminar', 'cancelar'],
                    'consultas' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'medicamentos' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'prescripciones' => ['crear', 'leer', 'actualizar', 'eliminar'],
                    'reportes' => ['generar', 'exportar'],
                    'configuracion' => ['modificar'],
                    'sistema' => ['backup', 'restore', 'logs']
                ]
            ],
            [
                'nombre' => Role::VETERINARIO,
                'descripcion' => 'Profesional veterinario con acceso a funciones médicas',
                'permisos' => [
                    'pacientes' => ['crear', 'leer', 'actualizar'],
                    'propietarios' => ['leer', 'actualizar'],
                    'citas' => ['leer', 'actualizar', 'cancelar'],
                    'consultas' => ['crear', 'leer', 'actualizar'],
                    'prescripciones' => ['crear', 'leer', 'actualizar'],
                    'medicamentos' => ['leer'],
                    'examenes' => ['crear', 'leer', 'actualizar'],
                    'cirugias' => ['crear', 'leer', 'actualizar'],
                    'hospitalizacion' => ['crear', 'leer', 'actualizar'],
                    'reportes' => ['generar'],
                    'perfil' => ['leer', 'actualizar']
                ]
            ],
            [
                'nombre' => Role::AUXILIAR,
                'descripcion' => 'Auxiliar de veterinaria con funciones de apoyo clínico',
                'permisos' => [
                    'pacientes' => ['leer', 'actualizar'],
                    'propietarios' => ['leer'],
                    'citas' => ['leer', 'actualizar'],
                    'consultas' => ['leer', 'actualizar'],
                    'triaje' => ['crear', 'leer', 'actualizar'],
                    'examenes' => ['crear', 'leer', 'actualizar'],
                    'medicamentos' => ['leer'],
                    'prescripciones' => ['leer'],
                    'perfil' => ['leer', 'actualizar']
                ]
            ],
            [
                'nombre' => Role::RECEPCIONISTA,
                'descripcion' => 'Personal de recepción y atención al cliente',
                'permisos' => [
                    'propietarios' => ['crear', 'leer', 'actualizar'],
                    'pacientes' => ['crear', 'leer', 'actualizar'],
                    'citas' => ['crear', 'leer', 'actualizar', 'cancelar', 'reprogramar'],
                    'calendario' => ['leer'],
                    'veterinarios' => ['leer'],
                    'pagos' => ['crear', 'leer'],
                    'reportes' => ['leer'],
                    'perfil' => ['leer', 'actualizar']
                ]
            ],
            [
                'nombre' => Role::CLIENTE,
                'descripcion' => 'Propietario de mascotas con acceso limitado',
                'permisos' => [
                    'pacientes' => ['leer'], // Solo sus propias mascotas
                    'citas' => ['crear', 'leer', 'cancelar'], // Solo sus propias citas
                    'consultas' => ['leer'], // Solo de sus mascotas
                    'veterinarios' => ['leer'], // Para seleccionar en citas
                    'perfil' => ['leer', 'actualizar']
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        // Mensaje informativo
        $this->command->info('✅ Roles creados exitosamente:');
        $this->command->info('   - Administrador (acceso completo)');
        $this->command->info('   - Veterinario (funciones médicas)');
        $this->command->info('   - Auxiliar (apoyo clínico)');
        $this->command->info('   - Recepcionista (atención al cliente)');
        $this->command->info('   - Cliente (propietario de mascotas)');
    }
}