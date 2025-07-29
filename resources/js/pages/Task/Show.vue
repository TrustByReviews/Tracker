<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import Icon from '@/components/Icon.vue'
import { ref } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Tasks',
    href: '/tasks',
  },
  {
    title: 'Task Details',
    href: '#',
  },
]

interface User {
    id: string;
    name: string;
    email: string;
}

interface Sprint {
    id: string;
    name: string;
    goal: string;
    start_date: string;
    end_date: string;
}

interface Task {
    id: string,
    name: string,
    description: string,
    estimated_start: string | null,
    estimated_finish: string | null,
    status: string,
    priority: string,
    category: string,
    story_points: number,
    estimated_hours: number,
    user_id: string | null,
    user?: User,
    sprint?: Sprint,
    project?: {
        id: string,
        name: string
    },
    created_at?: string,
    updated_at?: string
}

const props = defineProps<{
    task: Task,
    permissions: string,
    developers?: User[]
}>()

const isEditing = ref(false);

const form = useForm({
    name: props.task.name,
    description: props.task.description,
    priority: props.task.priority,
    category: props.task.category,
    story_points: props.task.story_points,
    estimated_hours: props.task.estimated_hours,
    assigned_user_id: props.task.user_id || '',
    estimated_start: props.task.estimated_start || '',
    estimated_finish: props.task.estimated_finish || '',
    status: props.task.status,
});

