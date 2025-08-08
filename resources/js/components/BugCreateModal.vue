<template>
  <Dialog :open="true" @update:open="$emit('close')">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogTitle class="text-xl font-semibold mb-4">
        Create New Bug
      </DialogTitle>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Required Fields Section -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <h3 class="text-lg font-semibold text-red-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            Required Fields
          </h3>
          
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="title" class="text-gray-700 font-medium">Title <span class="text-red-500">*</span></Label>
              <Input
                id="title"
                v-model="form.title"
                placeholder="Bug title"
                required
                class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
              />
            </div>

            <div>
              <Label for="importance" class="text-gray-700 font-medium">Importance <span class="text-red-500">*</span></Label>
              <Select v-model="form.importance">
                <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                  <SelectValue placeholder="Select importance" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="low">Low</SelectItem>
                  <SelectItem value="medium">Medium</SelectItem>
                  <SelectItem value="high">High</SelectItem>
                  <SelectItem value="critical">Critical</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="mt-6">
            <Label for="description" class="text-gray-700 font-medium">Description <span class="text-red-500">*</span></Label>
            <Textarea
              id="description"
              v-model="form.description"
              placeholder="Brief description of the bug"
              rows="3"
              required
              class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
              <Label for="bug_type" class="text-gray-700 font-medium">Bug Type <span class="text-red-500">*</span></Label>
              <Select v-model="form.bug_type">
                <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                  <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="frontend">Frontend</SelectItem>
                  <SelectItem value="backend">Backend</SelectItem>
                  <SelectItem value="database">Database</SelectItem>
                  <SelectItem value="api">API</SelectItem>
                  <SelectItem value="ui_ux">UI/UX</SelectItem>
                  <SelectItem value="performance">Performance</SelectItem>
                  <SelectItem value="security">Security</SelectItem>
                  <SelectItem value="other">Other</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label for="severity" class="text-gray-700 font-medium">Severity <span class="text-red-500">*</span></Label>
              <Select v-model="form.severity">
                <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                  <SelectValue placeholder="Select severity" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="low">Low</SelectItem>
                  <SelectItem value="medium">Medium</SelectItem>
                  <SelectItem value="high">High</SelectItem>
                  <SelectItem value="critical">Critical</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
              <Label for="reproducibility" class="text-gray-700 font-medium">Reproducibility <span class="text-red-500">*</span></Label>
              <Select v-model="form.reproducibility">
                <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                  <SelectValue placeholder="Select reproducibility" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="always">Always</SelectItem>
                  <SelectItem value="sometimes">Sometimes</SelectItem>
                  <SelectItem value="rarely">Rarely</SelectItem>
                  <SelectItem value="unable">Unable to Reproduce</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label for="environment" class="text-gray-700 font-medium">Environment <span class="text-red-500">*</span></Label>
              <Select v-model="form.environment">
                <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                  <SelectValue placeholder="Select environment" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="development">Development</SelectItem>
                  <SelectItem value="staging">Staging</SelectItem>
                  <SelectItem value="production">Production</SelectItem>
                  <SelectItem value="testing">Testing</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        <!-- Optional Fields Section -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Optional Fields
          </h3>
          
          <div class="space-y-6">
            <!-- Detailed Description -->
            <div>
              <Label for="long_description" class="text-gray-700 font-medium">Detailed Description</Label>
              <Textarea
                id="long_description"
                v-model="form.long_description"
                placeholder="Detailed description of the bug with additional context"
                rows="4"
                class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
              />
            </div>

            <!-- Technical Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="browser_info" class="text-gray-700 font-medium">Browser Info</Label>
                <div class="space-y-2">
                  <div class="flex gap-2">
                    <Select v-model="selectedBrowser" @change="onBrowserChange" class="flex-1">
                      <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                        <SelectValue placeholder="Select browser or enter custom" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="chrome">Chrome</SelectItem>
                        <SelectItem value="firefox">Firefox</SelectItem>
                        <SelectItem value="safari">Safari</SelectItem>
                        <SelectItem value="edge">Microsoft Edge</SelectItem>
                        <SelectItem value="opera">Opera</SelectItem>
                        <SelectItem value="brave">Brave</SelectItem>
                        <SelectItem value="other">Other / Custom</SelectItem>
                      </SelectContent>
                    </Select>
                    <button 
                      v-if="selectedBrowser" 
                      @click="clearBrowserSelection" 
                      type="button"
                      class="px-2 py-2 text-sm text-gray-500 hover:text-gray-700"
                    >
                      ✕
                    </button>
                    <button 
                      @click="autoDetectBrowser" 
                      type="button"
                      class="px-3 py-2 text-sm text-blue-600 hover:text-blue-700 border border-blue-300 rounded hover:bg-blue-50"
                    >
                      Auto
                    </button>
                  </div>
                  
                  <div v-if="selectedBrowser === 'other' || showCustomBrowser">
                    <Input
                      id="browser_info"
                      v-model="form.browser_info"
                      placeholder="e.g., Chrome 120.0.0.0, Firefox 121.0, Custom Browser"
                      class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                    />
                  </div>
                  
                  <div v-else-if="selectedBrowser && selectedBrowser !== 'other'">
                    <Input
                      id="browser_info"
                      v-model="form.browser_info"
                      :placeholder="getBrowserPlaceholder(selectedBrowser)"
                      class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                    />
                    <p class="text-xs text-gray-500 mt-1">You can edit the version information above</p>
                  </div>
                </div>
              </div>

              <div>
                <Label for="os_info" class="text-gray-700 font-medium">OS Info</Label>
                <div class="space-y-2">
                  <div class="flex gap-2">
                    <Select v-model="selectedOS" @change="onOSChange" class="flex-1">
                      <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                        <SelectValue placeholder="Select OS or enter custom" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="windows">Windows</SelectItem>
                        <SelectItem value="macos">macOS</SelectItem>
                        <SelectItem value="linux">Linux</SelectItem>
                        <SelectItem value="ubuntu">Ubuntu</SelectItem>
                        <SelectItem value="debian">Debian</SelectItem>
                        <SelectItem value="centos">CentOS</SelectItem>
                        <SelectItem value="fedora">Fedora</SelectItem>
                        <SelectItem value="android">Android</SelectItem>
                        <SelectItem value="ios">iOS</SelectItem>
                        <SelectItem value="other">Other / Custom</SelectItem>
                      </SelectContent>
                    </Select>
                    <button 
                      v-if="selectedOS" 
                      @click="clearOSSelection" 
                      type="button"
                      class="px-2 py-2 text-sm text-gray-500 hover:text-gray-700"
                    >
                      ✕
                    </button>
                    <button 
                      @click="autoDetectOS" 
                      type="button"
                      class="px-3 py-2 text-sm text-blue-600 hover:text-blue-700 border border-blue-300 rounded hover:bg-blue-50"
                    >
                      Auto
                    </button>
                  </div>
                  
                  <div v-if="selectedOS === 'other' || showCustomOS">
                    <Input
                      id="os_info"
                      v-model="form.os_info"
                      placeholder="e.g., Windows 11, macOS 14.0, Ubuntu 22.04, Custom OS"
                      class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                    />
                  </div>
                  
                  <div v-else-if="selectedOS && selectedOS !== 'other'">
                    <Input
                      id="os_info"
                      v-model="form.os_info"
                      :placeholder="getOSPlaceholder(selectedOS)"
                      class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                    />
                    <p class="text-xs text-gray-500 mt-1">You can edit the version information above</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Behavior Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="expected_behavior" class="text-gray-700 font-medium">Expected Behavior</Label>
                <Textarea
                  id="expected_behavior"
                  v-model="form.expected_behavior"
                  placeholder="What should happen..."
                  rows="3"
                  class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                />
              </div>

              <div>
                <Label for="actual_behavior" class="text-gray-700 font-medium">Actual Behavior</Label>
                <Textarea
                  id="actual_behavior"
                  v-model="form.actual_behavior"
                  placeholder="What actually happens..."
                  rows="3"
                  class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                />
              </div>
            </div>

            <!-- Steps to Reproduce -->
            <div>
              <Label for="steps_to_reproduce" class="text-gray-700 font-medium">Steps to Reproduce</Label>
              <div class="space-y-2">
                <div class="flex gap-2 mb-2">
                  <button 
                    @click="addStep" 
                    type="button"
                    class="px-3 py-1 text-sm text-blue-600 hover:text-blue-700 border border-blue-300 rounded hover:bg-blue-50"
                  >
                    + Add Step
                  </button>
                  <button 
                    @click="clearSteps" 
                    type="button"
                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-700 border border-gray-300 rounded hover:bg-gray-50"
                  >
                    Clear All
                  </button>
                  <button 
                    @click="loadTemplate" 
                    type="button"
                    class="px-3 py-1 text-sm text-green-600 hover:text-green-700 border border-green-300 rounded hover:bg-green-50"
                  >
                    Load Template
                  </button>
                </div>
                <Textarea
                  id="steps_to_reproduce"
                  v-model="form.steps_to_reproduce"
                  placeholder="1. Go to the specific page...&#10;2. Click on the button...&#10;3. Observe the error..."
                  rows="6"
                  class="font-mono text-sm bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                />
              </div>
            </div>

            <!-- Related Task -->
            <div>
              <Label for="related_task_id" class="text-gray-700 font-medium">Related Task</Label>
              <div class="space-y-2">
                <div class="flex gap-2">
                  <Input
                    v-model="searchQuery"
                    placeholder="Search for related tasks..."
                    class="flex-1 bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                  />
                  <button 
                    @click="searchRelatedTasks" 
                    type="button"
                    class="px-3 py-2 text-sm text-blue-600 hover:text-blue-700 border border-blue-300 rounded hover:bg-blue-50"
                  >
                    Search
                  </button>
                  <button 
                    v-if="selectedRelatedTask" 
                    @click="clearRelatedTask" 
                    type="button"
                    class="px-3 py-2 text-sm text-red-600 hover:text-red-700 border border-red-300 rounded hover:bg-red-50"
                  >
                    Clear
                  </button>
                </div>
                
                <div v-if="searchResults.length > 0" class="max-h-40 overflow-y-auto border border-gray-200 rounded bg-white">
                  <div 
                    v-for="task in searchResults" 
                    :key="task.id"
                    @click="selectRelatedTask(task)"
                    class="p-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                  >
                    <div class="font-medium text-sm text-gray-900">{{ task.name }}</div>
                    <div class="text-xs text-gray-500">{{ task.description }}</div>
                  </div>
                </div>
                
                <div v-if="selectedRelatedTask" class="p-2 bg-blue-50 border border-blue-200 rounded">
                  <div class="font-medium text-sm text-blue-900">Selected: {{ selectedRelatedTask.name }}</div>
                  <div class="text-xs text-blue-700">{{ selectedRelatedTask.description }}</div>
                </div>
              </div>
            </div>

            <!-- Additional Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="estimated_hours" class="text-gray-700 font-medium">Estimated Hours</Label>
                <Input
                  id="estimated_hours"
                  v-model="form.estimated_hours"
                  type="number"
                  min="0"
                  max="100"
                  placeholder="0"
                  class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                />
              </div>

              <div>
                <Label for="estimated_minutes" class="text-gray-700 font-medium">Estimated Minutes</Label>
                <Input
                  id="estimated_minutes"
                  v-model="form.estimated_minutes"
                  type="number"
                  min="0"
                  max="59"
                  placeholder="0"
                  class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                />
              </div>
            </div>

            <div>
              <Label for="tags" class="text-gray-700 font-medium">Tags</Label>
              <Input
                id="tags"
                v-model="form.tags"
                placeholder="Tags separated by commas (e.g., frontend, api, bug)"
                class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
              />
            </div>

            <!-- File Upload Section -->
            <div>
              <Label class="text-gray-700 font-medium">Attachments</Label>
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
                  Drag files here or 
                  <label for="file-upload" class="text-blue-600 hover:text-blue-500 cursor-pointer">
                    select files
                  </label>
                </p>
                <input 
                  id="file-upload" 
                  type="file" 
                  multiple 
                  @change="handleFileSelect" 
                  class="hidden"
                />
                <p class="text-xs text-gray-500">Maximum 10MB per file</p>
              </div>

              <!-- File List -->
              <div v-if="selectedFiles.length > 0" class="mt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Selected files:</h4>
                <div class="space-y-2">
                  <div 
                    v-for="(file, index) in selectedFiles" 
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
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { X, Upload, Paperclip } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'

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
  },
  // Context props for auto-selection
  currentProject: {
    type: Object,
    default: null
  },
  currentSprint: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'created'])

