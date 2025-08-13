<template>
  <AppLayout title="Dashboard de Cliente">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Dashboard de Cliente
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Resumen General -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              Resumen de Proyectos
            </h3>
            
            <div v-if="loading" class="flex justify-center items-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>

            <div v-else-if="error" class="text-red-600 text-center py-4">
              {{ error }}
            </div>

            <div v-else-if="dashboardData" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <!-- Total de Proyectos -->
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                  <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Total Proyectos</p>
                    <p class="text-2xl font-bold text-blue-900">{{ dashboardData.total_projects }}</p>
                  </div>
                </div>
              </div>

              <!-- Progreso Promedio -->
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center">
                  <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Progreso Promedio</p>
                    <p class="text-2xl font-bold text-green-900">{{ dashboardData.average_progress }}%</p>
                  </div>
                </div>
              </div>

              <!-- Tareas Completadas -->
              <div class="bg-purple-50 p-4 rounded-lg">
                <div class="flex items-center">
                  <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-purple-600">Tareas Completadas</p>
                    <p class="text-2xl font-bold text-purple-900">{{ dashboardData.completed_tasks }}</p>
                  </div>
                </div>
              </div>

              <!-- Sugerencias Pendientes -->
              <div class="bg-orange-50 p-4 rounded-lg">
                <div class="flex items-center">
                  <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-orange-600">Sugerencias Pendientes</p>
                    <p class="text-2xl font-bold text-orange-900">{{ dashboardData.pending_suggestions }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Proyectos Activos -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-900">
                Proyectos Activos
              </h3>
              <Link
                :href="route('client.projects')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Ver Todos
              </Link>
            </div>

            <div v-if="loading" class="flex justify-center items-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>

            <div v-else-if="dashboardData && dashboardData.projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="project in dashboardData.projects"
                :key="project.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start mb-3">
                  <h4 class="text-lg font-semibold text-gray-900">{{ project.name }}</h4>
                  <span
                    :class="{
                      'bg-green-100 text-green-800': project.status === 'active',
                      'bg-yellow-100 text-yellow-800': project.status === 'paused',
                      'bg-red-100 text-red-800': project.status === 'completed'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ getStatusLabel(project.status) }}
                  </span>
                </div>

                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ project.description }}</p>

                <!-- Progreso del Proyecto -->
                <div class="mb-4">
                  <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progreso</span>
                                         <span>{{ project.progress_percentage }}%</span>
                   </div>
                   <div class="w-full bg-gray-200 rounded-full h-2">
                     <div
                       class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                       :style="{ width: project.progress_percentage + '%' }"
                     ></div>
                  </div>
                </div>

                <!-- Sprint Actual -->
                <div v-if="project.current_sprint" class="mb-4">
                  <p class="text-sm text-gray-600 mb-1">Sprint Actual:</p>
                  <p class="text-sm font-medium text-gray-900">{{ project.current_sprint.name }}</p>
                  <p class="text-xs text-gray-500">
                    {{ formatDate(project.current_sprint.start_date) }} - {{ formatDate(project.current_sprint.end_date) }}
                  </p>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <p class="text-gray-600">Tareas</p>
                    <p class="font-semibold">{{ project.total_tasks }}</p>
                  </div>
                  <div>
                    <p class="text-gray-600">Completadas</p>
                    <p class="font-semibold text-green-600">{{ project.completed_tasks }}</p>
                  </div>
                </div>

                <!-- Acciones -->
                <div class="mt-4 flex space-x-2">
                  <Link
                    :href="route('client.project.details', project.id)"
                    class="flex-1 text-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                  >
                    Ver Detalles
                  </Link>
                  <Link
                    :href="route('client.suggestions')"
                    class="flex-1 text-center px-3 py-2 bg-orange-600 text-white text-sm rounded-md hover:bg-orange-700 transition-colors"
                  >
                    Sugerencias
                  </Link>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
              <p class="mt-2">No hay proyectos activos asignados</p>
            </div>
          </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              Acciones Rápidas
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <Link
                :href="route('client.projects')"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="p-2 bg-blue-100 rounded-lg mr-4">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900">Ver Proyectos</h4>
                  <p class="text-sm text-gray-600">Explorar todos tus proyectos</p>
                </div>
              </Link>

              <Link
                :href="route('client.suggestions')"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="p-2 bg-orange-100 rounded-lg mr-4">
                  <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900">Sugerencias</h4>
                  <p class="text-sm text-gray-600">Gestionar tus sugerencias</p>
                </div>
              </Link>

              <Link
                :href="route('client.tasks')"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="p-2 bg-green-100 rounded-lg mr-4">
                  <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900">Ver Tareas</h4>
                  <p class="text-sm text-gray-600">Revisar tareas del proyecto</p>
                </div>
              </Link>

              <Link
                :href="route('client.sprints')"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="p-2 bg-purple-100 rounded-lg mr-4">
                  <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900">Ver Sprints</h4>
                  <p class="text-sm text-gray-600">Revisar sprints del proyecto</p>
                </div>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const loading = ref(true)
const error = ref(null)
const dashboardData = ref(null)

const fetchDashboardData = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await fetch('/client/dashboard/api', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error('Error al cargar los datos del dashboard')
    }
    
    const data = await response.json()
    dashboardData.value = data.data
  } catch (err) {
    error.value = err.message
    console.error('Error fetching dashboard data:', err)
  } finally {
    loading.value = false
  }
}

const getStatusLabel = (status) => {
  const labels = {
    'active': 'Activo',
    'paused': 'Pausado',
    'completed': 'Completado'
  }
  return labels[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
