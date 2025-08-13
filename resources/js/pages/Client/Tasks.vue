<template>
  <AppLayout title="Tareas de Proyectos">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Tareas de Mis Proyectos
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filtros -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              Filtros
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Filtro por proyecto -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Proyecto
                </label>
                <select
                  v-model="filters.project_id"
                  @change="applyFilters"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">Todos los proyectos</option>
                  <option
                    v-for="project in projects"
                    :key="project.id"
                    :value="project.id"
                  >
                    {{ project.name }}
                  </option>
                </select>
              </div>

              <!-- Filtro por sprint -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Sprint
                </label>
                <select
                  v-model="filters.sprint_id"
                  @change="applyFilters"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">Todos los sprints</option>
                  <option
                    v-for="sprint in sprints"
                    :key="sprint.id"
                    :value="sprint.id"
                  >
                    {{ sprint.name }}
                  </option>
                </select>
              </div>

              <!-- Filtro por estado -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Estado
                </label>
                <select
                  v-model="filters.status"
                  @change="applyFilters"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">Todos los estados</option>
                  <option value="to do">Pendiente</option>
                  <option value="in progress">En Progreso</option>
                  <option value="done">Completada</option>
                </select>
              </div>

              <!-- Filtro por prioridad -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Prioridad
                </label>
                <select
                  v-model="filters.priority"
                  @change="applyFilters"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">Todas las prioridades</option>
                  <option value="high">Alta</option>
                  <option value="medium">Media</option>
                  <option value="low">Baja</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Estadísticas -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              Resumen de Tareas
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-blue-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-blue-900">{{ statistics.total }}</p>
                <p class="text-sm text-blue-600">Total</p>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-yellow-900">{{ statistics.pending }}</p>
                <p class="text-sm text-yellow-600">Pendientes</p>
              </div>
              <div class="bg-blue-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-blue-900">{{ statistics.in_progress }}</p>
                <p class="text-sm text-blue-600">En Progreso</p>
              </div>
              <div class="bg-green-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-green-900">{{ statistics.completed }}</p>
                <p class="text-sm text-green-600">Completadas</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de tareas -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-900">
                Tareas ({{ filteredTasks.length }})
              </h3>
              <div class="flex space-x-2">
                <button
                  @click="sortBy = 'due_date'"
                  :class="{
                    'bg-blue-600 text-white': sortBy === 'due_date',
                    'bg-gray-200 text-gray-700': sortBy !== 'due_date'
                  }"
                  class="px-3 py-1 text-sm rounded-md transition-colors"
                >
                  Por Fecha
                </button>
                <button
                  @click="sortBy = 'priority'"
                  :class="{
                    'bg-blue-600 text-white': sortBy === 'priority',
                    'bg-gray-200 text-gray-700': sortBy !== 'priority'
                  }"
                  class="px-3 py-1 text-sm rounded-md transition-colors"
                >
                  Por Prioridad
                </button>
              </div>
            </div>

            <div v-if="loading" class="flex justify-center items-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>

            <div v-else-if="error" class="text-red-600 text-center py-4">
              {{ error }}
            </div>

            <div v-else-if="filteredTasks.length > 0" class="space-y-4">
              <div
                v-for="task in sortedTasks"
                :key="task.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start mb-3">
                  <div class="flex-1">
                    <h4 class="text-lg font-semibold text-gray-900 mb-1">{{ task.name }}</h4>
                    <p class="text-sm text-gray-600 line-clamp-2">{{ task.description }}</p>
                  </div>
                  <div class="flex flex-col items-end space-y-2 ml-4">
                    <span
                      :class="{
                        'bg-yellow-100 text-yellow-800': task.status === 'to do',
                        'bg-blue-100 text-blue-800': task.status === 'in progress',
                        'bg-green-100 text-green-800': task.status === 'done'
                      }"
                      class="px-3 py-1 text-xs font-medium rounded-full"
                    >
                      {{ getStatusLabel(task.status) }}
                    </span>
                    <span
                      :class="{
                        'bg-red-100 text-red-800': task.priority === 'high',
                        'bg-yellow-100 text-yellow-800': task.priority === 'medium',
                        'bg-green-100 text-green-800': task.priority === 'low'
                      }"
                      class="px-2 py-1 text-xs font-medium rounded-full"
                    >
                      {{ getPriorityLabel(task.priority) }}
                    </span>
                  </div>
                </div>

                <!-- Información del proyecto y sprint -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 text-sm">
                  <div>
                    <span class="font-medium text-gray-700">Proyecto:</span>
                    <span class="text-gray-600 ml-1">{{ task.project?.name || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Sprint:</span>
                    <span class="text-gray-600 ml-1">{{ task.sprint?.name || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Asignado a:</span>
                    <span class="text-gray-600 ml-1">{{ task.user?.name || 'Sin asignar' }}</span>
                  </div>
                </div>

                <!-- Progreso de la tarea -->
                <div class="mb-3">
                  <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progreso</span>
                    <span>{{ task.completion_percentage || 0 }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: (task.completion_percentage || 0) + '%' }"
                    ></div>
                  </div>
                </div>

                <!-- Detalles adicionales -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div>
                    <span class="font-medium text-gray-700">Story Points:</span>
                    <span class="text-gray-600 ml-1">{{ task.story_points || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Horas Estimadas:</span>
                    <span class="text-gray-600 ml-1">{{ task.estimated_hours || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Horas Reales:</span>
                    <span class="text-gray-600 ml-1">{{ task.actual_hours || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Fecha Límite:</span>
                    <span class="text-gray-600 ml-1">{{ formatDate(task.estimated_finish) || 'N/A' }}</span>
                  </div>
                </div>

                <!-- Acciones -->
                <div class="mt-4 flex space-x-2">
                  <button
                    @click="createSuggestion(task.project?.id, task.id)"
                    class="flex-1 px-4 py-2 bg-orange-600 text-white text-sm rounded-md hover:bg-orange-700 transition-colors"
                  >
                    Crear Sugerencia
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
              </svg>
              <p class="mt-2">No hay tareas que coincidan con los filtros</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const loading = ref(true)
const error = ref(null)
const tasks = ref([])
const projects = ref([])
const sprints = ref([])
const statistics = ref({
  total: 0,
  pending: 0,
  in_progress: 0,
  completed: 0
})

const filters = ref({
  project_id: '',
  sprint_id: '',
  status: '',
  priority: ''
})

const sortBy = ref('due_date')

const fetchTasks = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await fetch('/client/tasks/api', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error('Error al cargar las tareas')
    }
    
    const data = await response.json()
    tasks.value = data.data.tasks || []
    projects.value = data.data.projects || []
    sprints.value = data.data.sprints || []
    
    // Calcular estadísticas
    calculateStatistics()
  } catch (err) {
    error.value = err.message
    console.error('Error fetching tasks:', err)
  } finally {
    loading.value = false
  }
}

const calculateStatistics = () => {
  const filtered = filteredTasks.value
  statistics.value = {
    total: filtered.length,
    pending: filtered.filter(t => t.status === 'to do').length,
    in_progress: filtered.filter(t => t.status === 'in progress').length,
    completed: filtered.filter(t => t.status === 'done').length
  }
}

const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    if (filters.value.project_id && task.project?.id !== filters.value.project_id) return false
    if (filters.value.sprint_id && task.sprint?.id !== filters.value.sprint_id) return false
    if (filters.value.status && task.status !== filters.value.status) return false
    if (filters.value.priority && task.priority !== filters.value.priority) return false
    return true
  })
})

const sortedTasks = computed(() => {
  const tasks = [...filteredTasks.value]
  
  if (sortBy.value === 'due_date') {
    return tasks.sort((a, b) => {
      const dateA = a.estimated_finish ? new Date(a.estimated_finish) : new Date('9999-12-31')
      const dateB = b.estimated_finish ? new Date(b.estimated_finish) : new Date('9999-12-31')
      return dateA - dateB
    })
  } else if (sortBy.value === 'priority') {
    const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 }
    return tasks.sort((a, b) => {
      return (priorityOrder[b.priority] || 0) - (priorityOrder[a.priority] || 0)
    })
  }
  
  return tasks
})

const applyFilters = () => {
  calculateStatistics()
}

const getStatusLabel = (status) => {
  const labels = {
    'to do': 'Pendiente',
    'in progress': 'En Progreso',
    'done': 'Completada'
  }
  return labels[status] || status
}

const getPriorityLabel = (priority) => {
  const labels = {
    'high': 'Alta',
    'medium': 'Media',
    'low': 'Baja'
  }
  return labels[priority] || priority
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const createSuggestion = (projectId, taskId = null) => {
  const params = { project_id: projectId }
  if (taskId) params.task_id = taskId
  
  router.visit('/client/suggestions', {
    data: params
  })
}

onMounted(() => {
  fetchTasks()
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
