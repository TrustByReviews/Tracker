<script setup lang="ts">
import { Project, Sprint, Task } from '@/types'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import ProjectCreateModal from '@/components/CreateProjectModal.vue'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import Badge from '@/components/ui/badge/Badge.vue'
import Icon from '@/components/Icon.vue'
// import { useToast } from '@/composables/useToast'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Projects',
    href: '/projects',
  },
]

const props = defineProps<{
  projects: Project[],
  permissions: string,
  developers: any[],
  stats?: {
    total: number,
    active: number,
    completed: number,
    paused: number
  }
}>()

// const { success } = useToast()

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    case 'completed':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
    case 'paused':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    case 'cancelled':
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'active':
      return 'play'
    case 'completed':
      return 'check'
    case 'paused':
      return 'pause'
    case 'cancelled':
      return 'x'
    default:
      return 'circle'
  }
}

const getProjectProgress = (project: Project) => {
  if (!project.sprints || project.sprints.length === 0) return 0
  
  const totalTasks = project.sprints.reduce((acc: number, sprint: Sprint) => {
    return acc + (sprint.tasks?.length || 0)
  }, 0)
  
  const completedTasks = project.sprints.reduce((acc: number, sprint: Sprint) => {
    return acc + (sprint.tasks?.filter((task: Task) => task.status === 'done').length || 0)
  }, 0)
  
  return totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0
}

const getProjectStats = (project: Project) => {
  if (!project.sprints || project.sprints.length === 0) {
    return { tasks: 0, completed: 0, inProgress: 0, pending: 0 }
  }
  
  let tasks = 0, completed = 0, inProgress = 0, pending = 0
  
  project.sprints.forEach(sprint => {
    if (sprint.tasks) {
      sprint.tasks.forEach(task => {
        tasks++
        switch (task.status) {
          case 'done':
            completed++
            break
          case 'in progress':
            inProgress++
            break
          case 'to do':
            pending++
            break
        }
      })
    }
  })
  
  return { tasks, completed, inProgress, pending }
}
</script>

<template>
  <Head title="Projects" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Projects</h1>
          <p class="text-muted-foreground">
            Manage and track your development projects
          </p>
        </div>
        
        <div class="flex items-center gap-2" v-if="props.permissions === 'admin'">
          <ProjectCreateModal :developers="props.developers" />
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4" v-if="props.stats">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Projects</CardTitle>
            <Icon name="folder" class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats.total }}</div>
          </CardContent>
        </Card>
        
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active</CardTitle>
            <Icon name="play" class="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">{{ props.stats.active }}</div>
          </CardContent>
        </Card>
        
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Completed</CardTitle>
            <Icon name="check" class="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">{{ props.stats.completed }}</div>
          </CardContent>
        </Card>
        
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Paused</CardTitle>
            <Icon name="pause" class="h-4 w-4 text-yellow-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">{{ props.stats.paused }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Projects Grid -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <template v-if="props.projects.length">
          <Card v-for="project in props.projects" :key="project.id" class="group hover:shadow-lg transition-shadow">
            <CardHeader>
              <div class="flex items-start justify-between">
                <div class="space-y-1">
                  <CardTitle class="text-lg">{{ project.name }}</CardTitle>
                  <CardDescription class="line-clamp-2">{{ project.description }}</CardDescription>
                </div>
                <Badge :class="getStatusClass(project.status)" class="flex items-center gap-1">
                  <Icon :name="getStatusIcon(project.status)" class="h-3 w-3" />
                  {{ project.status }}
                </Badge>
              </div>
            </CardHeader>
            
            <CardContent class="space-y-4">
              <!-- Progress Bar -->
              <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">Progress</span>
                  <span class="font-medium">{{ getProjectProgress(project) }}%</span>
                </div>
                <div class="w-full bg-secondary rounded-full h-2">
                  <div 
                    class="bg-primary h-2 rounded-full transition-all duration-300" 
                    :style="{ width: getProjectProgress(project) + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Project Stats -->
              <div class="grid grid-cols-3 gap-2 text-xs">
                <div class="text-center p-2 bg-secondary rounded">
                  <div class="font-semibold">{{ getProjectStats(project).tasks }}</div>
                  <div class="text-muted-foreground">Total</div>
                </div>
                <div class="text-center p-2 bg-green-100 dark:bg-green-900/20 rounded">
                  <div class="font-semibold text-green-700 dark:text-green-400">{{ getProjectStats(project).completed }}</div>
                  <div class="text-muted-foreground">Done</div>
                </div>
                <div class="text-center p-2 bg-blue-100 dark:bg-blue-900/20 rounded">
                  <div class="font-semibold text-blue-700 dark:text-blue-400">{{ getProjectStats(project).inProgress }}</div>
                  <div class="text-muted-foreground">In Progress</div>
                </div>
              </div>

              <!-- Team Members -->
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <div class="flex -space-x-2">
                    <div 
                      v-for="user in project.users?.slice(0, 3)" 
                      :key="user.id"
                      class="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-medium border-2 border-background"
                    >
                      {{ user.name.charAt(0) }}
                    </div>
                  </div>
                  <span v-if="project.users && project.users.length > 3" class="text-xs text-muted-foreground">
                    +{{ project.users.length - 3 }} more
                  </span>
                </div>
                
                <div class="flex items-center gap-2">
                  <Button 
                    variant="outline" 
                    size="sm"
                    @click="router.get(`/projects/${project.id}`)"
                  >
                    View
                  </Button>
                  <Button 
                    v-if="props.permissions === 'admin'"
                    variant="outline" 
                    size="sm"
                    @click="router.get(`/projects/${project.id}/edit`)"
                  >
                    Edit
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </template>
        
        <template v-else>
          <div class="col-span-full text-center py-12">
            <Icon name="folder" class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No projects</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Get started by creating a new project.
            </p>
            <div class="mt-6" v-if="props.permissions === 'admin'">
              <ProjectCreateModal :developers="props.developers" />
            </div>
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>
