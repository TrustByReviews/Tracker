<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
      <p class="text-sm text-gray-600 dark:text-gray-400">{{ description }}</p>
    </div>
    
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
      <div v-if="tasks.length === 0" class="px-6 py-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tasks</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No tasks found in this category.</p>
      </div>
      
      <div v-for="task in tasks" :key="task.id" class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
        <div class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                    :class="getPriorityClass(task.priority)">
                {{ task.priority }}
              </span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                    :class="getStatusClass(task.status)">
                {{ task.status }}
              </span>
            </div>
            
            <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ task.name }}
            </h4>
            
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
              {{ task.description }}
            </p>
            
            <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
              <span v-if="task.sprint?.project" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                {{ task.sprint.project.name }}
              </span>
              
              <span v-if="task.estimated_hours" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ task.estimated_hours }}h
              </span>
              
              <span v-if="task.actual_hours" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ task.actual_hours }}h
              </span>
            </div>
          </div>
          
          <div class="ml-4 flex-shrink-0">
            <button v-if="showAssignButton && !task.user_id" 
                    @click="$emit('assign-task', task)"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Assign to me
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Task {
  id: string;
  name: string;
  description: string;
  status: string;
  priority: string;
  estimated_hours?: number;
  actual_hours?: number;
  user_id?: string;
  sprint?: {
    project?: {
      name: string;
    };
  };
}

interface Props {
  title: string;
  description: string;
  tasks: Task[];
  showAssignButton?: boolean;
}

withDefaults(defineProps<Props>(), {
  showAssignButton: false,
});

defineEmits<{
  'assign-task': [task: Task];
}>();

const getPriorityClass = (priority: string): string => {
  const classes = {
    high: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
  };
  return classes[priority as keyof typeof classes] || classes.medium;
};

const getStatusClass = (status: string): string => {
  const classes = {
    'to do': 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    'in progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'done': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
  };
  return classes[status as keyof typeof classes] || classes['to do'];
};
</script> 