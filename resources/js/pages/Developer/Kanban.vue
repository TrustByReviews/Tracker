<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mi Tablero Kanban</h1>
        <p class="mt-2 text-gray-600">Gestiona tus tareas y rastrea tu tiempo de trabajo</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Tareas Pendientes</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.pending }}</p>
            </div>
          </div>
        </div>

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
              <p class="text-sm font-medium text-gray-500">En Progreso</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.inProgress }}</p>
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
              <p class="text-sm font-medium text-gray-500">Completadas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.completed }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Tiempo Total</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.totalTime }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Kanban Board -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- To Do Column -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <span class="w-3 h-3 bg-gray-400 rounded-full mr-3"></span>
              Por Hacer
              <span class="ml-auto bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-sm font-medium">
                {{ columns.toDo.length }}
              </span>
            </h3>
          </div>
          <div class="p-4 min-h-[600px]">
            <div
              v-for="task in columns.toDo"
              :key="task.id"
              class="mb-4"
              draggable="true"
              @dragstart="onDragStart($event, task, 'toDo')"
            >
              <TaskCard
                :task="task"
                :is-working="false"
                @start-work="startWork"
                @self-assign="selfAssignTask"
              />
            </div>
            <div
              v-if="columns.toDo.length === 0"
              class="text-center py-8 text-gray-500"
            >
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <p class="mt-2">No hay tareas pendientes</p>
            </div>
          </div>
        </div>

        <!-- In Progress Column -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <span class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></span>
              En Progreso
              <span class="ml-auto bg-yellow-100 text-yellow-600 px-2 py-1 rounded-full text-sm font-medium">
                {{ columns.inProgress.length }}
              </span>
            </h3>
          </div>
          <div class="p-4 min-h-[600px]">
            <div
              v-for="task in columns.inProgress"
              :key="task.id"
              class="mb-4"
              draggable="true"
              @dragstart="onDragStart($event, task, 'inProgress')"
            >
              <TaskCard
                :task="task"
                :is-working="task.is_working"
                @pause-work="pauseWork"
                @resume-work="resumeWork"
                @finish-work="finishWork"
                @resume-auto-paused="resumeAutoPaused"
              />
            </div>
            <div
              v-if="columns.inProgress.length === 0"
              class="text-center py-8 text-gray-500"
            >
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="mt-2">No hay tareas en progreso</p>
            </div>
          </div>
        </div>

        <!-- Done Column -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
              Completadas
              <span class="ml-auto bg-green-100 text-green-600 px-2 py-1 rounded-full text-sm font-medium">
                {{ columns.done.length }}
              </span>
            </h3>
          </div>
          <div class="p-4 min-h-[600px]">
            <div
              v-for="task in columns.done"
              :key="task.id"
              class="mb-4"
              draggable="true"
              @dragstart="onDragStart($event, task, 'done')"
            >
              <TaskCard
                :task="task"
                :is-working="false"
                :show-approval-status="true"
              />
            </div>
            <div
              v-if="columns.done.length === 0"
              class="text-center py-8 text-gray-500"
            >
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <p class="mt-2">No hay tareas completadas</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Drop Zones -->
      <div
        v-for="(column, columnName) in columns"
        :key="columnName"
        class="drop-zone"
        @dragover.prevent
        @drop="onDrop($event, columnName)"
      ></div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6">
        <div class="flex items-center">
          <svg class="animate-spin h-5 w-5 text-blue-500 mr-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Cargando...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import TaskCard from '@/components/TaskCard.vue'

const toast = useToast()
const loading = ref(false)
const tasks = ref([])
const draggedTask = ref(null)
const draggedFromColumn = ref('')

// Computed properties for columns
const columns = computed(() => {
  return {
    toDo: tasks.value.filter(task => task.status === 'to do'),
    inProgress: tasks.value.filter(task => task.status === 'in progress'),
    done: tasks.value.filter(task => task.status === 'done')
  }
})

// Computed properties for stats
const stats = computed(() => {
  const totalTime = tasks.value.reduce((total, task) => {
    return total + (task.total_time_seconds || 0)
  }, 0)

  return {
    pending: columns.value.toDo.length,
    inProgress: columns.value.inProgress.length,
    completed: columns.value.done.length,
    totalTime: formatTime(totalTime)
  }
})

