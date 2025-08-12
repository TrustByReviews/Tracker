<template>
  <div>
    <Button 
      @click="open = true" 
      class="bg-green-600 hover:bg-green-700 text-white"
      :disabled="!canFinishProject"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      Finish Project
    </Button>

    <Dialog :open="open" @update:open="open = $event">
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto bg-white rounded-lg shadow-lg">
        <DialogTitle class="text-xl font-semibold mb-4 text-gray-800">
          Finish Project: {{ project?.name }}
        </DialogTitle>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Project Status Check -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Project Status Check
            </h3>
            
            <div class="space-y-3">
              <div v-for="(check, index) in projectChecks" :key="index" class="flex items-center">
                <div :class="[
                  'w-5 h-5 rounded-full mr-3 flex items-center justify-center',
                  check.status === 'success' ? 'bg-green-500' : 
                  check.status === 'warning' ? 'bg-yellow-500' : 'bg-red-500'
                ]">
                  <svg v-if="check.status === 'success'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                  <svg v-else-if="check.status === 'warning'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                  </svg>
                  <svg v-else class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <span class="text-sm" :class="{
                  'text-green-700': check.status === 'success',
                  'text-yellow-700': check.status === 'warning',
                  'text-red-700': check.status === 'error'
                }">
                  {{ check.message }}
                </span>
              </div>
            </div>

            <div v-if="!canFinishProject" class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
              <p class="text-sm text-red-700">
                <strong>Cannot finish project:</strong> Some requirements are not met. Please address the issues above before proceeding.
              </p>
            </div>
          </div>

          <!-- Finish Type Selection -->
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Finish Type</h3>
            
            <div class="space-y-3">
              <label class="flex items-center">
                <input 
                  type="radio" 
                  v-model="form.finish_type" 
                  value="normal" 
                  class="mr-2"
                  :disabled="!canFinishProject"
                >
                <span class="text-gray-700">Normal Finish (All requirements met)</span>
              </label>
              
              <label class="flex items-center">
                <input 
                  type="radio" 
                  v-model="form.finish_type" 
                  value="early" 
                  class="mr-2"
                >
                <span class="text-gray-700">Early Termination (Project cancellation)</span>
              </label>
            </div>
          </div>

          <!-- Early Termination Section -->
          <div v-if="form.finish_type === 'early'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4">Early Termination Details</h3>
            
            <div class="space-y-4">
              <div>
                <Label for="termination_reason" class="text-gray-700 font-medium">Reason for Early Termination <span class="text-red-500">*</span></Label>
                <Select v-model="form.termination_reason">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select reason" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="business_priorities">Business Priorities Change</SelectItem>
                    <SelectItem value="budget_issues">Budget Issues</SelectItem>
                    <SelectItem value="technical_problems">Technical Problems</SelectItem>
                    <SelectItem value="scope_transfer">Scope Transferred to Another Project</SelectItem>
                    <SelectItem value="client_decision">Client Decision to Stop</SelectItem>
                    <SelectItem value="other">Other</SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.termination_reason" />
              </div>

              <div v-if="form.termination_reason === 'other'">
                <Label for="custom_reason" class="text-gray-700 font-medium">Custom Reason <span class="text-red-500">*</span></Label>
                <Textarea 
                  id="custom_reason" 
                  v-model="form.custom_reason" 
                  placeholder="Please specify the reason for early termination..."
                  :rows="3"
                  class="w-full border-gray-300 text-black bg-white"
                />
                <InputError :message="form.errors.custom_reason" />
              </div>
            </div>
          </div>

          <!-- Project Summary Section -->
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Project Summary</h3>
            
            <div class="space-y-4">
              <div>
                <Label for="achievements" class="text-gray-700 font-medium">Achievements & Accomplishments</Label>
                <Textarea 
                  id="achievements" 
                  v-model="form.achievements" 
                  placeholder="List the main achievements and accomplishments of this project..."
                  :rows="3"
                  class="w-full border-gray-300 text-black bg-white"
                />
              </div>

              <div>
                <Label for="difficulties" class="text-gray-700 font-medium">Challenges & Difficulties</Label>
                <Textarea 
                  id="difficulties" 
                  v-model="form.difficulties" 
                  placeholder="Describe the main challenges and difficulties encountered..."
                  :rows="3"
                  class="w-full border-gray-300 text-black bg-white"
                />
              </div>

              <div>
                <Label for="lessons_learned" class="text-gray-700 font-medium">Lessons Learned</Label>
                <Textarea 
                  id="lessons_learned" 
                  v-model="form.lessons_learned" 
                  placeholder="What lessons were learned from this project?"
                  :rows="3"
                  class="w-full border-gray-300 text-black bg-white"
                />
              </div>
            </div>
          </div>

          <!-- Final Documentation -->
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Final Documentation</h3>
            
            <div class="space-y-4">
              <div>
                <Label for="final_documentation" class="text-gray-700 font-medium">Final Documentation Notes</Label>
                <Textarea 
                  id="final_documentation" 
                  v-model="form.final_documentation" 
                  placeholder="Notes about final documentation, deliverables, etc..."
                  :rows="3"
                  class="w-full border-gray-300 text-black bg-white"
                />
              </div>

              <div>
                <Label class="text-gray-700 font-medium">Final Deliverables</Label>
                <div 
                  @drop="handleDrop"
                  @dragover="handleDragOver"
                  @dragleave="handleDragLeave"
                  :class="[
                    'border-2 border-dashed rounded-lg p-6 text-center transition-colors',
                    dragOver ? 'border-blue-400 bg-blue-50' : 'border-gray-300 hover:border-gray-400 bg-white'
                  ]"
                >
                  <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                  </svg>
                  <p class="text-sm text-gray-600 mb-2">
                    Drag final deliverables here or 
                    <label for="file-upload" class="text-blue-600 hover:text-blue-500 cursor-pointer">
                      select files
                    </label>
                  </p>
                  <input 
                    id="file-upload" 
                    type="file" 
                    multiple 
                    @change="handleFileUpload" 
                    class="hidden"
                  />
                  <p class="text-xs text-gray-500">Upload final documentation, user manuals, technical guides, etc.</p>
                </div>

                <!-- File List -->
                <div v-if="uploadedFiles.length > 0" class="mt-4">
                  <h4 class="text-sm font-medium text-gray-700 mb-2">Selected files:</h4>
                  <div class="space-y-2">
                    <div 
                      v-for="(file, index) in uploadedFiles" 
                      :key="index"
                      class="flex items-center justify-between p-2 bg-gray-50 rounded"
                    >
                      <span class="text-sm text-gray-700">{{ file.name }}</span>
                      <button 
                        @click="removeFile(index)" 
                        type="button"
                        class="text-red-500 hover:text-red-700"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Confirmation Warning -->
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-red-800 mb-2 flex items-center">
              <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
              Important Warning
            </h3>
            <p class="text-sm text-red-700">
              <strong>This action cannot be undone.</strong> Once you finish the project, it will be marked as completed and key fields will be locked for editing. 
              {{ form.finish_type === 'early' ? 'For early termination, active sprints and tasks will be marked as cancelled.' : '' }}
            </p>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <Button 
              type="button" 
              variant="outline" 
              @click="closeModal"
              :disabled="form.processing"
            >
              Cancel
            </Button>
            <Button 
              type="submit" 
              :disabled="form.processing || !canFinishProject"
              class="bg-green-600 hover:bg-green-700 text-white"
            >
              <span v-if="form.processing">Finishing Project...</span>
              <span v-else>Finish Project</span>
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import InputError from './InputError.vue'
import type { Project, Sprint, Task, Bug } from '@/types'

