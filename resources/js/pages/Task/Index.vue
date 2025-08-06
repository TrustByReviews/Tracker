<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import Icon from '@/components/Icon.vue'
import TaskCard from '@/components/TaskCard.vue'
import CreateTaskModal from '@/components/CreateTaskModal.vue'
import { usePermissions } from '@/composables/usePermissions'
import { ref, computed, onMounted } from 'vue'

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
    total_time_seconds?: number,
    auto_paused?: boolean,
    auto_paused_at?: string | null,
    auto_pause_reason?: string | null,
    alert_count?: number,
    last_alert_at?: string | null
}

const props = defineProps<{
    tasks: Task[],
    permissions: string,
    projects: any[],
    sprints: any[],
    developers: any[],
    filters?: any
}>()

// Estado reactivo para filtros
const filters = ref({
    project_id: props.filters?.project_id || '',
    sprint_id: props.filters?.sprint_id || '',
    status: props.filters?.status || '',
    priority: props.filters?.priority || '',
    assigned_user_id: props.filters?.assigned_user_id || '',
    sort_by: props.filters?.sort_by || 'recent',
    sort_order: props.filters?.sort_order || 'desc'
})

// Debug: Mostrar información de las props
console.log('Task/Index.vue - Props recibidas:', {
    tasksCount: props.tasks?.length || 0,
    permissions: props.permissions,
    projectsCount: props.projects?.length || 0,
    sprintsCount: props.sprints?.length || 0,
    developersCount: props.developers?.length || 0,
    tasks: props.tasks
})

