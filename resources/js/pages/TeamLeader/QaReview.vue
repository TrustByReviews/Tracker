<template>
  <AppLayout title="QA Review">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          QA Review - Tasks & Bugs Approved by QA
        </h2>
        <div class="flex items-center space-x-4">
          <Badge variant="secondary">
            {{ qaApprovedTasks.length + qaApprovedBugs.length }} items pending review
          </Badge>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Tabs -->
        <div class="mb-8">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
              <button
                @click="activeTab = 'tasks'"
                :class="[
                  activeTab === 'tasks'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Tasks ({{ qaApprovedTasks.length }})
              </button>
              <button
                @click="activeTab = 'bugs'"
                :class="[
                  activeTab === 'bugs'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Bugs ({{ qaApprovedBugs.length }})
              </button>
            </nav>
          </div>
        </div>

        <!-- Tasks Tab -->
        <div v-if="activeTab === 'tasks'" class="space-y-6">
          <div v-if="qaApprovedTasks.length === 0" class="text-center py-12">
            <div class="mx-auto h-12 w-12 text-gray-400">
              <CheckCircle class="h-12 w-12" />
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks pending review</h3>
            <p class="mt-1 text-sm text-gray-500">
              All QA-approved tasks have been reviewed.
            </p>
          </div>

          <div v-else class="grid gap-6">
            <Card
              v-for="task in qaApprovedTasks"
              :key="task.id"
              class="hover:shadow-md transition-shadow"
            >
              <CardContent class="p-6">
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                      <h3 class="text-lg font-medium text-gray-900">{{ task.name }}</h3>
                      <Badge variant="success">QA Approved</Badge>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                      <div>
                        <p class="text-sm font-medium text-gray-500">Project</p>
                        <p class="text-sm text-gray-900">{{ task.project?.name }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Developer</p>
                        <p class="text-sm text-gray-900">{{ task.user?.name }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">QA Reviewer</p>
                        <p class="text-sm text-gray-900">{{ task.qaReviewedBy?.name }}</p>
                      </div>
                    </div>

                    <div class="mb-4">
                      <p class="text-sm font-medium text-gray-500 mb-2">Description</p>
                      <p class="text-sm text-gray-700">{{ task.description }}</p>
                    </div>

                    <div v-if="task.qa_notes" class="mb-4">
                      <p class="text-sm font-medium text-gray-500 mb-2">QA Notes</p>
                      <p class="text-sm text-gray-700 bg-green-50 p-3 rounded-md">
                        {{ task.qa_notes }}
                      </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                      <div>
                        <p class="font-medium text-gray-500">Priority</p>
                        <Badge :variant="getPriorityVariant(task.priority)" size="sm">
                          {{ task.priority }}
                        </Badge>
                      </div>
                      <div>
                        <p class="font-medium text-gray-500">Story Points</p>
                        <p class="text-gray-900">{{ task.story_points }}</p>
                      </div>
                      <div>
                        <p class="font-medium text-gray-500">Estimated Time</p>
                        <p class="text-gray-900">
                          {{ task.estimated_hours }}h {{ task.estimated_minutes }}m
                        </p>
                      </div>
                      <div>
                        <p class="font-medium text-gray-500">Actual Time</p>
                        <p class="text-gray-900">
                          {{ formatTime(task.total_time_seconds) }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="ml-6 flex flex-col space-y-2">
                    <Button @click="approveTask(task)" variant="outline" size="sm">
                      <CheckCircle class="h-4 w-4 mr-2" />
                      Approve
                    </Button>
                    <Button @click="requestChanges(task)" variant="destructive" size="sm">
                      <XCircle class="h-4 w-4 mr-2" />
                      Request Changes
                    </Button>
                    <Button @click="viewTaskDetails(task)" variant="ghost" size="sm">
                      <Eye class="h-4 w-4 mr-2" />
                      View Details
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Bugs Tab -->
        <div v-if="activeTab === 'bugs'" class="space-y-6">
          <div v-if="qaApprovedBugs.length === 0" class="text-center py-12">
            <div class="mx-auto h-12 w-12 text-gray-400">
              <Bug class="h-12 w-12" />
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No bugs pending review</h3>
            <p class="mt-1 text-sm text-gray-500">
              All QA-approved bugs have been reviewed.
            </p>
          </div>

          <div v-else class="grid gap-6">
            <Card
              v-for="bug in qaApprovedBugs"
              :key="bug.id"
              class="hover:shadow-md transition-shadow border-red-200"
            >
              <CardContent class="p-6">
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                      <h3 class="text-lg font-medium text-gray-900">{{ bug.title }}</h3>
                      <Badge variant="success">QA Approved</Badge>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                      <div>
                        <p class="text-sm font-medium text-gray-500">Project</p>
                        <p class="text-sm text-gray-900">{{ bug.project?.name }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Developer</p>
                        <p class="text-sm text-gray-900">{{ bug.user?.name }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">QA Reviewer</p>
                        <p class="text-sm text-gray-900">{{ bug.qaReviewedBy?.name }}</p>
                      </div>
                    </div>

                    <div class="mb-4">
                      <p class="text-sm font-medium text-gray-500 mb-2">Description</p>
                      <p class="text-sm text-gray-700">{{ bug.description }}</p>
                    </div>

                    <div v-if="bug.qa_notes" class="mb-4">
                      <p class="text-sm font-medium text-gray-500 mb-2">QA Notes</p>
                      <p class="text-sm text-gray-700 bg-green-50 p-3 rounded-md">
                        {{ bug.qa_notes }}
                      </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                      <div>
                        <p class="font-medium text-gray-500">Importance</p>
                        <Badge :variant="getImportanceVariant(bug.importance)" size="sm">
                          {{ bug.importance }}
                        </Badge>
                      </div>
                      <div>
                        <p class="font-medium text-gray-500">Status</p>
                        <p class="text-gray-900">{{ bug.status }}</p>
                      </div>
                      <div>
                        <p class="font-medium text-gray-500">Created</p>
                        <p class="text-gray-900">{{ formatDate(bug.created_at) }}</p>
                      </div>
                      <div>
                        <p class="font-medium text-gray-500">QA Approved</p>
                        <p class="text-gray-900">{{ formatDate(bug.qa_completed_at) }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="ml-6 flex flex-col space-y-2">
                    <Button @click="approveBug(bug)" variant="outline" size="sm">
                      <CheckCircle class="h-4 w-4 mr-2" />
                      Approve
                    </Button>
                    <Button @click="requestChanges(bug)" variant="destructive" size="sm">
                      <XCircle class="h-4 w-4 mr-2" />
                      Request Changes
                    </Button>
                    <Button @click="viewBugDetails(bug)" variant="ghost" size="sm">
                      <Eye class="h-4 w-4 mr-2" />
                      View Details
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <Dialog v-model:open="showApprovalModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ approvalModalTitle }}</DialogTitle>
        </DialogHeader>
        <div class="space-y-4">
          <div>
            <Label for="notes">Notes (optional)</Label>
            <Textarea
              id="notes"
              v-model="approvalNotes"
              placeholder="Add any notes about the final approval..."
              rows="3"
            />
          </div>
        </div>
        <DialogFooter>
          <Button @click="confirmApproval" :disabled="isSubmitting">
            {{ isSubmitting ? 'Approving...' : 'Approve' }}
          </Button>
          <Button @click="showApprovalModal = false" variant="outline">
            Cancel
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Changes Requested Modal -->
    <Dialog v-model:open="showChangesModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ changesModalTitle }}</DialogTitle>
        </DialogHeader>
        <div class="space-y-4">
          <div>
            <Label for="changeNotes">Change Request Notes *</Label>
            <Textarea
              id="changeNotes"
              v-model="changeNotes"
              placeholder="Please provide detailed notes about what changes are needed..."
              rows="4"
              required
            />
          </div>
        </div>
        <DialogFooter>
          <Button @click="confirmChangesRequest" :disabled="isSubmitting || !changeNotes">
            {{ isSubmitting ? 'Requesting Changes...' : 'Request Changes' }}
          </Button>
          <Button @click="showChangesModal = false" variant="outline">
            Cancel
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Card from '@/components/ui/card/Card.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import Button from '@/components/ui/button/Button.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue'
import Label from '@/components/ui/label/Label.vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { CheckCircle, XCircle, Eye, Bug } from 'lucide-vue-next'

const props = defineProps({
  qaApprovedTasks: Array,
  qaApprovedBugs: Array,
})

// Reactive data
const activeTab = ref('tasks')
const showApprovalModal = ref(false)
const showChangesModal = ref(false)
const approvalModalTitle = ref('')
const changesModalTitle = ref('')
const approvalNotes = ref('')
const changeNotes = ref('')
const isSubmitting = ref(false)
const currentItem = ref(null)
const currentItemType = ref('') // 'task' or 'bug'

// Helper functions
const getPriorityVariant = (priority) => {
  switch (priority) {
    case 'high': return 'destructive'
    case 'medium': return 'secondary'
    case 'low': return 'outline'
    default: return 'secondary'
  }
}

const getImportanceVariant = (importance) => {
  switch (importance) {
    case 'critical': return 'destructive'
    case 'high': return 'secondary'
    case 'medium': return 'outline'
    case 'low': return 'outline'
    default: return 'secondary'
  }
}

const formatTime = (seconds) => {
  if (!seconds) return '0h 0m'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  return `${hours}h ${minutes}m`
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

// Task approval
const approveTask = (task) => {
  currentItem.value = task
  currentItemType.value = 'task'
  approvalModalTitle.value = `Approve Task: ${task.name}`
  showApprovalModal.value = true
}

// Bug approval
const approveBug = (bug) => {
  currentItem.value = bug
  currentItemType.value = 'bug'
  approvalModalTitle.value = `Approve Bug: ${bug.title}`
  showApprovalModal.value = true
}

// Task changes request
const requestChanges = (item) => {
  currentItem.value = item
  currentItemType.value = item.name ? 'task' : 'bug'
  changesModalTitle.value = `Request Changes: ${item.name || item.title}`
  showChangesModal.value = true
}

// View task details
const viewTaskDetails = (task) => {
  router.visit(`/tasks/${task.id}`)
}

// View bug details
const viewBugDetails = (bug) => {
  router.visit(`/bugs/${bug.id}`)
}

// Confirm approval
const confirmApproval = async () => {
  if (!currentItem.value) return

  isSubmitting.value = true
  try {
    const endpoint = currentItemType.value === 'task' 
      ? `/team-leader/tasks/${currentItem.value.id}/review-qa-approval`
      : `/team-leader/bugs/${currentItem.value.id}/review-qa-approval`

    const response = await router.post(endpoint, {
      action: 'approve',
      notes: approvalNotes.value
    })

    if (response.ok) {
      showApprovalModal.value = false
      approvalNotes.value = ''
      window.location.reload()
    }
  } catch (error) {
    console.error('Error approving item:', error)
  } finally {
    isSubmitting.value = false
  }
}

// Confirm changes request
const confirmChangesRequest = async () => {
  if (!currentItem.value || !changeNotes.value) return

  isSubmitting.value = true
  try {
    const endpoint = currentItemType.value === 'task' 
      ? `/team-leader/tasks/${currentItem.value.id}/review-qa-approval`
      : `/team-leader/bugs/${currentItem.value.id}/review-qa-approval`

    const response = await router.post(endpoint, {
      action: 'request_changes',
      notes: changeNotes.value
    })

    if (response.ok) {
      showChangesModal.value = false
      changeNotes.value = ''
      window.location.reload()
    }
  } catch (error) {
    console.error('Error requesting changes:', error)
  } finally {
    isSubmitting.value = false
  }
}

// Load data on mount
onMounted(async () => {
  try {
    // Load QA approved tasks
    const tasksResponse = await fetch('/team-leader/qa-approved-tasks')
    if (tasksResponse.ok) {
      const data = await tasksResponse.json()
      // Update the props data
      Object.assign(props, { qaApprovedTasks: data.qaApprovedTasks })
    }

    // Load QA approved bugs
    const bugsResponse = await fetch('/team-leader/qa-approved-bugs')
    if (bugsResponse.ok) {
      const data = await bugsResponse.json()
      // Update the props data
      Object.assign(props, { qaApprovedBugs: data.qaApprovedBugs })
    }
  } catch (error) {
    console.error('Error loading QA review data:', error)
  }
})
</script> 