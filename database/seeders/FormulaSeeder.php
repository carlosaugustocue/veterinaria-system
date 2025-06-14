<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Formula;
use App\Models\FormulaMedicamento;
use App\Models\Consulta;
use App\Models\User;
use Carbon\Carbon;

class FormulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener consultas completadas que pueden tener fórmulas
        $consultasCompletas = Consulta::where('estado_consulta', Consulta::ESTADO_COMPLETADA)
                                    ->whereNotNull('plan_tratamiento')
                                    ->orWhereNotNull('medicamentos_prescritos')
                                    ->with(['paciente', 'veterinario', 'propietario'])
                                    ->get();

        if ($consultasCompletas->isEmpty()) {
            $this->command->error('❌ Error: No hay consultas completadas para crear fórmulas.');
            return;
        }

        // Obtener usuario para crear fórmulas
        $adminUser = User::whereHas('role', function ($q) {
            $q->where('nombre', 'administrador');
        })->first();

        if (!$adminUser) {
            $this->command->error('❌ Error: No hay usuario administrador.');
            return;
        }

        $formulasCreadas = 0;

        // Datos específicos de fórmulas por paciente
        $formulasData = [
            'Max' => [
                'diagnostico_resumido' => 'Paciente sano - Mantenimiento preventivo',
                'observaciones_generales' => 'Continuar con suplementos vitamínicos según plan establecido',
                'requiere_control' => false,
                'farmacia_sugerida' => 'Farmacia Veterinaria Central',
                'medicamentos' => [
                    [
                        'nombre_medicamento' => 'Vitaminas para perros adultos',
                        'principio_activo' => 'Complejo vitamínico',
                        'concentracion' => '500mg',
                        'forma_farmaceutica' => 'tableta',
                        'dosis' => '1 tableta',
                        'frecuencia' => 'cada 24 horas',
                        'duracion_tratamiento' => '30 días',
                        'cantidad_total' => 30,
                        'unidad_medida' => 'tabletas',
                        'via_administracion' => 'oral',
                        'instrucciones_uso' => 'Administrar con alimento para mejor absorción',
                        'precio_unitario' => 1200,
                        'requiere_receta' => false,
                        'es_controlado' => false
                    ]
                ]
            ],

            'Luna' => [
                'diagnostico_resumido' => 'Post-esterilización - Control de cicatrización',
                'observaciones_generales' => 'Aplicar antiséptico según indicaciones. Retirar puntos en 3 días.',
                'requiere_control' => true,
                'dias_tratamiento' => 7,
                'medicamentos' => [
                    [
                        'nombre_medicamento' => 'Povidona Yodada',
                        'principio_activo' => 'Povidona yodo',
                        'concentracion' => '10%',
                        'forma_farmaceutica' => 'spray',
                        'dosis' => '2-3 aplicaciones',
                        'frecuencia' => 'cada 12 horas',
                        'duracion_tratamiento' => '7 días',
                        'cantidad_total' => 1,
                        'unidad_medida' => 'aplicaciones',
                        'via_administracion' => 'tópica',
                        'instrucciones_uso' => 'Limpiar la herida antes de aplicar. Mantener seco.',
                        'precio_unitario' => 15000,
                        'requiere_receta' => false,
                        'es_controlado' => false
                    ]
                ]
            ],

            'Simón' => [
                'diagnostico_resumido' => 'Dermatitis alérgica alimentaria',
                'observaciones_generales' => 'Seguir dieta hipoalergénica estricta. Control en 7 días.',
                'requiere_control' => true,
                'dias_tratamiento' => 7,
                'medicamentos' => [
                    [
                        'nombre_medicamento' => 'Cetirizina',
                        'principio_activo' => 'Cetirizina diclorhidrato',
                        'concentracion' => '10mg',
                        'forma_farmaceutica' => 'tableta',
                        'dosis' => '1 tableta',
                        'frecuencia' => 'cada 12 horas',
                        'duracion_tratamiento' => '7 días',
                        'cantidad_total' => 14,
                        'unidad_medida' => 'tabletas',
                        'via_administracion' => 'oral',
                        'instrucciones_uso' => 'Administrar con alimento. No suspender abruptamente.',
                        'contraindicaciones' => 'No usar con otros antihistamínicos',
                        'efectos_secundarios' => 'Posible somnolencia leve',
                        'precio_unitario' => 800,
                        'requiere_receta' => true,
                        'es_controlado' => false
                    ],
                    [
                        'nombre_medicamento' => 'Shampoo Hipoalergénico',
                        'principio_activo' => 'Avena coloidal',
                        'concentracion' => '2%',
                        'forma_farmaceutica' => 'shampoo',
                        'dosis' => 'Baño completo',
                        'frecuencia' => '2 veces por semana',
                        'duracion_tratamiento' => '4 semanas',
                        'cantidad_total' => 1,
                        'unidad_medida' => 'aplicaciones',
                        'via_administracion' => 'tópica',
                        'instrucciones_uso' => 'Dejar actuar 5 minutos antes de enjuagar',
                        'precio_unitario' => 25000,
                        'requiere_receta' => false,
                        'es_controlado' => false
                    ]
                ]
            ],

            'Rocky' => [
                'diagnostico_resumido' => 'Síndrome respiratorio braquicefálico',
                'observaciones_generales' => 'Evitar ejercicio intenso y exposición al calor. Control en 15 días.',
                'requiere_control' => true,
                'dias_tratamiento' => 5,
                'medicamentos' => [
                    [
                        'nombre_medicamento' => 'Prednisolona',
                        'principio_activo' => 'Prednisolona',
                        'concentracion' => '5mg',
                        'forma_farmaceutica' => 'tableta',
                        'dosis' => '1 tableta',
                        'frecuencia' => 'cada 24 horas',
                        'duracion_tratamiento' => '5 días',
                        'cantidad_total' => 5,
                        'unidad_medida' => 'tabletas',
                        'via_administracion' => 'oral',
                        'instrucciones_uso' => 'Administrar con alimento. Reducir gradualmente.',
                        'contraindicaciones' => 'No suspender abruptamente',
                        'efectos_secundarios' => 'Aumento del apetito y sed',
                        'precio_unitario' => 1500,
                        'requiere_receta' => true,
                        'es_controlado' => false
                    ]
                ]
            ],

            'Toby' => [
                'diagnostico_resumido' => 'Trastorno de ansiedad - Tratamiento de soporte',
                'observaciones_generales' => 'Combinar con terapia conductual. Ambiente tranquilo.',
                'requiere_control' => true,
                'dias_tratamiento' => 14,
                'medicamentos' => [
                    [
                        'nombre_medicamento' => 'Suplemento calmante natural',
                        'principio_activo' => 'L-teanina y valeriana',
                        'concentracion' => '200mg',
                        'forma_farmaceutica' => 'cápsula',
                        'dosis' => '1 cápsula',
                        'frecuencia' => 'cada 12 horas',
                        'duracion_tratamiento' => '14 días',
                        'cantidad_total' => 28,
                        'unidad_medida' => 'cápsulas',
                        'via_administracion' => 'oral',
                        'instrucciones_uso' => 'Administrar 30 minutos antes de situaciones estresantes',
                        'precio_unitario' => 900,
                        'requiere_receta' => false,
                        'es_controlado' => false
                    ]
                ]
            ]
        ];

        // Crear fórmulas para cada consulta
        foreach ($consultasCompletas as $consulta) {
            $nombrePaciente = $consulta->paciente->nombre;
            
            if (!isset($formulasData[$nombrePaciente])) {
                continue; // Saltar si no hay datos específicos
            }

            $datos = $formulasData[$nombrePaciente];
            
            // Datos base de la fórmula
            $formulaData = [
                'consulta_id' => $consulta->id,
                'paciente_id' => $consulta->paciente_id,
                'veterinario_id' => $consulta->veterinario_id,
                'propietario_id' => $consulta->propietario_id,
                'fecha_prescripcion' => $consulta->fecha_hora,
                'diagnostico_resumido' => $datos['diagnostico_resumido'],
                'observaciones_generales' => $datos['observaciones_generales'],
                'requiere_control' => $datos['requiere_control'] ?? false,
                'farmacia_sugerida' => $datos['farmacia_sugerida'] ?? 'Farmacia Veterinaria',
                'creada_por_user_id' => $adminUser->id
            ];

            // Configurar fechas de control si es necesario
            if ($datos['requiere_control'] ?? false) {
                $formulaData['dias_tratamiento'] = $datos['dias_tratamiento'] ?? 7;
                $formulaData['fecha_proximo_control'] = $consulta->fecha_hora->addDays($datos['dias_tratamiento']);
            }

            // Configurar vencimiento (30 días por defecto)
            $formulaData['fecha_vencimiento'] = $consulta->fecha_hora->addDays(30);

            // Crear la fórmula
            $formula = Formula::create($formulaData);

            // Agregar medicamentos
            $costoTotal = 0;
            foreach ($datos['medicamentos'] as $index => $medicamentoData) {
                $medicamentoData['formula_id'] = $formula->id;
                $medicamentoData['orden_administracion'] = $index + 1;
                
                // Calcular costo total del medicamento
                if (isset($medicamentoData['cantidad_total']) && isset($medicamentoData['precio_unitario'])) {
                    $medicamentoData['costo_total'] = $medicamentoData['cantidad_total'] * $medicamentoData['precio_unitario'];
                    $costoTotal += $medicamentoData['costo_total'];
                }

                FormulaMedicamento::create($medicamentoData);
            }

            // Actualizar costo total de la fórmula
            $formula->update(['costo_estimado' => $costoTotal]);

            // Marcar como impresa las fórmulas completadas
            if ($consulta->fecha_hora < now()->subDays(2)) {
                $formula->marcarComoImpresa();
                
                // Algunas también como entregadas
                if (fake()->boolean(80)) {
                    $formula->marcarComoEntregada($consulta->propietario->nombre_completo);
                }
            }

            $formulasCreadas++;
        }

        $this->command->info("✅ Fórmulas médicas creadas: {$formulasCreadas}");
        $this->command->info('');
        $this->command->info('💊 Resumen de fórmulas por estado:');
        $this->command->info('   🟢 Activas: ' . Formula::activas()->count());
        $this->command->info('   📋 Impresas: ' . Formula::where('impresa', true)->count());
        $this->command->info('   ✅ Entregadas: ' . Formula::where('entregada_propietario', true)->count());
        $this->command->info('   🔄 Requieren control: ' . Formula::requierenControl()->count());
        $this->command->info('');
        $this->command->info('📊 Total medicamentos prescritos: ' . FormulaMedicamento::count());
        $this->command->info('');
        $this->command->info('🏥 Fórmulas por veterinario:');
        
        $veterinarios = \App\Models\Veterinario::with('user')->get();
        foreach ($veterinarios as $vet) {
            $totalFormulas = $vet->formulas()->count() ?? 0;
            if ($totalFormulas > 0) {
                $this->command->info("   👨‍⚕️ {$vet->nombre_completo}: {$totalFormulas} fórmulas");
            }
        }
        
        $this->command->info('');
        $this->command->info('🎯 Sistema de fórmulas médicas listo');
        $this->command->info('   ✅ RF-11: Generación de fórmulas médicas - IMPLEMENTADO');
    }
}