interface Props {
  project?: Project
  sprints?: Sprint[]
  tasks?: Task[]
  bugs?: Bug[]
  userRole?: string
}

const props = defineProps<Props>()



// Modal state
const open = ref(false)
const uploadedFiles = ref<File[]>([])
const dragOver = ref(false)

// Form for finishing project
const form = useForm({
  finish_type: 'normal',
  termination_reason: '',
  custom_reason: '',
  achievements: '',
  difficulties: '',
  lessons_learned: '',
  final_documentation: '',
  attachments: [] as any[],
})

// Project validation checks
const projectChecks = computed(() => {
  if (!props.project || !props.sprints || !props.tasks || !props.bugs) {
    return []
  }

  const checks = []

  // Check if all sprints are finished
  const unfinishedSprints = props.sprints.filter(sprint => sprint.status !== 'completed')
  checks.push({
    status: unfinishedSprints.length === 0 ? 'success' : 'error',
    message: `Sprints: ${props.sprints.length - unfinishedSprints.length}/${props.sprints.length} completed`
  })

  // Check if all tasks are done
  const undoneTasks = props.tasks.filter(task => task.status !== 'done')
  checks.push({
    status: undoneTasks.length === 0 ? 'success' : 'warning',
    message: `Tasks: ${props.tasks.length - undoneTasks.length}/${props.tasks.length} completed`
  })

  // Check critical bugs
  const criticalBugs = props.bugs.filter(bug => bug.priority === 'critical' && bug.status !== 'resolved')
  checks.push({
    status: criticalBugs.length === 0 ? 'success' : 'error',
    message: `Critical Bugs: ${criticalBugs.length} unresolved`
  })

  // Check documentation (placeholder - would need backend validation)
  checks.push({
    status: 'warning',
    message: 'Documentation: Manual review required'
  })

  // Check deliverables (placeholder - would need backend validation)
  checks.push({
    status: 'warning',
    message: 'Deliverables: Manual review required'
  })

  return checks
})

