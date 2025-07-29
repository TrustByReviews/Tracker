<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import Badge from '@/components/ui/badge/Badge.vue'
import Icon from '@/components/Icon.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import RejectTaskModal from '@/components/RejectTaskModal.vue'

interface Props {
  projects: any[]
  tasksForReview: any[]
  rejectedTasks: any[]
  teamStats: {
    total_projects: number
    active_projects: number
    completed_projects: number
    total_tasks: number
    completed_tasks: number
    tasks_for_review: number
    rejected_tasks: number
    team_members: number
  }
  teamMembers: any[]
  user: any
}

defineProps<Props>()

// Estado para el modal de rechazo
const rejectModalOpen = ref(false)
const selectedTask = ref<any>(null)

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    case 'completed':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
    case 'paused':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

const getProjectProgress = (project: any) => {
  const totalTasks = project.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.length || 0)
  }, 0) || 0

  const completedTasks = project.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'done').length || 0)
  }, 0) || 0

  return totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
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

const approveTask = async (taskId: string) => {
  try {
    await router.post(`/team-leader/tasks/${taskId}/approve`, {}, {
      preserveState: true,
      preserveScroll: true
    })
  } catch (error) {
    console.error('Error approving task:', error)
  }
}

const openRejectModal = (task: any) => {
  selectedTask.value = task
  rejectModalOpen.value = true
}

const closeRejectModal = () => {
  rejectModalOpen.value = false
  selectedTask.value = null
}
</script>

