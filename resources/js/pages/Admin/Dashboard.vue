<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard de Administrador</h1>
        <p class="mt-2 text-gray-600">Vista general del sistema y métricas de rendimiento</p>
      </div>

      <!-- System Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Projects Stats -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Proyectos</p>
              <p class="text-2xl font-semibold text-gray-900">{{ systemStats.projects.total }}</p>
              <p class="text-xs text-gray-500">{{ systemStats.projects.active }} activos</p>
            </div>
          </div>
        </div>

        <!-- Tasks Stats -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Tareas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ systemStats.tasks.total }}</p>
              <p class="text-xs text-gray-500">{{ systemStats.tasks.in_progress }} en progreso</p>
            </div>
          </div>
        </div>

        <!-- Users Stats -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Usuarios</p>
              <p class="text-2xl font-semibold text-gray-900">{{ systemStats.users.total }}</p>
              <p class="text-xs text-gray-500">{{ systemStats.users.developers }} desarrolladores</p>
            </div>
          </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Pendientes</p>
              <p class="text-2xl font-semibold text-gray-900">{{ systemStats.tasks.pending_approval }}</p>
              <p class="text-xs text-gray-500">de aprobación</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Tasks Requiring Attention -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Tareas que Requieren Atención</h2>
              <p class="mt-1 text-sm text-gray-600">Tareas que exceden el tiempo estimado en más del 20%</p>
            </div>
            <div class="p-6">
              <div v-if="tasksRequiringAttention.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-gray-500">¡Excelente! No hay tareas que requieran atención</p>
              </div>
              
              <div v-else class="space-y-4">
                <div
                  v-for="task in tasksRequiringAttention"
                  :key="task.id"
                  class="border border-red-200 rounded-lg p-4 bg-red-50"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <h3 class="text-sm font-medium text-gray-900">{{ task.name }}</h3>
                      <p class="text-xs text-gray-500 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                      <p class="text-xs text-gray-500">Desarrollador: {{ task.user?.name }}</p>
                      <div class="mt-2 flex items-center space-x-4 text-xs">
                        <span class="text-red-600 font-medium">
                          Tiempo real: {{ formatTime(task.total_time_seconds || 0) }}
                        </span>
                        <span v-if="task.estimated_hours" class="text-gray-500">
                          Estimado: {{ task.estimated_hours }}h
                        </span>
                        <span class="text-red-600 font-medium">
                          Exceso: {{ getTimeExcess(task) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <button
                        @click="viewTaskDetails(task.id)"
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                      >
                        Ver detalles
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Projects Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Proyectos Activos</h2>
              <p class="mt-1 text-sm text-gray-600">Resumen de proyectos en curso</p>
            </div>
            <div class="p-6">
              <div v-if="activeProjectsSummary.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="mt-2 text-gray-500">No hay proyectos activos</p>
              </div>
              
              <div v-else class="space-y-4">
                <div
                  v-for="project in activeProjectsSummary"
                  :key="project.id"
                  class="border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-900">{{ project.name }}</h3>
                    <span class="text-xs text-gray-500">{{ project.team_members_count }} miembros</span>
                  </div>
                  
                  <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                      <span class="text-gray-500">En progreso:</span>
                      <span class="font-medium">{{ project.in_progress_tasks_count }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                      <span class="text-gray-500">Pendientes:</span>
                      <span class="font-medium">{{ project.pending_tasks_count }}</span>
                    </div>
                  </div>
                  
                  <div class="mt-3 pt-3 border-t border-gray-100">
                    <button
                      @click="viewProjectDetails(project.id)"
                      class="text-xs text-blue-600 hover:text-blue-800"
                    >
                      Ver detalles →
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Developer Performance Metrics -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Métricas de Rendimiento por Desarrollador</h2>
            <p class="mt-1 text-sm text-gray-600">Eficiencia y productividad del equipo</p>
          </div>
          <div class="p-6">
            <div v-if="developerMetrics.length === 0" class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <p class="mt-2 text-gray-500">No hay métricas disponibles</p>
            </div>
            
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desarrollador</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tareas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eficiencia</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo Promedio</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="metric in developerMetrics" :key="metric.developer.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ metric.developer.name }}</div>
                      <div class="text-sm text-gray-500">{{ metric.developer.email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ metric.total_tasks }}</div>
                      <div class="text-xs text-gray-500">{{ metric.completed_tasks }} completadas</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ metric.formatted_time_spent }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                          <div
                            :class="{
                              'bg-green-500': metric.efficiency_percentage >= 80,
                              'bg-yellow-500': metric.efficiency_percentage >= 60 && metric.efficiency_percentage < 80,
                              'bg-red-500': metric.efficiency_percentage < 60
                            }"
                            class="h-2 rounded-full"
                            :style="{ width: Math.min(metric.efficiency_percentage, 100) + '%' }"
                          ></div>
                        </div>
                        <span class="text-sm text-gray-900">{{ metric.efficiency_percentage }}%</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ metric.average_task_time }}h
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Acciones Rápidas</h2>
            <p class="mt-1 text-sm text-gray-600">Acceso directo a las funciones principales</p>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <button
                @click="navigateToInProgressTasks"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-sm font-medium text-gray-900">Tareas en Progreso</h3>
                  <p class="text-xs text-gray-500">Ver y filtrar tareas activas</p>
                </div>
              </button>

              <button
                @click="navigateToDeveloperMetrics"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-sm font-medium text-gray-900">Métricas Detalladas</h3>
                  <p class="text-xs text-gray-500">Análisis completo de rendimiento</p>
                </div>
              </button>

              <button
                @click="navigateToTimeReports"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-sm font-medium text-gray-900">Reportes de Tiempo</h3>
                  <p class="text-xs text-gray-500">Análisis temporal por períodos</p>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Component -->
    <Toast />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import Toast from '@/components/Toast.vue'

