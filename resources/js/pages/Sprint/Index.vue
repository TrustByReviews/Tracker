<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import Icon from '@/components/Icon.vue'
import CardSprint from '@/components/CardSprint.vue'
import CreateSprintModal from '@/components/CreateSprintModal.vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Sprints',
    href: '/sprints',
  },
]

interface Sprint {
    id: string,
    name: string,
    goal: string,
    start_date: string,
    end_date: string,
    project_id: string,
    created_at?: string,
    updated_at?: string,
    tasks?: any[],
    project?: {
        id: string,
        name: string
    },
    // Fase 1: Campos esenciales
    description?: string,
    sprint_type?: string,
    planned_start_date?: string,
    planned_end_date?: string,
    actual_start_date?: string,
    actual_end_date?: string,
    duration_days?: number,
    sprint_objective?: string,
    user_stories_included?: string[],
    assigned_tasks?: string[],
    acceptance_criteria?: string,
    
    // Fase 2: Campos de seguimiento avanzado
    planned_velocity?: number,
    actual_velocity?: number,
    velocity_deviation?: number,
    progress_percentage?: number,
    blockers?: string[],
    risks?: string[],
    blocker_resolution_notes?: string,
    detailed_acceptance_criteria?: string[],
    definition_of_done?: string[],
    quality_gates?: string[],
    bugs_found?: number,
    bugs_resolved?: number,
    bug_resolution_rate?: number,
    code_reviews_completed?: number,
    code_reviews_pending?: number,
    daily_scrums_held?: number,
    daily_scrums_missed?: number,
    daily_scrum_attendance_rate?: number,
    
    // Fase 3: Campos de retrospectiva y mejoras
    isCompleted?: boolean,
    hasRetrospective?: boolean,
    achievements?: string[],
    problems?: string[],
    actions_to_take?: string[],
    retrospective_notes?: string,
    lessons_learned?: string[],
    improvement_areas?: string[],
    team_feedback?: string[],
    stakeholder_feedback?: string[],
    team_satisfaction_score?: number,
    stakeholder_satisfaction_score?: number,
    process_improvements?: string[],
    tool_improvements?: string[],
    communication_improvements?: string[],
    technical_debt_added?: string[],
    technical_debt_resolved?: string[],
    knowledge_shared?: string[],
    skills_developed?: string[],
    mentoring_sessions?: string[],
    sprint_goals_achieved?: string[],
    sprint_goals_partially_achieved?: string[],
    sprint_goals_not_achieved?: string[],
    sprint_ceremony_effectiveness?: string[]
}

interface Project {
    id: string,
    name: string,
    description: string,
    status: string,
    created_by: string,
    created_at: string,
    updated_at: string
}

const props = defineProps<{
    sprints: Sprint[],
    permissions: string,
    projects: Project[],
    filters: {
        project_id?: string,
        sort_by?: string,
        sort_order?: string,
        status?: string,
        item_type?: string
    }
}>()

// Estado reactivo para filtros
const filters = ref({
    project_id: props.filters?.project_id || '',
    sort_by: props.filters?.sort_by || 'recent',
    sort_order: props.filters?.sort_order || 'desc',
    status: props.filters?.status || '',
    item_type: props.filters?.item_type || 'all' // 'all', 'tasks', 'bugs'
})

// Aplicar filtros
const applyFilters = () => {
    router.get('/sprints', filters.value, {
        preserveState: true,
        preserveScroll: true
    })
}

// Clear filtros
const clearFilters = () => {
    filters.value = {
        project_id: '',
        sort_by: 'recent',
        sort_order: 'desc',
        status: '',
        item_type: 'all'
    }
    applyFilters()
}

