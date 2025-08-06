<template>
  <div 
    :class="{
      'bg-white border-2 border-red-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-200': bug.status === 'new',
      'bg-white border-2 border-orange-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-200': bug.status === 'assigned',
      'bg-white border-2 border-blue-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-200': bug.status === 'in progress',
      'bg-white border-2 border-green-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-200': bug.status === 'resolved',
      'bg-white border-2 border-purple-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-200': bug.status === 'verified',
      'bg-white border-2 border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-200': bug.status === 'closed'
    }"
  >
    <!-- Bug Header -->
    <div class="p-4 border-b border-gray-100">
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <h4 class="text-sm font-medium text-gray-900 truncate">{{ bug.title }}</h4>
          <p class="text-xs text-gray-500 mt-1">{{ bug.project?.name }} - {{ bug.sprint?.name }}</p>
        </div>
        <div class="flex items-center space-x-2 ml-2">
          <!-- Status Badge -->
          <span
            :class="{
              'bg-red-100 text-red-800': bug.status === 'new',
              'bg-orange-100 text-orange-800': bug.status === 'assigned',
              'bg-blue-100 text-blue-800': bug.status === 'in progress',
              'bg-green-100 text-green-800': bug.status === 'resolved',
              'bg-purple-100 text-purple-800': bug.status === 'verified',
              'bg-gray-100 text-gray-800': bug.status === 'closed'
            }"
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
          >
            {{ getStatusLabel(bug.status) }}
          </span>
          
          <!-- Importance Badge -->
          <span
            :class="{
              'bg-red-100 text-red-800': bug.importance === 'critical',
              'bg-orange-100 text-orange-800': bug.importance === 'high',
              'bg-yellow-100 text-yellow-800': bug.importance === 'medium',
              'bg-green-100 text-green-800': bug.importance === 'low'
            }"
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
          >
            {{ getImportanceLabel(bug.importance) }}
          </span>
          
          <!-- Bug Type Badge -->
          <span
            :class="{
              'bg-blue-100 text-blue-800': bug.bug_type === 'frontend',
              'bg-purple-100 text-purple-800': bug.bug_type === 'backend',
              'bg-green-100 text-green-800': bug.bug_type === 'database',
              'bg-orange-100 text-orange-800': bug.bug_type === 'api',
              'bg-pink-100 text-pink-800': bug.bug_type === 'ui_ux',
              'bg-indigo-100 text-indigo-800': bug.bug_type === 'performance',
              'bg-red-100 text-red-800': bug.bug_type === 'security',
              'bg-gray-100 text-gray-800': bug.bug_type === 'other'
            }"
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
          >
            {{ getBugTypeLabel(bug.bug_type) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Bug Content -->
    <div class="p-4">
      <!-- Description -->
      <p v-if="bug.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
        {{ bug.description }}
      </p>

      <!-- Attachments -->
      <div v-if="bug.attachments && bug.attachments.length > 0" class="mb-4">
        <div class="flex items-center text-xs text-gray-500 mb-2">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
          </svg>
          Attachments ({{ bug.attachments.length }})
        </div>
        <div class="flex flex-wrap gap-2">
          <div
            v-for="(attachment, index) in bug.attachments"
            :key="index"
            class="flex items-center px-2 py-1 bg-gray-100 rounded text-xs text-gray-600 hover:bg-gray-200 cursor-pointer"
            @click="openAttachment(attachment)"
          >
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
            </svg>
            {{ getFileName(attachment) }}
          </div>
        </div>
      </div>

      <!-- Related Task -->
      <div v-if="bug.related_task" class="mb-4">
        <div class="flex items-center text-xs text-gray-500 mb-2">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Tarea Relacionada
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded p-2">
          <div class="text-sm font-medium text-blue-900">{{ bug.related_task.name }}</div>
          <div class="text-xs text-blue-700">{{ bug.related_task.status }}</div>
        </div>
      </div>

      <!-- Tags -->
      <div v-if="bug.tags" class="mb-4">
        <div class="flex flex-wrap gap-1">
          <span
            v-for="tag in bug.tags.split(',')"
            :key="tag"
            class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800"
          >
            {{ tag.trim() }}
          </span>
        </div>
      </div>

      <!-- Time Information -->
      <div class="space-y-2 mb-4">
        <!-- Estimated Time -->
        <div v-if="bug.estimated_hours" class="flex items-center text-xs text-gray-500">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Estimado: {{ bug.estimated_hours }}h</span>
        </div>

        <!-- Total Accumulated Time -->
        <div class="flex items-center text-xs text-gray-600">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Total acumulado: {{ totalAccumulatedTime }}</span>
        </div>

        <!-- Current Session Time (if working) -->
        <div v-if="isWorking" class="flex items-center text-xs text-blue-600 font-medium">
          <svg class="w-4 h-4 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Sesión actual: {{ elapsedTime }}</span>
        </div>

        <!-- Auto-Paused Status -->
        <div v-if="bug.auto_paused" class="flex items-center text-xs text-orange-600 font-medium">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          <span>Auto-pausado: {{ bug.auto_pause_reason }}</span>
        </div>
      </div>

      <!-- Assignment Info -->
      <div v-if="bug.assigned_by" class="text-xs text-gray-500 mb-4">
        <span>Asignado por: {{ bug.assigned_by_user?.name || 'N/A' }}</span>
        <span v-if="bug.assigned_at" class="ml-2">
          el {{ formatDate(bug.assigned_at) }}
        </span>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-2">
        <!-- Self Assign Button (for unassigned bugs) -->
        <button
          v-if="!bug.user_id && bug.status === 'new'"
          @click="$emit('self-assign', bug.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          Auto-asignar
        </button>

        <!-- Start Work Button -->
        <button
          v-if="bug.user_id && (bug.status === 'assigned' || (bug.status === 'in progress' && !isWorking && bug.total_time_seconds === 0)) && !isWorking"
          @click="$emit('start-work', bug.id)"
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
          @click="$emit('pause-work', bug.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Pausar
        </button>

        <!-- Resume Work Button -->
        <button
          v-if="bug.status === 'in progress' && !isWorking && bug.total_time_seconds > 0"
          @click="$emit('resume-work', bug.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Reanudar
        </button>

        <!-- Finish Work Button -->
        <button
          v-if="bug.status === 'in progress' && isWorking"
          @click="$emit('finish-work', bug.id)"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          Finalizar
        </button>

        <!-- View Details Button -->
        <button
          @click="$emit('view-details', bug.id)"
          class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
          Ver detalles
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  bug: {
    type: Object,
    required: true
  },
  showApprovalStatus: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'self-assign',
  'start-work',
  'pause-work',
  'resume-work',
  'finish-work',
  'view-details'
])