// Can finish project (admin only and all critical checks pass)
const canFinishProject = computed(() => {
  if (props.userRole !== 'admin') return false
  
  const criticalChecks = projectChecks.value.filter(check => check.status === 'error')
  return criticalChecks.length === 0
})

// File handling
const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    const files = Array.from(target.files)
    uploadedFiles.value.push(...files)
    form.attachments = uploadedFiles.value
  }
}

const removeFile = (index: number) => {
  uploadedFiles.value.splice(index, 1)
  form.attachments = uploadedFiles.value
}

const handleDragOver = (event: DragEvent) => {
  event.preventDefault()
  dragOver.value = true
}

const handleDragLeave = (event: DragEvent) => {
  event.preventDefault()
  dragOver.value = false
}

const handleDrop = (event: DragEvent) => {
  event.preventDefault()
  dragOver.value = false
  
  if (event.dataTransfer?.files) {
    const files = Array.from(event.dataTransfer.files)
    uploadedFiles.value.push(...files)
    form.attachments = uploadedFiles.value
  }
}

// Form submission
const submit = () => {
  if (!props.project?.id) return

  // Validate early termination
  if (form.finish_type === 'early' && !form.termination_reason) {
    alert('Please select a reason for early termination')
    return
  }

  if (form.finish_type === 'early' && form.termination_reason === 'other' && !form.custom_reason.trim()) {
    alert('Please specify the custom reason for early termination')
    return
  }

  // Create FormData for file uploads
  const formData = new FormData()
  
  // Add form fields
  Object.keys(form.data()).forEach(key => {
    if (key === 'attachments') {
      uploadedFiles.value.forEach(file => {
        formData.append('attachments[]', file)
      })
    } else {
      const value = form.data()[key as keyof ReturnType<typeof form.data>]
      if (value !== undefined && value !== '') {
        formData.append(key, value as any)
      }
    }
  })

  // Add project ID
  formData.append('project_id', props.project.id)

  // Submit to backend
  form.post(`/projects/${props.project.id}/finish`, {
    onSuccess: () => {
      form.reset()
      uploadedFiles.value = []
      open.value = false
      // Reload the page to reflect changes
      window.location.reload()
    },
    onError: (errors) => {
      console.error('Project finish errors:', errors)
    }
  })
}

const closeModal = () => {
  if (form.processing) return
  
  const hasData = form.achievements || form.difficulties || form.lessons_learned || uploadedFiles.value.length > 0
  
  if (hasData) {
    if (confirm('Are you sure you want to close? Any unsaved changes will be lost.')) {
      open.value = false
      form.reset()
      uploadedFiles.value = []
    }
  } else {
    open.value = false
    form.reset()
    uploadedFiles.value = []
  }
}

// Watch for modal open state
watch(open, (newValue) => {
  if (newValue) {
    // Reset form when opening
    form.reset()
    uploadedFiles.value = []
  }
})
</script>
