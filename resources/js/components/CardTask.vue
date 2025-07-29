<script setup lang="ts">
import { computed, defineProps } from 'vue';

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
    user?: {
        id: string,
        name: string,
        email: string
    }
}

const props = defineProps<{
    task: Task,
    permissions: string,
    project_id: string,
    sprint: Sprint,
    developers?: User[]
}>()

const formatDate = (date: string | null) => {
    if (!date) return 'Not set';
    try {
        return new Date(date).toLocaleDateString('en-US');
    } catch {
        return 'Invalid date';
    }
}

const taskStatusIconPath = computed(() => {
  switch (props.task.status) {
    case 'to do':
      return 'M4 6h16M4 12h16M4 18h16';
    case 'in progress':
      return 'M12 4v1m0 14v1m8-8h1M3 12H2m15.07-6.93l.71.71M6.22 17.78l-.71.71';
    case 'done':
      return 'M5 13l4 4L19 7';
    default:
      return 'M9 17v-2a4 4 0 014-4h6m-6 0V9a4 4 0 00-4-4H5a4 4 0 00-4 4v6a4 4 0 004 4h2';
  }
});

const taskPriorityStyles = computed(() => {
  switch (props.task.priority) {
    case 'low':
      return {
        classes: 'inline-flex items-center justify-center bg-yellow-100 text-yellow-800 border border-yellow-300 px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm'
      };
    case 'medium':
      return {
        classes: 'inline-flex items-center justify-center bg-orange-100 text-orange-800 border border-orange-300 px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm'
      };
    case 'high':
      return {
        classes: 'inline-flex items-center justify-center bg-red-100 text-red-800 border border-red-300 px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm'
      };
    default:
      return {
        classes: 'inline-flex items-center justify-center bg-gray-100 text-gray-800 border border-gray-300 px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm'
      };
  }
});

const taskStatusStyles = computed(() => {
  switch (props.task.status) {
    case 'to do':
      return {
        border: 'border-l-yellow-500',
        bg: 'from-white to-yellow-50',
        text: 'text-yellow-700',
        icon: 'text-yellow-500',
        button: 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200',
        titleColor: 'text-yellow-700',
        dateColor: 'text-yellow-600'
      };
    case 'in progress':
      return {
        border: 'border-l-blue-500',
        bg: 'from-white to-blue-50',
        text: 'text-blue-700',
        icon: 'text-blue-500',
        button: 'bg-blue-100 text-blue-700 hover:bg-blue-200',
        titleColor: 'text-blue-700',
        dateColor: 'text-blue-600'
      };
    case 'done':
      return {
        border: 'border-l-green-500',
        bg: 'from-white to-green-50',
        text: 'text-green-700',
        icon: 'text-green-500',
        button: 'bg-green-100 text-green-700 hover:bg-green-200',
        titleColor: 'text-green-700',
        dateColor: 'text-green-600'
      };
    default:
      return {
        border: 'border-l-gray-400',
        bg: 'from-white to-gray-50',
        text: 'text-gray-700',
        icon: 'text-gray-400',
        button: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
        titleColor: 'text-gray-700',
        dateColor: 'text-gray-600'
      };
  }
});

const showTask = () => {
    // Validar que tengamos un project_id válido
    if (!props.project_id || props.project_id === 'NaN' || props.project_id === 'undefined' || props.project_id === null) {
        console.error('Invalid project_id:', props.project_id);
        return;
    }
    
    // Validar que tengamos un task.id válido
    if (!props.task.id || props.task.id === 'NaN' || props.task.id === 'undefined') {
        console.error('Invalid task.id:', props.task.id);
        return;
    }
    
    const url = `/tasks/${props.task.id}`;
    window.location.href = url;
}
</script>

<template>
    <div :class="`w-full max-w-xs flex flex-col justify-between p-5 rounded-xl ${taskStatusStyles.border} bg-gradient-to-br ${taskStatusStyles.bg} shadow-lg transition-all duration-200 hover:shadow-xl hover:scale-105`">
        <!-- Task name and priority -->
        <div class="flex justify-between items-start mb-3">
            <h2 :class="`text-lg font-semibold ${taskStatusStyles.titleColor} flex items-center gap-2 break-words flex-1 mr-2`">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" :class="taskStatusStyles.icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="taskStatusIconPath" />
                </svg>
                <span class="line-clamp-2">{{ props.task.name }}</span>
            </h2>
            <span :class="`${taskPriorityStyles.classes} flex-shrink-0`">
                {{ props.task.priority.toUpperCase() }}
            </span>
        </div>

        <!-- Task description -->
        <p class="text-sm text-gray-600 mt-2 italic break-words line-clamp-2 mb-4">
            {{ props.task.description }}
        </p>

        <!-- Task details in two columns -->
        <div class="grid grid-cols-2 gap-4 text-xs text-gray-600 mb-4">
            <!-- Left column -->
            <div class="space-y-2">
                <div class="font-medium text-gray-700">Category</div>
                <div class="text-gray-600">{{ props.task.category }}</div>
                
                <div class="font-medium text-gray-700">Hours</div>
                <div class="text-gray-600">{{ props.task.estimated_hours }}h</div>
                
                <div :class="`font-medium ${taskStatusStyles.dateColor}`">Estimated date</div>
                <div class="flex items-center gap-1 text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs">{{ formatDate(props.task.estimated_start) }} → {{ formatDate(props.task.estimated_finish) }}</span>
                </div>
            </div>
            
            <!-- Right column -->
            <div class="space-y-2">
                <div class="font-medium text-gray-700">Story Points</div>
                <div class="text-gray-600">{{ props.task.story_points }}</div>
                
                <div class="font-medium text-gray-700">Assigned</div>
                <div v-if="props.task.user" class="text-gray-600 truncate">{{ props.task.user.name }}</div>
                <div v-else class="text-gray-400 italic">Unassigned</div>
            </div>
        </div>

        <!-- Action button -->
        <div class="flex justify-end mt-auto">
            <button 
                :class="`${taskStatusStyles.button} px-4 py-2 rounded-lg transition-all duration-200 font-medium text-sm`" 
                type="button" 
                @click="showTask"
            >
                View more
            </button>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>