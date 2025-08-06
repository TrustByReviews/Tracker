<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800">
      <div class="mt-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Create New Bug</h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <X class="w-6 h-6" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Bug Title *
              </label>
              <Input
                v-model="form.title"
                placeholder="Enter bug title..."
                required
              />
            </div>

            <!-- Project & Sprint -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Project *
              </label>
              <Select v-model="form.project_id" @change="onProjectChange" required>
                <option value="">Select Project</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </Select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Sprint
              </label>
              <Select v-model="form.sprint_id">
                <option value="">Select Sprint</option>
                <option v-for="sprint in filteredSprints" :key="sprint.id" :value="sprint.id">
                  {{ sprint.name }}
                </option>
              </Select>
            </div>

            <!-- Importance & Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Importance *
              </label>
              <Select v-model="form.importance" required>
                <option value="">Select Importance</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </Select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Bug Type *
              </label>
              <Select v-model="form.bug_type" required>
                <option value="">Select Type</option>
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

            <!-- Environment & Browser -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Environment
              </label>
              <Select v-model="form.environment">
                <option value="">Select Environment</option>
                <option value="development">Development</option>
                <option value="staging">Staging</option>
                <option value="production">Production</option>
              </Select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Browser Info
              </label>
              <Input
                v-model="form.browser_info"
                placeholder="e.g., Chrome 120.0.0.0, Firefox 121.0"
              />
            </div>

            <!-- OS Info & Reproducibility -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                OS Info
              </label>
              <Input
                v-model="form.os_info"
                placeholder="e.g., Windows 11, macOS 14.0, Ubuntu 22.04"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Reproducibility
              </label>
              <Select v-model="form.reproducibility">
                <option value="always">Always</option>
                <option value="sometimes">Sometimes</option>
                <option value="rarely">Rarely</option>
                <option value="unable">Unable to Reproduce</option>
              </Select>
            </div>

            <!-- Severity & Estimated Time -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Severity
              </label>
              <Select v-model="form.severity">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </Select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Estimated Time (hours)
              </label>
              <Input
                v-model="form.estimated_hours"
                type="number"
                min="0"
                step="0.5"
                placeholder="0"
              />
            </div>

            <!-- Assigned User -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Assign To
              </label>
              <Select v-model="form.user_id">
                <option value="">Unassigned</option>
                <option v-for="developer in developers" :key="developer.id" :value="developer.id">
                  {{ developer.name }}
                </option>
              </Select>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Description *
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              placeholder="Describe the bug..."
              required
            ></textarea>
          </div>

          <!-- Long Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Detailed Description
            </label>
            <textarea
              v-model="form.long_description"
              rows="5"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              placeholder="Provide detailed information about the bug..."
            ></textarea>
          </div>

          <!-- Steps to Reproduce -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Steps to Reproduce
            </label>
            <textarea
              v-model="form.steps_to_reproduce"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              placeholder="1. Go to...&#10;2. Click on...&#10;3. Observe..."
            ></textarea>
          </div>

          <!-- Expected vs Actual Behavior -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Expected Behavior
              </label>
              <textarea
                v-model="form.expected_behavior"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="What should happen..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Actual Behavior
              </label>
              <textarea
                v-model="form.actual_behavior"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="What actually happens..."
              ></textarea>
            </div>
          </div>

          <!-- Tags -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tags
            </label>
            <Input
              v-model="form.tags"
              placeholder="Enter tags separated by commas (e.g., ui, critical, frontend)"
            />
          </div>

          <!-- File Attachments -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Attachments
            </label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
              <Upload class="mx-auto h-12 w-12 text-gray-400" />
              <div class="mt-4">
                <label for="file-upload" class="cursor-pointer">
                  <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
                    <span class="font-medium text-blue-600 hover:text-blue-500">
                      Upload files
                    </span>
                    or drag and drop
                  </span>
                  <p class="mt-1 text-xs text-gray-500">
                    PNG, JPG, GIF, PDF, DOC up to 10MB each
                  </p>
                </label>
                <input
                  id="file-upload"
                  ref="fileInput"
                  type="file"
                  multiple
                  accept="image/*,.pdf,.doc,.docx,.txt"
                  class="sr-only"
                  @change="handleFileUpload"
                />
              </div>
            </div>

            <!-- File List -->
            <div v-if="selectedFiles.length > 0" class="mt-4">
              <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selected Files:</h4>
              <div class="space-y-2">
                <div
                  v-for="(file, index) in selectedFiles"
                  :key="index"
                  class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded"
                >
                  <div class="flex items-center">
                    <Paperclip class="w-4 h-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ file.name }}</span>
                    <span class="text-xs text-gray-500 ml-2">({{ formatFileSize(file.size) }})</span>
                  </div>
                  <button
                    @click="removeFile(index)"
                    type="button"
                    class="text-red-500 hover:text-red-700"
                  >
                    <X class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isSubmitting">Creating...</span>
              <span v-else>Create Bug</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { X, Upload, Paperclip } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import Select from '@/components/Select.vue'

const props = defineProps({
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
  }
})

const emit = defineEmits(['close', 'created'])

const isSubmitting = ref(false)
const selectedFiles = ref([])

const form = ref({
  title: '',
  description: '',
  long_description: '',
  project_id: '',
  sprint_id: '',
  user_id: '',
  importance: '',
  bug_type: '',
  environment: '',
  browser_info: '',
  os_info: '',
  steps_to_reproduce: '',
  expected_behavior: '',
  actual_behavior: '',
  tags: '',
  estimated_hours: '',
  reproducibility: 'sometimes',
  severity: 'medium'
})

const filteredSprints = computed(() => {
  if (!form.value.project_id) return props.sprints
  return props.sprints.filter(sprint => sprint.project_id === form.value.project_id)
})

const onProjectChange = () => {
  form.value.sprint_id = ''
}

const handleFileUpload = (event) => {
  const files = Array.from(event.target.files)
  files.forEach(file => {
    if (file.size <= 10 * 1024 * 1024) { // 10MB limit
      selectedFiles.value.push(file)
    } else {
      alert(`File ${file.name} is too large. Maximum size is 10MB.`)
    }
  })
}

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const submitForm = async () => {
  isSubmitting.value = true

  try {
    const formData = new FormData()
    
    // Add form fields
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== '') {
        formData.append(key, form.value[key])
      }
    })

    // Add files
    selectedFiles.value.forEach(file => {
      formData.append('attachments[]', file)
    })

    await router.post('/bugs', formData, {
      onSuccess: () => {
        emit('created')
      },
      onError: (errors) => {
        console.error('Error creating bug:', errors)
      }
    })
  } catch (error) {
    console.error('Error submitting form:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script> 