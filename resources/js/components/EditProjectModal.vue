<template>
  <Dialog :open="open" @close="closeModal">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto bg-white rounded-lg shadow-lg">
      <DialogHeader>
        <DialogTitle class="text-lg font-semibold text-gray-800">
          Edit Project: {{ project?.name }}
        </DialogTitle>
        <DialogDescription class="text-sm text-gray-600">
          Update project information and tracking data
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

        <!-- General Information Tab -->
        <div v-if="activeTab === 'general'" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="name" class="text-gray-700">Project Name</Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="Project Name"
                class="w-full border-gray-300 text-black bg-white"
              />
              <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                {{ form.errors.name }}
              </div>
            </div>

            <div>
              <Label for="status" class="text-gray-700">Status</Label>
              <Select v-model="form.status">
                <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="planned">Planned</SelectItem>
                  <SelectItem value="in_progress">In Progress</SelectItem>
                  <SelectItem value="paused">Paused</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div>
            <Label for="description" class="text-gray-700">Description</Label>
            <Textarea
              id="description"
              v-model="form.description"
              placeholder="Project description..."
              rows="3"
              class="w-full border-gray-300 text-black bg-white"
            />
          </div>

          <div>
            <Label for="objectives" class="text-gray-700">Objectives</Label>
            <Textarea
              id="objectives"
              v-model="form.objectives"
              placeholder="Project objectives..."
              rows="3"
              class="w-full border-gray-300 text-black bg-white"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label for="priority" class="text-gray-700">Priority</Label>
              <Select v-model="form.priority">
                <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                  <SelectValue placeholder="Select priority" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="low">Low</SelectItem>
                  <SelectItem value="medium">Medium</SelectItem>
                  <SelectItem value="high">High</SelectItem>
                  <SelectItem value="critical">Critical</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label for="category" class="text-gray-700">Category</Label>
              <Select v-model="form.category">
                <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="web">Web</SelectItem>
                  <SelectItem value="mobile">Mobile</SelectItem>
                  <SelectItem value="backend">Backend</SelectItem>
                  <SelectItem value="iot">IoT</SelectItem>
                  <SelectItem value="ai">AI/ML</SelectItem>
                  <SelectItem value="other">Other</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label for="development_type" class="text-gray-700">Development Type</Label>
              <Select v-model="form.development_type">
                <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                  <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="new">New Development</SelectItem>
                  <SelectItem value="maintenance">Maintenance</SelectItem>
                  <SelectItem value="enhancement">Enhancement</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        <!-- Planning Tab -->
        <div v-if="activeTab === 'planning'" class="space-y-4">
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
              <Label for="methodology" class="text-gray-700">Methodology</Label>
              <Select v-model="form.methodology">
                <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                  <SelectValue placeholder="Select methodology" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="scrum">Scrum</SelectItem>
                  <SelectItem value="kanban">Kanban</SelectItem>
                  <SelectItem value="waterfall">Waterfall</SelectItem>
                  <SelectItem value="hybrid">Hybrid</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div>
            <Label class="text-gray-700">Milestones</Label>
            <div class="space-y-2">
              <div v-for="(milestone, index) in form.milestones" :key="index" class="flex gap-2">
                <Input v-model="form.milestones[index]" placeholder="Milestone description" class="border-gray-300 text-black bg-white" />
                <Button type="button" variant="outline" size="sm" @click="removeArrayItem('milestones', index)">
                  Remove
                </Button>
              </div>
              <Button type="button" variant="outline" size="sm" @click="addArrayItem('milestones')">
                Add Milestone
              </Button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="estimated_velocity" class="text-gray-700">Estimated Velocity</Label>
              <Input
                id="estimated_velocity"
                v-model="form.estimated_velocity"
                type="number"
                placeholder="0"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>

            <div>
              <Label for="current_sprint" class="text-gray-700">Current Sprint</Label>
              <Input
                id="current_sprint"
                v-model="form.current_sprint"
                placeholder="Current sprint name"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>
          </div>
        </div>

        <!-- Technology Tab -->
        <div v-if="activeTab === 'technology'" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label class="text-gray-700">Technologies</Label>
              <div class="space-y-2">
                <div v-for="(tech, index) in form.technologies" :key="index" class="flex gap-2">
                  <Input v-model="form.technologies[index]" placeholder="Technology name" class="border-gray-300 text-black bg-white" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('technologies', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('technologies')">
                  Add Technology
                </Button>
              </div>
            </div>

            <div>
              <Label class="text-gray-700">Programming Languages</Label>
              <div class="space-y-2">
                <div v-for="(lang, index) in form.programming_languages" :key="index" class="flex gap-2">
                  <Input v-model="form.programming_languages[index]" placeholder="Language name" class="border-gray-300 text-black bg-white" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('programming_languages', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('programming_languages')">
                  Add Language
                </Button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label class="text-gray-700">Frameworks</Label>
              <div class="space-y-2">
                <div v-for="(framework, index) in form.frameworks" :key="index" class="flex gap-2">
                  <Input v-model="form.frameworks[index]" placeholder="Framework name" class="border-gray-300 text-black bg-white" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('frameworks', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('frameworks')">
                  Add Framework
                </Button>
              </div>
            </div>

            <div>
              <Label class="text-gray-700">External Integrations</Label>
              <div class="space-y-2">
                <div v-for="(integration, index) in form.external_integrations" :key="index" class="flex gap-2">
                  <Input v-model="form.external_integrations[index]" placeholder="Integration name" class="border-gray-300 text-black bg-white" />
                  <Button type="button" variant="outline" size="sm" @click="removeArrayItem('external_integrations', index)">
                    Remove
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addArrayItem('external_integrations')">
                  Add Integration
                </Button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="database_type" class="text-gray-700">Database Type</Label>
              <Input
                id="database_type"
                v-model="form.database_type"
                placeholder="Database type"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>

            <div>
              <Label for="architecture" class="text-gray-700">Architecture</Label>
              <Input
                id="architecture"
                v-model="form.architecture"
                placeholder="Architecture type"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>
          </div>
        </div>

        <!-- Team Tab -->
        <div v-if="activeTab === 'team'" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="project_owner" class="text-gray-700">Project Owner</Label>
              <Input
                id="project_owner"
                v-model="form.project_owner"
                placeholder="Project owner name"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>

            <div>
              <Label for="product_owner" class="text-gray-700">Product Owner</Label>
              <Input
                id="product_owner"
                v-model="form.product_owner"
                placeholder="Product owner name"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>
          </div>

          <div>
            <Label class="text-gray-700">Stakeholders</Label>
            <div class="space-y-2">
              <div v-for="(stakeholder, index) in form.stakeholders" :key="index" class="flex gap-2">
                <Input v-model="form.stakeholders[index]" placeholder="Stakeholder name" class="border-gray-300 text-black bg-white" />
                <Button type="button" variant="outline" size="sm" @click="removeArrayItem('stakeholders', index)">
                  Remove
                </Button>
              </div>
              <Button type="button" variant="outline" size="sm" @click="addArrayItem('stakeholders')">
                Add Stakeholder
              </Button>
            </div>
          </div>

          <div>
            <Label class="text-gray-700">Assigned Resources</Label>
            <div class="space-y-2">
              <div v-for="(resource, index) in form.assigned_resources" :key="index" class="flex gap-2">
                <Input v-model="form.assigned_resources[index]" placeholder="Resource description" class="border-gray-300 text-black bg-white" />
                <Button type="button" variant="outline" size="sm" @click="removeArrayItem('assigned_resources', index)">
                  Remove
                </Button>
              </div>
              <Button type="button" variant="outline" size="sm" @click="addArrayItem('assigned_resources')">
                Add Resource
              </Button>
            </div>
          </div>
        </div>

        <!-- Budget & Resources Tab -->
        <div v-if="activeTab === 'budget'" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="estimated_budget" class="text-gray-700">Estimated Budget</Label>
              <Input
                id="estimated_budget"
                v-model="form.estimated_budget"
                type="number"
                step="0.01"
                placeholder="0.00"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>

            <div>
              <Label for="used_budget" class="text-gray-700">Used Budget</Label>
              <Input
                id="used_budget"
                v-model="form.used_budget"
                type="number"
                step="0.01"
                placeholder="0.00"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>
          </div>

          <div>
            <Label for="progress_percentage" class="text-gray-700">Progress (%)</Label>
            <Input
              id="progress_percentage"
              v-model="form.progress_percentage"
              type="number"
              min="0"
              max="100"
              placeholder="0"
              class="w-full border-gray-300 text-black bg-white"
            />
          </div>
        </div>

        <!-- Tracking Tab -->
        <div v-if="activeTab === 'tracking'" class="space-y-4">
          <div>
            <Label class="text-gray-700">Identified Risks</Label>
            <div class="space-y-2">
              <div v-for="(risk, index) in form.identified_risks" :key="index" class="flex gap-2">
                <Input v-model="form.identified_risks[index]" placeholder="Risk description" class="border-gray-300 text-black bg-white" />
                <Button type="button" variant="outline" size="sm" @click="removeArrayItem('identified_risks', index)">
                  Remove
                </Button>
              </div>
              <Button type="button" variant="outline" size="sm" @click="addArrayItem('identified_risks')">
                Add Risk
              </Button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label for="open_issues" class="text-gray-700">Open Issues</Label>
              <Input
                id="open_issues"
                v-model="form.open_issues"
                type="number"
                placeholder="0"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label for="documentation_url" class="text-gray-700">Documentation URL</Label>
              <Input
                id="documentation_url"
                v-model="form.documentation_url"
                type="url"
                placeholder="https://docs.example.com"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>

            <div>
              <Label for="repository_url" class="text-gray-700">Repository URL</Label>
              <Input
                id="repository_url"
                v-model="form.repository_url"
                type="url"
                placeholder="https://github.com/example/repo"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>

            <div>
              <Label for="task_board_url" class="text-gray-700">Task Board URL</Label>
              <Input
                id="task_board_url"
                v-model="form.task_board_url"
                type="url"
                placeholder="https://jira.example.com"
                class="w-full border-gray-300 text-black bg-white"
              />
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
          <Button type="button" variant="outline" @click="closeModal">
            Cancel
          </Button>
          <Button type="submit" class="bg-blue-500" :disabled="form.processing">
            <span v-if="form.processing">Updating...</span>
            <span v-else>Update Project</span>
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import type { Project } from '@/types/Project'

interface Props {
  open: boolean
  project?: Project
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const activeTab = ref('general')

const tabs = [
  { id: 'general', name: 'General Information' },
  { id: 'planning', name: 'Planning' },
  { id: 'technology', name: 'Technology' },
  { id: 'team', name: 'Team' },
  { id: 'budget', name: 'Budget & Resources' },
  { id: 'tracking', name: 'Tracking' }
]

const form = useForm({
  name: '',
  description: '',
  objectives: '',
  status: '',
  priority: '',
  category: '',
  development_type: '',
  planned_end_date: '',
  methodology: '',
  milestones: [] as string[],
  estimated_velocity: 0,
  current_sprint: '',
  technologies: [] as string[],
  programming_languages: [] as string[],
  frameworks: [] as string[],
  database_type: '',
  architecture: '',
  external_integrations: [] as string[],
  project_owner: '',
  product_owner: '',
  stakeholders: [] as string[],
  assigned_resources: [] as string[],
  estimated_budget: 0,
  used_budget: 0,
  progress_percentage: 0,
  identified_risks: [] as string[],
  open_issues: 0,
  documentation_url: '',
  repository_url: '',
  task_board_url: ''
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

// Initialize form with project data
const initializeForm = () => {
  if (props.project) {
    form.name = props.project.name || ''
    form.description = props.project.description || ''
    form.objectives = props.project.objectives || ''
    form.status = props.project.status || ''
    form.priority = props.project.priority || ''
    form.category = props.project.category || ''
    form.development_type = props.project.development_type || ''
    form.planned_end_date = props.project.planned_end_date || ''
    form.methodology = props.project.methodology || ''
    form.milestones = Array.isArray(props.project.milestones) ? [...props.project.milestones] : []
    form.estimated_velocity = Number(props.project.estimated_velocity) || 0
    form.current_sprint = props.project.current_sprint || ''
    form.technologies = Array.isArray(props.project.technologies) ? [...props.project.technologies] : []
    form.programming_languages = Array.isArray(props.project.programming_languages) ? [...props.project.programming_languages] : []
    form.frameworks = Array.isArray(props.project.frameworks) ? [...props.project.frameworks] : []
    form.database_type = props.project.database_type || ''
    form.architecture = props.project.architecture || ''
    form.external_integrations = Array.isArray(props.project.external_integrations) ? [...props.project.external_integrations] : []
    form.project_owner = props.project.project_owner || ''
    form.product_owner = props.project.product_owner || ''
    form.stakeholders = Array.isArray(props.project.stakeholders) ? [...props.project.stakeholders] : []
    form.assigned_resources = Array.isArray(props.project.assigned_resources) ? [...props.project.assigned_resources] : []
    form.estimated_budget = Number(props.project.estimated_budget) || 0
    form.used_budget = Number(props.project.used_budget) || 0
    form.progress_percentage = Number(props.project.progress_percentage) || 0
    form.identified_risks = Array.isArray(props.project.identified_risks) ? [...props.project.identified_risks] : []
    form.open_issues = Number(props.project.open_issues) || 0
    form.documentation_url = props.project.documentation_url || ''
    form.repository_url = props.project.repository_url || ''
    form.task_board_url = props.project.task_board_url || ''
  }
}

const closeModal = () => {
  emit('update:open', false)
  form.reset()
}

const submit = () => {
  if (!props.project?.id) return

  form.put(`/projects/${props.project.id}`, {
    onSuccess: () => {
      closeModal()
    },
    onError: (errors) => {
      console.error('Project update failed:', errors)
    }
  })
}

// Watch for project changes and initialize form
watch(() => props.project, initializeForm, { immediate: true })

// Watch for modal open state
watch(() => props.open, (newValue) => {
  if (newValue && props.project) {
    initializeForm()
  }
})
</script>