// Methods
const loadTasks = async () => {
  try {
    loading.value = true
    const response = await fetch('/tasks/my-tasks')
    const data = await response.json()
    
    if (data.success) {
      tasks.value = data.tasks
    } else {
      toast.error('Error al cargar las tareas')
    }
  } catch (error) {
    console.error('Error loading tasks:', error)
    toast.error('Error al cargar las tareas')
  } finally {
    loading.value = false
  }
}

const startWork = async (taskId) => {
  try {
    const response = await fetch(`/tasks/${taskId}/start-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadTasks()
      toast.success('Trabajo iniciado correctamente')
    } else {
      toast.error(data.message || 'Error al iniciar trabajo')
    }
  } catch (error) {
    console.error('Error starting work:', error)
    toast.error('Error al iniciar trabajo')
  }
}

const pauseWork = async (taskId) => {
  try {
    const response = await fetch(`/tasks/${taskId}/pause-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadTasks()
      toast.success('Trabajo pausado correctamente')
    } else {
      toast.error(data.message || 'Error al pausar trabajo')
    }
  } catch (error) {
    console.error('Error pausing work:', error)
    toast.error('Error al pausar trabajo')
  }
}

const resumeWork = async (taskId) => {
  try {
    const response = await fetch(`/tasks/${taskId}/resume-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadTasks()
      toast.success('Trabajo reanudado correctamente')
    } else {
      toast.error(data.message || 'Error al reanudar trabajo')
    }
  } catch (error) {
    console.error('Error resuming work:', error)
    toast.error('Error al reanudar trabajo')
  }
}

const finishWork = async (taskId) => {
  try {
    const response = await fetch(`/tasks/${taskId}/finish-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadTasks()
      toast.success('Tarea completada correctamente')
    } else {
      toast.error(data.message || 'Error al completar tarea')
    }
  } catch (error) {
    console.error('Error finishing work:', error)
    toast.error('Error al completar tarea')
  }
}

const selfAssignTask = async (taskId) => {
  try {
    const response = await fetch(`/tasks/${taskId}/self-assign`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadTasks()
      toast.success('Tarea auto-asignada correctamente')
    } else {
      toast.error(data.message || 'Error al auto-asignar tarea')
    }
  } catch (error) {
    console.error('Error self-assigning task:', error)
    toast.error('Error al auto-asignar tarea')
  }
}

const resumeAutoPaused = async (taskId) => {
  try {
    const response = await fetch(`/tasks/${taskId}/resume-auto-paused`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadTasks()
      toast.success('Tarea auto-pausada reanudada correctamente')
    } else {
      toast.error(data.message || 'Error al reanudar tarea auto-pausada')
    }
  } catch (error) {
    console.error('Error resuming auto-paused task:', error)
    toast.error('Error al reanudar tarea auto-pausada')
  }
}

// Drag and Drop methods
const onDragStart = (event, task, columnName) => {
  draggedTask.value = task
  draggedFromColumn.value = columnName
  event.dataTransfer.effectAllowed = 'move'
}

const onDrop = async (event, targetColumnName) => {
  event.preventDefault()
  
  if (!draggedTask.value) return
  
  const task = draggedTask.value
  const fromColumn = draggedFromColumn.value
  
  // Determine new status based on target column
  let newStatus = 'to do'
  if (targetColumnName === 'inProgress') newStatus = 'in progress'
  if (targetColumnName === 'done') newStatus = 'done'
  
  // Don't update if status hasn't changed
  if (task.status === newStatus) return
  
  try {
    const response = await fetch(`/tasks/${task.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        status: newStatus
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      await loadTasks()
      toast.success('Tarea movida correctamente')
    } else {
      toast.error(data.message || 'Error al mover tarea')
    }
  } catch (error) {
    console.error('Error moving task:', error)
    toast.error('Error al mover tarea')
  }
  
  draggedTask.value = null
  draggedFromColumn.value = ''
}

// Utility functions
const formatTime = (seconds) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  
  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

// Lifecycle
onMounted(() => {
  loadTasks()
})
</script>

<style scoped>
.drop-zone {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

.drop-zone[dragover] {
  background-color: rgba(59, 130, 246, 0.1);
  border: 2px dashed #3b82f6;
}
</style> 