const toast = useToast()
const loading = ref(false)
const systemStats = ref({
  projects: { total: 0, active: 0, completed: 0, completion_rate: 0 },
  tasks: { total: 0, in_progress: 0, completed: 0, pending_approval: 0, completion_rate: 0 },
  users: { total: 0, developers: 0, team_leaders: 0, admins: 0 },
  sprints: { total: 0, active: 0 }
})
const tasksRequiringAttention = ref([])
const activeProjectsSummary = ref([])
const developerMetrics = ref([])

// Methods
const loadDashboardData = async () => {
  try {
    loading.value = true
    
    // Load system stats
    const statsResponse = await fetch('/admin/stats/system')
    const statsData = await statsResponse.json()
    if (statsData.success) {
      systemStats.value = statsData.stats
    }
    
    // Load tasks requiring attention
    const attentionResponse = await fetch('/admin/tasks/requiring-attention')
    const attentionData = await attentionResponse.json()
    if (attentionData.success) {
      tasksRequiringAttention.value = attentionData.tasks
    }
    
    // Load active projects summary
    const projectsResponse = await fetch('/admin/projects/active-summary')
    const projectsData = await projectsResponse.json()
    if (projectsData.success) {
      activeProjectsSummary.value = projectsData.projects
    }
    
    // Load developer metrics
    const metricsResponse = await fetch('/admin/metrics/developers')
    const metricsData = await metricsResponse.json()
    if (metricsData.success) {
      developerMetrics.value = metricsData.metrics
    }
    
  } catch (error) {
    console.error('Error loading dashboard data:', error)
    toast.error('Error al cargar los datos del dashboard')
  } finally {
    loading.value = false
  }
}

const formatTime = (seconds) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  
  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const getTimeExcess = (task) => {
  if (!task.estimated_hours) return 'N/A'
  
  const estimatedSeconds = task.estimated_hours * 3600
  const actualSeconds = task.total_time_seconds || 0
  const excessSeconds = actualSeconds - estimatedSeconds
  
  if (excessSeconds <= 0) return '0h'
  
  const excessHours = Math.floor(excessSeconds / 3600)
  const excessMinutes = Math.floor((excessSeconds % 3600) / 60)
  
  return `${excessHours}h ${excessMinutes}m`
}

const viewTaskDetails = (taskId) => {
  router.visit(`/tasks/${taskId}`)
}

const viewProjectDetails = (projectId) => {
  router.visit(`/projects/${projectId}`)
}

const navigateToInProgressTasks = () => {
  router.visit('/admin/in-progress-tasks')
}

const navigateToDeveloperMetrics = () => {
  router.visit('/admin/developer-metrics')
}

const navigateToTimeReports = () => {
  router.visit('/admin/time-reports')
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script> 