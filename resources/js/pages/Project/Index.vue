<script setup lang="ts">
import { Project, Sprint, Task } from '@/types'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import ProjectCreateModal from '@/components/CreateProjectModal.vue'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import Badge from '@/components/ui/badge/Badge.vue'
import Icon from '@/components/Icon.vue'
import { ref, computed } from 'vue'
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
    paused: number,
    cancelled: number
  },
  filters?: any
}>()

// Estado reactivo para filtros
const filters = ref({
    status: props.filters?.status || '',
    assigned_user_id: props.filters?.assigned_user_id || '',
    sort_by: props.filters?.sort_by || 'created_at',
    sort_order: props.filters?.sort_order || 'desc',
    search: props.filters?.search || ''
})

// const { success } = useToast()

// Funciones de filtrado
const applyFilters = () => {
    router.get('/projects', filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

const clearFilters = () => {
    filters.value = {
        status: '',
        assigned_user_id: '',
        sort_by: 'created_at',
        sort_order: 'desc',
        search: ''
    }
    applyFilters()
}

const hasActiveFilters = computed(() => {
    return filters.value.status || 
           filters.value.assigned_user_id || 
           filters.value.search ||
           filters.value.sort_by !== 'created_at' ||
           filters.value.sort_order !== 'desc'
})

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

// Funciones helper adicionales
const getProjectStatus = (status: string) => {
  switch (status) {
    case 'active':
      return { label: 'Active', color: 'bg-green-100 text-green-800' }
    case 'completed':
      return { label: 'Completed', color: 'bg-blue-100 text-blue-800' }
    case 'paused':
      return { label: 'Paused', color: 'bg-yellow-100 text-yellow-800' }
    case 'cancelled':
      return { label: 'Cancelled', color: 'bg-red-100 text-red-800' }
    case 'inactive':
      return { label: 'Inactive', color: 'bg-gray-100 text-gray-800' }
    default:
      return { label: status, color: 'bg-gray-100 text-gray-800' }
  }
}

const getBorderColor = (project: Project) => {
  switch (project.status) {
    case 'active':
      return 'border-green-500'
    case 'completed':
      return 'border-blue-500'
    case 'paused':
      return 'border-yellow-500'
    case 'cancelled':
      return 'border-red-500'
    default:
      return 'border-gray-300'
  }
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

      <!-- Filtros Avanzados -->
      <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Filtros Avanzados</h3>
          <Button 
            v-if="hasActiveFilters" 
            @click="clearFilters" 
            variant="outline" 
            size="sm"
          >
            <Icon name="x" class="h-4 w-4 mr-2" />
            Limpiar Filtros
          </Button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Filtro por Estado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
            <Select v-model="filters.status" @update:model-value="applyFilters">
              <SelectTrigger>
                <SelectValue placeholder="Todos los estados" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">Todos los estados</SelectItem>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="paused">Paused</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Filtro por Usuario Asignado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usuario Asignado</label>
            <Select v-model="filters.assigned_user_id" @update:model-value="applyFilters">
              <SelectTrigger>
                <SelectValue placeholder="Todos los usuarios" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">Todos los usuarios</SelectItem>
                <SelectItem 
                  v-for="developer in developers" 
                  :key="developer.id" 
                  :value="developer.id"
                >
                  {{ developer.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Búsqueda por Nombre -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar Proyecto</label>
            <Input 
              v-model="filters.search" 
              placeholder="Buscar por nombre..."
              @input="applyFilters"
            />
          </div>

          <!-- Ordenar por -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordenar por</label>
            <Select v-model="filters.sort_by" @update:model-value="applyFilters">
              <SelectTrigger>
                <SelectValue placeholder="Ordenar por" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="created_at">Fecha de creación</SelectItem>
                <SelectItem value="name">Nombre</SelectItem>
                <SelectItem value="status">Estado</SelectItem>
                <SelectItem value="completion_rate">Porcentaje completado</SelectItem>
                <SelectItem value="total_tasks">Total de tareas</SelectItem>
                <SelectItem value="completed_tasks">Tareas completadas</SelectItem>
                <SelectItem value="team_members">Miembros del equipo</SelectItem>
                <SelectItem value="sprints_count">Número de sprints</SelectItem>
                <SelectItem value="updated_at">Última actualización</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <!-- Orden -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Orden</label>
            <Select v-model="filters.sort_order" @update:model-value="applyFilters">
              <SelectTrigger>
                <SelectValue placeholder="Orden" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="asc">Ascendente</SelectItem>
                <SelectItem value="desc">Descendente</SelectItem>
              </SelectContent>
            </Select>
          </div>
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
        
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Cancelled</CardTitle>
            <Icon name="x" class="h-4 w-4 text-red-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-red-600">{{ props.stats.cancelled }}</div>
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
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
              {{ hasActiveFilters ? 'No projects found with current filters' : 'No projects' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ hasActiveFilters 
                ? 'Try adjusting your filters or clear them to see all projects.' 
                : 'Get started by creating a new project.' 
              }}
            </p>
            <div class="mt-6 flex justify-center gap-4">
              <Button 
                v-if="hasActiveFilters" 
                @click="clearFilters" 
                variant="outline"
              >
                <Icon name="x" class="h-4 w-4 mr-2" />
                Limpiar Filtros
              </Button>
              <ProjectCreateModal 
                v-if="props.permissions === 'admin'" 
                :developers="props.developers" 
              />
            </div>
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>
