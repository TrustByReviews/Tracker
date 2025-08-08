<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button @click="back" class="flex items-center text-gray-600 hover:text-gray-900">
            ← Back
          </button>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Test Sprint Details</h1>
            <p class="text-sm text-gray-600">Debugging view</p>
          </div>
        </div>
      </div>
    </template>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-medium mb-4">Debug Information</h2>
        
        <div class="space-y-4">
          <div>
            <strong>Sprint:</strong>
            <pre class="mt-2 text-sm bg-gray-100 p-2 rounded">{{ JSON.stringify(sprint, null, 2) }}</pre>
          </div>
          
          <div>
            <strong>Project:</strong>
            <pre class="mt-2 text-sm bg-gray-100 p-2 rounded">{{ JSON.stringify(project, null, 2) }}</pre>
          </div>
          
          <div>
            <strong>Tasks:</strong>
            <pre class="mt-2 text-sm bg-gray-100 p-2 rounded">{{ JSON.stringify(tasks, null, 2) }}</pre>
          </div>
          
          <div>
            <strong>Bugs:</strong>
            <pre class="mt-2 text-sm bg-gray-100 p-2 rounded">{{ JSON.stringify(bugs, null, 2) }}</pre>
          </div>
          
          <div>
            <strong>Developers:</strong>
            <pre class="mt-2 text-sm bg-gray-100 p-2 rounded">{{ JSON.stringify(developers, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'

interface Sprint {
  id: string;
  name: string;
  goal: string;
  start_date: string;
  end_date: string;
}

interface Project {
  id: string;
  name: string;
}

interface User {
  id: string;
  name: string;
  email: string;
}

interface Task {
  id: string;
  title: string;
  description: string;
  status: string;
  user?: User;
}

interface Bug {
  id: string;
  title: string;
  description: string;
  status: string;
  user?: User;
}

const props = defineProps<{
  sprint: Sprint;
  project: Project;
  tasks: Task[];
  bugs: Bug[];
  developers: User[];
  permissions: string;
}>();

function back() {
  router.visit('/team-leader/sprints');
}
</script>
