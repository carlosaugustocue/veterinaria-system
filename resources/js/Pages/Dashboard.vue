<template>
    <VeterinaryLayout title="Dashboard" subtitle="Resumen general del sistema">
      <!-- Estadísticas rápidas -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Citas de hoy -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Citas Hoy</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.citasHoy }}</dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <Link href="/citas" class="font-medium text-blue-600 hover:text-blue-500">
                Ver todas las citas
              </Link>
            </div>
          </div>
        </div>
  
        <!-- Pacientes registrados -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Pacientes</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.totalPacientes }}</dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <Link href="/pacientes" class="font-medium text-green-600 hover:text-green-500">
                Ver pacientes
              </Link>
            </div>
          </div>
        </div>
  
        <!-- Consultas este mes -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Consultas Este Mes</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.consultasEsteMes }}</dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <Link href="/consultas" class="font-medium text-yellow-600 hover:text-yellow-500">
                Ver consultas
              </Link>
            </div>
          </div>
        </div>
  
        <!-- Fórmulas activas -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Fórmulas Activas</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.formulasActivas }}</dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <Link href="/formulas" class="font-medium text-purple-600 hover:text-purple-500">
                Ver fórmulas
              </Link>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Sección principal -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Citas de hoy -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Citas de Hoy
            </h3>
            
            <div v-if="citasHoy.length === 0" class="text-center py-8 text-gray-500">
              No hay citas programadas para hoy
            </div>
            
            <div v-else class="space-y-4">
              <div 
                v-for="cita in citasHoy" 
                :key="cita.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h4 class="text-sm font-medium text-gray-900">
                      {{ cita.paciente.nombre }}
                    </h4>
                    <p class="text-sm text-gray-500">
                      {{ cita.propietario.user.nombre }} {{ cita.propietario.user.apellido }}
                    </p>
                    <p class="text-xs text-gray-400">
                      {{ formatTime(cita.fecha_hora) }} - {{ cita.tipo_cita }}
                    </p>
                  </div>
                  <div class="flex-shrink-0">
                    <span 
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getEstadoColor(cita.estado)"
                    >
                      {{ cita.estado }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
  
            <div class="mt-4 pt-4 border-t border-gray-200">
              <Link 
                href="/citas" 
                class="text-sm font-medium text-blue-600 hover:text-blue-500"
              >
                Ver todas las citas del día →
              </Link>
            </div>
          </div>
        </div>
  
        <!-- Recordatorios y alertas -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Recordatorios y Alertas
            </h3>
            
            <div class="space-y-4">
              <!-- Controles médicos pendientes -->
              <div v-if="controlesPendientes.length > 0" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h4 class="text-sm font-medium text-yellow-800">
                      Controles Médicos Pendientes
                    </h4>
                    <div class="mt-2 text-sm text-yellow-700">
                      <ul class="space-y-1">
                        <li v-for="control in controlesPendientes.slice(0, 3)" :key="control.id">
                          {{ control.paciente.nombre }} - {{ formatDate(control.fecha_proximo_control) }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Fórmulas próximas a vencer -->
              <div v-if="formulasProximasVencer.length > 0" class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h4 class="text-sm font-medium text-red-800">
                      Fórmulas Próximas a Vencer
                    </h4>
                    <div class="mt-2 text-sm text-red-700">
                      <ul class="space-y-1">
                        <li v-for="formula in formulasProximasVencer.slice(0, 3)" :key="formula.id">
                          {{ formula.numero_formula }} - Vence {{ formatDate(formula.fecha_vencimiento) }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Mensaje cuando no hay alertas -->
              <div v-if="controlesPendientes.length === 0 && formulasProximasVencer.length === 0" class="text-center py-8 text-gray-500">
                ✅ No hay alertas pendientes
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Gráfico de actividad (placeholder) -->
      <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Actividad de la Semana
          </h3>
          <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center">
            <p class="text-gray-500">Gráfico de actividad semanal (próximamente)</p>
          </div>
        </div>
      </div>
    </VeterinaryLayout>
  </template>
  
  <script setup>
  import { Link } from '@inertiajs/vue3'
  import VeterinaryLayout from '@/Layouts/VeterinaryLayout.vue'
  
  const props = defineProps({
    stats: Object,
    citasHoy: Array,
    controlesPendientes: Array,
    formulasProximasVencer: Array,
  })
  
  // Helpers para formateo
  const formatTime = (datetime) => {
    return new Date(datetime).toLocaleTimeString('es-ES', { 
      hour: '2-digit', 
      minute: '2-digit' 
    })
  }
  
  const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  }
  
  const getEstadoColor = (estado) => {
    const colores = {
      'programada': 'bg-blue-100 text-blue-800',
      'confirmada': 'bg-green-100 text-green-800',
      'en_proceso': 'bg-yellow-100 text-yellow-800',
      'completada': 'bg-gray-100 text-gray-800',
      'cancelada': 'bg-red-100 text-red-800'
    }
    return colores[estado] || 'bg-gray-100 text-gray-800'
  }
  </script>