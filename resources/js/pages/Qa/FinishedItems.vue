<template>
  <AppLayout title="Finished Items">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Finished Items</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Tasks and bugs finished for QA testing</p>
        </div>
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
        <!-- Type Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
          <Select v-model="filters.itemType" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All types" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All types</SelectItem>
              <SelectItem value="task">Tasks only</SelectItem>
              <SelectItem value="bug">Bugs only</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
          <Select v-model="filters.status" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All statuses</SelectItem>
              <SelectItem value="ready_for_test">Ready for Testing</SelectItem>
              <SelectItem value="testing">In Testing</SelectItem>
              <SelectItem value="approved">Approved</SelectItem>
              <SelectItem value="rejected">Rejected</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Priority Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
          <Select v-model="filters.priority" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All priorities" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All priorities</SelectItem>
              <SelectItem value="high">High</SelectItem>
              <SelectItem value="medium">Medium</SelectItem>
              <SelectItem value="low">Low</SelectItem>
              <SelectItem value="critical">Critical</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Project Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project</label>
          <Select v-model="filters.project_id" @update:model-value="applyFilters">
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
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <!-- Developer Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Developer</label>
          <Select v-model="filters.developer_id" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All developers" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All developers</SelectItem>
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
              <SelectItem value="project">Project</SelectItem>
              <SelectItem value="developer">Developer</SelectItem>
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

      <!-- Search -->
      <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search items..."
          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          @input="applyFilters"
        />
      </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Items</CardTitle>
          <Icon name="list" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ getStats().total }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Tasks Ready</CardTitle>
          <Icon name="check-square" class="h-4 w-4 text-green-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ getStats().tasksReady }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Bugs Ready</CardTitle>
          <Icon name="bug" class="h-4 w-4 text-purple-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-purple-600">{{ getStats().bugsReady }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">In Testing</CardTitle>
          <Icon name="play" class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-blue-600">{{ getStats().inTesting }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Tabs to organize -->
    <div class="mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'all'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'all'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            All ({{ filteredItems.length }})
          </button>
          <button
            @click="activeTab = 'tasks'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'tasks'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Tasks ({{ filteredTasks.length }})
          </button>
          <button
            @click="activeTab = 'bugs'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'bugs'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Bugs ({{ filteredBugs.length }})
          </button>
        </nav>
      </div>
    </div>

    <!-- List of Items -->
    <div v-if="displayItems.length > 0" class="space-y-4">
      <div
        v-for="item in displayItems"
        :key="`${item.type}-${item.id}`"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-3">
              <!-- Icon based on type -->
              <div class="flex-shrink-0">
                <Icon 
                  :name="item.type === 'task' ? 'check-square' : 'bug'" 
                  class="h-6 w-6"
                  :class="item.type === 'task' ? 'text-green-600' : 'text-purple-600'"
                />
              </div>
              
              <!-- Title -->
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ item.type === 'task' ? item.name : item.title }}
              </h3>
              
              <!-- Badges -->
              <div class="flex gap-2">
                <span 
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getTypeBadgeClass(item.type)"
                >
                  {{ item.type === 'task' ? 'Task' : 'Bug' }}
                </span>
                
                <span 
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getStatusBadgeClass(item.qa_status)"
                >
                  {{ getStatusLabel(item.qa_status) }}
                </span>
                
                <span 
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getPriorityBadgeClass(item.priority || item.importance)"
                >
                  {{ getPriorityLabel(item.priority || item.importance) }}
                </span>
              </div>
            </div>
            
            <!-- Description -->
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              {{ item.type === 'task' ? item.description : item.description }}
            </p>
            
            <!-- Additional Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500 dark:text-gray-400">
              <div>
                <span class="font-medium">Project:</span>
                {{ item.type === 'task' ? item.project?.name : item.sprint?.project?.name }}
              </div>
              <div>
                <span class="font-medium">Developer:</span>
                {{ item.user?.name || 'Unassigned' }}
              </div>
              <div>
                <span class="font-medium">Finished:</span>
                {{ formatDate(item.type === 'task' ? item.updated_at : item.updated_at) }}
              </div>
            </div>
            
            <!-- Testing Timer -->
            <div v-if="item.qa_status === 'testing' || item.qa_status === 'testing_paused'" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <Icon name="clock" class="h-4 w-4 text-blue-600" />
                  <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Testing Time:</span>
                </div>
                <div class="text-lg font-mono font-bold text-blue-800 dark:text-blue-200">
                  {{ getTestingTime(item) }}
                </div>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="flex flex-col gap-2 ml-4">
            <Button
              @click="viewDetails(item)"
              variant="outline"
              size="sm"
            >
              <Icon name="eye" class="h-4 w-4 mr-2" />
              View Details
            </Button>
            
            <!-- Testing Buttons -->
            <div v-if="item.qa_status === 'ready_for_test'" class="flex flex-col gap-1">
              <Button
                @click="startTesting(item)"
                variant="default"
                size="sm"
                class="bg-blue-600 hover:bg-blue-700"
              >
                <Icon name="play" class="h-4 w-4 mr-2" />
                Start Testing
              </Button>
            </div>
            
            <div v-if="item.qa_status === 'testing'" class="flex flex-col gap-1">
              <Button
                @click="pauseTesting(item)"
                variant="outline"
                size="sm"
                class="border-yellow-500 text-yellow-600 hover:bg-yellow-50 hover:text-yellow-700 dark:hover:bg-yellow-900/20"
              >
                <Icon name="pause" class="h-4 w-4 mr-2" />
                Pause Testing
              </Button>
              
              <Button
                @click="finishTesting(item)"
                variant="default"
                size="sm"
                class="bg-green-600 hover:bg-green-700"
              >
                <Icon name="check" class="h-4 w-4 mr-2" />
                Finish Testing
              </Button>
            </div>
            
            <div v-if="item.qa_status === 'testing_paused'" class="flex flex-col gap-1">
              <Button
                @click="resumeTesting(item)"
                variant="outline"
                size="sm"
                class="border-blue-500 text-blue-600 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20"
              >
                <Icon name="play" class="h-4 w-4 mr-2" />
                Resume Testing
              </Button>
            </div>
            
            <!-- Approval/Rejection Buttons (only after finishing testing) -->
            <div v-if="item.qa_status === 'testing_finished'" class="flex flex-col gap-1">
              <Button
                @click="showApproveModal(item)"
                variant="default"
                size="sm"
                class="bg-green-600 hover:bg-green-700"
              >
                <Icon name="check" class="h-4 w-4 mr-2" />
                Approve
              </Button>
              
              <Button
                @click="showRejectModal(item)"
                variant="destructive"
                size="sm"
              >
                <Icon name="x" class="h-4 w-4 mr-2" />
                Reject
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-8 flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing {{ paginationInfo.from }} to {{ paginationInfo.to }} of {{ paginationInfo.total }} items
        </div>
        
        <div class="flex items-center space-x-2">
          <Button
            @click="previousPage"
            :disabled="!paginationInfo.prev_page_url"
            variant="outline"
            size="sm"
          >
            <Icon name="chevron-left" class="h-4 w-4 mr-1" />
            Previous
          </Button>
          
          <div class="flex items-center space-x-1">
            <span
              v-for="page in paginationInfo.links"
              :key="page.label"
              @click="goToPage(page.url)"
              :class="[
                'px-3 py-1 text-sm rounded cursor-pointer',
                page.active
                  ? 'bg-blue-600 text-white'
                  : page.url
                    ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                    : 'text-gray-400 cursor-not-allowed'
              ]"
            >
              {{ page.label }}
            </span>
          </div>
          
          <Button
            @click="nextPage"
            :disabled="!paginationInfo.next_page_url"
            variant="outline"
            size="sm"
          >
            Next
            <Icon name="chevron-right" class="h-4 w-4 ml-1" />
          </Button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <Icon name="list" class="h-16 w-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
        {{ hasActiveFilters ? 'No items found with current filters' : 'No finished items to show' }}
      </h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ hasActiveFilters 
          ? 'Try adjusting your filters or clearing them to see all items.' 
          : 'Developers must finish tasks and bugs for them to appear here.' 
        }}
      </p>
      <Button 
        v-if="hasActiveFilters" 
        @click="clearFilters" 
        variant="outline"
      >
        <Icon name="x" class="h-4 w-4 mr-2" />
        Clear Filters
      </Button>
    </div>

    <!-- Approval Modal -->
    <Dialog :open="showApproveModalFlag" @update:open="showApproveModalFlag = false">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Approve Item</DialogTitle>
          <DialogDescription>
            Optionally, you can add notes about the approved task. These notes will be visible to the Team Leader.
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Approval Notes (Optional)
            </label>
            <textarea
              v-model="approveNotes"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Add comments about the approved task..."
            ></textarea>
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="showApproveModalFlag = false">
            Cancel
          </Button>
          <Button 
            variant="default" 
            @click="approveItem"
            class="bg-green-600 hover:bg-green-700"
          >
            Approve
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Rejection Modal -->
    <Dialog :open="showRejectModalFlag" @update:open="showRejectModalFlag = false">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Reject Item</DialogTitle>
          <DialogDescription>
            Provide a reason for rejection. This will be sent to the developer.
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Rejection Reason <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="rejectReason"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Describe why this item is rejected... (Required)"
              required
            ></textarea>
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="showRejectModalFlag = false">
            Cancel
          </Button>
          <Button 
            variant="destructive" 
            @click="rejectItem"
            :disabled="!rejectReason.trim()"
          >
            Reject
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import Icon from '@/components/Icon.vue'