const isSubmitting = ref(false)
const selectedFiles = ref([])
const dragOver = ref(false)

// Browser and OS selection
const selectedBrowser = ref('')
const selectedOS = ref('')
const showCustomBrowser = ref(false)
const showCustomOS = ref(false)

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
  estimated_minutes: '',
  reproducibility: 'sometimes',
  severity: 'medium',
  related_task_id: ''
})

// Browser and OS handling functions
const onBrowserChange = (value) => {
  if (value === 'other') {
    showCustomBrowser.value = true
    form.value.browser_info = ''
  } else if (value) {
    showCustomBrowser.value = false
    form.value.browser_info = getBrowserPlaceholder(value)
  }
}

const onOSChange = (value) => {
  if (value === 'other') {
    showCustomOS.value = true
    form.value.os_info = ''
  } else if (value) {
    showCustomOS.value = false
    form.value.os_info = getOSPlaceholder(value)
  }
}

const getBrowserPlaceholder = (browser) => {
  const placeholders = {
    chrome: 'Chrome 120.0.0.0',
    firefox: 'Firefox 121.0',
    safari: 'Safari 17.0',
    edge: 'Microsoft Edge 120.0.0.0',
    opera: 'Opera 104.0.0.0',
    brave: 'Brave 1.60.0'
  }
  return placeholders[browser] || 'Enter browser version...'
}

