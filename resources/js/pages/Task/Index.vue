<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import Icon from '@/components/Icon.vue'
import TaskCard from '@/components/TaskCard.vue'
import CreateTaskModal from '@/components/CreateTaskModal.vue'
import { usePermissions } from '@/composables/usePermissions'

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
    },
    is_working?: boolean,
    work_started_at?: string | null,
    total_time_seconds?: number
}

const props = defineProps<{
    tasks: Task[],
    permissions: string,
    projects: any[],
    sprints: any[],
    developers: any[]
}>()

const { hasPermission } = usePermissions();

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
  // Usar los desarrolladores del backend que ya incluyen roles
  return props.developers || [];
}

// Tracking de tiempo - manejo de eventos
const handleStartWork = async (taskId: string) => {
  try {
    const response = await fetch(`/tasks/${taskId}/start-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      // Recargar la página para actualizar el estado
      window.location.reload();
    } else {
      const error = await response.json();
      alert(error.message || 'Error al iniciar trabajo');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al iniciar trabajo');
  }
};

const handlePauseWork = async (taskId: string) => {
  try {
    const response = await fetch(`/tasks/${taskId}/pause-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      window.location.reload();
    } else {
      const error = await response.json();
      alert(error.message || 'Error al pausar trabajo');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al pausar trabajo');
  }
};

const handleResumeWork = async (taskId: string) => {
  try {
    const response = await fetch(`/tasks/${taskId}/resume-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      window.location.reload();
    } else {
      const error = await response.json();
      alert(error.message || 'Error al reanudar trabajo');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al reanudar trabajo');
  }
};

const handleFinishWork = async (taskId: string) => {
  try {
    const response = await fetch(`/tasks/${taskId}/finish-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      window.location.reload();
    } else {
      const error = await response.json();
      alert(error.message || 'Error al finalizar trabajo');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al finalizar trabajo');
  }
};

const handleSelfAssign = async (taskId: string) => {
  try {
    const response = await fetch(`/tasks/${taskId}/self-assign`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      window.location.reload();
    } else {
      const error = await response.json();
      alert(error.message || 'Error al auto-asignar tarea');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al auto-asignar tarea');
  }
};

// Verificar si una tarea está siendo trabajada actualmente
const isTaskWorking = (task: Task) => {
  return task.is_working === true;
};
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
          v-if="hasPermission('tasks.create') && projects.length > 0 && sprints.length > 0" 
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
        <TaskCard
          v-for="task in getToDoTasks()"
          :key="task.id"
          :task="task"
          :is-working="isTaskWorking(task)"
          :show-approval-status="false"
          @start-work="handleStartWork"
          @pause-work="handlePauseWork"
          @resume-work="handleResumeWork"
          @finish-work="handleFinishWork"
          @self-assign="handleSelfAssign"
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
        <TaskCard
          v-for="task in getInProgressTasks()"
          :key="task.id"
          :task="task"
          :is-working="isTaskWorking(task)"
          :show-approval-status="false"
          @start-work="handleStartWork"
          @pause-work="handlePauseWork"
          @resume-work="handleResumeWork"
          @finish-work="handleFinishWork"
          @self-assign="handleSelfAssign"
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
        <TaskCard
          v-for="task in getDoneTasks()"
          :key="task.id"
          :task="task"
          :is-working="isTaskWorking(task)"
          :show-approval-status="false"
          @start-work="handleStartWork"
          @pause-work="handlePauseWork"
          @resume-work="handleResumeWork"
          @finish-work="handleFinishWork"
          @self-assign="handleSelfAssign"
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
        v-if="hasPermission('tasks.create') && projects.length > 0 && sprints.length > 0" 
        :project="getDefaultProject()"
        :sprint="getDefaultSprint()"
        :developers="getDefaultDevelopers()"
      />
    </div>
  </AppLayout>
</template> 