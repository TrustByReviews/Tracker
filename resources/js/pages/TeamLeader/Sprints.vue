<template>
  <Head title="Sprints" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Sprints by Project</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manage sprints from your assigned projects</p>
        </div>
      </div>
    </template>

    <!-- Filters -->
    <Card class="mb-8">
      <CardHeader>
        <CardTitle class="flex items-center">
          <Filter class="h-5 w-5 mr-2" />
          Filters
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Project Filter -->
          <div>
            <Label class="block text-sm font-medium mb-2">Project</Label>
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

          <!-- Status Filter -->
          <div>
            <Label class="block text-sm font-medium mb-2">Status</Label>
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

          <!-- Sorting -->
          <div>
            <Label class="block text-sm font-medium mb-2">Sort by</Label>
            <Select v-model="filters.sort_by">
              <SelectTrigger>
                <SelectValue placeholder="Sort by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="recent">Most recent</SelectItem>
                <SelectItem value="name">Name</SelectItem>
                <SelectItem value="progress">Progress</SelectItem>
                <SelectItem value="end_date">End date</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex justify-end gap-2 mt-4">
          <Button variant="outline" @click="clearFilters">
            <X class="h-4 w-4 mr-2" />
            Clear Filters
          </Button>
          <Button @click="applyFilters">
            <Search class="h-4 w-4 mr-2" />
            Apply Filters
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- General Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Sprints</CardTitle>
          <List class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ getGeneralStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Active</CardTitle>
          <Play class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ getGeneralStats().active }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Upcoming</CardTitle>
          <Clock class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getGeneralStats().upcoming }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completed</CardTitle>
          <Check class="h-4 w-4 text-gray-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-gray-600">{{ getGeneralStats().completed }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Sprints by Project -->
    <div v-if="groupedSprints.length > 0" class="space-y-8">
      <div v-for="group in groupedSprints" :key="group.project.id" class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <!-- Project Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <Folder class="h-5 w-5 text-blue-500" />
                {{ group.project.name }}
              </h2>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ group.project.description }}
              </p>
            </div>
            <CreateSprintModal 
              v-if="canCreateSprint"
              :project="group.project"
            />
          </div>
          
          <!-- Project Statistics -->
          <div class="mt-4 grid grid-cols-4 gap-4">
            <div class="text-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Total Sprints</span>
              <p class="text-lg font-semibold">{{ group.sprints.length }}</p>
            </div>
            <div class="text-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Active</span>
              <p class="text-lg font-semibold text-green-600">
                {{ group.sprints.filter(s => getSprintStatus(s) === 'active').length }}
              </p>
            </div>
            <div class="text-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Upcoming</span>
              <p class="text-lg font-semibold text-blue-600">
                {{ group.sprints.filter(s => getSprintStatus(s) === 'upcoming').length }}
              </p>
            </div>
            <div class="text-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Completed</span>
              <p class="text-lg font-semibold text-gray-600">
                {{ group.sprints.filter(s => getSprintStatus(s) === 'completed').length }}
              </p>
            </div>
          </div>
        </div>

        <!-- Sprint List -->
        <div class="p-6">
          <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <CardSprint
              v-for="sprint in group.sprints"
              :key="sprint.id"
              :sprint="sprint"
              :permissions="permissions"
              :project_id="group.project.id"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <Calendar class="h-16 w-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No sprints found</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ sprints.length === 0 ? 'No sprints created in your projects.' : 'No sprints match the current filters.' }}
      </p>
      <div class="flex justify-center gap-2">
        <Button variant="outline" @click="clearFilters" v-if="sprints.length > 0">
          <X class="h-4 w-4 mr-2" />
          Clear Filters
        </Button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { 
  Filter,
  X,
  Search,
  List,
  Play,
  Clock,
  Check,
  Folder,
  Calendar
} from 'lucide-vue-next'
import CardSprint from '@/components/CardSprint.vue'
import CreateSprintModal from '@/components/CreateSprintModal.vue'

interface Sprint {
  id: string;
  name: string;
  goal: string;
  start_date: string;
  end_date: string;
  project_id: string;
  tasks?: any[];
  bugs?: any[];
}

interface Project {
  id: string;
  name: string;
  description: string;
  status: string;
  created_by: string;
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  sprints: Sprint[];
  projects: Project[];
  permissions: string;
}>()

// Filter state
const filters = ref({
  project_id: '',
  status: '',
  sort_by: 'recent'
})

// Clear filters
const clearFilters = () => {
  filters.value = {
    project_id: '',
    status: '',
    sort_by: 'recent'
  }
}

// Apply filters
const applyFilters = () => {
  // This function can be expanded if you need to make server calls
  // For now it only updates local state
}

// Get sprint status
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

// Filter and group sprints
const groupedSprints = computed(() => {
  if (!props.sprints || !props.projects) return [];

  let filtered = [...props.sprints]

  // Apply filters
  if (filters.value.project_id) {
    filtered = filtered.filter(sprint => sprint.project_id === filters.value.project_id)
  }

  if (filters.value.status) {
    filtered = filtered.filter(sprint => getSprintStatus(sprint) === filters.value.status)
  }

  // Sort
  switch (filters.value.sort_by) {
    case 'name':
      filtered.sort((a, b) => a.name.localeCompare(b.name))
      break
    case 'progress':
      filtered.sort((a, b) => {
        const progressA = getSprintProgress(a)
        const progressB = getSprintProgress(b)
        return progressB - progressA
      })
      break
    case 'end_date':
      filtered.sort((a, b) => new Date(a.end_date).getTime() - new Date(b.end_date).getTime())
      break
    default: // 'recent'
      filtered.sort((a, b) => new Date(b.start_date).getTime() - new Date(a.start_date).getTime())
  }

  // Group by project
  return props.projects
    .map(project => ({
      project,
      sprints: filtered.filter(sprint => sprint.project_id === project.id)
    }))
    .filter(group => group.sprints.length > 0)
})

// Calculate sprint progress
const getSprintProgress = (sprint: Sprint) => {
  const totalTasks = (sprint.tasks?.length || 0) + (sprint.bugs?.length || 0)
  if (totalTasks === 0) return 0

  const completedTasks = (sprint.tasks?.filter(task => task.status === 'done').length || 0) +
                        (sprint.bugs?.filter(bug => bug.status === 'resolved').length || 0)

  return Math.round((completedTasks / totalTasks) * 100)
}

// Get general statistics
const getGeneralStats = () => {
  if (!props.sprints) {
    return {
      total: 0,
      active: 0,
      upcoming: 0,
      completed: 0
    }
  }

  const total = props.sprints.length
  const active = props.sprints.filter(sprint => getSprintStatus(sprint) === 'active').length
  const upcoming = props.sprints.filter(sprint => getSprintStatus(sprint) === 'upcoming').length
  const completed = props.sprints.filter(sprint => getSprintStatus(sprint) === 'completed').length

  return {
    total,
    active,
    upcoming,
    completed
  }
}

// Check if can create sprints
const canCreateSprint = computed(() => {
  return props.permissions === 'admin' || props.permissions === 'team_leader'
})

// Watch filter changes
watch(filters, () => {
  applyFilters()
}, { deep: true })
</script>