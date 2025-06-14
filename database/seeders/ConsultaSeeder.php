<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Consulta;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;

class ConsultaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener citas completadas para crear consultas
        $citasCompletadas = Cita::where('estado', Cita::ESTADO_COMPLETADA)
                               ->with(['paciente.especie', 'veterinario.user', 'propietario'])
                               ->get();

        if ($citasCompletadas->isEmpty()) {
            $this->command->error('❌ Error: No hay citas completadas para crear consultas.');
            return;
        }

        // Obtener veterinario para crear consultas
        $veterinarios = User::whereHas('role', function ($q) {
            $q->where('nombre', 'veterinario');
        })->get();

        if ($veterinarios->isEmpty()) {
            $this->command->error('❌ Error: No hay veterinarios para crear consultas.');
            return;
        }

        $consultasCreadas = 0;

        // Datos específicos de consultas médicas por paciente
        $consultasData = [
            'Max' => [
                'motivo_consulta' => 'Revisión general anual',
                'sintomas_reportados' => 'Perro activo y saludable, propietario solicita chequeo preventivo',
                'sintomas_observados' => 'Paciente alerta, reactivo, sin signos de dolor o malestar',
                'signos_vitales' => [
                    'peso' => 32.5,
                    'temperatura' => 38.2,
                    'frecuencia_cardiaca' => 110,
                    'frecuencia_respiratoria' => 28
                ],
                'examen_fisico' => 'Condición corporal 4/5. Pelaje brillante. Mucosas rosadas. Ganglios normales. Auscultación cardiopulmonar normal.',
                'diagnostico_definitivo' => 'Paciente clínicamente saludable',
                'plan_tratamiento' => 'Continuar con dieta balanceada y ejercicio regular',
                'recomendaciones_generales' => 'Mantener vacunación al día, desparasitación cada 3 meses',
                'estado_paciente' => 'estable',
                'pronostico' => 'excelente'
            ],
            
            'Luna' => [
                'motivo_consulta' => 'Control post-esterilización',
                'sintomas_reportados' => 'Gata operada hace 10 días, evolución aparentemente normal',
                'sintomas_observados' => 'Herida quirúrgica en proceso de cicatrización',
                'signos_vitales' => [
                    'peso' => 4.2,
                    'temperatura' => 38.8,
                    'frecuencia_cardiaca' => 180,
                    'frecuencia_respiratoria' => 32
                ],
                'examen_fisico' => 'Herida quirúrgica limpia, sin secreciones. Sutura íntegra. Abdomen blando.',
                'diagnostico_definitivo' => 'Evolución post-quirúrgica satisfactoria',
                'plan_tratamiento' => 'Retirar puntos en 3 días',
                'recomendaciones_generales' => 'Mantener collar isabelino hasta retirar puntos',
                'estado_paciente' => 'mejorado',
                'pronostico' => 'excelente'
            ],

            'Simón' => [
                'motivo_consulta' => 'Alergia alimentaria',
                'sintomas_reportados' => 'Picazón intensa, enrojecimiento de piel, se rasca frecuentemente',
                'sintomas_observados' => 'Eritema en abdomen y extremidades, excoriaciones por rascado',
                'signos_vitales' => [
                    'peso' => 28.0,
                    'temperatura' => 38.5,
                    'frecuencia_cardiaca' => 120,
                    'frecuencia_respiratoria' => 30
                ],
                'examen_fisico' => 'Dermatitis alérgica generalizada. Piel enrojecida en abdomen y patas.',
                'diagnostico_definitivo' => 'Dermatitis alérgica por alimento',
                'plan_tratamiento' => 'Dieta hipoalergénica, antihistamínicos',
                'medicamentos_prescritos' => 'Cetirizina 10mg cada 12h por 7 días',
                'recomendaciones_generales' => 'Eliminar pollo de la dieta, usar alimento hipoalergénico',
                'requiere_seguimiento' => true,
                'dias_seguimiento' => 7,
                'estado_paciente' => 'sin_cambios',
                'pronostico' => 'bueno'
            ],

            'Bella' => [
                'motivo_consulta' => 'Chequeo de rutina',
                'sintomas_reportados' => 'Sin síntomas específicos, chequeo anual',
                'sintomas_observados' => 'Paciente en excelentes condiciones',
                'signos_vitales' => [
                    'peso' => 26.8,
                    'temperatura' => 38.3,
                    'frecuencia_cardiaca' => 105,
                    'frecuencia_respiratoria' => 26
                ],
                'examen_fisico' => 'Examen físico completo normal. Condición corporal 3/5.',
                'diagnostico_definitivo' => 'Paciente sano',
                'plan_tratamiento' => 'Mantener rutina actual',
                'recomendaciones_generales' => 'Continuar con ejercicio diario y dieta balanceada',
                'estado_paciente' => 'estable',
                'pronostico' => 'excelente'
            ],

            'Rocky' => [
                'motivo_consulta' => 'Problemas respiratorios',
                'sintomas_reportados' => 'Dificultad para respirar, ronquidos fuertes, cansancio',
                'sintomas_observados' => 'Disnea inspiratoria, estridor, cianosis leve',
                'signos_vitales' => [
                    'peso' => 11.5,
                    'temperatura' => 38.7,
                    'frecuencia_cardiaca' => 140,
                    'frecuencia_respiratoria' => 45
                ],
                'examen_fisico' => 'Síndrome braquicefálico severo. Estenosis de narinas.',
                'diagnostico_definitivo' => 'Síndrome respiratorio braquicefálico',
                'plan_tratamiento' => 'Manejo conservador, evitar ejercicio intenso y calor',
                'medicamentos_prescritos' => 'Prednisolona 5mg cada 24h por 5 días',
                'recomendaciones_generales' => 'Mantener en ambiente fresco, peso ideal',
                'requiere_seguimiento' => true,
                'dias_seguimiento' => 15,
                'estado_paciente' => 'estable',
                'pronostico' => 'reservado'
            ],

            'Mimi' => [
                'motivo_consulta' => 'Corte de pelo y aseo',
                'sintomas_reportados' => 'Pelo muy enredado, necesita aseo profundo',
                'sintomas_observados' => 'Pelaje muy enmarañado, requiere corte completo',
                'signos_vitales' => [
                    'peso' => 5.1,
                    'temperatura' => 38.6,
                    'frecuencia_cardiaca' => 175,
                    'frecuencia_respiratoria' => 34
                ],
                'examen_fisico' => 'Estado general bueno. Pelaje enmarañado pero piel sana.',
                'diagnostico_definitivo' => 'Paciente sano, procedimiento estético',
                'procedimientos_realizados' => 'Corte completo de pelo, baño medicado, limpieza de oídos',
                'recomendaciones_generales' => 'Cepillado diario para evitar enredos',
                'estado_paciente' => 'estable',
                'pronostico' => 'excelente'
            ],

            'Toby' => [
                'motivo_consulta' => 'Ansiedad y comportamiento',
                'sintomas_reportados' => 'Temblores, ansiedad, comportamiento agresivo',
                'sintomas_observados' => 'Paciente nervioso, temblores, hipervigilancia',
                'signos_vitales' => [
                    'peso' => 2.1,
                    'temperatura' => 38.4,
                    'frecuencia_cardiaca' => 160,
                    'frecuencia_respiratoria' => 40
                ],
                'examen_fisico' => 'Físicamente normal. Signos de estrés y ansiedad.',
                'diagnostico_definitivo' => 'Trastorno de ansiedad generalizada',
                'plan_tratamiento' => 'Terapia comportamental, ambiente tranquilo',
                'recomendaciones_generales' => 'Evitar ruidos fuertes, rutina estable',
                'estado_paciente' => 'estable',
                'pronostico' => 'bueno'
            ]
        ];

        // Crear consultas para cada cita completada
        foreach ($citasCompletadas as $cita) {
            $nombrePaciente = $cita->paciente->nombre;
            
            if (!isset($consultasData[$nombrePaciente])) {
                continue; // Saltar si no hay datos específicos
            }

            $datos = $consultasData[$nombrePaciente];
            
            // Datos base de la consulta
            $consultaData = [
                'cita_id' => $cita->id,
                'paciente_id' => $cita->paciente_id,
                'veterinario_id' => $cita->veterinario_id,
                'propietario_id' => $cita->propietario_id,
                'fecha_hora' => $cita->fecha_hora,
                'tipo_consulta' => $this->mapearTipoConsulta($cita->tipo_cita),
                'estado_consulta' => Consulta::ESTADO_COMPLETADA,
                'duracion_minutos' => $cita->duracion_minutos,
                'costo_consulta' => $cita->costo_consulta,
                'total_consulta' => $cita->costo_consulta,
                'creado_por_user_id' => $cita->creado_por_user_id
            ];

            // Agregar datos médicos específicos
            $consultaData = array_merge($consultaData, $datos);

            // Configurar seguimiento si es necesario
            if (isset($datos['requiere_seguimiento']) && $datos['requiere_seguimiento']) {
                $consultaData['fecha_proximo_control'] = $cita->fecha_hora->addDays($datos['dias_seguimiento']);
                $consultaData['motivo_seguimiento'] = 'Control de evolución del tratamiento';
            }

            Consulta::create($consultaData);
            $consultasCreadas++;
        }

        // Crear algunas consultas de emergencia adicionales
        $this->crearConsultasEmergencia($citasCompletadas);
        $consultasCreadas += 2;

        $this->command->info("✅ Consultas médicas creadas: {$consultasCreadas}");
        $this->command->info('');
        $this->command->info('🩺 Resumen de consultas por tipo:');
        
        // ⚠️ CORREGIDO: Usar query() antes de los scopes
        $this->command->info('   🔵 Generales: ' . Consulta::query()->consultasGenerales()->count());
        $this->command->info('   🚨 Emergencias: ' . Consulta::query()->emergencias()->count());
        $this->command->info('   🔄 Seguimientos: ' . Consulta::query()->seguimientos()->count());
        $this->command->info('   ⚕️ Procedimientos: ' . Consulta::query()->cirugias()->count());
        $this->command->info('');
        $this->command->info('📊 Estados de consultas:');
        $this->command->info('   ✅ Completadas: ' . Consulta::query()->completadas()->count());
        $this->command->info('   ⏳ En progreso: ' . Consulta::query()->enProgreso()->count());
        $this->command->info('   ✔️ Aprobadas: ' . Consulta::query()->aprobadas()->count());
        $this->command->info('');
        $this->command->info('🔄 Seguimientos pendientes: ' . Consulta::query()->requierenSeguimiento()->count());
        $this->command->info('');
        $this->command->info('🎯 Sistema de consultas médicas listo');
    }

    private function mapearTipoConsulta(string $tipoCita): string
    {
        $mapeo = [
            'consulta_general' => Consulta::TIPO_CONSULTA_GENERAL,
            'emergencia' => Consulta::TIPO_EMERGENCIA,
            'seguimiento' => Consulta::TIPO_SEGUIMIENTO,
            'cirugia' => Consulta::TIPO_CIRUGIA,
            'vacunacion' => Consulta::TIPO_VACUNACION,
            'revision' => Consulta::TIPO_REVISION,
            'estetica' => Consulta::TIPO_ESTETICA
        ];

        return $mapeo[$tipoCita] ?? Consulta::TIPO_CONSULTA_GENERAL;
    }

    private function crearConsultasEmergencia($citasCompletadas): void
    {
        // Buscar emergencias en las citas
        $emergencias = $citasCompletadas->where('tipo_cita', 'emergencia');

        foreach ($emergencias->take(2) as $citaEmergencia) {
            $consultaEmergencia = [
                'cita_id' => $citaEmergencia->id,
                'paciente_id' => $citaEmergencia->paciente_id,
                'veterinario_id' => $citaEmergencia->veterinario_id,
                'propietario_id' => $citaEmergencia->propietario_id,
                'fecha_hora' => $citaEmergencia->fecha_hora,
                'tipo_consulta' => Consulta::TIPO_EMERGENCIA,
                'motivo_consulta' => 'Atención de emergencia',
                'sintomas_reportados' => 'Síntomas agudos que requieren atención inmediata',
                'sintomas_observados' => 'Paciente en estado de emergencia',
                'signos_vitales' => [
                    'peso' => $citaEmergencia->paciente->peso,
                    'temperatura' => 39.2,
                    'frecuencia_cardiaca' => 150,
                    'frecuencia_respiratoria' => 50
                ],
                'examen_fisico' => 'Paciente en estado crítico, requiere intervención inmediata',
                'diagnostico_definitivo' => 'Emergencia médica',
                'plan_tratamiento' => 'Estabilización y tratamiento de soporte',
                'estado_paciente' => 'estable',
                'pronostico' => 'reservado',
                'estado_consulta' => Consulta::ESTADO_COMPLETADA,
                'duracion_minutos' => $citaEmergencia->duracion_minutos,
                'costo_consulta' => $citaEmergencia->costo_consulta,
                'total_consulta' => $citaEmergencia->costo_consulta,
                'creado_por_user_id' => $citaEmergencia->creado_por_user_id
            ];

            Consulta::create($consultaEmergencia);
        }
    }
}