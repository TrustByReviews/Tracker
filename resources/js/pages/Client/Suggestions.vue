<template>
  <AppLayout title="Sugerencias">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Mis Sugerencias
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Estadísticas -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              Resumen de Sugerencias
            </h3>
            
            <div v-if="loadingStats" class="flex justify-center items-center py-4">
              <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            </div>

            <div v-else-if="statistics" class="grid grid-cols-2 md:grid-cols-5 gap-4">
              <div class="bg-blue-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-blue-900">{{ statistics.total }}</p>
                <p class="text-sm text-blue-600">Total</p>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-yellow-900">{{ statistics.pending }}</p>
                <p class="text-sm text-yellow-600">Pendientes</p>
              </div>
              <div class="bg-blue-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-blue-900">{{ statistics.reviewed }}</p>
                <p class="text-sm text-blue-600">Revisadas</p>
              </div>
              <div class="bg-green-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-green-900">{{ statistics.implemented }}</p>
                <p class="text-sm text-green-600">Implementadas</p>
              </div>
              <div class="bg-red-50 p-4 rounded-lg text-center">
                <p class="text-2xl font-bold text-red-900">{{ statistics.rejected }}</p>
                <p class="text-sm text-red-600">Rechazadas</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Botón para crear nueva sugerencia -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold text-gray-900">
                Mis Sugerencias
              </h3>
              <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nueva Sugerencia
              </button>
            </div>
          </div>
        </div>

        <!-- Lista de sugerencias -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div v-if="loading" class="flex justify-center items-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>

            <div v-else-if="error" class="text-red-600 text-center py-4">
              {{ error }}
            </div>

            <div v-else-if="suggestions.length > 0" class="space-y-4">
              <div
                v-for="suggestion in suggestions"
                :key="suggestion.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start mb-3">
                  <div class="flex-1">
                    <h4 class="text-lg font-semibold text-gray-900">{{ suggestion.title }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ suggestion.description }}</p>
                  </div>
                  <span
                    :class="{
                      'bg-yellow-100 text-yellow-800': suggestion.status === 'pending',
                      'bg-blue-100 text-blue-800': suggestion.status === 'reviewed',
                      'bg-green-100 text-green-800': suggestion.status === 'implemented',
                      'bg-red-100 text-red-800': suggestion.status === 'rejected',
                      'bg-orange-100 text-orange-800': suggestion.status === 'in_progress'
                    }"
                    class="px-3 py-1 text-xs font-medium rounded-full ml-4"
                  >
                    {{ suggestion.status_label }}
                  </span>
                </div>

                <!-- Información del proyecto y entidad relacionada -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 text-sm">
                  <div>
                    <span class="font-medium text-gray-700">Proyecto:</span>
                    <span class="text-gray-600 ml-1">{{ suggestion.project.name }}</span>
                  </div>
                  <div v-if="suggestion.task">
                    <span class="font-medium text-gray-700">Tarea:</span>
                    <span class="text-gray-600 ml-1">{{ suggestion.task.title }}</span>
                  </div>
                  <div v-if="suggestion.sprint">
                    <span class="font-medium text-gray-700">Sprint:</span>
                    <span class="text-gray-600 ml-1">{{ suggestion.sprint.name }}</span>
                  </div>
                </div>

                <!-- Respuesta del admin -->
                <div v-if="suggestion.admin_response" class="bg-gray-50 p-3 rounded-lg mb-3">
                  <p class="text-sm font-medium text-gray-700 mb-1">Respuesta del Administrador:</p>
                  <p class="text-sm text-gray-600">{{ suggestion.admin_response }}</p>
                  <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                    <span v-if="suggestion.responded_by">Por: {{ suggestion.responded_by }}</span>
                    <span v-if="suggestion.responded_at">{{ formatDate(suggestion.responded_at) }}</span>
                  </div>
                </div>

                <!-- Fecha de creación -->
                <div class="text-xs text-gray-500">
                  Creada el {{ formatDate(suggestion.created_at) }}
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="mt-2">No tienes sugerencias aún</p>
              <button
                @click="showCreateModal = true"
                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
              >
                Crear tu primera sugerencia
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para crear sugerencia -->
    <Dialog :open="showCreateModal" @update:open="showCreateModal = $event">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>
            Nueva Sugerencia
          </DialogTitle>
        </DialogHeader>

            <form @submit.prevent="createSuggestion" class="space-y-4">
              <!-- Proyecto -->
              <div>
                                 <label class="block text-sm font-medium text-white mb-1">
                   Proyecto *
                 </label>
                                 <select
                   v-model="newSuggestion.project_id"
                   @change="loadProjectData"
                   required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                 >
                  <option value="">Seleccionar proyecto</option>
                  <option
                    v-for="project in availableProjects"
                    :key="project.id"
                    :value="project.id"
                  >
                    {{ project.name }}
                  </option>
                </select>
              </div>

              <!-- Tarea (opcional) -->
              <div>
                                 <label class="block text-sm font-medium text-white mb-1">
                   Tarea (opcional)
                 </label>
                                 <select
                   v-model="newSuggestion.task_id"
                   :disabled="!newSuggestion.project_id || loadingTasks"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 disabled:bg-gray-100 disabled:text-gray-500"
                 >
                  <option value="">Sin tarea específica</option>
                  <option
                    v-for="task in availableTasks"
                    :key="task.id"
                    :value="task.id"
                  >
                    {{ task.title }}
                  </option>
                </select>
                <div v-if="loadingTasks" class="text-sm text-gray-500 mt-1">
                  Cargando tareas...
                </div>
              </div>

              <!-- Sprint (opcional) -->
              <div>
                                 <label class="block text-sm font-medium text-white mb-1">
                   Sprint (opcional)
                 </label>
                                 <select
                   v-model="newSuggestion.sprint_id"
                   :disabled="!newSuggestion.project_id || loadingSprints"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 disabled:bg-gray-100 disabled:text-gray-500"
                 >
                  <option value="">Sin sprint específico</option>
                  <option
                    v-for="sprint in availableSprints"
                    :key="sprint.id"
                    :value="sprint.id"
                  >
                    {{ sprint.name }} ({{ formatDate(sprint.start_date) }} - {{ formatDate(sprint.end_date) }})
                  </option>
                </select>
                <div v-if="loadingSprints" class="text-sm text-gray-500 mt-1">
                  Cargando sprints...
                </div>
              </div>

              <!-- Título -->
              <div>
                                 <label class="block text-sm font-medium text-white mb-1">
                   Título *
                 </label>
                                 <input
                   v-model="newSuggestion.title"
                   type="text"
                   required
                   maxlength="255"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
                   placeholder="Título de la sugerencia"
                 />
              </div>

              <!-- Descripción -->
              <div>
                                 <label class="block text-sm font-medium text-white mb-1">
                   Descripción *
                 </label>
                                 <textarea
                   v-model="newSuggestion.description"
                   required
                   maxlength="1000"
                   rows="4"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 resize-none"
                   placeholder="Describe tu sugerencia en detalle..."
                 ></textarea>
                <div class="text-sm text-gray-500 mt-1">
                  {{ newSuggestion.description.length }}/1000 caracteres
                </div>
              </div>

              <!-- Botones -->
              <div class="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="showCreateModal = false"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="creating"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                >
                  <span v-if="creating">Creando...</span>
                  <span v-else>Crear Sugerencia</span>
                </button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'

