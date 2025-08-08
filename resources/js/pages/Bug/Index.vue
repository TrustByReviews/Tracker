<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Bugs Management</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Track and manage bugs across projects and sprints</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <Bug class="w-4 h-4 mr-2" />
            Create Bug
          </button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filters & Search</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Project Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project</label>
                <Select v-model="filters.project_id" @change="applyFilters">
                  <option value="">All Projects</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </Select>
              </div>

              <!-- Sprint Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sprint</label>
                <Select v-model="filters.sprint_id" @change="applyFilters">
                  <option value="">All Sprints</option>
                  <option v-for="sprint in sprints" :key="sprint.id" :value="sprint.id">
                    {{ sprint.name }} ({{ sprint.project?.name }})
                  </option>
                </Select>
              </div>

              <!-- Status Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <Select v-model="filters.status" @change="applyFilters">
                  <option value="">All Statuses</option>
                  <option value="new">New</option>
                  <option value="assigned">Assigned</option>
                  <option value="in progress">In Progress</option>
                  <option value="resolved">Resolved</option>
                  <option value="verified">Verified</option>
                  <option value="closed">Closed</option>
                  <option value="reopened">Reopened</option>
                </Select>
              </div>

              <!-- Importance Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Importance</label>
                <Select v-model="filters.importance" @change="applyFilters">
                  <option value="">All Importance Levels</option>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="critical">Critical</option>
                </Select>
              </div>

              <!-- Bug Type Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bug Type</label>
                <Select v-model="filters.bug_type" @change="applyFilters">
                  <option value="">All Types</option>
                  <option value="frontend">Frontend</option>
                  <option value="backend">Backend</option>
                  <option value="database">Database</option>
                  <option value="api">API</option>
                  <option value="ui_ux">UI/UX</option>
                  <option value="performance">Performance</option>
                  <option value="security">Security</option>
                  <option value="other">Other</option>
                </Select>
              </div>

              <!-- Assigned User Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assigned To</label>
                <Select v-model="filters.assigned_user_id" @change="applyFilters">
                  <option value="">All Users</option>
                  <option value="unassigned">Unassigned</option>
                  <option v-for="developer in developers" :key="developer.id" :value="developer.id">
                    {{ developer.name }}
                  </option>
                </Select>
              </div>

              <!-- Search -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <Input
                  v-model="filters.search"
                  placeholder="Search bugs..."
                  @input="applyFilters"
                />
              </div>

              <!-- Sort By -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort By</label>
                <Select v-model="filters.sort_by" @change="applyFilters">
                  <option value="recent">Most Recent</option>
                  <option value="importance">Importance</option>
                  <option value="status">Status</option>
                  <option value="priority_score">Priority Score</option>
                  <option value="estimated_hours">Estimated Hours</option>
                  <option value="actual_hours">Actual Hours</option>
                  <option value="completion_percentage">Completion %</option>
                  <option value="assigned_user">Assigned User</option>
                  <option value="project">Project</option>
                  <option value="sprint">Sprint</option>
                </Select>
              </div>

              <!-- Sort Order -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order</label>
                <Select v-model="filters.sort_order" @change="applyFilters">
                  <option value="desc">Descending</option>
                  <option value="asc">Ascending</option>
                </Select>
              </div>

              <!-- QA Status Filter - Developer Only -->
              <div v-if="permissions === 'developer'">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">QA Status</label>
                <Select v-model="filters.qa_status" @change="applyFilters">
                  <option value="">All QA Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </Select>
              </div>

              <!-- Team Leader Status Filter - Developer Only -->
              <div v-if="permissions === 'developer'">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Team Leader Status</label>
                <Select v-model="filters.team_leader_status" @change="applyFilters">
                  <option value="">All TL Statuses</option>
                  <option value="pending">Pending Review</option>
                  <option value="approved">Approved</option>
                  <option value="changes_requested">Changes Requested</option>
                </Select>
              </div>
            </div>

            <div class="flex justify-between items-center mt-4">
              <div class="flex space-x-2">
                <button
                  @click="applyFilters"
                  class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Apply Filters
                </button>
                <button
                  @click="clearFilters"
                  class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Clear Filters
                </button>
              </div>
              <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ bugs.length }} bugs found
              </div>
            </div>
          </div>
        </div>

        <!-- Bugs Organized by Status -->
        <div class="space-y-8">
          <!-- Active Bugs (New, Assigned, In Progress) -->
          <div v-if="activeBugs.length > 0">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Active Bugs ({{ activeBugs.length }})
              </h2>
              <div class="flex space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  New: {{ newBugs.length }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                  Assigned: {{ assignedBugs.length }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  In Progress: {{ inProgressBugs.length }}
                </span>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <BugCard
                v-for="bug in activeBugs"
                :key="bug.id"
                :bug="bug"
                @self-assign="handleSelfAssign"
                @start-work="handleStartWork"
                @pause-work="handlePauseWork"
                @resume-work="handleResumeWork"
                @finish-work="handleFinishWork"
                @view-details="viewBug"
              />
            </div>
          </div>

          <!-- Rejected by QA Bugs - Developer Only -->
          <div v-if="permissions === 'developer' && getRejectedByQaBugs().length > 0">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Rejected by QA ({{ getRejectedByQaBugs().length }})
              </h2>
              <div class="flex space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  Needs Fixes
                </span>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <BugCard
                v-for="bug in getRejectedByQaBugs()"
                :key="bug.id"
                :bug="bug"
                @self-assign="handleSelfAssign"
                @start-work="handleStartWork"
                @pause-work="handlePauseWork"
                @resume-work="handleResumeWork"
                @finish-work="handleFinishWork"
                @view-details="viewBug"
              />
            </div>
          </div>

          <!-- Changes Requested by Team Leader - Developer Only -->
          <div v-if="permissions === 'developer' && getTeamLeaderChangesRequestedBugs().length > 0">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Changes Requested by Team Leader ({{ getTeamLeaderChangesRequestedBugs().length }})
              </h2>
              <div class="flex space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                  Changes Required
                </span>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <BugCard
                v-for="bug in getTeamLeaderChangesRequestedBugs()"
                :key="bug.id"
                :bug="bug"
                @self-assign="handleSelfAssign"
                @start-work="handleStartWork"
                @pause-work="handlePauseWork"
                @resume-work="handleResumeWork"
                @finish-work="handleFinishWork"
                @view-details="viewBug"
              />
            </div>
          </div>

          <!-- Completed Bugs (Resolved, Verified, Closed) -->
          <div v-if="completedBugs.length > 0">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Completed Bugs ({{ completedBugs.length }})
              </h2>
              <div class="flex space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Resolved: {{ resolvedBugs.length }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                  Verified: {{ verifiedBugs.length }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  Closed: {{ closedBugs.length }}
                </span>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <BugCard
                v-for="bug in completedBugs"
                :key="bug.id"
                :bug="bug"
                @self-assign="handleSelfAssign"
                @start-work="handleStartWork"
                @pause-work="handlePauseWork"
                @resume-work="handleResumeWork"
                @finish-work="handleFinishWork"
                @view-details="viewBug"
              />
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="bugs.length === 0" class="text-center py-12">
            <Bug class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No bugs found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ hasActiveFilters ? 'Try adjusting your filters.' : 'Get started by creating a new bug.' }}
            </p>
            <div class="mt-6">
              <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <Bug class="w-4 h-4 mr-2" />
                Create Bug
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Bug Modal -->
    <BugCreateModal
      v-if="showCreateModal"
      :projects="projects"
      :sprints="sprints"
      :developers="developers"
      @close="showCreateModal = false"
      @created="onBugCreated"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Bug, Folder, Calendar, Clock } from 'lucide-vue-next'
