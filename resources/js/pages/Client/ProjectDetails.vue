<template>
     <AppLayout :title="project ? project.name : 'Project Details'">
     <template #header>
       <h2 class="font-semibold text-xl text-gray-800 leading-tight">
         {{ project ? project.name : 'Project Details' }}
       </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div v-if="loading" class="flex justify-center items-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="error" class="text-red-600 text-center py-4">
          {{ error }}
        </div>

        <div v-else-if="project" class="space-y-6">
          <!-- Información general del proyecto -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <h3 class="text-2xl font-semibold text-gray-900 mb-2">{{ project.name }}</h3>
                  <p class="text-gray-600">{{ project.description }}</p>
                </div>
                <span
                  :class="{
                    'bg-green-100 text-green-800': project.status === 'active',
                    'bg-yellow-100 text-yellow-800': project.status === 'paused',
                    'bg-red-100 text-red-800': project.status === 'completed'
                  }"
                  class="px-3 py-1 text-sm font-medium rounded-full"
                >
                  {{ getStatusLabel(project.status) }}
                </span>
              </div>

                             <!-- Project Progress -->
               <div class="mb-6">
                 <div class="flex justify-between text-sm text-gray-600 mb-2">
                   <span>General Progress</span>
                   <span>{{ project.progress }}%</span>
                 </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                    :style="{ width: project.progress + '%' }"
                  ></div>
                </div>
              </div>

                             <!-- Statistics -->
               <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                 <div class="text-center p-4 bg-gray-50 rounded-lg">
                   <p class="text-2xl font-bold text-gray-900">{{ project.statistics.total_tasks }}</p>
                   <p class="text-sm text-gray-600">Total Tasks</p>
                 </div>
                 <div class="text-center p-4 bg-green-50 rounded-lg">
                   <p class="text-2xl font-bold text-green-900">{{ project.statistics.completed_tasks }}</p>
                   <p class="text-sm text-green-600">Completed</p>
                 </div>
                 <div class="text-center p-4 bg-blue-50 rounded-lg">
                   <p class="text-2xl font-bold text-blue-900">{{ project.statistics.in_progress_tasks }}</p>
                   <p class="text-sm text-blue-600">In Progress</p>
                 </div>
                 <div class="text-center p-4 bg-yellow-50 rounded-lg">
                   <p class="text-2xl font-bold text-yellow-900">{{ project.statistics.pending_tasks }}</p>
                   <p class="text-sm text-yellow-600">Pending</p>
                 </div>
               </div>
            </div>
          </div>

          <!-- Sprints -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Sprints</h3>
              <div v-if="project.sprints && project.sprints.length > 0" class="space-y-4">
                <div
                  v-for="sprint in project.sprints"
                  :key="sprint.id"
                  class="border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex justify-between items-start mb-3">
                    <div>
                      <h4 class="text-lg font-medium text-gray-900">{{ sprint.name }}</h4>
                      <p class="text-sm text-gray-600">
                        {{ formatDate(sprint.start_date) }} - {{ formatDate(sprint.end_date) }}
                      </p>
                    </div>
                                         <span
                       :class="{
                         'bg-green-100 text-green-800': sprint.is_current,
                         'bg-gray-100 text-gray-800': !sprint.is_current
                       }"
                       class="px-2 py-1 text-xs font-medium rounded-full"
                     >
                       {{ sprint.is_current ? 'Current' : 'Completed' }}
                     </span>
                  </div>
                  
                                     <div class="mb-3">
                     <div class="flex justify-between text-sm text-gray-600 mb-1">
                       <span>Progress</span>
                       <span>{{ sprint.progress }}%</span>
                     </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: sprint.progress + '%' }"
                      ></div>
                    </div>
                  </div>

                  <div class="grid grid-cols-3 gap-4 text-sm">
                                         <div class="text-center">
                       <p class="font-semibold text-gray-900">{{ sprint.total_tasks }}</p>
                       <p class="text-gray-600">Total</p>
                     </div>
                     <div class="text-center">
                       <p class="font-semibold text-green-600">{{ sprint.completed_tasks }}</p>
                       <p class="text-gray-600">Completed</p>
                     </div>
                     <div class="text-center">
                       <p class="font-semibold text-blue-600">{{ sprint.total_tasks - sprint.completed_tasks }}</p>
                       <p class="text-gray-600">Pending</p>
                     </div>
                  </div>
                </div>
              </div>
                             <div v-else class="text-center py-8 text-gray-500">
                 <p>No sprints defined for this project</p>
               </div>
            </div>
          </div>

                                           <!-- Statistics by Role -->
            <div v-if="project.role_statistics && Object.keys(project.role_statistics).length > 0" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics by Role</h3>
               <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div v-if="project.role_statistics.developers" class="bg-blue-50 rounded-lg p-4">
                     <h4 class="text-lg font-semibold text-blue-900 mb-3">Developers</h4>
                     <div class="space-y-2">
                       <div class="flex justify-between">
                         <span class="text-sm text-blue-700">Members:</span>
                         <span class="font-semibold">{{ project.role_statistics.developers.count }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-blue-700">Total tasks:</span>
                         <span class="font-semibold">{{ project.role_statistics.developers.total_tasks }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-blue-700">Completed:</span>
                         <span class="font-semibold text-green-600">{{ project.role_statistics.developers.completed_tasks }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-blue-700">Completion rate:</span>
                         <span class="font-semibold">{{ project.role_statistics.developers.completion_rate }}%</span>
                       </div>
                     </div>
                   </div>

                                    <div v-if="project.role_statistics.qa" class="bg-green-50 rounded-lg p-4">
                     <h4 class="text-lg font-semibold text-green-900 mb-3">QA Testers</h4>
                     <div class="space-y-2">
                       <div class="flex justify-between">
                         <span class="text-sm text-green-700">Members:</span>
                         <span class="font-semibold">{{ project.role_statistics.qa.count }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-green-700">Total tasks:</span>
                         <span class="font-semibold">{{ project.role_statistics.qa.total_tasks }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-green-700">Completed:</span>
                         <span class="font-semibold text-green-600">{{ project.role_statistics.qa.completed_tasks }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-green-700">Completion rate:</span>
                         <span class="font-semibold">{{ project.role_statistics.qa.completion_rate }}%</span>
                       </div>
                     </div>
                   </div>

                                    <div v-if="project.role_statistics.team_leaders" class="bg-purple-50 rounded-lg p-4">
                     <h4 class="text-lg font-semibold text-purple-900 mb-3">Team Leaders</h4>
                     <div class="space-y-2">
                       <div class="flex justify-between">
                         <span class="text-sm text-purple-700">Members:</span>
                         <span class="font-semibold">{{ project.role_statistics.team_leaders.count }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-purple-700">Total tasks:</span>
                         <span class="font-semibold">{{ project.role_statistics.team_leaders.total_tasks }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-purple-700">Completed:</span>
                         <span class="font-semibold text-green-600">{{ project.role_statistics.team_leaders.completed_tasks }}</span>
                       </div>
                       <div class="flex justify-between">
                         <span class="text-sm text-purple-700">Completion rate:</span>
                         <span class="font-semibold">{{ project.role_statistics.team_leaders.completion_rate }}%</span>
                       </div>
                     </div>
                   </div>
               </div>
             </div>
           </div>

                       <!-- Project Team -->
            <div v-if="project.team_members && project.team_members.length > 0" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Team</h3>
               <div class="space-y-6">
                 <div
                   v-for="member in project.team_members"
                   :key="member.id"
                   class="border border-gray-200 rounded-lg p-4"
                 >
                   <div class="flex justify-between items-start mb-4">
                     <div class="flex-1">
                       <h4 class="text-lg font-medium text-gray-900">{{ member.name }}</h4>
                       <p class="text-sm text-gray-600">{{ member.email }}</p>
                       <div class="flex flex-wrap gap-1 mt-2">
                         <span
                           v-for="role in member.roles"
                           :key="role"
                           :class="{
                             'px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full': role === 'developer',
                             'px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full': role === 'qa',
                             'px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full': role === 'team_leader'
                           }"
                         >
                           {{ getRoleLabel(role) }}
                         </span>
                       </div>
                     </div>
                                           <div class="text-right">
                        <div class="text-2xl font-bold text-gray-900">{{ member.statistics.completion_rate }}%</div>
                        <div class="text-sm text-gray-600">Completion Rate</div>
                      </div>
                   </div>

                                       <!-- Member Statistics -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                      <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-xl font-bold text-gray-900">{{ member.statistics.total_tasks }}</p>
                        <p class="text-xs text-gray-600">Total Tasks</p>
                      </div>
                      <div class="text-center p-3 bg-green-50 rounded-lg">
                        <p class="text-xl font-bold text-green-900">{{ member.statistics.completed_tasks }}</p>
                        <p class="text-xs text-green-600">Completed</p>
                      </div>
                      <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <p class="text-xl font-bold text-blue-900">{{ member.statistics.in_progress_tasks }}</p>
                        <p class="text-xs text-blue-600">In Progress</p>
                      </div>
                    </div>

                                       <!-- Recent Member Tasks -->
                    <div v-if="member.recent_tasks && member.recent_tasks.length > 0">
                      <h5 class="text-sm font-medium text-gray-900 mb-2">Recent Tasks</h5>
                     <div class="space-y-2">
                       <div
                         v-for="task in member.recent_tasks"
                         :key="task.id"
                         class="flex justify-between items-center p-2 bg-gray-50 rounded text-xs"
                       >
                         <span class="text-gray-700 truncate flex-1">{{ task.name }}</span>
                         <div class="flex items-center space-x-1">
                           <span
                             :class="{
                               'bg-yellow-100 text-yellow-800': task.status === 'to do',
                               'bg-blue-100 text-blue-800': task.status === 'in progress',
                               'bg-green-100 text-green-800': task.status === 'done'
                             }"
                             class="px-2 py-1 rounded-full"
                           >
                             {{ getTaskStatusLabel(task.status) }}
                           </span>
                           <span
                             :class="{
                               'bg-red-100 text-red-800': task.priority === 'high',
                               'bg-yellow-100 text-yellow-800': task.priority === 'medium',
                               'bg-green-100 text-green-800': task.priority === 'low'
                             }"
                             class="px-2 py-1 rounded-full"
                           >
                             {{ getPriorityLabel(task.priority) }}
                           </span>
                         </div>
                       </div>
                     </div>
                   </div>
                 </div>
               </div>
             </div>
           </div>

                       <!-- Recent Tasks -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Project Tasks</h3>
               <div v-if="project.recent_tasks && project.recent_tasks.length > 0" class="space-y-3">
                 <div
                   v-for="task in project.recent_tasks"
                   :key="task.id"
                   class="flex justify-between items-center p-3 border border-gray-200 rounded-lg"
                 >
                   <div class="flex-1">
                     <h4 class="font-medium text-gray-900">{{ task.name }}</h4>
                     <p class="text-sm text-gray-600">
                       Assigned to: {{ task.assigned_to }} | Sprint: {{ task.sprint }}
                     </p>
                   </div>
                   <div class="flex items-center space-x-2">
                     <span
                       :class="{
                         'bg-yellow-100 text-yellow-800': task.status === 'to do',
                         'bg-blue-100 text-blue-800': task.status === 'in progress',
                         'bg-green-100 text-green-800': task.status === 'done'
                       }"
                       class="px-2 py-1 text-xs font-medium rounded-full"
                     >
                       {{ getTaskStatusLabel(task.status) }}
                     </span>
                     <span
                       :class="{
                         'bg-red-100 text-red-800': task.priority === 'high',
                         'bg-yellow-100 text-yellow-800': task.priority === 'medium',
                         'bg-green-100 text-green-800': task.priority === 'low'
                       }"
                       class="px-2 py-1 text-xs font-medium rounded-full"
                     >
                       {{ getPriorityLabel(task.priority) }}
                     </span>
                   </div>
                 </div>
               </div>
               <div v-else class="text-center py-8 text-gray-500">
                 <p>No recent tasks to display</p>
               </div>
             </div>
           </div>

                     <!-- Back Button -->
           <div class="flex justify-start">
             <Link
               :href="route('client.projects')"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
             >
               Back to Projects
             </Link>
           </div>
        </div>

                 <div v-else class="text-center py-12">
           <p class="text-gray-500">Project not found</p>
         </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  projectId: {
    type: String,
    required: true
  },
  project: {
    type: Object,
    default: null
  }
})

const loading = ref(false)
const error = ref(null)
const project = ref(props.project)

// Debug: Log de los datos recibidos
console.log('ProjectDetails - Props recibidos:', props)
console.log('ProjectDetails - Project data:', project.value)
if (project.value) {
  console.log('ProjectDetails - Role statistics:', project.value.role_statistics)
  console.log('ProjectDetails - Team members:', project.value.team_members)
}

const getStatusLabel = (status) => {
  const labels = {
    'active': 'Active',
    'paused': 'Paused',
    'completed': 'Completed'
  }
  return labels[status] || status
}

const getTaskStatusLabel = (status) => {
  const labels = {
    'to do': 'Pending',
    'in progress': 'In Progress',
    'done': 'Completed'
  }
  return labels[status] || status
}

const getPriorityLabel = (priority) => {
  const labels = {
    'high': 'High',
    'medium': 'Medium',
    'low': 'Low'
  }
  return labels[priority] || priority
}

const getRoleLabel = (role) => {
  const labels = {
    'developer': 'Developer',
    'qa': 'QA Tester',
    'team_leader': 'Team Leader'
  }
  return labels[role] || role
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Los datos ya vienen como props desde el servidor
</script>
