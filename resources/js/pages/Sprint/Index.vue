<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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
    }
}

const props = defineProps<{
    sprints: Sprint[],
    permissions: string,
    projects: any[]
}>()



const _formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Obtener estadísticas generales de sprints
const getSprintStats = () => {
  const totalSprints = props.sprints.length;
  const activeSprints = props.sprints.filter(sprint => {
    const today = new Date();
    const startDate = new Date(sprint.start_date);
    const endDate = new Date(sprint.end_date);
    return today >= startDate && today <= endDate;
  }).length;
  
  const completedSprints = props.sprints.filter(sprint => {
    const today = new Date();
    const endDate = new Date(sprint.end_date);
    return today > endDate;
  }).length;
  
  const upcomingSprints = props.sprints.filter(sprint => {
    const today = new Date();
    const startDate = new Date(sprint.start_date);
    return today < startDate;
  }).length;
  
  return {
    total: totalSprints,
    active: activeSprints,
    completed: completedSprints,
    upcoming: upcomingSprints
  };
}

// Filtrar sprints por estado
const getActiveSprints = () => {
  return props.sprints.filter(sprint => {
    const today = new Date();
    const startDate = new Date(sprint.start_date);
    const endDate = new Date(sprint.end_date);
    return today >= startDate && today <= endDate;
  });
}

const getUpcomingSprints = () => {
  return props.sprints.filter(sprint => {
    const today = new Date();
    const startDate = new Date(sprint.start_date);
    return today < startDate;
  });
}

const getCompletedSprints = () => {
  return props.sprints.filter(sprint => {
    const today = new Date();
    const endDate = new Date(sprint.end_date);
    return today > endDate;
  });
}
</script>

<template>
  <Head title="Sprints" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Sprints</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manage and track all project sprints</p>
        </div>
        <CreateSprintModal 
          v-if="permissions === 'admin' && projects.length > 0" 
          :project="projects[0]" 
        />
      </div>
    </template>

    <!-- Sprint Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Sprints</CardTitle>
          <Icon name="list" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ getSprintStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Active</CardTitle>
          <Icon name="play" class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ getSprintStats().active }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Upcoming</CardTitle>
          <Icon name="clock" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getSprintStats().upcoming }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completed</CardTitle>
          <Icon name="check" class="h-4 w-4 text-gray-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-gray-600">{{ getSprintStats().completed }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Active Sprints -->
    <div v-if="getActiveSprints().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="play" class="h-5 w-5 text-green-600" />
        Active Sprints
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <CardSprint
          v-for="sprint in getActiveSprints()"
          :key="sprint.id"
          :sprint="sprint"
          :permissions="permissions"
          :project_id="sprint.project?.id || sprint.project_id"
        />
      </div>
    </div>

    <!-- Upcoming Sprints -->
    <div v-if="getUpcomingSprints().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="clock" class="h-5 w-5 text-blue-600" />
        Upcoming Sprints
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <CardSprint
          v-for="sprint in getUpcomingSprints()"
          :key="sprint.id"
          :sprint="sprint"
          :permissions="permissions"
          :project_id="sprint.project?.id || sprint.project_id"
        />
      </div>
    </div>

    <!-- Completed Sprints -->
    <div v-if="getCompletedSprints().length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <Icon name="check" class="h-5 w-5 text-gray-600" />
        Completed Sprints
      </h2>
      <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <CardSprint
          v-for="sprint in getCompletedSprints()"
          :key="sprint.id"
          :sprint="sprint"
          :permissions="permissions"
          :project_id="sprint.project?.id || sprint.project_id"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="sprints.length === 0" class="text-center py-12">
      <Icon name="calendar" class="h-16 w-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No sprints found</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        Get started by creating your first sprint for a project.
      </p>
      <CreateSprintModal 
        v-if="permissions === 'admin' && projects.length > 0" 
        :project="projects[0]" 
      />
    </div>
  </AppLayout>
</template> 