// Funciones de filtrado
const applyFilters = () => {
    router.get('/tasks', filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

const clearFilters = () => {
    filters.value = {
        project_id: '',
        sprint_id: '',
        status: '',
        priority: '',
        assigned_user_id: '',
        sort_by: 'recent',
        sort_order: 'desc'
    }
    applyFilters()
}

// Computed properties para filtros
const filteredSprints = computed(() => {
    if (!filters.value.project_id) return props.sprints
    return props.sprints.filter(sprint => sprint.project_id === filters.value.project_id)
})

const hasActiveFilters = computed(() => {
    return filters.value.project_id || 
           filters.value.sprint_id || 
           filters.value.status || 
           filters.value.priority || 
           filters.value.assigned_user_id ||
           filters.value.sort_by !== 'recent' ||
           filters.value.sort_order !== 'desc'
})

const { hasPermission } = usePermissions();

// Obtener estadísticas generales de tareas
const getTaskStats = () => {
  const totalTasks = props.tasks.length;
  const toDoTasks = props.tasks.filter(task => task.status === 'to do').length;
  const inProgressTasks = props.tasks.filter(task => task.status === 'in progress').length;
  const doneTasks = props.tasks.filter(task => task.status === 'done').length;
  const activeTasks = toDoTasks + inProgressTasks;
  
  return {
    total: totalTasks,
    toDo: toDoTasks,
    inProgress: inProgressTasks,
    done: doneTasks,
    active: activeTasks
  };
}

// Filtrar tareas por estado
const getToDoTasks = () => {
  const tasks = props.tasks.filter(task => task.status === 'to do');
  console.log('getToDoTasks:', tasks.length, 'tareas');
  return tasks;
}

const getInProgressTasks = () => {
  const tasks = props.tasks.filter(task => task.status === 'in progress');
  console.log('getInProgressTasks:', tasks.length, 'tareas');
  return tasks;
}

const getActiveTasks = () => {
  const tasks = props.tasks.filter(task => task.status === 'to do' || task.status === 'in progress');
  console.log('getActiveTasks:', tasks.length, 'tareas');
  return tasks;
}

const getDoneTasks = () => {
  const tasks = props.tasks.filter(task => task.status === 'done');
  console.log('getDoneTasks:', tasks.length, 'tareas');
  return tasks;
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

// Funciones helper para filtros
const getTaskStatus = (status: string) => {
  switch (status) {
    case 'to do':
      return { label: 'To Do', color: 'bg-gray-100 text-gray-800' }
    case 'in progress':
      return { label: 'In Progress', color: 'bg-blue-100 text-blue-800' }
    case 'done':
      return { label: 'Done', color: 'bg-green-100 text-green-800' }
    default:
      return { label: status, color: 'bg-gray-100 text-gray-800' }
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
      return 'minus'
  }
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'to do':
      return 'border-gray-300'
    case 'in progress':
      return 'border-blue-500'
    case 'done':
      return 'border-green-500'
    default:
      return 'border-gray-300'
  }
}

const getBorderColor = (task: Task) => {
  if (task.is_working) return 'border-blue-500'
  if (task.auto_paused) return 'border-orange-500'
  return getStatusColor(task.status)
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
    console.log('Iniciando trabajo en tarea:', taskId);
    
    // Actualizar inmediatamente el estado local para feedback instantáneo
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = true;
      task.work_started_at = new Date().toISOString();
    }
    
    const response = await fetch(`/tasks/${taskId}/start-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      // Actualizar solo los datos de tareas sin recargar toda la página
      router.reload({ only: ['tasks'] });
    } else {
      let errorMessage = 'Error starting work';
      try {
        const errorData = await response.json();
        errorMessage = errorData.message || errorMessage;
      } catch (jsonError) {
        // Si no es JSON, obtener el texto de la respuesta
        const textResponse = await response.text();
        console.error('Server response:', textResponse);
        
        // Verificar si es una redirección a login
        if (response.status === 302 || textResponse.includes('login') || textResponse.includes('<!DOCTYPE html>')) {
          errorMessage = 'Authentication required. Please log in again.';
        } else {
          errorMessage = `Server error (${response.status}): ${response.statusText}`;
        }
      }
      alert(errorMessage);
      
      // Revertir cambios si hay error
      if (task) {
        task.is_working = false;
        task.work_started_at = null;
      }
    }
  } catch (error) {
    console.error('Network error:', error);
    alert('Network error: Unable to connect to server');
    
    // Revertir cambios si hay error
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = false;
      task.work_started_at = null;
    }
  }
};

const handlePauseWork = async (taskId: string) => {
  try {
    console.log('Pausando trabajo en tarea:', taskId);
    
    // Actualizar inmediatamente el estado local para feedback instantáneo
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = false;
    }
    
    const response = await fetch(`/tasks/${taskId}/pause-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      // Actualizar solo los datos de tareas sin recargar toda la página
      router.reload({ only: ['tasks'] });
    } else {
      let errorMessage = 'Error pausing work';
      try {
        const errorData = await response.json();
        errorMessage = errorData.message || errorMessage;
      } catch (jsonError) {
        // Si no es JSON, obtener el texto de la respuesta
        const textResponse = await response.text();
        console.error('Server response:', textResponse);
        
        // Verificar si es una redirección a login
        if (response.status === 302 || textResponse.includes('login') || textResponse.includes('<!DOCTYPE html>')) {
          errorMessage = 'Authentication required. Please log in again.';
        } else {
          errorMessage = `Server error (${response.status}): ${response.statusText}`;
        }
      }
      alert(errorMessage);
      
      // Revertir cambios si hay error
      if (task) {
        task.is_working = true;
      }
    }
  } catch (error) {
    console.error('Network error:', error);
    alert('Network error: Unable to connect to server');
    
    // Revertir cambios si hay error
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = true;
    }
  }
};

