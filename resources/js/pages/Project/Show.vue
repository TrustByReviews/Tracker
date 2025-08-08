<script setup lang="ts">
import { Project } from '@/types'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import Badge from '@/components/ui/badge/Badge.vue'
import Icon from '@/components/Icon.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import UpdateProjectModal from '@/components/UpdateProjectModal.vue'
import CreateSprintModal from '@/components/CreateSprintModal.vue'
import CardSprint from '@/components/CardSprint.vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Projects',
    href: '/projects',
  },
  {
    title: 'Project Details',
    href: '#',
  },
]

const props = defineProps<{
  project: Project,
  developers: any[],
  permissions: string,
  sprints: any[]
}>()



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

const getProjectProgress = () => {
  const totalTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.length || 0)
  }, 0) || 0

  const completedTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'done').length || 0)
  }, 0) || 0

  return totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getProjectStats = () => {
  const totalTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.length || 0)
  }, 0) || 0

  const completedTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'done').length || 0)
  }, 0) || 0

  const inProgressTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'in progress').length || 0)
  }, 0) || 0

  const rejectedTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'rejected').length || 0)
  }, 0) || 0

  return {
    total: totalTasks,
    completed: completedTasks,
    inProgress: inProgressTasks,
    rejected: rejectedTasks
  }
}

const back = () => {
  router.get('/projects')
}
</script>

<template>
  <Head :title="`${project.name} - Project Details`" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" size="sm" @click="back" class="flex items-center">
            <Icon name="arrow-left" class="h-4 w-4 mr-2" />
            Back
          </Button>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ project.name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Project Details</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <Badge :class="getStatusClass(project.status)">
            {{ project.status }}
          </Badge>
          <UpdateProjectModal
            v-if="permissions === 'admin'"
            :project="project"
            :developers="developers"
          />
        </div>
      </div>
    </template>

    <!-- Project Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
          <Icon name="list" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ getProjectStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completed</CardTitle>
          <Icon name="check-circle" class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ getProjectStats().completed }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">In Progress</CardTitle>
          <Icon name="play" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getProjectStats().inProgress }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Rejected</CardTitle>
          <Icon name="x-circle" class="h-4 w-4 text-red-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-red-600">{{ getProjectStats().rejected }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Project Progress -->
    <Card class="mb-8">
      <CardHeader>
        <CardTitle>Project Progress</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Completion</span>
            <span class="font-medium">{{ getProjectProgress() }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all"
              :style="{ width: `${getProjectProgress()}%` }"
            ></div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Project Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Description -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="file-text" class="h-5 w-5 mr-2" />
            Description
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-gray-700 dark:text-gray-300">{{ project.description }}</p>
        </CardContent>
      </Card>

      <!-- Team Members -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="users" class="h-5 w-5 mr-2" />
            Team Members
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <template v-if="developers.length">
              <div
                v-for="developer in developers"
                :key="developer.id"
                class="flex items-center space-x-3"
              >
                <Avatar class="h-8 w-8">
                  <AvatarImage :src="developer.avatar || ''" :alt="developer.name" />
                  <AvatarFallback class="text-xs">
                    {{ getInitials(developer.name) }}
                  </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ developer.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ developer.email }}
                  </p>
                </div>
                <Badge variant="secondary" class="text-xs">
                  {{ developer.roles?.[0]?.name || 'Developer' }}
                </Badge>
              </div>
            </template>
            <div v-else class="text-center py-4">
              <Icon name="users" class="h-8 w-8 text-gray-400 mx-auto mb-2" />
              <p class="text-sm text-gray-500 dark:text-gray-400">No team members assigned</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Project Info -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="info" class="h-5 w-5 mr-2" />
            Project Info
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
              <Badge :class="getStatusClass(project.status)">
                {{ project.status }}
              </Badge>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Sprints</span>
              <span class="text-sm font-medium">{{ sprints.length }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Created</span>
              <span class="text-sm font-medium">{{ new Date(project.created_at).toLocaleDateString() }}</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Sprints Section -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Sprints</h2>
        <CreateSprintModal 
          v-if="permissions === 'admin' || permissions === 'team_leader'" 
          :project="project" 
        />
      </div>

      <template v-if="sprints.length">
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
          <CardSprint
            v-for="sprint in sprints"
            :key="sprint.id"
            :sprint="sprint"
            :permissions="permissions"
            :project_id="project.id"
          />
        </div>
      </template>
      
      <template v-else>
        <Card>
          <CardContent class="text-center py-8">
            <Icon name="calendar" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No sprints created</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              Get started by creating your first sprint for this project.
            </p>
            <CreateSprintModal 
              v-if="permissions === 'admin' || permissions === 'team_leader'" 
              :project="project" 
            />
          </CardContent>
        </Card>
      </template>
    </div>
  </AppLayout>
</template>
  
