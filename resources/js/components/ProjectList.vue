<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Projects</h3>
      <p class="text-sm text-gray-600 dark:text-gray-400">Projects you are assigned to</p>
    </div>
    
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
      <div v-if="projects.length === 0" class="px-6 py-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No projects</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You are not assigned to any projects yet.</p>
      </div>
      
      <div v-for="project in projects" :key="project.id" class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
        <div class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                    :class="getStatusClass(project.status)">
                {{ project.status }}
              </span>
            </div>
            
            <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ project.name }}
            </h4>
            
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
              {{ project.description }}
            </p>
            
            <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                {{ getMyTasksCount(project) }} tasks
              </span>
              
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                {{ project.users?.length || 0 }} members
              </span>
            </div>
          </div>
          
          <div class="ml-4 flex-shrink-0">
            <a :href="`/projects/${project.id}`" 
               class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              View
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Project {
  id: string;
  name: string;
  description: string;
  status: string;
  users?: any[];
  sprints?: Array<{
    tasks?: Array<{
      user_id?: string;
    }>;
  }>;
}

interface Props {
  projects: Project[];
  userId: string;
}

const props = defineProps<Props>();

const getStatusClass = (status: string): string => {
  const classes = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
  };
  return classes[status as keyof typeof classes] || classes.active;
};

const getMyTasksCount = (project: Project): number => {
  if (!project.sprints) return 0;
  
  return project.sprints.reduce((total, sprint) => {
    if (!sprint.tasks) return total;
    return total + sprint.tasks.filter(task => task.user_id === props.userId).length;
  }, 0);
};
</script> 