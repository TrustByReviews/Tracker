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
import { ref, computed } from 'vue'
import { robustPost } from '@/utils/network'

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
    last_alert_at?: string | null,
    // QA fields
    qa_status?: string,
    qa_assigned_to?: string,
    qa_assigned_at?: string,
    qa_started_at?: string,
    qa_completed_at?: string,
    qa_notes?: string,
    qa_rejection_reason?: string,
    qa_reviewed_by?: string,
    qa_reviewed_at?: string,
    // Team Leader fields
    team_leader_final_approval?: boolean,
    team_leader_final_approval_at?: string,
    team_leader_final_notes?: string,
    team_leader_requested_changes?: boolean,
    team_leader_requested_changes_at?: string,
    team_leader_change_notes?: string,
    team_leader_reviewed_by?: string,
    // Re-work tracking fields
    original_time_seconds?: number,
    retwork_time_seconds?: number,
    original_work_finished_at?: string,
    retwork_started_at?: string,
    has_been_returned?: boolean,
    return_count?: number,
    last_returned_by?: string,
    last_returned_at?: string
}

const props = defineProps<{
    tasks: Task[],
    permissions: string,
    projects: any[],
    sprints: any[],
    developers: any[],
    filters?: any
}>()

// Reactive state for filters
const filters = ref({
    project_id: props.filters?.project_id || '',
    sprint_id: props.filters?.sprint_id || '',
    status: props.filters?.status || '',
    priority: props.filters?.priority || '',
    assigned_user_id: props.filters?.assigned_user_id || '',
    sort_by: props.filters?.sort_by || 'recent',
    sort_order: props.filters?.sort_order || 'desc',
    qa_status: props.filters?.qa_status || '',
    team_leader_status: props.filters?.team_leader_status || ''
})

// Debug: Show props information
console.log('Task/Index.vue - Props received:', {
    tasksCount: props.tasks?.length || 0,
    permissions: props.permissions,
    projectsCount: props.projects?.length || 0,
    sprintsCount: props.sprints?.length || 0,
    developersCount: props.developers?.length || 0,
    tasks: props.tasks
})

// Filtering functions
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
        sort_order: 'desc',
        qa_status: '',
        team_leader_status: ''
    }
    applyFilters()
}

// Computed properties for filters
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
           filters.value.qa_status ||
           filters.value.team_leader_status ||
           filters.value.sort_by !== 'recent' ||
           filters.value.sort_order !== 'desc'
})

const { hasPermission } = usePermissions();

// Get general task statistics
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

// Filter helpers
const getToDoTasks = () => props.tasks.filter(task => task.status === 'to do')
const getInProgressTasks = () => props.tasks.filter(task => task.status === 'in progress')
const getActiveTasks = () => props.tasks.filter(task => task.status === 'to do' || task.status === 'in progress')
const getDoneTasks = () => props.tasks.filter(task => task.status === 'done')
const getRejectedByQaTasks = () => props.tasks.filter(task => task.qa_status === 'rejected')
const getTeamLeaderChangesRequestedTasks = () => props.tasks.filter(task => task.team_leader_requested_changes === true)
const getActiveTasksIncludingRejected = () => props.tasks.filter(task => (
  task.status === 'to do' || 
  task.status === 'in progress' || 
  task.qa_status === 'rejected' || 
  task.team_leader_requested_changes === true
))

// UI helpers
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

// Defaults for modal
const getDefaultProject = () => props.projects.length > 0 ? props.projects[0] : null
const getDefaultSprint = () => props.sprints.length > 0 ? props.sprints[0] : null
const getDefaultDevelopers = () => props.developers || []

// Time tracking handlers
const handleStartWork = async (taskId: string) => {
  const task = props.tasks.find(t => t.id === taskId);
  
  // Optimistic update
  if (task) {
    task.is_working = true;
    task.work_started_at = new Date().toISOString();
  }

  const result = await robustPost(`/tasks/${taskId}/start-work`, {}, {
    timeout: 10000,
    retries: 2,
    retryDelay: 1000
  });

  if (result.success) {
    router.reload({ only: ['tasks'] });
  } else {
    // Revert optimistic update
    if (task) {
      task.is_working = false;
      task.work_started_at = null;
    }
    
    alert(result.error || 'Error starting work');
  }
};

const handlePauseWork = async (taskId: string) => {
  const task = props.tasks.find(t => t.id === taskId);
  
  // Optimistic update
  if (task) {
    task.is_working = false;
  }

  const result = await robustPost(`/tasks/${taskId}/pause-work`, {}, {
    timeout: 10000,
    retries: 2,
    retryDelay: 1000
  });

  if (result.success) {
    router.reload({ only: ['tasks'] });
  } else {
    // Revert optimistic update
    if (task) {
      task.is_working = true;
    }
    
    alert(result.error || 'Error pausing work');
  }
};

const handleResumeWork = async (taskId: string) => {
  const task = props.tasks.find(t => t.id === taskId);
  
  // Optimistic update
  if (task) {
    task.is_working = true;
    task.work_started_at = new Date().toISOString();
  }

  const result = await robustPost(`/tasks/${taskId}/resume-work`, {}, {
    timeout: 10000,
    retries: 2,
    retryDelay: 1000
  });

  if (result.success) {
    router.reload({ only: ['tasks'] });
  } else {
    // Revert optimistic update
    if (task) {
      task.is_working = false;
      task.work_started_at = null;
    }
    
    alert(result.error || 'Error resuming work');
  }
};

