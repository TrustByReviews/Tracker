<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard de Team Leader</h1>
        <p class="mt-2 text-gray-600">Gestiona tu equipo y revisa las tareas completadas</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
              <p class="text-sm font-medium text-gray-500">Pendientes de Aprobación</p>
              <p class="text-2xl font-semibold text-gray-900">{{ approvalStats.pending }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Aprobadas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ approvalStats.approved }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Rechazadas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ approvalStats.rejected }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Desarrolladores</p>
              <p class="text-2xl font-semibold text-gray-900">{{ developersWithTasks.length }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Pending Tasks Column -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Tareas Pendientes de Aprobación</h2>
              <p class="mt-1 text-sm text-gray-600">Revisa y aprueba las tareas completadas por tu equipo</p>
            </div>
            <div class="p-6">
              <div v-if="pendingTasks.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-gray-500">No hay tareas pendientes de aprobación</p>
              </div>
              
              <div v-else class="space-y-4">
                <div
                  v-for="task in pendingTasks"
                  :key="task.id"
                  class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <h3 class="text-sm font-medium text-gray-900">{{ task.name }}</h3>
                      <p class="text-xs text-gray-500 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                      <p class="text-xs text-gray-500">Desarrollador: {{ task.user?.name }}</p>
                      <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                        <span>Tiempo total: {{ formatTime(task.total_time_seconds || 0) }}</span>
                        <span v-if="task.estimated_hours">Estimado: {{ task.estimated_hours }}h</span>
                        <span>Completada: {{ formatDate(task.actual_finish) }}</span>
                      </div>
                    </div>
                    <div class="flex space-x-2 ml-4">
                      <button
                        @click="approveTask(task.id)"
                        :disabled="loading"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                      >
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Aprobar
                      </button>
                      <button
                        @click="openRejectModal(task)"
                        :disabled="loading"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                      >
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Rechazar
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Overview Column -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Resumen del Equipo</h2>
              <p class="mt-1 text-sm text-gray-600">Actividad actual de los desarrolladores</p>
            </div>
            <div class="p-6">
              <div v-if="developersWithTasks.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <p class="mt-2 text-gray-500">No hay desarrolladores asignados</p>
              </div>
              
              <div v-else class="space-y-4">
                <div
                  v-for="developer in developersWithTasks"
                  :key="developer.id"
                  class="border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-900">{{ developer.name }}</h3>
                    <span class="text-xs text-gray-500">{{ developer.tasks.length }} tareas</span>
                  </div>
                  
                  <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                      <span class="text-gray-500">En progreso:</span>
                      <span class="font-medium">{{ getActiveTasksCount(developer) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                      <span class="text-gray-500">Tiempo total:</span>
                      <span class="font-medium">{{ formatTime(getTotalTime(developer)) }}</span>
                    </div>
                  </div>
                  
                  <div class="mt-3 pt-3 border-t border-gray-100">
                    <div
                      v-for="task in developer.tasks.slice(0, 2)"
                      :key="task.id"
                      class="text-xs text-gray-600 truncate"
                    >
                      {{ task.name }}
                    </div>
                    <div v-if="developer.tasks.length > 2" class="text-xs text-gray-500 mt-1">
                      +{{ developer.tasks.length - 2 }} más
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recently Completed Tasks -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Tareas Completadas Recientemente</h2>
            <p class="mt-1 text-sm text-gray-600">Últimas tareas aprobadas por tu equipo</p>
          </div>
          <div class="p-6">
            <div v-if="recentlyCompleted.length === 0" class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <p class="mt-2 text-gray-500">No hay tareas completadas recientemente</p>
            </div>
            
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarea</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desarrollador</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="task in recentlyCompleted" :key="task.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ task.name }}</div>
                      <div class="text-sm text-gray-500">{{ task.project?.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ task.user?.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatTime(task.total_time_seconds || 0) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="{
                          'bg-green-100 text-green-800': task.approval_status === 'approved',
                          'bg-red-100 text-red-800': task.approval_status === 'rejected',
                          'bg-yellow-100 text-yellow-800': task.approval_status === 'pending'
                        }"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      >
                        {{ getApprovalStatusLabel(task.approval_status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(task.actual_finish) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="isRejectModalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Rechazar Tarea</h3>
          <div class="mb-4">
            <label for="rejection-reason" class="block text-sm font-medium text-gray-700 mb-2">
              Motivo del rechazo
            </label>
            <textarea
              id="rejection-reason"
              v-model="rejectionReason"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Explica por qué se rechaza la tarea..."
            ></textarea>
          </div>
          <div class="flex justify-end space-x-3">
            <button
              @click="closeRejectModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Cancelar
            </button>
            <button
              @click="rejectTask"
              :disabled="!rejectionReason.trim() || loading"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
            >
              Rechazar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Component -->
    <Toast />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import Toast from '@/components/Toast.vue'

const toast = useToast()
const loading = ref(false)
const pendingTasks = ref([])
const developersWithTasks = ref([])
const approvalStats = ref({
  pending: 0,
  approved: 0,
  rejected: 0,
  total_reviewed: 0
})
const recentlyCompleted = ref([])
const isRejectModalOpen = ref(false)
const rejectionReason = ref('')
const selectedTaskId = ref(null)

// Methods
const loadDashboardData = async () => {
  try {
    loading.value = true
    
    // Load pending tasks
    const pendingResponse = await fetch('/team-leader/pending-tasks')
    const pendingData = await pendingResponse.json()
    if (pendingData.success) {
      pendingTasks.value = pendingData.tasks
    }
    
    // Load developers
    const developersResponse = await fetch('/team-leader/developers')
    const developersData = await developersResponse.json()
    if (developersData.success) {
      developersWithTasks.value = developersData.developers
    }
    
    // Load approval stats
    const statsResponse = await fetch('/team-leader/stats/approval')
    const statsData = await statsResponse.json()
    if (statsData.success) {
      approvalStats.value = statsData.stats
    }
    
    // Load recently completed
    const completedResponse = await fetch('/team-leader/stats/recently-completed')
    const completedData = await completedResponse.json()
    if (completedData.success) {
      recentlyCompleted.value = completedData.recentlyCompleted
    }
    
  } catch (error) {
    console.error('Error loading dashboard data:', error)
    toast.error('Error al cargar los datos del dashboard')
  } finally {
    loading.value = false
  }
}

const approveTask = async (taskId) => {
  try {
    loading.value = true
    const response = await fetch(`/team-leader/tasks/${taskId}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        notes: ''
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadDashboardData()
      toast.success('Tarea aprobada correctamente')
    } else {
      toast.error(data.message || 'Error al aprobar tarea')
    }
  } catch (error) {
    console.error('Error approving task:', error)
    toast.error('Error al aprobar tarea')
  } finally {
    loading.value = false
  }
}

const openRejectModal = (task) => {
  selectedTaskId.value = task.id
  rejectionReason.value = ''
  isRejectModalOpen.value = true
}

const closeRejectModal = () => {
  isRejectModalOpen.value = false
  selectedTaskId.value = null
  rejectionReason.value = ''
}

const rejectTask = async () => {
  if (!rejectionReason.value.trim()) {
    toast.error('Debes proporcionar un motivo para el rechazo')
    return
  }
  
  try {
    loading.value = true
    const response = await fetch(`/team-leader/tasks/${selectedTaskId.value}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        rejection_reason: rejectionReason.value
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadDashboardData()
      closeRejectModal()
      toast.success('Tarea rechazada correctamente')
    } else {
      toast.error(data.message || 'Error al rechazar tarea')
    }
  } catch (error) {
    console.error('Error rejecting task:', error)
    toast.error('Error al rechazar tarea')
  } finally {
    loading.value = false
  }
}

// Utility functions
const formatTime = (seconds) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  
  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getApprovalStatusLabel = (status) => {
  const labels = {
    pending: 'Pendiente',
    approved: 'Aprobada',
    rejected: 'Rechazada'
  }
  return labels[status] || status
}

const getActiveTasksCount = (developer) => {
  return developer.tasks.filter(task => task.status === 'in progress').length
}

const getTotalTime = (developer) => {
  return developer.tasks.reduce((total, task) => {
    return total + (task.total_time_seconds || 0)
  }, 0)
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script> 