interface User {
  id: string
  name: string
  email: string
}

interface Project {
  id: string
  name: string
}

interface Sprint {
  id: string
  name: string
  project: Project
}

interface Task {
  id: string
  name: string
  description: string
  priority: string
  status: string
  qa_status: string
  user: User
  project: Project
  sprint?: Sprint
  created_at: string
  updated_at: string
  qa_testing_started_at?: string
  qa_testing_paused_at?: string
}

interface Bug {
  id: string
  title: string
  description: string
  importance: string
  status: string
  qa_status: string
  user: User
  sprint: Sprint
  created_at: string
  updated_at: string
  qa_testing_started_at?: string
  qa_testing_paused_at?: string
}

type ItemWithType = (Task & { type: 'task' }) | (Bug & { type: 'bug' })

// Helper functions for type safety
const isTask = (item: ItemWithType): item is Task & { type: 'task' } => {
  return item.type === 'task'
}

const isBug = (item: ItemWithType): item is Bug & { type: 'bug' } => {
  return item.type === 'bug'
}

const props = defineProps<{
  finishedTasks: {
    data: Task[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
    prev_page_url: string | null
    next_page_url: string | null
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
  }
  finishedBugs: {
    data: Bug[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
    prev_page_url: string | null
    next_page_url: string | null
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
  }
  projects: Project[]
  developers: User[]
}>()

// Reactive state
const activeTab = ref('all')
const showRejectModalFlag = ref(false)
const showApproveModalFlag = ref(false)
const rejectReason = ref('')
const approveNotes = ref('')
const selectedItem = ref<ItemWithType | null>(null)
const testingTimers = ref<Map<string, { startTime: number; pausedTime: number; isPaused: boolean }>>(new Map())
const timerInterval = ref<number | null>(null)
const timerTick = ref(0) // Reactive variable to force update

// Filters
const filters = ref({
  itemType: '',
  status: '',
  priority: '',
  project_id: '',
  developer_id: '',
  sort_by: 'recent',
  sort_order: 'desc',
  search: ''
})

// Computed properties
const hasActiveFilters = computed(() => {
  return filters.value.itemType || 
         filters.value.status || 
         filters.value.priority || 
         filters.value.project_id || 
         filters.value.developer_id ||
         filters.value.search ||
         filters.value.sort_by !== 'recent' ||
         filters.value.sort_order !== 'desc'
})

const filteredTasks = computed(() => {
  let tasks = props.finishedTasks.data

  if (filters.value.status) {
    tasks = tasks.filter(task => task.qa_status === filters.value.status)
  }
  
  if (filters.value.priority) {
    tasks = tasks.filter(task => task.priority === filters.value.priority)
  }
  
  if (filters.value.project_id) {
    tasks = tasks.filter(task => task.project?.id === filters.value.project_id)
  }
  
  if (filters.value.developer_id) {
    tasks = tasks.filter(task => task.user?.id === filters.value.developer_id)
  }
  
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    tasks = tasks.filter(task => 
      task.name.toLowerCase().includes(search) ||
      task.description.toLowerCase().includes(search) ||
      task.project?.name.toLowerCase().includes(search) ||
      task.user?.name.toLowerCase().includes(search)
    )
  }

  return tasks
})

const filteredBugs = computed(() => {
  let bugs = props.finishedBugs.data

  if (filters.value.status) {
    bugs = bugs.filter(bug => bug.qa_status === filters.value.status)
  }
  
  if (filters.value.priority) {
    bugs = bugs.filter(bug => bug.importance === filters.value.priority)
  }
  
  if (filters.value.project_id) {
    bugs = bugs.filter(bug => bug.sprint?.project?.id === filters.value.project_id)
  }
  
  if (filters.value.developer_id) {
    bugs = bugs.filter(bug => bug.user?.id === filters.value.developer_id)
  }
  
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    bugs = bugs.filter(bug => 
      bug.title.toLowerCase().includes(search) ||
      bug.description.toLowerCase().includes(search) ||
      bug.sprint?.project?.name.toLowerCase().includes(search) ||
      bug.user?.name.toLowerCase().includes(search)
    )
  }

  return bugs
})

