<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import Icon from '@/components/Icon.vue'
import UpdateSprintModal from '@/components/UpdateSprintModal.vue'
import CreateTaskModal from '@/components/CreateTaskModal.vue'
import CardTask from '@/components/CardTask.vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Projects',
    href: '/projects',
  },
  {
    title: 'Sprint Details',
    href: '#',
  },
]

interface Sprint {
    id: string,
    goal: string,
    name: string,
    start_date: string,
    end_date: string,
}

const props = defineProps<{
    sprint: Sprint,
    tasks: any[]
    permissions: string
    project_id: string
    developers?: any[]
}>()



const getSprintProgress = () => {
  const totalTasks = props.tasks?.length || 0
  const completedTasks = props.tasks?.filter((task: any) => task.status === 'done').length || 0
  return totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0
}

const getSprintStats = () => {
  const totalTasks = props.tasks?.length || 0
  const completedTasks = props.tasks?.filter((task: any) => task.status === 'done').length || 0
  const inProgressTasks = props.tasks?.filter((task: any) => task.status === 'in progress').length || 0
  const readyForTestTasks = props.tasks?.filter((task: any) => task.status === 'ready for test').length || 0
  const rejectedTasks = props.tasks?.filter((task: any) => task.status === 'rejected').length || 0
  const toDoTasks = props.tasks?.filter((task: any) => task.status === 'to do').length || 0

  return {
    total: totalTasks,
    completed: completedTasks,
    inProgress: inProgressTasks,
    readyForTest: readyForTestTasks,
    rejected: rejectedTasks,
    toDo: toDoTasks
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const back = () => {
  router.get(`/projects/${props.project_id}`)
}
</script>

<template>
  <Head :title="`${sprint.name} - Sprint Details`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" size="sm" @click="back" class="flex items-center">
            <Icon name="arrow-left" class="h-4 w-4 mr-2" />
            Back
          </Button>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ sprint.name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Sprint Details</p>
          </div>
        </div>
        <UpdateSprintModal
          v-if="permissions === 'admin'"
          :sprint="sprint"
          :project_id="project_id"
        />
      </div>
    </template>

    <!-- Sprint Stats -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
          <Icon name="list" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ getSprintStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">To Do</CardTitle>
          <Icon name="circle" class="h-4 w-4 text-yellow-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-yellow-600">{{ getSprintStats().toDo }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">In Progress</CardTitle>
          <Icon name="play" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getSprintStats().inProgress }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Ready for Test</CardTitle>
          <Icon name="check-square" class="h-4 w-4 text-yellow-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-yellow-600">{{ getSprintStats().readyForTest }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Rejected</CardTitle>
          <Icon name="x-circle" class="h-4 w-4 text-red-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-red-600">{{ getSprintStats().rejected }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completed</CardTitle>
          <Icon name="check-circle" class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ getSprintStats().completed }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Sprint Progress -->
    <Card class="mb-8">
      <CardHeader>
        <CardTitle>Sprint Progress</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Completion</span>
            <span class="font-medium">{{ getSprintProgress() }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all"
              :style="{ width: `${getSprintProgress()}%` }"
            ></div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Sprint Goal -->
    <Card class="mb-8">
      <CardHeader>
        <CardTitle class="flex items-center">
          <Icon name="target" class="h-5 w-5 mr-2" />
          Goal
        </CardTitle>
      </CardHeader>
      <CardContent>
        <p class="text-gray-700 dark:text-gray-300 mb-4">{{ sprint.goal }}</p>
                  <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
            <Icon name="calendar" class="h-4 w-4 mr-2" />
            {{ formatDate(sprint.start_date) }} → {{ formatDate(sprint.end_date) }}
          </div>
      </CardContent>
    </Card>

    <!-- Tasks Section -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Tasks</h2>
        <CreateTaskModal
          v-if="permissions === 'admin'"
          :sprint="sprint"
          :project_id="project_id"
          :developers="developers || []"
        />
      </div>

      <template v-if="tasks && tasks.length">
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
          <CardTask
            v-for="task in tasks"
            :key="task.id"
            :task="task"
            :permissions="permissions"
            :project_id="project_id"
            :sprint="sprint"
            :developers="developers || []"
          />
        </div>
      </template>
      
      <template v-else>
        <Card>
          <CardContent class="text-center py-8">
            <Icon name="check-square" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No tasks created</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              Get started by creating your first task for this sprint.
            </p>
            <CreateTaskModal
              v-if="permissions === 'admin'"
              :sprint="sprint"
              :project_id="project_id"
              :developers="developers || []"
            />
          </CardContent>
        </Card>
      </template>
    </div>
  </AppLayout>
</template>