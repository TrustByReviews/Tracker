<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DashboardStats from '@/components/DashboardStats.vue';
import QaDashboardStats from '@/components/QaDashboardStats.vue';
import TeamLeaderDashboardStats from '@/components/TeamLeaderDashboardStats.vue';
import TaskList from '@/components/TaskList.vue';
import ProjectList from '@/components/ProjectList.vue';
import AdminStats from '@/components/AdminStats.vue';
import DeveloperPerformance from '@/components/DeveloperPerformance.vue';
import ProjectStatus from '@/components/ProjectStatus.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

/**
 * Dashboard Component
 * 
 * This is the main dashboard page that provides role-specific views and functionality
 * for different user types (Admin, Team Leader, Developer, QA). It dynamically renders
 * different content based on user permissions and role.
 * 
 * Features:
 * - Role-based dashboard views
 * - Real-time statistics and metrics
 * - Task and project management
 * - Performance tracking
 * - Quick action buttons
 * - Responsive design for all screen sizes
 * 
 * @component
 * @example
 * <Dashboard 
 *   :user="currentUser"
 *   :isAdmin="userIsAdmin"
 *   :isQa="userIsQa"
 *   :isTeamLeader="userIsTeamLeader"
 *   :assignedProjects="userProjects"
 *   :tasksInProgress="userTasks"
 * />
 */

/**
 * Breadcrumb navigation items for the dashboard
 */
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

/**
 * Component props interface for dashboard data
 */
interface Props {
    user: any;                    // Current authenticated user
    isAdmin: boolean;             // Whether user has admin privileges
    isQa?: boolean;               // Whether user has QA role
    isTeamLeader?: boolean;       // Whether user has team leader role
    
    // Developer-specific props
    assignedProjects?: any[];     // Projects assigned to the developer
    tasksInProgress?: any[];      // Tasks currently in progress
    completedTasks?: any[];       // Recently completed tasks
    highPriorityUnassignedTasks?: any[]; // High priority tasks available for assignment
    stats?: any;                  // Developer performance statistics
    
    // Admin-specific props
    projects?: any[];             // All projects in the system
    developers?: any[];           // All developers in the system
    systemStats?: any;            // System-wide statistics
    tasksRequiringAttention?: any[]; // Tasks that need admin attention
    activeProjectsSummary?: any[]; // Summary of active projects
    developerMetrics?: any[];     // Performance metrics for all developers
    
    // QA-specific props
    tasksReadyForTesting?: any[]; // Tasks ready for QA testing
    bugsReadyForTesting?: any[];  // Bugs ready for QA testing
    tasksInTesting?: any[];       // Tasks currently being tested
    bugsInTesting?: any[];        // Bugs currently being tested
    existingTasks?: any[];        // All tasks in the system
    existingBugs?: any[];         // All bugs in the system
    
    // Team Leader-specific props
    pendingTasks?: any[];         // Tasks pending team leader approval
    qaApprovedTasks?: any[];      // Tasks approved by QA
    qaApprovedBugs?: any[];       // Bugs approved by QA
    
    // Bug-related props
    bugs?: any[];                 // Bugs assigned to the user
    bugStats?: any;               // Bug-related statistics
}

const props = defineProps<Props>();

/**
 * Assign a task to the current user
 * Updates the task status to 'in_progress' and assigns it to the user
 * 
 * @param {any} task - The task to be assigned
 */
const assignTask = (task: any) => {
    router.put(`/tasks/${task.id}`, {
        user_id: props.user.id,
        status: 'in_progress'
    });
};

/**
 * Format time in seconds to HH:MM:SS format
 * Converts total seconds to a readable time format
 * 
 * @param {number} seconds - Total seconds to format
 * @returns {string} Formatted time string (HH:MM:SS)
 */
const formatTime = (seconds: number) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

/**
 * Calculate time excess for a task
 * Compares actual time spent vs estimated time and returns the excess
 * 
 * @param {any} task - The task to calculate time excess for
 * @returns {string} Formatted excess time or 'N/A' if no estimate
 */
const getTimeExcess = (task: any) => {
    if (!task.estimated_hours) return 'N/A';
    
    const estimatedSeconds = task.estimated_hours * 3600;
    const actualSeconds = task.total_time_seconds || 0;
    const excessSeconds = actualSeconds - estimatedSeconds;
    
    if (excessSeconds <= 0) return '0h';
    
    const excessHours = Math.floor(excessSeconds / 3600);
    const excessMinutes = Math.floor((excessSeconds % 3600) / 60);
    
    return `${excessHours}h ${excessMinutes}m`;
};

