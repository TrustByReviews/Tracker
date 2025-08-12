<script setup lang="ts">
import { ref } from 'vue'
import { Project } from '@/types'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import Badge from '@/components/ui/badge/Badge.vue'
import Icon from '@/components/Icon.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import UpdateProjectModal from '@/components/UpdateProjectModal.vue'
import CreateSprintModal from '@/components/CreateSprintModal.vue'
import CardSprint from '@/components/CardSprint.vue'
import FinishProjectModal from '@/components/FinishProjectModal.vue'
import ManageProjectUsersModal from '@/components/ManageProjectUsersModal.vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Projects',
    href: '/projects',
  },
  {
    title: 'Project Details',
    href: '#',
  },
]

const props = defineProps<{
  project: Project,
  developers: any[],
  permissions: string,
  sprints: any[]
}>()





const getStatusClass = (status: string) => {
  switch (status) {
    case 'active':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    case 'completed':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
    case 'paused':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

const getProjectProgress = () => {
  const totalTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.length || 0)
  }, 0) || 0

  const completedTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'done').length || 0)
  }, 0) || 0

  return totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getPriorityClass = (priority: string) => {
  switch (priority) {
    case 'high':
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    case 'medium':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    case 'low':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const getProjectStats = () => {
  const totalTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.length || 0)
  }, 0) || 0

  const completedTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'done').length || 0)
  }, 0) || 0

  const inProgressTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'in progress').length || 0)
  }, 0) || 0

  const rejectedTasks = props.sprints?.reduce((acc: number, sprint: any) => {
    return acc + (sprint.tasks?.filter((task: any) => task.status === 'rejected').length || 0)
  }, 0) || 0

  return {
    total: totalTasks,
    completed: completedTasks,
    inProgress: inProgressTasks,
    rejected: rejectedTasks
  }
}

const back = () => {
  router.get('/projects')
}

const getAllTasks = () => {
  return props.sprints?.reduce((acc: any[], sprint: any) => {
    return acc.concat(sprint.tasks || [])
  }, []) || []
}

const getAllBugs = () => {
  return props.sprints?.reduce((acc: any[], sprint: any) => {
    return acc.concat(sprint.bugs || [])
  }, []) || []
}

// Manage Users Modal
const showManageUsersModal = ref(false)
</script>

