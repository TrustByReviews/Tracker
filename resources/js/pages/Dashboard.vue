<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DashboardStats from '@/components/DashboardStats.vue';
import TaskList from '@/components/TaskList.vue';
import ProjectList from '@/components/ProjectList.vue';
import AdminStats from '@/components/AdminStats.vue';
import DeveloperPerformance from '@/components/DeveloperPerformance.vue';
import ProjectStatus from '@/components/ProjectStatus.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Props from the controller
interface Props {
    user: any;
    isAdmin: boolean;
    // Developer props
    assignedProjects?: any[];
    tasksInProgress?: any[];
    completedTasks?: any[];
    highPriorityUnassignedTasks?: any[];
    stats?: any;
    // Admin props
    projects?: any[];
    developers?: any[];
    systemStats?: any;
}

const props = defineProps<Props>();

const assignTask = (task: any) => {
    router.put(`/tasks/${task.id}`, {
        user_id: props.user.id,
        status: 'in_progress'
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Developer Dashboard -->
            <div v-if="!isAdmin">
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
                    <h1 class="text-2xl font-bold mb-2">Welcome back, {{ user.name }}!</h1>
                    <p class="text-blue-100">Here's your current work overview and progress.</p>
                </div>

                <!-- Statistics Cards -->
                <DashboardStats :stats="stats" />

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- My Projects -->
                <ProjectList :projects="assignedProjects || []" :user-id="user.id" />

                <!-- Tasks In Progress -->
                <TaskList 
                    title="Tasks In Progress" 
                    description="Tasks you're currently working on"
                    :tasks="tasksInProgress || []"
                />
                </div>

                <!-- Tasks Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Completed Tasks -->
                    <TaskList 
                        title="Completed Tasks" 
                        description="Tasks you've finished"
                        :tasks="completedTasks || []"
                    />

                    <!-- High Priority Unassigned Tasks -->
                    <TaskList 
                        title="High Priority Tasks Available" 
                        description="High priority tasks that need assignment"
                        :tasks="highPriorityUnassignedTasks || []"
                        :show-assign-button="true"
                        @assign-task="assignTask"
                    />
                </div>
            </div>

            <!-- Admin Dashboard -->
            <div v-else>
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg p-6 text-white">
                    <h1 class="text-2xl font-bold mb-2">Admin Dashboard</h1>
                    <p class="text-green-100">System overview and team performance metrics.</p>
                </div>

                <!-- System Statistics -->
                <AdminStats :stats="systemStats" />

                <!-- Project Status Overview -->
                <ProjectStatus :projects="projects || []" />

                <!-- Developer Performance -->
                <DeveloperPerformance :developers="developers || []" />

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Common administrative tasks</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="/payment-reports" 
                               class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900">
                                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Payment Reports</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Generate and export payment reports</p>
                                </div>
                            </a>
                            
                            <a href="/users" 
                               class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Manage Users</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Add and manage team members</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