// Calculate sprint statistics with new fields
const getSprintStats = (sprint: Sprint) => {
    const totalTasks = sprint.tasks?.length || 0
    const completedTasks = sprint.tasks?.filter(task => task.status === 'done').length || 0
    const pendingTasks = totalTasks - completedTasks
    const completionRate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0
    
    const today = new Date()
    const endDate = new Date(sprint.end_date)
    const daysToEnd = Math.ceil((endDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))
    
    // Calculate priority based on pending tasks vs remaining days
    const priorityScore = daysToEnd > 0 ? pendingTasks / daysToEnd : pendingTasks
    
    // New metrics from Phase 2
    const velocityDeviation = sprint.velocity_deviation || 0
    const bugResolutionRate = sprint.bug_resolution_rate || 0
    const attendanceRate = sprint.daily_scrum_attendance_rate || 0
    const codeReviewCompletionRate = sprint.code_reviews_completed && sprint.code_reviews_pending 
        ? Math.round((sprint.code_reviews_completed / (sprint.code_reviews_completed + sprint.code_reviews_pending)) * 100)
        : 0
    
    return {
        totalTasks,
        completedTasks,
        pendingTasks,
        completionRate,
        daysToEnd,
        priorityScore,
        velocityDeviation,
        bugResolutionRate,
        attendanceRate,
        codeReviewCompletionRate,
        progressPercentage: sprint.progress_percentage || 0,
        blockersCount: sprint.blockers?.length || 0,
        risksCount: sprint.risks?.length || 0
    }
}

// Obtener estado del sprint
const getSprintStatus = (sprint: Sprint) => {
    const today = new Date()
    const startDate = new Date(sprint.start_date)
    const endDate = new Date(sprint.end_date)
    
    if (today >= startDate && today <= endDate) {
        return 'active'
    } else if (today < startDate) {
        return 'upcoming'
    } else {
        return 'completed'
    }
}

// Obtener color de prioridad
const getPriorityColor = (priorityScore: number) => {
    if (priorityScore > 2) return 'text-red-600'
    if (priorityScore > 1) return 'text-orange-600'
    if (priorityScore > 0.5) return 'text-yellow-600'
    return 'text-green-600'
}

// Obtener icono de prioridad
const getPriorityIcon = (priorityScore: number) => {
    if (priorityScore > 2) return 'alert-triangle'
    if (priorityScore > 1) return 'clock'
    if (priorityScore > 0.5) return 'info'
    return 'check-circle'
}

// Get sprint type badge
const getSprintTypeBadge = (sprintType: string) => {
    const types = {
        'regular': { label: 'Regular', class: 'bg-blue-100 text-blue-800' },
        'release': { label: 'Release', class: 'bg-green-100 text-green-800' },
        'hotfix': { label: 'Hotfix', class: 'bg-red-100 text-red-800' }
    }
    return types[sprintType as keyof typeof types] || { label: 'Unknown', class: 'bg-gray-100 text-gray-800' }
}

// Filter sprints por estado
const filteredSprints = computed(() => {
    let filtered = props.sprints

    // Filter by status if selected in filters
    if (filters.value.status) {
        filtered = filtered.filter(sprint => getSprintStatus(sprint) === filters.value.status)
    }

    return filtered
})

// Get enhanced general statistics
const getGeneralStats = () => {
    const totalSprints = props.sprints.length
    const activeSprints = props.sprints.filter(sprint => getSprintStatus(sprint) === 'active').length
    const completedSprints = props.sprints.filter(sprint => getSprintStatus(sprint) === 'completed').length
    const upcomingSprints = props.sprints.filter(sprint => getSprintStatus(sprint) === 'upcoming').length
    
    // New metrics
    const totalBugs = props.sprints.reduce((sum, sprint) => sum + (sprint.bugs_found || 0), 0)
    const resolvedBugs = props.sprints.reduce((sum, sprint) => sum + (sprint.bugs_resolved || 0), 0)
    const totalBlockers = props.sprints.reduce((sum, sprint) => sum + (sprint.blockers?.length || 0), 0)
    const totalRisks = props.sprints.reduce((sum, sprint) => sum + (sprint.risks?.length || 0), 0)
    
    const avgVelocityDeviation = props.sprints.length > 0 
        ? props.sprints.reduce((sum, sprint) => sum + (sprint.velocity_deviation || 0), 0) / props.sprints.length
        : 0
    
    return {
        total: totalSprints,
        active: activeSprints,
        completed: completedSprints,
        upcoming: upcomingSprints,
        totalBugs,
        resolvedBugs,
        totalBlockers,
        totalRisks,
        avgVelocityDeviation: Math.round(avgVelocityDeviation * 100) / 100
    }
}

