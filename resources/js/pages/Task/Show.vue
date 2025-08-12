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
    long_description?: string,
    status: string,
    priority: string,
    category: string,
    story_points: number,
    estimated_hours: number,
    estimated_minutes: number,
    actual_hours?: number,
    actual_minutes?: number,
    total_time_seconds?: number,
    user_id: string | null,
    user?: User,
    sprint?: Sprint,
    project?: {
        id: string,
        name: string
    },
    assigned_by?: string,
    assigned_by_user?: User,
    assigned_at?: string,
    actual_start?: string,
    actual_finish?: string,
    work_started_at?: string,
    is_working?: boolean,
    work_paused_at?: string,
    work_finished_at?: string,
    approval_status?: string,
    rejection_reason?: string,
    reviewed_by?: string,
    reviewed_by_user?: User,
    reviewed_at?: string,
    auto_close_at?: string,
    alert_count?: number,
    last_alert_at?: string,
    auto_paused?: boolean,
    auto_paused_at?: string,
    auto_pause_reason?: string,
    acceptance_criteria?: string,
    technical_notes?: string,
    complexity_level?: string,
    task_type?: string,
    tags?: string,
    attachments?: any[],
    created_at?: string,
    updated_at?: string,
    // QA fields
    qa_status?: string,
    qa_assigned_to?: string,
    qa_assigned_at?: string,
    qa_started_at?: string,
    qa_completed_at?: string,
    qa_notes?: string,
    qa_rejection_reason?: string,
    qa_reviewed_by?: string,
    qa_reviewed_at?: string,
    qa_testing_started_at?: string,
    qa_testing_paused_at?: string,
    qa_testing_finished_at?: string,
    // Team Leader fields
    team_leader_final_approval?: boolean,
    team_leader_final_approval_at?: string,
    team_leader_final_notes?: string,
    team_leader_requested_changes?: boolean,
    team_leader_requested_changes_at?: string,
    team_leader_change_notes?: string,
    team_leader_reviewed_by?: string,
    // Re-work tracking fields
    original_time_seconds?: number,
    retwork_time_seconds?: number,
    original_work_finished_at?: string,
    retwork_started_at?: string,
    has_been_returned?: boolean,
    return_count?: number,
    last_returned_by?: string,
    last_returned_at?: string
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
    long_description: props.task.long_description || '',
    priority: props.task.priority,
    category: props.task.category,
    story_points: props.task.story_points,
    estimated_hours: props.task.estimated_hours,
    estimated_minutes: props.task.estimated_minutes || 0,
    assigned_user_id: props.task.user_id || '',
    actual_start: props.task.actual_start || '',
    actual_finish: props.task.actual_finish || '',
    status: props.task.status,
    acceptance_criteria: props.task.acceptance_criteria || '',
    technical_notes: props.task.technical_notes || '',
    complexity_level: props.task.complexity_level || 'medium',
    task_type: props.task.task_type || 'feature',
    tags: props.task.tags || '',
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

const formatDateTime = (date: string | null) => {
    if (!date) return 'Not set';
    try {
        return new Date(date).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return 'Invalid date';
    }
}