const filteredItems = computed(() => {
  if (filters.value.itemType === 'task') {
    return filteredTasks.value.map(task => ({ ...task, type: 'task' }))
  } else if (filters.value.itemType === 'bug') {
    return filteredBugs.value.map(bug => ({ ...bug, type: 'bug' }))
  } else {
    return [
      ...filteredTasks.value.map(task => ({ ...task, type: 'task' })),
      ...filteredBugs.value.map(bug => ({ ...bug, type: 'bug' }))
    ]
  }
})

const displayItems = computed(() => {
  if (activeTab.value === 'tasks') {
    return filteredTasks.value.map(task => ({ ...task, type: 'task' }))
  } else if (activeTab.value === 'bugs') {
    return filteredBugs.value.map(bug => ({ ...bug, type: 'bug' }))
  } else {
    return filteredItems.value
  }
})

const paginationInfo = computed(() => {
  return activeTab.value === 'tasks' ? props.finishedTasks : props.finishedBugs
})

// Pagination functions
const previousPage = () => {
  if (paginationInfo.value.prev_page_url) {
    router.visit(paginationInfo.value.prev_page_url)
  }
}

const nextPage = () => {
  if (paginationInfo.value.next_page_url) {
    router.visit(paginationInfo.value.next_page_url)
  }
}