const currentTime = ref(Date.now())
let timer = null

// Computed properties
const isWorking = computed(() => props.bug.is_working)

const elapsedTime = computed(() => {
  if (!isWorking.value || !props.bug.work_started_at) {
    return '0h 0m 0s'
  }
  
  const startTime = new Date(props.bug.work_started_at).getTime()
  const elapsedSeconds = Math.max(0, Math.floor((currentTime.value - startTime) / 1000))
  const hours = Math.floor(elapsedSeconds / 3600)
  const minutes = Math.floor((elapsedSeconds % 3600) / 60)
  const seconds = elapsedSeconds % 60
  return `${hours}h ${minutes}m ${seconds}s`
})

const totalAccumulatedTime = computed(() => {
  const totalSeconds = props.bug.total_time_seconds || 0
  const hours = Math.floor(totalSeconds / 3600)
  const minutes = Math.floor((totalSeconds % 3600) / 60)
  const seconds = totalSeconds % 60
  return `${hours}h ${minutes}m ${seconds}s`
})

const getStatusLabel = (status) => {
  const labels = {
    'new': 'Nuevo',
    'assigned': 'Asignado',
    'in progress': 'En Progreso',
    'resolved': 'Resuelto',
    'verified': 'Verificado',
    'closed': 'Cerrado',
    'reopened': 'Reabierto'
  }
  return labels[status] || status
}

const getImportanceLabel = (importance) => {
  const labels = {
    'low': 'Baja',
    'medium': 'Media',
    'high': 'Alta',
    'critical': 'Crítica'
  }
  return labels[importance] || importance
}

const getBugTypeLabel = (bugType) => {
  const labels = {
    'frontend': 'Frontend',
    'backend': 'Backend',
    'database': 'Base de Datos',
    'api': 'API',
    'ui_ux': 'UI/UX',
    'performance': 'Rendimiento',
    'security': 'Seguridad',
    'other': 'Otro'
  }
  return labels[bugType] || bugType
}

const getFileName = (attachment) => {
  if (typeof attachment === 'string') {
    return attachment.split('/').pop()
  }
  return attachment.name || 'Archivo'
}

const openAttachment = (attachment) => {
  if (typeof attachment === 'string') {
    window.open(attachment, '_blank')
  } else if (attachment.url) {
    window.open(attachment.url, '_blank')
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES')
}

const startTimer = () => {
  if (!timer) {
    timer = setInterval(() => {
      // Forzar la actualización del computed elapsedTime
      currentTime.value = Date.now()
    }, 1000)
  }
}

const stopTimer = () => {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

// Watch for changes in isWorking to start/stop timer
watch(isWorking, (newValue) => {
  if (newValue) {
    startTimer()
  } else {
    stopTimer()
  }
}, { immediate: true })

onMounted(() => {
  // Iniciar timer si el bug ya está trabajando
  if (isWorking.value) {
    startTimer()
  }
})

onUnmounted(() => {
  stopTimer()
})
</script> 