<template>
  <Head :title="`${project.name} - Project Details`" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" size="sm" @click="back" class="flex items-center">
            <Icon name="arrow-left" class="h-4 w-4 mr-2" />
            Back
          </Button>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ project.name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Project Details</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <Button 
            v-if="permissions === 'admin'"
            variant="outline" 
            size="sm"
            @click="router.visit('/analytics/projects')"
            class="flex items-center"
          >
            <Icon name="trending-up" class="h-4 w-4 mr-2" />
            View Analytics
          </Button>

          <Button 
            v-if="permissions === 'admin'"
            variant="outline" 
            size="sm"
            @click="showManageUsersModal = true"
            class="flex items-center"
          >
            <Icon name="users" class="h-4 w-4 mr-2" />
            Manage Users
          </Button>


          
          <FinishProjectModal
            v-if="permissions === 'admin' && project.status !== 'completed' && project.status !== 'cancelled'"
            :project="project"
            :sprints="sprints"
            :tasks="getAllTasks()"
            :bugs="getAllBugs()"
            :user-role="permissions"
          />
          <Badge :class="getStatusClass(project.status)">
            {{ project.status }}
          </Badge>
          <UpdateProjectModal
            v-if="permissions === 'admin'"
            :project="project"
            :developers="developers"
          />
        </div>
      </div>
    </template>

    <!-- Project Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
          <Icon name="list" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ getProjectStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completed</CardTitle>
          <Icon name="check-circle" class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ getProjectStats().completed }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">In Progress</CardTitle>
          <Icon name="play" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getProjectStats().inProgress }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Rejected</CardTitle>
          <Icon name="x-circle" class="h-4 w-4 text-red-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-red-600">{{ getProjectStats().rejected }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Project Progress -->
    <Card class="mb-8">
      <CardHeader>
        <CardTitle>Project Progress</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Completion</span>
            <span class="font-medium">{{ getProjectProgress() }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all"
              :style="{ width: `${getProjectProgress()}%` }"
            ></div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Project Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Description -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="file-text" class="h-5 w-5 mr-2" />
            Description
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-gray-700 dark:text-gray-300">{{ project.description }}</p>
          
          <!-- Objectives -->
          <div v-if="project.objectives" class="mt-4">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Objectives</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ project.objectives }}</p>
          </div>
        </CardContent>
      </Card>

      <!-- Team Members -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="users" class="h-5 w-5 mr-2" />
            Team Members
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <template v-if="developers.length">
              <div
                v-for="developer in developers"
                :key="developer.id"
                class="flex items-center space-x-3"
              >
                <Avatar class="h-8 w-8">
                  <AvatarImage :src="developer.avatar || ''" :alt="developer.name" />
                  <AvatarFallback class="text-xs">
                    {{ getInitials(developer.name) }}
                  </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ developer.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ developer.email }}
                  </p>
                </div>
                <Badge variant="secondary" class="text-xs">
                  {{ developer.roles?.[0]?.name || 'Developer' }}
                </Badge>
              </div>
            </template>
            <div v-else class="text-center py-4">
              <Icon name="users" class="h-8 w-8 text-gray-400 mx-auto mb-2" />
              <p class="text-sm text-gray-500 dark:text-gray-400">No team members assigned</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Project Info -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="info" class="h-5 w-5 mr-2" />
            Project Info
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
              <Badge :class="getStatusClass(project.status)">
                {{ project.status }}
              </Badge>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Priority</span>
              <Badge :class="getPriorityClass(project.priority)">
                {{ project.priority }}
              </Badge>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Category</span>
              <span class="text-sm font-medium">{{ project.category }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Development Type</span>
              <span class="text-sm font-medium">{{ project.development_type }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Methodology</span>
              <span class="text-sm font-medium">{{ project.methodology }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Sprints</span>
              <span class="text-sm font-medium">{{ sprints.length }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Created</span>
              <span class="text-sm font-medium">{{ new Date(project.created_at).toLocaleDateString() }}</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Advanced Project Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
      <!-- Technology Stack -->
      <Card v-if="project.technologies || project.programming_languages || project.frameworks">
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="code" class="h-5 w-5 mr-2" />
            Technology Stack
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Technologies -->
            <div v-if="project.technologies && project.technologies.length">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Technologies</h4>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="tech in project.technologies" 
                  :key="tech"
                  class="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                >
                  {{ tech }}
                </Badge>
              </div>
            </div>

            <!-- Programming Languages -->
            <div v-if="project.programming_languages && project.programming_languages.length">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Programming Languages</h4>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="lang in project.programming_languages" 
                  :key="lang"
                  class="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400"
                >
                  {{ lang }}
                </Badge>
              </div>
            </div>

            <!-- Frameworks -->
            <div v-if="project.frameworks && project.frameworks.length">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Frameworks</h4>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="framework in project.frameworks" 
                  :key="framework"
                  class="bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400"
                >
                  {{ framework }}
                </Badge>
              </div>
            </div>

            <!-- Database & Architecture -->
            <div class="grid grid-cols-2 gap-4">
              <div v-if="project.database_type">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Database</h4>
                <Badge class="bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400">
                  {{ project.database_type }}
                </Badge>
              </div>
              <div v-if="project.architecture">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Architecture</h4>
                <Badge class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400">
                  {{ project.architecture }}
                </Badge>
              </div>
            </div>

            <!-- External Integrations -->
            <div v-if="project.external_integrations && project.external_integrations.length">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">External Integrations</h4>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="integration in project.external_integrations" 
                  :key="integration"
                  class="bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                >
                  {{ integration }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Planning & Budget -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="calendar" class="h-5 w-5 mr-2" />
            Planning & Budget
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Dates -->
            <div class="grid grid-cols-2 gap-4">
              <div v-if="project.planned_start_date">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Planned Start</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ new Date(project.planned_start_date).toLocaleDateString() }}</p>
              </div>
              <div v-if="project.planned_end_date">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Planned End</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ new Date(project.planned_end_date).toLocaleDateString() }}</p>
              </div>
              <div v-if="project.actual_start_date">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Actual Start</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ new Date(project.actual_start_date).toLocaleDateString() }}</p>
              </div>
              <div v-if="project.actual_end_date">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Actual End</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ new Date(project.actual_end_date).toLocaleDateString() }}</p>
              </div>
            </div>

            <!-- Budget -->
            <div v-if="project.estimated_budget" class="space-y-2">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white">Budget</h4>
              <div class="space-y-1">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Estimated:</span>
                  <span class="text-sm font-medium">${{ formatCurrency(project.estimated_budget) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Used:</span>
                  <span class="text-sm font-medium">${{ formatCurrency(project.used_budget || 0) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Remaining:</span>
                  <span class="text-sm font-medium">${{ formatCurrency((project.estimated_budget || 0) - (project.used_budget || 0)) }}</span>
                </div>
              </div>
            </div>

            <!-- Current Sprint -->
            <div v-if="project.current_sprint">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Current Sprint</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ project.current_sprint }}</p>
            </div>

            <!-- Estimated Velocity -->
            <div v-if="project.estimated_velocity">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Estimated Velocity</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ project.estimated_velocity }} points/sprint</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Team & Stakeholders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
      <!-- Team Information -->
      <Card v-if="project.project_owner || project.product_owner">
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="users" class="h-5 w-5 mr-2" />
            Team Information
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div v-if="project.project_owner">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Project Owner</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ project.project_owner }}</p>
            </div>
            <div v-if="project.product_owner">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Product Owner</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ project.product_owner }}</p>
            </div>
            <div v-if="project.stakeholders && project.stakeholders.length">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Stakeholders</h4>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="stakeholder in project.stakeholders" 
                  :key="stakeholder"
                  class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400"
                >
                  {{ stakeholder }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Milestones & Resources -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="flag" class="h-5 w-5 mr-2" />
            Milestones & Resources
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Milestones -->
            <div v-if="project.milestones && project.milestones.length">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Milestones</h4>
              <div class="space-y-2">
                <div 
                  v-for="(milestone, index) in project.milestones" 
                  :key="index"
                  class="flex items-center gap-2"
                >
                  <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ milestone }}</span>
                </div>
              </div>
            </div>

            <!-- Assigned Resources -->
            <div v-if="project.assigned_resources && project.assigned_resources.length">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Assigned Resources</h4>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="resource in project.assigned_resources" 
                  :key="resource"
                  class="bg-teal-100 text-teal-800 dark:bg-teal-900/20 dark:text-teal-400"
                >
                  {{ resource }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Tracking & Links -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
      <!-- Tracking Information -->
      <Card v-if="project.identified_risks && project.identified_risks.length">
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="alert-triangle" class="h-5 w-5 mr-2" />
            Identified Risks
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-2">
            <div 
              v-for="(risk, index) in project.identified_risks" 
              :key="index"
              class="flex items-start gap-2"
            >
              <div class="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ risk }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- External Links -->
      <Card v-if="project.documentation_url || project.repository_url || project.task_board_url">
        <CardHeader>
          <CardTitle class="flex items-center">
            <Icon name="link" class="h-5 w-5 mr-2" />
            External Links
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div v-if="project.documentation_url">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Documentation</h4>
              <a 
                :href="project.documentation_url" 
                target="_blank" 
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
              >
                {{ project.documentation_url }}
              </a>
            </div>
            <div v-if="project.repository_url">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Repository</h4>
              <a 
                :href="project.repository_url" 
                target="_blank" 
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
              >
                {{ project.repository_url }}
              </a>
            </div>
            <div v-if="project.task_board_url">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Task Board</h4>
              <a 
                :href="project.task_board_url" 
                target="_blank" 
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
              >
                {{ project.task_board_url }}
              </a>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Sprints Section -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Sprints</h2>
        <CreateSprintModal 
          v-if="permissions === 'admin' || permissions === 'team_leader'" 
          :project="project" 
        />
      </div>

      <template v-if="sprints.length">
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
          <CardSprint
            v-for="sprint in sprints"
            :key="sprint.id"
            :sprint="sprint"
            :permissions="permissions"
            :project_id="project.id"
          />
        </div>
      </template>
      
      <template v-else>
        <Card>
          <CardContent class="text-center py-8">
            <Icon name="calendar" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No sprints created</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              Get started by creating your first sprint for this project.
            </p>
            <CreateSprintModal 
              v-if="permissions === 'admin' || permissions === 'team_leader'" 
              :project="project" 
            />
          </CardContent>
        </Card>
      </template>
    </div>

    <!-- Manage Users Modal -->
    <ManageProjectUsersModal
      :is-open="showManageUsersModal"
      :project="project"
      :current-users="project.users || []"
      :all-users="developers"
      @close="showManageUsersModal = false"
    />
  </AppLayout>
</template>
  