import Select from '@/components/Select.vue'
import { Input } from '@/components/ui/input'
import BugCreateModal from '@/components/BugCreateModal.vue'
import BugCard from '@/components/BugCard.vue'

const props = defineProps({
  bugs: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: String,
    default: 'developer'
  },
  projects: {
    type: Array,
    default: () => []
  },
  sprints: {
    type: Array,
    default: () => []
  },
  developers: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const showCreateModal = ref(false)

const filters = ref({
  project_id: '',
  sprint_id: '',
  status: '',
  importance: '',
  bug_type: '',
  assigned_user_id: '',
  search: '',
  sort_by: 'recent',
  sort_order: 'desc',
  qa_status: '',
  team_leader_status: '',
  ...props.filters
})

// Computed properties for organized bugs
const activeBugs = computed(() => {
  return props.bugs.filter(bug => 
    ['new', 'assigned', 'in progress'].includes(bug.status)
  )
})

const completedBugs = computed(() => {
  return props.bugs.filter(bug => 
    ['resolved', 'verified', 'closed'].includes(bug.status)
  )
})

const newBugs = computed(() => {
  return props.bugs.filter(bug => bug.status === 'new')
})

const assignedBugs = computed(() => {
  return props.bugs.filter(bug => bug.status === 'assigned')
})

const inProgressBugs = computed(() => {
  return props.bugs.filter(bug => bug.status === 'in progress')
})

const resolvedBugs = computed(() => {
  return props.bugs.filter(bug => bug.status === 'resolved')
})

const verifiedBugs = computed(() => {
  return props.bugs.filter(bug => bug.status === 'verified')
})

const closedBugs = computed(() => {
  return props.bugs.filter(bug => bug.status === 'closed')
})

// QA and Team Leader filtering functions
const getRejectedByQaBugs = () => {
  const bugs = props.bugs.filter(bug => bug.qa_status === 'rejected');
  return bugs;
}

const getTeamLeaderChangesRequestedBugs = () => {
  const bugs = props.bugs.filter(bug => bug.team_leader_requested_changes === true);
  return bugs;
}

const getActiveBugsIncludingRejected = () => {
  const bugs = props.bugs.filter(bug =>
    bug.status === 'new' ||
    bug.status === 'assigned' ||
    bug.status === 'in progress' ||
    bug.qa_status === 'rejected' ||
    bug.team_leader_requested_changes === true
  );
  return bugs;
}

const hasActiveFilters = computed(() => {
  return filters.value.project_id ||
         filters.value.sprint_id ||
         filters.value.status ||
         filters.value.importance ||
         filters.value.bug_type ||
         filters.value.assigned_user_id ||
         filters.value.search ||
         filters.value.qa_status ||
         filters.value.team_leader_status ||
         filters.value.sort_by !== 'recent' ||
         filters.value.sort_order !== 'desc'
})

const applyFilters = () => {
  router.get('/bugs', filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  filters.value = {
    project_id: '',
    sprint_id: '',
    status: '',
    importance: '',
    bug_type: '',
    assigned_user_id: '',
    search: '',
    sort_by: 'recent',
    sort_order: 'desc',
    qa_status: '',
    team_leader_status: ''
  }
  applyFilters()
}

const viewBug = (bugId) => {
  router.get(`/bugs/${bugId}`)
}

const onBugCreated = () => {
  showCreateModal.value = false
  router.reload()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatTime = (seconds) => {
  if (!seconds) return '0h 0m'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  return `${hours}h ${minutes}m`
}

// Time tracking handlers
const handleSelfAssign = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/self-assign`)
    router.reload()
  } catch (error) {
    console.error('Error self-assigning bug:', error)
  }
}

const handleStartWork = async (bugId) => {
  try {
    console.log('Starting work on bug:', bugId)
    const response = await router.post(`/bugs/${bugId}/start-work`, {}, {
      onError: (errors) => {
        console.error('Error starting work:', errors)
        alert('Error starting work: ' + (errors.message || 'Unknown error'))
      },
      onSuccess: () => {
        console.log('Work started successfully')
        router.reload()
      }
    })
  } catch (error) {
    console.error('Error starting work on bug:', error)
    alert('Error starting work: ' + error.message)
  }
}

const handlePauseWork = async (bugId) => {
  try {
    console.log('Pausing work on bug:', bugId)
    const response = await router.post(`/bugs/${bugId}/pause-work`, {}, {
      onError: (errors) => {
        console.error('Error pausing work:', errors)
        alert('Error pausing work: ' + (errors.message || 'Unknown error'))
      },
      onSuccess: () => {
        console.log('Work paused successfully')
        router.reload()
      }
    })
  } catch (error) {
    console.error('Error pausing work on bug:', error)
    alert('Error pausing work: ' + error.message)
  }
}

const handleResumeWork = async (bugId) => {
  try {
    console.log('Resuming work on bug:', bugId)
    const response = await router.post(`/bugs/${bugId}/resume-work`, {}, {
      onError: (errors) => {
        console.error('Error resuming work:', errors)
        alert('Error resuming work: ' + (errors.message || 'Unknown error'))
      },
      onSuccess: () => {
        console.log('Work resumed successfully')
        router.reload()
      }
    })
  } catch (error) {
    console.error('Error resuming work on bug:', error)
    alert('Error resuming work: ' + error.message)
  }
}

const handleFinishWork = async (bugId) => {
  try {
    console.log('Finishing work on bug:', bugId)
    const response = await router.post(`/bugs/${bugId}/finish-work`, {}, {
      onError: (errors) => {
        console.error('Error finishing work:', errors)
        alert('Error finishing work: ' + (errors.message || 'Unknown error'))
      },
      onSuccess: () => {
        console.log('Work finished successfully')
        router.reload()
      }
    })
  } catch (error) {
    console.error('Error finishing work on bug:', error)
    alert('Error finishing work: ' + error.message)
  }
}

const getStatusColor = (status) => {
  const colors = {
    'new': 'bg-blue-100 text-blue-800',
    'assigned': 'bg-yellow-100 text-yellow-800',
    'in progress': 'bg-orange-100 text-orange-800',
    'resolved': 'bg-green-100 text-green-800',
    'verified': 'bg-purple-100 text-purple-800',
    'closed': 'bg-gray-100 text-gray-800',
    'reopened': 'bg-red-100 text-red-800'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const getImportanceColor = (importance) => {
  const colors = {
    'low': 'bg-green-100 text-green-800',
    'medium': 'bg-yellow-100 text-yellow-800',
    'high': 'bg-orange-100 text-orange-800',
    'critical': 'bg-red-100 text-red-800'
  }
  return colors[importance] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  // Initialize filters from props
  filters.value = { ...filters.value, ...props.filters }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 