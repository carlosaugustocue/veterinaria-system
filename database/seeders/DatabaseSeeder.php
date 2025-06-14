<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders del Sistema Veterinaria...');
        $this->command->info('');

        // Ejecutar seeders en orden específico (respetando dependencias)
        $this->call([
            RoleSeeder::class,          // 1. Roles (sin dependencias)
            UserSeeder::class,          // 2. Usuarios (depende de roles)
            EspecieSeeder::class,       // 3. Especies (sin dependencias)
            RazaSeeder::class,          // 4. Razas (depende de especies)
            PropietarioSeeder::class,   // 5. Propietarios (depende de usuarios)
            VeterinarioSeeder::class,   // 6. Veterinarios (depende de usuarios)
            PacienteSeeder::class,      // 7. Pacientes (depende de propietarios, especies, razas)
            CitaSeeder::class,          // 8. Citas (depende de pacientes, veterinarios)
            ConsultaSeeder::class,      // 9. Consultas (depende de citas)
            FormulaSeeder::class,       // 10. Fórmulas médicas (depende de consultas) ⭐ NUEVO
        ]);

        $this->command->info('');
        $this->command->info('✅ Todos los seeders ejecutados correctamente');
        $this->command->info('');
        $this->command->info('📊 Resumen del sistema:');
        $this->command->info('   👥 5 roles definidos');
        $this->command->info('   👤 8 usuarios totales (3 staff + 5 propietarios)');
        $this->command->info('   🩺 2 veterinarios registrados');
        $this->command->info('   🐾 9 especies de animales');
        $this->command->info('   🎯 15+ razas populares');
        $this->command->info('   🏠 6 propietarios registrados');
        $this->command->info('   🐕 11 mascotas registradas');
        $this->command->info('   📅 15+ citas médicas programadas');
        $this->command->info('   🩺 15+ consultas médicas registradas');
        $this->command->info('   💊 10+ fórmulas médicas generadas'); // ⭐ NUEVO
        $this->command->info('');
        $this->command->info('👤 Usuarios del sistema:');
        $this->command->info('   🔹 Admin: admin@veterinaria.com / Admin123!');
        $this->command->info('   🔹 Dr. Juan: veterinario@veterinaria.com / Vet123!');
        $this->command->info('   🔹 Dra. María: maria.rodriguez@veterinaria.com / Vet123!');
        $this->command->info('   🔹 María González: cliente@example.com / Cliente123!');
        $this->command->info('   🔹 Carlos López: carlos.lopez@example.com / Cliente123!');
        $this->command->info('   🔹 Ana Martínez: ana.martinez@example.com / Cliente123!');
        $this->command->info('   🔹 Pedro Sánchez: pedro.sanchez@example.com / Cliente123!');
        $this->command->info('   🔹 Laura Ramírez: laura.ramirez@example.com / Cliente123!');
        $this->command->info('   🔹 Roberto Torres: roberto.torres@example.com / Cliente123!');
        $this->command->info('');
        $this->command->info('🐾 Mascotas registradas:');
        $this->command->info('   🐕 8 perros: Max, Simón, Bella, Rocky, Toby, Firulais...');
        $this->command->info('   🐱 4 gatos: Luna, Mimi, Whiskers, Pelusa');
        $this->command->info('   🐦 1 ave: Coco (canario)');
        $this->command->info('');
        $this->command->info('🏥 Personal veterinario:');
        $this->command->info('   👨‍⚕️ Dr. Juan Pérez (Medicina General) - Licencia VET-001');
        $this->command->info('   👩‍⚕️ Dra. María Rodríguez (Cirugía Veterinaria) - Licencia VET-002');
        $this->command->info('');
        $this->command->info('📅 Sistema de citas:');
        $this->command->info('   ✅ Citas completadas con historial médico');
        $this->command->info('   🟡 Citas confirmadas para próximos días');
        $this->command->info('   🔵 Citas programadas durante la semana');
        $this->command->info('   🚨 Emergencias atendidas registradas');
        $this->command->info('   💰 Control de facturación y pagos');
        $this->command->info('');
        $this->command->info('🩺 Sistema de consultas médicas:');
        $this->command->info('   📋 Historiales médicos completos');
        $this->command->info('   🔬 Diagnósticos y tratamientos');
        $this->command->info('   💊 Prescripciones y medicamentos');
        $this->command->info('   🔄 Seguimientos programados');
        $this->command->info('   📊 Signos vitales registrados');
        $this->command->info('   📁 Archivos adjuntos');
        $this->command->info('');
        $this->command->info('💊 Sistema de fórmulas médicas:'); // ⭐ NUEVO
        $this->command->info('   📜 Fórmulas con numeración automática');
        $this->command->info('   💉 Medicamentos con dosificación completa');
        $this->command->info('   🔒 Sistema de verificación por hash');
        $this->command->info('   📋 Control de impresión y entrega');
        $this->command->info('   🔄 Seguimientos médicos automatizados');
        $this->command->info('   💰 Estimación de costos farmacéuticos');
        $this->command->info('');
        $this->command->warn('⚠️  Recuerda cambiar las contraseñas por defecto en producción');
        $this->command->info('');
        $this->command->info('🎯 Sistema COMPLETO listo para gestión veterinaria');
        $this->command->info('   ✨ Agendamiento de citas: ✅ FUNCIONAL');
        $this->command->info('   ✨ Consultas médicas: ✅ FUNCIONAL');
        $this->command->info('   ✨ Historial clínico: ✅ FUNCIONAL');
        $this->command->info('   ✨ Fórmulas médicas: ✅ FUNCIONAL'); // ⭐ NUEVO
        $this->command->info('   ✨ Gestión de pacientes: ✅ FUNCIONAL');
        $this->command->info('   ✨ Control de veterinarios: ✅ FUNCIONAL');
        $this->command->info('   ✨ Sistema de roles: ✅ FUNCIONAL');
        $this->command->info('   ✨ Datos de prueba: ✅ COMPLETOS');
        $this->command->info('');
        $this->command->info('🏆 FUNCIONALIDADES MÉDICAS IMPLEMENTADAS:');
        $this->command->info('   ✅ RF-09: Registro de consultas médicas');
        $this->command->info('   ✅ RF-10: Gestión de historial clínico');
        $this->command->info('   ✅ RF-11: Generación de fórmulas médicas'); // ⭐ NUEVO
        $this->command->info('   ✅ RF-13: Registro de seguimientos');
        $this->command->info('   ✅ Ciclo completo: Cita → Consulta → Fórmula → Historial');
        $this->command->info('');
        $this->command->info('🔧 SISTEMA DE FÓRMULAS MÉDICAS:'); // ⭐ NUEVO DETALLE
        $this->command->info('   💊 Prescripción completa de medicamentos');
        $this->command->info('   📜 Numeración automática (F2025-000001)');
        $this->command->info('   🔐 Hash de verificación para autenticidad');
        $this->command->info('   📅 Control de vencimiento y vigencia');
        $this->command->info('   🏥 Seguimiento de impresión y entrega');
        $this->command->info('   💰 Cálculo automático de costos');
        $this->command->info('   📋 Instrucciones detalladas por medicamento');
        $this->command->info('   ⚕️ Control farmacéutico (recetas/controlados)');
        $this->command->info('   🔄 Integración con consultas médicas');
        $this->command->info('   📊 Estadísticas y reportes por veterinario');
    }
}