const goToPage = (url: string | null) => {
  if (url) {
    router.visit(url)
  }
}

// Functions
const getStats = () => {
  const total = props.finishedTasks.total + props.finishedBugs.total
  const tasksReady = props.finishedTasks.data.filter(task => task.qa_status === 'ready_for_test').length
  const bugsReady = props.finishedBugs.data.filter(bug => bug.qa_status === 'ready_for_test').length
  const inTesting = props.finishedTasks.data.filter(task => task.qa_status === 'testing').length + 
                   props.finishedBugs.data.filter(bug => bug.qa_status === 'testing').length
  
  return { total, tasksReady, bugsReady, inTesting }
}

const getTypeBadgeClass = (type: string) => {
  return type === 'task' 
    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
}

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'ready_for_test':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
    case 'testing':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    case 'testing_paused':
      return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
    case 'testing_finished':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
    case 'approved':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    case 'rejected':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
  }
}

const getPriorityBadgeClass = (priority: string) => {
  switch (priority) {
    case 'high':
    case 'critical':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    case 'medium':
      return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
    case 'low':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
  }
}

const getStatusLabel = (status: string) => {
  switch (status) {
    case 'ready_for_test':
      return 'Ready for Testing'
    case 'testing':
      return 'In Testing'
    case 'testing_paused':
      return 'Paused'
    case 'testing_finished':
      return 'Finished'
    case 'approved':
      return 'Approved'
    case 'rejected':
      return 'Rejected'
    default:
      return status
  }
}

