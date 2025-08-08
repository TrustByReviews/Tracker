<template>
  <AppLayout title="Finished Bugs">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Finished Bugs for Testing
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Statistics -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-blue-900">Total Bugs</h3>
                <p class="text-2xl font-bold text-blue-600">{{ finishedBugs.length }}</p>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-green-900">Ready for Testing</h3>
                <p class="text-2xl font-bold text-green-600">
                  {{ finishedBugs.filter(bug => bug.qa_status === 'ready_for_test').length }}
                </p>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-yellow-900">In Testing</h3>
                <p class="text-2xl font-bold text-yellow-600">
                  {{ finishedBugs.filter(bug => bug.qa_status === 'testing').length }}
                </p>
              </div>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-wrap gap-4">
              <select
                v-model="statusFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">All statuses</option>
                <option value="ready_for_test">Ready for Testing</option>
                <option value="testing">In Testing</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>

              <select
                v-model="importanceFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">All importance levels</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>

              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search bugs..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>

            <!-- Bug list -->
            <div v-if="filteredBugs.length === 0" class="text-center py-8">
              <p class="text-gray-500">No finished bugs to display</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="bug in filteredBugs"
                :key="bug.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                      <h3 class="text-lg font-semibold text-gray-900">{{ bug.title }}</h3>
                      <span
                        class="px-2 py-1 text-xs font-medium rounded-full"
                        :class="{
                          'bg-blue-100 text-blue-800': bug.qa_status === 'ready_for_test',
                          'bg-yellow-100 text-yellow-800': bug.qa_status === 'testing',
                          'bg-green-100 text-green-800': bug.qa_status === 'approved',
                          'bg-red-100 text-red-800': bug.qa_status === 'rejected'
                        }"
                      >
                        {{ getStatusText(bug.qa_status) }}
                      </span>
                      <span
                        class="px-2 py-1 text-xs font-medium rounded-full"
                        :class="{
                          'bg-red-100 text-red-800': bug.importance === 'critical',
                          'bg-orange-100 text-orange-800': bug.importance === 'high',
                          'bg-yellow-100 text-yellow-800': bug.importance === 'medium',
                          'bg-green-100 text-green-800': bug.importance === 'low'
                        }"
                      >
                        {{ bug.importance }}
                      </span>
                    </div>

                    <p class="text-gray-600 mb-3">{{ bug.description }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500">
                      <div>
                        <span class="font-medium">Project:</span> {{ bug.sprint?.project?.name }}
                      </div>
                      <div>
                        <span class="font-medium">Developer:</span> {{ bug.user?.name }}
                      </div>
                      <div>
                        <span class="font-medium">Sprint:</span> {{ bug.sprint?.name }}
                      </div>
                    </div>

                    <!-- Testing timer -->
                    <div v-if="bug.qa_status === 'testing'" class="mt-4 p-3 bg-yellow-50 rounded-lg">
                      <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                          <div class="text-sm">
                            <span class="font-medium">Testing time:</span>
                            <span class="ml-2 font-mono">{{ formatTime(bug.testing_time || 0) }}</span>
                          </div>
                          <div class="flex space-x-2">
                            <button
                              v-if="!bug.qa_testing_paused_at"
                              @click="pauseTesting(bug)"
                              class="px-3 py-1 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600"
                            >
                              Pause
                            </button>
                            <button
                              v-else
                              @click="resumeTesting(bug)"
                              class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600"
                            >
                              Resume
                            </button>
                            <button
                              @click="finishTesting(bug)"
                              class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600"
                            >
                              Finish Testing
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 flex space-x-2">
                      <button
                        v-if="bug.qa_status === 'ready_for_test'"
                        @click="startTesting(bug)"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                      >
                        Start Testing
                      </button>
                      
                      <button
                        v-if="bug.qa_status === 'testing'"
                        @click="approveBug(bug)"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                      >
                        Approve
                      </button>
                      
                      <button
                        v-if="bug.qa_status === 'testing'"
                        @click="openRejectModal(bug)"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                      >
                        Reject
                      </button>
                      
                      <Link
                        :href="`/bugs/${bug.id}`"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
                      >
                        View Details
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject modal -->
    <div
      v-if="showRejectModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="closeRejectModal"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.stop>
        <h3 class="text-lg font-semibold mb-4">Reject Bug</h3>
        <textarea
          v-model="rejectReason"
          placeholder="Rejection reason..."
          class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
          rows="4"
        ></textarea>
        <div class="mt-4 flex space-x-2">
          <button
            @click="confirmReject"
            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
          >
            Confirm Rejection
          </button>
          <button
            @click="closeRejectModal"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Bug {
  id: string
  title: string
  description: string
  importance: string
  qa_status: string
  qa_assigned_to: string | null
  qa_testing_started_at: string | null
  qa_testing_paused_at: string | null
  qa_testing_finished_at: string | null
  testing_time: number
  sprint: any
  user: any
}

interface Props {
  finishedBugs: Bug[]
}

const props = defineProps<Props>()

const statusFilter = ref('')
const importanceFilter = ref('')
const searchQuery = ref('')
const showRejectModal = ref(false)
const rejectReason = ref('')
const selectedBug = ref<Bug | null>(null)

// Filter bugs
const filteredBugs = computed(() => {
  let bugs = props.finishedBugs

  if (statusFilter.value) {
    bugs = bugs.filter(bug => bug.qa_status === statusFilter.value)
  }

  if (importanceFilter.value) {
    bugs = bugs.filter(bug => bug.importance === importanceFilter.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    bugs = bugs.filter(bug => 
      bug.title.toLowerCase().includes(query) ||
      bug.description.toLowerCase().includes(query) ||
      bug.sprint?.project?.name.toLowerCase().includes(query) ||
      bug.user?.name.toLowerCase().includes(query)
    )
  }

  return bugs
})

// Get status text
const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    'ready_for_test': 'Ready for Testing',
    'testing': 'In Testing',
    'approved': 'Approved',
    'rejected': 'Rejected'
  }
  return statusMap[status] || status
}