const loading = ref(true)
const loadingStats = ref(true)
const error = ref(null)
const suggestions = ref([])
const statistics = ref(null)
const showCreateModal = ref(false)
const creating = ref(false)
const loadingTasks = ref(false)
const loadingSprints = ref(false)

const availableProjects = ref([])
const availableTasks = ref([])
const availableSprints = ref([])

const newSuggestion = ref({
  project_id: '',
  task_id: '',
  sprint_id: '',
  title: '',
  description: ''
})

const fetchSuggestions = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await fetch('/client/suggestions/api', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error('Error al cargar las sugerencias')
    }
    
    const data = await response.json()
    suggestions.value = data.data
  } catch (err) {
    error.value = err.message
    console.error('Error fetching suggestions:', err)
  } finally {
    loading.value = false
  }
}

const fetchStatistics = async () => {
  try {
    loadingStats.value = true
    
    const response = await fetch('/client/suggestions/statistics/overview', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error('Error al cargar las estadísticas')
    }
    
    const data = await response.json()
    statistics.value = data.data
  } catch (err) {
    console.error('Error fetching statistics:', err)
  } finally {
    loadingStats.value = false
  }
}

const fetchAvailableProjects = async () => {
  try {
    const response = await fetch('/client/suggestions/projects/available', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error('Error al cargar los proyectos')
    }
    
    const data = await response.json()
    availableProjects.value = data.data
  } catch (err) {
    console.error('Error fetching projects:', err)
  }
}

const loadProjectData = async () => {
  if (!newSuggestion.value.project_id) {
    availableTasks.value = []
    availableSprints.value = []
    return
  }

  // Cargar tareas
  loadingTasks.value = true
  try {
    const response = await fetch(`/client/suggestions/projects/${newSuggestion.value.project_id}/tasks`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      availableTasks.value = data.data
    }
  } catch (err) {
    console.error('Error fetching tasks:', err)
  } finally {
    loadingTasks.value = false
  }

  // Cargar sprints
  loadingSprints.value = true
  try {
    const response = await fetch(`/client/suggestions/projects/${newSuggestion.value.project_id}/sprints`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      availableSprints.value = data.data
    }
  } catch (err) {
    console.error('Error fetching sprints:', err)
  } finally {
    loadingSprints.value = false
  }
}

const createSuggestion = async () => {
  try {
    creating.value = true
    
    const response = await fetch('/client/suggestions/api', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(newSuggestion.value)
    })
    
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Error al crear la sugerencia')
    }
    
    const data = await response.json()
    
    // Agregar la nueva sugerencia a la lista
    suggestions.value.unshift(data.data)
    
    // Actualizar estadísticas
    await fetchStatistics()
    
    // Limpiar formulario y cerrar modal
    newSuggestion.value = {
      project_id: '',
      task_id: '',
      sprint_id: '',
      title: '',
      description: ''
    }
    showCreateModal.value = false
    
    // Mostrar mensaje de éxito
    alert('Sugerencia creada exitosamente')
  } catch (err) {
    alert(err.message)
    console.error('Error creating suggestion:', err)
  } finally {
    creating.value = false
  }
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
  fetchSuggestions()
  fetchStatistics()
  fetchAvailableProjects()
})
</script>
