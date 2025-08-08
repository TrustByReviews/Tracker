<template>
  <Head :title="`${sprint.name} - Sprint Details`" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" size="sm" @click="back" class="flex items-center">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Back
          </Button>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ sprint.name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Sprint Details</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Sprint Stats -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
          <List class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ getSprintStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">To Do</CardTitle>
          <Circle class="h-4 w-4 text-yellow-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-yellow-600">{{ getSprintStats().toDo }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">In Progress</CardTitle>
          <Play class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getSprintStats().inProgress }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Ready for Test</CardTitle>
          <CheckSquare class="h-4 w-4 text-yellow-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-yellow-600">{{ getSprintStats().readyForTest }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Rejected</CardTitle>
          <XCircle class="h-4 w-4 text-red-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-red-600">{{ getSprintStats().rejected }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completed</CardTitle>
          <CheckCircle class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ getSprintStats().completed }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Sprint Goal -->
    <Card class="mb-8">
      <CardHeader>
        <CardTitle class="flex items-center">
          <Target class="h-5 w-5 mr-2" />
          Goal
        </CardTitle>
      </CardHeader>
      <CardContent>
        <p class="text-gray-700 dark:text-gray-300 mb-4">{{ sprint.goal }}</p>
        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
          <Calendar class="h-4 w-4 mr-2" />
          {{ formatDate(sprint.start_date) }} → {{ formatDate(sprint.end_date) }}
        </div>
      </CardContent>
    </Card>

    <!-- Tasks Section -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Tasks</h2>
        <CreateTaskModal
          v-if="permissions === 'team_leader'"
          :sprint="sprint"
          :project_id="project.id"
          :developers="developers"
        />
      </div>

      <template v-if="tasks && tasks.length">
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
          <Card v-for="task in tasks" :key="task.id" class="p-4">
            <CardHeader>
              <CardTitle class="text-lg">{{ task.name }}</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-gray-600 mb-2">{{ task.description }}</p>
              <div class="flex justify-between items-center">
                <Badge :class="getStatusBadgeColor(task.status)">
                  {{ task.status }}
                </Badge>
                <span class="text-sm text-gray-500">{{ task.user?.name || 'Unassigned' }}</span>
              </div>
                             <div v-if="task.qa_status === 'rejected'" class="mt-2 p-2 bg-red-50 rounded">
                 <p class="text-sm text-red-700">
                   <strong>Rejected by QA:</strong> {{ task.qa_rejection_reason }}
                 </p>
               </div>
               <div class="flex justify-end mt-3">
                 <Button 
                   variant="outline" 
                   size="sm" 
                   @click="viewTaskDetails(task.id)"
                   class="text-xs"
                 >
                   View Details
                 </Button>
               </div>
             </CardContent>
           </Card>
        </div>
      </template>
      
      <template v-else>
        <Card>
          <CardContent class="text-center py-8">
            <CheckSquare class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No tasks created</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              Get started by creating your first task for this sprint.
            </p>
          </CardContent>
        </Card>
      </template>
    </div>

    <!-- Bugs Section -->
    <div class="mt-12">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Bugs</h2>
        <CreateBugModal
          v-if="permissions === 'team_leader'"
          :projects="[project]"
          :sprints="[sprint]"
          :developers="developers"
          :current-project="project"
          :current-sprint="sprint"
        />
      </div>

      <template v-if="bugs && bugs.length">
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
          <Card v-for="bug in bugs" :key="bug.id" class="p-4">
            <CardHeader>
              <CardTitle class="text-lg">{{ bug.title }}</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-gray-600 mb-2">{{ bug.description }}</p>
                             <div class="flex justify-between items-center">
                 <Badge :class="getStatusBadgeColor(bug.status)">
                   {{ bug.status }}
                 </Badge>
                 <span class="text-sm text-gray-500">{{ bug.user?.name || 'Unassigned' }}</span>
               </div>
               <div class="flex justify-end mt-3">
                 <Button 
                   variant="outline" 
                   size="sm" 
                   @click="viewBugDetails(bug.id)"
                   class="text-xs"
                 >
                   View Details
                 </Button>
               </div>
             </CardContent>
           </Card>
        </div>
      </template>
      
      <template v-else>
        <Card>
          <CardContent class="text-center py-8">
            <BugIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No bugs reported</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              Create a bug report if you find any issues.
            </p>
          </CardContent>
        </Card>
      </template>
    </div>

    <!-- Debug Panel -->
    <DebugPanel :data="{ sprint, project, tasks, bugs, developers, permissions }" />
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { 
  ArrowLeft,
  List,
  Circle,
  Play,
  CheckSquare,
  XCircle,
  CheckCircle,
  Target,
  Calendar,
  Bug as BugIcon
} from 'lucide-vue-next'
import DebugPanel from '@/components/DebugPanel.vue'
import CreateTaskModal from '@/components/CreateTaskModal.vue'
import CreateBugModal from '@/components/CreateBugModal.vue'

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
  sprint: Sprint;
  project: Project;
  tasks: any[];
  bugs: any[];
  developers: any[];
  permissions: string;
}>();

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getStatusBadgeColor = (status: string) => {
  switch (status) {
    case 'done':
    case 'resolved':
    case 'verified':
    case 'closed':
      return 'bg-green-100 text-green-800';
    case 'in progress':
    case 'assigned':
      return 'bg-blue-100 text-blue-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    case 'ready for test':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const getSprintStats = () => {
  // Tasks stats
  const totalTasks = props.tasks?.length || 0;
  const completedTasks = props.tasks?.filter(task => task.status === 'done').length || 0;
  const inProgressTasks = props.tasks?.filter(task => task.status === 'in progress').length || 0;
  const readyForTestTasks = props.tasks?.filter(task => task.status === 'ready for test').length || 0;
  const rejectedTasks = props.tasks?.filter(task => task.status === 'rejected').length || 0;
  const toDoTasks = props.tasks?.filter(task => task.status === 'to do').length || 0;

  // Bugs stats
  const totalBugs = props.bugs?.length || 0;
  const completedBugs = props.bugs?.filter(bug => ['resolved', 'verified', 'closed'].includes(bug.status)).length || 0;
  const inProgressBugs = props.bugs?.filter(bug => ['assigned', 'in progress'].includes(bug.status)).length || 0;
  const readyForTestBugs = props.bugs?.filter(bug => bug.status === 'ready for test').length || 0;
  const rejectedBugs = props.bugs?.filter(bug => bug.status === 'rejected').length || 0;
  const toBeBugs = props.bugs?.filter(bug => bug.status === 'to do').length || 0;

  return {
    total: totalTasks + totalBugs,
    completed: completedTasks + completedBugs,
    inProgress: inProgressTasks + inProgressBugs,
    readyForTest: readyForTestTasks + readyForTestBugs,
    rejected: rejectedTasks + rejectedBugs,
    toDo: toDoTasks + toBeBugs
  };
};

const back = () => {
  router.get('/team-leader/sprints');
};

const viewTaskDetails = (taskId: string) => {
  router.get(`/tasks/${taskId}`);
};

const viewBugDetails = (bugId: string) => {
  router.get(`/bugs/${bugId}`);
};
</script>
