<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Navegación superior -->
      <nav class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
          <div class="flex justify-between h-16">
            <!-- Logo y navegación principal -->
            <div class="flex">
              <!-- Logo -->
              <div class="flex-shrink-0 flex items-center">
                <Link href="/dashboard" class="text-white text-xl font-bold">
                  🏥 Clínica Veterinaria
                </Link>
              </div>
  
              <!-- Navegación principal -->
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink href="/dashboard" :active="route().current('dashboard')">
                  Dashboard
                </NavLink>
                <NavLink href="/citas" :active="route().current('citas.*')">
                  Citas
                </NavLink>
                <NavLink href="/pacientes" :active="route().current('pacientes.*')">
                  Pacientes
                </NavLink>
                <NavLink href="/consultas" :active="route().current('consultas.*')" v-if="canAccess('consultas')">
                  Consultas
                </NavLink>
                <NavLink href="/formulas" :active="route().current('formulas.*')" v-if="canAccess('formulas')">
                  Fórmulas
                </NavLink>
              </div>
            </div>
  
            <!-- Usuario y opciones -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
              <!-- Notificaciones -->
              <button class="p-1 rounded-full text-white hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white">
                <span class="sr-only">Ver notificaciones</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5v0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                </svg>
              </button>
  
              <!-- Dropdown del usuario -->
              <div class="ml-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                        {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </span>
                  </template>
  
                  <template #content>
                    <DropdownLink href="/profile">Perfil</DropdownLink>
                    <DropdownLink href="/logout" method="post" as="button">
                      Cerrar Sesión
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>
          </div>
        </div>
      </nav>
  
      <!-- Breadcrumbs -->
      <div class="bg-white shadow-sm border-b border-gray-200" v-if="breadcrumbs">
        <div class="max-w-7xl mx-auto py-3 px-4">
          <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
              <li v-for="(breadcrumb, index) in breadcrumbs" :key="index" class="inline-flex items-center">
                <Link v-if="breadcrumb.href" :href="breadcrumb.href" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                  {{ breadcrumb.title }}
                </Link>
                <span v-else class="text-sm font-medium text-gray-500">
                  {{ breadcrumb.title }}
                </span>
                <svg v-if="index < breadcrumbs.length - 1" class="w-6 h-6 text-gray-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
              </li>
            </ol>
          </nav>
        </div>
      </div>
  
      <!-- Contenido principal -->
      <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Alertas globales -->
          <div v-if="$page.props.flash.message" class="mb-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
              {{ $page.props.flash.message }}
            </div>
          </div>
  
          <div v-if="$page.props.flash.error" class="mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
              {{ $page.props.flash.error }}
            </div>
          </div>
  
          <!-- Título de página -->
          <div class="mb-6" v-if="title">
            <h1 class="text-3xl font-bold text-gray-900">{{ title }}</h1>
            <p v-if="subtitle" class="mt-1 text-sm text-gray-600">{{ subtitle }}</p>
          </div>
  
          <!-- Contenido de la página -->
          <slot />
        </div>
      </main>
  
      <!-- Footer -->
      <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <p class="text-center text-sm text-gray-500">
            © {{ new Date().getFullYear() }} Sistema de Clínica Veterinaria. Todos los derechos reservados.
          </p>
        </div>
      </footer>
    </div>
  </template>
  
  <script setup>
  import { Link } from '@inertiajs/vue3'
  import Dropdown from '@/Components/Dropdown.vue'
  import DropdownLink from '@/Components/DropdownLink.vue'
  import NavLink from '@/Components/NavLink.vue'
  
  defineProps({
    title: String,
    subtitle: String,
    breadcrumbs: Array,
  })
  
  // Función para verificar permisos
  const canAccess = (module) => {
    const user = usePage().props.auth.user
    // Aquí puedes implementar lógica de permisos basada en roles
    return true // Por ahora, permitir todo
  }
  </script>