<template>
  <Head title="Team Leader Dashboard" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Team Leader Dashboard</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Manage your team and project progress
          </p>
        </div>
        <div class="flex items-center space-x-4">
          <div class="text-right">
            <p class="text-sm text-gray-600 dark:text-gray-400">Team Members</p>
            <p class="text-lg font-semibold">{{ teamStats.team_members }}</p>
          </div>
          <Avatar class="h-10 w-10">
            <AvatarImage :src="user.avatar || ''" :alt="user.name" />
            <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
          </Avatar>
        </div>
      </div>
    </template>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Projects</CardTitle>
          <Icon name="folder" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ teamStats.total_projects }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Tasks for Review</CardTitle>
          <Icon name="eye" class="h-4 w-4 text-yellow-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-yellow-600">{{ teamStats.tasks_for_review }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Rejected Tasks</CardTitle>
          <Icon name="x-circle" class="h-4 w-4 text-red-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-red-600">{{ teamStats.rejected_tasks }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completion Rate</CardTitle>
          <Icon name="trending-up" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">
            {{ teamStats.total_tasks > 0 ? Math.round((teamStats.completed_tasks / teamStats.total_tasks) * 100) : 0 }}%
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Tasks for Review Section -->
    <div v-if="tasksForReview.length > 0" class="mb-8">
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="eye" class="h-5 w-5 mr-2 text-yellow-600" />
            Tasks Pending Review
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="task in tasksForReview"
              :key="task.id"
              class="border rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <div class="flex items-start justify-between mb-2">
                <h4 class="font-medium text-sm text-gray-900 dark:text-white">
                  {{ task.name }}
                </h4>
                <Badge class="bg-yellow-100 text-yellow-800">
                  Ready for Test
                </Badge>
              </div>
              
              <p class="text-xs text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                {{ task.description }}
              </p>

              <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-3">
                <span>{{ task.project?.name }}</span>
                <span>{{ task.user?.name }}</span>
              </div>

              <div class="flex gap-2">
                <Button 
                  size="sm" 
                  variant="outline" 
                  class="flex-1"
                  @click="approveTask(task.id)"
                >
                  <Icon name="check" class="h-4 w-4 mr-1" />
                  Approve
                </Button>
                <Button 
                  size="sm" 
                  variant="outline" 
                  class="flex-1"
                  @click="openRejectModal(task)"
                >
                  <Icon name="x" class="h-4 w-4 mr-1" />
                  Reject
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Rejected Tasks Section -->
    <div v-if="rejectedTasks.length > 0" class="mb-8">
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="x-circle" class="h-5 w-5 mr-2 text-red-600" />
            Rejected Tasks
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="task in rejectedTasks"
              :key="task.id"
              class="border border-red-200 dark:border-red-800 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <div class="flex items-start justify-between mb-2">
                <h4 class="font-medium text-sm text-gray-900 dark:text-white">
                  {{ task.name }}
                </h4>
                <Badge class="bg-red-100 text-red-800">
                  Rejected
                </Badge>
              </div>
              
              <p class="text-xs text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                {{ task.description }}
              </p>

              <!-- Rejection Reason -->
              <div v-if="task.rejection_reason" class="mb-3 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded">
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

              <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-3">
                <span>{{ task.project?.name }}</span>
                <span>{{ task.user?.name }}</span>
              </div>

              <div class="flex gap-2">
                <Button 
                  size="sm" 
                  variant="outline" 
                  class="flex-1"
                  @click="approveTask(task.id)"
                >
                  <Icon name="check" class="h-4 w-4 mr-1" />
                  Approve Now
                </Button>
                <Button 
                  size="sm" 
                  variant="outline" 
                  class="flex-1"
                  @click="openRejectModal(task)"
                >
                  <Icon name="edit" class="h-4 w-4 mr-1" />
                  Update Reason
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Projects Section -->
    <div class="mb-8">
      <Card>
        <CardHeader>
          <CardTitle>Your Projects</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="project in projects"
              :key="project.id"
              class="border rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer"
              @click="router.get(`/team-leader/projects/${project.id}`)"
            >
              <div class="flex items-start justify-between mb-3">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                  {{ project.name }}
                </h3>
                <Badge :class="getStatusClass(project.status)">
                  {{ project.status }}
                </Badge>
              </div>

              <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                {{ project.description }}
              </p>

              <!-- Progress Bar -->
              <div class="mb-4">
                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                  <span>Progress</span>
                  <span>{{ getProjectProgress(project) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all"
                    :style="{ width: `${getProjectProgress(project)}%` }"
                  ></div>
                </div>
              </div>

              <!-- Project Stats -->
              <div class="grid grid-cols-2 gap-4 text-xs">
                <div class="text-center">
                  <div class="font-semibold text-gray-900 dark:text-white">
                    {{ project.sprints?.reduce((acc: number, sprint: any) => acc + (sprint.tasks?.length || 0), 0) || 0 }}
                  </div>
                  <div class="text-gray-500 dark:text-gray-400">Total Tasks</div>
                </div>
                <div class="text-center">
                  <div class="font-semibold text-green-600">
                    {{ project.sprints?.reduce((acc: number, sprint: any) => acc + (sprint.tasks?.filter((task: any) => task.status === 'done').length || 0), 0) || 0 }}
                  </div>
                  <div class="text-gray-500 dark:text-gray-400">Completed</div>
                </div>
              </div>

              <!-- Team Members -->
              <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    Team Members
                  </span>
                  <div class="flex -space-x-2">
                    <Avatar
                      v-for="member in project.users?.slice(0, 3)"
                      :key="member.id"
                      class="h-6 w-6 border-2 border-white dark:border-gray-800"
                    >
                      <AvatarImage :src="member.avatar || ''" :alt="member.name" />
                      <AvatarFallback class="text-xs">
                        {{ getInitials(member.name) }}
                      </AvatarFallback>
                    </Avatar>
                    <div
                      v-if="project.users?.length > 3"
                      class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs text-gray-600 dark:text-gray-400 border-2 border-white dark:border-gray-800"
                    >
                      +{{ project.users.length - 3 }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Team Members Section -->
    <div class="mb-8">
      <Card>
        <CardHeader>
          <CardTitle>Team Performance</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="member in teamMembers"
              :key="member.id"
              class="border rounded-lg p-4"
            >
              <div class="flex items-center space-x-3 mb-3">
                <Avatar class="h-10 w-10">
                  <AvatarImage :src="member.avatar || ''" :alt="member.name" />
                  <AvatarFallback>{{ getInitials(member.name) }}</AvatarFallback>
                </Avatar>
                <div>
                  <h4 class="font-medium text-gray-900 dark:text-white">
                    {{ member.name }}
                  </h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ member.roles?.[0]?.name || 'Developer' }}
                  </p>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Tasks Completed</span>
                  <span class="font-medium">{{ member.completed_tasks }}/{{ member.total_tasks }}</span>
                </div>
                
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Rejected Tasks</span>
                  <span class="font-medium text-red-600">{{ member.rejected_tasks }}</span>
                </div>
                
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Performance</span>
                  <span class="font-medium text-green-600">{{ member.performance }}%</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Hour Rate</span>
                  <span class="font-medium">{{ formatCurrency(member.hour_value) }}</span>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Reject Task Modal -->
    <RejectTaskModal
      :task="selectedTask"
      :is-open="rejectModalOpen"
      @update:is-open="closeRejectModal"
    />
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