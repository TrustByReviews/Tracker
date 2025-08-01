<template>
  <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
    <!-- Task Header -->
    <div class="p-4 border-b border-gray-100">
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <h4 class="text-sm font-medium text-gray-900 truncate">{{ task.name }}</h4>
          <p class="text-xs text-gray-500 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
        </div>
        <div class="flex items-center space-x-2 ml-2">
          <!-- Priority Badge -->
          <span
            :class="{
              'bg-red-100 text-red-800': task.priority === 'high',
              'bg-yellow-100 text-yellow-800': task.priority === 'medium',
              'bg-green-100 text-green-800': task.priority === 'low'
            }"
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
          >
            {{ getPriorityLabel(task.priority) }}
          </span>
          
          <!-- Approval Status Badge (if applicable) -->
          <span
            v-if="showApprovalStatus && task.approval_status"
            :class="{
              'bg-yellow-100 text-yellow-800': task.approval_status === 'pending',
              'bg-green-100 text-green-800': task.approval_status === 'approved',
              'bg-red-100 text-red-800': task.approval_status === 'rejected'
            }"
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
          >
            {{ getApprovalStatusLabel(task.approval_status) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Task Content -->
    <div class="p-4">
      <!-- Description -->
      <p v-if="task.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
        {{ task.description }}
      </p>

      <!-- Time Information -->
      <div class="space-y-2 mb-4">
        <!-- Estimated Time -->
        <div v-if="task.estimated_hours" class="flex items-center text-xs text-gray-500">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Estimado: {{ task.estimated_hours }}h</span>
        </div>

        <!-- Total Time -->
        <div class="flex items-center text-xs text-gray-500">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Total: {{ formatTime(task.total_time_seconds || 0) }}</span>
        </div>

        <!-- Current Session Time (if working) -->
        <div v-if="isWorking" class="flex items-center text-xs text-blue-600 font-medium">
          <svg class="w-4 h-4 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Sesión actual: {{ currentSessionTime }}</span>
        </div>
      </div>

      <!-- Assignment Info -->
      <div v-if="task.assigned_by" class="text-xs text-gray-500 mb-4">
        <span>Asignado por: {{ task.assigned_by_user?.name || 'N/A' }}</span>
        <span v-if="task.assigned_at" class="ml-2">
          el {{ formatDate(task.assigned_at) }}
        </span>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-2">
        <!-- Self Assign Button (for unassigned tasks) -->
        <button
          v-if="!task.user_id && task.status === 'to do'"
          @click="$emit('self-assign', task.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          Auto-asignar
        </button>

        <!-- Start Work Button -->
        <button
          v-if="task.user_id && task.status === 'to do' && !isWorking"
          @click="$emit('start-work', task.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Iniciar
        </button>

        <!-- Pause Work Button -->
        <button
          v-if="isWorking"
          @click="$emit('pause-work', task.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Pausar
        </button>

        <!-- Resume Work Button -->
        <button
          v-if="task.status === 'in progress' && !isWorking && task.total_time_seconds > 0"
          @click="$emit('resume-work', task.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Reanudar
        </button>

        <!-- Finish Work Button -->
        <button
          v-if="isWorking"
          @click="$emit('finish-work', task.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          Finalizar
        </button>

        <!-- View Details Button -->
        <button
          @click="viewTaskDetails"
          class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
          Ver
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'

// Props
const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  isWorking: {
    type: Boolean,
    default: false
  },
  showApprovalStatus: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits([
  'start-work',
  'pause-work',
  'resume-work',
  'finish-work',
  'self-assign'
])

// Reactive data
const currentTime = ref(Date.now())
let timer = null

// Computed properties
const currentSessionTime = computed(() => {
  if (!props.isWorking || !props.task.work_started_at) {
    return '00:00:00'
  }
  
  const startTime = new Date(props.task.work_started_at).getTime()
  const elapsed = Math.floor((currentTime.value - startTime) / 1000)
  
  return formatTime(elapsed)
})

// Methods
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

const getPriorityLabel = (priority) => {
  const labels = {
    high: 'Alta',
    medium: 'Media',
    low: 'Baja'
  }
  return labels[priority] || priority
}

const getApprovalStatusLabel = (status) => {
  const labels = {
    pending: 'Pendiente',
    approved: 'Aprobada',
    rejected: 'Rechazada'
  }
  return labels[status] || status
}

const viewTaskDetails = () => {
  router.visit(`/tasks/${props.task.id}`)
}

// Lifecycle
onMounted(() => {
  if (props.isWorking) {
    timer = setInterval(() => {
      currentTime.value = Date.now()
    }, 1000)
  }
})

onUnmounted(() => {
  if (timer) {
    clearInterval(timer)
  }
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