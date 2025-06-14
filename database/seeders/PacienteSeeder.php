<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Propietario;
use App\Models\Paciente;
use App\Models\Especie;
use App\Models\Raza;
use Carbon\Carbon;

class PacienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener especies y razas
        $perro = Especie::where('nombre', 'Perro')->first();
        $gato = Especie::where('nombre', 'Gato')->first();
        $ave = Especie::where('nombre', 'Ave')->first();
        $conejo = Especie::where('nombre', 'Conejo')->first();

        if (!$perro || !$gato) {
            $this->command->error('❌ Error: Especies no encontradas. Ejecuta EspecieSeeder primero.');
            return;
        }

        // Obtener propietarios
        $propietarios = Propietario::with('user')->get();
        
        if ($propietarios->isEmpty()) {
            $this->command->error('❌ Error: No hay propietarios. Ejecuta PropietarioSeeder primero.');
            return;
        }

        // Obtener razas
        $razasPerros = Raza::where('especie_id', $perro->id)->get();
        $razasGatos = Raza::where('especie_id', $gato->id)->get();
        $razasAves = $ave ? Raza::where('especie_id', $ave->id)->get() : collect();
        $razasConejos = $conejo ? Raza::where('especie_id', $conejo->id)->get() : collect();

        $pacientesCreados = 0;

        // Datos de pacientes específicos para cada propietario
        $pacientesData = [
            // Propietario 1: Carlos López (2 mascotas)
            [
                'propietario_email' => 'carlos.lopez@example.com',
                'mascotas' => [
                    [
                        'nombre' => 'Max',
                        'especie' => 'Perro',
                        'raza' => 'Labrador Retriever',
                        'fecha_nacimiento' => '2020-03-15',
                        'sexo' => 'M',
                        'peso' => 32.5,
                        'color' => 'Dorado',
                        'esterilizado' => true,
                        'temperamento' => 'jugueton',
                        'nivel_actividad' => 'alto'
                    ],
                    [
                        'nombre' => 'Luna',
                        'especie' => 'Gato',
                        'raza' => 'Siamés',
                        'fecha_nacimiento' => '2021-07-22',
                        'sexo' => 'F',
                        'peso' => 4.2,
                        'color' => 'Seal point',
                        'esterilizado' => true,
                        'temperamento' => 'normal'
                    ]
                ]
            ],
            // Propietario 2: Ana Martínez (1 mascota)
            [
                'propietario_email' => 'ana.martinez@example.com',
                'mascotas' => [
                    [
                        'nombre' => 'Simón',
                        'especie' => 'Perro',
                        'raza' => 'Golden Retriever',
                        'fecha_nacimiento' => '2019-11-08',
                        'sexo' => 'M',
                        'peso' => 28.0,
                        'color' => 'Dorado claro',
                        'esterilizado' => true,
                        'alergias_conocidas' => 'Alérgico al pollo',
                        'temperamento' => 'docil',
                        'observaciones_generales' => 'Muy tranquilo con niños'
                    ]
                ]
            ],
            // Propietario 3: Pedro Sánchez (3 mascotas)
            [
                'propietario_email' => 'pedro.sanchez@example.com',
                'mascotas' => [
                    [
                        'nombre' => 'Bella',
                        'especie' => 'Perro',
                        'raza' => 'Pastor Alemán',
                        'fecha_nacimiento' => '2018-05-20',
                        'sexo' => 'F',
                        'peso' => 26.8,
                        'color' => 'Negro y fuego',
                        'esterilizado' => true,
                        'temperamento' => 'normal',
                        'nivel_actividad' => 'alto',
                        'microchip' => 'DE123456789012345'
                    ],
                    [
                        'nombre' => 'Mimi',
                        'especie' => 'Gato',
                        'raza' => 'Persa',
                        'fecha_nacimiento' => '2020-01-12',
                        'sexo' => 'F',
                        'peso' => 5.1,
                        'color' => 'Blanco',
                        'esterilizado' => false,
                        'cuidados_especiales' => 'Cepillado diario por pelo largo'
                    ],
                    [
                        'nombre' => 'Coco',
                        'especie' => 'Ave',
                        'raza' => 'Canario', 
                        'fecha_nacimiento' => '2022-03-01',
                        'sexo' => 'M',
                        'color' => 'Amarillo',
                        'temperamento' => 'normal',
                        'observaciones_generales' => 'Canta muy bien por las mañanas'
                    ]
                ]
            ],
            // Propietario 4: Laura Ramírez (2 mascotas)
            [
                'propietario_email' => 'laura.ramirez@example.com',
                'mascotas' => [
                    [
                        'nombre' => 'Rocky',
                        'especie' => 'Perro',
                        'raza' => 'Bulldog Francés',
                        'fecha_nacimiento' => '2021-09-14',
                        'sexo' => 'M',
                        'peso' => 11.5,
                        'color' => 'Atigrado',
                        'esterilizado' => false,
                        'condiciones_medicas' => 'Problemas respiratorios leves',
                        'temperamento' => 'jugueton'
                    ],
                    [
                        'nombre' => 'Whiskers',
                        'especie' => 'Gato',
                        'raza' => 'Maine Coon',
                        'fecha_nacimiento' => '2019-12-03',
                        'sexo' => 'M',
                        'peso' => 7.8,
                        'color' => 'Tabby',
                        'esterilizado' => true,
                        'temperamento' => 'docil',
                        'microchip' => 'US987654321098765'
                    ]
                ]
            ],
            // Propietario 5: Roberto Torres (1 mascota)
            [
                'propietario_email' => 'roberto.torres@example.com',
                'mascotas' => [
                    [
                        'nombre' => 'Toby',
                        'especie' => 'Perro',
                        'raza' => 'Chihuahua',
                        'fecha_nacimiento' => '2020-08-30',
                        'sexo' => 'M',
                        'peso' => 2.1,
                        'color' => 'Marrón',
                        'esterilizado' => true,
                        'temperamento' => 'ansioso',
                        'nivel_actividad' => 'moderado',
                        'observaciones_generales' => 'Muy protector de su dueño'
                    ]
                ]
            ],
            // Propietario existente: María González (2 mascotas)
            [
                'propietario_email' => 'cliente@example.com',
                'mascotas' => [
                    [
                        'nombre' => 'Firulais',
                        'especie' => 'Perro',
                        'raza' => 'Mestizo',
                        'fecha_nacimiento' => '2019-06-15',
                        'sexo' => 'M',
                        'peso' => 18.5,
                        'color' => 'Café con blanco',
                        'esterilizado' => true,
                        'temperamento' => 'jugueton',
                        'observaciones_generales' => 'Rescatado de la calle en 2019'
                    ],
                    [
                        'nombre' => 'Pelusa',
                        'especie' => 'Gato',
                        'raza' => 'Doméstico Común',
                        'fecha_nacimiento' => '2021-04-10',
                        'sexo' => 'F',
                        'peso' => 3.8,
                        'color' => 'Gris',
                        'esterilizado' => false,
                        'temperamento' => 'normal'
                    ]
                ]
            ]
        ];

        // Crear pacientes
        foreach ($pacientesData as $propietarioData) {
            $propietario = Propietario::whereHas('user', function ($q) use ($propietarioData) {
                $q->where('email', $propietarioData['propietario_email']);
            })->first();

            if (!$propietario) {
                $this->command->warn("⚠️  Propietario no encontrado: {$propietarioData['propietario_email']}");
                continue;
            }

            foreach ($propietarioData['mascotas'] as $mascotaData) {
                // Obtener la raza
                $especie = null;
                $raza = null;

                switch ($mascotaData['especie']) {
                    case 'Perro':
                        $especie = $perro;
                        $raza = $razasPerros->where('nombre', $mascotaData['raza'])->first();
                        break;
                    case 'Gato':
                        $especie = $gato;
                        $raza = $razasGatos->where('nombre', $mascotaData['raza'])->first();
                        break;
                    case 'Ave':
                        $especie = $ave;
                        $raza = $razasAves->where('nombre', $mascotaData['raza'])->first();
                        break;
                    case 'Conejo':
                        $especie = $conejo;
                        $raza = $razasConejos->where('nombre', $mascotaData['raza'])->first();
                        break;
                }

                if (!$especie || !$raza) {
                    $this->command->warn("⚠️  Especie/Raza no encontrada: {$mascotaData['especie']} - {$mascotaData['raza']}");
                    continue;
                }

                // Crear el paciente
                $paciente = Paciente::create([
                    'nombre' => $mascotaData['nombre'],
                    'propietario_id' => $propietario->id,
                    'especie_id' => $especie->id,
                    'raza_id' => $raza->id,
                    'fecha_nacimiento' => Carbon::parse($mascotaData['fecha_nacimiento']),
                    'sexo' => $mascotaData['sexo'],
                    'peso' => $mascotaData['peso'] ?? null,
                    'color' => $mascotaData['color'],
                    'esterilizado' => $mascotaData['esterilizado'] ?? false,
                    'fecha_esterilizacion' => ($mascotaData['esterilizado'] ?? false) ? 
                        Carbon::parse($mascotaData['fecha_nacimiento'])->addMonths(8) : null,
                    'temperamento' => $mascotaData['temperamento'] ?? 'normal',
                    'nivel_actividad' => $mascotaData['nivel_actividad'] ?? 'moderado',
                    'microchip' => $mascotaData['microchip'] ?? null,
                    'alergias_conocidas' => $mascotaData['alergias_conocidas'] ?? null,
                    'condiciones_medicas' => $mascotaData['condiciones_medicas'] ?? null,
                    'observaciones_generales' => $mascotaData['observaciones_generales'] ?? null,
                    'fecha_registro' => now()->subDays(fake()->numberBetween(1, 365)),
                    'estado' => 'activo',
                    'fecha_proxima_vacuna' => Carbon::parse($mascotaData['fecha_nacimiento'])->addYear(),
                    'fecha_proxima_desparasitacion' => now()->addMonths(3)
                ]);

                $pacientesCreados++;
            }

            // Actualizar estadísticas del propietario
            $propietario->actualizarEstadisticas();
        }

        $this->command->info("✅ Pacientes creados: {$pacientesCreados}");
        $this->command->info('');
        $this->command->info('🐾 Mascotas por propietario:');
        $this->command->info('   🐕 Carlos López: Max (Labrador) y Luna (Siamés)');
        $this->command->info('   🐕 Ana Martínez: Simón (Golden Retriever)');
        $this->command->info('   🐕 Pedro Sánchez: Bella (Pastor Alemán), Mimi (Persa), Coco (Canario)');
        $this->command->info('   🐕 Laura Ramírez: Rocky (Bulldog Francés), Whiskers (Maine Coon)');
        $this->command->info('   🐕 Roberto Torres: Toby (Chihuahua)');
        $this->command->info('   🐕 María González: Firulais (Mestizo), Pelusa (Doméstico)');
        $this->command->info('');
        $this->command->info("📊 Total: {$pacientesCreados} mascotas registradas");
    }
}