const getPriorityLabel = (priority: string) => {
  switch (priority) {
    case 'high':
      return 'High'
    case 'medium':
      return 'Medium'
    case 'low':
      return 'Low'
    case 'critical':
      return 'Critical'
    default:
      return priority
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const applyFilters = () => {
  // Filters are applied automatically via computed properties
}

const clearFilters = () => {
  filters.value = {
    itemType: '',
    status: '',
    priority: '',
    project_id: '',
    developer_id: '',
    sort_by: 'recent',
    sort_order: 'desc',
    search: ''
  }
}

const viewDetails = (item: ItemWithType) => {
  if (item.type === 'task') {
    router.visit(`/tasks/${item.id}`)
  } else {
    router.visit(`/bugs/${item.id}`)
  }
}

const showRejectModal = (item: ItemWithType) => {
  selectedItem.value = item
  showRejectModalFlag.value = true
  rejectReason.value = ''
}

// Testing functions
const startTesting = async (item: ItemWithType) => {
  try {
    const endpoint = item.type === 'task' 
      ? `/qa/tasks/${item.id}/start-testing`
      : `/qa/bugs/${item.id}/start-testing`
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      // Update local state immediately
      item.qa_status = 'testing'
      item.qa_testing_started_at = new Date().toISOString()
      
      // Initialize local timer
      testingTimers.value.set(item.id, {
        startTime: Date.now(),
        pausedTime: Date.now(),
        isPaused: false
      })
    } else {
      const error = await response.json()
      // Show specific message if an active task/bug already exists
      if (error.activeTask) {
        alert(`You already have an active testing task: "${error.activeTask}". You must finish or pause that task before starting another.`)
      } else if (error.activeBug) {
        alert(`You already have an active testing bug: "${error.activeBug}". You must finish or pause that bug before starting another.`)
      } else {
        alert(error.message || 'Error starting testing')
      }
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error starting testing')
  }
}

const pauseTesting = async (item: ItemWithType) => {
  try {
    const endpoint = item.type === 'task' 
      ? `/qa/tasks/${item.id}/pause-testing`
      : `/qa/bugs/${item.id}/pause-testing`
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      // Update local state immediately
      item.qa_status = 'testing_paused'
      item.qa_testing_paused_at = new Date().toISOString()
      
      // Update local timer
      const timer = testingTimers.value.get(item.id)
      if (timer) {
        timer.isPaused = true
        timer.pausedTime = Date.now()
      }
    } else {
      const error = await response.json()
      alert(error.message || 'Error pausing testing')
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error pausing testing')
  }
}

const resumeTesting = async (item: ItemWithType) => {
  try {
    const endpoint = item.type === 'task' 
      ? `/qa/tasks/${item.id}/resume-testing`
      : `/qa/bugs/${item.id}/resume-testing`
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      // Update local state immediately
      item.qa_status = 'testing'
      ;(item as any).qa_testing_paused_at = null
      
      // Update local timer
      const timer = testingTimers.value.get(item.id)
      if (timer) {
        timer.isPaused = false
        // Adjust startTime to continue from where it was paused
        const elapsedBeforePause = timer.pausedTime - timer.startTime
        timer.startTime = Date.now() - elapsedBeforePause
      }
    } else {
      const error = await response.json()
      alert(error.message || 'Error resuming testing')
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error resuming testing')
  }
}

const finishTesting = async (item: ItemWithType) => {
  try {
    const endpoint = item.type === 'task' 
      ? `/qa/tasks/${item.id}/finish-testing`
      : `/qa/bugs/${item.id}/finish-testing`
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      // Update local state immediately
      item.qa_status = 'testing_finished'
      item.qa_testing_finished_at = new Date().toISOString()
      
      // Remove timer as it's finished
      testingTimers.value.delete(item.id)
    } else {
      const error = await response.json()
      alert(error.message || 'Error finishing testing')
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error finishing testing')
  }
}

// Functions for the testing timer
const getTestingTime = (item: ItemWithType) => {
  // Use timerTick to force reactivity
  const tick = timerTick.value
  
  const timer = testingTimers.value.get(item.id)
  if (!timer) return '00:00:00'
  
  let elapsed = 0
  
  if (timer.isPaused) {
    // If paused, show the accumulated time up to the pause moment
    elapsed = Math.max(0, timer.pausedTime - timer.startTime)
  } else {
    // If active, calculate the current time
    elapsed = Math.max(0, Date.now() - timer.startTime)
  }
  
  const hours = Math.floor(elapsed / 3600000)
  const minutes = Math.floor((elapsed % 3600000) / 60000)
  const seconds = Math.floor((elapsed % 60000) / 1000)
  
  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
}

// Function to start the automatic timer
const startTimerInterval = () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
  }
  
  timerInterval.value = setInterval(() => {
    // Increment timerTick to force component update
    timerTick.value++
  }, 1000)
}