// Format time
const formatTime = (seconds: number) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

// Testing actions
const startTesting = async (bug: Bug) => {
  try {
    const response = await fetch(`/qa/bugs/${bug.id}/start-testing`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      router.reload()
    }
  } catch (error) {
    console.error('Error starting testing:', error)
  }
}

const pauseTesting = async (bug: Bug) => {
  try {
    const response = await fetch(`/qa/bugs/${bug.id}/pause-testing`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      router.reload()
    }
  } catch (error) {
    console.error('Error pausing testing:', error)
  }
}

const resumeTesting = async (bug: Bug) => {
  try {
    const response = await fetch(`/qa/bugs/${bug.id}/resume-testing`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      router.reload()
    }
  } catch (error) {
    console.error('Error resuming testing:', error)
  }
}

const finishTesting = async (bug: Bug) => {
  try {
    const response = await fetch(`/qa/bugs/${bug.id}/finish-testing`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      router.reload()
    }
  } catch (error) {
    console.error('Error finishing testing:', error)
  }
}

const approveBug = async (bug: Bug) => {
  try {
    const response = await fetch(`/qa/bugs/${bug.id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      router.reload()
    }
  } catch (error) {
    console.error('Error approving bug:', error)
  }
}

const openRejectModal = (bug: Bug) => {
  selectedBug.value = bug
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  rejectReason.value = ''
  selectedBug.value = null
}

const confirmReject = async () => {
  if (!selectedBug.value || !rejectReason.value.trim()) return

  try {
    const response = await fetch(`/qa/bugs/${selectedBug.value.id}/reject`, {
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
      closeRejectModal()
      router.reload()
    }
  } catch (error) {
    console.error('Error rejecting bug:', error)
  }
}
</script> 