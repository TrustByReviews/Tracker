<template>
  <Dialog :open="open" @close="closeModal">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto bg-white rounded-lg shadow-lg">
      <DialogHeader>
        <DialogTitle class="text-lg font-semibold text-gray-800">
          Edit Sprint: {{ sprint?.name }}
        </DialogTitle>
        <DialogDescription class="text-sm text-gray-600">
          Update sprint information and tracking data
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" @click.stop class="space-y-6">
        <!-- Tabs -->
        <div class="border-b border-gray-200" @click.stop>
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              type="button"
              @click.stop.prevent="activeTab = tab.id"
              @keydown.enter.prevent
              @keydown.space.prevent
              :class="[
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Basic Info Tab -->
        <div v-if="activeTab === 'basic'" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="name" class="text-gray-700">Sprint Name</Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="Sprint 5 - Payment Integration"
                class="w-full border-gray-300 text-black bg-white"
              />
              <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                {{ form.errors.name }}
              </div>
            </div>

            <div>
              <Label for="sprint_type" class="text-gray-700">Sprint Type</Label>
              <Select v-model="form.sprint_type">
                <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                  <SelectValue placeholder="Select sprint type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="regular">Regular Sprint</SelectItem>
                  <SelectItem value="release">Release Sprint</SelectItem>
                  <SelectItem value="hotfix">Hotfix Sprint</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div>
            <Label for="description" class="text-gray-700">Description</Label>
            <Textarea
              id="description"
              v-model="form.description"
              placeholder="Brief description of the sprint objectives..."
              rows="3"
              class="w-full border-gray-300 text-black bg-white"
            />
          </div>

          <div>
            <Label for="sprint_objective" class="text-gray-700">Sprint Objective</Label>
            <Textarea
              id="sprint_objective"
              v-model="form.sprint_objective"
              placeholder="Main goal of this sprint..."
              rows="3"
              class="w-full border-gray-300 text-black bg-white"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="planned_end_date" class="text-gray-700">Planned End Date</Label>
              <Input
                id="planned_end_date"
                v-model="form.planned_end_date"
                type="date"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>

            <div>
              <Label for="acceptance_criteria" class="text-gray-700">Acceptance Criteria</Label>
              <Textarea
                id="acceptance_criteria"
                v-model="form.acceptance_criteria"
                placeholder="Criteria to consider this sprint complete..."
                rows="3"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>
          </div>
        </div>

        <!-- Planning Tab -->
        <div v-if="activeTab === 'planning'" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label>User Stories Included</Label>
              <div class="space-y-2">
                <div v-for="(story, index) in form.user_stories_included" :key="index" class="flex gap-2">
                  <Input v-model="form.user_stories_included[index]" placeholder="User story description" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('user_stories_included', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('user_stories_included')">
                  Add User Story
                </Button>
              </div>
            </div>

            <div>
              <Label>Assigned Tasks</Label>
              <div class="space-y-2">
                <div v-for="(task, index) in form.assigned_tasks" :key="index" class="flex gap-2">
                  <Input v-model="form.assigned_tasks[index]" placeholder="Task description" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('assigned_tasks', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('assigned_tasks')">
                  Add Task
                </Button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="planned_velocity">Planned Velocity</Label>
              <Input
                id="planned_velocity"
                v-model="form.planned_velocity"
                type="number"
                placeholder="0"
                class="w-full"
              />
            </div>

            <div>
              <Label for="actual_velocity">Actual Velocity</Label>
              <Input
                id="actual_velocity"
                v-model="form.actual_velocity"
                type="number"
                placeholder="0"
                class="w-full"
              />
            </div>
          </div>
        </div>

        <!-- Tracking Tab -->
        <div v-if="activeTab === 'tracking'" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label>Blockers</Label>
              <div class="space-y-2">
                <div v-for="(blocker, index) in form.blockers" :key="index" class="flex gap-2">
                  <Input v-model="form.blockers[index]" placeholder="Blocker description" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('blockers', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('blockers')">
                  Add Blocker
                </Button>
              </div>
            </div>

            <div>
              <Label>Risks</Label>
              <div class="space-y-2">
                <div v-for="(risk, index) in form.risks" :key="index" class="flex gap-2">
                  <Input v-model="form.risks[index]" placeholder="Risk description" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('risks', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('risks')">
                  Add Risk
                </Button>
              </div>
            </div>
          </div>

          <div>
            <Label for="blocker_resolution_notes">Blocker Resolution Notes</Label>
            <Textarea
              id="blocker_resolution_notes"
              v-model="form.blocker_resolution_notes"
              placeholder="Notes about how blockers were resolved..."
              rows="3"
              class="w-full"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label for="bugs_found">Bugs Found</Label>
              <Input
                id="bugs_found"
                v-model="form.bugs_found"
                type="number"
                placeholder="0"
                class="w-full"
              />
            </div>

            <div>
              <Label for="bugs_resolved">Bugs Resolved</Label>
              <Input
                id="bugs_resolved"
                v-model="form.bugs_resolved"
                type="number"
                placeholder="0"
                class="w-full"
              />
            </div>

            <div>
              <Label for="progress_percentage">Progress (%)</Label>
              <Input
                id="progress_percentage"
                v-model="form.progress_percentage"
                type="number"
                min="0"
                max="100"
                placeholder="0"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="daily_scrums_held">Daily Scrums Held</Label>
              <Input
                id="daily_scrums_held"
                v-model="form.daily_scrums_held"
                type="number"
                placeholder="0"
                class="w-full"
              />
            </div>

            <div>
              <Label for="daily_scrums_missed">Daily Scrums Missed</Label>
              <Input
                id="daily_scrums_missed"
                v-model="form.daily_scrums_missed"
                type="number"
                placeholder="0"
                class="w-full"
              />
            </div>
          </div>
        </div>

        <!-- Quality Tab -->
        <div v-if="activeTab === 'quality'" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label>Detailed Acceptance Criteria</Label>
              <div class="space-y-2">
                <div v-for="(criteria, index) in form.detailed_acceptance_criteria" :key="index" class="flex gap-2">
                  <Input v-model="form.detailed_acceptance_criteria[index]" placeholder="Acceptance criteria" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('detailed_acceptance_criteria', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('detailed_acceptance_criteria')">
                  Add Criteria
                </Button>
              </div>
            </div>

            <div>
              <Label>Definition of Done</Label>
              <div class="space-y-2">
                <div v-for="(item, index) in form.definition_of_done" :key="index" class="flex gap-2">
                  <Input v-model="form.definition_of_done[index]" placeholder="Definition of done item" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('definition_of_done', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('definition_of_done')">
                  Add Item
                </Button>
              </div>
            </div>
          </div>

          <div>
            <Label>Quality Gates</Label>
            <div class="space-y-2">
              <div v-for="(gate, index) in form.quality_gates" :key="index" class="flex gap-2">
                <Input v-model="form.quality_gates[index]" placeholder="Quality gate description" />
                <Button type="button" variant="outline" size="sm" @click="removeArrayItem('quality_gates', index)">
                  Remove
                </Button>
              </div>
              <Button type="button" variant="outline" size="sm" @click="addArrayItem('quality_gates')">
                Add Quality Gate
              </Button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="code_reviews_completed">Code Reviews Completed</Label>
              <Input
                id="code_reviews_completed"
                v-model="form.code_reviews_completed"
                type="number"
                placeholder="0"
                class="w-full"
              />
            </div>

            <div>
              <Label for="code_reviews_pending">Code Reviews Pending</Label>
              <Input
                id="code_reviews_pending"
                v-model="form.code_reviews_pending"
                type="number"
                placeholder="0"
                class="w-full"
              />
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
          <Button type="button" variant="outline" @click="closeModal">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing">
            <span v-if="form.processing">Updating...</span>
            <span v-else>Update Sprint</span>
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import type { Sprint } from '@/types/Project'

interface Props {
  open: boolean
  sprint?: Sprint
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const activeTab = ref('basic')

const tabs = [
  { id: 'basic', name: 'Basic Info' },
  { id: 'planning', name: 'Planning' },
  { id: 'tracking', name: 'Tracking' },
  { id: 'quality', name: 'Quality' }
]

const form = useForm({
  name: '',
  description: '',
  sprint_type: '',
  planned_end_date: '',
  sprint_objective: '',
  acceptance_criteria: '',
  user_stories_included: [] as string[],
  assigned_tasks: [] as string[],
  planned_velocity: 0,
  actual_velocity: 0,
  blockers: [] as string[],
  risks: [] as string[],
  blocker_resolution_notes: '',
  detailed_acceptance_criteria: [] as string[],
  definition_of_done: [] as string[],
  quality_gates: [] as string[],
  bugs_found: 0,
  bugs_resolved: 0,
  progress_percentage: 0,
  daily_scrums_held: 0,
  daily_scrums_missed: 0,
  code_reviews_completed: 0,
  code_reviews_pending: 0
})

// Helper functions for array inputs
const addArrayItem = (field: string) => {
  if (Array.isArray(form[field])) {
    form[field].push('')
  }
}

const removeArrayItem = (field: string, index: number) => {
  if (Array.isArray(form[field])) {
    form[field].splice(index, 1)
  }
}

// Initialize form with sprint data
const initializeForm = () => {
  if (props.sprint) {
    form.name = props.sprint.name || ''
    form.description = props.sprint.description || ''
    form.sprint_type = props.sprint.sprint_type || ''
    form.planned_end_date = props.sprint.planned_end_date || ''
    form.sprint_objective = props.sprint.sprint_objective || ''
    form.acceptance_criteria = props.sprint.acceptance_criteria || ''
    form.user_stories_included = Array.isArray(props.sprint.user_stories_included) ? [...props.sprint.user_stories_included] : []
    form.assigned_tasks = Array.isArray(props.sprint.assigned_tasks) ? [...props.sprint.assigned_tasks] : []
    form.planned_velocity = Number(props.sprint.planned_velocity) || 0
    form.actual_velocity = Number(props.sprint.actual_velocity) || 0
    form.blockers = Array.isArray(props.sprint.blockers) ? [...props.sprint.blockers] : []
    form.risks = Array.isArray(props.sprint.risks) ? [...props.sprint.risks] : []
    form.blocker_resolution_notes = props.sprint.blocker_resolution_notes || ''
    form.detailed_acceptance_criteria = Array.isArray(props.sprint.detailed_acceptance_criteria) ? [...props.sprint.detailed_acceptance_criteria] : []
    form.definition_of_done = Array.isArray(props.sprint.definition_of_done) ? [...props.sprint.definition_of_done] : []
    form.quality_gates = Array.isArray(props.sprint.quality_gates) ? [...props.sprint.quality_gates] : []
    form.bugs_found = Number(props.sprint.bugs_found) || 0
    form.bugs_resolved = Number(props.sprint.bugs_resolved) || 0
    form.progress_percentage = Number(props.sprint.progress_percentage) || 0
    form.daily_scrums_held = Number(props.sprint.daily_scrums_held) || 0
    form.daily_scrums_missed = Number(props.sprint.daily_scrums_missed) || 0
    form.code_reviews_completed = Number(props.sprint.code_reviews_completed) || 0
    form.code_reviews_pending = Number(props.sprint.code_reviews_pending) || 0
  }
}

const closeModal = () => {
  emit('update:open', false)
  form.reset()
}

const submit = () => {
  if (!props.sprint?.id) return

  form.put(`/sprints/${props.sprint.id}`, {
    onSuccess: () => {
      closeModal()
    },
    onError: (errors) => {
      console.error('Sprint update failed:', errors)
    }
  })
}

// Watch for sprint changes and initialize form
watch(() => props.sprint, initializeForm, { immediate: true })

// Watch for modal open state
watch(() => props.open, (newValue) => {
  if (newValue && props.sprint) {
    initializeForm()
  }
})
</script>