// Function to stop the automatic timer
const stopTimerInterval = () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
    timerInterval.value = null
  }
}

// Function to show approval modal
const showApproveModal = (item: ItemWithType) => {
  selectedItem.value = item
  showApproveModalFlag.value = true
  approveNotes.value = ''
}

// Function to approve item with notes
const approveItem = async () => {
  if (!selectedItem.value) return
  
  try {
    const endpoint = selectedItem.value.type === 'task' 
      ? `/qa/tasks/${selectedItem.value.id}/approve`
      : `/qa/bugs/${selectedItem.value.id}/approve`
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        notes: approveNotes.value
      })
    })
    
    if (response.ok) {
      showApproveModalFlag.value = false
      selectedItem.value = null
      approveNotes.value = ''
      router.reload()
    } else {
      const error = await response.json()
      alert(error.message || 'Error approving item')
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error approving item')
  }
}

// Function to reject item with required reason
const rejectItem = async () => {
  if (!selectedItem.value || !rejectReason.value.trim()) return
  
  try {
    const endpoint = selectedItem.value.type === 'task' 
      ? `/qa/tasks/${selectedItem.value.id}/reject`
      : `/qa/bugs/${selectedItem.value.id}/reject`
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        reason: rejectReason.value
      })
    })
    
    if (response.ok) {
      showRejectModalFlag.value = false
      selectedItem.value = null
      rejectReason.value = ''
      router.reload()
    } else {
      const error = await response.json()
      alert(error.message || 'Error rejecting item')
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error rejecting item')
  }
}

// Initialize timers when items are loaded in testing
onMounted(() => {
  // Initialize timers for items already in testing
  const allItems = [...props.finishedTasks.data.map(task => ({ ...task, type: 'task' })), ...props.finishedBugs.data.map(bug => ({ ...bug, type: 'bug' }))]
  
  allItems.forEach(item => {
    if (item.qa_status === 'testing' || item.qa_status === 'testing_paused') {
      // Use real timestamps from the database
      if (item.qa_testing_started_at) {
        const startTime = new Date(item.qa_testing_started_at).getTime()
        const pausedTime = item.qa_testing_paused_at ? new Date(item.qa_testing_paused_at).getTime() : startTime
        
        testingTimers.value.set(item.id, {
          startTime,
          pausedTime,
          isPaused: item.qa_status === 'testing_paused'
        })
      }
    }
  })
  
  // Start the automatic timer
  startTimerInterval()
})

// Clear the interval when the component unmounts
onUnmounted(() => {
  stopTimerInterval()
})
</script> 