const formatTime = (seconds: number | null) => {
    if (!seconds) return '0h 0m 0s';
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${hours}h ${minutes}m ${secs}s`;
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
        case 'critical':
            return 'text-red-600 bg-red-100';
        case 'high':
            return 'text-orange-600 bg-orange-100';
        case 'medium':
            return 'text-yellow-600 bg-yellow-100';
        case 'low':
            return 'text-green-600 bg-green-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
}

const getComplexityColor = (complexity: string) => {
    switch (complexity) {
        case 'expert':
            return 'text-red-600 bg-red-100';
        case 'high':
            return 'text-orange-600 bg-orange-100';
        case 'medium':
            return 'text-yellow-600 bg-yellow-100';
        case 'low':
            return 'text-green-600 bg-green-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
}

const getTaskTypeColor = (taskType: string) => {
    switch (taskType) {
        case 'feature':
            return 'text-blue-600 bg-blue-100';
        case 'bugfix':
            return 'text-red-600 bg-red-100';
        case 'improvement':
            return 'text-green-600 bg-green-100';
        case 'refactor':
            return 'text-purple-600 bg-purple-100';
        case 'documentation':
            return 'text-gray-600 bg-gray-100';
        case 'testing':
            return 'text-orange-600 bg-orange-100';
        case 'research':
            return 'text-indigo-600 bg-indigo-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
}

const getApprovalStatusColor = (status: string) => {
    switch (status) {
        case 'approved':
            return 'text-green-600 bg-green-100';
        case 'rejected':
            return 'text-red-600 bg-red-100';
        case 'pending':
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
    isEditing.value = true;
    
    // Reset form with current task values
    form.name = props.task.name;
    form.description = props.task.description;
    form.long_description = props.task.long_description || '';
    form.priority = props.task.priority;
    form.category = props.task.category;
    form.story_points = props.task.story_points;
    form.estimated_hours = props.task.estimated_hours;
    form.estimated_minutes = props.task.estimated_minutes || 0;
    form.assigned_user_id = props.task.user_id || '';
    form.actual_start = props.task.actual_start || '';
    form.actual_finish = props.task.actual_finish || '';
    form.status = props.task.status;
    form.acceptance_criteria = props.task.acceptance_criteria || '';
    form.technical_notes = props.task.technical_notes || '';
    form.complexity_level = props.task.complexity_level || 'medium';
    form.task_type = props.task.task_type || 'feature';
    form.tags = props.task.tags || '';
}

const saveChanges = () => {
    form.put(`/tasks/${props.task.id}`, {
        onSuccess: () => {
            isEditing.value = false;
            form.reset();
        },
        onError: (errors) => {
            console.error('Task update errors:', errors);
        }
    });
}

const cancelEditing = () => {
    isEditing.value = false;
    form.reset();
}

// Time tracking handlers
const handleStartWork = async () => {
    try {
        await router.post(`/tasks/${props.task.id}/start-work`);
        router.reload();
    } catch (error) {
        console.error('Error starting work:', error);
    }
}

const handlePauseWork = async () => {
    try {
        await router.post(`/tasks/${props.task.id}/pause-work`);
        router.reload();
    } catch (error) {
        console.error('Error pausing work:', error);
    }
}

const handleResumeWork = async () => {
    try {
        await router.post(`/tasks/${props.task.id}/resume-work`);
        router.reload();
    } catch (error) {
        console.error('Error resuming work:', error);
    }
}

const handleFinishWork = async () => {
    try {
        await router.post(`/tasks/${props.task.id}/finish-work`);
        router.reload();
    } catch (error) {
        console.error('Error finishing work:', error);
    }
}

const getFileName = (attachment: any) => {
    if (typeof attachment === 'string') {
        return attachment.split('/').pop();
    }
    return attachment.name || 'File';
}

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

const openAttachment = (attachment: any) => {
    if (typeof attachment === 'string') {
        window.open(attachment, '_blank');
    } else if (attachment.path) {
        window.open(`/storage/${attachment.path}`, '_blank');
    }
}
</script>

<template>
  <Head :title="`${task.name} - Task Details`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-white">
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
            class="border-green-500 text-white bg-green-500 hover:bg-green-600 transition-colors"
          >
            Edit Task
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

        <!-- Long Description -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="file-text" class="h-5 w-5 mr-2" />
              Detailed Description
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="!isEditing">
              <p class="text-gray-700 dark:text-gray-300">{{ task.long_description }}</p>
            </div>
            <div v-else>
              <textarea 
                v-model="form.long_description"
                rows="6"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter detailed description..."
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
              
              <!-- Assign to Developer (project developers) -->
              <div class="mt-4">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Assign To</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <span class="text-sm text-gray-900 dark:text-white">{{ task.user?.name || 'Unassigned' }}</span>
                  </div>
                  <select 
                    v-else 
                    v-model="form.assigned_user_id" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2"
                  >
                    <option value="">Unassigned</option>
                    <option 
                      v-for="dev in (props.developers || [])" 
                      :key="dev.id" 
                      :value="dev.id"
                    >
                      {{ dev.name }}
                    </option>
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
                    <option value="critical">Critical</option>
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
                    <option value="database">Database</option>
                    <option value="api">API</option>
                    <option value="security">Security</option>
                    <option value="performance">Performance</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Task Type</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTaskTypeColor(task.task_type || 'feature')}`">
                      {{ task.task_type || 'feature' }}
                    </span>
                  </div>
                  <select v-else v-model="form.task_type" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="feature">New Feature</option>
                    <option value="bugfix">Bug Fix</option>
                    <option value="improvement">Improvement</option>
                    <option value="refactor">Refactor</option>
                    <option value="documentation">Documentation</option>
                    <option value="testing">Testing</option>
                    <option value="research">Research</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Complexity Level</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getComplexityColor(task.complexity_level || 'medium')}`">
                      {{ task.complexity_level || 'medium' }}
                    </span>
                  </div>
                  <select v-else v-model="form.complexity_level" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="expert">Expert</option>
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
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Estimated Time</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">{{ task.estimated_hours }}h {{ task.estimated_minutes || 0 }}m</p>
                  </div>
                  <div v-else class="flex space-x-2">
                    <input v-model="form.estimated_hours" type="number" min="0" max="40" class="w-1/2 border border-gray-300 rounded-md px-3 py-2" placeholder="Hours">
                    <input v-model="form.estimated_minutes" type="number" min="0" max="59" class="w-1/2 border border-gray-300 rounded-md px-3 py-2" placeholder="Minutes">
                  </div>
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

        <!-- Acceptance Criteria -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="check-circle" class="h-5 w-5 mr-2" />
              Acceptance Criteria
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="!isEditing">
              <p class="text-gray-700 dark:text-gray-300">{{ task.acceptance_criteria }}</p>
            </div>
            <div v-else>
              <textarea 
                v-model="form.acceptance_criteria"
                rows="4"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter acceptance criteria..."
              ></textarea>
            </div>
          </CardContent>
        </Card>

        <!-- Technical Notes -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="code" class="h-5 w-5 mr-2" />
              Technical Notes
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="!isEditing">
              <p class="text-gray-700 dark:text-gray-300">{{ task.technical_notes }}</p>
            </div>
            <div v-else>
              <textarea 
                v-model="form.technical_notes"
                rows="4"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter technical notes..."
              ></textarea>
            </div>
          </CardContent>
        </Card>

        <!-- Task Workflow Logs -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="activity" class="h-5 w-5 mr-2" />
              Workflow History & Logs
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-6">
              <!-- Original Developer Work Time -->
              <div v-if="task.total_time_seconds || task.work_started_at" class="border-l-4 border-blue-500 pl-4">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2 flex items-center">
                  <Icon name="user" class="h-4 w-4 mr-2 text-blue-500" />
                  Original Development Work
                </h4>
                <div class="space-y-2 text-sm">
                  <div v-if="task.original_time_seconds" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Original time worked:</span>
                    <span class="font-medium text-blue-600 dark:text-blue-400">{{ formatTime(task.original_time_seconds) }}</span>
                  </div>
                  <div v-if="!task.original_time_seconds && task.total_time_seconds" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total time worked:</span>
                    <span class="font-medium text-blue-600 dark:text-blue-400">{{ formatTime(task.total_time_seconds) }}</span>
                  </div>
                  <div v-if="task.work_started_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">First started:</span>
                    <span class="font-medium">{{ formatDateTime(task.work_started_at) }}</span>
                  </div>
                  <div v-if="task.original_work_finished_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Original work completed:</span>
                    <span class="font-medium">{{ formatDateTime(task.original_work_finished_at || null) }}</span>
                  </div>
                  <div v-if="!task.original_work_finished_at && task.work_finished_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">First completed:</span>
                    <span class="font-medium">{{ formatDateTime(task.work_finished_at) }}</span>
                  </div>
                </div>
              </div>

              <!-- QA Testing Time -->
              <div v-if="task.qa_started_at || task.qa_completed_at" class="border-l-4 border-green-500 pl-4">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2 flex items-center">
                  <Icon name="shield" class="h-4 w-4 mr-2 text-green-500" />
                  QA Testing
                </h4>
                <div class="space-y-2 text-sm">
                  <div v-if="task.qa_assigned_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Assigned to QA:</span>
                    <span class="font-medium">{{ formatDateTime(task.qa_assigned_at || null) }}</span>
                  </div>
                  <div v-if="task.qa_started_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Testing started:</span>
                    <span class="font-medium">{{ formatDateTime(task.qa_started_at || null) }}</span>
                  </div>
                  <div v-if="task.qa_completed_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Testing completed:</span>
                    <span class="font-medium">{{ formatDateTime(task.qa_completed_at || null) }}</span>
                  </div>
                  <div v-if="task.qa_notes" class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded">
                    <span class="text-gray-600 dark:text-gray-400 text-xs">QA Notes:</span>
                    <p class="text-sm text-green-800 dark:text-green-300 mt-1">{{ task.qa_notes }}</p>
                  </div>
                  <div v-if="task.qa_rejection_reason" class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded">
                    <span class="text-gray-600 dark:text-gray-400 text-xs">Rejection Reason:</span>
                    <p class="text-sm text-red-800 dark:text-red-300 mt-1">{{ task.qa_rejection_reason }}</p>
                  </div>
                </div>
              </div>

              <!-- Team Leader Review -->
              <div v-if="task.team_leader_final_approval_at || task.team_leader_requested_changes_at" class="border-l-4 border-purple-500 pl-4">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2 flex items-center">
                  <Icon name="award" class="h-4 w-4 mr-2 text-purple-500" />
                  Team Leader Review
                </h4>
                <div class="space-y-2 text-sm">
                  <div v-if="task.team_leader_final_approval_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Final approval:</span>
                    <span class="font-medium text-green-600 dark:text-green-400">{{ formatDateTime(task.team_leader_final_approval_at || null) }}</span>
                  </div>
                  <div v-if="task.team_leader_requested_changes_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Changes requested:</span>
                    <span class="font-medium text-orange-600 dark:text-orange-400">{{ formatDateTime(task.team_leader_requested_changes_at || null) }}</span>
                  </div>
                  <div v-if="task.team_leader_final_notes" class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded">
                    <span class="text-gray-600 dark:text-gray-400 text-xs">Approval Notes:</span>
                    <p class="text-sm text-green-800 dark:text-green-300 mt-1">{{ task.team_leader_final_notes }}</p>
                  </div>
                  <div v-if="task.team_leader_change_notes" class="mt-2 p-2 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded">
                    <span class="text-gray-600 dark:text-gray-400 text-xs">Change Request Notes:</span>
                    <p class="text-sm text-orange-800 dark:text-orange-300 mt-1">{{ task.team_leader_change_notes }}</p>
                  </div>
                </div>
              </div>

              <!-- Re-work Time (if task was returned) -->
              <div v-if="(task.qa_rejection_reason || task.team_leader_change_notes) && task.work_started_at" class="border-l-4 border-red-500 pl-4">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2 flex items-center">
                  <Icon name="refresh-cw" class="h-4 w-4 mr-2 text-red-500" />
                  Re-work Information
                </h4>
                <div class="space-y-2 text-sm">
                  <div v-if="task.qa_rejection_reason" class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded">
                    <span class="text-gray-600 dark:text-gray-400 text-xs">Returned by QA:</span>
                    <p class="text-sm text-red-800 dark:text-red-300 mt-1">{{ task.qa_rejection_reason }}</p>
                  </div>
                  <div v-if="task.team_leader_change_notes" class="mt-2 p-2 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded">
                    <span class="text-gray-600 dark:text-gray-400 text-xs">Returned by Team Leader:</span>
                    <p class="text-sm text-orange-800 dark:text-orange-300 mt-1">{{ task.team_leader_change_notes }}</p>
                  </div>
                  <div v-if="task.work_started_at" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Re-work started:</span>
                    <span class="font-medium">{{ formatDateTime(task.work_started_at || null) }}</span>
                  </div>
                  <!-- Re-work Time Tracking -->
                  <div v-if="task.is_working" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Currently working on re-work:</span>
                    <span class="font-medium text-blue-600 dark:text-blue-400">Yes</span>
                  </div>
                  <div v-if="task.total_time_seconds && (task.qa_rejection_reason || task.team_leader_change_notes)" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Additional time spent:</span>
                    <span class="font-medium text-red-600 dark:text-red-400">{{ formatTime(task.total_time_seconds) }}</span>
                  </div>
                </div>
              </div>

              <!-- Current Re-work Session (if actively working) -->
              <div v-if="task.is_working && (task.qa_rejection_reason || task.team_leader_change_notes)" class="border-l-4 border-orange-500 pl-4">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2 flex items-center">
                  <Icon name="clock" class="h-4 w-4 mr-2 text-orange-500" />
                  Current Re-work Session
                </h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Session started:</span>
                    <span class="font-medium">{{ formatDateTime(task.work_started_at || null) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Current session time:</span>
                    <span class="font-medium text-orange-600 dark:text-orange-400 animate-pulse">{{ formatTime(task.total_time_seconds || 0) }}</span>
                  </div>
                </div>
              </div>

              <!-- No logs message -->
              <div v-if="!task.total_time_seconds && !task.qa_started_at && !task.team_leader_final_approval_at && !task.team_leader_requested_changes_at" class="text-center py-4 text-gray-500 dark:text-gray-400">
                <Icon name="activity" class="h-8 w-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                <p class="text-sm">No workflow activity recorded yet.</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Tags -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="tag" class="h-5 w-5 mr-2" />
              Tags
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="!isEditing">
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="tag in task.tags?.split(',')"
                  :key="tag"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                  {{ tag.trim() }}
                </span>
              </div>
            </div>
            <div v-else>
              <input 
                v-model="form.tags"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter tags separated by commas..."
              />
            </div>
          </CardContent>
        </Card>

        <!-- Attachments -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="paperclip" class="h-5 w-5 mr-2" />
              Attachments
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="(attachment, index) in task.attachments"
                :key="index"
                class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                @click="openAttachment(attachment)"
              >
                <Icon name="file" class="w-8 h-8 text-gray-400 mr-3" />
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ getFileName(attachment) }}</p>
                  <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }}</p>
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
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Actual Start</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(task.actual_start || null) }}</p>
                  </div>
                  <input v-else v-model="form.actual_start" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
              </div>
              
              <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Actual Finish</label>
                <div class="mt-1">
                  <div v-if="!isEditing">
                    <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(task.actual_finish || null) }}</p>
                  </div>
                  <input v-else v-model="form.actual_finish" type="date" :min="form.actual_start" class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Time Tracking -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="clock" class="h-5 w-5 mr-2" />
              Time Tracking
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estimated</span>
                <span class="text-sm text-gray-900 dark:text-white">
                  {{ task.estimated_hours }}h {{ task.estimated_minutes || 0 }}m
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Actual</span>
                <span class="text-sm text-gray-900 dark:text-white">
                  {{ task.actual_hours || 0 }}h {{ task.actual_minutes || 0 }}m
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Time</span>
                                 <span class="text-sm text-gray-900 dark:text-white">
                   {{ formatTime(task.total_time_seconds || null) }}
                 </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                <span
                  :class="{
                    'text-green-600': task.is_working,
                    'text-gray-600': !task.is_working
                  }"
                  class="text-sm font-medium"
                >
                  {{ task.is_working ? 'Working' : 'Not Working' }}
                </span>
              </div>
              <div v-if="task.work_started_at" class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Started At</span>
                <span class="text-sm text-gray-900 dark:text-white">
                  {{ formatDateTime(task.work_started_at || null) }}
                </span>
              </div>
              <div v-if="task.work_paused_at" class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Paused At</span>
                <span class="text-sm text-gray-900 dark:text-white">
                  {{ formatDateTime(task.work_paused_at || null) }}
                </span>
              </div>
              <div v-if="task.work_finished_at" class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Finished At</span>
                <span class="text-sm text-gray-900 dark:text-white">
                  {{ formatDateTime(task.work_finished_at || null) }}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Action Buttons -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="play" class="h-5 w-5 mr-2" />
              Actions
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <!-- Start Work Button -->
              <Button
                v-if="task.user_id && (task.status === 'to do' || (task.status === 'in progress' && !task.is_working && task.total_time_seconds === 0)) && !task.is_working"
                @click="handleStartWork"
                class="w-full bg-green-600 hover:bg-green-700 text-white"
              >
                <Icon name="play" class="w-4 h-4 mr-2" />
                Start Work
              </Button>

              <!-- Pause Work Button -->
              <Button
                v-if="task.is_working"
                @click="handlePauseWork"
                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white"
              >
                <Icon name="pause" class="w-4 h-4 mr-2" />
                Pause Work
              </Button>

              <!-- Resume Work Button -->
              <Button
                v-if="task.status === 'in progress' && !task.is_working && task.total_time_seconds && task.total_time_seconds > 0"
                @click="handleResumeWork"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white"
              >
                <Icon name="play" class="w-4 h-4 mr-2" />
                Resume Work
              </Button>

              <!-- Finish Work Button -->
              <Button
                v-if="task.status === 'in progress' && task.is_working"
                @click="handleFinishWork"
                class="w-full bg-green-600 hover:bg-green-700 text-white"
              >
                <Icon name="check" class="w-4 h-4 mr-2" />
                Finish Work
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Assignment Info -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="user" class="h-5 w-5 mr-2" />
              Assignment
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned To</span>
                <p class="text-sm text-gray-900 dark:text-white mt-1">
                  {{ task.user?.name || 'Unassigned' }}
                </p>
              </div>
              <div v-if="task.assigned_by_user">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned By</span>
                <p class="text-sm text-gray-900 dark:text-white mt-1">
                  {{ task.assigned_by_user.name }}
                </p>
              </div>
                             <div v-if="task.assigned_at">
                 <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Date</span>
                 <p class="text-sm text-gray-900 dark:text-white mt-1">
                   {{ formatDateTime(task.assigned_at || null) }}
                 </p>
               </div>
            </div>
          </CardContent>
        </Card>

        <!-- Approval Status -->
        <Card v-if="task.approval_status">
          <CardHeader>
            <CardTitle class="flex items-center">
              <Icon name="shield" class="h-5 w-5 mr-2" />
              Approval Status
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                <div class="mt-1">
                  <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getApprovalStatusColor(task.approval_status)}`">
                    {{ task.approval_status }}
                  </span>
                </div>
              </div>
              <div v-if="task.rejection_reason">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejection Reason</span>
                <p class="text-sm text-gray-900 dark:text-white mt-1">
                  {{ task.rejection_reason }}
                </p>
              </div>
              <div v-if="task.reviewed_by_user">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Reviewed By</span>
                <p class="text-sm text-gray-900 dark:text-white mt-1">
                  {{ task.reviewed_by_user.name }}
                </p>
              </div>
                             <div v-if="task.reviewed_at">
                 <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Reviewed At</span>
                 <p class="text-sm text-gray-900 dark:text-white mt-1">
                   {{ formatDateTime(task.reviewed_at || null) }}
                 </p>
               </div>
            </div>
          </CardContent>
        </Card>

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
                                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(task.created_at || null) }}</p>
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