const handleFinishWork = async (taskId: string) => {
  const task = props.tasks.find(t => t.id === taskId);
  
  // Optimistic update
  if (task) {
    task.is_working = false;
    task.status = 'done';
  }

  const result = await robustPost(`/tasks/${taskId}/finish-work`, {}, {
    timeout: 10000,
    retries: 2,
    retryDelay: 1000
  });

  if (result.success) {
    router.reload({ only: ['tasks'] });
  } else {
    // Revert optimistic update
    if (task) {
      task.is_working = true;
      task.status = 'in progress';
    }
    
    alert(result.error || 'Error finishing work');
  }
};

const handleSelfAssign = async (taskId: string) => {
  try {
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.user_id = '1';
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
      router.reload({ only: ['tasks'] });
    } else {
      const error = await response.json();
      alert(error.message || 'Error self-assigning task');
      if (task) {
        task.user_id = null;
        task.status = 'to do';
      }
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error self-assigning task');
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.user_id = null;
      task.status = 'to do';
    }
  }
};

const handleResumeAutoPaused = async (taskId: string) => {
  try {
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
      await response.json();
      router.reload({ only: ['tasks'] });
    } else {
      const error = await response.json();
      alert(error.message || 'Error resuming auto-paused task');
      if (task) {
        task.is_working = false;
        task.work_started_at = null;
        task.auto_paused = true;
      }
    }
  } catch (error) {
    console.error('Client error:', error);
    const errorMonthsage = error instanceof Error ? error.message : 'Unknown error';
    alert('Error resuming auto-paused task: ' + errorMonthsage);
    const task = props.tasks.find(t => t.id === taskId);
    if (task) {
      task.is_working = false;
      task.work_started_at = null;
      task.auto_paused = true;
    }
  }
};

// Check if a task is currently being worked on
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

    <!-- Advanced Filters -->
    <div class="mb-8 p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Advanced Filters</h3>
        <Button 
          v-if="hasActiveFilters" 
          @click="clearFilters" 
          variant="outline" 
          size="sm"
        >
          <Icon name="x" class="h-4 w-4 mr-2" />
          Clear Filters
        </Button>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Filter by Project -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project</label>
          <Select v-model="filters.project_id" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All Projects" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Projects</SelectItem>
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

        <!-- Filter by Sprint -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sprint</label>
          <Select v-model="filters.sprint_id" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All Sprints" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Sprints</SelectItem>
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

        <!-- Filter by Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
          <Select v-model="filters.status" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All Statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Statuses</SelectItem>
              <SelectItem value="to do">To Do</SelectItem>
              <SelectItem value="in progress">In Progress</SelectItem>
              <SelectItem value="done">Done</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Filter by Priority -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
          <Select v-model="filters.priority" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All Priorities" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Priorities</SelectItem>
              <SelectItem value="high">High</SelectItem>
              <SelectItem value="medium">Medium</SelectItem>
              <SelectItem value="low">Low</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <!-- Filter by Assigned User -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned User</label>
          <Select v-model="filters.assigned_user_id" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All Users" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Users</SelectItem>
              <SelectItem value="unassigned">Unassigned</SelectItem>
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

        <!-- Sort by -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort by</label>
          <Select v-model="filters.sort_by" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="Sort by" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="recent">Most recent</SelectItem>
              <SelectItem value="priority">Priority</SelectItem>
              <SelectItem value="status">Status</SelectItem>
              <SelectItem value="story_points">Story Points</SelectItem>
              <SelectItem value="estimated_hours">Estimated Hours</SelectItem>
              <SelectItem value="actual_hours">Actual Hours</SelectItem>
              <SelectItem value="completion_percentage">Completion Percentage</SelectItem>
              <SelectItem value="due_date">Due Date</SelectItem>
              <SelectItem value="assigned_user">Assigned User</SelectItem>
              <SelectItem value="project">Project</SelectItem>
              <SelectItem value="sprint">Sprint</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Order -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order</label>
          <Select v-model="filters.sort_order" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="Order" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="asc">Ascending</SelectItem>
              <SelectItem value="desc">Descending</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <!-- Developer-specific filters -->
      <div v-if="permissions === 'developer'" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <!-- QA Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">QA Status</label>
          <Select v-model="filters.qa_status" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All QA statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All QA statuses</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="ready_for_test">Ready for test</SelectItem>
              <SelectItem value="testing">Testing</SelectItem>
              <SelectItem value="approved">Approved</SelectItem>
              <SelectItem value="rejected">Rejected</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Team Leader Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Team Leader Status</label>
          <Select v-model="filters.team_leader_status" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All TL statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All TL statuses</SelectItem>
              <SelectItem value="pending">Pending review</SelectItem>
              <SelectItem value="approved">Approved</SelectItem>
              <SelectItem value="changes_requested">Changes requested</SelectItem>
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

    <!-- Active Tasks (To Do + In Progress) -->
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

    <!-- Completed Tasks -->
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

    <!-- Rejected by QA (Developer only) -->
    <div v-if="permissions === 'developer' && getRejectedByQaTasks().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="x-circle" class="h-5 w-5 text-red-600" />
        Rejected by QA
        <span class="text-sm text-red-600 bg-red-100 px-2 py-1 rounded-full">{{ getRejectedByQaTasks().length }}</span>
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <TaskCard
          v-for="task in getRejectedByQaTasks()"
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

    <!-- Team Leader Changes Requested (Developer only) -->
    <div v-if="permissions === 'developer' && getTeamLeaderChangesRequestedTasks().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="alert-triangle" class="h-5 w-5 text-orange-600" />
        Changes Requested by Team Leader
        <span class="text-sm text-orange-600 bg-orange-100 px-2 py-1 rounded-full">{{ getTeamLeaderChangesRequestedTasks().length }}</span>
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <TaskCard
          v-for="task in getTeamLeaderChangesRequestedTasks()"
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
          Clear Filters
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
