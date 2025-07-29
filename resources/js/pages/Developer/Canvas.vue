<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import Badge from '@/components/ui/badge/Badge.vue'
import Icon from '@/components/Icon.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

interface Task {
  id: string
  name: string
  description: string
  status: string
  priority: string
  category: string
  story_points: number
  estimated_hours?: number
  actual_hours?: number
  rejection_reason?: string
  rejected_by?: any
  rejected_at?: string
  approved_by?: any
  approved_at?: string
  project?: any
  sprint?: any
  user?: any
}

interface Props {
  taskColumns: {
    'to do': Task[]
    'in progress': Task[]
    'ready for test': Task[]
    'in review': Task[]
    'rejected': Task[]
    'done': Task[]
  }
  projects: any[]
  stats: {
    total_tasks: number
    completed_tasks: number
    in_progress_tasks: number
    ready_for_test_tasks: number
    rejected_tasks: number
    total_projects: number
    total_hours_worked: number
    total_earnings: number
  }
  user: any
}

defineProps<Props>()


// Estado para drag and drop
const draggedTask = ref<Task | null>(null)
const draggedOverColumn = ref<string | null>(null)

// Columnas del canvas
const columns = [
  { id: 'to do', title: 'To Do', color: 'bg-gray-100', icon: 'circle' },
  { id: 'in progress', title: 'In Progress', color: 'bg-blue-100', icon: 'play' },
  { id: 'ready for test', title: 'Ready for Test', color: 'bg-yellow-100', icon: 'check-square' },
  { id: 'in review', title: 'In Review', color: 'bg-purple-100', icon: 'eye' },
  { id: 'rejected', title: 'Rejected', color: 'bg-red-100', icon: 'x-circle' },
  { id: 'done', title: 'Done', color: 'bg-green-100', icon: 'check-circle' },
]

// Funciones de drag and drop
const onDragStart = (event: DragEvent, task: Task) => {
  draggedTask.value = task
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }
}

const onDragOver = (event: DragEvent, columnId: string) => {
  event.preventDefault()
  draggedOverColumn.value = columnId
}

const onDragLeave = () => {
  draggedOverColumn.value = null
}

const onDrop = async (event: DragEvent, columnId: string) => {
  event.preventDefault()
  
  if (draggedTask.value && draggedTask.value.status !== columnId) {
    try {
      await router.put(`/tasks/${draggedTask.value.id}/status`, {
        status: columnId
      }, {
        preserveState: true,
        preserveScroll: true
      })
    } catch (error) {
      console.error('Error updating task status:', error)
    }
  }
  
  draggedTask.value = null
  draggedOverColumn.value = null
}