/**
 * Format date string to localized format
 * Converts date string to Spanish locale format
 * 
 * @param {string} dateString - Date string to format
 * @returns {string} Formatted date string
 */
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

/**
 * Navigate to task details page
 * Redirects user to the detailed view of a specific task
 * 
 * @param {string} taskId - ID of the task to view
 */
const viewTaskDetails = (taskId: string) => {
    router.visit(`/tasks/${taskId}`);
};

const viewProjectDetails = (projectId: string) => {
    router.visit(`/projects/${projectId}`);
};

// QA Functions
const assignTaskToQa = (task: any) => {
    router.post(`/qa/tasks/${task.id}/assign`);
};

const assignBugToQa = (bug: any) => {
    router.post(`/qa/bugs/${bug.id}/assign`);
};

const approveTask = (task: any, notes?: string) => {
    router.post(`/qa/tasks/${task.id}/approve`, { notes });
};

const rejectTask = (task: any, reason: string) => {
    router.post(`/qa/tasks/${task.id}/reject`, { reason });
};

const approveBug = (bug: any, notes?: string) => {
    router.post(`/qa/bugs/${bug.id}/approve`, { notes });
};

const rejectBug = (bug: any, reason: string) => {
    router.post(`/qa/bugs/${bug.id}/reject`, { reason });
};

// Team Leader Functions
const approveTaskFinal = (task: any, notes?: string) => {
    router.post(`/team-leader/tasks/${task.id}/review-qa-approval`, { 
        action: 'approve',
        notes 
    });
};

const requestTaskChanges = (task: any, notes: string) => {
    router.post(`/team-leader/tasks/${task.id}/review-qa-approval`, { 
        action: 'request_changes',
        notes 
    });
};

const approveBugFinal = (bug: any, notes?: string) => {
    router.post(`/team-leader/bugs/${bug.id}/review-qa-approval`, { 
        action: 'approve',
        notes 
    });
};