// Obtener sprints por estado
const getSprintsByStatus = (status: string) => {
    return filteredSprints.value.filter(sprint => getSprintStatus(sprint) === status)
}

// Obtener opciones de ordenamiento
const sortOptions = [
    { value: 'recent', label: 'Most recent' },
    { value: 'task_count', label: 'Most tasks' },
    { value: 'completed_tasks', label: 'Most completed tasks' },
    { value: 'pending_tasks', label: 'Most pending tasks' },
    { value: 'completion_rate', label: 'Highest completion rate' },
    { value: 'days_to_end', label: 'Closest to closing' },
    { value: 'priority_score', label: 'Highest priority' },
    { value: 'velocity_deviation', label: 'Velocity deviation' },
    { value: 'bug_resolution_rate', label: 'Bug resolution rate' },
    { value: 'progress_percentage', label: 'Progress percentage' }
]

// Opciones de filtro por tipo
const itemTypeOptions = [
    { value: 'all', label: 'All elements' },
    { value: 'tasks', label: 'Tasks only' },
    { value: 'bugs', label: 'Bugs only' }
]

// Observar cambios en filtros
watch(filters, () => {
    applyFilters()
}, { deep: true })
</script>

<template>
  <Head title="Sprints" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Sprints</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manage and track all project sprints with advanced metrics</p>
        </div>
        <CreateSprintModal 
          v-if="permissions === 'admin' && projects.length > 0" 
          :projects="projects" 
        />
      </div>
    </template>

    <!-- Enhanced Sprint Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xs sm:text-sm font-medium">Total Sprints</CardTitle>
          <Icon name="list" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-xl sm:text-2xl font-bold">{{ getGeneralStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xs sm:text-sm font-medium">Active</CardTitle>
          <Icon name="play" class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-xl sm:text-2xl font-bold text-green-600">{{ getGeneralStats().active }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xs sm:text-sm font-medium">Upcoming</CardTitle>
          <Icon name="clock" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-xl sm:text-2xl font-bold text-blue-600">{{ getGeneralStats().upcoming }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xs sm:text-sm font-medium">Completed</CardTitle>
          <Icon name="check" class="h-4 w-4 text-gray-600" />
        </CardHeader>
        <CardContent>
          <div class="text-xl sm:text-2xl font-bold text-gray-600">{{ getGeneralStats().completed }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xs sm:text-sm font-medium">Bugs Resolved</CardTitle>
          <Icon name="bug" class="h-4 w-4 text-purple-600" />
        </CardHeader>
        <CardContent>
          <div class="text-xl sm:text-2xl font-bold text-purple-600">{{ getGeneralStats().resolvedBugs }}/{{ getGeneralStats().totalBugs }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xs sm:text-sm font-medium">Avg Velocity Dev.</CardTitle>
          <Icon name="trending-down" class="h-4 w-4 text-orange-600" />
        </CardHeader>
        <CardContent>
          <div class="text-xl sm:text-2xl font-bold text-orange-600">{{ getGeneralStats().avgVelocityDeviation }}%</div>
        </CardContent>
      </Card>
    </div>

    <!-- Filtros Avanzados -->
    <Card class="mb-8">
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Icon name="filter" class="h-5 w-5" />
          Advanced Filters
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
          <!-- Filtro por Project -->
          <div>
            <label class="block text-sm font-medium mb-2">Project</label>
            <Select v-model="filters.project_id">
              <SelectTrigger>
                <SelectValue placeholder="All projects" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">All projects</SelectItem>
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

          <!-- Filtro por Type de Elemento -->
          <div>
            <label class="block text-sm font-medium mb-2">Element Type</label>
            <Select v-model="filters.item_type">
              <SelectTrigger>
                <SelectValue placeholder="All elements" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="option in itemTypeOptions" 
                  :key="option.value" 
                  :value="option.value"
                >
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Filtro por Estado -->
          <div>
            <label class="block text-sm font-medium mb-2">Status</label>
            <Select v-model="filters.status">
              <SelectTrigger>
                <SelectValue placeholder="All statuses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">All statuses</SelectItem>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="upcoming">Upcoming</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Ordenamiento -->
          <div>
            <label class="block text-sm font-medium mb-2">Sort by</label>
            <Select v-model="filters.sort_by">
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="option in sortOptions" 
                  :key="option.value" 
                  :value="option.value"
                >
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Orden -->
          <div>
            <label class="block text-sm font-medium mb-2">Order</label>
            <Select v-model="filters.sort_order">
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="desc">Descending</SelectItem>
                <SelectItem value="asc">Ascending</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex justify-end gap-2 mt-4">
          <Button variant="outline" @click="clearFilters">
            <Icon name="x" class="h-4 w-4 mr-2" />
            Clear Filters
          </Button>
          <Button @click="applyFilters">
            <Icon name="search" class="h-4 w-4 mr-2" />
            Apply Filters
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Sprints with Enhanced Information -->
    <div v-if="filteredSprints.length > 0" class="space-y-8">
      <!-- Active Sprints -->
      <div v-if="getSprintsByStatus('active').length > 0">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="play" class="h-5 w-5 text-green-600" />
          Active Sprints
          <Badge variant="secondary" class="ml-2">{{ getSprintsByStatus('active').length }}</Badge>
        </h2>
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
          <CardSprint
            v-for="sprint in getSprintsByStatus('active')"
            :key="sprint.id"
            :sprint="sprint"
            :permissions="permissions"
            :project_id="sprint.project?.id || sprint.project_id"
          />
        </div>
      </div>

      <!-- Upcoming Sprints -->
      <div v-if="getSprintsByStatus('upcoming').length > 0">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="clock" class="h-5 w-5 text-blue-600" />
          Upcoming Sprints
          <Badge variant="secondary" class="ml-2">{{ getSprintsByStatus('upcoming').length }}</Badge>
        </h2>
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
          <CardSprint
            v-for="sprint in getSprintsByStatus('upcoming')"
            :key="sprint.id"
            :sprint="sprint"
            :permissions="permissions"
            :project_id="sprint.project?.id || sprint.project_id"
          />
        </div>
      </div>

      <!-- Completed Sprints -->
      <div v-if="getSprintsByStatus('completed').length > 0">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="check" class="h-5 w-5 text-gray-600" />
          Completed Sprints
          <Badge variant="secondary" class="ml-2">{{ getSprintsByStatus('completed').length }}</Badge>
        </h2>
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
          <CardSprint
            v-for="sprint in getSprintsByStatus('completed')"
            :key="sprint.id"
            :sprint="sprint"
            :permissions="permissions"
            :project_id="sprint.project?.id || sprint.project_id"
          />
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredSprints.length === 0" class="text-center py-12">
      <Icon name="calendar" class="h-16 w-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No sprints found</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ props.sprints.length === 0 ? 'Get started by creating your first sprint for a project.' : 'No sprints match the current filters.' }}
      </p>
      <div class="flex justify-center gap-2">
        <Button variant="outline" @click="clearFilters" v-if="props.sprints.length > 0">
          <Icon name="x" class="h-4 w-4 mr-2" />
          Clear Filters
        </Button>
        <CreateSprintModal 
          v-if="permissions === 'admin' && projects.length > 0" 
          :projects="projects" 
        />
      </div>
    </div>
  </AppLayout>
</template> 