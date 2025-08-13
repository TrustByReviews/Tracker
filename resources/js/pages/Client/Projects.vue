<template>
  <AppLayout title="Mis Proyectos">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Mis Proyectos
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div v-if="loading" class="flex justify-center items-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="error" class="text-red-600 text-center py-4">
          {{ error }}
        </div>

        <div v-else-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="project in projects"
            :key="project.id"
            class="bg-white overflow-hidden shadow-xl sm:rounded-lg hover:shadow-2xl transition-shadow duration-300"
          >
            <div class="p-6">
              <!-- Header del proyecto -->
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ project.name }}</h3>
                  <p class="text-gray-600 text-sm line-clamp-3">{{ project.description }}</p>
                </div>
                <span
                  :class="{
                    'bg-green-100 text-green-800': project.status === 'active',
                    'bg-yellow-100 text-yellow-800': project.status === 'paused',
                    'bg-red-100 text-red-800': project.status === 'completed'
                  }"
                  class="px-3 py-1 text-xs font-medium rounded-full ml-4 flex-shrink-0"
                >
                  {{ getStatusLabel(project.status) }}
                </span>
              </div>

              <!-- Progreso del proyecto -->
              <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                  <span>Progreso General</span>
                  <span>{{ project.progress_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                    :style="{ width: project.progress_percentage + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Sprint actual -->
              <div v-if="project.current_sprint" class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-medium text-blue-900 mb-2">Sprint Actual</h4>
                <p class="text-sm font-semibold text-blue-800 mb-1">{{ project.current_sprint.name }}</p>
                <p class="text-xs text-blue-600">
                  {{ formatDate(project.current_sprint.start_date) }} - {{ formatDate(project.current_sprint.end_date) }}
                </p>
                
                <!-- Progreso del sprint -->
                <div class="mt-3">
                  <div class="flex justify-between text-xs text-blue-600 mb-1">
                    <span>Progreso del Sprint</span>
                    <span>{{ project.current_sprint.progress_percentage }}%</span>
                  </div>
                  <div class="w-full bg-blue-200 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: project.current_sprint.progress_percentage + '%' }"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Estadísticas del proyecto -->
              <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                  <p class="text-2xl font-bold text-gray-900">{{ project.total_tasks }}</p>
                  <p class="text-xs text-gray-600">Total Tareas</p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                  <p class="text-2xl font-bold text-green-900">{{ project.completed_tasks }}</p>
                  <p class="text-xs text-green-600">Completadas</p>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                  <p class="text-2xl font-bold text-blue-900">{{ project.in_progress_tasks }}</p>
                  <p class="text-xs text-blue-600">En Progreso</p>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                  <p class="text-2xl font-bold text-yellow-900">{{ project.pending_tasks }}</p>
                  <p class="text-xs text-yellow-600">Pendientes</p>
                </div>
              </div>

              <!-- Equipo del proyecto -->
              <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Equipo del Proyecto</h4>
                <div class="space-y-2">
                  <div v-if="project.team && project.team.length > 0">
                    <div class="flex flex-wrap gap-1">
                      <span
                        v-for="member in project.team"
                        :key="member.id"
                        :class="{
                          'px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full': member.roles.includes('developer'),
                          'px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full': member.roles.includes('qa'),
                          'px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full': member.roles.includes('team_leader')
                        }"
                      >
                        {{ member.name }}
                      </span>
                    </div>
                  </div>
                  <div v-else class="text-xs text-gray-500">
                    No hay miembros del equipo asignados
                  </div>
                </div>
              </div>

              <!-- Tareas recientes -->
              <div v-if="project.recent_tasks && project.recent_tasks.length > 0" class="mb-6">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Tareas Recientes</h4>
                <div class="space-y-2">
                  <div
                    v-for="task in project.recent_tasks.slice(0, 3)"
                    :key="task.id"
                    class="flex justify-between items-center p-2 bg-gray-50 rounded text-xs"
                  >
                    <span class="text-gray-700 truncate flex-1">{{ task.name }}</span>
                    <span
                      :class="{
                        'bg-yellow-100 text-yellow-800': task.status === 'to do',
                        'bg-blue-100 text-blue-800': task.status === 'in progress',
                        'bg-green-100 text-green-800': task.status === 'done'
                      }"
                      class="px-2 py-1 rounded-full ml-2 flex-shrink-0"
                    >
                      {{ getTaskStatusLabel(task.status) }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Acciones -->
              <div class="flex space-x-2">
                <button
                  @click="viewProjectDetails(project.id)"
                  class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                >
                  Ver Detalles
                </button>
                <button
                  @click="createSuggestion(project.id)"
                  class="flex-1 px-4 py-2 bg-orange-600 text-white text-sm rounded-md hover:bg-orange-700 transition-colors"
                >
                  Sugerencia
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay proyectos asignados</h3>
          <p class="mt-1 text-sm text-gray-500">No tienes proyectos asignados actualmente.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const loading = ref(true)
const error = ref(null)
const projects = ref([])

const fetchProjects = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch('/client/projects/api', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken || ''
      },
      credentials: 'include'
    })
    
    console.log('Response status:', response.status)
    console.log('Response headers:', response.headers)
    
    if (!response.ok) {
      const errorText = await response.text()
      console.log('Error response text:', errorText)
      
      try {
        const errorData = JSON.parse(errorText)
        throw new Error(errorData.message || `Error ${response.status}: ${response.statusText}`)
      } catch (parseError) {
        throw new Error(`Error ${response.status}: ${response.statusText} - ${errorText}`)
      }
    }
    
    const data = await response.json()
    console.log('Response data:', data)
    projects.value = data.data || []
  } catch (err) {
    error.value = err.message
    console.error('Error fetching projects:', err)
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

const getTaskStatusLabel = (status) => {
  const labels = {
    'to do': 'Pendiente',
    'in progress': 'En Progreso',
    'done': 'Completada'
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

const viewProjectDetails = (projectId) => {
  router.visit(`/client/projects/${projectId}`)
}

const createSuggestion = (projectId) => {
  router.visit('/client/suggestions', {
    data: { project_id: projectId }
  })
}

onMounted(() => {
  fetchProjects()
})
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