const getOSPlaceholder = (os) => {
  const placeholders = {
    windows: 'Windows 11',
    macos: 'macOS 14.0',
    linux: 'Linux (Ubuntu 22.04)',
    ubuntu: 'Ubuntu 22.04',
    debian: 'Debian 12',
    centos: 'CentOS 8',
    fedora: 'Fedora 38',
    android: 'Android 14',
    ios: 'iOS 17'
  }
  return placeholders[os] || 'Enter OS version...'
}

// Auto-detect browser and OS
const autoDetectBrowserAndOS = () => {
  const userAgent = navigator.userAgent
  
  // Auto-detect browser
  if (userAgent.includes('Chrome') && !userAgent.includes('Edg')) {
    selectedBrowser.value = 'chrome'
    form.value.browser_info = 'Chrome ' + (userAgent.match(/Chrome\/(\d+)/)?.[1] || 'Unknown')
  } else if (userAgent.includes('Firefox')) {
    selectedBrowser.value = 'firefox'
    form.value.browser_info = 'Firefox ' + (userAgent.match(/Firefox\/(\d+)/)?.[1] || 'Unknown')
  } else if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) {
    selectedBrowser.value = 'safari'
    form.value.browser_info = 'Safari ' + (userAgent.match(/Version\/(\d+)/)?.[1] || 'Unknown')
  } else if (userAgent.includes('Edg')) {
    selectedBrowser.value = 'edge'
    form.value.browser_info = 'Microsoft Edge ' + (userAgent.match(/Edg\/(\d+)/)?.[1] || 'Unknown')
  } else if (userAgent.includes('OPR') || userAgent.includes('Opera')) {
    selectedBrowser.value = 'opera'
    form.value.browser_info = 'Opera ' + (userAgent.match(/OPR\/(\d+)/)?.[1] || 'Unknown')
  }

  // Auto-detect OS
  if (userAgent.includes('Windows')) {
    selectedOS.value = 'windows'
    form.value.os_info = 'Windows ' + (userAgent.match(/Windows NT (\d+\.\d+)/)?.[1] || 'Unknown')
  } else if (userAgent.includes('Mac OS X')) {
    selectedOS.value = 'macos'
    form.value.os_info = 'macOS ' + (userAgent.match(/Mac OS X (\d+_\d+)/)?.[1]?.replace('_', '.') || 'Unknown')
  } else if (userAgent.includes('Linux')) {
    selectedOS.value = 'linux'
    form.value.os_info = 'Linux (Unknown distribution)'
  } else if (userAgent.includes('Android')) {
    selectedOS.value = 'android'
    form.value.os_info = 'Android ' + (userAgent.match(/Android (\d+)/)?.[1] || 'Unknown')
  } else if (userAgent.includes('iPhone') || userAgent.includes('iPad')) {
    selectedOS.value = 'ios'
    form.value.os_info = 'iOS ' + (userAgent.match(/OS (\d+_\d+)/)?.[1]?.replace('_', '.') || 'Unknown')
  }
}