const requestBugChanges = (bug: any, notes: string) => {
    router.post(`/team-leader/bugs/${bug.id}/review-qa-approval`, { 
        action: 'request_changes',
        notes 
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- QA Dashboard -->
            <div v-if="isQa">
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg p-4 text-white">
                    <h1 class="text-xl font-bold mb-1">QA Dashboard - {{ user.name }}</h1>
                    <p class="text-purple-100 text-sm">Manage testing workflow and quality assurance tasks.</p>
                </div>

                <!-- Statistics Cards -->
                <QaDashboardStats :stats="stats" />

                <!-- Tasks Ready for Testing -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tasks Ready for Testing</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Tasks that need QA review</p>
                    </div>
                    <div class="p-4">
                        <div v-if="tasksReadyForTesting?.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No tasks ready for testing</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="task in tasksReadyForTesting" :key="task.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ task.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ task.user?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ task.description }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <button @click="assignTaskToQa(task)" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            Assign to Me
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tasks In Testing -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tasks In Testing</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Tasks currently being tested by you</p>
                    </div>
                    <div class="p-4">
                        <div v-if="tasksInTesting?.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No tasks in testing</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="task in tasksInTesting" :key="task.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ task.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ task.user?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ task.description }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 space-x-2">
                                        <button @click="approveTask(task)" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                            Approve
                                        </button>
                                        <button @click="rejectTask(task, '')" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Tasks (Read Only) -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Existing Tasks</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">All tasks in your projects (read only)</p>
                    </div>
                    <div class="p-4">
                        <div v-if="existingTasks?.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No existing tasks</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="task in existingTasks" :key="task.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ task.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ task.user?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Status: {{ task.status }} | QA Status: {{ task.qa_status }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ task.description }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <button @click="viewTaskDetails(task.id)" 
                                                class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bugs Ready for Testing -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Bugs Ready for Testing</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Bugs that need QA review</p>
                    </div>
                    <div class="p-4">
                        <div v-if="bugsReadyForTesting?.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No bugs ready for testing</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="bug in bugsReadyForTesting" :key="bug.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ bug.title }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ bug.project?.name }} - {{ bug.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ bug.user?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ bug.description }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <button @click="assignBugToQa(bug)" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            Assign to Me
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bugs In Testing -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Bugs In Testing</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Bugs currently being tested by you</p>
                    </div>
                    <div class="p-4">
                        <div v-if="bugsInTesting?.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No bugs in testing</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="bug in bugsInTesting" :key="bug.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ bug.title }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ bug.project?.name }} - {{ bug.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ bug.user?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ bug.description }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 space-x-2">
                                        <button @click="approveBug(bug)" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                            Approve
                                        </button>
                                        <button @click="rejectBug(bug, '')" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Leader Dashboard -->
            <div v-else-if="isTeamLeader">
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-lg p-4 text-white">
                    <h1 class="text-xl font-bold mb-1">Team Leader Dashboard - {{ user.name }}</h1>
                    <p class="text-orange-100 text-sm">Manage team workflow and final approvals.</p>
                </div>

                <!-- Statistics Cards -->
                <TeamLeaderDashboardStats :stats="stats" />

                <!-- Pending Tasks -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Pending Tasks</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Tasks completed by developers awaiting approval</p>
                    </div>
                    <div class="p-4">
                        <div v-if="pendingTasks?.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No pending tasks</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="task in pendingTasks" :key="task.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ task.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ task.user?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ task.description }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <button @click="viewTaskDetails(task.id)" 
                                                class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            Review
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QA Approved Tasks -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">QA Approved Tasks</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Tasks approved by QA awaiting final review</p>
                    </div>
                    <div class="p-4">
                        <div v-if="qaApprovedTasks?.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No QA approved tasks</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="task in qaApprovedTasks" :key="task.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ task.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ task.user?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">QA: {{ task.qaReviewedBy?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ task.description }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 space-x-2">
                                        <button @click="approveTaskFinal(task)" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                            Final Approve
                                        </button>
                                        <button @click="requestTaskChanges(task, '')" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                                            Request Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Developer Dashboard -->
            <div v-else-if="!isAdmin">
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-4 text-white">
                    <h1 class="text-xl font-bold mb-1">Welcome back, {{ user.name }}!</h1>
                    <p class="text-blue-100 text-sm">Here's your current work overview and progress.</p>
                </div>

                <!-- Statistics Cards -->
                <DashboardStats :stats="stats" />

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
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
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
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
                <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg p-4 text-white">
                    <h1 class="text-xl font-bold mb-1">Admin Dashboard</h1>
                    <p class="text-green-100 text-sm">System overview and team performance metrics.</p>
                </div>

                <!-- System Statistics -->
                <AdminStats :stats="systemStats" />

                <!-- Compact Grid Layout -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                    <!-- Tasks Requiring Attention -->
                    <div class="xl:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tasks Requiring Attention</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Tasks that exceed estimated time by more than 20%</p>
                            </div>
                            <div class="p-4 max-h-64 overflow-y-auto">
                                <div v-if="tasksRequiringAttention?.length === 0" class="text-center py-4">
                                    <svg class="mx-auto h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="mt-1 text-xs text-gray-500">Excellent! No tasks require attention</p>
                                </div>
                                
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="task in tasksRequiringAttention"
                                        :key="task.id"
                                        class="border border-red-300 dark:border-red-600 rounded-lg p-3 bg-red-50 dark:bg-red-900/20"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ task.name }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Dev: {{ task.user?.name }}</p>
                                                <div class="mt-2 flex items-center space-x-3 text-xs">
                                                    <span class="text-red-600 dark:text-red-400 font-medium">
                                                        Actual: {{ formatTime(task.total_time_seconds || 0) }}
                                                    </span>
                                                    <span v-if="task.estimated_hours" class="text-gray-500 dark:text-gray-400">
                                                        Est: {{ task.estimated_hours }}h
                                                    </span>
                                                    <span class="text-red-600 dark:text-red-400 font-medium">
                                                        +{{ getTimeExcess(task) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-2 flex-shrink-0">
                                                <button
                                                    @click="viewTaskDetails(task.id)"
                                                    class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                                >
                                                    View
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Projects Summary -->
                    <div class="xl:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Active Projects</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Summary of ongoing projects</p>
                            </div>
                            <div class="p-4 max-h-64 overflow-y-auto">
                                <div v-if="activeProjectsSummary?.length === 0" class="text-center py-4">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="mt-1 text-xs text-gray-500">No active projects</p>
                                </div>
                                
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="project in activeProjectsSummary"
                                        :key="project.id"
                                        class="border border-gray-200 dark:border-gray-600 rounded-lg p-3"
                                    >
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ project.name }}</h4>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ project.team_members_count }}m</span>
                                        </div>
                                        
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-500 dark:text-gray-400">In progress:</span>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ project.in_progress_tasks_count }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-500 dark:text-gray-400">Pending:</span>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ project.pending_tasks_count }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-600">
                                            <button
                                                @click="viewProjectDetails(project.id)"
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                            >
                                                View details →
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Developer Performance Metrics -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Developer Performance Metrics</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Team efficiency and productivity</p>
                    </div>
                    <div class="p-4 max-h-64 overflow-y-auto">
                        <div v-if="developerMetrics?.length === 0" class="text-center py-4">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <p class="mt-1 text-xs text-gray-500">No metrics available</p>
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dev</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tasks</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Efficiency</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Average</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    <tr v-for="metric in developerMetrics" :key="metric.developer.id">
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-xs font-medium text-gray-900 dark:text-white">{{ metric.developer.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ metric.developer.email }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-xs text-gray-900 dark:text-white">{{ metric.total_tasks }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ metric.completed_tasks }} completed</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-white">
                                            {{ metric.formatted_time_spent }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-12 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mr-2">
                                                    <div
                                                        :class="{
                                                            'bg-green-500': metric.efficiency_percentage >= 80,
                                                            'bg-yellow-500': metric.efficiency_percentage >= 60 && metric.efficiency_percentage < 80,
                                                            'bg-red-500': metric.efficiency_percentage < 60
                                                        }"
                                                        class="h-1.5 rounded-full"
                                                        :style="{ width: Math.min(metric.efficiency_percentage, 100) + '%' }"
                                                    ></div>
                                                </div>
                                                <span class="text-xs text-gray-900 dark:text-white">{{ metric.efficiency_percentage }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-white">
                                            {{ metric.average_task_time }}h
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Direct access to main functions</p>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <a href="/payment-reports" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="p-2 rounded-full bg-emerald-100 dark:bg-emerald-900">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-white">Payment Reports</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Generate payment reports</p>
                                </div>
                            </a>
                            
                            <a href="/users" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-white">Manage Users</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Add and manage team</p>
                                </div>
                            </a>

                            <a href="/permissions" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-900">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-white">Permissions</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Manage user permissions</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bugs Section -->
                <div v-if="bugs && bugs.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Bugs</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Latest bugs requiring attention</p>
                            </div>
                            <button
                                @click="router.visit('/bugs')"
                                class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                            >
                                View All →
                            </button>
                        </div>
                    </div>
                    <div class="p-4 max-h-64 overflow-y-auto">
                        <div class="space-y-3">
                            <div
                                v-for="bug in bugs.slice(0, 5)"
                                :key="bug.id"
                                class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                @click="router.visit(`/bugs/${bug.id}`)"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-white truncate flex-1">{{ bug.title }}</h4>
                                    <div class="flex items-center space-x-1 ml-2">
                                        <span
                                            :class="{
                                                'bg-blue-100 text-blue-800': bug.status === 'new',
                                                'bg-yellow-100 text-yellow-800': bug.status === 'assigned',
                                                'bg-orange-100 text-orange-800': bug.status === 'in progress',
                                                'bg-green-100 text-green-800': bug.status === 'resolved',
                                                'bg-purple-100 text-purple-800': bug.status === 'verified',
                                                'bg-gray-100 text-gray-800': bug.status === 'closed',
                                                'bg-red-100 text-red-800': bug.status === 'reopened'
                                            }"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{ bug.status }}
                                        </span>
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800': bug.importance === 'low',
                                                'bg-yellow-100 text-yellow-800': bug.importance === 'medium',
                                                'bg-orange-100 text-orange-800': bug.importance === 'high',
                                                'bg-red-100 text-red-800': bug.importance === 'critical'
                                            }"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{ bug.importance }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ bug.project?.name || 'No Project' }}</span>
                                    <span>{{ formatDate(bug.created_at) }}</span>
                                </div>
                                
                                <div v-if="bug.user" class="flex items-center mt-2 text-xs text-gray-600 dark:text-gray-400">
                                    <div class="w-4 h-4 bg-gray-300 rounded-full flex items-center justify-center mr-1">
                                        <span class="text-xs font-medium text-gray-700">
                                            {{ bug.user.name.charAt(0) }}
                                        </span>
                                    </div>
                                    <span>{{ bug.user.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