// Funciones helper
const getPriorityClass = (priority: string) => {
  switch (priority) {
    case 'high':
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    case 'medium':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    case 'low':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getPriorityIcon = (priority: string) => {
  switch (priority) {
    case 'high':
      return 'alert-triangle'
    case 'medium':
      return 'minus'
    case 'low':
      return 'chevron-down'
    default:
      return 'circle'
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <Head title="Developer Canvas" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Developer Canvas</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Manage your tasks with drag and drop
          </p>
        </div>
        <div class="flex items-center space-x-4">
          <div class="text-right">
            <p class="text-sm text-gray-600 dark:text-gray-400">Total Earnings</p>
            <p class="text-lg font-semibold text-green-600">{{ formatCurrency(stats.total_earnings) }}</p>
          </div>
          <Avatar class="h-10 w-10">
            <AvatarImage :src="user.avatar || ''" :alt="user.name" />
            <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
          </Avatar>
        </div>
      </div>
    </template>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
          <Icon name="list" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.total_tasks }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completed</CardTitle>
          <Icon name="check-circle" class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ stats.completed_tasks }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">In Progress</CardTitle>
          <Icon name="play" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ stats.in_progress_tasks }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Ready for Test</CardTitle>
          <Icon name="check-square" class="h-4 w-4 text-yellow-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-yellow-600">{{ stats.ready_for_test_tasks }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Rejected</CardTitle>
          <Icon name="x-circle" class="h-4 w-4 text-red-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-red-600">{{ stats.rejected_tasks }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Kanban Board -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
      <div
        v-for="column in columns"
        :key="column.id"
        class="space-y-4"
        @dragover="onDragOver($event, column.id)"
        @dragleave="onDragLeave"
        @drop="onDrop($event, column.id)"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <div :class="['w-3 h-3 rounded-full', column.color]"></div>
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ column.title }}</h3>
            <Badge variant="secondary" class="text-xs">
              {{ taskColumns[column.id as keyof typeof taskColumns]?.length || 0 }}
            </Badge>
          </div>
          <Icon :name="column.icon" class="h-4 w-4 text-gray-400" />
        </div>

        <!-- Column Content -->
        <div
          :class="[
            'min-h-[500px] p-4 rounded-lg border-2 border-dashed transition-colors',
            draggedOverColumn === column.id ? 'border-blue-400 bg-blue-50' : 'border-gray-200'
          ]"
        >
          <div class="space-y-3">
            <div
              v-for="task in taskColumns[column.id as keyof typeof taskColumns]"
              :key="task.id"
              draggable="true"
              @dragstart="onDragStart($event, task)"
              class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700 cursor-move hover:shadow-md transition-shadow"
            >
              <!-- Task Header -->
              <div class="flex items-start justify-between mb-2">
                <h4 class="font-medium text-sm text-gray-900 dark:text-white line-clamp-2">
                  {{ task.name }}
                </h4>
                <Badge :class="getPriorityClass(task.priority)" class="text-xs">
                  <Icon :name="getPriorityIcon(task.priority)" class="h-3 w-3 mr-1" />
                  {{ task.priority }}
                </Badge>
              </div>

              <!-- Task Description -->
              <p class="text-xs text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                {{ task.description }}
              </p>

              <!-- Rejection Info (only for rejected tasks) -->
              <div v-if="task.status === 'rejected' && task.rejection_reason" class="mb-3 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded">
                <div class="flex items-start space-x-2">
                  <Icon name="alert-triangle" class="h-4 w-4 text-red-600 mt-0.5 flex-shrink-0" />
                  <div class="flex-1">
                    <p class="text-xs font-medium text-red-800 dark:text-red-400 mb-1">Rejection Reason:</p>
                    <p class="text-xs text-red-700 dark:text-red-300">{{ task.rejection_reason }}</p>
                    <div class="flex items-center justify-between mt-2 text-xs text-red-600 dark:text-red-400">
                      <span v-if="task.rejected_by">By: {{ task.rejected_by.name }}</span>
                      <span v-if="task.rejected_at">{{ formatDate(task.rejected_at) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Task Meta -->
              <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center space-x-2">
                  <span class="flex items-center">
                    <Icon name="clock" class="h-3 w-3 mr-1" />
                    {{ task.estimated_hours || 0 }}h
                  </span>
                  <span class="flex items-center">
                    <Icon name="target" class="h-3 w-3 mr-1" />
                    {{ task.story_points }}pts
                  </span>
                </div>
                <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                  {{ task.category }}
                </span>
              </div>

              <!-- Project Info -->
              <div v-if="task.project" class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ task.project.name }}
                  </span>
                  <Avatar class="h-6 w-6">
                    <AvatarImage :src="task.user?.avatar || ''" :alt="task.user?.name" />
                    <AvatarFallback class="text-xs">
                      {{ task.user ? getInitials(task.user.name) : '?' }}
                    </AvatarFallback>
                  </Avatar>
                </div>
              </div>

              <!-- Action for rejected tasks -->
              <div v-if="task.status === 'rejected'" class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <Button 
                  size="sm" 
                  variant="outline" 
                  class="w-full text-xs"
                  @click="onDrop({ preventDefault: () => {} } as any, 'in progress')"
                >
                  <Icon name="refresh-cw" class="h-3 w-3 mr-1" />
                  Fix and Move to In Progress
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="stats.total_tasks === 0" class="text-center py-12">
      <Icon name="kanban" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No tasks assigned</h3>
      <p class="text-gray-600 dark:text-gray-400">
        You don't have any tasks assigned yet. Contact your team leader to get started.
      </p>
    </div>
  </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 