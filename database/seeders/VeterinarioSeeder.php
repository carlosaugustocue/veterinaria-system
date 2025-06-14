<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Veterinario;

class VeterinarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar usuarios veterinarios
        $veterinarioRole = Role::where('nombre', Role::VETERINARIO)->first();
        
        if (!$veterinarioRole) {
            $this->command->error('❌ Error: No se encontró el rol de veterinario.');
            return;
        }

        $usuariosVeterinarios = User::where('role_id', $veterinarioRole->id)->get();
        
        if ($usuariosVeterinarios->isEmpty()) {
            $this->command->error('❌ Error: No hay usuarios con rol de veterinario.');
            return;
        }

        $veterinariosCreados = 0;

        foreach ($usuariosVeterinarios as $user) {
            // Verificar si ya existe un registro de veterinario para este usuario
            if (Veterinario::where('user_id', $user->id)->exists()) {
                continue;
            }

            // Datos específicos según el veterinario
            $datosVeterinario = $this->getDatosVeterinario($user->email);
            $datosVeterinario['user_id'] = $user->id;

            Veterinario::create($datosVeterinario);
            $veterinariosCreados++;
        }

        $this->command->info("✅ Veterinarios creados: {$veterinariosCreados}");
        $this->command->info('');
        $this->command->info('👨‍⚕️ Personal veterinario registrado:');
        
        $veterinarios = Veterinario::with('user')->get();
        foreach ($veterinarios as $vet) {
            $this->command->info("   🩺 {$vet->nombre_completo} - {$vet->especialidad}");
            $this->command->info("      📋 Licencia: {$vet->licencia_medica}");
            $this->command->info("      💰 Tarifa: $" . number_format($vet->tarifa_consulta, 0));
        }
    }

    private function getDatosVeterinario(string $email): array
    {
        switch ($email) {
            case 'veterinario@veterinaria.com':
                return [
                    'licencia_medica' => 'VET-001',
                    'especialidad' => 'Medicina General',
                    'certificaciones' => 'Medicina Veterinaria - Universidad Nacional, Especialización en Pequeños Animales',
                    'anos_experiencia' => 12,
                    'horario_trabajo' => [
                        'lunes' => ['08:00', '17:00'],
                        'martes' => ['08:00', '17:00'],
                        'miercoles' => ['08:00', '17:00'],
                        'jueves' => ['08:00', '17:00'],
                        'viernes' => ['08:00', '16:00'],
                        'sabado' => ['08:00', '12:00']
                    ],
                    'duracion_consulta' => 30,
                    'max_citas_dia' => 16,
                    'disponible_emergencias' => true,
                    'tarifa_consulta' => 50000,
                    'tarifa_emergencia' => 75000,
                    'observaciones' => 'Veterinario principal de la clínica, especialista en medicina general y preventiva.'
                ];

            case 'maria.rodriguez@veterinaria.com':
                return [
                    'licencia_medica' => 'VET-002',
                    'especialidad' => 'Cirugía Veterinaria',
                    'certificaciones' => 'Medicina Veterinaria - Universidad Javeriana, Especialización en Cirugía de Tejidos Blandos',
                    'anos_experiencia' => 8,
                    'horario_trabajo' => [
                        'lunes' => ['09:00', '18:00'],
                        'martes' => ['09:00', '18:00'],
                        'miercoles' => ['09:00', '18:00'],
                        'jueves' => ['09:00', '18:00'],
                        'viernes' => ['09:00', '17:00'],
                        'sabado' => ['08:00', '12:00']
                    ],
                    'duracion_consulta' => 45,
                    'max_citas_dia' => 12,
                    'disponible_emergencias' => true,
                    'tarifa_consulta' => 65000,
                    'tarifa_emergencia' => 95000,
                    'observaciones' => 'Especialista en cirugía, procedimientos complejos y casos de emergencia.'
                ];

            default:
                return [
                    'licencia_medica' => 'VET-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
                    'especialidad' => 'Medicina General',
                    'certificaciones' => 'Medicina Veterinaria',
                    'anos_experiencia' => rand(3, 15),
                    'horario_trabajo' => [
                        'lunes' => ['08:00', '17:00'],
                        'martes' => ['08:00', '17:00'],
                        'miercoles' => ['08:00', '17:00'],
                        'jueves' => ['08:00', '17:00'],
                        'viernes' => ['08:00', '16:00']
                    ],
                    'duracion_consulta' => 30,
                    'max_citas_dia' => 14,
                    'disponible_emergencias' => true,
                    'tarifa_consulta' => 45000,
                    'tarifa_emergencia' => 70000,
                    'observaciones' => 'Veterinario general de la clínica.'
                ];
        }
    }
}