const clearBrowserSelection = () => {
  selectedBrowser.value = ''
  showCustomBrowser.value = false
  form.value.browser_info = ''
}

const clearOSSelection = () => {
  selectedOS.value = ''
  showCustomOS.value = false
  form.value.os_info = ''
}

const autoDetectBrowser = () => {
  autoDetectBrowserAndOS()
  alert('Browser and OS information auto-detected!')
}

const autoDetectOS = () => {
  autoDetectBrowserAndOS()
  alert('Browser and OS information auto-detected!')
}

// Auto-set project and sprint from context
watch(() => props.currentProject, (newProject) => {
  if (newProject) {
    form.value.project_id = newProject.id
  }
}, { immediate: true })

watch(() => props.currentSprint, (newSprint) => {
  if (newSprint) {
    form.value.sprint_id = newSprint.id
  }
}, { immediate: true })

// File handling functions
const handleFileSelect = (event) => {
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

const handleDragOver = (event) => {
  event.preventDefault()
  dragOver.value = true
}

const handleDragLeave = (event) => {
  event.preventDefault()
  dragOver.value = false
}

const handleDrop = (event) => {
  event.preventDefault()
  dragOver.value = false
  
  if (event.dataTransfer?.files) {
    const files = Array.from(event.dataTransfer.files)
    files.forEach(file => {
      if (file.size <= 10 * 1024 * 1024) { // 10MB limit
        selectedFiles.value.push(file)
      } else {
        alert(`File ${file.name} is too large. Maximum size is 10MB.`)
      }
    })
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Related task search
const searchQuery = ref('')
const searchResults = ref([])
const selectedRelatedTask = ref(null)

const searchRelatedTasks = async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }
  try {
    const response = await fetch(`/api/tasks/search?q=${encodeURIComponent(searchQuery.value)}`)
    if (response.ok) {
      const tasks = await response.json()
      searchResults.value = tasks.slice(0, 10)
    }
  } catch (error) {
    console.error('Error searching tasks:', error)
    searchResults.value = []
  }
}

const selectRelatedTask = (task) => {
  selectedRelatedTask.value = task
  form.value.related_task_id = task.id
  searchQuery.value = ''
  searchResults.value = []
}

const clearRelatedTask = () => {
  selectedRelatedTask.value = null
  form.value.related_task_id = ''
}

const addStep = () => {
  const currentSteps = form.value.steps_to_reproduce.split('\n').filter(step => step.trim() !== '')
  const nextStepNumber = currentSteps.length + 1
  const newStep = `${nextStepNumber}. `
  
  if (form.value.steps_to_reproduce.trim() === '') {
    form.value.steps_to_reproduce = newStep
  } else {
    form.value.steps_to_reproduce += '\n' + newStep
  }
}

const clearSteps = () => {
  if (confirm('Are you sure you want to clear all steps?')) {
    form.value.steps_to_reproduce = ''
  }
}

const loadTemplate = () => {
  const template = `1. Navigate to the specific page or feature
2. Perform the action that triggers the bug
3. Fill in any required fields or data
4. Submit or save the form/action
5. Observe the unexpected behavior or error
6. Note any error messages or console errors
7. Describe what you expected to happen instead`
  
  form.value.steps_to_reproduce = template
}

const submitForm = async () => {
  // Client-side validation
  if (!form.value.title.trim()) {
    alert('Bug title is required')
    return
  }
  
  if (!form.value.description.trim()) {
    alert('Description is required')
    return
  }
  
  if (!form.value.importance) {
    alert('Importance is required')
    return
  }
  
  if (!form.value.bug_type) {
    alert('Bug type is required')
    return
  }
  
  if (!form.value.sprint_id) {
    alert('Sprint is required')
    return
  }
  
  if (!form.value.project_id) {
    alert('Project is required')
    return
  }

  isSubmitting.value = true

  try {
    const formData = new FormData()
    
    // Add all form fields, including empty ones for proper validation
    Object.keys(form.value).forEach(key => {
      const value = form.value[key]
      
      // Handle different field types
      if (key === 'estimated_hours' || key === 'estimated_minutes') {
        // Send numeric fields as numbers, empty string for null
        const numValue = value === '' || value === null || value === undefined ? '' : Number(value)
        formData.append(key, numValue)
      } else if (key === 'user_id') {
        // Send user_id only if it's not empty
        if (value && value !== '') {
          formData.append(key, value)
        }
      } else if (key === 'related_task_id') {
        // Send related_task_id only if it's not empty
        if (value && value !== '') {
          formData.append(key, value)
        }
      } else {
        // Send all other fields, including empty strings for nullable fields
        formData.append(key, value || '')
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
        // Show validation errors to user
        if (errors.title) {
          alert('Error: ' + errors.title[0])
        } else if (errors.description) {
          alert('Error: ' + errors.description[0])
        } else if (errors.importance) {
          alert('Error: ' + errors.importance[0])
        } else if (errors.bug_type) {
          alert('Error: ' + errors.bug_type[0])
        } else if (errors.reproducibility) {
          alert('Error: ' + errors.reproducibility[0])
        } else if (errors.severity) {
          alert('Error: ' + errors.severity[0])
        } else if (errors.sprint_id) {
          alert('Error: Sprint is required')
        } else if (errors.project_id) {
          alert('Error: Project is required')
        } else {
          alert('Error creating bug. Please check all required fields.')
        }
      }
    })
  } catch (error) {
    console.error('Error submitting form:', error)
    alert('Error submitting form. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}
</script> 