const handleResumeWork = async (taskId: string) => {
  try {
    console.log('Intentando reanudar tarea:', taskId);
    
    // Actualizar inmediatamente el estado local para feedback instantáneo
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = true;
      task.work_started_at = new Date().toISOString();
    }
    
    const response = await fetch(`/tasks/${taskId}/resume-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    console.log('Respuesta del servidor:', response.status, response.statusText);
    
    if (response.ok) {
      const result = await response.json();
      console.log('Resultado exitoso:', result);
      
      // Actualizar la página sin recargar usando Inertia
      router.reload({ only: ['tasks'] });
    } else {
      let errorMessage = 'Error resuming work';
      try {
        const errorData = await response.json();
        errorMessage = errorData.message || errorMessage;
      } catch (jsonError) {
        // Si no es JSON, obtener el texto de la respuesta
        const textResponse = await response.text();
        console.error('Server response:', textResponse);
        
        // Verificar si es una redirección a login
        if (response.status === 302 || textResponse.includes('login') || textResponse.includes('<!DOCTYPE html>')) {
          errorMessage = 'Authentication required. Please log in again.';
        } else {
          errorMessage = `Server error (${response.status}): ${response.statusText}`;
        }
      }
      alert(errorMessage);
      
      // Revertir cambios si hay error
      if (task) {
        task.is_working = false;
        task.work_started_at = null;
      }
    }
  } catch (error) {
    console.error('Network error:', error);
    alert('Network error: Unable to connect to server');
    
    // Revertir cambios si hay error
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = false;
      task.work_started_at = null;
    }
  }
};

const handleFinishWork = async (taskId: string) => {
  try {
    console.log('Finalizando trabajo en tarea:', taskId);
    
    // Actualizar inmediatamente el estado local para feedback instantáneo
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = false;
      task.status = 'done';
    }
    
    const response = await fetch(`/tasks/${taskId}/finish-work`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      // Actualizar solo los datos de tareas sin recargar toda la página
      router.reload({ only: ['tasks'] });
    } else {
      let errorMessage = 'Error finishing work';
      try {
        const errorData = await response.json();
        errorMessage = errorData.message || errorMessage;
      } catch (jsonError) {
        // Si no es JSON, obtener el texto de la respuesta
        const textResponse = await response.text();
        console.error('Server response:', textResponse);
        
        // Verificar si es una redirección a login
        if (response.status === 302 || textResponse.includes('login') || textResponse.includes('<!DOCTYPE html>')) {
          errorMessage = 'Authentication required. Please log in again.';
        } else {
          errorMessage = `Server error (${response.status}): ${response.statusText}`;
        }
      }
      alert(errorMessage);
      
      // Revertir cambios si hay error
      if (task) {
        task.is_working = true;
        task.status = 'in progress';
      }
    }
  } catch (error) {
    console.error('Network error:', error);
    alert('Network error: Unable to connect to server');
    
    // Revertir cambios si hay error
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = true;
      task.status = 'in progress';
      }
  }
};

const handleSelfAssign = async (taskId: string) => {
  try {
    console.log('Auto-asignando tarea:', taskId);
    
    // Actualizar inmediatamente el estado local para feedback instantáneo
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.user_id = '1'; // Asumiendo que el usuario actual tiene ID 1
      task.status = 'to do';
    }
    
    const response = await fetch(`/tasks/${taskId}/self-assign`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      // Actualizar solo los datos de tareas sin recargar toda la página
      router.reload({ only: ['tasks'] });
    } else {
      const error = await response.json();
      alert(error.message || 'Error self-assigning task');
      
      // Revertir cambios si hay error
      if (task) {
        task.user_id = null;
        task.status = 'to do';
      }
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error self-assigning task');
    
    // Revertir cambios si hay error
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.user_id = null;
      task.status = 'to do';
    }
  }
};

const handleResumeAutoPaused = async (taskId: string) => {
  try {
    console.log('Reanudando tarea auto-pausada:', taskId);
    
    // Actualizar inmediatamente el estado local para feedback instantáneo
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = true;
      task.work_started_at = new Date().toISOString();
      task.auto_paused = false;
      task.auto_paused_at = null;
      task.auto_pause_reason = null;
      task.alert_count = 0;
      task.last_alert_at = null;
    }
    
    const response = await fetch(`/tasks/${taskId}/resume-auto-paused`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      const result = await response.json();
      console.log('Tarea auto-pausada reanudada exitosamente:', result);
      
      // Actualizar la página sin recargar usando Inertia
      router.reload({ only: ['tasks'] });
    } else {
      const error = await response.json();
      console.error('Error del servidor:', error);
      alert(error.message || 'Error al reanudar tarea auto-pausada');
      
      // Revertir cambios si hay error
      if (task) {
        task.is_working = false;
        task.work_started_at = null;
        task.auto_paused = true;
      }
    }
  } catch (error) {
    console.error('Client error:', error);
    const errorMessage = error instanceof Error ? error.message : 'Unknown error';
    alert('Error resuming auto-paused task: ' + errorMessage);
    
    // Revertir cambios si hay error
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = false;
      task.work_started_at = null;
      task.auto_paused = true;
    }
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

    <!-- Filtros Avanzados -->
    <div class="mb-8 p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
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
        <!-- Filtro por Proyecto -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Proyecto</label>
          <Select v-model="filters.project_id" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="Todos los proyectos" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">Todos los proyectos</SelectItem>
              <SelectItem 
                v-for="project in projects" 
                :key="project.id" 
                :value="project.id"
              >
                {{ project.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Filtro por Sprint -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sprint</label>
          <Select v-model="filters.sprint_id" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="Todos los sprints" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">Todos los sprints</SelectItem>
              <SelectItem 
                v-for="sprint in filteredSprints" 
                :key="sprint.id" 
                :value="sprint.id"
              >
                {{ sprint.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Filtro por Estado -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
          <Select v-model="filters.status" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="Todos los estados" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">Todos los estados</SelectItem>
              <SelectItem value="to do">To Do</SelectItem>
              <SelectItem value="in progress">In Progress</SelectItem>
              <SelectItem value="done">Done</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Filtro por Prioridad -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prioridad</label>
          <Select v-model="filters.priority" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="Todas las prioridades" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">Todas las prioridades</SelectItem>
              <SelectItem value="high">Alta</SelectItem>
              <SelectItem value="medium">Media</SelectItem>
              <SelectItem value="low">Baja</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <!-- Filtro por Usuario Asignado -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usuario Asignado</label>
          <Select v-model="filters.assigned_user_id" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="Todos los usuarios" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">Todos los usuarios</SelectItem>
              <SelectItem value="unassigned">Sin asignar</SelectItem>
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

        <!-- Ordenar por -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordenar por</label>
          <Select v-model="filters.sort_by" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="Ordenar por" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="recent">Más recientes</SelectItem>
              <SelectItem value="priority">Prioridad</SelectItem>
              <SelectItem value="status">Estado</SelectItem>
              <SelectItem value="story_points">Story Points</SelectItem>
              <SelectItem value="estimated_hours">Horas estimadas</SelectItem>
              <SelectItem value="actual_hours">Horas reales</SelectItem>
              <SelectItem value="completion_percentage">Porcentaje completado</SelectItem>
              <SelectItem value="due_date">Fecha de vencimiento</SelectItem>
              <SelectItem value="assigned_user">Usuario asignado</SelectItem>
              <SelectItem value="project">Proyecto</SelectItem>
              <SelectItem value="sprint">Sprint</SelectItem>
            </SelectContent>
          </Select>
        </div>

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
          <CardTitle class="text-sm font-medium">Active</CardTitle>
          <Icon name="list" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getTaskStats().active }}</div>
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

    <!-- Active Tasks (To Do + In Progress) - Fixed Position -->
    <div v-if="getActiveTasks().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="list" class="h-5 w-5 text-gray-600" />
        Active Tasks
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <TaskCard
          v-for="task in getActiveTasks()"
          :key="task.id"
          :task="task"
          :is-working="isTaskWorking(task)"
          :show-approval-status="false"
          @start-work="handleStartWork"
          @pause-work="handlePauseWork"
          @resume-work="handleResumeWork"
          @finish-work="handleFinishWork"
          @self-assign="handleSelfAssign"
          @resume-auto-paused="handleResumeAutoPaused"
        />
      </div>
    </div>

    <!-- Completed Tasks - Separate Section -->
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
          :show-approval-status="true"
          @start-work="handleStartWork"
          @pause-work="handlePauseWork"
          @resume-work="handleResumeWork"
          @finish-work="handleFinishWork"
          @self-assign="handleSelfAssign"
          @resume-auto-paused="handleResumeAutoPaused"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="tasks.length === 0" class="text-center py-12">
      <Icon name="list" class="h-16 w-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
        {{ hasActiveFilters ? 'No tasks found with current filters' : 'No tasks found' }}
      </h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ hasActiveFilters 
          ? 'Try adjusting your filters or clear them to see all tasks.' 
          : 'Get started by creating your first task for a project.' 
        }}
      </p>
      <div class="flex justify-center gap-4">
        <Button 
          v-if="hasActiveFilters" 
          @click="clearFilters" 
          variant="outline"
        >
          <Icon name="x" class="h-4 w-4 mr-2" />
          Limpiar Filtros
        </Button>
        <CreateTaskModal 
          v-if="hasPermission('tasks.create') && projects.length > 0 && sprints.length > 0" 
          :project="getDefaultProject()"
          :sprint="getDefaultSprint()"
          :developers="getDefaultDevelopers()"
        />
      </div>
    </div>
  </AppLayout>
</template> 