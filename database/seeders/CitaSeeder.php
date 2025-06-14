<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Veterinario;
use App\Models\Propietario;
use App\Models\User;
use Carbon\Carbon;

class CitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener veterinarios disponibles
        $veterinarios = Veterinario::with('user')->get();
        
        if ($veterinarios->isEmpty()) {
            $this->command->error('❌ Error: No hay veterinarios registrados.');
            return;
        }

        // Obtener pacientes con sus propietarios
        $pacientes = Paciente::with('propietario.user')->get();
        
        if ($pacientes->isEmpty()) {
            $this->command->error('❌ Error: No hay pacientes registrados.');
            return;
        }

        // Obtener usuario administrador para las citas
        $adminUser = User::whereHas('role', function ($q) {
            $q->where('nombre', 'administrador');
        })->first();

        if (!$adminUser) {
            $this->command->error('❌ Error: No hay usuario administrador.');
            return;
        }

        $citasCreadas = 0;

        // Datos específicos de citas para cada mascota
        $citasData = [
            // Max (Carlos López) - Labrador
            'Max' => [
                [
                    'tipo_cita' => Cita::TIPO_CONSULTA_GENERAL,
                    'fecha_offset' => -15, // hace 15 días
                    'estado' => Cita::ESTADO_COMPLETADA,
                    'motivo' => 'Revisión general anual',
                    'sintomas' => 'Mascota sana, revisión preventiva'
                ],
                [
                    'tipo_cita' => Cita::TIPO_VACUNACION,
                    'fecha_offset' => 3, // en 3 días
                    'estado' => Cita::ESTADO_CONFIRMADA,
                    'motivo' => 'Refuerzo de vacunas anuales'
                ]
            ],
            
            // Luna (Carlos López) - Siamés
            'Luna' => [
                [
                    'tipo_cita' => Cita::TIPO_REVISION,
                    'fecha_offset' => -8, // hace 8 días
                    'estado' => Cita::ESTADO_COMPLETADA,
                    'motivo' => 'Control post-esterilización',
                    'sintomas' => 'Evolución favorable'
                ]
            ],

            // Simón (Ana Martínez) - Golden Retriever
            'Simón' => [
                [
                    'tipo_cita' => Cita::TIPO_CONSULTA_GENERAL,
                    'fecha_offset' => -22, // hace 22 días
                    'estado' => Cita::ESTADO_COMPLETADA,
                    'motivo' => 'Consulta por alergia alimentaria',
                    'sintomas' => 'Irritación en la piel, picazón'
                ],
                [
                    'tipo_cita' => Cita::TIPO_SEGUIMIENTO,
                    'fecha_offset' => 5, // en 5 días
                    'estado' => Cita::ESTADO_PROGRAMADA,
                    'motivo' => 'Seguimiento de tratamiento para alergias'
                ]
            ],

            // Bella (Pedro Sánchez) - Pastor Alemán
            'Bella' => [
                [
                    'tipo_cita' => Cita::TIPO_CONSULTA_GENERAL,
                    'fecha_offset' => -30, // hace 30 días
                    'estado' => Cita::ESTADO_COMPLETADA,
                    'motivo' => 'Chequeo de rutina',
                    'sintomas' => 'Sin síntomas, revisión preventiva'
                ],
                [
                    'tipo_cita' => Cita::TIPO_VACUNACION,
                    'fecha_offset' => 7, // en 7 días
                    'estado' => Cita::ESTADO_PROGRAMADA,
                    'motivo' => 'Vacunación antirrábica'
                ]
            ],

            // Mimi (Pedro Sánchez) - Persa
            'Mimi' => [
                [
                    'tipo_cita' => Cita::TIPO_ESTETICA,
                    'fecha_offset' => -5, // hace 5 días
                    'estado' => Cita::ESTADO_COMPLETADA,
                    'motivo' => 'Corte de pelo y aseo',
                    'sintomas' => 'Pelo enredado, necesita aseo'
                ]
            ],

            // Coco (Pedro Sánchez) - Canario
            'Coco' => [
                [
                    'tipo_cita' => Cita::TIPO_CONSULTA_GENERAL,
                    'fecha_offset' => 10, // en 10 días
                    'estado' => Cita::ESTADO_PROGRAMADA,
                    'motivo' => 'Revisión de salud general',
                    'sintomas' => 'Chequeo preventivo'
                ]
            ],

            // Rocky (Laura Ramírez) - Bulldog Francés
            'Rocky' => [
                [
                    'tipo_cita' => Cita::TIPO_CONSULTA_GENERAL,
                    'fecha_offset' => -12, // hace 12 días
                    'estado' => Cita::ESTADO_COMPLETADA,
                    'motivo' => 'Consulta por problemas respiratorios',
                    'sintomas' => 'Dificultad para respirar, ronquidos fuertes'
                ],
                [
                    'tipo_cita' => Cita::TIPO_SEGUIMIENTO,
                    'fecha_offset' => 1, // mañana
                    'estado' => Cita::ESTADO_CONFIRMADA,
                    'motivo' => 'Control de tratamiento respiratorio'
                ]
            ],

            // Whiskers (Laura Ramírez) - Maine Coon
            'Whiskers' => [
                [
                    'tipo_cita' => Cita::TIPO_DESPARASITACION,
                    'fecha_offset' => 4, // en 4 días
                    'estado' => Cita::ESTADO_PROGRAMADA,
                    'motivo' => 'Desparasitación trimestral'
                ]
            ],

            // Toby (Roberto Torres) - Chihuahua
            'Toby' => [
                [
                    'tipo_cita' => Cita::TIPO_CONSULTA_GENERAL,
                    'fecha_offset' => -18, // hace 18 días
                    'estado' => Cita::ESTADO_COMPLETADA,
                    'motivo' => 'Consulta por ansiedad',
                    'sintomas' => 'Comportamiento ansioso, temblores'
                ]
            ],

            // Firulais (María González) - Mestizo
            'Firulais' => [
                [
                    'tipo_cita' => Cita::TIPO_VACUNACION,
                    'fecha_offset' => -45, // hace 45 días
                    'estado' => Cita::ESTADO_COMPLETADA,
                    'motivo' => 'Vacunación completa',
                    'sintomas' => 'Perro rescatado, necesita vacunas'
                ],
                [
                    'tipo_cita' => Cita::TIPO_REVISION,
                    'fecha_offset' => 2, // pasado mañana
                    'estado' => Cita::ESTADO_CONFIRMADA,
                    'motivo' => 'Control de salud general'
                ]
            ],

            // Pelusa (María González) - Doméstico Común
            'Pelusa' => [
                [
                    'tipo_cita' => Cita::TIPO_CONSULTA_GENERAL,
                    'fecha_offset' => 6, // en 6 días
                    'estado' => Cita::ESTADO_PROGRAMADA,
                    'motivo' => 'Primera consulta',
                    'sintomas' => 'Chequeo inicial'
                ]
            ]
        ];

        // Crear citas para cada mascota
        foreach ($citasData as $nombrePaciente => $citas) {
            $paciente = $pacientes->firstWhere('nombre', $nombrePaciente);
            
            if (!$paciente) {
                $this->command->warn("⚠️  Paciente no encontrado: {$nombrePaciente}");
                continue;
            }

            foreach ($citas as $citaData) {
                // Calcular fecha y hora
                $fechaBase = now()->addDays($citaData['fecha_offset']);
                
                // Asignar hora realista según el tipo de cita
                $hora = $this->getHoraSegunTipo($citaData['tipo_cita']);
                $fechaHora = $fechaBase->setTime($hora['hora'], $hora['minuto']);
                
                // Seleccionar veterinario (alternar entre los disponibles)
                $veterinario = $veterinarios->random();
                
                // Calcular duración según tipo
                $duracion = $this->getDuracionSegunTipo($citaData['tipo_cita']);
                
                // Calcular costo
                $costo = $this->getCostoSegunTipo($citaData['tipo_cita'], $veterinario);

                // Datos de la cita
                $datosCompletos = [
                    'paciente_id' => $paciente->id,
                    'veterinario_id' => $veterinario->id,
                    'propietario_id' => $paciente->propietario_id,
                    'fecha_hora' => $fechaHora,
                    'duracion_minutos' => $duracion,
                    'tipo_cita' => $citaData['tipo_cita'],
                    'estado' => $citaData['estado'],
                    'motivo_consulta' => $citaData['motivo'],
                    'sintomas_reportados' => $citaData['sintomas'] ?? null,
                    'prioridad' => $this->getPrioridadSegunTipo($citaData['tipo_cita']),
                    'costo_consulta' => $costo,
                    'creado_por_user_id' => $adminUser->id
                ];

                // Campos adicionales según el estado
                if ($citaData['estado'] === Cita::ESTADO_COMPLETADA) {
                    $datosCompletos['hora_llegada'] = $fechaHora->copy()->subMinutes(5);
                    $datosCompletos['hora_inicio_atencion'] = $fechaHora->copy();
                    $datosCompletos['hora_fin_atencion'] = $fechaHora->copy()->addMinutes($duracion);
                    $datosCompletos['total_pagado'] = $costo;
                    $datosCompletos['estado_pago'] = Cita::ESTADO_PAGO_PAGADO;
                }

                // Crear la cita
                Cita::create($datosCompletos);
                $citasCreadas++;
            }
        }

        // Crear algunas citas de emergencia pasadas
        $this->crearCitasEmergencia($veterinarios, $pacientes, $adminUser);
        $citasCreadas += 2; // Se crean 2 emergencias

        $this->command->info("✅ Citas creadas: {$citasCreadas}");
        $this->command->info('');
        $this->command->info('📅 Resumen de citas por estado:');
        $this->command->info('   🟢 Completadas: ' . Cita::completadas()->count());
        $this->command->info('   🟡 Confirmadas: ' . Cita::confirmadas()->count());
        $this->command->info('   🔵 Programadas: ' . Cita::programadas()->count());
        $this->command->info('');
        $this->command->info('📊 Citas por veterinario:');
        foreach ($veterinarios as $vet) {
            $totalCitas = $vet->citas()->count();
            $this->command->info("   👨‍⚕️ {$vet->nombre_completo}: {$totalCitas} citas");
        }
        $this->command->info('');
        $this->command->info('🎯 Sistema de citas listo para usar');
    }

    private function getHoraSegunTipo(string $tipo): array
    {
        $horas = [
            Cita::TIPO_CONSULTA_GENERAL => ['hora' => 10, 'minuto' => 0],
            Cita::TIPO_VACUNACION => ['hora' => 9, 'minuto' => 30],
            Cita::TIPO_CIRUGIA => ['hora' => 8, 'minuto' => 0],
            Cita::TIPO_EMERGENCIA => ['hora' => 15, 'minuto' => 45],
            Cita::TIPO_SEGUIMIENTO => ['hora' => 14, 'minuto' => 0],
            Cita::TIPO_REVISION => ['hora' => 11, 'minuto' => 30],
            Cita::TIPO_DESPARASITACION => ['hora' => 9, 'minuto' => 0],
            Cita::TIPO_ESTETICA => ['hora' => 13, 'minuto' => 0]
        ];

        return $horas[$tipo] ?? ['hora' => 10, 'minuto' => 0];
    }

    private function getDuracionSegunTipo(string $tipo): int
    {
        $duraciones = [
            Cita::TIPO_CONSULTA_GENERAL => 30,
            Cita::TIPO_VACUNACION => 15,
            Cita::TIPO_CIRUGIA => 120,
            Cita::TIPO_EMERGENCIA => 45,
            Cita::TIPO_SEGUIMIENTO => 20,
            Cita::TIPO_REVISION => 25,
            Cita::TIPO_DESPARASITACION => 15,
            Cita::TIPO_ESTETICA => 60
        ];

        return $duraciones[$tipo] ?? 30;
    }

    private function getCostoSegunTipo(string $tipo, Veterinario $veterinario): float
    {
        $costoBase = $veterinario->tarifa_consulta ?? 50000;
        
        $multiplicadores = [
            Cita::TIPO_CONSULTA_GENERAL => 1.0,
            Cita::TIPO_VACUNACION => 0.8,
            Cita::TIPO_CIRUGIA => 3.0,
            Cita::TIPO_EMERGENCIA => 1.5,
            Cita::TIPO_SEGUIMIENTO => 0.7,
            Cita::TIPO_REVISION => 0.9,
            Cita::TIPO_DESPARASITACION => 0.6,
            Cita::TIPO_ESTETICA => 1.2
        ];

        $multiplicador = $multiplicadores[$tipo] ?? 1.0;
        return $costoBase * $multiplicador;
    }

    private function getPrioridadSegunTipo(string $tipo): string
    {
        $prioridades = [
            Cita::TIPO_CONSULTA_GENERAL => Cita::PRIORIDAD_NORMAL,
            Cita::TIPO_VACUNACION => Cita::PRIORIDAD_NORMAL,
            Cita::TIPO_CIRUGIA => Cita::PRIORIDAD_ALTA,
            Cita::TIPO_EMERGENCIA => Cita::PRIORIDAD_URGENTE,
            Cita::TIPO_SEGUIMIENTO => Cita::PRIORIDAD_NORMAL,
            Cita::TIPO_REVISION => Cita::PRIORIDAD_BAJA,
            Cita::TIPO_DESPARASITACION => Cita::PRIORIDAD_BAJA,
            Cita::TIPO_ESTETICA => Cita::PRIORIDAD_BAJA
        ];

        return $prioridades[$tipo] ?? Cita::PRIORIDAD_NORMAL;
    }

    private function crearCitasEmergencia($veterinarios, $pacientes, $adminUser): void
    {
        // Emergencia 1: Max con problema digestivo (hace 3 días)
        $max = $pacientes->firstWhere('nombre', 'Max');
        if ($max) {
            Cita::create([
                'paciente_id' => $max->id,
                'veterinario_id' => $veterinarios->first()->id,
                'propietario_id' => $max->propietario_id,
                'fecha_hora' => now()->subDays(3)->setTime(16, 30),
                'duracion_minutos' => 45,
                'tipo_cita' => Cita::TIPO_EMERGENCIA,
                'estado' => Cita::ESTADO_COMPLETADA,
                'motivo_consulta' => 'Problema digestivo agudo',
                'sintomas_reportados' => 'Vómitos, diarrea, decaimiento',
                'prioridad' => Cita::PRIORIDAD_URGENTE,
                'hora_llegada' => now()->subDays(3)->setTime(16, 25),
                'hora_inicio_atencion' => now()->subDays(3)->setTime(16, 30),
                'hora_fin_atencion' => now()->subDays(3)->setTime(17, 15),
                'costo_consulta' => 75000,
                'total_pagado' => 75000,
                'estado_pago' => Cita::ESTADO_PAGO_PAGADO,
                'creado_por_user_id' => $adminUser->id
            ]);
        }

        // Emergencia 2: Bella con herida (hace 1 semana)
        $bella = $pacientes->firstWhere('nombre', 'Bella');
        if ($bella) {
            Cita::create([
                'paciente_id' => $bella->id,
                'veterinario_id' => $veterinarios->last()->id,
                'propietario_id' => $bella->propietario_id,
                'fecha_hora' => now()->subWeek()->setTime(19, 0),
                'duracion_minutos' => 60,
                'tipo_cita' => Cita::TIPO_EMERGENCIA,
                'estado' => Cita::ESTADO_COMPLETADA,
                'motivo_consulta' => 'Herida profunda en pata',
                'sintomas_reportados' => 'Sangrado, cojera, dolor',
                'prioridad' => Cita::PRIORIDAD_URGENTE,
                'hora_llegada' => now()->subWeek()->setTime(18, 55),
                'hora_inicio_atencion' => now()->subWeek()->setTime(19, 0),
                'hora_fin_atencion' => now()->subWeek()->setTime(20, 0),
                'costo_consulta' => 80000,
                'costo_adicional' => 25000, // Sutura
                'total_pagado' => 105000,
                'estado_pago' => Cita::ESTADO_PAGO_PAGADO,
                'creado_por_user_id' => $adminUser->id
            ]);
        }
    }
}