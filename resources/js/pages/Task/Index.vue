<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import Icon from '@/components/Icon.vue'
import CardTask from '@/components/CardTask.vue'
import CreateTaskModal from '@/components/CreateTaskModal.vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Tasks',
    href: '/tasks',
  },
]

interface User {
    id: string;
    name: string;
    email: string;
}

interface Sprint {
    id: string;
    name: string;
    goal: string;
    start_date: string;
    end_date: string;
}

interface Task {
    id: string,
    name: string,
    description: string,
    estimated_start: string | null,
    estimated_finish: string | null,
    status: string,
    priority: string,
    category: string,
    story_points: number,
    estimated_hours: number,
    user_id: string | null,
    user?: User,
    sprint?: Sprint,
    project?: {
        id: string,
        name: string
    }
}

const props = defineProps<{
    tasks: Task[],
    permissions: string,
    projects: any[],
    sprints: any[]
}>()

// Obtener estadísticas generales de tareas
const getTaskStats = () => {
  const totalTasks = props.tasks.length;
  const toDoTasks = props.tasks.filter(task => task.status === 'to do').length;
  const inProgressTasks = props.tasks.filter(task => task.status === 'in progress').length;
  const doneTasks = props.tasks.filter(task => task.status === 'done').length;
  
  return {
    total: totalTasks,
    toDo: toDoTasks,
    inProgress: inProgressTasks,
    done: doneTasks
  };
}

// Filtrar tareas por estado
const getToDoTasks = () => {
  return props.tasks.filter(task => task.status === 'to do');
}

const getInProgressTasks = () => {
  return props.tasks.filter(task => task.status === 'in progress');
}

const getDoneTasks = () => {
  return props.tasks.filter(task => task.status === 'done');
}

// Obtener colores por prioridad
const getPriorityColor = (priority: string) => {
  switch (priority) {
    case 'high':
      return 'text-red-600';
    case 'medium':
      return 'text-orange-600';
    case 'low':
      return 'text-yellow-600';
    default:
      return 'text-gray-600';
  }
}

// Obtener el proyecto y sprint por defecto para el modal
const getDefaultProject = () => {
  return props.projects.length > 0 ? props.projects[0] : null;
}

const getDefaultSprint = () => {
  return props.sprints.length > 0 ? props.sprints[0] : null;
}

const getDefaultDevelopers = () => {
  // Obtener todos los usuarios únicos de las tareas
  const developers = new Map();
  props.tasks.forEach(task => {
    if (task.user && !developers.has(task.user.id)) {
      developers.set(task.user.id, task.user);
    }
  });
  return Array.from(developers.values());
}
</script>

<template>
  <Head title="Tasks" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tasks</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manage and track all project tasks</p>
        </div>
        <CreateTaskModal 
          v-if="permissions === 'admin' && projects.length > 0 && sprints.length > 0" 
          :project="getDefaultProject()"
          :sprint="getDefaultSprint()"
          :developers="getDefaultDevelopers()"
        />
      </div>
    </template>

    <!-- Task Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
          <Icon name="list" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ getTaskStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">To Do</CardTitle>
          <Icon name="circle" class="h-4 w-4 text-yellow-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-yellow-600">{{ getTaskStats().toDo }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">In Progress</CardTitle>
          <Icon name="play" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getTaskStats().inProgress }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Done</CardTitle>
          <Icon name="check" class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ getTaskStats().done }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- To Do Tasks -->
    <div v-if="getToDoTasks().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="circle" class="h-5 w-5 text-yellow-600" />
        To Do Tasks
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <CardTask
          v-for="task in getToDoTasks()"
          :key="task.id"
          :task="task"
          :permissions="permissions"
          :project_id="task.project?.id || ''"
          :sprint="task.sprint || { id: '', name: '', goal: '', start_date: '', end_date: '' }"
          :developers="getDefaultDevelopers()"
        />
      </div>
    </div>

    <!-- In Progress Tasks -->
    <div v-if="getInProgressTasks().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="play" class="h-5 w-5 text-blue-600" />
        In Progress Tasks
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <CardTask
          v-for="task in getInProgressTasks()"
          :key="task.id"
          :task="task"
          :permissions="permissions"
          :project_id="task.project?.id || ''"
          :sprint="task.sprint || { id: '', name: '', goal: '', start_date: '', end_date: '' }"
          :developers="getDefaultDevelopers()"
        />
      </div>
    </div>

    <!-- Done Tasks -->
    <div v-if="getDoneTasks().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="check" class="h-5 w-5 text-green-600" />
        Completed Tasks
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <CardTask
          v-for="task in getDoneTasks()"
          :key="task.id"
          :task="task"
          :permissions="permissions"
          :project_id="task.project?.id || ''"
          :sprint="task.sprint || { id: '', name: '', goal: '', start_date: '', end_date: '' }"
          :developers="getDefaultDevelopers()"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="tasks.length === 0" class="text-center py-12">
      <Icon name="list" class="h-16 w-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No tasks found</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        Get started by creating your first task for a project.
      </p>
      <CreateTaskModal 
        v-if="permissions === 'admin' && projects.length > 0 && sprints.length > 0" 
        :project="getDefaultProject()"
        :sprint="getDefaultSprint()"
        :developers="getDefaultDevelopers()"
      />
    </div>
  </AppLayout>
</template> 