const formatDate = (date: string | null) => {
    if (!date) return 'Not set';
    try {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch {
        return 'Invalid date';
    }
}

const getStatusColor = (status: string) => {
    switch (status) {
        case 'to do':
            return 'text-yellow-600 bg-yellow-100';
        case 'in progress':
            return 'text-blue-600 bg-blue-100';
        case 'done':
            return 'text-green-600 bg-green-100';
        case 'ready for test':
            return 'text-orange-600 bg-orange-100';
        case 'rejected':
            return 'text-red-600 bg-red-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
}

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high':
            return 'text-red-600 bg-red-100';
        case 'medium':
            return 'text-orange-600 bg-orange-100';
        case 'low':
            return 'text-yellow-600 bg-yellow-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
}

const back = () => {
    if (props.task.sprint && props.task.project) {
        router.get(`/projects/${props.task.project.id}/sprints/${props.task.sprint.id}`);
    } else {
        router.get('/tasks');
    }
}

const startEditing = () => {
    console.log('🔍 DEBUG: startEditing called');
    console.log('🔍 DEBUG: isEditing before:', isEditing.value);
    console.log('🔍 DEBUG: task data:', props.task);
    
    isEditing.value = true;
    
    console.log('🔍 DEBUG: isEditing after:', isEditing.value);
    
    // Reset form with current task values
    form.name = props.task.name;
    form.description = props.task.description;
    form.priority = props.task.priority;
    form.category = props.task.category;
    form.story_points = props.task.story_points;
    form.estimated_hours = props.task.estimated_hours;
    form.assigned_user_id = props.task.user_id || '';
    form.estimated_start = props.task.estimated_start || '';
    form.estimated_finish = props.task.estimated_finish || '';
    form.status = props.task.status;
    
    console.log('🔍 DEBUG: form values set:', form.data());
    console.log('🔍 DEBUG: form processing:', form.processing);
}

const saveChanges = () => {
    console.log('Saving changes...');
    form.put(`/tasks/${props.task.id}`, {
        onSuccess: () => {
            console.log('Task updated successfully');
            isEditing.value = false;
            form.reset();
        },
        onError: (errors) => {
            console.error('Task update errors:', errors);
        }
    });
}

const cancelEditing = () => {
    console.log('Canceling edit mode...');
    isEditing.value = false;
    form.reset();
}
</script>

<template>
  <Head :title="`${task.name} - Task Details`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-white">
        <!-- Debug info -->
        <div class="text-xs text-red-500 mb-2">
          🔍 DEBUG: isEditing = {{ isEditing }}, task.name = {{ task.name }}, task.id = {{ task.id }}
        </div>
        
        <div class="flex items-center space-x-4">
          <Button variant="ghost" size="sm" @click="back" class="flex items-center">
            <Icon name="arrow-left" class="h-4 w-4 mr-2" />
            Back
          </Button>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ task.name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Task Details</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <Button 
            v-if="!isEditing" 
            @click="startEditing" 
            class="border-blue-500 text-white bg-blue-500 hover:bg-blue-600 transition-colors"
          >
            Edit Task (DEBUG: {{ isEditing ? 'TRUE' : 'FALSE' }})
          </Button>
          <div v-else class="flex space-x-2">
            <Button 
              @click="saveChanges" 
              :disabled="form.processing"
              class="bg-green-500 text-white hover:bg-green-600 disabled:opacity-50"
            >
              <span v-if="form.processing">Saving...</span>
              <span v-else>Save Changes</span>
            </Button>
            <Button 
              @click="cancelEditing" 
              class="bg-gray-500 text-white hover:bg-gray-600"
            >
              Cancel
            </Button>
          </div>
        </div>
      </div>
    </template>

    <!-- Task Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Task Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Task Description -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="file-text" class="h-5 w-5 mr-2" />
              Description
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="!isEditing">
              <p class="text-gray-700 dark:text-gray-300">{{ task.description }}</p>
            </div>
            <div v-else>
              <textarea 
                v-model="form.description"
                rows="4"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter task description..."
              ></textarea>
            </div>
          </CardContent>
        </Card>

        <!-- Task Details -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="info" class="h-5 w-5 mr-2" />
              Task Details
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(task.status)}`">
                      {{ task.status }}
                    </span>
                  </div>
                  <select v-else v-model="form.status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="to do">To Do</option>
                    <option value="in progress">In Progress</option>
                    <option value="ready for test">Ready for Test</option>
                    <option value="done">Done</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Priority</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPriorityColor(task.priority)}`">
                      {{ task.priority }}
                    </span>
                  </div>
                  <select v-else v-model="form.priority" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Category</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">{{ task.category }}</p>
                  </div>
                  <select v-else v-model="form.category" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="frontend">Frontend</option>
                    <option value="backend">Backend</option>
                    <option value="full stack">Full Stack</option>
                    <option value="design">Design</option>
                    <option value="deployment">Deployment</option>
                    <option value="fixes">Fixes</option>
                    <option value="testing">Testing</option>
                    <option value="documentation">Documentation</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Story Points</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">{{ task.story_points }}</p>
                  </div>
                  <select v-else v-model="form.story_points" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option :value="1">1</option>
                    <option :value="2">2</option>
                    <option :value="3">3</option>
                    <option :value="5">5</option>
                    <option :value="8">8</option>
                    <option :value="13">13</option>
                    <option :value="21">21</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Estimated Hours</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">{{ task.estimated_hours }}h</p>
                  </div>
                  <input v-else v-model="form.estimated_hours" type="number" min="1" max="40" class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Assigned To</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">
                      {{ task.user ? task.user.name : 'Unassigned' }}
                    </p>
                  </div>
                  <select v-else v-model="form.assigned_user_id" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">Unassigned</option>
                    <option v-for="developer in developers" :key="developer.id" :value="developer.id">
                      {{ developer.name }} ({{ developer.email }})
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Dates -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="calendar" class="h-5 w-5 mr-2" />
              Dates
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Estimated Start</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(task.estimated_start) }}</p>
                  </div>
                  <input v-else v-model="form.estimated_start" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Estimated Finish</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(task.estimated_finish) }}</p>
                  </div>
                  <input v-else v-model="form.estimated_finish" type="date" :min="form.estimated_start" class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Project & Sprint Info -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="folder" class="h-5 w-5 mr-2" />
              Project & Sprint
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div v-if="task.project">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Project</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ task.project.name }}</p>
              </div>
              
              <div v-if="task.sprint">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Sprint</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ task.sprint.name }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ formatDate(task.sprint.start_date) }} → {{ formatDate(task.sprint.end_date) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Task ID -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="hash" class="h-5 w-5 mr-2" />
              Task Information
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Task ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ task.id }}</p>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Created</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(task.created_at) }}</p>
              </div>
              
              <div v-if="task.updated_at">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Last Updated</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(task.updated_at) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template> 